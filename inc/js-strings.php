<?php
defined('ABSPATH') || exit;

/**
 * Textes affichés par le JavaScript de ce paquet (messages d'erreur, confirmations, libellés).
 * Le JavaScript les appelle avec ispagT('Texte anglais') ; ce fichier fournit leur traduction dans la langue du site
 * (fichiers languages/ de ce paquet). Un texte absent de la liste reste tel quel (anglais).
 * Après avoir ajouté un texte dans un fichier JS, ajoutez-le ici puis lancez tools/i18n/build.py (ISPAG Project Manager).
 */
if (!function_exists('ispag_theme_js_strings')) {
    function ispag_theme_js_strings() {
        return [
        'Apply changes to ' => __('Apply changes to ', 'ispag-crm'),
        'Error: ' => __('Error: ', 'ispag-crm'),
        'Invalid phone number' => __('Invalid phone number', 'ispag-crm'),
        'No project selected.' => __('No project selected.', 'ispag-crm'),
        'Please choose a stage or a contact date.' => __('Please choose a stage or a contact date.', 'ispag-crm'),
        'Select an image' => __('Select an image', 'ispag-crm'),
        'Utiliser cette image' => __('Utiliser cette image', 'ispag-crm'),
        ];
    }

    /** Dictionnaire {texte anglais → texte traduit} injecté dans la page ; seuls les textes réellement traduits sont envoyés. */
    function ispag_theme_print_js_i18n() {
        $map = array_filter(ispag_theme_js_strings(), function ($translated, $english) { return $translated !== $english; }, ARRAY_FILTER_USE_BOTH);
        echo '<script>window.ISPAG_JS_I18N=Object.assign(window.ISPAG_JS_I18N||{},' . wp_json_encode($map, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . ');'
            . 'window.ispagT=function(s){var d=window.ISPAG_JS_I18N||{};return Object.prototype.hasOwnProperty.call(d,s)?d[s]:s};</script>' . "\n";
    }
    add_action('wp_head', 'ispag_theme_print_js_i18n', 1);
    add_action('admin_head', 'ispag_theme_print_js_i18n', 1);
}
