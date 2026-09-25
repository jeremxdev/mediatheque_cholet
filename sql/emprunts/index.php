<?php
// ============================================================
// LISTE DES EMPRUNTS (emprunts/index.php)
// Affiche tous les emprunts avec le livre, l'exemplaire
// et l'adherent concernes (via une serie de jointures).
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// --- Recuperer tous les emprunts avec informations associees ---
// Enchainement des jointures :
//   Emprunt -> Adherent     : qui a emprunte
//   Emprunt -> Emprunter    : lien avec l'exemplaire
//   Emprunter -> Exemplaire : quel exemplaire
//   Exemplaire -> Posseder  -> Livre : quel livre
$emprunts = $pdo->query("
    SELECT e.*, a.nomadh, a.prenomadh, ej.code_adherent, ex.Id_Exemplaire, ex.code_exemplaire, ex.etat, l.titre, l.isbn
    FROM Emprunt e
    JOIN Adherent a ON e.Id_Adherent = a.Id_Adherent
    JOIN Adherent ej ON e.Id_Adherent = ej.Id_Adherent
    JOIN Emprunter em ON e.Id_Emprunt = em.Id_Emprunt
    JOIN Exemplaire ex ON em.Id_Exemplaire = ex.Id_Exemplaire
    JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    JOIN Livre l ON p.Id_Livre = l.Id_Livre
    ORDER BY e.dateemprrunt DESC   -- emprunts les plus recents en premier
")->fetchAll();

// Surbrillance du menu "Emprunts"
$currentPage = 'emprunts';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des Emprunts</h1>
<p class="subtitle">Suivi des emprunts de la Mediatheque</p>

<!-- Message de confirmation apres une action -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<div class="table-container">
    <!-- En-tete : compteur + bouton d'enregistrement -->
    <div class="table-header">
        <h2><?php echo count($emprunts); ?> emprunt(s)</h2>
        <a href="enregistrer.php" class="btn btn-primary">+ Enregistrer un emprunt</a>
    </div>

    <!-- Si aucun emprunt -->
    <?php if (empty($emprunts)): ?>
        <div class="empty-state">
            <p>&#128203;</p>
            <p>Aucun emprunt enregistre.</p>
        </div>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Exemplaire</th>
                    <th>Adherent</th>
                    <th>Date emprunt</th>
                    <th>Statut / Retour</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Une ligne par emprunt -->
                <?php foreach ($emprunts as $emprunt): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($emprunt['titre']); ?></strong></td>
                        <!-- Code Dewey+Rang de l'exemplaire + badge etat -->
                        <td>
                            <strong><?php echo htmlspecialchars($emprunt['code_exemplaire'] ?? ('#' . $emprunt['Id_Exemplaire'])); ?></strong>
                            <span class="badge badge-blue"><?php echo htmlspecialchars($emprunt['etat'] ?? '-'); ?></span>
                        </td>
                        <!-- Nom complet + code de l'adherent -->
                        <td>
                            <?php echo htmlspecialchars($emprunt['prenomadh'] . ' ' . $emprunt['nomadh']); ?>
                            <span class="badge badge-gray"><?php echo htmlspecialchars($emprunt['code_adherent'] ?? ''); ?></span>
                        </td>
                        <!-- Date d'emprunt formatee -->
                        <td><?php echo $emprunt['dateemprrunt'] ? date('d/m/Y', strtotime($emprunt['dateemprrunt'])) : '-'; ?></td>
                        <!-- Statut : en cours (pas de retour) ou rendu + bouton retour -->
                        <td>
                            <?php if (empty($emprunt['dateretour'])): ?>
                                <span class="badge badge-success">En cours</span>
                                <a href="retour.php?id=<?php echo $emprunt['Id_Emprunt']; ?>" class="btn btn-secondary btn-sm">Rendre</a>
                            <?php else: ?>
                                <span class="badge badge-gray">Rendu le <?php echo date('d/m/Y', strtotime($emprunt['dateretour'])); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="consulter.php?id=<?php echo $emprunt['Id_Emprunt']; ?>" class="btn btn-secondary btn-sm">Voir</a>
                                <a href="modifier.php?id=<?php echo $emprunt['Id_Emprunt']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
                                <a href="supprimer.php?id=<?php echo $emprunt['Id_Emprunt']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet emprunt ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>