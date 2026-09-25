<?php
// ============================================================
// AJOUT D'UN LIVRE (livres/ajouter.php)
// Affiche le formulaire et, a la soumission (POST),
// valide puis insere le livre dans la base.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Recupere toutes les classifications Dewey pour la liste deroulante
$categories = $pdo->query("SELECT * FROM CODEDEWEY ORDER BY libelledewey ASC")->fetchAll();

// Initialisation : tableau des erreurs et valeurs du formulaire
$errors = [];
$titre = $isbn = $datesortie = $dateachat = $codewey = '';
$type_support = 'papier';

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des champs (trim() supprime les espaces superflus)
    $titre = trim($_POST['titre'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $datesortie = trim($_POST['datesortie'] ?? '');
    $dateachat = trim($_POST['dateachat'] ?? '');
    $codewey = trim($_POST['codewey'] ?? '');
    $type_support = trim($_POST['type_support'] ?? 'papier');

    // --- Validations : les champs marques * sont obligatoires ---
    if (empty($titre)) $errors[] = "Le titre est obligatoire.";
    if (empty($isbn)) $errors[] = "L'ISBN est obligatoire.";
    if (empty($codewey)) $errors[] = "La classification Dewey est obligatoire.";
    // -- Regle de gestion : le type de support doit avoir une valeur autorisee
    if (!in_array($type_support, ['papier', 'numerique', 'les deux'], true)) {
        $errors[] = "Le type de support est invalide.";
    }

    // Si aucune erreur, on enregistre en base
    if (empty($errors)) {
        // Requete preparee (recommendee contre les injections SQL)
        $stmt = $pdo->prepare("INSERT INTO Livre (titre, type_support, datesortie, isbn, dateachat, codewey) VALUES (?, ?, ?, ?, ?, ?)");
        // Les dates vides sont converties en NULL avec ?: null
        $stmt->execute([$titre, $type_support, $datesortie ?: null, $isbn, $dateachat ?: null, $codewey]);

        // Redirection vers la liste avec un message de succes
        header("Location: index.php?success=Livre ajoute avec succes");
        exit; // on stoppe le script apres la redirection
    }
}

// Active le menu "Livres" et affiche l'en-tete
$currentPage = 'livres';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Ajouter un livre</h1>
<p class="subtitle">Ajouter un nouveau livre au catalogue</p>

<div class="form-container">
    <!-- formulaire envoye en POST vers cette meme page -->
    <form method="POST">
        <!-- Affichage des erreurs eventuelles -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Champ titre -->
        <div class="form-group">
            <label for="titre">Titre *</label>
            <!-- value conserve la saisie precedente si une erreur survient -->
            <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($titre); ?>" required>
        </div>

        <!-- Champ ISBN -->
        <div class="form-group">
            <label for="isbn">ISBN *</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>" required>
        </div>

        <!-- Liste deroulante des classifications Dewey -->
        <div class="form-group">
            <label for="codewey">Classification Dewey *</label>
            <select id="codewey" name="codewey" required>
                <option value="">-- Selectionner une classification --</option>
                <!-- Boucle sur les categories pour remplir les options -->
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['codewey']); ?>" <?php echo $codewey === $cat['codewey'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['codewey'] . ' - ' . $cat['libelledewey']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Type de support (papier / numerique / les deux) -->
        <div class="form-group">
            <label for="type_support">Type de support *</label>
            <select id="type_support" name="type_support" required>
                <option value="papier" <?php echo $type_support === 'papier' ? 'selected' : ''; ?>>Papier</option>
                <option value="numerique" <?php echo $type_support === 'numerique' ? 'selected' : ''; ?>>Numerique</option>
                <option value="les deux" <?php echo $type_support === 'les deux' ? 'selected' : ''; ?>>Papier et numerique</option>
            </select>
        </div>

        <!-- Dates (optionnelles) sur deux colonnes -->
        <div class="form-row">
            <div class="form-group">
                <label for="datesortie">Date de sortie</label>
                <input type="date" id="datesortie" name="datesortie" value="<?php echo htmlspecialchars($datesortie); ?>">
            </div>
            <div class="form-group">
                <label for="dateachat">Date d'achat</label>
                <input type="date" id="dateachat" name="dateachat" value="<?php echo htmlspecialchars($dateachat); ?>">
            </div>
        </div>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ajouter le livre</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>