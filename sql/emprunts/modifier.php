<?php
// ============================================================
// MODIFICATION D'UN EMPRUNT (emprunts/modifier.php)
// Modifie l'adherent et/ou les dates d'un emprunt existant.
// L'exemplaire lie n'est pas modifiable ici (affichage en lecture seule).
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'emprunt dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge l'emprunt avec ses informations (pour pre-remplir et afficher l'exemplaire)
$stmt = $pdo->prepare("
    SELECT e.*, a.nomadh, a.prenomadh, ex.Id_Exemplaire, ex.etat, l.titre
    FROM Emprunt e
    JOIN Adherent a ON e.Id_Adherent = a.Id_Adherent
    JOIN Emprunter em ON e.Id_Emprunt = em.Id_Emprunt
    JOIN Exemplaire ex ON em.Id_Exemplaire = ex.Id_Exemplaire
    JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE e.Id_Emprunt = ?
");
$stmt->execute([$id]);
$emprunt = $stmt->fetch();

if (!$emprunt) { header("Location: index.php"); exit; }

// Liste des adherents pour la liste deroulante
$adherents = $pdo->query("SELECT Id_Adherent, nomadh, prenomadh FROM Adherent ORDER BY nomadh ASC, prenomadh ASC")->fetchAll();

// Initialisation des valeurs actuelles de l'emprunt
$errors = [];
$Id_Adherent = $emprunt['Id_Adherent'];
// Format aaaa-mm-jj requis par les champs <input type="date">
$dateemprrunt = date('Y-m-d', strtotime($emprunt['dateemprrunt']));
$dateretour = $emprunt['dateretour'] ? date('Y-m-d', strtotime($emprunt['dateretour'])) : '';

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des nouvelles valeurs
    $Id_Adherent = intval($_POST['Id_Adherent'] ?? 0);
    $dateemprrunt = trim($_POST['dateemprrunt'] ?? '');
    $dateretour = trim($_POST['dateretour'] ?? '');

    // Validations
    if ($Id_Adherent <= 0) $errors[] = "Veuillez selectionner un adherent.";
    if (empty($dateemprrunt)) $errors[] = "La date d'emprunt est obligatoire.";
    // La date de retour reste optionnelle : elle est renseignee lors du retour

    // Sans erreur, mise a jour de l'emprunt
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE Emprunt SET dateemprrunt=?, dateretour=?, Id_Adherent=? WHERE Id_Emprunt=?");
        $stmt->execute([$dateemprrunt, $dateretour, $Id_Adherent, $id]);

        // Redirection vers la fiche avec message de succes
        header("Location: consulter.php?id=$id&success=Emprunt modifie avec succes");
        exit;
    }
}

// Surbrillance du menu "Emprunts"
$currentPage = 'emprunts';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Modifier l'emprunt</h1>
<p class="subtitle">Emprunt #<?php echo $id; ?></p>

<div class="form-container">
    <form method="POST">
        <!-- Affichage des erreurs eventuelles -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Choix de l'adherent (pre-selection de l'adherent actuel) -->
        <div class="form-group">
            <label for="Id_Adherent">Adherent *</label>
            <select id="Id_Adherent" name="Id_Adherent" required>
                <option value="">-- Selectionner un adherent --</option>
                <?php foreach ($adherents as $adm): ?>
                    <option value="<?php echo $adm['Id_Adherent']; ?>" <?php echo $Id_Adherent == $adm['Id_Adherent'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($adm['prenomadh'] . ' ' . $adm['nomadh']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Exemplaire lie : affiche mais non modifiable (disabled) -->
        <div class="form-group">
            <label>Exemplaire</label>
            <input type="text" value="<?php echo htmlspecialchars($emprunt['code_exemplaire'] ?? ('#' . $emprunt['Id_Exemplaire'])); ?> - <?php echo htmlspecialchars($emprunt['titre']); ?>" disabled>
        </div>

        <!-- Dates (la date de retour se remplit lors du retour effectif) -->
        <div class="form-row">
            <div class="form-group">
                <label for="dateemprrunt">Date d'emprunt *</label>
                <input type="date" id="dateemprrunt" name="dateemprrunt" value="<?php echo htmlspecialchars($dateemprrunt); ?>" required>
            </div>
            <div class="form-group">
                <label for="dateretour">Date de retour</label>
                <input type="date" id="dateretour" name="dateretour" value="<?php echo htmlspecialchars($dateretour); ?>">
            </div>
        </div>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="consulter.php?id=<?php echo $id; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>