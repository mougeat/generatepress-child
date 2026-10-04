/**
 * Champs de numéro de téléphone : drapeau + indicatif du pays, puis mise en forme internationale (ex. « +41 79 123 45 67 »).
 * S'applique à tous les champs téléphone du site, y compris ceux créés après coup (fenêtres, volets, formulaires ajoutés par script).
 * Bibliothèque : intl-tel-input (chargée par le thème, voir functions.php).
 *
 * Sont reconnus : input[type=tel], name = phone / num_tel_contact / mobile, inputmode=tel, data-f=phone, classe .ispag-phone.
 * Les champs gérés par leur propre script (volet de création de contact, fenêtre d'édition en ligne) sont ignorés ici.
 */
(function () {
    'use strict';

    var SELECTOR = 'input[type="tel"], input[name="phone"], input[name="num_tel_contact"], input[name="mobile"], input[inputmode="tel"], input[data-f="phone"], input.ispag-phone';
    var EXCLUDE  = '#c_phone, #popover-phone-input, .iti__tel-input';
    var UTILS    = (window.ispag_params && window.ispag_params.utils_url) || 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/js/utils.js';

    // Le champ occupe toute la largeur disponible, comme les autres champs
    var css = document.createElement('style');
    css.textContent = '.iti{width:100%}.iti input.ispag-phone-field{width:100%}.iti__country-list{z-index:100000}';
    document.head.appendChild(css);

    function formatInput(input, iti) {
        if (!window.intlTelInputUtils || !String(input.value).trim()) return;
        try {
            if (iti.isValidNumber()) {
                var n = iti.getNumber(window.intlTelInputUtils.numberFormat.INTERNATIONAL);
                if (n) input.value = n;
            }
        } catch (e) { /* mise en forme impossible : on laisse la saisie telle quelle */ }
    }

    function init(input) {
        if (!window.intlTelInput || input.dataset.ispagPhone === '1') return;
        if (input.matches(EXCLUDE) || input.closest('.iti') || (window.jQuery && window.jQuery(input).data('iti'))) return;
        input.dataset.ispagPhone = '1';
        input.classList.add('ispag-phone-field');

        var iti = window.intlTelInput(input, {
            initialCountry: 'ch',
            preferredCountries: ['ch', 'fr', 'de', 'it', 'be'],
            separateDialCode: false,
            nationalMode: false,
            formatOnDisplay: true,
            autoPlaceholder: 'polite',
            allowDropdown: !input.readOnly && !input.disabled,
            dropdownContainer: document.body,
            utilsScript: UTILS
        });
        input._ispagIti = iti;
        if (window.jQuery) window.jQuery(input).data('iti', iti); // les scripts qui testent data('iti') ne le réinitialisent pas

        // Valeur déjà présente (numéro enregistré) : pays détecté et numéro mis en forme
        if (input.value) {
            iti.setNumber(input.value);
            setTimeout(function () { formatInput(input, iti); }, 400); // le temps que les règles de mise en forme soient chargées
        }
        input.addEventListener('blur', function () { formatInput(input, iti); });
        input.addEventListener('countrychange', function () { formatInput(input, iti); });
    }

    /** À appeler après avoir rempli un champ par script (input.value = …) : détecte le pays et met en forme. */
    window.ispagPhoneRefresh = function (input) {
        if (!input) return;
        if (!input._ispagIti) { init(input); return; }
        if (input.value) {
            input._ispagIti.setNumber(input.value);
            formatInput(input, input._ispagIti);
        }
    };

    function scan(root) {
        if (!root || !root.querySelectorAll) return;
        if (root.matches && root.matches(SELECTOR)) init(root);
        root.querySelectorAll(SELECTOR).forEach(init);
    }

    function start() {
        scan(document);
        new MutationObserver(function (mutations) {
            mutations.forEach(function (m) { m.addedNodes.forEach(function (n) { if (n.nodeType === 1) scan(n); }); });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
