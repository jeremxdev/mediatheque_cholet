<?php
// ============================================================
// SUPPRESSION D'UN ADHERENT (adherents/supprimer.php)
// Supprime l'adherent identifie par ?id= puis redirige.
// Page "action" : aucun affichage.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Identifiant de l'adherent dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Requete preparee de suppression
$stmt = $pdo->prepare("DELETE FROM Adherent WHERE Id_Adherent = ?");
$stmt->execute([$id]);

// Redirection vers la liste avec message de confirmation
header("Location: index.php?success=Adherent supprime avec succes");
exit;