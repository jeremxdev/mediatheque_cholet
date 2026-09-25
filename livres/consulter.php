<?php
// ============================================================
// CONSULTATION D'UN LIVRE (livres/consulter.php)
// Affiche la fiche detaillee d'un livre identifie par ?id=
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Recupere l'identifiant du livre passe dans l'URL (GET), securise par intval()
$id = intval($_GET['id'] ?? 0);

// Si l'id est invalide (0 ou negatif), on revient a la liste
if ($id <= 0) { header("Location: index.php"); exit; }

// Requete preparee : recupere le livre + le libelle Dewey correspondant
$stmt = $pdo->prepare("
    SELECT l.*, d.libelledewey
    FROM Livre l
    JOIN CODEDEWEY d ON l.codewey = d.codewey
    WHERE l.Id_Livre = ?
");
$stmt->execute([$id]);
$livre = $stmt->fetch();

// Si le livre n'existe pas, retour a la liste
if (!$livre) { header("Location: index.php"); exit; }

// Active le menu "Livres" et affiche l'en-tete
$currentPage = 'livres';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Fiche du livre</h1>
<p class="subtitle">Informations detaillees</p>

<!-- Conteneur de la fiche -->
<div class="detail-container">
    <!-- En-tete de la fiche : titre + boutons d'action -->
    <div class="detail-header">
        <h2><?php echo htmlspecialchars($livre['titre']); ?></h2>
        <div class="btn-group">
            <a href="modifier.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
            <a href="supprimer.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce livre ?')">Supprimer</a>
        </div>
    </div>

    <!-- Corps de la fiche : chaque ligne affiche une information -->
    <div class="detail-body">
        <div class="detail-row">
            <span class="detail-label">Titre</span>
            <span class="detail-value"><?php echo htmlspecialchars($livre['titre']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">ISBN</span>
            <span class="detail-value"><?php echo htmlspecialchars($livre['isbn']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Classification Dewey</span>
            <span class="detail-value"><span class="badge badge-blue"><?php echo htmlspecialchars($livre['codewey'] . ' - ' . $livre['libelledewey']); ?></span></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date de sortie</span>
            <!-- Date formatee en jj/mm/aaaa, ou tiret si absente -->
            <span class="detail-value"><?php echo $livre['datesortie'] ? date('d/m/Y', strtotime($livre['datesortie'])) : '-'; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date d'achat</span>
            <span class="detail-value"><?php echo $livre['dateachat'] ? date('d/m/Y', strtotime($livre['dateachat'])) : '-'; ?></span>
        </div>
    </div>
</div>

<!-- Bouton retour a la liste -->
<div style="margin-top: 1.5rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Retour a la liste</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>