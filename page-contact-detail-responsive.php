<?php
/**
 * Template Name: ISPAG Contact Detail responsive
 * Template Post Type: page
 * Description: Affiche le détail d'un contact dans l'interface CRM d'ISPAG.
 */

// // S'assurer que la classe existe avant d'enregistrer l'action
// if ( class_exists( 'ISPAG_Contact_Note_Manager' ) ) {
//     // Le modal sera ajouté au wp_footer si et seulement si ce template est chargé.
//     add_action( 'wp_footer', array( new ISPAG_Contact_Note_Manager(), 'render_note_modal_html' ) );

// } 

get_header(); // CONSERVÉ : Début du thème

// ====================================================
// --- DÉPENDANCES ET INITIALISATION DES DONNÉES (Contrôleur dans la Vue) ---
// ====================================================

// 1. Assurez-vous que les classes nécessaires sont chargées
if ( ! class_exists( 'ISPAG_Crm_Contacts_Repository' ) ) {
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


// 5. Vérification des données et affichage de l'erreur
if ( empty( $contact ) ) {
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

// error_log(print_r($contact, true));

$contact_id              = absint( $contact->ID );
$contact_name            = esc_html( $contact->display_name ?? '' );
$contact_mail            = esc_html( $contact->email ?? 'ERREUR' );
$contact_lead_function   = esc_html( $contact->lead_function ?? '' ); // Déjà utilisé pour la recherche, mais on le garde pour l'affichage
// Simulation de la récupération des autres méta-données pour le template
// NOTE: En production, vous auriez probablement une méthode pour charger TOUTES les métadonnées ici.
$contact_phone           = esc_html( $contact->phone ?? '' );
$company_address         = esc_html( $contact->address ?? '' );
$company_postal_code     = esc_html( $contact->postal_code ?? '' );
$company_city            = esc_html( $contact->city ?? '' );
$company_country         = esc_html( $contact->country ?? '' );
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
$contact_priority_level  = esc_html( $contact->priority_level ?? '' );
$contact_prio_options    = 'A:A;B:B;C:C';
$link_contact_list       = home_url( '/contact-list/' ); // URL de la page de liste des contacts
$link_new_contact        = home_url( '/add-contact/' ); // URL du formulaire d'ajout de contact
$link_new_project        = home_url( '/add-project/' ); // URL du formulaire d'ajout de projet
$transactions_list_full  = []; // Liste complète des transactions (devrait être chargée ici)
$single_company_id       =  $contact->ispag_company_id ;
if ( $single_company_id > 0 ) {
    $associated_companies_list_full = [ $single_company_id ];
} else {
    $associated_companies_list_full = [];
}

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
        // error_log('-> activity_detail ', printf($activity_detail, true));

        // On charge la liste et on l'affecte à la variable destinée au template
        $notes_list_full = $note_renderer->render_activities_list( $activity_detail);
    } else {
        // Sinon, on s'assure qu'elle est un tableau vide pour éviter les erreurs dans la vue.
        $notes_list_full = '<p>' . __( 'No registered activity', 'ispag-crm' ) . '</p>';
    }

$company_ids_arr = [];
$company_names_arr = [];


$company_repo = new ISPAG_Crm_Company_Repository();

foreach ($associated_companies_list_full as $company_id) {
    $company = $company_repo->get_company_by_viag_id($company_id);
    
    if ( $company && !empty($company->company_name) ) {
        $company_ids_arr[]   = $company_id;
        // On retire les virgules éventuelles du nom pour ne pas casser le split JS
        $company_names_arr[] = str_replace(',', ' ', $company->company_name); 
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
    'transactions_list_full', 'associated_contacts_list_full', 'last_contact_date'
    // ... etc.
);

// Rendre toutes les variables disponibles dans la portée du template
extract( $template_args );


// ====================================================
// --- DÉBUT DE LA VUE (Contenu HTML) ---
// ====================================================
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="ispag-container-detail ispag-contact-detail" data-contact-id="<?php echo absint($user_id); ?>">
            
            <aside class="ispag-column-left">
                <div class="ispag-card">
                    <div class="ispag-contact-header">
                        <div class="ispag-contact-avatar <?php echo ($favicon) ? 'has-favicon' : ''; ?>">
                            <?php 
                            if ($favicon) {
                                echo '<img src="' . $favicon . '" alt="Favicon" style="width:32px; height:32px;">';
                            } else {
                                $initials = strtoupper( substr( $contact_name, 0, 1 ) . substr( $contact_name, strpos($contact_name, ' ') + 1, 1 ) );
                                echo esc_html( $initials ); 
                            }
                            ?>
                        </div>
                        <div class="ispag-contact-info-top">
                            <input type="hidden" id="hidden_contact_name" value="<?php echo $contact_name; ?>"/>
                            <h1><?php echo $contact_name; ?></h1>
                            <p class="ispag-editable-field" 
                               data-type="text" 
                               data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_FUNCTION; ?>" 
                               data-contact-ids="<?php echo absint($user_id); ?>"
                               data-contact-names="<?php echo ($contact_name); ?>"
                               data-value="<?php echo esc_attr( $contact_lead_function ); ?>">
                                <?php echo $contact_lead_function; ?>
                                <span class="edit-icon">✏️</span>
                            </p>
                        </div>
                    </div>

                    <div class="ispag-info-section">
                        <h3><?php _e( 'Key information', 'ispag-crm' ); ?></h3>
                        
                        <div class="ispag-info-row">
                            <span class="ispag-info-label"><?php _e( 'Email', 'ispag-crm' ); ?></span>
                            <span class="ispag-info-value ispag-editable-field" data-type="email" data-name="user_email" data-value="<?php echo esc_attr( $contact_mail ); ?>">
                                <?php echo esc_html( $contact_mail ); ?> <span class="edit-icon">✏️</span>
                            </span>
                        </div>

                        <div class="ispag-info-row">
                            <span class="ispag-info-label"><?php _e( 'Phone', 'ispag-crm' ); ?></span>
                            <span class="ispag-info-value ispag-editable-field" data-type="text" data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_PHONE; ?>" data-value="<?php echo esc_attr( $contact_phone ); ?>">
                                <?php echo $contact_phone; ?> <span class="edit-icon">✏️</span>
                            </span>
                        </div>

                        <div class="ispag-info-row">
                            <span class="ispag-info-label"><?php _e( 'Lead status', 'ispag-crm' ); ?></span>
                            <span class="ispag-info-value ispag-editable-field" data-type="select" data-name="<?php echo ISPAG_Crm_Contact_Constants::META_LEAD_STATUS; ?>" data-value="<?php echo esc_attr($contact->lead_status_badge); ?>" data-options="<?php echo esc_attr( ISPAG_Crm_Contacts_Repository::get_lead_status_for_inline_edit() ); ?>">
                                <?php echo $contact->lead_status_badge; ?> <span class="edit-icon">✏️</span>
                            </span>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="ispag-column-right">
                
                <div class="ispag-quick-actions">
                    <?php 
                        $actions['company_ids']       = $company_ids;
                        $actions['company_names']     = $company_names;
                        $actions['user_id']           = $user_id;
                        $actions['contact_name']      = $contact_name;
                        $actions['deal_ids']          = $deal_ids;
                        $actions['deal_names']        = $deal_names;
                        $actions['project_nums']      = $project_nums;
                        $actions['closing_dates']     = $closing_dates;
                        $actions['total_excl_vats']   = $total_excl_vats;

                        ispag_get_template( 'action-bar', [ 'actions' => $actions ] ); 
                    ?>
                </div>

                <ul class="ispag-nav-tabs">
                    <li class="active"><a href="#ispag-tab-activity"><?php esc_html_e( 'Activities', 'ispag-crm' ); ?></a></li>
                    <li><a href="#ispag-tab-about"><?php esc_html_e( 'About', 'ispag-crm' ); ?></a></li>
                    <li><a href="#ispag-tab-deal"><?php esc_html_e( 'Transactions', 'ispag-crm' ); ?></a></li>
                    <li><a href="#ispag-tab-intelligence"><?php esc_html_e( 'Intelligence', 'ispag-crm' ); ?></a></li>
                </ul>

                <div class="ispag-tabs-content">
                    
                    <div id="ispag-tab-activity" class="ispag-tab-pane active">
                        <?php echo $notes_list_full; ?>
                    </div>
                    
                    <div id="ispag-tab-about" class="ispag-tab-pane">
                        <div class="ispag-card">
                            <h5><?php _e( 'Company Profile', 'ispag-crm' ); ?></h5>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                                <div><strong><?php _e( 'Street adress', 'ispag-crm' ); ?> :</strong> <?php echo esc_html($company_address); ?></div>
                                <div><strong><?php _e( 'City', 'ispag-crm' ); ?> :</strong> <?php echo esc_html($company_city); ?></div>
                            </div>
                        </div>
                        <?php if ( isset( $revenue_stats ) ) : ?>
                            <div class="ispag-card">
                                <?php echo $revenue_stats->render_perspective_cards( $user_id, 'contact' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="ispag-tab-deal" class="ispag-tab-pane">
                        <?php ispag_get_template( 'deal-table', [ 'transactions' => $transactions_list_full ] ); ?>
                    </div>

                    <div id="ispag-tab-intelligence" class="ispag-tab-pane">
                         <div id="gemini-ai-summary-<?php echo absint($user_id); ?>" class="ispag-ai-placeholder" data-contact-id="<?php echo absint($user_id); ?>">
                            <p style="text-align: center; color: #999; padding: 20px;"><span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> <?php _e( 'Loading AI summary...', 'ispag-crm' ); ?></p>
                         </div>
                    </div>
                </div>
            </main>

            <aside class="ispag-column-right-sidebar">
                <div class="ispag-card">
                    <h5>
                        <?php _e( 'Company', 'ispag-crm' ); ?> (<?php echo count($associated_companies_list_full); ?>) 
                        <span id="open-add-company-modal" style="font-size: 12px; color: #007bff; cursor: pointer;" data-contact-id="<?php echo absint($user_id); ?>">+ <?php _e( 'Add', 'ispag-crm' ); ?></span>
                    </h5>
                    <?php 
                    if (class_exists( 'ISPAG_Crm_Company_Repository' ) ){
                        $company_repo = new ISPAG_Crm_Company_Repository();
                        foreach ($associated_companies_list_full as $company_id) {
                            $company = $company_repo->get_company_by_viag_id($company_id);
                            if($company) {
                                $company_app_url = home_url( '/company/' . $company->viag_id . '/' );
                                ?>
                                <div class="ispag-sidebar-item">
                                    <strong><a href="<?php echo esc_url($company_app_url); ?>"><?php echo $company->company_name; ?></a></strong>
                                    <p><?php echo $company->city; ?></p>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>

                <div class="ispag-card">
                    <h5><?php _e( 'Transactions', 'ispag-crm' ); ?> (<?php echo count($transactions_list_full); ?>)</h5>
                    <div class="ispag-transactions-mini-list">
                        <?php 
                        $nb_trans = 0;
                        foreach ( $transactions_list_full as $transaction ): 
                            if ( ++$nb_trans > 5 ) break;
                            $current_stage_label = !empty($transaction->stage_label) ? $transaction->stage_label : __('Non défini', 'ispag-crm');
                            $current_stage_color = !empty($transaction->stage_color) ? $transaction->stage_color : '#cccccc';
                        ?>
                            <div class="ispag-transaction-item-mini">
                                <strong><a href="<?php echo esc_url($transaction->get_deal_detail_link()); ?>"><?php echo $transaction->project_name; ?></a></strong>
                                <p><?php echo number_format( (float)$transaction->total_excl_vat, 2, '.', '\'' ) . ' CHF'; ?></p>
                                <span class="ispag-status-badge" style="background-color: <?php echo esc_attr($current_stage_color); ?>;"><?php echo esc_html($current_stage_label); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>

        </div> </main> </div> <div id="ispag-modal-container"></div>

<?php 
get_footer(); 
?>