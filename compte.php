<?php
// ============================================================
// ESPACE ADHERENT (compte.php)
// Affiche l'espace personnel d'un adherent connecte :
// sa fiche, sa carte et l'historique de ses emprunts.
// Accessible uniquement aux adherents (un admin est redirige
// vers le tableau de bord).
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/config/database.php';

// Gestion de session + fonctions d'auth
require_once __DIR__ . '/includes/auth.php';

// Exige d'etre connecte
require_auth();

// Si l'utilisateur est administrateur, retour au tableau de bord
if (is_admin()) {
    header("Location: " . $prefix . "index.php");
    exit;
}

// Recupere l'identifiant adherent de l'utilisateur connecte
$user = current_user();
$Id_Adherent = $user['Id_Adherent'];

// --- Nouvel emprunt : traitement du formulaire ---
$errors = [];
$Id_Exemplaire = 0;
// Date d'emprunt proposee par defaut : aujourd'hui
// (le retour est enregistre lors de la restitution effective)
$dateemprrunt = date('Y-m-d');

// --- Validite de l'adhesion (carte valable 1 an) ---
$statutAdhesion = statut_validite_adhesion($pdo, $Id_Adherent);

// --- Liste des exemplaires disponibles ---
// Regle de gestion : un exemplaire est disponible s'il n'a AUCUN emprunt
// en cours (emprunt dont le retour n'a pas ete enregistre, dateretour IS NULL).
$disponibles = $pdo->query("
    SELECT ex.Id_Exemplaire, ex.code_exemplaire, ex.etat, l.titre
    FROM Exemplaire ex
    LEFT JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    LEFT JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE NOT EXISTS (
        SELECT 1 FROM Emprunter em
        JOIN Emprunt e ON em.Id_Emprunt = e.Id_Emprunt
        WHERE em.Id_Exemplaire = ex.Id_Exemplaire AND e.dateretour IS NULL
    )
    ORDER BY l.titre IS NULL, l.titre ASC, ex.Id_Exemplaire ASC
")->fetchAll();

// Soumission du formulaire d'emprunt (champ cache "action" sert de garde-fou)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'emprunter') {
    $Id_Exemplaire = intval($_POST['Id_Exemplaire'] ?? 0);
    $dateemprrunt = trim($_POST['dateemprrunt'] ?? '');

    // Validations
    if ($Id_Exemplaire <= 0) $errors[] = "Veuillez selectionner un exemplaire disponible.";
    if (empty($dateemprrunt)) $errors[] = "La date d'emprunt est obligatoire.";

    // -- Regle de gestion : une adhesion expiree interdit tout emprunt
    if (!$statutAdhesion['valide']) {
        $errors[] = "Votre adhesion est expiree : reencaissez-vous aupres de la mediatheque.";
    }

    if (empty($errors)) {
        // Verifie que l'exemplaire est TOUJOURS disponible (pas d'emprunt en cours entre-temps)
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM Emprunter em
            JOIN Emprunt e ON em.Id_Emprunt = e.Id_Emprunt
            WHERE em.Id_Exemplaire = ? AND e.dateretour IS NULL
        ");
        $check->execute([$Id_Exemplaire]);
        if ($check->fetchColumn() > 0) {
            $errors[] = "Cet exemplaire n'est plus disponible.";
        } else {
            // Insertion dans une transaction : Emprunt + lien Emprunter
            $pdo->beginTransaction();

            // 1. Creation de l'emprunt (dateemprrunt + adherent). La date de
            //    retour est NULL : elle sera remplie lors du retour du livre.
            $stmt = $pdo->prepare("INSERT INTO Emprunt (dateemprrunt, dateretour, Id_Adherent) VALUES (?, NULL, ?)");
            $stmt->execute([$dateemprrunt, $Id_Adherent]);
            $idEmprunt = $pdo->lastInsertId();

            // 2. Liaison de l'exemplaire choisi a cet emprunt (table Emprunter)
            $stmt2 = $pdo->prepare("INSERT INTO Emprunter (Id_Exemplaire, Id_Emprunt) VALUES (?, ?)");
            $stmt2->execute([$Id_Exemplaire, $idEmprunt]);

            // Validation de la transaction
            $pdo->commit();

            // Redirection pour actualiser la liste (avec message de succes)
            header("Location: compte.php?success=Emprunt enregistre avec succes");
            exit;
        }
    }
}

// --- Retour de livre : traitement du formulaire "Rendre" ---
// Le retour ENREGISTRE la date de retour (l'exemplaire redevient ainsi
// disponible) ; il met aussi a jour l'etat de l'exemplaire si besoin.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rendre') {
    $Id_Emprunt = intval($_POST['Id_Emprunt'] ?? 0);
    $etat = trim($_POST['etat'] ?? '');

    if ($Id_Emprunt > 0) {
        // Securite : l'emprunt doit appartenir a l'adherent connecte
        // et ne pas etre deja rendu
        $check = $pdo->prepare("SELECT ex.Id_Exemplaire, e.dateretour FROM Emprunt e JOIN Emprunter em ON e.Id_Emprunt = em.Id_Emprunt JOIN Exemplaire ex ON em.Id_Exemplaire = ex.Id_Exemplaire WHERE e.Id_Emprunt = ? AND e.Id_Adherent = ?");
        $check->execute([$Id_Emprunt, $Id_Adherent]);
        $empruntEnCours = $check->fetch();

        if ($empruntEnCours && empty($empruntEnCours['dateretour'])) {
            // Transaction : enregistrement de la date de retour + etat
            $pdo->beginTransaction();

            // 1. La date de retour est renseignee -> emprunt cloture
            $stmt = $pdo->prepare("UPDATE Emprunt SET dateretour = ? WHERE Id_Emprunt = ?");
            $stmt->execute([date('Y-m-d'), $Id_Emprunt]);

            // 2. Mise a jour de l'etat de l'exemplaire si une valeur valide
            //    a ete fournie par le formulaire
            if (in_array($etat, ETATS_EXEMPLAIRE, true)) {
                $stmt2 = $pdo->prepare("UPDATE Exemplaire SET etat = ? WHERE Id_Exemplaire = ?");
                $stmt2->execute([$etat, $empruntEnCours['Id_Exemplaire']]);
            }

            // Validation de la transaction
            $pdo->commit();
        }
    }

    // Redirection avec message de confirmation
    header("Location: compte.php?success=Retour enregistre avec succes");
    exit;
}

// --- Telechargement d'un livre numerique : traitement du formulaire ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'telecharger') {
    $Id_Livre = intval($_POST['Id_Livre'] ?? 0);

    // L'adhesion doit etre valide pour acceder aux ressources numeriques
    if (!$statutAdhesion['valide']) {
        $errors[] = "Votre adhesion est expiree : vous ne pouvez plus telecharger.";
    } else {
        // Verifie que le livre existe bien en version numerique et qu'il
        // est disponible a la consultation (periodes d'acces respectees)
        $stmtD = $pdo->prepare("
            SELECT Id_Livre FROM livre_num
            WHERE Id_Livre = ?
              AND (datedebutacces IS NULL OR datedebutacces <= ?)
              AND (datefinacces IS NULL OR datefinacces >= ?)
        ");
        $stmtD->execute([$Id_Livre, date('Y-m-d'), date('Y-m-d')]);
        if ($stmtD->fetchColumn()) {
            // Historise le telechargement
            $stmtT = $pdo->prepare("INSERT INTO telechargement (Id_Livre, Id_Adherent, datetelechargement) VALUES (?, ?, ?)");
            $stmtT->execute([$Id_Livre, $Id_Adherent, date('Y-m-d')]);
            header("Location: compte.php?success=Telechargement enregistre avec succes");
        } else {
            header("Location: compte.php?error=Livre numerique indisponible");
        }
        exit;
    }
}

// --- Fiche de l'adherent ---
$stmt = $pdo->prepare("SELECT * FROM Adherent WHERE Id_Adherent = ?");
$stmt->execute([$Id_Adherent]);
$adherent = $stmt->fetch();

if (!$adherent) {
    // Aucun adherent lie au compte : on deconnecte proprement
    header("Location: logout.php");
    exit;
}

// --- Parrain eventuel ---
$parrain = null;
if ($adherent['Id_Adherent_1']) {
    $stmt2 = $pdo->prepare("SELECT nomadh, prenomadh FROM Adherent WHERE Id_Adherent = ?");
    $stmt2->execute([$adherent['Id_Adherent_1']]);
    $parrain = $stmt2->fetch();
}

// --- Carte de la mediatheque ---
$stmt3 = $pdo->prepare("SELECT * FROM Carte WHERE Id_Adherent = ? ORDER BY dateemission DESC");
$stmt3->execute([$Id_Adherent]);
$cartes = $stmt3->fetchAll();

// --- Historique des emprunts de l'adherent ---
// Jointures : Emprunt -> Emprunter -> Exemplaire -> (Posseder) -> Livre
$stmt4 = $pdo->prepare("
    SELECT e.*, ex.Id_Exemplaire, ex.code_exemplaire, l.titre
    FROM Emprunt e
    JOIN Emprunter em ON e.Id_Emprunt = em.Id_Emprunt
    JOIN Exemplaire ex ON em.Id_Exemplaire = ex.Id_Exemplaire
    LEFT JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    LEFT JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE e.Id_Adherent = ?
    ORDER BY e.dateemprrunt DESC
");
$stmt4->execute([$Id_Adherent]);
$emprunts = $stmt4->fetchAll();

// --- Livres numeriques ---
// Version numerique des livres (type_support "numerique" ou "les deux").
$stmt5 = $pdo->prepare("
    SELECT l.Id_Livre, l.titre, l.isbn, ln.datedebutacces, ln.datefinacces
    FROM livre_num ln
    JOIN Livre l ON ln.Id_Livre = l.Id_Livre
    ORDER BY l.titre ASC
");
$stmt5->execute();
$numeriques = $stmt5->fetchAll();

// --- Historique des telechargements de l'adherent ---
$stmt6 = $pdo->prepare("
    SELECT t.datetelechargement, l.titre
    FROM telechargement t
    JOIN Livre l ON t.Id_Livre = l.Id_Livre
    WHERE t.Id_Adherent = ?
    ORDER BY t.datetelechargement DESC
");
$stmt6->execute([$Id_Adherent]);
$telechargements = $stmt6->fetchAll();

// Cette page correspond au menu "Mon compte"
$currentPage = 'compte';

// En-tete commun (le header affiche le menu adherent grace a la session)
require_once __DIR__ . '/includes/header.php';
?>

<h1>Mon espace adherent</h1>
<p class="subtitle">Bienvenue <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>

<!-- Message de confirmation apres une action -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<!-- Fiche de l'adherent -->
<div class="detail-container">
    <div class="detail-header">
        <h2>Ma fiche</h2>
    </div>
    <div class="detail-body">
        <div class="detail-row">
            <span class="detail-label">Code adherent</span>
            <span class="detail-value"><?php echo htmlspecialchars($adherent['code_adherent'] ?? '-'); ?></span>
        </div>
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
            <span class="detail-label">Date d'adhesion</span>
            <span class="detail-value"><?php echo $adherent['dateadhesion'] ? date('d/m/Y', strtotime($adherent['dateadhesion'])) : '-'; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Validite de l'adhesion</span>
            <span class="detail-value">
                <?php if ($statutAdhesion['expiration'] === null): ?>
                    <span class="badge badge-gray">Aucune carte</span>
                <?php elseif ($statutAdhesion['valide']): ?>
                    <span class="badge badge-success">Valide</span>
                    <span class="subtitle"> (jusqu'au <?php echo date('d/m/Y', strtotime($statutAdhesion['expiration'])); ?>)</span>
                <?php else: ?>
                    <span class="badge badge-danger">Expiree</span>
                    <span class="subtitle"> (le <?php echo date('d/m/Y', strtotime($statutAdhesion['expiration'])); ?>)</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Referent (adulte)</span>
            <span class="detail-value"><?php echo $parrain ? htmlspecialchars($parrain['prenomadh'] . ' ' . $parrain['nomadh']) : '-'; ?></span>
        </div>
    </div>
</div>

<!-- Carte de la mediatheque -->
<div class="detail-container" style="margin-top: 1.5rem;">
    <div class="detail-header">
        <h2>Ma carte</h2>
    </div>
    <div class="detail-body">
        <?php if (empty($cartes)): ?>
            <p class="empty-state">Aucune carte enregistree.</p>
        <?php else: ?>
            <?php foreach ($cartes as $carte): ?>
                <div class="detail-row">
                    <span class="detail-label">Carte #<?php echo htmlspecialchars($carte['Id_Carte']); ?></span>
                    <span class="detail-value">Emise le <?php echo $carte['dateemission'] ? date('d/m/Y', strtotime($carte['dateemission'])) : '-'; ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Livres numeriques : consultation et telechargement -->
<div class="table-container" style="margin-top: 1.5rem;">
    <div class="table-header">
        <h2>Livres numeriques (<?php echo count($numeriques); ?>)</h2>
    </div>

    <?php if (empty($numeriques)): ?>
        <div class="empty-state">
            <p>&#128214;</p>
            <p>Aucun livre numerique propose.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Disponibilite en ligne</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($numeriques as $num): ?>
                    <?php
                        // Disponible si les periodes d'acces le permettent
                        $numDispo = (empty($num['datedebutacces']) || $num['datedebutacces'] <= date('Y-m-d'))
                                 && (empty($num['datefinacces']) || $num['datefinacces'] >= date('Y-m-d'));
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($num['titre']); ?></strong></td>
                        <td>
                            <?php if ($numDispo): ?>
                                <span class="badge badge-success">Disponible en ligne</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Indisponible</span>
                                <?php if (!empty($num['datefinacces']) && $num['datefinacces'] < date('Y-m-d')): ?>
                                    <span class="subtitle"> (retire le <?php echo date('d/m/Y', strtotime($num['datefinacces'])); ?>)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($numDispo && $statutAdhesion['valide']): ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="telecharger">
                                    <input type="hidden" name="Id_Livre" value="<?php echo $num['Id_Livre']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Telecharger</button>
                                </form>
                            <?php else: ?>
                                <span class="subtitle">Non accessible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Historique des telechargements -->
<?php if (!empty($telechargements)): ?>
    <div class="detail-container" style="margin-top: 1.5rem;">
        <div class="detail-header">
            <h2>Mes telechargements (<?php echo count($telechargements); ?>)</h2>
        </div>
        <div class="detail-body">
            <?php foreach ($telechargements as $t): ?>
                <div class="detail-row">
                    <span class="detail-label"><?php echo htmlspecialchars($t['titre']); ?></span>
                    <span class="detail-value">le <?php echo $t['datetelechargement'] ? date('d/m/Y', strtotime($t['datetelechargement'])) : '-'; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Nouvel emprunt : formulaire permettant a l'adherent d'emprunter un livre -->
<div class="form-container" style="margin-top: 1.5rem;">
    <h2>Nouvel emprunt</h2>

    <!-- Affichage des erreurs eventuelles -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$statutAdhesion['valide']): ?>
        <!-- Adhesion expiree : emprunt impossible -->
        <div class="alert alert-danger">
            <div>Votre adhesion est expiree. Rapprochez-vous de la mediatheque pour la renouveler avant d'emprunter ou de telecharger.</div>
        </div>
    <?php elseif (empty($disponibles)): ?>
        <!-- Aucun exemplaire n'est disponible -->
        <div class="empty-state">
            <p>&#128218;</p>
            <p>Aucun exemplaire disponible actuellement.</p>
        </div>
    <?php else: ?>
        <!-- Formulaire poste sur cette meme page -->
        <form method="POST">
            <!-- Champ cache : distingue ce formulaire des autres soumissions -->
            <input type="hidden" name="action" value="emprunter">

            <!-- Choix de l'exemplaire disponible -->
            <div class="form-group">
                <label for="Id_Exemplaire">Exemplaire disponible *</label>
                <select id="Id_Exemplaire" name="Id_Exemplaire" required>
                    <option value="">-- Selectionner un exemplaire --</option>
                    <?php foreach ($disponibles as $ex): ?>
                        <option value="<?php echo $ex['Id_Exemplaire']; ?>" <?php echo $Id_Exemplaire == $ex['Id_Exemplaire'] ? 'selected' : ''; ?>>
                            <!-- "Livre non associe" si l'exemplaire n'a pas de titre -->
                            <?php echo htmlspecialchars($ex['code_exemplaire'] ?? ('#' . $ex['Id_Exemplaire'])); ?> - <?php echo $ex['titre'] ? htmlspecialchars($ex['titre']) : 'Livre non associe'; ?> (<?php echo htmlspecialchars($ex['etat'] ?? '-'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date d'emprunt (le retour est enregistre lors de la restitution) -->
            <div class="form-group">
                <label for="dateemprrunt">Date d'emprunt *</label>
                <input type="date" id="dateemprrunt" name="dateemprrunt" value="<?php echo htmlspecialchars($dateemprrunt); ?>" required>
            </div>

            <!-- Bouton de validation -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Emprunter</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Historique des emprunts -->
<div class="table-container" style="margin-top: 1.5rem;">
    <div class="table-header">
        <h2>Mes emprunts (<?php echo count($emprunts); ?>)</h2>
    </div>

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
                    <th>Date emprunt</th>
                    <th>Statut / Retour</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprunts as $em): ?>
                    <tr>
                        <!-- "Livre non associe" si l'exemplaire n'a pas de titre -->
                        <td><strong><?php echo htmlspecialchars($em['titre'] ?? 'Livre non associe'); ?></strong></td>
                        <td><?php echo htmlspecialchars($em['code_exemplaire'] ?? ('#' . $em['Id_Exemplaire'])); ?></td>
                        <td><?php echo $em['dateemprrunt'] ? date('d/m/Y', strtotime($em['dateemprrunt'])) : '-'; ?></td>
                        <td>
                            <?php if (empty($em['dateretour'])): ?>
                                <span class="badge badge-success">En cours</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Rendu le <?php echo date('d/m/Y', strtotime($em['dateretour'])); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Retour d'un livre : enregistre la date de retour et libere l'exemplaire -->
                            <?php if (empty($em['dateretour'])): ?>
                                <form method="POST" class="inline-form" onsubmit="return confirm('Rendre ce livre a la mediatheque ?');">
                                    <input type="hidden" name="action" value="rendre">
                                    <input type="hidden" name="Id_Emprunt" value="<?php echo $em['Id_Emprunt']; ?>">
                                    <select name="etat" class="inline-select">
                                        <?php foreach (ETATS_EXEMPLAIRE as $e): ?>
                                            <option value="<?php echo htmlspecialchars($e); ?>"><?php echo htmlspecialchars($e); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-secondary btn-sm">Rendre</button>
                                </form>
                            <?php else: ?>
                                <span class="subtitle">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>