<?php
// ============================================================
// MODIFICATION D'UN LIVRE (livres/modifier.php)
// Affiche le formulaire pre-rempli avec les donnees du livre,
// puis met a jour la base a la soumission du formulaire.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Recupere l'identifiant du livre dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge le livre existant (pre-remplissage du formulaire)
$stmt = $pdo->prepare("SELECT * FROM Livre WHERE Id_Livre = ?");
$stmt->execute([$id]);
$livre = $stmt->fetch();

// Si le livre n'existe pas, retour a la liste
if (!$livre) { header("Location: index.php"); exit; }

// Classifications Dewey disponibles pour la liste deroulante
$categories = $pdo->query("SELECT * FROM CODEDEWEY ORDER BY libelledewey ASC")->fetchAll();

// Initialisation des valeurs avec les donnees actuelles du livre
// Les dates sont reformatees en aaaa-mm-jj (format attendu par les <input type="date">)
$errors = [];
$titre = $livre['titre'];
$isbn = $livre['isbn'];
$datesortie = $livre['datesortie'] ? date('Y-m-d', strtotime($livre['datesortie'])) : '';
$dateachat = $livre['dateachat'] ? date('Y-m-d', strtotime($livre['dateachat'])) : '';
$codewey = $livre['codewey'];
$type_support = $livre['type_support'] ?: 'papier';

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des nouvelles valeurs
    $titre = trim($_POST['titre'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $datesortie = trim($_POST['datesortie'] ?? '');
    $dateachat = trim($_POST['dateachat'] ?? '');
    $codewey = trim($_POST['codewey'] ?? '');
    $type_support = trim($_POST['type_support'] ?? 'papier');

    // Validations des champs obligatoires
    if (empty($titre)) $errors[] = "Le titre est obligatoire.";
    if (empty($isbn)) $errors[] = "L'ISBN est obligatoire.";
    if (empty($codewey)) $errors[] = "La classification Dewey est obligatoire.";
    if (!in_array($type_support, ['papier', 'numerique', 'les deux'], true)) {
        $errors[] = "Le type de support est invalide.";
    }

    // Si aucune erreur, mise a jour de l'enregistrement
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE Livre SET titre=?, type_support=?, datesortie=?, isbn=?, dateachat=?, codewey=? WHERE Id_Livre=?");
        $stmt->execute([$titre, $type_support, $datesortie ?: null, $isbn, $dateachat ?: null, $codewey, $id]);

        // Redirection vers la fiche du livre avec message de succes
        header("Location: consulter.php?id=$id&success=Livre modifie avec succes");
        exit;
    }
}

// Active le menu "Livres" et affiche l'en-tete
$currentPage = 'livres';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Modifier le livre</h1>
<p class="subtitle"><?php echo htmlspecialchars($livre['titre']); ?></p>

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

        <!-- Champ titre (pre-rempli) -->
        <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($titre); ?>" required>
        </div>

        <!-- Champ ISBN (pre-rempli) -->
        <div class="form-group">
            <label for="isbn">ISBN *</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>" required>
        </div>

        <!-- Liste deroulante des classifications (selection actuelle predefinie) -->
        <div class="form-group">
            <label for="codewey">Classification Dewey *</label>
            <select id="codewey" name="codewey" required>
                <option value="">-- Selectionner une classification --</option>
                <?php foreach ($categories as $cat): ?>
                    <!-- selected si c'est la valeur actuelle du livre -->
                    <option value="<?php echo htmlspecialchars($cat['codewey']); ?>" <?php echo $codewey === $cat['codewey'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['codewey'] . ' - ' . $cat['libelledewey']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Type de support -->
        <div class="form-group">
            <label for="type_support">Type de support *</label>
            <select id="type_support" name="type_support" required>
                <option value="papier" <?php echo $type_support === 'papier' ? 'selected' : ''; ?>>Papier</option>
                <option value="numerique" <?php echo $type_support === 'numerique' ? 'selected' : ''; ?>>Numerique</option>
                <option value="les deux" <?php echo $type_support === 'les deux' ? 'selected' : ''; ?>>Papier et numerique</option>
            </select>
        </div>

        <!-- Dates (optionnelles) -->
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
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="consulter.php?id=<?php echo $id; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>