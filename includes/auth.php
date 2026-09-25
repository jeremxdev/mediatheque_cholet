<?php
// ============================================================
// GESTION DE L'AUTHENTIFICATION (auth.php)
// Demarre la session et fournit les fonctions de controle
// d'acces (admin / adherent) utilisees par toutes les pages.
// ============================================================

// Demarre la session PHP si ce n'est pas encore fait
if (session_status() === PHP_SESSION_NONE) session_start();

// Prefixe des URLs du projet (necessaire pour les redirections
// depuis des sous-dossiers comme livres/, exemplaires/, etc.)
require_once __DIR__ . '/prefix.php';

// Regles de gestion metier (majorite, validite adhesion, disponibilites)
require_once __DIR__ . '/functions.php';

/**
 * Retourne l'utilisateur connecte (session) ou null.
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Vrai si l'utilisateur connecte est administrateur.
 */
function is_admin() {
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'admin';
}

/**
 * Vrai si l'utilisateur connecte est adherent.
 */
function is_adherent() {
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'adherent';
}

/**
 * Exige une connexion : redirige vers la page de connexion.
 */
function require_auth() {
    if (!current_user()) {
        global $prefix;
        header("Location: " . $prefix . "login.php");
        exit;
    }
}

/**
 * Exige le role administrateur : redirige les adherents
 * vers leur espace personnel (compte.php).
 */
function require_admin() {
    require_auth();
    if (!is_admin()) {
        global $prefix;
        header("Location: " . $prefix . "compte.php");
        exit;
    }
}