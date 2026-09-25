</main>
    <!-- ============================================================
         PIED DE PAGE COMMUN (footer)
         Ce fichier est inclus par toutes les pages, apres leur contenu.
         Il ferme la zone principale et affiche le copyright.
         ============================================================ -->
    <footer>
        <!-- Affiche le nom du site et l'annee actuelle (mise a jour automatique) -->
        Mediatheque de Cholet &copy; <?php echo date('Y'); ?>
    </footer>

    <!-- Script du menu burger (charge a la fin du DOM) -->
    <script src="<?php echo $prefix; ?>js/main.js"></script>
</body>
</html>