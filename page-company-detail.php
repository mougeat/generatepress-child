<?php
/**
 * Template Name: ISPAG Company Detail
 * Template Post Type: page
 * Description: Affiche le détail d'une entreprise dans l'interface CRM d'ISPAG.
 */


global $user_department;


// S'assurer que les classes nécessaires sont chargées
if (!class_exists('ISPAG_Crm_Company_Repository')) {
    get_header();
    echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
    echo '<div class="ispag-error-message">' . __('Technical error: Required CRM classes are missing.', 'ispag-crm') . '</div>';
    echo '</main></div>';
    get_footer();
    return;
}


// Options pour le type d'entreprise
if(class_exists('ISPAG_Crm_Lifecycle_Manager')){
    $lifecycle_manager = new ISPAG_Crm_Lifecycle_Manager();
    $company_types_options = $lifecycle_manager->get_standard_lifecycle();

}else{
    $company_types_options = [
        'Prospect' => __('Prospect', 'ispag-crm'),
        'Partner'  => __('Partner', 'ispag-crm'),
        'Reseller' => __('Reseller', 'ispag-crm'),
        'Vendor'   => __('Vendor', 'ispag-crm'),
        'Engineer' => __('Engineer', 'ispag-crm'),
        'Other'    => __('Other', 'ispag-crm'),
    ];
}

// Transformation en format "Prospect:Prospect;Partner:Partner;..."
$options_string = [];
foreach ($company_types_options as $key => $display) {
    $options_string[] = $key . ':' . $display;
}
$type_source_options = implode(';', $options_string);

// Initialisation du Repository
$repository = new ISPAG_Crm_Company_Repository();
if (class_exists('ISPAG_Revenue_Stats')) {
    $revenue_stats = new ISPAG_Revenue_Stats();
}

define('NB_TRANSACTIONS_RIGHT', 5);

// Récupération de l'ID VIAG de l'URL
$company_viag_id = get_query_var('company_id');
if (empty($company_viag_id)) {
    global $wp_query;
    $company_viag_id = $wp_query->query_vars['company_id'] ?? 0;
}

// Chargement des données de l'entreprise
$company = $repository->get_company_by_viag_id($company_viag_id);

// // Récupération des options de coefficients (wpcb_sales_coef*)
// $sales_coef_options = [];
// $all_options = wp_load_alloptions();
// foreach ($all_options as $option_name => $option_value) {
//     if (strpos($option_name, 'wpcb_sales_coef') === 0) {
//         $key = str_replace('wpcb_sales_coef', '', $option_name);
//         $sales_coef_options[$key] = $option_value;
//     }
// }
// $sales_coef_options = array_unique($sales_coef_options);
// sort($sales_coef_options);

// Options pour les types de rabais
$discount_values = array();
if(class_exists('ISPAG_Crm_Discount_Manager')){
    $discount_values = ISPAG_Crm_Discount_Manager::get_standard_discounts(false);
    $discount_values = array_unique($discount_values);
    sort($discount_values);

    $sales_coef_options = ISPAG_Crm_Discount_Manager::get_sales_coef_options();
    $sales_coef_options = array_unique($sales_coef_options);
    sort($sales_coef_options);
}


// // Options pour les coefficients
// $sales_coef_options_string = [];
// foreach ($sales_coef_options as $key => $value) {
//     $sales_coef_options_string[] = $key . ':' . $value;
// }
// $sales_coef_options_string = implode(';', $sales_coef_options_string);

// Vérification des données et affichage de l'erreur
if (empty($company)) {
    get_header();
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <header class="page-header">
                <h1 class="page-title"><?php _e('Company Datas', 'ispag-crm'); ?></h1>
            </header>
            <div class="ispag-error-message">
                <?php
                printf(
                    __('Company data is missing or not found for ID VIAG: %s', 'ispag-crm'),
                    esc_html($company_viag_id)
                );
                ?>
            </div>
        </main>
    </div>
    <?php
    get_footer();
    return;
}

// Préparation des variables
$company_id = absint($company->Id);
$company_name = esc_html($company->company_name ?? '');
$company_viag_id = esc_html($company->viag_id ?? '');
$company_type = esc_html($company->type ?? '');
$isIngenieur = esc_html($company->isIngenieur ?? '');
$is_active = esc_html($company->is_active ?? '');
$company_phone = esc_html($company->phone ?? '');
$company_address = esc_html($company->address ?? '');
$company_postal_code = esc_html($company->postal_code ?? '');
$company_city = esc_html($company->city ?? '');
$company_country = esc_html($company->country ?? '');
$company_domain = !empty($company->compagny_domain) ? esc_html($company->compagny_domain) : null;
$favicon = $company->favicon ?? null;
$initials = $company->initials ?? null;
$company_meta_owner = 1;
$company_meta_type = 'installateur';
$company_priority_level = esc_html($company->priority_level ?? '');
$company_prio_options = 'A:A;B:B;C:C';
$link_contact_list = home_url('/contact-list/');
$link_new_contact = home_url('/add-contact/');
$link_new_project = home_url('/add-project/');
$transactions_list_full = [];
$associated_contacts_list_full = $company->associated_contacts_list_full ?? [];
$last_activity_date = $company->last_contact_date ? date_i18n('d.m.Y', strtotime($company->last_contact_date)) : __('N/A', 'ispag-crm');
$coef_value = $company->coef_value ?? null;
$discount_value = $company->discount_value ?? null;

error_log('Coef de vente : ' . $coef_value);
error_log('Rabais : ' . $discount_value);

// Chargement des transactions
if (class_exists('ISPAG_Crm_Deals_Repository')) {
    $deals_repository = new ISPAG_Crm_Deals_Repository();
    $transactions_list_full = $deals_repository->get_projects_by_company($company_viag_id);
}

// Chargement des notes
if (class_exists('ISPAG_Note_Manager')) {
    $note_repository = new ISPAG_Note_Repository();
    $note_renderer = new ISPAG_Note_Renderer();
    $activity_detail = $note_repository->get_activities_for_entity('company', $company_viag_id);
    $notes_list_full = $note_renderer->render_activities_list($activity_detail);
} else {
    $notes_list_full = '<p>' . __('No registered activity', 'ispag-crm') . '</p>';
}

// Préparation des données pour les contacts
$contact_ids_arr = [];
$contact_names_arr = [];
$contact_emails_arr = [];
$contact_phones_arr = [];

if (!empty($associated_contacts_list_full) && is_array($associated_contacts_list_full)) {
    foreach ($associated_contacts_list_full as $contact_obj) {
        if (is_object($contact_obj)) {
            $id = isset($contact_obj->ID) ? $contact_obj->ID : ($contact_obj->Id ?? 0);
            $contact_ids_arr[] = $id;
            $contact_names_arr[] = str_replace(',', ' ', $contact_obj->display_name ?? 'Inconnu');
            $contact_emails_arr[] = $contact_obj->email ?? '';
            $contact_phones_arr[] = $contact_obj->phone ?? '';
        }
    }
}

$contact_ids = implode(',', $contact_ids_arr);
$contact_names = implode(',', $contact_names_arr);
$contact_emails = implode(',', $contact_emails_arr);
$contact_phones = implode(',', $contact_phones_arr);

// Préparation des données pour les transactions
$deal_ids_arr = [];
$deal_names_arr = [];

foreach ($transactions_list_full as $transaction) {
    if ($transaction && !empty($transaction->project_name)) {
        $deal_ids_arr[] = $transaction->deal_group_ref;
        $deal_names_arr[] = str_replace(',', ' ', $transaction->project_name);
    }
}

$deal_ids = implode(',', $deal_ids_arr);
$deal_names = implode(',', $deal_names_arr);

// Récupération du propriétaire actuel
$current_owner_id = 0;
$current_owner_name = __('Not assigned', 'ispag-crm');
$key = ISPAG_Crm_Contact_Constants::USER_DEPARTMENT;
$companies_owner_table = ISPAG_Crm_Company_Constants::TABLE_COMPANY_OWNER;

$owner_entry = $wpdb->get_row($wpdb->prepare(
    "SELECT user_id FROM {$companies_owner_table}
     WHERE company_id = %d
     AND department_key = %s
     AND status = 'active'",
    $company_viag_id,
    $user_department
));

if ($owner_entry) {
    $current_owner_id = absint($owner_entry->user_id);
    $user_info = get_userdata($current_owner_id);
    if ($user_info) {
        $current_owner_name = $user_info->display_name;
    }
}

// Préparation de la liste des utilisateurs
$users = get_users([
    'fields' => ['ID', 'display_name'],
    'orderby' => 'display_name',
    'meta_query' => [
        [
            'key' => $key,
            'value' => $user_department,
            'compare' => '='
        ],
    ],
]);

$users_list_arr = ['0:' . __('Select...', 'ispag-crm')];
foreach ($users as $u) {
    $users_list_arr[] = $u->ID . ':' . $u->display_name;
}
$users_list_source = implode(';', $users_list_arr);

// Préparation des variables pour le template
$template_args = compact(
    'company',
    'company_id',
    'company_name',
    'company_viag_id',
    'company_type',
    'isIngenieur',
    'is_active',
    'company_phone',
    'company_city',
    'company_country',
    'company_address',
    'company_postal_code',
    'company_domain',
    'favicon',
    'initials',
    'company_meta_owner',
    'company_priority_level',
    'company_meta_type',
    'owner_options_js',
    'type_options_js',
    'link_contact_list',
    'link_new_contact',
    'link_new_project',
    'transactions_list_full',
    'associated_contacts_list_full',
    'coef_value',
    'discount_value',
    'discount_type_options_string',
    'sales_coef_options_string',
    'user_department',
    'current_owner_id',
    'current_owner_name',
    'users_list_source'
);

// Rendre toutes les variables disponibles dans la portée du template
extract($template_args);

// Filtrer le titre de la page dynamiquement
add_filter('pre_get_document_title', function($title) use ($company_name) {
    if (!empty($company_name)) {
        $site_name = get_bloginfo('name');
        return $company_name . ' | ' . $site_name;
    }
    return $title;
}, 999);

wp_enqueue_media();
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="ispag-detail-container ispag-company-detail" data-company-id="<?php echo absint($company_id); ?>">
            
        
            <!-- Colonne de gauche -->
            <div class="ispag-left-panel" data-panel="left">
                <div class="ispag-card ispag-header-card">
                    <div class="ispag-header-top-row">
                        <div class="ispag-profile-pic <?php echo ($favicon) ? 'has-favicon' : ''; ?> ispag-popover-field ispag-avatar-trigger"
                            data-field-type="avatar"
                            data-company-id="<?php echo absint($company_id); ?>"
                            style="cursor: pointer;"
                            title="<?php _e('Modifier l\'icône', 'ispag-crm'); ?>">
                            <span class="current-value">
                                <?php
                                if ($favicon) {
                                    ?>
                                    <img src="<?php echo esc_url($favicon); ?>"
                                        alt="<?php echo esc_attr($company_name); ?>"
                                        class="ispag-avatar-img">
                                    <?php
                                } else {
                                    echo esc_html($initials);
                                }
                                ?>
                            </span>
                        </div>

                        <div class="ispag-header-info">
                            <input type="hidden" id="hidden_company_name" value="<?php echo $company_name; ?>"/>
                            <h4
                                class="ispag-popover-field"
                                data-field-type="text"
                                data-department-id="<?php echo $user_department; ?>"
                                data-company-id="<?php echo $company_id; ?>"
                                data-name="company_name"
                                data-value="<?php echo esc_attr($company_name); ?>">
                                <?php echo $company_name; ?>
                            </h4>
                            <p
                                class="ispag-popover-field"
                                data-field-type="text"
                                data-department-id="<?php echo $user_department; ?>"
                                data-company-id="<?php echo $company_id; ?>"
                                data-name="compagny_domain"
                                data-value="<?php echo esc_attr($company_domain); ?>">
                                <?php echo $company_domain ?? 'pas de domaine défini'; ?>
                            </p>
                            <p
                                class="ispag-popover-field"
                                data-field-type="text"
                                data-department-id="<?php echo $user_department; ?>"
                                data-company-id="<?php echo $company_id; ?>"
                                data-name="viag_id"
                                data-value="<?php echo esc_attr($company_viag_id); ?>">
                                <?php echo $company_viag_id; ?>
                                <span class="edit-icon">✏️</span>
                            </p>
                        </div>
                    </div>

                    <div class="ispag-actions-bar">
                        <?php
                        $actions['company_ids'] = $company_viag_id;
                        $actions['company_names'] = $company_name;
                        $actions['user_id'] = get_current_user_id();
                        $actions['contact_ids'] = $contact_ids;
                        $actions['contact_names'] = $contact_names;
                        $actions['contact_emails'] = $contact_emails;
                        $actions['contact_phones'] = $contact_phones;
                        $actions['deal_ids'] = $deal_ids;
                        $actions['deal_names'] = $deal_names;
                        $actions['user_department'] = $user_department;

                        ispag_get_template('action-bar', ['actions' => $actions]);
                        ?>
                    </div>
                </div>

                <div class="ispag-card ispag-key-info">
                    <h5><?php _e( 'Key information', 'ispag-crm' ); ?></h5>
                    <dl class="ispag-key-info-list">
                        <dt><?php _e('Company Status', 'ispag-crm'); ?></dt>
                        <dd
                            class="ispag-editable-field"
                            data-type="checkbox"
                            data-department-id="<?php echo $user_department; ?>"
                            data-title="<?php _e('Company Status', 'ispag-crm'); ?>"
                            data-name="is_active"
                            data-value="<?php echo esc_attr($is_active); ?>">
                            <?php echo ($is_active == 1) ? '✅' : '❌'; ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e('Is Engineer', 'ispag-crm'); ?></dt>
                        <dd
                            class="ispag-editable-field"
                            data-type="checkbox"
                            data-department-id="<?php echo $user_department; ?>"
                            data-title="<?php _e('Is Engineer', 'ispag-crm'); ?>"
                            data-name="isIngenieur"
                            data-value="<?php echo esc_attr($isIngenieur); ?>">
                            <?php echo ($isIngenieur == 1) ? '✅' : '❌'; ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e('Phone Number', 'ispag-crm'); ?></dt>
                        <dd
                            class="ispag-popover-field"
                            data-department-id="<?php echo $user_department; ?>"
                            data-company-id="<?php echo $company_viag_id; ?>"
                            data-field-type="phone"
                            data-value="<?php echo esc_attr($company_phone); ?>">
                            <?php echo $company_phone; ?>
                        </dd>

                        <dt><?php _e('Type', 'ispag-crm'); ?></dt>
                        <dd
                            class="ispag-editable-field"
                            data-type="select"
                            data-department-id="<?php echo $user_department; ?>"
                            data-company-id="<?php echo $company_viag_id; ?>"
                            data-name="<?php echo ISPAG_Crm_Company_Constants::COMPANY_TYPE; ?>"
                            data-options='<?php echo $type_source_options; ?>'>
                            <?php _e($company_type, 'ispag-crm'); ?>
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e('Priority Level', 'ispag-crm'); ?></dt>
                        <dd
                            class="ispag-editable-field"
                            data-type="select"
                            data-department-id="<?php echo $user_department; ?>"
                            data-company-id="<?php echo $company_viag_id; ?>"
                            data-name="<?php echo ISPAG_Crm_Company_Constants::PRIORITY_LEVEL; ?>"
                            data-value="<?php echo esc_attr($company_priority_level); ?>"
                            data-options="<?php echo esc_attr($company_prio_options); ?>">
                            <?php
                            $priority = strtoupper($company_priority_level);
                            $badge_configs = [
                                'A' => ['color' => '#d63031', 'label' => 'A - ' . __('High', 'ispag-crm')],
                                'B' => ['color' => '#e67e22', 'label' => 'B - ' . __('Medium', 'ispag-crm')],
                                'C' => ['color' => '#2980b9', 'label' => 'C - ' . __('Low', 'ispag-crm')],
                            ];

                            if (isset($badge_configs[$priority])) :
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

                        <dt><?php _e('Company Owner', 'ispag-crm'); ?></dt>
                        <dd
                            class="ispag-editable-field"
                            data-type="select"
                            data-name="department_owner"
                            data-company-id="<?php echo esc_attr($company_viag_id); ?>"
                            data-department-id="<?php echo esc_attr($user_department); ?>"
                            data-value="<?php echo esc_attr($current_owner_id); ?>"
                            data-options='<?php echo esc_attr($users_list_source); ?>'>
                            <?php echo esc_html($current_owner_name); ?>
                            <span class="edit-icon" style="margin-left: 5px; cursor: pointer; opacity: 0.6;">✏️</span>
                        </dd>

                        <dt><?php _e('Last Contacted', 'ispag-crm'); ?></dt>
                        <dd>
                            <?php echo $last_activity_date; ?>
                        </dd>

                        <!-- Menus déroulants pour le type de rabais et le coefficient -->
                        <dt><?php _e('Discount', 'ispag-crm'); ?></dt>
                        <?php 
                        $discount_options_arr = ['0:' . __('Select...', 'ispag-crm')];
                        foreach ($discount_values as $key => $value) :
                            $discount_options_arr[] = $value . ':' . $value . '%';
                        endforeach;
                        $discount_options = implode(';', $discount_options_arr);


                         ?>
                        <!-- Pour le Rabais -->
                        <dd class="ispag-editable-field"
                            data-type="select"
                            data-department-id="<?php echo esc_attr($user_department); ?>"
                            data-company-id="<?php echo esc_attr($company_viag_id); ?>"
                            data-name="rabais"
                            data-value="<?php echo esc_attr($discount_value); ?>"
                            data-options="<?php echo esc_attr($discount_options); ?>">
                            <?php echo floatval($discount_value); ?> %
                            <span class="edit-icon">✏️</span>
                        </dd>

                        <dt><?php _e('Sales Coefficient', 'ispag-crm'); ?></dt>
                        <?php 
                        $coef_options_arr = ['0:' . __('Select...', 'ispag-crm')];                        
                        foreach ($sales_coef_options as $key => $value) :
                            $coef_options_arr[] = $value . ':' . $value;
                         endforeach;
                         $coef_options = implode(';', $coef_options_arr);
                         ?>
                        <!-- Pour le Coefficient -->
                        <dd class="ispag-editable-field"
                            data-type="select"
                            data-department-id="<?php echo esc_attr($user_department); ?>"
                            data-company-id="<?php echo absint($company_viag_id); ?>"
                            data-name="coef_vente"
                            data-value="<?php echo esc_attr($coef_value); ?>"
                            data-options="<?php echo esc_attr($coef_options); ?>">
                            <?php echo floatval($coef_value); ?>
                            <span class="edit-icon">✏️</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="ispag-main-content" data-panel="main">
                <div class="ispag-tabs-navigation">
                    <button class="ispag-tab-btn active" data-tab="about">
                        <?php esc_html_e('About', 'ispag-crm'); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="activity">
                        <?php esc_html_e('Activities', 'ispag-crm'); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="deal">
                        <?php esc_html_e('Transactions', 'ispag-crm'); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="intelligence">
                        <?php esc_html_e('Intelligence', 'ispag-crm'); ?>
                    </button>
                </div>

                <div class="ispag-tabs-content">
                    <div id="ispag-tab-about" class="ispag-tab-pane active">
                        <div class="ispag-card">
                            <h5><?php _e('Company Profile', 'ispag-crm'); ?></h5>
                            <div data-company-id="<?php echo $company_id; ?>" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; font-size: 14px;">
                                <div class="ispag-field-container">
                                    <strong><?php _e('Street Address', 'ispag-crm'); ?>:</strong>
                                    <p>
                                        <span><?php echo esc_html($company_address); ?></span>
                                    </p>
                                </div>

                                <div class="ispag-field-container">
                                    <strong><?php _e('Postal Code', 'ispag-crm'); ?>:</strong>
                                    <p>
                                        <span><?php echo esc_html($company_postal_code); ?></span>
                                    </p>
                                </div>

                                <div class="ispag-field-container">
                                    <strong><?php _e('City', 'ispag-crm'); ?>:</strong>
                                    <p>
                                        <span><?php echo esc_html($company_city); ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($revenue_stats)) : ?>
                            <div class="ispag-card ispag-revenue-dashboard">
                                <h5><?php _e('Revenue Perspectives', 'ispag-crm'); ?></h5>
                                <?php echo $revenue_stats->render_perspective_cards($company_viag_id, 'company'); ?>
                            </div>
                        <?php endif; ?>

                        <div
                            id="gemini-ai-summary-<?php echo absint(get_current_user_id()); ?>"
                            class="ispag-ai-placeholder"
                            data-contact-id="<?php echo absint(get_current_user_id()); ?>"
                            data-company-id="<?php echo absint($company_viag_id); ?>">
                            <?php ispag_get_template('ai-loader', [null]); ?>
                        </div>
                    </div>

                    <div id="ispag-tab-activity" class="ispag-tab-pane">
                        <?php echo $notes_list_full; ?>
                    </div>

                    <div id="ispag-tab-deal" class="ispag-tab-pane">
                        <h5><?php esc_html_e('Transaction Information', 'ispag-crm'); ?></h5>
                        <?php ispag_get_template('deal-table', ['transactions' => $transactions_list_full]); ?>
                    </div>

                    <div id="ispag-tab-intelligence" class="ispag-tab-pane">
                        <div
                            id="gemini-ai-profil-<?php echo absint(get_current_user_id()); ?>"
                            class="ispag-ai-profil-placeholder"
                            data-contact-id="<?php echo absint(get_current_user_id()); ?>"
                            data-company-id="<?php echo absint($company_viag_id); ?>">
                            <?php ispag_get_template('ai-loader', [null]); ?>
                        </div>

                        <div
                            id="gemini-ai-actions-<?php echo absint(get_current_user_id()); ?>"
                            class="ispag-ai-actions-placeholder"
                            data-contact-id="<?php echo absint(get_current_user_id()); ?>"
                            data-company-id="<?php echo absint($company_viag_id); ?>">
                            <?php ispag_get_template('ai-loader', [null]); ?>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Colonne de droite -->
            <!-- Wrapper qui porte la largeur flex + le bouton -->
            <div class="ispag-right-panel-wrapper" data-panel="right-wrapper">
                                <!-- Bouton collé au bord gauche du wrapper : il suit le panneau -->
                <button id="toggle-right-panel" class="ispag-panel-toggle-right" type="button"
                        aria-label="<?php esc_attr_e('Display / Mask panel', 'ispag-crm'); ?>"
                        title="<?php esc_attr_e('Mask panel', 'ispag-crm'); ?>">
                    <img
                        src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/ios-sidebar-hide.png'); ?>"
                        data-icon-hide="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/ios-sidebar-hide.png'); ?>"
                        data-icon-show="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/ios-sidebar-display.png'); ?>"
                        alt=""
                        class="ispag-panel-toggle-icon">
                </button>
                <div class="ispag-right-panel" data-panel="right">
            
                    <?php
                    $datas['associated_contacts_list_full'] = $associated_contacts_list_full;
                    $datas['company_id'] = $company_id;
                    $datas['company_viag_id'] = $company_viag_id;
                    ispag_get_template( 'ispag-template-contact-card', [ 'datas' => $datas ] ); 
                    ?>
                    

                    <?php
                    $datas['transactions_list_full'] = $transactions_list_full;
                    $datas['link_new_project'] = $link_new_project;
                    ispag_get_template( 'ispag-template-deal-card', [ 'datas' => $datas ] ); 
                    ?>
                    <div id="ispag-modal-container"></div>

                </div>
            </div>
        </div>
    </main>
</div>


<?php
ispag_get_template('deal-reason-for-rejection-modal', []);
ispag_get_template('ispag-popover-modal', [null]);


get_footer();
?>