<?php
// ============================================================
// SUPPRESSION D'UN LIVRE (livres/supprimer.php)
// Supprime le livre identifie par ?id= puis redirige vers la liste.
// Aucun affichage : c'est une page "action".
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Recupere l'identifiant du livre depuis l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Requete preparee de suppression selon l'id.
// Le livre est supprime dans une transaction avec ses dependances
// (liens exemplaires dans Posseder). Les tables livre_num et
// telechargement sont supprimees automatiquement (ON DELETE CASCADE).
$pdo->beginTransaction();

// 1. Retire les liens entre ce livre et ses exemplaires (table Posseder)
$stmt = $pdo->prepare("DELETE FROM Posseder WHERE Id_Livre = ?");
$stmt->execute([$id]);

// 2. Supprime le livre lui-meme
$stmt = $pdo->prepare("DELETE FROM Livre WHERE Id_Livre = ?");
$stmt->execute([$id]);

// Validation des suppressions
$pdo->commit();

// Redirection vers la liste avec un message de confirmation
header("Location: index.php?success=Livre supprime avec succes");
exit;