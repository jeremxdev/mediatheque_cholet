<?php
// ============================================================
// CONSULTATION D'UN LIVRE NUMERIQUE (numeriques/consulter.php)
// Affiche la fiche : livre, fenetre d'acces en ligne et la liste
// des telechargements effectues par les adherents.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant du livre dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// --- Recuperer le livre et son acces numerique ---
$stmt = $pdo->prepare("
    SELECT l.*, d.libelledewey, ln.datedebutacces, ln.datefinacces
    FROM Livre l
    JOIN CODEDEWEY d ON l.codewey = d.codewey
    LEFT JOIN livre_num ln ON ln.Id_Livre = l.Id_Livre
    WHERE l.Id_Livre = ?
");
$stmt->execute([$id]);
$livre = $stmt->fetch();

// Si le livre n'existe pas ou n'est pas numerique, retour a la liste
if (!$livre || $livre['type_support'] === 'papier') {
    header("Location: index.php");
    exit;
}

// --- Historique des telechargements ---
$stmtT = $pdo->prepare("
    SELECT a.nomadh, a.prenomadh, t.datetelechargement
    FROM telechargement t
    JOIN Adherent a ON t.Id_Adherent = a.Id_Adherent
    WHERE t.Id_Livre = ?
    ORDER BY t.datetelechargement DESC
");
$stmtT->execute([$id]);
$telechargements = $stmtT->fetchAll();

// Disponibilite en ligne (calculee a partir des dates d'acces)
$aujourdhui = date('Y-m-d');
$dispo = $livre['datedebutacces']
      && (!$livre['datedebutacces'] || $livre['datedebutacces'] <= $aujourdhui)
      && (!$livre['datefinacces'] || $livre['datefinacces'] >= $aujourdhui);

// Surbrillance du menu "Numeriques"
$currentPage = 'numeriques';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Fiche d'acces numerique</h1>
<p class="subtitle">Disponibilite en ligne du livre</p>

<!-- Fiche principale -->
<div class="detail-container">
    <div class="detail-header">
        <h2><?php echo htmlspecialchars($livre['titre']); ?></h2>
        <div class="btn-group">
            <a href="modifier.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
            <a href="supprimer.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer l\'acces numerique ?')">Supprimer</a>
        </div>
    </div>
    <div class="detail-body">
        <div class="detail-row">
            <span class="detail-label">ISBN</span>
            <span class="detail-value"><?php echo htmlspecialchars($livre['isbn']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Classification Dewey</span>
            <span class="detail-value"><span class="badge badge-blue"><?php echo htmlspecialchars($livre['codewey'] . ' - ' . $livre['libelledewey']); ?></span></span>
        </div>
        <?php if (!$livre['datedebutacces']): ?>
            <div class="detail-row">
                <span class="detail-label">Acces en ligne</span>
                <span class="detail-value"><span class="badge badge-warning">Non configure</span></span>
            </div>
        <?php else: ?>
            <div class="detail-row">
                <span class="detail-label">Date de debut d'acces</span>
                <span class="detail-value"><?php echo date('d/m/Y', strtotime($livre['datedebutacces'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date de fin d'acces</span>
                <span class="detail-value"><?php echo $livre['datefinacces'] ? date('d/m/Y', strtotime($livre['datefinacces'])) : 'Illimitee'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Disponibilite en ligne</span>
                <span class="detail-value">
                    <?php if ($dispo): ?>
                        <span class="badge badge-success">Disponible</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Indisponible</span>
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Historique des telechargements -->
<div class="table-container" style="margin-top: 1.5rem;">
    <div class="table-header">
        <h2><?php echo count($telechargements); ?> telechargement(s)</h2>
    </div>

    <?php if (empty($telechargements)): ?>
        <div class="empty-state">
            <p>&#11015;</p>
            <p>Aucun telechargement enregistre.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Adherent</th>
                    <th>Date de telechargement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($telechargements as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['prenomadh'] . ' ' . $t['nomadh']); ?></td>
                        <td><?php echo $t['datetelechargement'] ? date('d/m/Y H:i', strtotime($t['datetelechargement'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Bouton retour -->
<div style="margin-top: 1.5rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Retour a la liste</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>