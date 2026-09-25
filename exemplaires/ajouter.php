<?php
// ============================================================
// AJOUT D'UN EXEMPLAIRE (exemplaires/ajouter.php)
// Cree un exemplaire (avec un etat) et l'associe a un livre.
// Deux insertions sont effectuees dans une transaction :
//   1. une ligne dans la table Exemplaire
//   2. une ligne dans la table Posseder (lien livre <-> exemplaire)
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Liste des livres disponibles pour l'association
$livres = $pdo->query("SELECT Id_Livre, titre FROM Livre ORDER BY titre ASC")->fetchAll();

// Initialisation des variables
$errors = [];
$etat = '';
$Id_Livre = 0;

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etat = trim($_POST['etat'] ?? '');
    $Id_Livre = intval($_POST['Id_Livre'] ?? 0);

    // Validations
    if (empty($etat)) $errors[] = "L'etat est obligatoire.";
    if ($Id_Livre <= 0) $errors[] = "Veuillez selectionner un livre.";

    if (empty($errors)) {
        // Debut de transaction : les deux insertions seront validees ensemble
        $pdo->beginTransaction();

        // 1. Creation de l'exemplaire (etat)
        $stmt = $pdo->prepare("INSERT INTO Exemplaire (etat) VALUES (?)");
        $stmt->execute([$etat]);
        // lastInsertId() recupere l'identifiant auto-genere de l'exemplaire
        $idEx = $pdo->lastInsertId();

        // 2. Creation du lien Posseder (id livre + id exemplaire)
        $stmt2 = $pdo->prepare("INSERT INTO Posseder (Id_Livre, Id_Exemplaire) VALUES (?, ?)");
        $stmt2->execute([$Id_Livre, $idEx]);

        // Validation des deux insertions
        $pdo->commit();

        // Redirection vers la liste avec message de succes
        header("Location: index.php?success=Exemplaire ajoute avec succes");
        exit;
    }
}

// Surbrillance du menu "Exemplaires"
$currentPage = 'exemplaires';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Ajouter un exemplaire</h1>
<p class="subtitle">Nouvel exemplaire de livre</p>

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

        <!-- Liste deroulante du livre a associer -->
        <div class="form-group">
            <label for="Id_Livre">Livre *</label>
            <select id="Id_Livre" name="Id_Livre" required>
                <option value="">-- Selectionner un livre --</option>
                <?php foreach ($livres as $livre): ?>
                    <!-- selected si c'est la valeur choisie au formulaire -->
                    <option value="<?php echo $livre['Id_Livre']; ?>" <?php echo $Id_Livre == $livre['Id_Livre'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($livre['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Etat physique de l'exemplaire -->
        <div class="form-group">
            <label for="etat">Etat *</label>
            <input type="text" id="etat" name="etat" value="<?php echo htmlspecialchars($etat); ?>" placeholder="Ex : Neuf, Bon, Use" required>
        </div>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ajouter l'exemplaire</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>