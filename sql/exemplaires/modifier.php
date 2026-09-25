<?php
// ============================================================
// MODIFICATION D'UN EXEMPLAIRE (exemplaires/modifier.php)
// Modifie l'etat de l'exemplaire et le livre associe (lien Posseder).
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'exemplaire dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge l'exemplaire existant
$stmt = $pdo->prepare("SELECT * FROM Exemplaire WHERE Id_Exemplaire = ?");
$stmt->execute([$id]);
$exemplaire = $stmt->fetch();

if (!$exemplaire) { header("Location: index.php"); exit; }

// Liste des livres pour la liste deroulante
$livres = $pdo->query("SELECT Id_Livre, titre FROM Livre ORDER BY titre ASC")->fetchAll();

// Recupere le livre actuellement associe (via Posseder)
$stmtP = $pdo->prepare("SELECT Id_Livre FROM Posseder WHERE Id_Exemplaire = ?");
$stmtP->execute([$id]);
$Id_Livre = $stmtP->fetchColumn();

// Initialisation
$errors = [];
$etat = $exemplaire['etat'];

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etat = trim($_POST['etat'] ?? '');
    $Id_Livre = intval($_POST['Id_Livre'] ?? 0);

    // Validations
    // -- Regle de gestion : l'etat doit appartenir aux valeurs autorisees
    if (!in_array($etat, ETATS_EXEMPLAIRE, true)) $errors[] = "L'etat est invalide.";
    if ($Id_Livre <= 0) $errors[] = "Veuillez selectionner un livre.";

    if (empty($errors)) {
        // Transaction : deux mises a jour simultanees
        $pdo->beginTransaction();

        // 1. Mise a jour de l'etat de l'exemplaire
        $stmt = $pdo->prepare("UPDATE Exemplaire SET etat=? WHERE Id_Exemplaire=?");
        $stmt->execute([$etat, $id]);

        // 2. Re-calcul du code si le livre associe change (Dewey + rang)
        $stmtC = $pdo->prepare("SELECT codewey FROM Livre WHERE Id_Livre = ?");
        $stmtC->execute([$Id_Livre]);
        $codewey = $stmtC->fetchColumn();
        $code = ($codewey ?: 'XX') . '-' . $id;
        $stmtCode = $pdo->prepare("UPDATE Exemplaire SET code_exemplaire = ? WHERE Id_Exemplaire = ?");
        $stmtCode->execute([$code, $id]);

        // 3. Re-liaison du livre (Posseder)
        $stmtP = $pdo->prepare("UPDATE Posseder SET Id_Livre=? WHERE Id_Exemplaire=?");
        $stmtP->execute([$Id_Livre, $id]);

        // Validation
        $pdo->commit();

        // Redirection vers la fiche avec message de succes
        header("Location: consulter.php?id=$id&success=Exemplaire modifie avec succes");
        exit;
    }
}

// Surbrillance du menu "Exemplaires"
$currentPage = 'exemplaires';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Modifier l'exemplaire</h1>
<p class="subtitle">Exemplaire #<?php echo $id; ?></p>

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

        <!-- Liste deroulante du livre (selection actuelle predefinie) -->
        <div class="form-group">
            <label for="Id_Livre">Livre *</label>
            <select id="Id_Livre" name="Id_Livre" required>
                <option value="">-- Selectionner un livre --</option>
                <?php foreach ($livres as $livre): ?>
                    <option value="<?php echo $livre['Id_Livre']; ?>" <?php echo $Id_Livre == $livre['Id_Livre'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($livre['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Code unique de l'exemplaire (Dewey + rang) : non modifiable -->
        <div class="form-group">
            <label>Code exemplaire</label>
            <input type="text" value="<?php echo htmlspecialchars($exemplaire['code_exemplaire'] ?? ''); ?>" disabled>
        </div>

        <!-- Etat de l'exemplaire (valeurs normalisees) -->
        <div class="form-group">
            <label for="etat">Etat *</label>
            <select id="etat" name="etat" required>
                <?php foreach (ETATS_EXEMPLAIRE as $e): ?>
                    <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $etat === $e ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($e); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="consulter.php?id=<?php echo $id; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>