<?php
/**
 * Functions and definitions for ISPAG CRM Child Theme / Plugin integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sécurité
}

/* ==========================================================================
   0. MISE À JOUR DEPUIS UNE BRANCHE GITHUB
   Jeton + branche : wp-config.php ou Outils → Updates ISPAG (« main » par défaut). Inactif sans jeton.
   ========================================================================== */
require_once get_stylesheet_directory() . '/inc/class-ispag-github-updater.php';
ISPAG_GitHub_Updater::theme( get_stylesheet(), 'mougeat/generatepress-child' );

// Pages basées sur les modèles du thème : créées à l'activation du thème ou via Outils → Pages ISPAG (jamais automatiquement)
require_once get_stylesheet_directory() . '/inc/class-ispag-page-installer.php';
require_once get_stylesheet_directory() . '/inc/class-ispag-entity-summary.php';
ISPAG_Page_Installer::register( 'Thème ISPAG', require get_stylesheet_directory() . '/install/pages.php' );
add_action( 'after_switch_theme', function () { ISPAG_Page_Installer::on_activation( 'Thème ISPAG' ); } );

/* ==========================================================================
   1. CHARGEMENT DES TRADUCTIONS (TEXTDOMAIN)
   ========================================================================== */

/**
 * Traductions FR / DE (fichiers dans languages/ : <domaine>-fr_FR.mo, <domaine>-de_DE.mo ; générés par tools/i18n/build.py d'ISPAG Project Manager).
 * Toute variante de langue du site est couverte : fr_CH, fr_BE… utilisent le français ; de_CH, de_DE_formal, de_AT… l'allemand.
 * Les textes de base sont en anglais : sans fichier pour la langue du site, l'anglais est affiché.
 * Le chargement est refait quand la langue change (Polylang la fixe après le chargement des plugins, switch_to_locale…).
 */
if (!function_exists('ispag_i18n_register_dir')) {
    /** Dossiers languages/ enregistrés par les plugins et le thème ISPAG. */
    function ispag_i18n_dirs($add = null) {
        static $dirs = [];
        if ($add !== null && !in_array($add, $dirs, true)) $dirs[] = $add;
        return $dirs;
    }
    function ispag_i18n_register_dir($dir) {
        ispag_i18n_dirs($dir);
        if (!did_action('ispag_i18n_hooked')) {
            do_action('ispag_i18n_hooked');
            add_action('plugins_loaded', 'ispag_i18n_reload', 20);
            add_action('after_setup_theme', 'ispag_i18n_reload', 20);
            add_action('init', 'ispag_i18n_reload', 1);
            add_action('pll_language_defined', 'ispag_i18n_reload', 1);
            add_action('change_locale', 'ispag_i18n_reload', 20);
            add_action('restore_previous_locale', 'ispag_i18n_reload', 20);
        }
    }
    /** (Re)charge les traductions pour la langue courante, uniquement si elle a changé depuis le dernier chargement. */
    function ispag_i18n_reload() {
        static $done = null;
        $locale   = determine_locale();
        $fallback = ['fr' => 'fr_FR', 'de' => 'de_DE'][substr($locale, 0, 2)] ?? '';
        $signature = $locale . '|' . implode(',', ispag_i18n_dirs()); // langue + dossiers connus (le thème s'enregistre après les plugins)
        if ($done === $signature) return;
        if ($done !== null) {
            foreach (ispag_i18n_dirs() as $dir) {
                foreach ((array) glob(rtrim($dir, '/\\') . '/*-{fr_FR,de_DE}.mo', GLOB_BRACE) as $mo) {
                    unload_textdomain(preg_replace('/-(fr_FR|de_DE)\.mo$/', '', basename($mo)), true);
                }
            }
        }
        $done = $signature;
        if ($fallback === '') return;
        foreach (ispag_i18n_dirs() as $dir) {
            foreach ((array) glob(rtrim($dir, '/\\') . '/*-' . $fallback . '.mo') as $mo) {
                $domain = basename($mo, '-' . $fallback . '.mo');
                $exact  = rtrim($dir, '/\\') . '/' . $domain . '-' . $locale . '.mo';
                load_textdomain($domain, is_readable($exact) ? $exact : $mo);
            }
        }
    }
}

ispag_i18n_register_dir(get_stylesheet_directory() . '/languages');


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
        ob_start();
        include( $template );
        return ob_get_clean(); // <-- On capture et on retourne le contenu du template
    }

    return ''; // On retourne une chaîne vide si le fichier n'existe pas
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
        <h2 style='color: #E11D48;'>New tank request</h2>
        <p><strong>Client:</strong> {$company}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Projet:</strong> {$project}</p>
        <p><strong>Phone:</strong> {$phone}</p>
        <hr style='border: 0; border-top: 1px solid #eee;'>
        <h3>Technical Specifications</h3>
        <ul>
            <li>Dimensions: Ø {$dia}mm x H {$height}mm</li>
            <li>Volume: {$vol} Litres</li>
            <li>Pression: {$pressure} bar</li>
            <li>Material: {$material}</li>
            <li>Isolation: {$insulation}</li>
            <li>Soudure sur site: {$site_w}</li>
        </ul>
        <p style='font-size: 10px; color: #999;'>Sent from the ISPAG online configurator.</p>
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
   5b. DÉTAIL D'UN PROJET : VUE « 3 COLONNES » POUR TOUS
   ==========================================================================
   Les pages « details-du-projet » (FR) et « projektdetails » (DE) affichaient l'ancienne vue (shortcode [ispag_detail]).
   Elles utilisent maintenant le modèle « ISPAG Project Detail Viewer » (page-project-detail-viewer.php, 3 colonnes),
   quel que soit le chemin d'accès : /project-detail/<id>, /details-du-projet/?deal_id=<id>, liste, notifications…
   Pour revenir à l'ancienne vue : add_filter( 'ispag_project_detail_three_columns', '__return_false' );
   ========================================================================== */
add_filter( 'template_include', function ( $template ) {
    if ( ! is_page( array( 'details-du-projet', 'projektdetails' ) ) ) {
        return $template;
    }
    if ( ! apply_filters( 'ispag_project_detail_three_columns', true ) ) {
        return $template;
    }
    $three_columns = locate_template( 'page-project-detail-viewer.php' );
    return $three_columns ? $three_columns : $template;
}, 20 );


/* ==========================================================================
   6. GESTION DES DÉPARTEMENTS UTILISATEUR & SIDEBARS GLOBALES
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
    echo ispag_get_template( 'ispag-create-contact-sidebar', [] );
    echo '<input type="hidden" name="user_departement" id="user_departement" value="' . esc_attr( $user_department ) . '">';
}
add_action( 'wp_footer', 'ispag_add_global_contact_sidebar' );

// Panneau « Créer une entreprise » : uniquement pour les utilisateurs autorisés (voir ISPAG_Crm_Company_Creator::can_create)
function ispag_add_global_company_sidebar() {
    if ( class_exists( 'ISPAG_Crm_Company_Creator' ) && ISPAG_Crm_Company_Creator::can_create() ) {
        echo ispag_get_template( 'ispag-create-company-sidebar', [] );
    }
}
add_action( 'wp_footer', 'ispag_add_global_company_sidebar' );


/* ==========================================================================
   7. ENREGISTREMENT DES ZONES DE WIDGETS
   ========================================================================== */

function ispag_register_crm_sidebar() {
    register_sidebar( array(
        'name'          => __( 'CRM Sidebar', 'ispag-crm' ),
        'id'            => 'ispag-crm-widgt-sidebar',
        'description'   => __( 'Widget area dedicated to the CRM.', 'ispag-crm' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'ispag_register_crm_sidebar' );

// Hauteur réellement visible sur mobile (barres du navigateur) -> variable CSS --ispag-vh, utilisée par les modales
add_action( 'wp_footer', function () {
    ?>
    <script>
    (function () {
        function setVh() {
            var h = (window.visualViewport && window.visualViewport.height) || window.innerHeight;
            document.documentElement.style.setProperty('--ispag-vh', Math.round(h) + 'px');
            var bar = document.getElementById('wpadminbar');
            var barH = (bar && getComputedStyle(bar).position === 'fixed') ? bar.offsetHeight : 0;
            document.documentElement.style.setProperty('--ispag-adminbar', barH + 'px');
        }
        setVh();
        window.addEventListener('resize', setVh);
        window.addEventListener('orientationchange', setVh);
        if (window.visualViewport) { window.visualViewport.addEventListener('resize', setVh); }
    })();
    </script>
    <?php
}, 5 );
