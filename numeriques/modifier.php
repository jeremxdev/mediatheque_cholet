<?php
// ============================================================
// MODIFICATION D'UN ACCES NUMERIQUE (numeriques/modifier.php)
// Modifie la fenetre d'acces en ligne d'un livre numerique.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant du livre dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge le livre et son acces numerique
$stmt = $pdo->prepare("
    SELECT l.*, ln.datedebutacces, ln.datefinacces
    FROM Livre l
    JOIN livre_num ln ON ln.Id_Livre = l.Id_Livre
    WHERE l.Id_Livre = ?
");
$stmt->execute([$id]);
$livre = $stmt->fetch();

// Si absent, retour a la liste
if (!$livre) { header("Location: index.php"); exit; }

// Initialisation des valeurs actuelles
$errors = [];
$datedebutacces = $livre['datedebutacces'] ? date('Y-m-d', strtotime($livre['datedebutacces'])) : '';
$datefinacces = $livre['datefinacces'] ? date('Y-m-d', strtotime($livre['datefinacces'])) : '';

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datedebutacces = trim($_POST['datedebutacces'] ?? '');
    $datefinacces = trim($_POST['datefinacces'] ?? '');

    // Validations
    if (empty($datedebutacces)) $errors[] = "La date de debut d'acces est obligatoire.";
    if ($datefinacces && $datefinacces < $datedebutacces) {
        $errors[] = "La date de fin doit etre posterieure a la date de debut.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE livre_num SET datedebutacces = ?, datefinacces = ? WHERE Id_Livre = ?");
        $stmt->execute([$datedebutacces, $datefinacces ?: null, $id]);

        // Redirection vers la fiche avec message de succes
        header("Location: consulter.php?id=$id&success=Acces numerique modifie avec succes");
        exit;
    }
}

// Surbrillance du menu "Numeriques"
$currentPage = 'numeriques';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Modifier l'acces numerique</h1>
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

        <!-- Fenetre d'acces en ligne -->
        <div class="form-row">
            <div class="form-group">
                <label for="datedebutacces">Date de debut d'acces *</label>
                <input type="date" id="datedebutacces" name="datedebutacces" value="<?php echo htmlspecialchars($datedebutacces); ?>" required>
            </div>
            <div class="form-group">
                <label for="datefinacces">Date de fin d'acces</label>
                <input type="date" id="datefinacces" name="datefinacces" value="<?php echo htmlspecialchars($datefinacces); ?>">
            </div>
        </div>
        <p class="subtitle">Laissez la date de fin vide pour un acces illimite.</p>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="consulter.php?id=<?php echo $id; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>