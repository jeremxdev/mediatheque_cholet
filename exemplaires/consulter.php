<?php
// ============================================================
// CONSULTATION D'UN EXEMPLAIRE (exemplaires/consulter.php)
// Affiche la fiche d'un exemplaire et son historique d'emprunts.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Identifiant de l'exemplaire dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// --- Recuperer l'exemplaire et son livre associe ---
$stmt = $pdo->prepare("
    SELECT ex.*, l.titre, l.isbn
    FROM Exemplaire ex
    LEFT JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    LEFT JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE ex.Id_Exemplaire = ?
");
$stmt->execute([$id]);
$exemplaire = $stmt->fetch();

// Si l'exemplaire n'existe pas, retour a la liste
if (!$exemplaire) { header("Location: index.php"); exit; }

// --- Historique des emprunts de cet exemplaire ---
// Jointure : Emprunter -> Emprunt -> Adherent, pour obtenir les dates
// et le nom de l'adherent qui a emprunte cet exemplaire.
$stmt2 = $pdo->prepare("
    SELECT e.*, a.nomadh, a.prenomadh
    FROM Emprunter em
    JOIN Emprunt e ON em.Id_Emprunt = e.Id_Emprunt
    JOIN Adherent a ON e.Id_Adherent = a.Id_Adherent
    WHERE em.Id_Exemplaire = ?
    ORDER BY e.dateemprrunt DESC
");
$stmt2->execute([$id]);
$emprunts = $stmt2->fetchAll();

// Surbrillance du menu "Exemplaires"
$currentPage = 'exemplaires';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Fiche exemplaire</h1>
<p class="subtitle">Informations detaillees</p>

<!-- Fiche principale -->
<div class="detail-container">
    <div class="detail-header">
        <h2>Exemplaire #<?php echo $exemplaire['Id_Exemplaire']; ?></h2>
        <div class="btn-group">
            <a href="modifier.php?id=<?php echo $exemplaire['Id_Exemplaire']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
            <a href="supprimer.php?id=<?php echo $exemplaire['Id_Exemplaire']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet exemplaire ?')">Supprimer</a>
        </div>
    </div>
    <div class="detail-body">
        <div class="detail-row">
            <span class="detail-label">N° exemplaire</span>
            <span class="detail-value">#<?php echo $exemplaire['Id_Exemplaire']; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Livre</span>
            <span class="detail-value"><?php echo htmlspecialchars($exemplaire['titre'] ?? '-'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">ISBN</span>
            <span class="detail-value"><?php echo htmlspecialchars($exemplaire['isbn'] ?? '-'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Etat</span>
            <span class="detail-value"><span class="badge badge-blue"><?php echo htmlspecialchars($exemplaire['etat'] ?? '-'); ?></span></span>
        </div>
    </div>
</div>

<!-- Historique des emprunts (affiché uniquement s'il y en a) -->
<?php if (!empty($emprunts)): ?>
    <div class="table-container" style="margin-top: 1.5rem;">
        <div class="table-header">
            <h2>Historique des emprunts</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Adherent</th>
                    <th>Date emprunt</th>
                    <th>Date retour</th>
                </tr>
            </thead>
            <tbody>
                <!-- Une ligne par emprunt de l'historique -->
                <?php foreach ($emprunts as $em): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($em['prenomadh'] . ' ' . $em['nomadh']); ?></td>
                        <td><?php echo $em['dateemprrunt'] ? date('d/m/Y', strtotime($em['dateemprrunt'])) : '-'; ?></td>
                        <td><?php echo $em['dateretour'] ? date('d/m/Y', strtotime($em['dateretour'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Bouton retour -->
<div style="margin-top: 1.5rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Retour a la liste</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>