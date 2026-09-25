<?php
// ============================================================
// CONSULTATION D'UN EMPRUNT (emprunts/consulter.php)
// Affiche la fiche detaillee d'un emprunt : livre, exemplaire,
// adherent (coordonees) et dates.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'emprunt dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// --- Recuperer l'emprunt avec toutes ses informations ---
// Jointures identiques a la liste : adherent, exemplaire, livre.
$stmt = $pdo->prepare("
    SELECT e.*, a.nomadh, a.prenomadh, a.mailadh, a.telephoneadh, a.code_adherent, ex.Id_Exemplaire, ex.code_exemplaire, ex.etat, l.titre, l.isbn
    FROM Emprunt e
    JOIN Adherent a ON e.Id_Adherent = a.Id_Adherent
    JOIN Emprunter em ON e.Id_Emprunt = em.Id_Emprunt
    JOIN Exemplaire ex ON em.Id_Exemplaire = ex.Id_Exemplaire
    JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE e.Id_Emprunt = ?
");
$stmt->execute([$id]);
$emprunt = $stmt->fetch();

// Si l'emprunt n'existe pas, retour a la liste
if (!$emprunt) { header("Location: index.php"); exit; }

// Surbrillance du menu "Emprunts"
$currentPage = 'emprunts';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Fiche d'emprunt</h1>
<p class="subtitle">Details de l'emprunt</p>

<!-- Fiche detaillee -->
<div class="detail-container">
    <div class="detail-header">
        <h2>Emprunt #<?php echo $emprunt['Id_Emprunt']; ?></h2>
        <div class="btn-group">
            <a href="modifier.php?id=<?php echo $emprunt['Id_Emprunt']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
            <a href="supprimer.php?id=<?php echo $emprunt['Id_Emprunt']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet emprunt ?')">Supprimer</a>
        </div>
    </div>
    <div class="detail-body">
        <!-- Chaque bloc affiche une information de l'emprunt -->
        <div class="detail-row">
            <span class="detail-label">Livre</span>
            <span class="detail-value"><strong><?php echo htmlspecialchars($emprunt['titre']); ?></strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">ISBN</span>
            <span class="detail-value"><?php echo htmlspecialchars($emprunt['isbn']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Exemplaire</span>
            <span class="detail-value"><?php echo htmlspecialchars($emprunt['code_exemplaire'] ?? ('#' . $emprunt['Id_Exemplaire'])); ?> (<?php echo htmlspecialchars($emprunt['etat'] ?? '-'); ?>)</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Adherent</span>
            <span class="detail-value">
                <?php echo htmlspecialchars($emprunt['prenomadh'] . ' ' . $emprunt['nomadh']); ?>
                <span class="badge badge-gray"><?php echo htmlspecialchars($emprunt['code_adherent'] ?? ''); ?></span>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email adherent</span>
            <span class="detail-value"><?php echo htmlspecialchars($emprunt['mailadh']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Telephone adherent</span>
            <span class="detail-value"><?php echo htmlspecialchars($emprunt['telephoneadh'] ?? '-'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date d'emprunt</span>
            <span class="detail-value"><?php echo $emprunt['dateemprrunt'] ? date('d/m/Y', strtotime($emprunt['dateemprrunt'])) : '-'; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date de retour</span>
            <span class="detail-value">
                <?php if (empty($emprunt['dateretour'])): ?>
                    <span class="badge badge-success">En cours</span>
                <?php else: ?>
                    <?php echo date('d/m/Y', strtotime($emprunt['dateretour'])); ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<!-- Bouton retour -->
<div style="margin-top: 1.5rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Retour a la liste</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>