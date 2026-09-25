<?php
// ============================================================
// AJOUT D'UN ACCES NUMERIQUE (numeriques/ajouter.php)
// Enregistre la fenetre d'acces en ligne d'un livre supportant
// le format numerique (table livre_num).
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Livres pouvant accueillir un acces numerique :
// - support numerique ou mixte ;
// - pas encore de ligne dans livre_num.
$livres = $pdo->query("
    SELECT l.Id_Livre, l.titre
    FROM Livre l
    WHERE l.type_support IN ('numerique', 'les deux')
      AND NOT EXISTS (SELECT 1 FROM livre_num ln WHERE ln.Id_Livre = l.Id_Livre)
    ORDER BY l.titre ASC
")->fetchAll();

// Initialisation
$errors = [];
$Id_Livre = 0;
$datedebutacces = date('Y-m-d');
$datefinacces = '';

// Pre-selection du livre via le parametre ?id=
if (isset($_GET['id'])) {
    $Id_Livre = intval($_GET['id']);
}

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Id_Livre = intval($_POST['Id_Livre'] ?? 0);
    $datedebutacces = trim($_POST['datedebutacces'] ?? '');
    $datefinacces = trim($_POST['datefinacces'] ?? '');

    // Validations
    if ($Id_Livre <= 0) $errors[] = "Veuillez selectionner un livre numerique.";
    if (empty($datedebutacces)) $errors[] = "La date de debut d'acces est obligatoire.";
    if ($datefinacces && $datefinacces < $datedebutacces) {
        $errors[] = "La date de fin doit etre posterieure a la date de debut.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO livre_num (Id_Livre, datedebutacces, datefinacces) VALUES (?, ?, ?)");
        $stmt->execute([$Id_Livre, $datedebutacces, $datefinacces ?: null]);

        // Redirection vers la fiche avec message de succes
        header("Location: consulter.php?id=$Id_Livre&success=Acces numerique ajoute avec succes");
        exit;
    }
}

// Surbrillance du menu "Numeriques"
$currentPage = 'numeriques';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Ajouter un acces numerique</h1>
<p class="subtitle">Definir la fenetre d'acces en ligne d'un livre</p>

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

        <!-- Choix du livre supportant le numerique -->
        <div class="form-group">
            <label for="Id_Livre">Livre numerique *</label>
            <select id="Id_Livre" name="Id_Livre" required>
                <option value="">-- Selectionner un livre --</option>
                <?php foreach ($livres as $livre): ?>
                    <option value="<?php echo $livre['Id_Livre']; ?>" <?php echo $Id_Livre == $livre['Id_Livre'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($livre['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Fenetre d'acces en ligne -->
        <div class="form-row">
            <div class="form-group">
                <label for="datedebutacces">Date de debut d'acces *</label>
                <input type="date" id="datedebutacces" name="datedebutacces" value="<?php echo htmlspecialchars($datedebutacces); ?>" required>
            </div>
            <div class="form-group">
                <label for="datefinacces">Date de fin d'acces</label>
                <!-- Laisse vide si l'acces est illimite -->
                <input type="date" id="datefinacces" name="datefinacces" value="<?php echo htmlspecialchars($datefinacces); ?>">
            </div>
        </div>
        <p class="subtitle">Laissez la date de fin vide si l'acces au livre reste illimite.</p>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ajouter l'acces</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>