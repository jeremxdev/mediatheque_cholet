<?php
// ============================================================
// MODIFICATION D'UN ADHERENT (adherents/modifier.php)
// Affiche le formulaire pre-rempli puis met a jour l'adherent.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'adherent dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge l'adherent existant
$stmt = $pdo->prepare("SELECT * FROM Adherent WHERE Id_Adherent = ?");
$stmt->execute([$id]);
$adherent = $stmt->fetch();

if (!$adherent) { header("Location: index.php"); exit; }

// Liste des adherents MAJEURS pouvant etre choisis comme referent
// (regle de gestion : un referent doit etre majeur).
// On exclut l'adherent courant pour eviter qu'il se refere lui-meme.
$adherents = $pdo->query("
    SELECT Id_Adherent, nomadh, prenomadh
    FROM Adherent
    WHERE Id_Adherent != $id
      AND datenaissance IS NOT NULL
      AND DATE_ADD(datenaissance, INTERVAL 18 YEAR) <= CURDATE()
    ORDER BY nomadh ASC, prenomadh ASC
")->fetchAll();

// Initialisation des valeurs avec les donnees actuelles (pre-remplissage)
$errors = [];
$nomadh = $adherent['nomadh'];
$prenomadh = $adherent['prenomadh'];
$datenaissance = $adherent['datenaissance'] ? date('Y-m-d', strtotime($adherent['datenaissance'])) : '';
$adresseadh = $adherent['adresseadh'];
$mailadh = $adherent['mailadh'];
$telephoneadh = $adherent['telephoneadh'];
$dateadhesion = $adherent['dateadhesion'] ? date('Y-m-d', strtotime($adherent['dateadhesion'])) : '';
$parrain = $adherent['Id_Adherent_1'];

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des nouvelles valeurs
    $nomadh = trim($_POST['nomadh'] ?? '');
    $prenomadh = trim($_POST['prenomadh'] ?? '');
    $datenaissance = trim($_POST['datenaissance'] ?? '');
    $adresseadh = trim($_POST['adresseadh'] ?? '');
    $mailadh = trim($_POST['mailadh'] ?? '');
    $telephoneadh = trim($_POST['telephoneadh'] ?? '');
    $dateadhesion = trim($_POST['dateadhesion'] ?? '');
    $parrain = intval($_POST['Id_Adherent_1'] ?? 0);

    // Validations
    if (empty($nomadh)) $errors[] = "Le nom est obligatoire.";
    if (empty($prenomadh)) $errors[] = "Le prenom est obligatoire.";
    if (empty($mailadh)) $errors[] = "L'email est obligatoire.";
    if (empty($dateadhesion)) $errors[] = "La date d'adhesion est obligatoire.";

    // -- Regle de gestion : adherent mineur -> referent majeur obligatoire
    if (est_mineur($datenaissance) && $parrain <= 0) {
        $errors[] = "Un adherent mineur doit etre rattache a un adulte referent.";
    }
    if ($parrain > 0) {
        // Le referent doit etre majeur (la liste ne propose deja que des majeurs)
        $stmtP = $pdo->prepare("SELECT datenaissance FROM Adherent WHERE Id_Adherent = ?");
        $stmtP->execute([$parrain]);
        $dateRef = $stmtP->fetchColumn();
        if (!$dateRef || !est_majeur($dateRef)) {
            $errors[] = "L'adulte referent selectionne doit etre majeur.";
        }
    }

    // Sans erreur, mise a jour de l'enregistrement
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE Adherent SET nomadh=?, prenomadh=?, datenaissance=?, adresseadh=?, mailadh=?, telephoneadh=?, dateadhesion=?, Id_Adherent_1=? WHERE Id_Adherent=?");
        $stmt->execute([$nomadh, $prenomadh, $datenaissance ?: null, $adresseadh ?: null, $mailadh, $telephoneadh ?: null, $dateadhesion, $parrain ?: null, $id]);

        // Redirection vers la fiche avec message de succes
        header("Location: consulter.php?id=$id&success=Adherent modifie avec succes");
        exit;
    }
}

// Surbrillance du menu "Adherents"
$currentPage = 'adherents';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Modifier l'adherent</h1>
<p class="subtitle"><?php echo htmlspecialchars($adherent['prenomadh'] . ' ' . $adherent['nomadh']); ?></p>

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

        <!-- Code (genere automatiquement, non modifiable) + adhesion -->
        <div class="form-row">
            <div class="form-group">
                <label>Code adherent</label>
                <input type="text" value="<?php echo htmlspecialchars($adherent['code_adherent'] ?? ''); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="dateadhesion">Date d'adhesion *</label>
                <input type="date" id="dateadhesion" name="dateadhesion" value="<?php echo htmlspecialchars($dateadhesion); ?>" required>
            </div>
        </div>

        <!-- Ligne 1 : nom et prenom -->
        <div class="form-row">
            <div class="form-group">
                <label for="nomadh">Nom *</label>
                <input type="text" id="nomadh" name="nomadh" value="<?php echo htmlspecialchars($nomadh); ?>" required>
            </div>
            <div class="form-group">
                <label for="prenomadh">Prenom *</label>
                <input type="text" id="prenomadh" name="prenomadh" value="<?php echo htmlspecialchars($prenomadh); ?>" required>
            </div>
        </div>

        <!-- Ligne 2 : email et telephone -->
        <div class="form-row">
            <div class="form-group">
                <label for="mailadh">Email *</label>
                <input type="email" id="mailadh" name="mailadh" value="<?php echo htmlspecialchars($mailadh); ?>" required>
            </div>
            <div class="form-group">
                <label for="telephoneadh">Telephone</label>
                <input type="tel" id="telephoneadh" name="telephoneadh" value="<?php echo htmlspecialchars($telephoneadh); ?>">
            </div>
        </div>

        <!-- Ligne 3 : date de naissance et referent (adulte, pour mineurs) -->
        <div class="form-row">
            <div class="form-group">
                <label for="datenaissance">Date de naissance</label>
                <input type="date" id="datenaissance" name="datenaissance" value="<?php echo htmlspecialchars($datenaissance); ?>">
            </div>
            <div class="form-group">
                <label for="Id_Adherent_1">Referent (adulte, pour un mineur)</label>
                <select id="Id_Adherent_1" name="Id_Adherent_1">
                    <option value="">-- Aucun referent --</option>
                    <?php foreach ($adherents as $a): ?>
                        <option value="<?php echo $a['Id_Adherent']; ?>" <?php echo $parrain == $a['Id_Adherent'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($a['nomadh'] . ' ' . $a['prenomadh']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Adresse complete -->
        <div class="form-group">
            <label for="adresseadh">Adresse</label>
            <input type="text" id="adresseadh" name="adresseadh" value="<?php echo htmlspecialchars($adresseadh); ?>">
        </div>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="consulter.php?id=<?php echo $id; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>