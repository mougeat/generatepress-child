<?php
/**
 * Template Name: ISPAG Company Detail
 * Template Post Type: page
 * Description: Affiche le détail d'une entreprise dans l'interface CRM d'ISPAG.
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
if ( ! class_exists( 'ISPAG_Crm_Company_Repository' ) ) {
    // Affichage d'une erreur technique si les dépendances manquent
    echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
    echo '<div class="ispag-error-message">' . __( 'Technical error: Required CRM classes are missing.', 'ispag-crm' ) . '</div>';
    echo '</main></div>';
    get_footer();
    return;
}

$company_types_options = [
    'Prospect' => __('Prospect', 'ispag-crm'),
    'Partner'  => __('Partner', 'ispag-crm'),
    'Reseller' => __('Reseller', 'ispag-crm'),
    'Vendor'   => __('Vendor', 'ispag-crm'),
    'Engineer' => __('Engineer', 'ispag-crm'),
    'Other'    => __('Other', 'ispag-crm'),
];
// Transformation en format "Prospect:Prospect;Partner:Partner;..."
$options_string = [];
foreach ( $company_types_options as $key => $display ) {
    $options_string[] = $key . ':' . $display;
}
$type_source_options = implode( ';', $options_string );

// 2. Initialisation du Repository
$repository = new ISPAG_Crm_Company_Repository();
if ( class_exists( 'ISPAG_Revenue_Stats' ) ) {
    $revenue_stats = new ISPAG_Revenue_Stats();
}

CONST NB_TRANSACTIONS_RIGHT = 5;
// 3. Récupération de l'ID VIAG de l'URL
// NOTE: Vous devez avoir une règle de réécriture qui mappe l'ID de l'URL (/company/46390/)
// à une variable de requête personnalisée comme 'ispag_viag_id' (ou 'viag_id').
// Si 'viag_id' fonctionne, utilisez 'viag_id'.
$company_viag_id = get_query_var( 'company_id' ); 

// Fallback pour tester ou si le query_var n'est pas enregistré
if ( empty( $company_viag_id ) ) {
    global $wp_query;
    // Essaie d'utiliser 'viag_id' qui est souvent le nom donné dans les rewrite rules
    $company_viag_id = $wp_query->query_vars['company_id'] ?? 0;
}


// 4. Chargement des données de l'entreprise via le Repository
$company = $repository->get_company_by_viag_id( $company_viag_id );


// 5. Vérification des données et affichage de l'erreur
if ( empty( $company ) ) {
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <header class="page-header">
                <h1 class="page-title"><?php _e( 'Détail de l\'Entreprise', 'ispag-crm' ); ?></h1>
            </header>
            <div class="ispag-error-message">
                <?php 
                printf( 
                    __( 'Company data is missing or not found for ID VIAG: %s', 'ispag-crm' ), 
                    esc_html( $company_viag_id ) 
                ); 
                ?>
            </div>
        </main>
    </div>
    <?php
    get_footer();
    return;
}

// ----------------------------------------------------
// 6. Préparation des variables (Extraction des propriétés de l'objet $company)
// ----------------------------------------------------
$company_id              = absint( $company->Id );
$company_name            = esc_html( $company->company_name ?? '' );
$company_viag_id         = esc_html( $company->viag_id ?? '' );
$company_type            = esc_html( $company->type ?? '' );
$isIngenieur             = esc_html( $company->isIngenieur ?? '' );
// Simulation de la récupération des autres méta-données pour le template
// NOTE: En production, vous auriez probablement une méthode pour charger TOUTES les métadonnées ici.
$company_phone           = esc_html( $company->phone ?? '' );
$company_address         = esc_html( $company->address ?? '' );
$company_postal_code     = esc_html( $company->postal_code ?? '' );
$company_city            = esc_html( $company->city ?? '' );
$company_country         = esc_html( $company->country ?? '' );
$company_domain          = esc_html( $company->compagny_domain ?? __('N/A', 'ispag-crm') );; // Exemple statique
$favicon                 = $company->favicon ?? null;
$initials                = $company->initials ?? null;
$company_meta_owner      = 1; // Exemple ID
$company_meta_type       = 'installateur'; // Exemple
$owner_options_js        = '...'; // Liste d'options JSON
$type_options_js         = '...'; // Liste d'options JSON
$company_priority_level  = esc_html( $company->priority_level ?? '' );
$company_prio_options    = 'A:A;B:B;C:C';
$link_contact_list       = home_url( '/contact-list/' ); // URL de la page de liste des contacts
$link_new_contact        = home_url( '/add-contact/' ); // URL du formulaire d'ajout de contact
$link_new_project        = home_url( '/add-project/' ); // URL du formulaire d'ajout de projet
$transactions_list_full  = []; // Liste complète des transactions (devrait être chargée ici)
$associated_contacts_list_full = $company->associated_contacts_list_full; // Liste complète des contacts (devrait être chargée ici)
$last_activity_date       = $company->last_contact_date ? date_i18n( 'd.m.Y', strtotime( $company->last_contact_date ) ) : __('N/A', 'ispag-crm');

// ⚠️ Assurez-vous que la classe Deals Repository existe.
if ( class_exists( 'ISPAG_Crm_Deals_Repository' ) ) {
    $deals_repository = new ISPAG_Crm_Deals_Repository();
    // On charge la liste et on l'affecte à la variable destinée au template
    $transactions_list_full = $deals_repository->get_projects_by_company( $company_viag_id );
} 

// ⚠️ À FAIRE : Charger les contacts de la même manière
// $associated_contacts_list_full = []; // Remplacer par l'appel au Repository de Contacts
 

// ⚠️ Assurez-vous que la classe Deals Repository existe.
if ( class_exists( 'ISPAG_Note_Manager' ) ) {
    $note_repository = new ISPAG_Note_Repository();
    $note_renderer = new ISPAG_Note_Renderer();
    $activity_detail = $note_repository->get_activities_for_entity('company', $company_viag_id);
    // error_log('-> activity_detail ', printf($activity_detail, true));

    // On charge la liste et on l'affecte à la variable destinée au template
    $notes_list_full = $note_renderer->render_activities_list( $activity_detail);
} else {
    // Sinon, on s'assure qu'elle est un tableau vide pour éviter les erreurs dans la vue.
    $notes_list_full = '<p>' . __( 'No registered activity', 'ispag-crm' ) . '</p>';
}

$company_ids_arr[] = $company_viag_id;
$company_names_arr[] = $company_name;

$company_ids   = implode(',', $company_ids_arr);
$company_names = implode(',', $company_names_arr);

// $contact_ids_arr = [];
// $contact_names_arr = [];
// foreach ($associated_contacts_list_full as $contact) {
       
//     if ( $contact && !empty($contact->display_name) ) {
//         $contact_ids_arr[]   = $contact->ID ;
//         // On retire les virgules éventuelles du nom pour ne pas casser le split JS
//         $contact_names_arr[] = str_replace(',', ' ', $contact->display_name); 
//     }
// }
// // On transforme les tableaux en chaînes propres
// $contact_ids   = implode(',', $contact_ids_arr);
// $contact_names = implode(',', $contact_names_arr);


//**** Contacts */
// Création des tableaux pour les attributs data
$contact_ids_arr = [];
$contact_names_arr = [];
$contact_emails_arr = [];
$contact_phones_arr = [];

if (!empty($associated_contacts_list_full) && is_array($associated_contacts_list_full)) {
    foreach ($associated_contacts_list_full as $contact_obj) {
        if (is_object($contact_obj)) {
            // On gère les deux cas de figure pour l'ID (ID ou Id)
            $id = isset($contact_obj->ID) ? $contact_obj->ID : ($contact_obj->Id ?? 0);
            
            $contact_ids_arr[]    = $id;
            $contact_names_arr[]  = str_replace(',', ' ', $contact_obj->display_name ?? 'Inconnu');
            $contact_emails_arr[] = $contact_obj->email ?? '';
            $contact_phones_arr[] = $contact_obj->phone ?? '';
        }
    }
}

$contact_ids    = implode(',', $contact_ids_arr);
$contact_names  = implode(',', $contact_names_arr);
$contact_emails = implode(',', $contact_emails_arr);
$contact_phones = implode(',', $contact_phones_arr);


$deal_ids_arr = [];
$deal_names_arr = [];

foreach ($transactions_list_full as $transaction) {
       
    if ( $transaction && !empty($transaction->project_name) ) {
        $deal_ids_arr[]   = $transaction->deal_group_ref ;
        // On retire les virgules éventuelles du nom pour ne pas casser le split JS
        $deal_names_arr[] = str_replace(',', ' ', $transaction->project_name); 
    }
}
// On transforme les tableaux en chaînes propres
$deal_ids   = implode(',', $deal_ids_arr);
$deal_names = implode(',', $deal_names_arr);



// --- RÉCUPÉRATION DU PROPRIÉTAIRE ET FILTRAGE DES USERS ---
$current_owner_id = 0;
$current_owner_name = __('Non assigné', 'ispag-crm');
$target_dept = 'vaulruz_ispag'; 
$key = ISPAG_Crm_Contact_Constants::USER_DEPARTMENT;
$companies_owner_table = ISPAG_Crm_Company_Constants::TABLE_COMPANY_OWNER;

// 1. Chercher le propriétaire ACTUEL (status = 'active')
$owner_entry = $wpdb->get_row( $wpdb->prepare(
    "SELECT user_id FROM {$companies_owner_table} 
     WHERE company_id = %d 
     AND department_key = %s 
     AND status = 'active'", // Filtre crucial pour l'historique
    $company_viag_id,
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
    $current_owner_name = __('Non assigné', 'ispag-crm');
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
// ----------------------------------------------------
// 7. Création et Extraction des variables
// ----------------------------------------------------

// On met toutes les variables nécessaires dans un tableau pour l'extraction (bonne pratique)
$template_args = compact(
    'company', 
    'company_id', 
    'company_name', 
    'company_viag_id',
    'company_type',
    'isIngenieur',
    // ... toutes les autres variables ...
    'contact_names','contact_names', 'target_dept',
    'company_phone', 'company_city', 'company_country', 
    'company_address', 'company_postal_code',
    'company_domain', 'favicon', 'company_meta_owner', 
    'company_priority_level', 'company_meta_type', 'owner_options_js', 'type_options_js',
    'link_contact_list', 'link_new_contact', 'link_new_project',
    'transactions_list_full', 'associated_contacts_list_full'
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


        <div class="ispag-detail-container ispag-company-detail" data-company-id="<?php echo absint($company_id); ?>">
            
            <div class="ispag-left-panel">
                <div class="ispag-card ispag-header-card">
                    <div class="ispag-profile-pic <?php echo ($favicon) ? 'has-favicon' : ''; ?>">
                        <?php 
                        if ($favicon) {
                            // Afficher l'icône :
                            echo '<img src="' . $favicon . '" alt="Favicon" style="width:32px; height:32px;">';
                        } else {
                            // Afficher les deux premières lettres du nom de l'entreprise
                            // $initials = strtoupper( substr( $company_name, 0, 1 ) . substr( $company_name, strpos($company_name, ' ') + 1, 1 ) );
                            echo esc_html( $initials ); 
                        }
                        
                        ?>
                    </div>
                    <div class="ispag-header-info">
                        <input type="hidden" id="hidden_company_name"  value="<?php echo $company_name; ?>"/>
                        <h4>
                            <?php echo $company_name; ?>
                            
                        </h4>
                        <p 
                            class="ispag-editable-field" 
                            data-type="text" 
                            data-name="compagny_domain" 
                            data-value="<?php echo esc_attr( $company_domain ); ?>"
                        >
                            <?php echo $company_domain; ?>
                            <span class="edit-icon">✏️</span>
                        </p>
                        <p 
                            class="ispag-editable-field" 
                            data-type="text" 
                            data-name="viag_id" 
                            data-value="<?php echo esc_attr( $company_viag_id ); ?>"
                        >
                            <?php echo $company_viag_id; ?>
                            <span class="edit-icon">✏️</span>
                        </p>
                    </div>
                </div>
                <div class="ispag-actions-bar">
                    <?php 
                        $actions['company_ids']       = $company_ids;
                        $actions['company_names']     = $company_names;
                        $actions['user_id']           = $user_id;
                        $actions['contact_ids']       = $contact_ids;
                        $actions['contact_names']     = $contact_names;
                        $actions['contact_emails']    = $contact_emails;
                        $actions['contact_phones']    = $contact_phones;
                        $actions['deal_ids']          = $deal_ids;
                        $actions['deal_names']        = $deal_names;

                        $actions['project_nums']      = $project_nums;
                        $actions['closing_dates']     = $closing_dates;
                        $actions['total_excl_vats']   = $total_excl_vats;

                        $actions['target_dept']       = $target_dept;
                                                

                        // Appelle le template et lui passe les données
                        ispag_get_template( 'action-bar', [ 'actions' => $actions ] ); 
                    ?>
                    
                </div>
                <div class="ispag-card ispag-key-info">
                    <h5><?php _e( 'Key information', 'ispag-crm' ); ?></h5>
                    <dl class="ispag-key-info-list">

                        <dt><?php _e( 'Is engineer', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="checkbox" 
                            data-department-id="<?php echo $target_dept; ?>"
                            data-title="<?php _e( 'Is engineer', 'ispag-crm' ); ?>"
                            data-name="isIngenieur" 
                            data-value="<?php echo esc_attr( $isIngenieur ); ?>"
                        >
                            <?php 
                                // Si la valeur est 1 (ou true), on affiche le ✅, sinon on affiche "Non" (ou rien)
                                echo ( $isIngenieur == 1 ) ? '✅' : '❌'; 
                            ?>
                            <span class="edit-icon">✏️</span>
                        </dd>
                        
                        <dt><?php _e( 'Phone', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="text" 
                            data-department-id="<?php echo $target_dept; ?>"
                            data-company-id="<?php echo $company_viag_id; ?>"
                            data-name="phone" 
                            data-value="<?php echo esc_attr( $company_phone ); ?>"
                        >
                            <?php echo $company_phone; ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e( 'Type', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="select" 
                            data-department-id="<?php echo $target_dept; ?>"
                            data-company-id="<?php echo $company_viag_id; ?>"
                            data-name="type" 
                            data-options='<?php echo $type_source_options; ?>'
                        >
                            <?php echo $company_type; ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e( 'Priority level', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="select" 
                            data-department-id="<?php echo $target_dept; ?>"
                            data-company-id="<?php echo $company_viag_id; ?>"
                            data-name="<?php echo ISPAG_Crm_Company_Constants::PRIORITY_LEVEL; ?>" 
                            data-value="<?php echo esc_attr( $company_priority_level ); ?>"
                            data-options="<?php echo esc_attr($company_prio_options); ?>"
                        >
                            <?php 
                            $priority = strtoupper($company_priority_level);
                            
                            $badge_configs = [
                                'A' => ['color' => '#d63031', 'label' => 'A - ' . __( 'High', 'ispag-crm' )],
                                'B' => ['color' => '#e67e22', 'label' => 'B - ' . __( 'Medium', 'ispag-crm' )],
                                'C' => ['color' => '#2980b9', 'label' => 'C - ' . __( 'Low', 'ispag-crm' )],
                            ];

                            if ( isset($badge_configs[$priority]) ) : 
                                $config = $badge_configs[$priority];
                                ?>
                                <span class="ispag-status-badge" style="background-color: <?php echo $config['color']; ?>; color: #fff;">
                                    <?php echo esc_html($config['label']); ?>
                                </span>
                            <?php else : ?>
                                <span class="ispag-status-badge" style="background-color: #f0f0f0; color: #999; border: 1px dashed #ccc; font-weight: normal;">
                                    <?php echo __('None', 'ispag-crm'); ?>
                                </span>
                            <?php endif; ?>

                            <span class="edit-icon" style="margin-left: 5px; cursor: pointer; opacity: 0.6;">✏️</span>
                        </dd>
                        
                        <dt><?php _e( 'Company Owner', 'ispag-crm' ); ?></dt>
                        <dd 
                            class="ispag-editable-field" 
                            data-type="select" 
                            data-name="department_owner"
                            data-company-id="<?php echo esc_attr($company_viag_id); ?>"
                            data-department-id="<?php echo esc_attr($target_dept); ?>"
                            data-value="<?php echo esc_attr($current_owner_id); ?>"
                            data-options='<?php echo esc_attr($users_list_source); ?>'
                        >
                            <?php echo esc_html($current_owner_name); ?>
                            <span class="edit-icon" style="margin-left: 5px; cursor: pointer; opacity: 0.6;">✏️</span>
                        </dd>

                        <dt><?php _e( 'Last contacted', 'ispag-crm' ); ?></dt>
                        <dd >
                            <?php echo $last_activity_date; ?>
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
                            <div data-company-id="<?php echo $company_id; ?>" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; font-size: 14px;">
                                
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
                                <?php echo $revenue_stats->render_perspective_cards( $company_viag_id, 'company' ); ?>
                            </div>
                        <?php endif; ?>

                        
                        <div 
                            id="gemini-ai-summary-<?php echo absint($user_id); ?>" 
                            class="ispag-ai-placeholder"
                            data-contact-id="<?php echo absint($user_id); ?>"
                            data-company-id="<?php echo absint($company_viag_id); ?>"
                        >
                            <?php ispag_get_template( 'ai-loader', [ null ] ); ?>
                        </div>
                         
                    </div>
                    
                    <div id="ispag-tab-activity" class="ispag-tab-pane">
                        <?php echo $notes_list_full; ?>
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
                        data-company-id="<?php echo absint($company_viag_id); ?>"
                        >
                            <?php ispag_get_template( 'ai-loader', [ null ] ); ?>
                        </div>
                        
                        <div 
                        id="gemini-ai-actions-<?php echo absint($user_id); ?>" 
                        class="ispag-ai-actions-placeholder"
                        data-contact-id="<?php echo absint($user_id); ?>"
                        data-company-id="<?php echo absint($company_viag_id); ?>"
                        >
                            <?php ispag_get_template( 'ai-loader', [ null ] ); ?>
                        </div>
                    </div>
                    
                </div>
            </div> 
            
            <div class="ispag-right-panel">
                <div class="ispag-card ispag-company-card">
                    <h5>
                        <?php _e( 'Contacts', 'ispag-crm' ); ?> (<?php echo count($associated_contacts_list_full); ?>) 
                        <span id="open-add-contact-modal" 
                            style="font-size: 12px; color: #007bff; cursor: pointer;" 
                            data-company-id="<?php echo absint($company_id); ?>">
                            + <?php _e( 'Add', 'ispag-crm' ); ?>
                        </span>
                    </h5>
                    <?php 
                        $date_format = get_option( 'date_format' );
                        // Définition de la constante si elle n'est pas déjà définie dans un fichier de configuration
                        if ( ! defined( 'NB_TRANSACTIONS_RIGHT' ) ) {
                            define( 'NB_TRANSACTIONS_RIGHT', 5 );
                        }
                        $nb_contact = 0;
                        foreach ( $associated_contacts_list_full as $contact ): 
                        $nb_contact++;
    
                        // 2. Vérifier si on a atteint la limite après l'incrémentation
                        // Si $nb_transactions est strictement supérieur à la limite, on arrête la boucle.
                        if ( $nb_contact > NB_TRANSACTIONS_RIGHT ) {
                            break; // Arrête l'exécution de la boucle foreach
                        }
                        $contact_deal_url = home_url( '/contact/' . $contact->ID . '/' );
                        ?>
                        <div class="ispag-card" style="font-size: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong>
                                    <a href="<?php echo $contact_deal_url; ?>"><?php echo $contact->display_name; ?></a>
                                </strong>
                                <span 
                                    class="ispag-remove-association" 
                                    data-action="remove-contact-from-company"
                                    data-contact-id="<?php echo absint($contact->ID); ?>"
                                    data-company-id="<?php echo absint($company_viag_id); ?>"
                                    title="<?php esc_attr_e( 'Remove association', 'ispag-crm' ); ?>"
                                    style="color: #e74c3c; cursor: pointer;"
                                >
                                    <span class="dashicons dashicons-trash"></span>
                                </span>
                            </div>
                            <p style="margin: 5px 0 0;"><?php _e( 'Function', 'ispag-crm' ); ?>: <?php echo esc_html( $contact->lead_function ?? '' ); ?></p>
                            <p style="margin: 5px 0 0;"><?php _e( 'Last contact', 'ispag-crm' ); ?>: <?php echo date_i18n(  get_option('date_format'), strtotime( $contact->last_contact_date) ); ?></p>
                            
                        </div>
                    <?php 
                    endforeach; 
                    
                    if ( $nb_contact > NB_TRANSACTIONS_RIGHT ) {
                        
                        $company_url = home_url( '/listes-des-contacts/?filter_company=' . $company_viag_id . '/' );
                        ?>
                        <a href="<?php echo $company_url; ?>" class="ispag-button-link"><?php _e( 'Show all contacts', 'ispag-crm' ); ?></a>
                    <?php
                    }
                    ?>
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
                        ?>
                        <div class="ispag-transaction-item">
                            <strong>
                                <a href="<?php echo esc_url($transaction->get_deal_detail_link()); ?>"><?php echo $transaction->project_name; ?></a>
                            </strong>
                            <p><?php _e( 'Amount', 'ispag-crm' ); ?>: <?php echo number_format( (float)$transaction->total_excl_vat, 2, '.', '\'' ) . ' CHF'; ?></p>
                            <p><?php _e( 'Closing date', 'ispag-crm' ); ?>: <?php echo date_i18n( get_option('date_format'), strtotime( $transaction->closing_date ) ); ?></p>
                            <p><?php _e( 'Transaction phase', 'ispag-crm' ); ?>: 
                                <span class="ispag-status-badge" style="background-color: <?php echo esc_attr($transaction->stage_color); ?>; color: #fff;">
                                    <?php echo esc_html($transaction->stage_label); ?>
                                </span>
                            </p>
                        </div>
                    <?php 
                    endforeach; 
                    
                    if ( $nb_transactions > NB_TRANSACTIONS_RIGHT ) {
                        $company_deal_url = home_url( '/deals-list/?search=company-' . $company->viag_id );
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

<?php 
ispag_get_template( 'deal-reason-for-rejection-modal', [] ); 
get_footer(); // CONSERVÉ : Fin du thème
?>