/**
 * Panneau « Créer une entreprise » (page publique du CRM).
 * Ouverture : tout élément .trigger-add-company. Envoi en AJAX (action ispag_create_company), puis redirection vers la fiche.
 */
jQuery(function ($) {
    const $overlay = $('#ispag-company-sidebar-modal');
    const form     = document.getElementById('ispag-create-company-form');
    if (!$overlay.length || !form) return;

    const $panel    = $overlay.find('.ispag-sidebar-content');
    const $error    = $('#company-form-error');
    const submitBtn = document.getElementById('btn-submit-company');
    const nameInput = document.getElementById('co_name');
    const params    = window.ispag_company_params || {};
    const t         = params.i18n || {};
    const WIDTH     = '500px';
    let opener = null;

    function showError(message, linkUrl, linkLabel) {
        $error.empty().text(message);
        if (linkUrl) {
            $error.append(' ').append($('<a>', { href: linkUrl, text: linkLabel || '', style: 'font-weight:600;' }));
        }
        $error.show();
    }

    function openPanel(e) {
        if (e) e.preventDefault();
        opener = document.activeElement;
        $error.hide().empty();
        $('body').addClass('sidebar-open');
        $overlay.attr('aria-hidden', 'false').fadeIn(200, function () {
            $panel.animate({ right: '0' }, 300, function () { if (nameInput) nameInput.focus(); });
        });
    }

    function closePanel() {
        $panel.animate({ right: '-' + WIDTH }, 300, function () {
            $overlay.fadeOut(200, function () {
                $('body').removeClass('sidebar-open');
                $overlay.attr('aria-hidden', 'true');
                if (opener && opener.focus) opener.focus();
            });
        });
    }

    $(document).on('click', '.trigger-add-company', openPanel);
    $(document).on('click', '.ispag-company-close', closePanel);
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $overlay.is(':visible')) closePanel();
        if ((e.key === 'Enter' || e.key === ' ') && $(e.target).is('.ispag-company-close[role="button"]')) { e.preventDefault(); closePanel(); }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        $error.hide().empty();

        if (!nameInput.value.trim()) {
            showError(t.name_required || 'The company name is required.');
            nameInput.focus();
            return;
        }

        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = t.creating || 'Creating...';

        const data = new FormData(form);
        data.append('action', 'ispag_create_company');
        data.append('nonce', params.nonce);

        fetch(params.ajax_url, { method: 'POST', body: data, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) {
                    window.location.href = res.data.redirect_url;
                    return;
                }
                const d = (res && res.data) || {};
                showError(d.message || t.error || 'An error occurred.', d.existing_url, d.existing_url ? (t.open_existing || 'Open the existing company') : '');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            })
            .catch(function () {
                showError(t.error || 'An error occurred.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
    });
});
