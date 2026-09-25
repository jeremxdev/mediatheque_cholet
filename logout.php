<?php
// ============================================================
// DECONNEXION (logout.php)
// Detruit la session en cours puis redirige vers la page
// de connexion. Page "action" sans affichage.
// ============================================================

// Demarre la session pour pouvoir la detruire
session_start();

// Vide toutes les variables de session
$_SESSION = [];

// Detruit la session cote serveur
session_destroy();

// Supprime egalement le cookie de session (nettoyage complet)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Retour a la page de connexion
header("Location: login.php");
exit;