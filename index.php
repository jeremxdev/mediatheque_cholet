<?php
// ============================================================
// PAGE D'ACCUEIL (index.php)
// Affiche un tableau de bord avec le nombre de livres,
// d'exemplaires, d'adherents et d'emprunts enregistres.
// ============================================================

// Connexion a la base de donnees (fournit la variable $pdo)
require_once __DIR__ . '/config/database.php';

// --- Compter les enregistrements de chaque table ---
// fetchColumn() retourne la premiere valeur du resultat (le compteur)
$nbLivres = $pdo->query("SELECT COUNT(*) FROM Livre")->fetchColumn();        // total des livres
$nbAdherents = $pdo->query("SELECT COUNT(*) FROM Adherent")->fetchColumn();  // total des adherents
$nbExemplaires = $pdo->query("SELECT COUNT(*) FROM Exemplaire")->fetchColumn(); // total des exemplaires
$nbEmprunts = $pdo->query("SELECT COUNT(*) FROM Emprunt")->fetchColumn();    // total des emprunts

// Indique au header que la page active est l'accueil (surbrillance menu)
$currentPage = 'accueil';

// Affiche l'en-tete (logo + navigation)
require_once __DIR__ . '/includes/header.php';
?>

<!-- Titre principal de la page -->
<h1>Bienvenue a la Mediatheque de Cholet</h1>
<p class="subtitle">Gestion des livres, adherents et emprunts</p>

<!-- Grille de cartes : chaque carte est un lien vers un module -->
<div class="dashboard-grid">
    <!-- Carte Livres -->
    <a href="livres/" class="card">
        <div class="card-icon blue">&#128214;</div>   <!-- icone livre -->
        <h3>Livres</h3>                              <!-- titre du module -->
        <p>Gestion du catalogue</p>                  <!-- description -->
        <div class="card-count"><?php echo $nbLivres; ?></div> <!-- compteur dynamique -->
    </a>

    <!-- Carte Exemplaires -->
    <a href="exemplaires/" class="card">
        <div class="card-icon green">&#128218;</div>
        <h3>Exemplaires</h3>
        <p>Gestion des exemplaires</p>
        <div class="card-count"><?php echo $nbExemplaires; ?></div>
    </a>

    <!-- Carte Adherents -->
    <a href="adherents/" class="card">
        <div class="card-icon amber">&#128101;</div>
        <h3>Adherents</h3>
        <p>Inscriptions et profils</p>
        <div class="card-count"><?php echo $nbAdherents; ?></div>
    </a>

    <!-- Carte Emprunts -->
    <a href="emprunts/" class="card">
        <div class="card-icon blue">&#128203;</div>
        <h3>Emprunts</h3>
        <p>Suivi des emprunts</p>
        <div class="card-count"><?php echo $nbEmprunts; ?></div>
    </a>
</div>

<?php
// Affiche le pied de page commun
require_once __DIR__ . '/includes/footer.php';
?>