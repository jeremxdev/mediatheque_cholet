<?php
// ============================================================
// LISTE DES LIVRES NUMERIQUES (numeriques/index.php)
// Affiche les livres disponibles sous forme numerique avec
// leur fenetre d'acces en ligne et le nombre de telechargements.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// --- Recuperer les livres au support numerique ---
// LEFT JOIN livre_num : un livre peut ne pas encore avoir d'acces configure.
$livres = $pdo->query("
    SELECT l.*, d.libelledewey, ln.datedebutacces, ln.datefinacces,
    (SELECT COUNT(*) FROM telechargement t WHERE t.Id_Livre = l.Id_Livre) AS nb_telechargements
    FROM Livre l
    JOIN CODEDEWEY d ON l.codewey = d.codewey
    LEFT JOIN livre_num ln ON ln.Id_Livre = l.Id_Livre
    WHERE l.type_support IN ('numerique', 'les deux')
    ORDER BY l.titre ASC
")->fetchAll();

// Surbrillance du menu "Numeriques"
$currentPage = 'numeriques';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des Livres Numeriques</h1>
<p class="subtitle">Disponibilite en ligne et telechargements</p>

<!-- Message de confirmation apres une action -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<div class="table-container">
    <!-- En-tete : compteur + bouton d'ajout -->
    <div class="table-header">
        <h2><?php echo count($livres); ?> livre(s) numerique(s)</h2>
        <a href="ajouter.php" class="btn btn-primary">+ Ajouter un acces numerique</a>
    </div>

    <!-- Si aucun livre numerique -->
    <?php if (empty($livres)): ?>
        <div class="empty-state">
            <p>&#128421;</p>
            <p>Aucun livre numerique dans le catalogue.</p>
        </div>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Classification</th>
                    <th>Acces en ligne</th>
                    <th>Disponibilite</th>
                    <th>Telechargements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livres as $livre): ?>
                    <?php
                    // Disponibilite en ligne : debut atteint et fin non depassee
                    $aujourdhui = date('Y-m-d');
                    $dispo = (!$livre['datedebutacces'] || $livre['datedebutacces'] <= $aujourdhui)
                          && (!$livre['datefinacces'] || $livre['datefinacces'] >= $aujourdhui);
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($livre['titre']); ?></strong></td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($livre['libelledewey']); ?></span></td>
                        <!-- Fenetre d'acces en ligne -->
                        <td>
                            <?php if (!$livre['datedebutacces']): ?>
                                <span class="badge badge-warning">Acces non configure</span>
                            <?php else: ?>
                                du <?php echo date('d/m/Y', strtotime($livre['datedebutacces'])); ?>
                                au <?php echo $livre['datefinacces'] ? date('d/m/Y', strtotime($livre['datefinacces'])) : 'illimite'; ?>
                            <?php endif; ?>
                        </td>
                        <!-- Statut de disponibilite -->
                        <td>
                            <?php if (!$livre['datedebutacces']): ?>
                                <span class="badge badge-gray">Non determinee</span>
                            <?php elseif ($dispo): ?>
                                <span class="badge badge-success">Disponible</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Indisponible</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $livre['nb_telechargements']; ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="consulter.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-secondary btn-sm">Voir</a>
                                <?php if ($livre['datedebutacces']): ?>
                                    <a href="modifier.php?id=<?php echo $livre['Id_Livre']; ?>" class="btn btn-secondary btn-sm">Modifier</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>