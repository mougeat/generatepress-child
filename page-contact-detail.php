<?php
/**
 * Template Name: ISPAG Contact Detail
 * Template Post Type: page
 * Description: Affiche le détail d'un contact dans l'interface CRM d'ISPAG.
 */

// // S'assurer que la classe existe avant d'enregistrer l'action
// if ( class_exists( 'ISPAG_Contact_Note_Manager' ) ) {
//     // Le modal sera ajouté au wp_footer si et seulement si ce template est chargé.
//     add_action( 'wp_footer', array( new ISPAG_Contact_Note_Manager(), 'render_note_modal_html' ) );

// } 

// get_header(); // CONSERVÉ : Début du thème

// ====================================================
// --- DÉPENDANCES ET INITIALISATION DES DONNÉES (Contrôleur dans la Vue) ---
// ====================================================

// 1. Assurez-vous que les classes nécessaires sont chargées
if ( ! class_exists( 'ISPAG_Crm_Contacts_Repository' ) ) {
    get_header();
    // Affichage d'une erreur technique si les dépendances manquent
    echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
    echo '<div class="ispag-error-message">' . __( 'Technical error: Required CRM classes are missing.', 'ispag-crm' ) . '</div>';
    echo '</main></div>';
    get_footer();
    return;
}
 
// 2. Initialisation du Repository
$repository = new ISPAG_Crm_Contacts_Repository();
if ( class_exists( 'ISPAG_Crm_Company_Modal' ) ) :
    new ISPAG_Crm_Company_Modal();  
endif;

if ( class_exists( 'ISPAG_Revenue_Stats' ) ) {
    $revenue_stats = new ISPAG_Revenue_Stats();
}

CONST NB_TRANSACTIONS_RIGHT = 5;
// 3. Récupération de l'ID VIAG de l'URL
// NOTE: Vous devez avoir une règle de réécriture qui mappe l'ID de l'URL (/company/46390/)
// à une variable de requête personnalisée comme 'ispag_viag_id' (ou 'viag_id').
// Si 'viag_id' fonctionne, utilisez 'viag_id'.
$user_id = get_query_var( 'user_id' ); 

// Fallback pour tester ou si le query_var n'est pas enregistré
if ( empty( $user_id ) ) {
    global $wp_query;
    // Essaie d'utiliser 'viag_id' qui est souvent le nom donné dans les rewrite rules
    $user_id = $wp_query->query_vars['user_id'] ?? 0;
}


// 4. Chargement des données de l'entreprise via le Repository
$contact = $repository->get_contact_by_id( $user_id );
// error_log(print_r($contact, true));

// 5. Vérification des données et affichage de l'erreur
if ( empty( $contact ) ) {
    get_header();
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <header class="page-header">
                <h1 class="page-title"><?php _e( 'Contact details', 'ispag-crm' ); ?></h1>
            </header>
            <div class="ispag-error-message">
                <?php 
                printf( 
                    __( 'Contact data are missing or not found for ID : %s', 'ispag-crm' ), 
                    esc_html( $user_id ) 
                ); 
                ?>
            </div>
        </main>
    </div>
    <?php
    get_footer();
    return;
}

$date_format = get_option( 'date_format' );

// ----------------------------------------------------
// 6. Préparation des variables (Extraction des propriétés de l'objet $company)
// ----------------------------------------------------



$contact_id              = absint( $contact->ID );
$contact_name            = esc_html( $contact->display_name ?? '' );
$contact_mail            = esc_html( $contact->email ?? 'ERREUR' );
$contact_lead_function   = esc_html( $contact->lead_function ?? '—' ); // Déjà utilisé pour la recherche, mais on le garde pour l'affichage
// Simulation de la récupération des autres méta-données pour le template
// NOTE: En production, vous auriez probablement une méthode pour charger TOUTES les métadonnées ici.
$contact_phone           = esc_html( $contact->phone ?? '' );

$avatar_url              = $contact->avatar_url;
$linkedin_url            = $contact->linkedin_page ?? '—' ;
$company_domain          = ''; // Exemple statique
$favicon                 = ''; // Exemple vide
$contact_meta_owner      = $contact->crm_owner_id; // Exemple ID
$owner_data              = get_userdata( $contact_meta_owner );
$contact_owner           = $owner_data->display_name;
$last_contact_date       = $contact->last_contact_date ? date_i18n( 'd.m.Y', strtotime( $contact->last_contact_date ) ) : __('N/A', 'ispag-crm');   
$last_contact_source       = esc_html( $contact->last_contact_date->type ?? '');
$company_meta_type       = 'installateur'; // Exemple
$owner_options_js        = '...'; // Liste d'options JSON
$type_options_js         = '...'; // Liste d'options JSON
$contact_priority_level  = esc_html( $contact->priority_level ?? '—' );
$priority_level_badge    =  $contact->priority_level_badge ?? '—';
$contact_prio_options    = 'A:A;B:B;C:C';
$link_contact_list       = home_url( '/contact-list/' ); // URL de la page de liste des contacts
$link_new_contact        = home_url( '/add-contact/' ); // URL du formulaire d'ajout de contact
$link_new_project        = home_url( '/add-project/' ); // URL du formulaire d'ajout de projet
$transactions_list_full  = []; // Liste complète des transactions (devrait être chargée ici)
// Extraction de tous les IDs d'entreprises associés
if ( ! empty( $contact->companies ) && is_array( $contact->companies ) ) {
    // wp_list_pluck extrait uniquement la colonne 'Id' de votre tableau d'objets
    $associated_companies_list_full = wp_list_pluck( $contact->companies, 'viag_id' );

} else {
    // Sécurité : si aucune entreprise n'est trouvée
    $associated_companies_list_full = [];
}

// Pour vos besoins d'affichage spécifique (ex: le premier ID pour un lien principal)
$single_company_id = ! empty( $associated_companies_list_full ) ? $associated_companies_list_full[0] : 0;

// ⚠️ Assurez-vous que la classe Deals Repository existe.
if ( class_exists( 'ISPAG_Crm_Deals_Repository' ) ) {
    $deals_repository = new ISPAG_Crm_Deals_Repository();
    // On charge la liste et on l'affecte à la variable destinée au template
    $transactions_list_full = $deals_repository->get_projects_by_contact( $user_id );
} else {
    // Sinon, on s'assure qu'elle est un tableau vide pour éviter les erreurs dans la vue.
    $transactions_list_full = [];
}

// ⚠️ À FAIRE : Charger les contacts de la même manière
$associated_contacts_list_full = []; // Remplacer par l'appel au Repository de Contacts
 

    // ⚠️ Assurez-vous que la classe Deals Repository existe.
    if ( class_exists( 'ISPAG_Note_Manager' ) ) {
        $note_repository = new ISPAG_Note_Repository();
        $note_renderer = new ISPAG_Note_Renderer();
        $activity_detail = $note_repository->get_activities_for_entity('contact', $user_id);
        

        // On charge la liste et on l'affecte à la variable destinée au template
        $notes_list_full = $note_renderer->render_activities_list( $activity_detail);
    } else {
        // Sinon, on s'assure qu'elle est un tableau vide pour éviter les erreurs dans la vue.
        $notes_list_full = '<p>' . __( 'No registered activity', 'ispag-crm' ) . '</p>';
    }

$company_ids_arr = [];
$company_names_arr = [];


$company_repo = new ISPAG_Crm_Company_Repository();
$primary_company_priority = '';
$primary_company_owner = __('Not assigned', 'ispag-crm');

foreach ($associated_companies_list_full as $index => $company_id) {
    $company = $company_repo->get_company_by_viag_id($company_id);
    
    if ( $company && !empty($company->company_name) ) {
        $company_ids_arr[]   = $company_id;
        // On retire les virgules éventuelles du nom pour ne pas casser le split JS
        $company_names_arr[] = str_replace(',', ' ', $company->company_name); 
        // On récupère la priorité de la première entreprise associée
        if ($index === 0) {
            $primary_company_priority = $company->priority_level ?? '';

            // --- RÉCUPÉRATION DU PROPRIÉTAIRE ET FILTRAGE DES USERS ---
            $current_owner_id = 0;
            $target_dept = 'vaulruz_ispag'; 
            $key = ISPAG_Crm_Contact_Constants::USER_DEPARTMENT;
            $companies_owner_table = ISPAG_Crm_Company_Constants::TABLE_COMPANY_OWNER;

            // 1. Chercher le propriétaire ACTUEL (status = 'active')
            $owner_entry = $wpdb->get_row( $wpdb->prepare(
                "SELECT user_id FROM {$companies_owner_table} 
                WHERE company_id = %d 
                AND department_key = %s 
                AND status = 'active'", // Filtre crucial pour l'historique
                $company_id,
                $target_dept
            ));

            if ( $owner_entry ) {
                $current_owner_id = absint( $owner_entry->user_id );
                $user_info = get_userdata( $current_owner_id );
                if ( $user_info ) {
                    $primary_company_owner = $user_info->display_name;
                }
            } else {
                // Optionnel : s'assurer que les variables sont vides si aucun owner actif
                $current_owner_id = 0;
                $primary_company_owner = __('Not assigned', 'ispag-crm');
            }

            $company_repo = new ISPAG_Crm_Company_Repository();
            $company = $company_repo->get_company_by_viag_id($company_id);

            //Adresse de l'entreprise principale
            $company_address         = esc_html( $company->address ?? '' );
            $company_postal_code     = esc_html( $company->postal_code ?? '' );
            $company_city            = esc_html( $company->city ?? '' );
            $company_country         = esc_html( $company->country ?? '' );

            
        }
    }
}
// On transforme les tableaux en chaînes propres
$company_ids   = implode(',', $company_ids_arr);
$company_names = implode(',', $company_names_arr);

$deal_ids_arr = [];
$deal_names_arr = [];
$project_num_arr = [];
$total_excl_vat_arr = [];
$closing_date_arr = [];
foreach ($transactions_list_full as $transaction) {
       
    if ( $transaction && !empty($transaction->project_name) ) {
        $deal_ids_arr[]   = $transaction->deal_group_ref ;
        $deal_names_arr[] = str_replace(',', ' ', $transaction->project_name); 
        $project_num_arr[]   = $transaction->project_num ;
        $total_excl_vat_arr[]   = $transaction->total_excl_vat ;
        $closing_date_arr[]   = $transaction->closing_date ;
    }
}
// On transforme les tableaux en chaînes propres
$deal_ids   = implode(',', $deal_ids_arr);
$deal_names = implode(',', $deal_names_arr);
$project_nums = implode(',', $project_num_arr);
$total_excl_vats = implode(',', $total_excl_vat_arr);
$closing_dates = implode(',', $closing_date_arr);

// --- RÉCUPÉRATION DU PROPRIÉTAIRE ET FILTRAGE DES USERS ---
$current_owner_id = 0;
$current_owner_name = __('Not assigned', 'ispag-crm');
$target_dept = 'vaulruz_ispag'; 
$key = ISPAG_Crm_Contact_Constants::USER_DEPARTMENT;
$contacts_owner_table = ISPAG_Crm_Contact_Constants::TABLE_CONTACT_OWNER;

// 1. Chercher le propriétaire ACTUEL (status = 'active')
$owner_entry = $wpdb->get_row( $wpdb->prepare(
    "SELECT user_id FROM {$contacts_owner_table} 
     WHERE contact_id = %d 
     AND department_key = %s 
     AND status = 'active'", // Filtre crucial pour l'historique
    $user_id,
    $target_dept
));

if ( $owner_entry ) {
    $current_owner_id = absint( $owner_entry->user_id );
    $user_info = get_userdata( $current_owner_id );
    if ( $user_info ) {
        $current_owner_name = $user_info->display_name;
    }
} else {
    // Optionnel : s'assurer que les variables sont vides si aucun owner actif
    $current_owner_id = 0;
    $current_owner_name = __('Not assigned', 'ispag-crm');
}
// 2. Préparer la liste des utilisateurs (Logique inchangée mais propre)
$users = get_users( array( 
    'fields'     => array( 'ID', 'display_name' ), 
    'orderby'    => 'display_name',
    'meta_query' => array(
        array(
            'key'     => $key, 
            'value'   => $target_dept, 
            'compare' => '=' 
        ),
    ),
) );

$users_list_arr = array( '0:' . __('Selectionner...', 'ispag-crm') );
foreach ( $users as $u ) {
    $users_list_arr[] = $u->ID . ':' . $u->display_name;
}
$users_list_source = implode( ';', $users_list_arr );

// ----------------------------------------------
// Selection du role principal sur wordpress
// ----------------------------------------------

$roles_to_exclude = [
    'administrator',
    'editor',
    'supplier',
    'translator',
];

// Vérifier si l'utilisateur *qui effectue l'action* (l'éditeur) est un administrateur.
// Si l'utilisateur actuel n'est PAS un administrateur, nous limitons davantage les options.
if ( ! current_user_can( 'administrator' ) ) {
    
    // Rôles internes à ISPAG qui ne peuvent être attribués que par un administrateur.
    $ispag_roles_to_exclude = [
        'membre_ispag',
        'vente_ispag',
        'achat_ispag',
        'ispag_commercial',
    ];
    
    // Fusionner la liste d'exclusion des rôles critiques et des rôles internes à ISPAG.
    $roles_to_exclude = array_merge( $roles_to_exclude, $ispag_roles_to_exclude );
    
    // Optionnel : s'assurer qu'il n'y a pas de doublons, même si array_merge devrait suffire.
    $roles_to_exclude = array_unique( $roles_to_exclude );
}

// 1. Récupérer tous les rôles WordPress disponibles
global $wp_roles;
if ( ! isset( $wp_roles ) ) {
    $wp_roles = new WP_Roles();
}
// Utilise get_names() qui renvoie [cle => Nom Affiché]
$all_roles = $wp_roles->get_names(); 

// 2. Préparer le tableau des options avec le rôle par défaut "none"
$role_options_data = [
    'none' => __('(No selected role)', 'ispag-crm')
];

// 2b. Filtrage des rôles
foreach ($all_roles as $key => $display_name) {
    if ( ! in_array( $key, $roles_to_exclude ) ) {
        // Le rôle est pertinent et n'est pas dans la liste d'exclusion
        $role_options_data[$key] = $display_name;
    }
}
 
// 3. Formater les options au format brut "key:Display Name; key:Display Name..."
$formatted_options = [];
foreach ($role_options_data as $key => $display_name) {
    // S'assurer que la chaîne est sûre pour l'attribut HTML.
    // L'échappement HTML est critique car les données vont dans un attribut data-options.
    $safe_key = esc_attr($key);
    $safe_display_name = esc_attr($display_name); 
    
    // Pour une sécurité accrue : on retire tout point-virgule ou deux-points 
    // qui pourraient casser le format brut, même si les noms de rôles WP standard sont sûrs.
    $safe_display_name = str_replace( [':', ';'], '', $safe_display_name ); 
    
    $formatted_options[] = $safe_key . ':' . $safe_display_name;
}

// Joindre les éléments avec un point-virgule (;)
$role_data_options = implode(';', $formatted_options);

// 4. Récupérer le rôle principal actuel de l'utilisateur (Logique inchangée)
$user_info = get_userdata($user_id);
$current_role_key = 'none';
$user_role_display = __('Not defined', 'ispag-crm');

if ($user_info && !empty($user_info->roles)) {
    $current_role_key = array_shift($user_info->roles);
    
    if (isset($wp_roles->role_names[$current_role_key])) {
        $user_role_display = translate_user_role($wp_roles->role_names[$current_role_key]);
    } else {
        $user_role_display = $current_role_key;
    }
}
// ---------------------------------
// Ignorer le suivis du contact
// ---------------------------------
$is_ignored = get_user_meta( $contact->ID, ISPAG_Crm_Contact_Constants::META_HEALTH_CHECK_IGNORE, true );
// Assurez-vous que la valeur est '0' si elle est vide (non définie)
if ( empty( $is_ignored ) ) {
    $is_ignored = '0';
}

// 3. Déterminer le texte à afficher
$status_text = ( $is_ignored == '1' ) ? __('Ignored', 'ispag-crm') :  __('Not ignored', 'ispag-crm');


// Exemple de code à placer avant ton HTML pour récupérer l'explication
global $wpdb;
$table_note = ISPAG_Note_Manager::TABLE_NOTE;
$last_system_note = $wpdb->get_var($wpdb->prepare(
    "SELECT content FROM {$table_note}
     WHERE contact_id = %d AND type = 'SYSTEM' 
     ORDER BY created_at DESC LIMIT 1",
    $contact->ID
));

// Message par défaut si aucune note n'est trouvée
$explanation_lifecycle = $last_system_note ? strip_tags($last_system_note) : __("Aucune donnée d'automatisation disponible.", "creation-reservoir");

// ----------------------------------------------------
// 7. Création et Extraction des variables
// ----------------------------------------------------

// On met toutes les variables nécessaires dans un tableau pour l'extraction (bonne pratique)
$template_args = compact(
    'company', 
    'company_id', 
    'company_names', 
    'company_ids',
    'contact_mail',
    // ... toutes les autres variables ...
    'contact_phone', 'company_city', 'company_country', 
    'company_address', 'company_postal_code',
    'company_domain', 'favicon', 'contact_meta_owner', 
    'company_meta_type', 'owner_options_js', 'type_options_js',
    'link_contact_list', 'link_new_contact', 'link_new_project',
    'linkedin_url', 'linkedin_key',
    'transactions_list_full', 'associated_contacts_list_full', 'last_contact_date'
    // ... etc.
);

// Rendre toutes les variables disponibles dans la portée du template
extract( $template_args );


// ====================================================
// --- DÉBUT DE LA VUE (Contenu HTML) ---
// ====================================================
// Filtrer le titre de la page dynamiquement
add_filter('pre_get_document_title', function($title) use ($contact_name) {
    if (!empty($contact_name)) {
        $site_name = get_bloginfo('name');
        return $contact_name . ' | ' . $site_name;
    }
    return $title;
}, 999);
wp_enqueue_media();
get_header();

?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">


        <div class="ispag-detail-container ispag-contact-detail" data-contact-id="<?php echo absint($user_id); ?>">
            
            <div class="ispag-left-panel">
                <div class="ispag-card ispag-header-card">
                    <div class="ispag-header-top-row">
                        <div class="ispag-profile-pic <?php echo ($favicon) ? 'has-favicon' : ''; ?> ispag-popover-field ispag-avatar-trigger"
                            data-field-type="avatar"  
                            data-contact-id="<?php echo absint($user_id); ?>"
                            style="cursor: pointer;"
                            title="<?php _e('Modifier l\'avatar', 'ispag-crm'); ?>">
                            <span class="current-value">
                                <?php 
                                if ( $avatar_url ){ ?>
                                    <img src="<?php echo esc_url( $avatar_url ); ?>" 
                                        alt="<?php echo esc_attr( $contact->display_name ); ?>"
                                        class="ispag-avatar-img"> 
                                <?php
                                } else {
                                    // Afficher les deux premières lettres du nom de l'entreprise
                                    $initials = strtoupper( substr( $contact_name, 0, 1 ) . substr( $contact_name, strpos($contact_name, ' ') + 1, 1 ) );
                                    echo esc_html( $initials ); 
                                }
                                
                                ?>
                            </span>
                        </div>
                        <div class="ispag-header-info">
                            <input type="hidden" id="hidden_contact_name"  value="<?php echo $contact_name; ?>"/>
                            
                            <h2 class="ispag-contact-title-fullname">
                                <span 
                                    class="ispag-editable-field" 
                                    data-type="text" 
                                    data-name="first_name" 
                                    data-contact-ids="<?php echo absint($user_id); ?>"
                                    data-value="<?php echo esc_attr( $contact->first_name ?? '' ); ?>"
                                    placeholder="<?php _e('Prénom', 'ispag-crm'); ?>"
                                >
                                    <?php echo !empty($contact->first_name) ? esc_html($contact->first_name) : '<span class="ispag-placeholder">Prénom</span>'; ?>
                                </span>

                                <span 
                                    class="ispag-editable-field" 
                                    data-type="text" 
                                    data-name="last_name" 
                                    data-contact-ids="<?php echo absint($user_id); ?>"
                                    data-value="<?php echo esc_attr( $contact->last_name ?? '' ); ?>"
                                    placeholder="<?php _e('Nom', 'ispag-crm'); ?>"
                                >
                                    <?php echo !empty($contact->last_name) ? esc_html($contact->last_name) : '<span class="ispag-placeholder">Nom</span>'; ?>
                                </span>
                                <span class="edit-icon">✏️</span>
                            </h2>
                            
                            <p
                                class="ispag-popover-field" 
                                data-field-type="text" 
                                data-contact-id="<?php echo absint($user_id); ?>"
                                data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_FUNCTION; ?>" 
                                data-value="<?php echo esc_attr( $contact_lead_function ?: '' ); ?>"
                            >
                                <span class="current-value"><?php echo $contact_lead_function ?: '—'; ?></span>
                                
                            </p>
                            <p 
                                class="ispag-popover-field" 
                                data-type="email" 
                                data-contact-id="<?php echo absint($user_id); ?>"
                                data-name="user_email" 
                                data-value="<?php echo esc_attr( $contact_mail ?: ''  ); ?>"
                            >
                                <?php echo esc_html( $contact_mail ); ?>
                                
                            </p>
                        </div>
                    </div>
                    <div class="ispag-actions-bar">

                        <?php 
                            $actions['company_ids']       = $company_ids;
                            $actions['company_names']     = $company_names;
                            $actions['contact_ids']       = $user_id;
                            $actions['contact_names']     = $contact_name;
                            $actions['contact_emails']    = $contact_emails;
                            $actions['contact_phones']    = $contact_phones;
                            $actions['deal_ids']          = $deal_ids;
                            $actions['deal_names']        = $deal_names;

                            $actions['project_nums']      = $project_nums;
                            $actions['closing_dates']     = $closing_dates;
                            $actions['total_excl_vats']   = $total_excl_vats;
                                                    
    
                            // Appelle le template et lui passe les données
                            ispag_get_template( 'action-bar', [ 'actions' => $actions ] ); 
                        ?>
                    </div>  
                    <div class="ispag-field-group">
                        <input type="button" 
                            id="meeting-preparation" 
                            class="button button-secondary btn-meeting-prep" 
                            name="meeting_preparation" 
                            data-contact-id="<?php echo $user_id; ?>"
                            value="<?php esc_attr_e( 'Prepare meeting', 'ispag-crm' ); ?>">
                    </div> 
                    
                </div>
                
                <div class="ispag-card ispag-key-info">
                    <h5><?php _e( 'Key information', 'ispag-crm' ); ?></h5>
                    <dl class="ispag-key-info-list">
                        
 
                        <dt><?php _e( 'Ignore health reminder', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="checkbox" 
                            data-title="<?php _e( 'Ignore health reminder', 'ispag-crm' ); ?>"
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::META_HEALTH_CHECK_IGNORE; ?>" 
                            data-value="<?php echo esc_attr( $is_ignored ); ?>"
                        >
                            <?php echo esc_html( $status_text ); ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e( 'Phone number', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-popover-field" 
                            data-field-type="phone" 
                            data-contact-id="<?php echo absint($user_id); ?>"
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_PHONE; ?>" 
                            data-value="<?php echo esc_attr( $contact_phone ); ?>"
                        >
                            <span class="current-value"><?php echo $contact_phone ?: '—'; ?></span>
                            
                        </dd>


                        <dt><?php _e( 'Birthday', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-popover-field" 
                            data-field-type="date" 
                            data-contact-id="<?php echo absint($user_id); ?>"
                            data-name="billing_birthdate" 
                            data-value="<?php echo esc_attr( get_user_meta( $user_id, 'billing_birthdate', true ) ); ?>"
                        >
                            <span class="current-value">
                                <?php 
                                if ( ! empty( $contact->birthday ) && $contact->birthday !== 'Non renseignée' ) {
                                    echo esc_html( $contact->birthday );
                                    if ( ! empty( $contact->age ) ) {
                                        echo ' <span style="color: #666; font-size: 0.9em;">(' . esc_html( $contact->age ) . ')</span>';
                                    }
                                } else {
                                    echo '<span style="color: #999; font-style: italic;">' . __( 'Non renseignée', 'ispag-crm' ) . '</span>';
                                }
                                ?>
                            </span>
                        </dd>

                        

                        <dt><?php _e( 'Role', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field user-role" 
                            data-type="select" 
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::META_USER_ROLE; ?>" 
                            data-value="<?php echo esc_attr($current_role_key); ?>" 
                            data-options="<?php echo esc_attr($role_data_options); ?>"
                        >
                            <?php echo esc_html($user_role_display); ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e( 'Priority level', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="select" 
                            data-contact-id="<?php echo $user_id; ?>"
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::PRIORITY_LEVEL; ?>" 
                            data-value="<?php echo esc_attr( $contact_priority_level ); ?>"
                            data-options="<?php echo esc_attr($contact_prio_options); ?>"
                        >
                            <?php echo $priority_level_badge; ?>
                           
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <?php
                        $status_reason = get_user_meta( $user_id, ISPAG_Crm_Contact_Constants::META_LEAD_STATUS_REASON, true );
                        ?>

                        <dt>
                            <?php _e( 'Lead status', 'ispag-crm' ); ?>
                            <span class="info-icon" style="cursor:help; font-size: 0.8em; margin-left: 5px;" title="<?php echo esc_attr__($contact->status_description, 'ispag-crm'); ?>">ⓘ</span>
                        </dt>
                        <dd 
                            
                            data-type="select" 
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_STATUS; ?>" 
                            data-value="<?php echo esc_attr($contact->lead_status_badge); ?>" 
                            data-options="<?php echo esc_attr( ISPAG_Crm_Contacts_Repository::get_lead_status_for_inline_edit() ); ?>"
                        >
                            <?php echo $contact->lead_status_badge; ?>
                            
                        </dd>
 
                        <dt>
                            <?php _e( 'Lifecycle phase', 'ispag-crm' ); ?>
                            <span class="info-icon" style="cursor:help; font-size: 0.8em; margin-left: 5px;" title="<?php echo esc_attr__($contact->lifecycle_description, 'ispag-crm'); ?>">ⓘ</span>
                        </dt>
                        <dd 
                            
                            data-type="select" 
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LIFECYCLE_PHASE; ?>" 
                            data-value="<?php echo esc_attr($contact->lifecycle_phase_badge); ?>" 
                            data-options="<?php echo esc_attr( ISPAG_Crm_Contacts_Repository::get_lifecycle_phases_for_inline_edit() ); ?>"
                        >
                            <?php echo $contact->lifecycle_phase_badge; ?>
                            
                        </dd>

                        <dt>
                            <?php _e( 'Linkedin page', 'ispag-crm' ); ?>
                            
                        </dt>
                        
                        <dd 
                            class="ispag-popover-field" 
                            data-field-type="text" 
                            data-contact-id="<?php echo absint($user_id); ?>"
                            data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_LINKEDIN_PAGE; ?>" 
                            data-value="<?php echo esc_attr( $contact->linkedin_url ?: ''  ); ?>"
                        >
                            <span class="current-value"><?php echo $contact->linkedin_url ?: '—'; ?></span>
                            
                        </dd>
                        
                        <dt><?php _e( 'Contact owner', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="select" 
                            data-name="department_owner"
                            data-contact-id="<?php echo absint($user_id); ?>"
                            data-department-id="<?php echo esc_attr($target_dept); ?>"
                            data-value="<?php echo esc_attr($current_owner_id); ?>"
                            data-options="<?php echo esc_attr($users_list_source); ?>"
                        >
                            <?php echo $current_owner_name; ?>
                        </dd>

                        <dt><?php _e( 'Last contacted', 'ispag-crm' ); ?></dt>
                        <dd >
                           <?php echo $last_contact_date; ?>
                        </dd>
                        
                    </dl>
                </div>
            </div> 
            
            <div class="ispag-main-content">
                <div class="ispag-tabs-navigation">
                    <button class="ispag-tab-btn active" data-tab="about">
                        <?php esc_html_e( 'About', 'ispag-crm' ); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="activity">
                        <?php esc_html_e( 'Activities', 'ispag-crm' ); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="deal">
                        <?php esc_html_e( 'Transactions', 'ispag-crm' ); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="intelligence">
                        <?php esc_html_e( 'Intelligence', 'ispag-crm' ); ?>
                    </button>
                </div>
                <div class="ispag-tabs-content">
                    
                    <div id="ispag-tab-about" class="ispag-tab-pane active">
                        
                        <div class="ispag-card">
                            <h5><?php _e( 'Company Profile', 'ispag-crm' ); ?></h5>
                            <div data-contact-id="<?php echo $user_id; ?>" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; font-size: 14px;">
                                
                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Street adress', 'ispag-crm' ); ?> :</strong>
                                    <p>
                                        <span ><?php echo esc_html($company_address); ?></span>
                                    </p>
                                </div>
                                
                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Postal code', 'ispag-crm' ); ?> :</strong>
                                    <p>
                                        <span ><?php echo esc_html($company_postal_code); ?></span>
                                    </p>
                                </div>

                                <div class="ispag-field-container">
                                    <strong><?php _e( 'City', 'ispag-crm' ); ?> :</strong>
                                    <p>
                                        <span ><?php echo esc_html($company_city); ?></span>
                                    </p>
                                </div>
                            </div>

                            

                        </div>
                        <?php if ( isset( $revenue_stats ) ) : ?>
                            <div class="ispag-card ispag-revenue-dashboard">
                                <h5><?php _e( 'Revenue Perspectives', 'ispag-crm' ); ?></h5>
                                <?php echo $revenue_stats->render_perspective_cards( $user_id, 'contact' ); ?>
                            </div>
                        <?php endif; ?>

                        <div 
                            id="gemini-ai-summary-<?php echo absint($user_id); ?>" 
                            class="ispag-ai-placeholder"
                            data-contact-id="<?php echo absint($user_id); ?>"
                        >
                            <?php ispag_get_template( 'ai-loader', [ null ] ); ?>
                        </div>
                        
                        
                        
                    </div>
                    
                    <div id="ispag-tab-activity" class="ispag-tab-pane">
                        <div class="ispag-card">
                            <?php echo $notes_list_full; ?> 
                        </div>
                    </div>
                    
                    <div id="ispag-tab-deal" class="ispag-tab-pane">

                        <h5><?php esc_html_e( 'Transaction information', 'ispag-crm' ); ?></h5>

                        <?php 
                            // Appelle le template et lui passe les données
                            ispag_get_template( 'deal-table', [ 'transactions' => $transactions_list_full ] ); 
                        ?>
                    </div>
                    
                    <div id="ispag-tab-intelligence" class="ispag-tab-pane">
                        <div 
                        id="gemini-ai-profil-<?php echo absint($user_id); ?>" 
                        class="ispag-ai-profil-placeholder"
                        data-contact-id="<?php echo absint($user_id); ?>"
                        >
                            <?php ispag_get_template( 'ai-loader', [ null ] ); ?>
                        </div>
                        
                        <div 
                        id="gemini-ai-actions-<?php echo absint($user_id); ?>" 
                        class="ispag-ai-actions-placeholder"
                        data-contact-id="<?php echo absint($user_id); ?>"
                        >
                            <?php ispag_get_template( 'ai-loader', [ null ] ); ?>
                        </div>
                    </div>
                    
                </div>
            </div> 
            
            <div class="ispag-right-panel">
                <div class="ispag-card ispag-company-card">
                    <h5>
                        <?php _e( 'Company', 'ispag-crm' ); ?> (<?php echo count($associated_companies_list_full); ?>) 
                        <span id="open-add-company-modal"  
                            style="font-size: 12px; color: #007bff; cursor: pointer;" 
                            data-contact-id="<?php echo absint($user_id); ?>">
                            + <?php _e( 'Add', 'ispag-crm' ); ?>
                        </span>
                    </h5>
                    <?php 
                    if (class_exists( 'ISPAG_Crm_Company_Repository' ) ){
                        $company_repo = new ISPAG_Crm_Company_Repository();

                        
                        
                        foreach ($associated_companies_list_full as $company_id) {
                            
                            $company = $company_repo->get_company_by_viag_id($company_id);
                            $company_app_url = home_url( '/company/' . $company->viag_id . '/' );

                            // 1. On récupère le domaine (assure-tu que la propriété est bien 'compagny_domain' ou 'domain')
                            $company_domain = !empty($company->compagny_domain) ? $company->compagny_domain : '';
                            
                            // 2. Génération du favicon via le domaine
                            
                            $favicon = $company->favicon ?? null;
                            // if (!empty($company_domain)) {
                            //     $favicon = "https://www.google.com/s2/favicons?domain=" . esc_attr($company_domain) . "&sz=64";
                            // }

                            // // 3. Calcul des initiales si pas de favicon
                            // $initials = '';
                            // if (empty($favicon)) {
                            //     $name = $company->company_name;
                            //     $words = explode(' ', $name);
                            //     if (count($words) >= 2) {
                            //         $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                            //     } else {
                            //         $initials = strtoupper(substr($name, 0, 2));
                            //     }
                            // }
                            ?>
                            <div class="ispag-card" style="font-size: 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="ispag-mini-profile-pic" >
                                        <?php
                                        if ($favicon) {
                                        ?>
                                            <img src="<?php echo esc_url( $favicon ); ?>" 
                                                alt="<?php echo esc_attr( $company_name ); ?>"
                                                class="ispag-avatar-img"
                                                style="width:20px; height:20px;"> 
                                            <?php
                                            
                                        } else {
                                            // Afficher les deux premières lettres du nom de l'entreprise
                                            // $initials = strtoupper( substr( $company_name, 0, 1 ) . substr( $company_name, strpos($company_name, ' ') + 1, 1 ) );
                                            echo esc_html( $initials ); 
                                        }
                                        ?>
                                        
                                    </div>
                                    <strong>
                                        <a href="<?php echo esc_url($company_app_url); ?>"><?php echo $company->company_name; ?></a>
                                    </strong>
                                    <span 
                                        class="ispag-remove-association" 
                                        data-action="remove-contact-from-company"
                                        data-contact-id="<?php echo absint($user_id); ?>"
                                        data-company-id="<?php echo absint($company->viag_id); ?>"
                                        title="<?php esc_attr_e( 'Remove association', 'ispag-crm' ); ?>"
                                        style="color: #e74c3c; cursor: pointer;"
                                    >
                                        <span class="dashicons dashicons-trash"></span>
                                    </span>
                                </div>
                                <p style="margin: 5px 0 0;"><?php _e( 'City', 'ispag-crm' ); ?>: <?php echo $company->city; ?></p>
                                <p style="margin: 5px 0 0;"><?php _e( 'Phone number', 'ispag-crm' ); ?>: <?php echo $company->phone; ?></p>
                                
                            </div>
                            <?php
                        }
                    }
                    
                    ?>
                    <input type="hidden" id="hidden_company_name"  value="<?php echo $company->company_name; ?>"/>
                </div>

                <div id="ispag-modal-container"></div>

                <div class="ispag-card ispag-transactions-card">
                    <h5>
                        <?php _e( 'Transactions', 'ispag-crm' ); ?> (<?php echo count($transactions_list_full); ?>)
                        <span style="font-size: 12px; color: #007bff; cursor: pointer;"><a href="<?php echo $link_new_project; ?>" target="_blank">+ <?php _e( 'Add', 'ispag-crm' ); ?></a></span>
                    </h5>
                    <?php 
                    // Définition de la constante si elle n'est pas déjà définie dans un fichier de configuration
                    if ( ! defined( 'NB_TRANSACTIONS_RIGHT' ) ) {
                        define( 'NB_TRANSACTIONS_RIGHT', 5 );
                    }
                    $nb_transactions = 0;
                    foreach ( $transactions_list_full as $transaction ): 
                        
                        $nb_transactions++;
    
                        // 2. Vérifier si on a atteint la limite après l'incrémentation
                        // Si $nb_transactions est strictement supérieur à la limite, on arrête la boucle.
                        if ( $nb_transactions > NB_TRANSACTIONS_RIGHT ) {
                            break; // Arrête l'exécution de la boucle foreach
                        }
                        $current_stage_label      = $transaction->stage_label ?? __('Non défini', 'ispag-crm');
                        $current_stage_color      = $transaction->stage_color ?? '#cccccc';

                        // CORRECTION ICI : Le repo injecte stage_label et stage_color
                        $current_stage_label = !empty($transaction->stage_label) ? $transaction->stage_label : __('Non défini', 'ispag-crm');
                        $current_stage_color = !empty($transaction->stage_color) ? $transaction->stage_color : '#cccccc';
                        ?>
                        <div class="ispag-card" style="font-size: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="ispag-transaction-item">
                                    <strong>
                                        <a href="<?php echo esc_url($transaction->get_deal_detail_link()); ?>"><?php echo $transaction->project_name; ?></a>
                                    </strong>
                                    <p><?php _e( 'Amount', 'ispag-crm' ); ?>: <?php echo number_format( (float)$transaction->total_excl_vat, 2, '.', '\'' ) . ' CHF'; ?></p>
                                    <p><?php _e( 'Closing date', 'ispag-crm' ); ?>: <?php echo date_i18n( get_option('date_format'), strtotime( $transaction->closing_date ) ); ?></p>
                                    <p><?php _e( 'Transaction phase', 'ispag-crm' ); ?>: 
                                        <span class="ispag-status-badge" style="background-color: <?php echo esc_attr($current_stage_color); ?>; color: #fff; padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                            <?php echo esc_html($current_stage_label); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php 
                    
                    endforeach; 
                    
                    if ( $nb_transactions > NB_TRANSACTIONS_RIGHT ) {
                        $company_deal_url = home_url( '/deals-list/?search=user-' . $user_id );
                        ?>
                        <a href="<?php echo $company_deal_url; ?>" class="ispag-button-link"><?php _e( 'Show all transactions', 'ispag-crm' ); ?></a>
                    <?php
                    }
                    ?>
                </div>
            </div> 
            
        </div>

    </main>
</div>


<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Données du Contact (depuis PHP)
        const contactPrio = "<?php echo esc_js( trim($contact_priority_level) ); ?>";
        const contactOwner = "<?php echo esc_js( trim($contact_owner) ); ?>";
        
        // Données de l'Entreprise (depuis PHP)
        const companyPrio = "<?php echo esc_js( trim($primary_company_priority) ); ?>";
        const companyOwner = "<?php echo esc_js( trim($primary_company_owner) ); ?>";

        // Traduction du message d'alerte principal
        const alertTitle = "<?php echo esc_js(__('Warning', 'ispag-crm')); ?>";
        const alertHeader = "<?php echo esc_js(__('Warning: Consistency issues detected!', 'ispag-crm')); ?>";

        console.group("ISPAG CRM: Consistency Check");
        console.log("Checking Contact vs Primary Company...");
        
        let errors = [];

        // 1. Contrôle Priorité
        if (companyPrio !== "" && contactPrio !== companyPrio) {
            errors.push("<?php echo esc_js(__('- Priority mismatch:', 'ispag-crm')); ?> " + 
                        "Contact(" + contactPrio + ") vs Company(" + companyPrio + ")");
        }

        // 2. Contrôle Propriétaire (Owner)
        if (companyOwner !== "" && contactOwner !== companyOwner) {
            errors.push("<?php echo esc_js(__('- Owner mismatch:', 'ispag-crm')); ?> " + 
                        "Contact ID(" + contactOwner + ") vs Company ID(" + companyOwner + ")");
        }

        if (errors.length > 0) {
            const alertMsg = alertHeader + "\n\n" + errors.join("\n");
            console.warn(alertMsg);
            // alert(alertMsg);
            ispag_show_modal("Warning", alertMsg);
        } else {
            console.log("✅ All checks passed (Priority & Owner match).");
        }
        
        console.groupEnd();
    });

    /**
     * Affiche une modale à la place d'une alert()
     * @param {string} title - Titre de la modale
     * @param {string} message - Message à afficher
     */
    function ispag_show_modal(title, message) {
        const modal = document.getElementById('ispag-crm-modal');
        document.getElementById('ispag-modal-title').innerText = title;
        document.getElementById('ispag-modal-message').innerText = message;
        
        modal.style.display = 'block';

        // Fermeture (bouton X, bouton fermer, ou clic extérieur)
        const closeElements = modal.querySelectorAll('.ispag-modal-close, .ispag-btn-close');
        closeElements.forEach(el => {
            el.onclick = () => modal.style.display = 'none';
        });

        window.onclick = (event) => {
            if (event.target == modal) modal.style.display = 'none';
        };
    }
</script>



<?php 
ispag_get_template( 'deal-reason-for-rejection-modal', [] ); 
ispag_get_template( 'control-notif-modal', [] ); 
ispag_get_template( 'ispag-popover-modal', [ null ] );
ispag_get_template( 'ispag-sequence-modal', [ null ] );
get_footer(); // CONSERVÉ : Fin du thème 
?>