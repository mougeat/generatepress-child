
document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------------------
    // --- 2. GESTION DES ONGLETS (VANILLA JS) et MODALE (JQUERY) ---
    // -------------------------------------------------------------------------

    const tabButtons = document.querySelectorAll('.ispag-tabs-navigation .ispag-tab-btn');
    const tabPanes = document.querySelectorAll('.ispag-tabs-content .ispag-tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            const targetPane = document.getElementById(`ispag-tab-${targetTab}`);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });


});

document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.querySelector('.ispag-dropdown-toggle');
    const menu = document.querySelector('.ispag-dropdown-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        // Fermer le menu si on clique n'importe où ailleurs sur la page
        document.addEventListener('click', function() {
            menu.classList.remove('show');
        });
    }
});