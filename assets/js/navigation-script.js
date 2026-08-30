document.addEventListener('DOMContentLoaded', () => {
    // Gestion des onglets
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

jQuery(document).ready(function($) {
    const $wrapper   = $('.ispag-right-panel-wrapper');
    const $toggleBtn = $('#toggle-right-panel');
    const $icon      = $toggleBtn.find('.ispag-panel-toggle-icon');

    function applyState(collapsed) {
        $wrapper.toggleClass('collapsed', collapsed);
        $icon.attr('src', collapsed ? $icon.data('icon-show') : $icon.data('icon-hide'));
        $toggleBtn.attr('title', collapsed ? 'Afficher le panneau' : 'Masquer le panneau');
    }

    const savedCollapsed = localStorage.getItem('ispag-right-panel-collapsed') === 'true';
    applyState(savedCollapsed);

    $toggleBtn.on('click', function() {
        const nowCollapsed = !$wrapper.hasClass('collapsed');
        applyState(nowCollapsed);
        localStorage.setItem('ispag-right-panel-collapsed', nowCollapsed);
    });
});