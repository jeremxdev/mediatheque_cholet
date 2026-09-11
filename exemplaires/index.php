<?php
// ============================================================
// LISTE DES EXEMPLAIRES (exemplaires/index.php)
// Affiche tous les exemplaires avec le livre associe (via le lien
// Posseder) et le nombre total d'emprunts de chaque exemplaire.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// --- Recuperer tous les exemplaires ---
// LEFT JOIN : garde aussi les exemplaires sans livre associe.
// Sous-requete : compte le nombre d'emprunts par exemplaire via la table Emprunter.
$exemplaires = $pdo->query("
    SELECT ex.*, l.titre, l.isbn,
    (SELECT COUNT(*) FROM Emprunter em WHERE em.Id_Exemplaire = ex.Id_Exemplaire) AS nb_emprunts
    FROM Exemplaire ex
    LEFT JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire  -- lien exemplaire -> posseder
    LEFT JOIN Livre l ON p.Id_Livre = l.Id_Livre                -- lien posseder -> livre
    ORDER BY ex.Id_Exemplaire DESC                              -- plus recents en premier
")->fetchAll();

// Surbrillance du menu "Exemplaires"
$currentPage = 'exemplaires';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des Exemplaires</h1>
<p class="subtitle">Suivi des exemplaires de la Mediatheque</p>

<!-- Message de confirmation apres une action -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<div class="table-container">
    <!-- En-tete : compteur + bouton d'ajout -->
    <div class="table-header">
        <h2><?php echo count($exemplaires); ?> exemplaire(s)</h2>
        <a href="ajouter.php" class="btn btn-primary">+ Ajouter un exemplaire</a>
    </div>

    <!-- Seulement si la liste est vide -->
    <?php if (empty($exemplaires)): ?>
        <div class="empty-state">
            <p>&#128218;</p>
            <p>Aucun exemplaire enregistre.</p>
        </div>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Livre</th>
                    <th>Etat</th>
                    <th>Emprunts</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Une ligne par exemplaire -->
                <?php foreach ($exemplaires as $ex): ?>
                    <tr>
                        <td>#<?php echo $ex['Id_Exemplaire']; ?></td>
                        <!-- Titre du livre, ou tiret si exemplaire non associe -->
                        <td><?php echo $ex['titre'] ? htmlspecialchars($ex['titre']) : '-'; ?></td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($ex['etat'] ?? '-'); ?></span></td>
                        <td><?php echo $ex['nb_emprunts']; ?></td>
                        <td>
                            <!-- Boutons d'action avec l'id en parametre GET -->
                            <div class="btn-group">
                                <a href="consulter.php?id=<?php echo $ex['Id_Exemplaire']; ?>" class="btn btn-secondary btn-sm">Voir</a>
                                <a href="modifier.php?id=<?php echo $ex['Id_Exemplaire']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
                                <a href="supprimer.php?id=<?php echo $ex['Id_Exemplaire']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet exemplaire ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>