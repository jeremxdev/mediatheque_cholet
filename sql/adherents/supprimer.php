<?php
// ============================================================
// SUPPRESSION D'UN ADHERENT (adherents/supprimer.php)
// Supprime l'adherent identifie par ?id= puis redirige.
// Page "action" : aucun affichage.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // gestion reservee aux administrateurs

// Identifiant de l'adherent dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Transaction : suppression de toutes les dependances de l'adherent
$pdo->beginTransaction();

// 1. Historique des telechargements de l'adherent (livres numeriques)
$stmt = $pdo->prepare("DELETE FROM telechargement WHERE Id_Adherent = ?");
$stmt->execute([$id]);

// 2. Liaisons emprunt/exemplaire des emprunts de l'adherent, puis emprunts
$stmt = $pdo->prepare("DELETE FROM Emprunter WHERE Id_Emprunt IN (SELECT Id_Emprunt FROM Emprunt WHERE Id_Adherent = ?)");
$stmt->execute([$id]);
$stmt = $pdo->prepare("DELETE FROM Emprunt WHERE Id_Adherent = ?");
$stmt->execute([$id]);

// 3. Cartes de pret de l'adherent
$stmt = $pdo->prepare("DELETE FROM Carte WHERE Id_Adherent = ?");
$stmt->execute([$id]);

// 4. Les adherents dont il etait le referent n'ont plus de referent
$stmt = $pdo->prepare("UPDATE Adherent SET Id_Adherent_1 = NULL WHERE Id_Adherent_1 = ?");
$stmt->execute([$id]);

// 5. Compte de connexion (table utilisateur) s'il existe
$stmt = $pdo->prepare("DELETE FROM utilisateur WHERE Id_Adherent = ?");
$stmt->execute([$id]);

// 6. L'adherent lui-meme
$stmt = $pdo->prepare("DELETE FROM Adherent WHERE Id_Adherent = ?");
$stmt->execute([$id]);

// Validation des suppressions
$pdo->commit();

// Redirection vers la liste avec message de confirmation
header("Location: index.php?success=Adherent supprime avec succes");
exit;