<?php
// ============================================================
// SUPPRESSION D'UN LIVRE (livres/supprimer.php)
// Supprime le livre identifie par ?id= puis redirige vers la liste.
// Aucun affichage : c'est une page "action".
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Recupere l'identifiant du livre depuis l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Requete preparee de suppression selon l'id
$stmt = $pdo->prepare("DELETE FROM Livre WHERE Id_Livre = ?");
$stmt->execute([$id]);

// Redirection vers la liste avec un message de confirmation
header("Location: index.php?success=Livre supprime avec succes");
exit;