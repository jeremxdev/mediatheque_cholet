<?php
// ============================================================
// RETOUR D'UN EMPRUNT (emprunts/retour.php)
// Enregistre le retour d'un exemplaire : la date de retour est
// renseignee (dateretour), ce qui libere l'exemplaire pour un
// nouvel emprunt. L'etat de l'exemplaire peut etre mis a jour.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'emprunt dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Charge l'emprunt en cours (pas encore rendu)
$stmt = $pdo->prepare("
    SELECT e.Id_Emprunt, e.dateemprrunt, e.dateretour, a.nomadh, a.prenomadh, ex.Id_Exemplaire, ex.code_exemplaire, ex.etat, l.titre
    FROM Emprunt e
    JOIN Adherent a ON e.Id_Adherent = a.Id_Adherent
    JOIN Emprunter em ON e.Id_Emprunt = em.Id_Emprunt
    JOIN Exemplaire ex ON em.Id_Exemplaire = ex.Id_Exemplaire
    JOIN Posseder p ON ex.Id_Exemplaire = p.Id_Exemplaire
    JOIN Livre l ON p.Id_Livre = l.Id_Livre
    WHERE e.Id_Emprunt = ?
");
$stmt->execute([$id]);
$emprunt = $stmt->fetch();

if (!$emprunt) { header("Location: index.php"); exit; }

// Si l'emprunt est deja rendu, on ne peut pas le rendre une seconde fois
if (!empty($emprunt['dateretour'])) { header("Location: index.php"); exit; }

// Initialisation du formulaire
$errors = [];
$dateretour = date('Y-m-d');                // retour effectif : aujourd'hui
$etat = $emprunt['etat'];                   // etat actuel de l'exemplaire

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateretour = trim($_POST['dateretour'] ?? '');
    $etat = trim($_POST['etat'] ?? '');

    // Validations
    if (empty($dateretour)) $errors[] = "La date de retour est obligatoire.";
    if (!in_array($etat, ETATS_EXEMPLAIRE, true)) $errors[] = "L'etat est invalide.";

    // Sans erreur, enregistrement du retour
    if (empty($errors)) {
        $pdo->beginTransaction();

        // 1. Date de retour enregistree (l'emprunt devient "rendu")
        $stmt1 = $pdo->prepare("UPDATE Emprunt SET dateretour = ? WHERE Id_Emprunt = ?");
        $stmt1->execute([$dateretour, $id]);

        // 2. Mise a jour eventuelle de l'etat de l'exemplaire
        if ($etat !== $emprunt['etat']) {
            $stmt2 = $pdo->prepare("UPDATE Exemplaire SET etat = ? WHERE Id_Exemplaire = ?");
            $stmt2->execute([$etat, $emprunt['Id_Exemplaire']]);
        }

        $pdo->commit();

        header("Location: index.php?success=Retour enregistre. L'exemplaire est de nouveau disponible.");
        exit;
    }
}

// Surbrillance du menu "Emprunts"
$currentPage = 'emprunts';
require_once __DIR__ . '/../includes/header.php';
?>

<h1>Retour de l'emprunt</h1>
<p class="subtitle">Enregistrement de la restitution</p>

<div class="form-container">
    <!-- Recapitulatif de l'emprunt a rendre -->
    <div class="detail-container" style="margin-bottom: 1.5rem;">
        <div class="detail-body">
            <div class="detail-row">
                <span class="detail-label">Livre</span>
                <span class="detail-value"><strong><?php echo htmlspecialchars($emprunt['titre']); ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Exemplaire</span>
                <span class="detail-value"><?php echo htmlspecialchars($emprunt['code_exemplaire'] ?? ('#' . $emprunt['Id_Exemplaire'])); ?> (<?php echo htmlspecialchars($emprunt['etat'] ?? '-'); ?>)</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Adherent</span>
                <span class="detail-value"><?php echo htmlspecialchars($emprunt['prenomadh'] . ' ' . $emprunt['nomadh']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date d'emprunt</span>
                <span class="detail-value"><?php echo date('d/m/Y', strtotime($emprunt['dateemprrunt'])); ?></span>
            </div>
        </div>
    </div>

    <form method="POST">
        <!-- Affichage des erreurs eventuelles -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Date effective du retour -->
        <div class="form-row">
            <div class="form-group">
                <label for="dateretour">Date du retour *</label>
                <input type="date" id="dateretour" name="dateretour" value="<?php echo htmlspecialchars($dateretour); ?>" required>
            </div>

            <!-- Etat de l'exemplaire constate au retour (valeurs normalisees) -->
            <div class="form-group">
                <label for="etat">Etat au retour *</label>
                <select id="etat" name="etat" required>
                    <?php foreach (ETATS_EXEMPLAIRE as $e): ?>
                        <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $etat === $e ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Boutons : valider le retour ou annuler -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Valider le retour</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>