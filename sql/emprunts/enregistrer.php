<?php
// ============================================================
// ENREGISTREMENT D'UN EMPRUNT (emprunts/enregistrer.php)
// Affiche le formulaire (adherent + exemplaire disponible + dates)
// puis enregistre l'emprunt dans deux tables en transaction :
//   1. Emprunt : le prets avec dates et adherent
//   2. Emprunter : le lien entre l'emprunt et l'exemplaire
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Liste des adherents pour la liste deroulante
$adherents = $pdo->query("SELECT Id_Adherent, nomadh, prenomadh FROM Adherent ORDER BY nomadh ASC, prenomadh ASC")->fetchAll();

// --- Liste des exemplaires DISPONIBLES uniquement ---
// Regle de gestion : un exemplaire est disponible s'il n'a AUCUN emprunt
// en cours. Un emprunt en cours = lien Emprunter dont le retour n'a pas
// encore ete enregistre (dateretour IS NULL).
// LEFT JOIN : on garde aussi les exemplaires sans livre associe.
$exemplaires = $pdo->query("
    SELECT ex.Id_Exemplaire, ex.code_exemplaire, ex.etat, l.titre
    FROM Exemplaire ex
    LEFT JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    LEFT JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE NOT EXISTS (
        SELECT 1 FROM Emprunter em
        JOIN Emprunt e ON em.Id_Emprunt = e.Id_Emprunt
        WHERE em.Id_Exemplaire = ex.Id_Exemplaire AND e.dateretour IS NULL
    )
    ORDER BY l.titre IS NULL, l.titre ASC, ex.Id_Exemplaire ASC
")->fetchAll();

// Initialisation des valeurs (date d'emprunt par defaut : aujourd'hui)
$errors = [];
$Id_Adherent = $Id_Exemplaire = 0;
$dateemprrunt = date('Y-m-d');

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperation des champs
    $Id_Adherent = intval($_POST['Id_Adherent'] ?? 0);
    $Id_Exemplaire = intval($_POST['Id_Exemplaire'] ?? 0);
    $dateemprrunt = trim($_POST['dateemprrunt'] ?? '');

    // Validations
    if ($Id_Adherent <= 0) $errors[] = "Veuillez selectionner un adherent.";
    if ($Id_Exemplaire <= 0) $errors[] = "Veuillez selectionner un exemplaire.";
    if (empty($dateemprrunt)) $errors[] = "La date d'emprunt est obligatoire.";

    // -- Regle de gestion : une adhesion expiree interdit tout emprunt
    if ($Id_Adherent > 0 && !adhesion_valide($pdo, $Id_Adherent)) {
        $errors[] = "L'adhesion de cet adherent est expiree : nouvel emprunt impossible.";
    }

    // -- Regle de gestion : l'exemplaire doit etre disponible (aucun emprunt en cours)
    if ($Id_Exemplaire > 0 && !exemplaire_est_disponible($pdo, $Id_Exemplaire)) {
        $errors[] = "Cet exemplaire n'est plus disponible.";
    }

    // Sans erreur, enregistrement de l'emprunt
    if (empty($errors)) {
        // Les deux insertions sont effectuees dans une transaction
        $pdo->beginTransaction();

        // 1. Creation de l'emprunt (date + adherent). La date de retour est
        //    NULL : elle sera renseignee lors du retour effectif de l'ouvrage.
        $stmt = $pdo->prepare("INSERT INTO Emprunt (dateemprrunt, dateretour, Id_Adherent) VALUES (?, NULL, ?)");
        $stmt->execute([$dateemprrunt, $Id_Adherent]);
        $idEmprunt = $pdo->lastInsertId();   // identifiant auto-genere de l'emprunt

        // 2. Liaison de l'exemplaire a l'emprunt (table Emprunter)
        $stmt2 = $pdo->prepare("INSERT INTO Emprunter (Id_Exemplaire, Id_Emprunt) VALUES (?, ?)");
        $stmt2->execute([$Id_Exemplaire, $idEmprunt]);

        // Validation des deux insertions
        $pdo->commit();

        // Redirection vers la liste avec message de succes
        header("Location: index.php?success=Emprunt enregistre avec succes");
        exit;
    }
}

// Surbrillance du menu "Emprunts"
$currentPage = 'emprunts';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Enregistrer un emprunt</h1>
<p class="subtitle">Nouvel emprunt a la Mediatheque</p>

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

        <!-- Choix de l'adherent -->
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

        <!-- Choix de l'exemplaire : uniquement les exemplaires disponibles -->
        <div class="form-group">
            <label for="Id_Exemplaire">Exemplaire disponible *</label>
            <select id="Id_Exemplaire" name="Id_Exemplaire" required>
                <option value="">-- Selectionner un exemplaire --</option>
                <?php foreach ($exemplaires as $adm): ?>
                    <option value="<?php echo $adm['Id_Exemplaire']; ?>" <?php echo $Id_Exemplaire == $adm['Id_Exemplaire'] ? 'selected' : ''; ?>>
                        <!-- "Livre non associe" quand l'exemplaire n'a pas de titre -->
                        <?php echo htmlspecialchars($adm['code_exemplaire'] ?? ('#' . $adm['Id_Exemplaire'])); ?> - <?php echo $adm['titre'] ? htmlspecialchars($adm['titre']) : 'Livre non associe'; ?> (<?php echo htmlspecialchars($adm['etat'] ?? '-'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Date d'emprunt uniquement (le retour est enregistre lors de la restitution) -->
        <div class="form-group">
            <label for="dateemprrunt">Date d'emprunt *</label>
            <input type="date" id="dateemprrunt" name="dateemprrunt" value="<?php echo htmlspecialchars($dateemprrunt); ?>" required>
        </div>

        <!-- Boutons : enregistrer ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer l'emprunt</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>