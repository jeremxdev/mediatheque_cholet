<?php
// ============================================================
// LISTE DES LIVRES (livres/index.php)
// Affiche le tableau de tous les livres du catalogue,
// avec un lien d'action par livre (voir / modifier / supprimer).
// ============================================================

// Connexion a la base de donnees (fournit $pdo)
require_once __DIR__ . '/../config/database.php';

// --- Recuperer tous les livres avec leur classification Dewey ---
// La jointure (JOIN) relie la table Livre a la table CODEDEWEY
// grace a la cle etrangere codewey, pour obtenir le libelle associe.
$livres = $pdo->query("
    SELECT l.*, d.libelledewey
    FROM Livre l
    JOIN CODEDEWEY d ON l.codewey = d.codewey
    ORDER BY l.titre ASC          -- tri alphabetique par titre
")->fetchAll();

// Surbrillance du menu "Livres" dans la navigation
$currentPage = 'livres';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Titre de la page -->
<h1>Gestion des Livres</h1>
<p class="subtitle">Catalogue de la Mediatheque de Cholet</p>

<!-- Message de confirmation affiche apres un insert/update/delete -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<div class="table-container">
    <!-- En-tete du tableau : compteur + bouton d'ajout -->
    <div class="table-header">
        <h2><?php echo count($livres); ?> livre(s)</h2>
        <a href="ajouter.php" class="btn btn-primary">+ Ajouter un livre</a>
    </div>

    <!-- Si aucun livre : message d'information -->
    <?php if (empty($livres)): ?>
        <div class="empty-state">
            <p>&#128214;</p>
            <p>Aucun livre dans le catalogue.</p>
        </div>

    <!-- Sinon : tableau des livres -->
    <?php else: ?>
        <table>
            <!-- Colonnes du tableau -->
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>ISBN</th>
                    <th>Classification</th>
                    <th>Date sortie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Boucle : une ligne HTML par livre -->
                <?php foreach ($livres as $livre): ?>
                    <tr>
                        <!-- htmlspecialchars() protege contre les attaques XSS -->
                        <td><strong><?php echo htmlspecialchars($livre['titre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($livre['isbn']); ?></td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($livre['libelledewey']); ?></span></td>
                        <!-- Formatage de la date francais jj/mm/aaaa -->
                        <td><?php echo $livre['datesortie'] ? date('d/m/Y', strtotime($livre['datesortie'])) : '-'; ?></td>
                        <td>
                            <!-- Boutons d'action, chacun transmet l'id en parametre GET -->
                            <div class="btn-group">
                                <a href="consulter.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-secondary btn-sm">Voir</a>
                                <a href="modifier.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
                                <!-- confirm() demande la validation avant la suppression -->
                                <a href="supprimer.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce livre ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>