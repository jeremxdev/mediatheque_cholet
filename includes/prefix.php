<?php
// ============================================================
// CALCUL DU PREFIXE D'URL DU PROJET (prefix.php)
// Ce fichier est inclus pour connaitre l'URL de base du projet,
// permettant aux liens (CSS, navigation) de fonctionner
// quel que soit l'emplacement du dossier sur le serveur.
// Il definit la variable $prefix.
// ============================================================

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; // protocole (http ou https)
$host = $_SERVER['HTTP_HOST'];                                                          // nom de domaine / port

// Chemin reel (sur le disque) du dossier racine du serveur web
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '.') ?: '');

// Chemin reel (sur le disque) du dossier du projet (chemin parent de /includes)
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