<?php
// ============================================================
// CONFIGURATION DE LA BASE DE DONNEES
// ============================================================
// Ce fichier est inclus par toutes les pages du site.
// Il etablit et partage la connexion unique a MySQL via $pdo.
// ============================================================

// --- Parametres de connexion MySQL ---
$host = 'localhost';          // Adresse du serveur MySQL (machine locale ici)
$dbname = 'mediateque';       // Nom de la base de donnees utilisee
$username = 'root';           // Identifiant MySQL (compte par defaut de Laragon/XAMPP)
$password = '';               // Mot de passe MySQL (vide par defaut en local)

// --- Tentative de connexion ---
try {
    // Cree l'objet PDO : connexion a la base avec le jeu de caracteres utf8mb4
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // En cas d'erreur SQL, PDO leve une exception (plus facile a diagnostiquer)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Les resultats des requetes sont retournes sous forme de tableaux associatifs
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la connexion echoue, on affiche l'erreur et on stoppe le script
    die("Erreur de connexion : " . $e->getMessage());
}
// La variable $pdo est maintenant disponible dans tous les fichiers inclus.