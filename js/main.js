document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('navToggle');
    var nav    = document.getElementById('mainNav');
    if (!toggle || !nav) return;

    // Ouvre/ferme le menu mobile au clic sur le burger
    toggle.addEventListener('click', function () {
        var isOpen = nav.classList.toggle('open');      // ajoute/retire la classe "open"
        toggle.classList.toggle('open', isOpen);        // anime la croix (X) quand ouvert
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    });

    // Ferme le menu si on clique ailleurs sur la page
    document.addEventListener('click', function (e) {
        if (!nav.contains(e.target) && !toggle.contains(e.target)) {
            nav.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Ouvrir le menu');
        }
    });
});
