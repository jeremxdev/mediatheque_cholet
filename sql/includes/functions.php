<?php
// ============================================================
// FONCTIONS METIER COMMUNES (functions.php)
// Centralise les regles de gestion de la mediatheque :
//  - majorite des adherents (mineur / majeur) ;
//  - validite de l'adhesion (carte de pret valable 1 an) ;
//  - disponibilite d'un exemplaire (pas d'emprunt en cours).
// Les pages qui incluent auth.php disposent automatiquement
// de ces fonctions.
// ============================================================

// Age de la majorite (regle de gestion)
define('MAJORITE_ANS', 18);

// Duree de validite d'une carte de pret (regle de gestion)
define('DUREE_VALIDITE_CARTE', '+1 year');

// Etats possibles d'un exemplaire (regle de gestion)
define('ETATS_EXEMPLAIRE', ['Neuf', 'Bon état', 'Usagé']);

/**
 * Age en annees d'une personne nee a $dateNaissance (format YYYY-MM-DD).
 */
function age_en_annees($dateNaissance) {
    if (empty($dateNaissance)) return -1;
    try {
        $naissance = new DateTime($dateNaissance);
        return $naissance->diff(new DateTime('today'))->y;
    } catch (Exception $e) {
        return -1;
    }
}

/**
 * Vrai si la personne est majeure (>= 18 ans).
 */
function est_majeur($dateNaissance) {
    $age = age_en_annees($dateNaissance);
    return $age >= 0 && $age >= MAJORITE_ANS;
}

/**
 * Vrai si la personne est mineure (< 18 ans).
 */
function est_mineur($dateNaissance) {
    $age = age_en_annees($dateNaissance);
    return $age >= 0 && $age < MAJORITE_ANS;
}

/**
 * Date d'expiration de l'adhesion : date d'emission de la carte la plus
 * recente + 1 an. Retourne null si l'adherent n'a aucune carte.
 */
function date_expiration_adhesion($pdo, $Id_Adherent) {
    $stmt = $pdo->prepare("
        SELECT dateemission
        FROM Carte
        WHERE Id_Adherent = ?
        ORDER BY dateemission DESC, Id_Carte DESC
        LIMIT 1
    ");
    $stmt->execute([$Id_Adherent]);
    $emission = $stmt->fetchColumn();
    if (!$emission) return null;

    $date = new DateTime($emission);
    $date->modify(DUREE_VALIDITE_CARTE);
    return $date->format('Y-m-d');
}

/**
 * Vrai si l'adhesion est valide : au moins une carte existe
 * et la carte la plus recente n'est pas expiree.
 */
function adhesion_valide($pdo, $Id_Adherent) {
    $expiration = date_expiration_adhesion($pdo, $Id_Adherent);
    return $expiration !== null && $expiration >= date('Y-m-d');
}

/**
 * Vrai si l'exemplaire est disponible : aucun emprunt en cours
 * (un emprunt en cours = lien Emprunter dont le retour n'a pas
 * encore ete enregistre, c'est-a-dire dateretour IS NULL).
 */
function exemplaire_est_disponible($pdo, $Id_Exemplaire) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM Emprunter em
        JOIN Emprunt e ON em.Id_Emprunt = e.Id_Emprunt
        WHERE em.Id_Exemplaire = ? AND e.dateretour IS NULL
    ");
    $stmt->execute([$Id_Exemplaire]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Libelle lisible de la validite d'une adhesion (pour affichage).
 */
function statut_validite_adhesion($pdo, $Id_Adherent) {
    $expiration = date_expiration_adhesion($pdo, $Id_Adherent);
    if ($expiration === null) {
        return ['valide' => false, 'libelle' => 'Aucune carte', 'expiration' => null];
    }
    $valide = $expiration >= date('Y-m-d');
    return [
        'valide'     => $valide,
        'libelle'    => $valide ? 'Valide' : 'Expiree',
        'expiration' => $expiration,
    ];
}