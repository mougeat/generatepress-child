<?php
/**
 * Functions and definitions for ISPAG CRM Child Theme / Plugin integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sécurité
}

/* ==========================================================================
   1. CHARGEMENT DES TRADUCTIONS (TEXTDOMAIN)
   ========================================================================== */

// function ispag_load_custom_textdomain() {
//     // Charge les traductions pour le domaine 'ispag-crm'
//     load_plugin_textdomain( 'ispag-crm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
// }
// add_action( 'init', 'ispag_load_custom_textdomain' );


/* ==========================================================================
   2. CHARGEMENT DES SCRIPTS ET STYLES (ASSETS)
   ========================================================================== */

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 30 );

function theme_enqueue_styles() {
    // 1. Styles de base du thème parent
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    // Dépendances communes CRM
    $crm_deps = array( 'jquery', 'ispag-crm-js' );

    // 2. Librairies tiers (Intl-Tel-Input)
    wp_enqueue_style( 'intl-tel-input-css', 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/css/intlTelInput.css', array(), '20.0.5' );
    wp_enqueue_script( 'intl-tel-input-js', 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/js/intlTelInput.min.js', array(), '20.0.5', true );

    // 3. Scripts de navigation et utilitaires
    wp_enqueue_script( 'ispag-navigation-script', get_stylesheet_directory_uri() . '/assets/js/navigation-script.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
    wp_enqueue_script( 'ispag-select2-script', get_stylesheet_directory_uri() . '/assets/js/select2.min.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );

    // 4. Scripts CRM spécifiques
    wp_enqueue_script( 'ispag-crm-bulk', get_stylesheet_directory_uri() . '/assets/js/ispag-crm-bulk-actions.js', array( 'jquery' ), '1.0.0', true );
    wp_enqueue_script( 'ispag-crm-contact-bulk', get_stylesheet_directory_uri() . '/assets/js/ispag-crm-contact-bulk-actions.js', array( 'jquery', 'ispag-crm-bulk', 'ispag-crm-js' ), '1.0.1', true );
    wp_enqueue_script( 'ispag-crm-create-contact', get_stylesheet_directory_uri() . '/assets/js/ispag-crm-create-contact.js', array( 'jquery', 'intl-tel-input-js' ), '1.0.1', true );
    wp_enqueue_script( 'ispag-crm-popover', get_stylesheet_directory_uri() . '/assets/js/popover.js', array( 'jquery', 'intl-tel-input-js' ), '1.0.1', true );
    wp_enqueue_script( 'ispag-crm-deal-select', get_stylesheet_directory_uri() . '/assets/js/ispag-crm-deal-list-select.js', $crm_deps, '1.0.0', true );

    // Localisation AJAX pour la création de contact
    wp_localize_script( 'ispag-crm-create-contact', 'ispag_params', array(
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'ispag_new_contact_nonce' ),
        'utils_url' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@20.0.5/build/js/utils.js'
    ) );
}


/* ==========================================================================
   3. CONFIGURATION DES TEMPLATES ET UPLOADS
   ========================================================================== */

/**
 * Autorise l'upload de types de fichiers spécifiques (CRM)
 */
function allow_custom_upload_mimes( $mimes ) {
    $mimes['msg']  = 'application/vnd.ms-outlook';
    $mimes['eml']  = 'message/rfc822';
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
        extract( $args );
    }

    $template = locate_template( "generatepress-child/templates/{$template_name}.php" );

    if ( ! $template ) {
        $template = plugin_dir_path( __FILE__ ) . "templates/{$template_name}.php";
    }

    if ( file_exists( $template ) ) {
        include( $template );
    }
}


/* ==========================================================================
   4. PWA & HEAD META TAGS
   ========================================================================== */

add_action( 'wp_head', function() {
    ?>
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ISPAG">
    <link rel="apple-touch-icon" href="<?php echo home_url(); ?>/wp-content/uploads/2026/01/icon-192x192-1.png">
    <?php
});


/* ==========================================================================
   5. GESTION DES FORMULAIRES DE DEvis (QUOTES)
   ========================================================================== */

add_action( 'admin_post_nopriv_submit_ispag_quote', 'handle_ispag_quote_submission' );
add_action( 'admin_post_submit_ispag_quote', 'handle_ispag_quote_submission' );

function handle_ispag_quote_submission() {
    if ( ! isset( $_POST['ispag_nonce'] ) || ! wp_verify_nonce( $_POST['ispag_nonce'], 'ispag_quote_verify' ) ) {
        wp_die( __( 'Security violation.', 'ispag-crm' ) );
    }

    $company    = sanitize_text_field( $_POST['company'] );
    $email      = sanitize_email( $_POST['customer_email'] );
    $project    = sanitize_text_field( $_POST['project'] );
    $phone      = sanitize_text_field( $_POST['phone'] );
    $dia        = intval( $_POST['dia'] );
    $height     = intval( $_POST['height'] );
    $vol        = intval( $_POST['volume'] );
    $pressure   = intval( $_POST['pressure'] );
    $material   = sanitize_text_field( $_POST['material'] );
    $insulation = sanitize_text_field( $_POST['insulation'] );
    $site_w     = isset( $_POST['site_welding'] ) ? 'YES' : 'NO';

    $to = 'info@ispag-asp.ch';
    $subject = sprintf( '[OFFRE] %s - %s', $company, $project );
    
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
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

    wp_mail( $to, $subject, $message, $headers );

    // Confirmation client
    $client_subject = __( 'Your quote request at ISPAG', 'ispag-crm' );
    $client_message = __( 'Hello, we have received your request for the project: ', 'ispag-crm' ) . $project;
    wp_mail( $email, $client_subject, $client_message, $headers );

    wp_redirect( esc_url_raw( add_query_arg( 'status', 'success', wp_get_referer() ) ) );
    exit;
}


/* ==========================================================================
   6. MULTILINGUISME (POLYLANG) & LOGOS
   ========================================================================== */

add_filter( 'generate_logo', 'ispag_multilingual_logo_url' );
add_filter( 'generate_mobile_header_logo', 'ispag_multilingual_logo_url' ); 

function ispag_multilingual_logo_url( $logo_url ) {
    if ( function_exists( 'pll_current_language' ) ) {
        $lang = pll_current_language( 'slug' );

        $logo_fr = home_url() . '/wp-content/uploads/2024/06/Logo_ISPAG_CMYK_F_web.png';
        $logo_de = home_url() . '/wp-content/uploads/2026/07/Logo_ISPAG_RGB_D.png';

        if ( strpos( $lang, 'de' ) !== false ) {
            return $logo_de;
        } elseif ( strpos( $lang, 'fr' ) !== false ) {
            return $logo_fr;
        }
    }
    return $logo_url;
}

add_filter( 'wp_get_attachment_image_attributes', 'ispag_fix_logo_srcset', 10, 3 );

function ispag_fix_logo_srcset( $attr, $attachment, $size ) {
    if ( function_exists( 'pll_current_language' ) && isset( $attr['class'] ) && strpos( $attr['class'], 'is-logo-image' ) !== false ) {
        $lang = pll_current_language( 'slug' );
        if ( strpos( $lang, 'de' ) !== false ) {
            unset( $attr['srcset'] );
        }
    }
    return $attr;
}


/* ==========================================================================
   7. GESTION DES DÉPARTEMENTS UTILISATEUR & SIDEBARS GLOBALES
   ========================================================================== */

function ispag_init_user_department() {
    global $user_department;

    if ( ! empty( $user_department ) ) {
        return $user_department;
    }

    $user_department = 'vaulruz_ispag';
    $current_user_id = get_current_user_id();
    $saved_user_department = get_user_meta( $current_user_id, 'ispag_user_department', true );
    $source_from_url_or_get = false;

    if ( ! empty( $_GET['department'] ) ) {
        $user_department = sanitize_text_field( $_GET['department'] );
        $source_from_url_or_get = true;
    } elseif ( ! empty( $_GET['user_departement'] ) ) {
        $user_department = sanitize_text_field( $_GET['user_departement'] );
        $source_from_url_or_get = true;
    } elseif ( get_query_var( 'department' ) ) {
        $user_department = sanitize_text_field( get_query_var( 'department' ) );
        $source_from_url_or_get = true;
    } elseif ( get_query_var( 'user_departement' ) ) {
        $user_department = sanitize_text_field( get_query_var( 'user_departement' ) );
        $source_from_url_or_get = true;
    }
    else {
    //     if ( ! empty( $_COOKIE['user_departement'] ) ) {
    //         $user_department = sanitize_text_field( $_COOKIE['user_departement'] );
    //     } else
        if ( ! empty( $saved_user_department ) ) {
            $user_department = $saved_user_department;
        }
    }

    // if ( $source_from_url_or_get && ! headers_sent() ) {
    //     setcookie( 'user_departement', $user_department, time() + 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
    // }

    return $user_department;
}
add_action( 'template_redirect', 'ispag_init_user_department', 1 );

function ispag_add_global_contact_sidebar() {
    global $user_department;
    ispag_get_template( 'ispag-create-contact-sidebar', [] );
    echo '<input type="hidden" name="user_departement" id="user_departement" value="' . esc_attr( $user_department ) . '">';
}
add_action( 'wp_footer', 'ispag_add_global_contact_sidebar' );


/* ==========================================================================
   8. ENREGISTREMENT DES ZONES DE WIDGETS
   ========================================================================== */

function ispag_register_crm_sidebar() {
    register_sidebar( array(
        'name'          => __( 'CRM Sidebar', 'ispag-crm' ),
        'id'            => 'ispag-crm-widgt-sidebar',
        'description'   => __( 'Zone de widgets dédiée au CRM.', 'ispag-crm' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'ispag_register_crm_sidebar' );