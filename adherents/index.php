<?php
// ============================================================
// LISTE DES ADHERENTS (adherents/index.php)
// Affiche le tableau des adherents inscrits a la mediatheque.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// --- Recuperer tous les adherents ---
// Tri alphabetique : par nom puis par prenom.
$adherents = $pdo->query("SELECT * FROM Adherent ORDER BY nomadh ASC, prenomadh ASC")->fetchAll();

// Surbrillance du menu "Adherents"
$currentPage = 'adherents';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des Adherents</h1>
<p class="subtitle">Suivi des inscriptions a la Mediatheque</p>

<!-- Message de confirmation apres une action -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<div class="table-container">
    <!-- En-tete : compteur + bouton d'ajout -->
    <div class="table-header">
        <h2><?php echo count($adherents); ?> adherent(s)</h2>
        <a href="ajouter.php" class="btn btn-primary">+ Ajouter un adherent</a>
    </div>

    <!-- Si aucun adherent inscrit -->
    <?php if (empty($adherents)): ?>
        <div class="empty-state">
            <p>&#128101;</p>
            <p>Aucun adherent inscrit.</p>
        </div>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Naissance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Une ligne par adherent -->
                <?php foreach ($adherents as $adherent): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($adherent['nomadh']); ?></strong></td>
                        <td><?php echo htmlspecialchars($adherent['prenomadh']); ?></td>
                        <td><?php echo htmlspecialchars($adherent['mailadh']); ?></td>
                        <td><?php echo htmlspecialchars($adherent['telephoneadh'] ?? '-'); ?></td>
                        <!-- Date de naissance formatee jj/mm/aaaa -->
                        <td><?php echo $adherent['datenaissance'] ? date('d/m/Y', strtotime($adherent['datenaissance'])) : '-'; ?></td>
                        <td>
                            <!-- Boutons d'action avec l'id en parametre GET -->
                            <div class="btn-group">
                                <a href="consulter.php?id=<?php echo $adherent['Id_Adherent']; ?>" class="btn btn-secondary btn-sm">Voir</a>
                                <a href="modifier.php?id=<?php echo $adherent['Id_Adherent']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
                                <!-- confirm() demande confirmation avant suppression -->
                                <a href="supprimer.php?id=<?php echo $adherent['Id_Adherent']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet adherent ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>