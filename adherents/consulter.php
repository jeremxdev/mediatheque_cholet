<?php
// ============================================================
// CONSULTATION D'UN ADHERENT (adherents/consulter.php)
// Affiche la fiche detaillee d'un adherent (avec son parrain).
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Identifiant de l'adherent dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge l'adherent correspondant
$stmt = $pdo->prepare("SELECT * FROM Adherent WHERE Id_Adherent = ?");
$stmt->execute([$id]);
$adherent = $stmt->fetch();

// Si l'adherent n'existe pas, retour a la liste
if (!$adherent) { header("Location: index.php"); exit; }

// --- Recuperer l'eventuel parrain ---
// Le champ Id_Adherent_1 de l'adherent contient l'id de son parrain.
$parrain = null;
if ($adherent['Id_Adherent_1']) {
    $stmt2 = $pdo->prepare("SELECT nomadh, prenomadh FROM Adherent WHERE Id_Adherent = ?");
    $stmt2->execute([$adherent['Id_Adherent_1']]);
    $parrain = $stmt2->fetch();
}

// Surbrillance du menu "Adherents"
$currentPage = 'adherents';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Fiche adherent</h1>
<p class="subtitle">Informations detaillees</p>

<!-- Fiche detaillee -->
<div class="detail-container">
    <div class="detail-header">
        <!-- Nom complet : prenom + nom -->
        <h2><?php echo htmlspecialchars($adherent['prenomadh'] . ' ' . $adherent['nomadh']); ?></h2>
        <div class="btn-group">
            <a href="modifier.php?id=<?php echo $adherent['Id_Adherent']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
            <a href="supprimer.php?id=<?php echo $adherent['Id_Adherent']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet adherent ?')">Supprimer</a>
        </div>
    </div>
    <div class="detail-body">
        <!-- Chaque bloc affiche une information de l'adherent -->
        <div class="detail-row">
            <span class="detail-label">Nom</span>
            <span class="detail-value"><?php echo htmlspecialchars($adherent['nomadh']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Prenom</span>
            <span class="detail-value"><?php echo htmlspecialchars($adherent['prenomadh']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value"><?php echo htmlspecialchars($adherent['mailadh']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Telephone</span>
            <span class="detail-value"><?php echo htmlspecialchars($adherent['telephoneadh'] ?? '-'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Adresse</span>
            <span class="detail-value"><?php echo htmlspecialchars($adherent['adresseadh'] ?? '-'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date de naissance</span>
            <span class="detail-value"><?php echo $adherent['datenaissance'] ? date('d/m/Y', strtotime($adherent['datenaissance'])) : '-'; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Parrain</span>
            <!-- Nom du parrain, ou tiret si aucun parrain -->
            <span class="detail-value"><?php echo $parrain ? htmlspecialchars($parrain['prenomadh'] . ' ' . $parrain['nomadh']) : '-'; ?></span>
        </div>
    </div>
</div>

<!-- Bouton retour -->
<div style="margin-top: 1.5rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Retour a la liste</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>