<?php
// ============================================================
// EN-TETE COMMUN DU SITE (header)
// Ce fichier est inclus par toutes les pages.
// Il affiche : le logo, la barre utilisateur, la navigation
// (adaptee au role : admin ou adherent) et l'ouverture du contenu.
// ============================================================

// Si la page courante n'est pas definie, on lui donne une valeur vide
// (evite une erreur lors des comparaisons de $currentPage dans le menu)
if (!isset($currentPage)) $currentPage = '';

// Calcule le prefixe des URLs du projet ($prefix)
require_once __DIR__ . '/prefix.php';

// Gestion de session et fonctions d'auth (current_user, is_admin, is_adherent)
require_once __DIR__ . '/auth.php';

// Utilisateur connecte (null si personne)
$user = current_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediatheque de Cholet</title>
    <!-- Feuille de style : le chemin est precede de $prefix pour rester valable partout -->
    <link rel="stylesheet" href="<?php echo $prefix; ?>css/style.css">
</head>
<body>
    <!-- En-tete avec le logo du site -->
    <header>
        <div class="header-top">
            <!-- Lien du logo : retour a l'accueil -->
            <a href="<?php echo $prefix; ?>index.php" class="logo">
                <div class="logo-icon">MC</div>
                <div>
                    <div class="logo-text">Mediatheque</div>
                    <div class="logo-sub">Ville de Cholet</div>
                </div>
            </a>

            <!-- Zone droite : utilisateur connecte + bouton burger -->
            <div class="header-actions">
                <!-- Barre utilisateur : nom + deconnexion (ou bouton connexion) -->
                <div class="user-bar">
                    <?php if ($user): ?>
                        <span class="user-greeting">
                            <?php echo htmlspecialchars(($user['prenom'] ? $user['prenom'] . ' ' : '') . $user['nom']); ?>
                        </span>
                        <a href="<?php echo $prefix; ?>logout.php" class="btn btn-secondary btn-sm">Deconnexion</a>
                    <?php else: ?>
                        <a href="<?php echo $prefix; ?>login.php" class="btn btn-primary btn-sm">Connexion</a>
                    <?php endif; ?>
                </div>

                <!-- Bouton "burger" : visible uniquement sur mobile (affiche/masque le menu) -->
                <button type="button" class="nav-toggle" id="navToggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mainNav">
                    <!-- Les 3 barres du burger seront animees en croix ("X") quand le menu est ouvert -->
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Barre de navigation principale (repliable sur mobile) -->
    <nav id="mainNav">
        <!-- Menu affiche si utilisateur connecte -->
        <?php if ($user): ?>

            <!-- Menu des administrateurs : tous les modules de gestion -->
            <?php if (is_admin()): ?>
                <ul>
                    <!-- Chaque lien possede la classe "active" si $currentPage correspond -->
                    <li><a href="<?php echo $prefix; ?>index.php" class="<?php echo $currentPage === 'accueil' ? 'active' : ''; ?>">&#127968; Accueil</a></li>
                    <li><a href="<?php echo $prefix; ?>livres/" class="<?php echo $currentPage === 'livres' ? 'active' : ''; ?>">&#128214; Livres</a></li>
                    <li><a href="<?php echo $prefix; ?>numeriques/" class="<?php echo $currentPage === 'numeriques' ? 'active' : ''; ?>">&#128241; Numeriques</a></li>
                    <li><a href="<?php echo $prefix; ?>exemplaires/" class="<?php echo $currentPage === 'exemplaires' ? 'active' : ''; ?>">&#128218; Exemplaires</a></li>
                    <li><a href="<?php echo $prefix; ?>adherents/" class="<?php echo $currentPage === 'adherents' ? 'active' : ''; ?>">&#128101; Adherents</a></li>
                    <li><a href="<?php echo $prefix; ?>emprunts/" class="<?php echo $currentPage === 'emprunts' ? 'active' : ''; ?>">&#128203; Emprunts</a></li>
                </ul>

            <!-- Menu des adherents : uniquement leur espace personnel -->
            <?php else: ?>
                <ul>
                    <li><a href="<?php echo $prefix; ?>compte.php" class="<?php echo $currentPage === 'compte' ? 'active' : ''; ?>">&#128101; Mon compte</a></li>
                </ul>
            <?php endif; ?>

        <?php endif; ?>
    </nav>

    <!-- Ouverture de la zone centrale : le contenu de chaque page s'affichera ici -->
    <main>