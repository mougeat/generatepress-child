<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 30 );

function theme_enqueue_styles() {

    // 1. Styles
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    // Dépendances communes pour tous les scripts CRM du thème
    // On attend jQuery ET le script du plugin qui contient les données 'ispag_ajax'
    $crm_deps = array('jquery', 'ispag-crm-js');

    // 1. Intl-Tel-Input (Drapeaux téléphone)
    wp_enqueue_style( 'intl-tel-input-css', 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/css/intlTelInput.css', array(), '20.0.5' );
    wp_enqueue_script( 'intl-tel-input-js', 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/js/intlTelInput.min.js', array(), '20.0.5', true );

    // 2. Navigation & Plugins tiers
    wp_enqueue_script( 'ispag-navigation-script', 
        get_stylesheet_directory_uri() . '/assets/js/navigation-script.js', 
        array('jquery'), 
        wp_get_theme()->get('Version'), 
        true 
    );

    wp_enqueue_script( 'ispag-select2-script', 
        get_stylesheet_directory_uri() . '/assets/js/select2.min.js', 
        array('jquery'), 
        wp_get_theme()->get('Version'), 
        true 
    );

    // 3. Scripts CRM (Thème)
    
    // Actions bulk générales (Projets/Contacts)
    wp_enqueue_script(
        'ispag-crm-bulk', 
        get_stylesheet_directory_uri() . '/assets/js/ispag-crm-bulk-actions.js', 
        array('jquery'), 
        '1.0.0', 
        true 
    );

    // Actions spécifiques contacts (Celui qui posait l'erreur ispag_ajax)
    wp_enqueue_script(
        'ispag-crm-contact-bulk', 
        get_stylesheet_directory_uri() . '/assets/js/ispag-crm-contact-bulk-actions.js', 
        array('jquery', 'ispag-crm-bulk', 'ispag-crm-js'), 
        '1.0.1', 
        true 
    );

    
    wp_enqueue_script(
        'ispag-crm-create-contact', 
        get_stylesheet_directory_uri() . '/assets/js/ispag-crm-create-contact.js', 
        array('jquery', 'intl-tel-input-js'), 
        '1.0.1', 
        true 
    );

    wp_enqueue_script(
        'ispag-crm-popover', 
        get_stylesheet_directory_uri() . '/assets/js/popover.js', 
        array('jquery', 'intl-tel-input-js'), 
        '1.0.1', 
        true 
    );

    // Juste après wp_enqueue_script('ispag-crm-create-contact', ...)
    wp_localize_script('ispag-crm-create-contact', 'ispag_params', array(
        'ajax_url'  => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('ispag_new_contact_nonce'),
        'utils_url' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/js/utils.js'
    ));

    // Sélection et affichage dans la liste des deals
    wp_enqueue_script(
        'ispag-crm-deal-select', 
        get_stylesheet_directory_uri() . '/assets/js/ispag-crm-deal-list-select.js', 
        $crm_deps, 
        '1.0.0', 
        true 
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
//     // Remplacez 'ispag-crm' par le nom souhaité (ex: ISPAG CRM)
//     return 'ispag-crm'; 
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


add_action('admin_post_nopriv_submit_ispag_quote', 'handle_ispag_quote_submission');
add_action('admin_post_submit_ispag_quote', 'handle_ispag_quote_submission');

function handle_ispag_quote_submission() {
    // 1. Security Check
    if (!isset($_POST['ispag_nonce']) || !wp_verify_nonce($_POST['ispag_nonce'], 'ispag_quote_verify')) {
        wp_die(__('Security violation.', 'ispag-crm'));
    }

    // 2. Data Sanitization
    $company     = sanitize_text_field($_POST['company']);
    $email       = sanitize_email($_POST['customer_email']);
    $project     = sanitize_text_field($_POST['project']);
    $phone       = sanitize_text_field($_POST['phone']);
    $dia         = intval($_POST['dia']);
    $height      = intval($_POST['height']);
    $vol         = intval($_POST['volume']);
    $pressure    = intval($_POST['pressure']);
    $material    = sanitize_text_field($_POST['material']);
    $insulation  = sanitize_text_field($_POST['insulation']);
    $site_w      = isset($_POST['site_welding']) ? 'YES' : 'NO';

    // 3. Email Preparation (to ISPAG technical team)
    $to = 'info@ispag-asp.ch'; // Or your dedicated crm address
    $subject = sprintf('[OFFRE] %s - %s', $company, $project);
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: ISPAG CRM <no-reply@ispag-asp.ch>';
    $headers[] = 'Reply-To: ' . $email;

    $message = "
    <div style='font-family: sans-serif; color: #333; max-width: 600px; border: 1px solid #eee; padding: 20px;'>
        <h2 style='color: #E11D48;'>Nouvelle demande de réservoir</h2>
        <p><strong>Client:</strong> {$company}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Projet:</strong> {$project}</p>
        <p><strong>Téléphone:</strong> {$phone}</p>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <h3>Spécifications Techniques</h3>
        <ul>
            <li>Dimensions: Ø {$dia}mm x H {$height}mm</li>
            <li>Volume: {$vol} Litres</li>
            <li>Pression: {$pressure} bar</li>
            <li>Matière: {$material}</li>
            <li>Isolation: {$insulation}</li>
            <li>Soudure sur site: {$site_w}</li>
        </ul>
        <p style='font-size: 10px; color: #999;'>Envoyé depuis le configurateur en ligne ISPAG.</p>
    </div>";

    // 4. Send Email
    wp_mail($to, $subject, $message, $headers);

    // 5. Send Confirmation to Client (Optional but recommended)
    $client_subject = __('Your quote request at ISPAG', 'ispag-crm');
    $client_message = __("Hello, we have received your request for the project: ", 'ispag-crm') . $project;
    wp_mail($email, $client_subject, $client_message, $headers);

    // 6. Redirect to success page
    wp_redirect(esc_url_raw(add_query_arg('status', 'success', wp_get_referer())));
    exit;
}