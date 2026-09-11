<?php
// ============================================================
// EN-TETE COMMUN DU SITE (header)
// Ce fichier est inclus par toutes les pages.
// Il affiche : le logo, la barre de navigation et l'ouverture du contenu.
// ============================================================

// Si la page courante n'est pas definie, on lui donne une valeur vide
// (evite une erreur lors des comparaisons de $currentPage dans le menu)
if (!isset($currentPage)) $currentPage = '';

// --- Calcul automatique de l'URL de base du projet ---
// Cette partie rend les liens (CSS, navigation) fonctionnels
// quel que soit l'emplacement du dossier sur le serveur.

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; // protocole (http ou https)
$host = $_SERVER['HTTP_HOST'];                                                          // nom de domaine / port

// Chemin reel (sur le disque) du dossier racine du serveur web
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '.') ?: '');

// Chemin reel (sur le disque) du dossier du projet (grace a __DIR__ qui pointe sur includes/)
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');

if ($docRoot !== '' && strpos($projectRoot, $docRoot) === 0) {
    // Si le projet est dans le dossier du serveur (cas normal) :
    // on deduit son chemin web et on construit une URL absolue
    $webPath = substr($projectRoot, strlen($docRoot));           // ex : /mediatheque
    $prefix = $scheme . '://' . $host . $webPath . '/';
} else {
    // Sinon (cas particulier) : on calcule un chemin relatif en fonction
    // de la profondeur de la page courante dans l'arborescence
    $dirName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $segments = array_values(array_filter(explode('/', $dirName)));
    $depth = max(0, count($segments) - 1);                       // 0 = racine, 1 = sous-dossier
    $prefix = str_repeat('../', $depth);                         // rajoute ../ autant de fois que necessaire
}
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

            <!-- Bouton "burger" : visible uniquement sur mobile (affiche/masque le menu) -->
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mainNav">
                <!-- Les 3 barres du burger seront animees en croix ("X") quand le menu est ouvert -->
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Barre de navigation principale (repliable sur mobile) -->
    <nav id="mainNav">
        <ul>
            <!-- Chaque lien possede la classe "active" si $currentPage correspond -->
            <li><a href="<?php echo $prefix; ?>index.php" class="<?php echo $currentPage === 'accueil' ? 'active' : ''; ?>">&#127968; Accueil</a></li>
            <li><a href="<?php echo $prefix; ?>livres/" class="<?php echo $currentPage === 'livres' ? 'active' : ''; ?>">&#128214; Livres</a></li>
            <li><a href="<?php echo $prefix; ?>exemplaires/" class="<?php echo $currentPage === 'exemplaires' ? 'active' : ''; ?>">&#128218; Exemplaires</a></li>
            <li><a href="<?php echo $prefix; ?>adherents/" class="<?php echo $currentPage === 'adherents' ? 'active' : ''; ?>">&#128101; Adherents</a></li>
            <li><a href="<?php echo $prefix; ?>emprunts/" class="<?php echo $currentPage === 'emprunts' ? 'active' : ''; ?>">&#128203; Emprunts</a></li>
        </ul>
    </nav>

    <!-- Ouverture de la zone centrale : le contenu de chaque page s'affichera ici -->
    <main>