<?php
// ============================================================
// PAGE DE CONNEXION (login.php)
// Permet a un administrateur ou un adherent de s'authentifier.
// Affiche le formulaire, verifie l'identifiant et le mot de passe
// (mot de passe hashé en base avec password_hash), puis ouvre
// une session et redirige vers le bon espace.
// ============================================================

// Connexion a la base de donnees ($pdo)
require_once __DIR__ . '/config/database.php';

// Gestion de session et fonctions d'auth ($prefix de defini ici)
require_once __DIR__ . '/includes/auth.php';

// Si l'utilisateur est deja connecte, direction son espace
$u = current_user();
if ($u) {
    header("Location: " . $prefix . ($u['role'] === 'admin' ? 'index.php' : 'compte.php'));
    exit;
}

// Initialisation des variables du formulaire
$errors = [];
$identifiant = '';

// --- Traitement du formulaire soumis ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motdepasse  = $_POST['motdepasse'] ?? '';

    // Verifie que les deux champs sont remplis
    if ($identifiant === '' || $motdepasse === '') {
        $errors[] = "Veuillez remplir tous les champs.";
    } else {
        // Recherche de l'utilisateur par son identifiant
        // (LEFT JOIN : recupere aussi nom/prenom de l'adherent lie)
        $stmt = $pdo->prepare("
            SELECT u.*, a.nomadh, a.prenomadh
            FROM utilisateur u
            LEFT JOIN adherent a ON u.Id_Adherent = a.Id_Adherent
            WHERE u.identifiant = ?
        ");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();

        // Verifie le mot de passe avec password_verify()
        if ($user && password_verify($motdepasse, $user['motdepasse'])) {
            // Enregistre l'utilisateur en session
            $_SESSION['user'] = [
                'id'          => $user['Id_Utilisateur'],
                'role'        => $user['role'],
                'identifiant' => $user['identifiant'],
                'Id_Adherent' => $user['Id_Adherent'] ? intval($user['Id_Adherent']) : null,
                'nom'         => $user['role'] === 'admin' ? 'Administrateur' : $user['nomadh'],
                'prenom'      => $user['role'] === 'admin' ? '' : $user['prenomadh'],
            ];
            // Regenerer l'id de session (securite : evite la fixation de session)
            session_regenerate_id(true);

            // Redirection selon le role
            header("Location: " . $prefix . ($user['role'] === 'admin' ? 'index.php' : 'compte.php'));
            exit;
        } else {
            $errors[] = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mediatheque de Cholet</title>
    <link rel="stylesheet" href="<?php echo $prefix; ?>css/style.css">
</head>
<body class="auth-page">
    <!-- Carte centrale de connexion -->
    <div class="auth-card">
        <!-- Logo -->
        <a href="<?php echo $prefix; ?>login.php" class="logo-link">
            <div class="logo-icon auth-logo">MC</div>
            <h1>Mediatheque de Cholet</h1>
            <p class="subtitle">Connexion a votre espace</p>
        </a>

        <!-- Affichage des erreurs eventuelles -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST">
            <div class="form-group">
                <label for="identifiant">Identifiant (email)</label>
                <input type="text" id="identifiant" name="identifiant" value="<?php echo htmlspecialchars($identifiant); ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="motdepasse">Mot de passe</label>
                <input type="password" id="motdepasse" name="motdepasse" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </form>

        <!-- Comptes de demonstration -->
        <div class="auth-hint">
            <p><strong>Comptes de demonstration :</strong></p>
            <p>Admin : <code>admin</code> / <code>admin123</code></p>
            <p>Adherent : <code>jean.dupont@email.fr</code> / <code>adherent123</code></p>
        </div>
    </div>
</body>
</html>