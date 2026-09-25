<?php
// ============================================================
// SUPPRESSION D'UN EMPRUNT (emprunts/supprimer.php)
// Supprime l'emprunt et ses dependances (table Emprunter)
// dans une transaction. Page "action" sans affichage.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'emprunt dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Transaction : la suppression des liens puis de l'emprunt
// doit etre effectuee entierement
$pdo->beginTransaction();

// 1. Supprime d'abord les liaisons emprunt/exemplaire (table Emprunter)
$stmt = $pdo->prepare("DELETE FROM Emprunter WHERE Id_Emprunt = ?");
$stmt->execute([$id]);

// 2. Supprime l'emprunt lui-meme (table Emprunt)
$stmt = $pdo->prepare("DELETE FROM Emprunt WHERE Id_Emprunt = ?");
$stmt->execute([$id]);

// Validation des suppressions
$pdo->commit();

// Redirection vers la liste avec message de confirmation
header("Location: index.php?success=Emprunt supprime avec succes");
exit;