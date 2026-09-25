<?php
// ============================================================
// AJOUT D'UN ADHERENT (adherents/ajouter.php)
// Affiche le formulaire d'inscription puis insere le nouvel
// adherent (avec un parrain facultatif) dans la base.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Liste des adherents MAJEURS existants : servent a choisir un referent
// pour un adherent mineur. Un referent doit etre majeur (regle de gestion).
$adherents = $pdo->query("
    SELECT Id_Adherent, nomadh, prenomadh
    FROM Adherent
    WHERE datenaissance IS NOT NULL
      AND DATE_ADD(datenaissance, INTERVAL 18 YEAR) <= CURDATE()
    ORDER BY nomadh ASC, prenomadh ASC
")->fetchAll();

// Initialisation des variables du formulaire
$errors = [];
$nomadh = $prenomadh = $datenaissance = $adresseadh = $mailadh = $telephoneadh = $parrain = '';
$dateadhesion = date('Y-m-d');

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des champs (trim() retire les espaces autour)
    $nomadh = trim($_POST['nomadh'] ?? '');
    $prenomadh = trim($_POST['prenomadh'] ?? '');
    $datenaissance = trim($_POST['datenaissance'] ?? '');
    $adresseadh = trim($_POST['adresseadh'] ?? '');
    $mailadh = trim($_POST['mailadh'] ?? '');
    $telephoneadh = trim($_POST['telephoneadh'] ?? '');
    $dateadhesion = trim($_POST['dateadhesion'] ?? '');
    $parrain = intval($_POST['Id_Adherent_1'] ?? 0);   // id du referent (0 = aucun)

    // Validations : nom, prenom et email obligatoires
    if (empty($nomadh)) $errors[] = "Le nom est obligatoire.";
    if (empty($prenomadh)) $errors[] = "Le prenom est obligatoire.";
    if (empty($mailadh)) $errors[] = "L'email est obligatoire.";
    if (empty($dateadhesion)) $errors[] = "La date d'adhesion est obligatoire.";

    // -- Regle de gestion : adherent mineur -> referent majeur obligatoire
    if (est_mineur($datenaissance) && $parrain <= 0) {
        $errors[] = "Un adherent mineur doit etre rattache a un adulte referent.";
    }
    if ($parrain > 0) {
        // Le referent choisi doit etre majeur
        $stmtP = $pdo->prepare("SELECT datenaissance FROM Adherent WHERE Id_Adherent = ?");
        $stmtP->execute([$parrain]);
        $dateRef = $stmtP->fetchColumn();
        if (!$dateRef || !est_majeur($dateRef)) {
            $errors[] = "L'adulte referent selectionne doit etre majeur.";
        }
    }

    // Sans erreur, insertion en base
    if (empty($errors)) {
        // Transaction : creation de l'adherent puis de son code unique
        // (rang d'inscription) et de sa carte de pret.
        $pdo->beginTransaction();

        // Requete preparee (anti-injection SQL)
        $stmt = $pdo->prepare("INSERT INTO Adherent (code_adherent, nomadh, prenomadh, datenaissance, adresseadh, mailadh, telephoneadh, dateadhesion, Id_Adherent_1) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // Le code provisoire sera remplace juste apres par le code definitif
        $stmt->execute([null, $nomadh, $prenomadh, $datenaissance ?: null, $adresseadh ?: null, $mailadh, $telephoneadh ?: null, $dateadhesion, $parrain ?: null]);

        // Code definitif : "AD" + rang d'inscription (identifiant sur 4 chiffres)
        $idAdh = $pdo->lastInsertId();
        $code = 'AD' . str_pad((string)$idAdh, 4, '0', STR_PAD_LEFT);
        $stmtCode = $pdo->prepare("UPDATE Adherent SET code_adherent = ? WHERE Id_Adherent = ?");
        $stmtCode->execute([$code, $idAdh]);

        // Carte de pret emise a la date d'adhesion (valable 1 an)
        $stmtCarte = $pdo->prepare("INSERT INTO Carte (dateemission, Id_Adherent) VALUES (?, ?)");
        $stmtCarte->execute([$dateadhesion, $idAdh]);

        $pdo->commit();

        // Redirection vers la liste avec message de succes
        header("Location: index.php?success=Adherent ajoute avec succes");
        exit;
    }
}

// Surbrillance du menu "Adherents"
$currentPage = 'adherents';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Ajouter un adherent</h1>
<p class="subtitle">Inscrire un nouvel adherent</p>

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

        <!-- Ligne 0 : code (genere automatiquement) + date d'adhesion -->
        <div class="form-row">
            <div class="form-group">
                <label>Code adherent</label>
                <!-- Le code est attribue automatiquement (rang d'inscription) -->
                <input type="text" value="Genere automatiquement" disabled>
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

        <!-- Ligne 3 : date de naissance et referent (adulte) pour mineurs -->
        <div class="form-row">
            <div class="form-group">
                <label for="datenaissance">Date de naissance</label>
                <input type="date" id="datenaissance" name="datenaissance" value="<?php echo htmlspecialchars($datenaissance); ?>">
            </div>
            <div class="form-group">
                <label for="Id_Adherent_1">Referent (adulte, pour un mineur)</label>
                <!-- Liste restreinte aux adherents MAJEURS uniquement -->
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
            <button type="submit" class="btn btn-primary">Ajouter l'adherent</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>