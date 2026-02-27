<?php

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function theme_enqueue_styles() {

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    wp_enqueue_script( 'ispag-navigation-script', // Nom unique (handle)
        get_stylesheet_directory_uri() . '/assets/js/navigation-script.js', // Chemin vers votre fichier JS
        ['jquery'], // Dépendances (ici, jQuery)
        wp_get_theme()->get('Version'), // Version pour éviter les problèmes de cache
        true // Charger le script dans le pied de page (footer)
    );

    wp_enqueue_script( 'ispag-select2-script', // Nom unique (handle)
        get_stylesheet_directory_uri() . '/assets/js/select2.min.js', // Chemin vers votre fichier JS
        ['jquery'], // Dépendances (ici, jQuery)
        wp_get_theme()->get('Version'), // Version pour éviter les problèmes de cache
        true // Charger le script dans le pied de page (footer)
    );

    wp_enqueue_script(
        'ispag-crm-bulk', 
        get_stylesheet_directory_uri() . '/assets/js/ispag-crm-bulk-actions.js', 
        array('jquery'), // Indique que ton script a besoin de jQuery
        '1.0.0', 
        true // Charge le script en bas de page (footer) pour la performance
    );

    wp_enqueue_script(
        'ispag-crm-deal-select', 
        get_stylesheet_directory_uri() . '/assets/js/ispag-crm-deal-list-select.js', 
        array('jquery'), // Indique que ton script a besoin de jQuery
        '1.0.0', 
        true // Charge le script en bas de page (footer) pour la performance
    );

}

//********************************************************** */
//-------- MODIFICATIONS DE L'EXPEDITEUR DES MAILS -------- */
//********************************************************** */

// 1. Modifier l'adresse e-mail de l'expéditeur (From Email)
// add_filter( 'wp_mail_from', 'ispag_new_mail_from' );
// function ispag_new_mail_from( $original_email_address ) {
//     // Remplacez 'contact@app.ispag-asp.ch' par l'adresse souhaitée
//     return 'contact@app.ispag-asp.ch'; 
// }

// // 2. Modifier le nom de l'expéditeur (From Name)
// add_filter( 'wp_mail_from_name', 'ispag_new_mail_from_name' );
// function ispag_new_mail_from_name( $original_email_from ) {
//     // Remplacez 'ISPAG' par le nom souhaité (ex: ISPAG CRM)
//     return 'ISPAG'; 
// }

function allow_custom_upload_mimes( $mimes ) {
    $mimes['msg']  = 'application/vnd.ms-outlook';
    $mimes['eml']  = 'message/rfc822'; // Type MIME standard pour .eml
    $mimes['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    $mimes['xls']  = 'application/vnd.ms-excel';
    return $mimes;
}
add_filter( 'upload_mimes', 'allow_custom_upload_mimes' );

/**
 * Charge un template ISPAG en cherchant d'abord dans le thème, puis dans le plugin.
 */
function ispag_get_template( $template_name, $args = [] ) {
    if ( $args && is_array( $args ) ) {
        extract( $args ); // Rend les clés du tableau accessibles comme variables (ex: $transactions)
    }

    // 1. Chercher dans le thème enfant ou parent (dossier generatepress-child/)
    $template = locate_template( "generatepress-child/templates/{$template_name}.php" );

    // 2. Si non trouvé, prendre celui par défaut dans le plugin
    if ( ! $template ) {
        $template = plugin_dir_path( __FILE__ ) . "templates/{$template_name}.php";
    }

    if ( file_exists( $template ) ) {
        include( $template );
    }
}

add_action('wp_head', function() {
    ?>
    <link rel="manifest" href="/manifest.json">
    
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ISPAG">
    
    <link rel="apple-touch-icon" href="https://app.ispag-asp.ch/wp-content/uploads/2026/01/icon-192x192-1.png">
    <?php
});