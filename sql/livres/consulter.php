<?php
// ============================================================
// CONSULTATION D'UN LIVRE (livres/consulter.php)
// Affiche la fiche detaillee d'un livre identifie par ?id=
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

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

// --- Exemplaires papier associes a ce livre ---
// Jointure Posseder -> Exemplaire. On recupere le code (Dewey + rang),
// l'etat et la disponibilite actuelle de chaque exemplaire.
$stmtEx = $pdo->prepare("
    SELECT ex.*
    FROM Posseder p
    JOIN Exemplaire ex ON ex.Id_Exemplaire = p.Id_Exemplaire
    WHERE p.Id_Livre = ?
    ORDER BY ex.Id_Exemplaire ASC
");
$stmtEx->execute([$id]);
$exemplaires = $stmtEx->fetchAll();

// --- Version numerique du livre (disponibilite en ligne) ---
$stmtNum = $pdo->prepare("SELECT * FROM livre_num WHERE Id_Livre = ?");
$stmtNum->execute([$id]);
$livreNum = $stmtNum->fetch();

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
        <div class="detail-row">
            <span class="detail-label">Type de support</span>
            <!-- Indique si le livre existe en papier, en numerique ou les deux -->
            <span class="detail-value">
                <?php if ($livre['type_support'] === 'papier'): ?>
                    <span class="badge badge-gray">Papier</span>
                <?php elseif ($livre['type_support'] === 'numerique'): ?>
                    <span class="badge badge-purple">Numerique</span>
                <?php else: ?>
                    <span class="badge badge-purple">Papier + numerique</span>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<!-- Exemplaires papier de ce livre -->
<div class="table-container" style="margin-top: 1.5rem;">
    <div class="table-header">
        <h2><?php echo count($exemplaires); ?> exemplaire(s) papier</h2>
        <a href="../exemplaires/ajouter.php" class="btn btn-primary btn-sm">+ Ajouter un exemplaire</a>
    </div>

    <?php if (empty($exemplaires)): ?>
        <div class="empty-state">
            <p>&#128218;</p>
            <p>Aucun exemplaire papier associe a ce livre.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Code exemplaire</th>
                    <th>Etat</th>
                    <th>Disponibilite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exemplaires as $ex): ?>
                    <tr>
                        <!-- Code = classification Dewey + rang d'arrivee -->
                        <td><strong><?php echo htmlspecialchars($ex['code_exemplaire']); ?></strong></td>
                        <td><span class="badge badge-blue"><?php echo htmlspecialchars($ex['etat'] ?? '-'); ?></span></td>
                        <!-- Disponibilite = aucun emprunt en cours -->
                        <td>
                            <?php if (exemplaire_est_disponible($pdo, $ex['Id_Exemplaire'])): ?>
                                <span class="badge badge-success">Disponible</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Emprunte</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Informations sur la version numerique (si le support le permet) -->
<?php if ($livre['type_support'] === 'numerique' || $livre['type_support'] === 'les deux'): ?>
    <div class="table-container" style="margin-top: 1.5rem;">
        <div class="table-header">
            <h2>Version numerique</h2>
            <a href="../numeriques/<?php echo $livreNum ? 'modifier.php?id=' . $livre['Id_Livre'] : 'ajouter.php?id=' . $livre['Id_Livre']; ?>" class="btn btn-primary btn-sm">
                <?php echo $livreNum ? 'Gerer l\'acces' : 'Ajouter l\'acces'; ?>
            </a>
        </div>
        <?php if (!$livreNum): ?>
            <div class="empty-state">
                <p>&#128421;</p>
                <p>Aucune information d'acces numerique enregistree.</p>
            </div>
        <?php else: ?>
            <?php
            // Disponibilite en ligne : debut atteint et fin pas encore depassee
            $aujourdhui = date('Y-m-d');
            $dispo = (!$livreNum['datedebutacces'] || $livreNum['datedebutacces'] <= $aujourdhui)
                   && (!$livreNum['datefinacces'] || $livreNum['datefinacces'] >= $aujourdhui);
            ?>
            <table>
                <thead>
                    <tr>
                        <th>Date debut d'acces</th>
                        <th>Date fin d'acces</th>
                        <th>Disponibilite en ligne</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $livreNum['datedebutacces'] ? date('d/m/Y', strtotime($livreNum['datedebutacces'])) : '-'; ?></td>
                        <td><?php echo $livreNum['datefinacces'] ? date('d/m/Y', strtotime($livreNum['datefinacces'])) : 'Illimitee'; ?></td>
                        <td>
                            <?php if ($dispo): ?>
                                <span class="badge badge-success">Disponible</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Indisponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Bouton retour a la liste -->
<div style="margin-top: 1.5rem;">
    <a href="index.php" class="btn btn-secondary">&larr; Retour a la liste</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>