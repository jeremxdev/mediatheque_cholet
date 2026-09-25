<?php
// ============================================================
// SUPPRESSION D'UN ACCES NUMERIQUE (numeriques/supprimer.php)
// Supprime l'acces en ligne ainsi que l'historique des
// telechargements du livre. Page "action" sans affichage.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant du livre dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Transaction : suppression des telechargements puis de l'acces
$pdo->beginTransaction();

// 1. Historique des telechargements du livre
$stmt = $pdo->prepare("DELETE FROM telechargement WHERE Id_Livre = ?");
$stmt->execute([$id]);

// 2. Acces numerique lui-meme
$stmt = $pdo->prepare("DELETE FROM livre_num WHERE Id_Livre = ?");
$stmt->execute([$id]);

// Validation des suppressions
$pdo->commit();

// Redirection vers la liste avec message de confirmation
header("Location: index.php?success=Acces numerique supprime avec succes");
exit;