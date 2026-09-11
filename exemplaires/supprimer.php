<?php
// ============================================================
// SUPPRESSION D'UN EXEMPLAIRE (exemplaires/supprimer.php)
// Supprime l'exemplaire apres avoir retire ses dependances
// (Emprunter, Posseder) dans une transaction. Page "action" sans affichage.
// ============================================================

// Connexion a la base de donnees
require_once __DIR__ . '/../config/database.php';

// Identifiant de l'exemplaire dans l'URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit; }

// Transaction : les suppressions doivent etre toutes reussies
$pdo->beginTransaction();

// 1. Retire les liens d'emprunt de cet exemplaire (table Emprunter)
$stmt = $pdo->prepare("DELETE FROM Emprunter WHERE Id_Exemplaire = ?");
$stmt->execute([$id]);

// 2. Retire le lien livre/exemplaire (table Posseder)
$stmt = $pdo->prepare("DELETE FROM Posseder WHERE Id_Exemplaire = ?");
$stmt->execute([$id]);

// 3. Supprime enfin l'exemplaire (table Exemplaire)
$stmt = $pdo->prepare("DELETE FROM Exemplaire WHERE Id_Exemplaire = ?");
$stmt->execute([$id]);

// Validation de la transaction
$pdo->commit();

// Redirection vers la liste avec message de confirmation
header("Location: index.php?success=Exemplaire supprime avec succes");
exit;