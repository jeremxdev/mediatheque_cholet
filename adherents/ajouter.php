<?php
// ============================================================
// AJOUT D'UN ADHERENT (adherents/ajouter.php)
// Affiche le formulaire d'inscription puis insere le nouvel
// adherent (avec un parrain facultatif) dans la base.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Liste des adherents existants : servent a choisir un parrain (facultatif)
$adherents = $pdo->query("SELECT Id_Adherent, nomadh, prenomadh FROM Adherent ORDER BY nomadh ASC, prenomadh ASC")->fetchAll();

// Initialisation des variables du formulaire
$errors = [];
$nomadh = $prenomadh = $datenaissance = $adresseadh = $mailadh = $telephoneadh = $parrain = '';

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des champs (trim() retire les espaces autour)
    $nomadh = trim($_POST['nomadh'] ?? '');
    $prenomadh = trim($_POST['prenomadh'] ?? '');
    $datenaissance = trim($_POST['datenaissance'] ?? '');
    $adresseadh = trim($_POST['adresseadh'] ?? '');
    $mailadh = trim($_POST['mailadh'] ?? '');
    $telephoneadh = trim($_POST['telephoneadh'] ?? '');
    $parrain = intval($_POST['Id_Adherent_1'] ?? 0);   // id du parrain (0 = aucun)

    // Validations : nom, prenom et email obligatoires
    if (empty($nomadh)) $errors[] = "Le nom est obligatoire.";
    if (empty($prenomadh)) $errors[] = "Le prenom est obligatoire.";
    if (empty($mailadh)) $errors[] = "L'email est obligatoire.";

    // Sans erreur, insertion en base
    if (empty($errors)) {
        // Requete preparee (anti-injection SQL)
        $stmt = $pdo->prepare("INSERT INTO Adherent (nomadh, prenomadh, datenaissance, adresseadh, mailadh, telephoneadh, Id_Adherent_1) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // Les champs vides sont enregistres comme NULL (? : null)
        $stmt->execute([$nomadh, $prenomadh, $datenaissance ?: null, $adresseadh ?: null, $mailadh, $telephoneadh ?: null, $parrain ?: null]);

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

        <!-- Ligne 3 : date de naissance et choix du parrain -->
        <div class="form-row">
            <div class="form-group">
                <label for="datenaissance">Date de naissance</label>
                <input type="date" id="datenaissance" name="datenaissance" value="<?php echo htmlspecialchars($datenaissance); ?>">
            </div>
            <div class="form-group">
                <label for="Id_Adherent_1">Parrain (adherent existant)</label>
                <!-- Liste deroulante des parrains possibles -->
                <select id="Id_Adherent_1" name="Id_Adherent_1">
                    <option value="">-- Aucun parrain --</option>
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