<?php
/**
 * Template Name: ISPAG Project Detail Viewer
 * Template Post Type: page
 * Description: Affiche la vue détaillée d'un project (Deal/Projet) ISPAG.
 */

get_header();

$deal_id = get_query_var('deal_id');
$user_id = get_current_user_id();
$can_manage_order = current_user_can('manage_order');
$can_view_prices = current_user_can('display_sales_prices');

if (empty($deal_id)) {
    ?>
    <div class="ispag-error-message">
        <?php
        printf(
            __('Deal data are missing or not found for ID : %s', 'ispag-crm'),
            esc_html($deal_id)
        );
        ?>
    </div>
    <?php
    get_footer();
    return;
}

if (!class_exists('ISPAG_Projet_Repository')) {
    ?>
    <div class="ispag-error-message">
        <?php
        printf(
            __('Error while creating page for deal ID : %s', 'ispag-crm'),
            esc_html($deal_id)
        );
        ?>
    </div>
    <?php
    get_footer();
    return;
}

$deal_repo = new ISPAG_Projet_Repository();
$deal_detail_repo = new ISPAG_Project_Details_Repository();
$project = $deal_repo->get_project_detail_by_hubspot_deal_id($deal_id);
$project_revenue = $deal_detail_repo->get_project_profitability($deal_id);
$article_renderer = new ISPAG_Project_views_Renderer();

//-----------------------------------------------------------
// Création de la liste des entreprises liées au projet
//-----------------------------------------------------------
$companies = array();
$associated_companies_list_full = array();
if (!empty($project->AssociatedCompanyID) && class_exists('ISPAG_Crm_Company_Repository')) {
    $company_repo = new ISPAG_Crm_Company_Repository();
    $company_ids = explode(',', $project->AssociatedCompanyID);
    $company_ids = array_filter(array_map('trim', $company_ids));
    foreach ($company_ids as $company_id) {
        $company_id = absint($company_id);
        $associated_companies_list_full[] = $company_id;
        if ($company_id > 0) {
            $companies[] = $company_repo->get_company_by_viag_id($company_id);
        }
    }
}

//-----------------------------------------------------------
// Création de la liste des contacts liés au projet
//-----------------------------------------------------------
$contacts = [];
if (!empty($project->AssociatedContactIDs) && class_exists('ISPAG_Crm_Contacts_Repository')) {
    $contact_repo = new ISPAG_Crm_Contacts_Repository();
    $contact_ids = explode(',', $project->AssociatedContactIDs);
    $contact_ids = array_filter(array_map('trim', $contact_ids));
    foreach ($contact_ids as $contact_id) {
        $contact_id = absint($contact_id);
        if ($contact_id > 0) {
            $contacts[] = $contact_repo->get_contact_by_id($contact_id);
        }
    }
}

//-----------------------------------------------------------------------
// Création de la liste owner possible pour le departement en cours
//-----------------------------------------------------------------------
$owner_list_arr = ['0:' . __('Select...', 'ispag-crm')];
if(class_exists('ISPAG_Contact_Ajax_Handler')){
    $contact_ajax = new ISPAG_Contact_Ajax_Handler();
    $owner_list = $contact_ajax->get_all_owners();
    
    foreach ($owner_list as $u) {
        $owner_list_arr[] = $u->ID . ':' . $u->display_name;
    }
    
}
$owner_list_source = implode(';', $owner_list_arr);

//-----------------------------------------------------------------------
// Création du badge prochaine étape du projet
//-----------------------------------------------------------------------
$bgcolor = !empty($project->next_phase->Color) ? esc_attr($project->next_phase->Color) : '#ccc';
$next_step_badge = '<span class="ispag-state-badge" style="background-color:' . $bgcolor . ';">' . esc_html($project->next_phase->TitrePhase ?? 'Non défini') . '</span>';


//-----------------------------------------------------------------------
// Création de la liste des activités (notes)
//-----------------------------------------------------------------------
$notes_list_full = '<p>' . __('No registered activity', 'ispag-crm') . '</p>';
if (class_exists('ISPAG_Note_Manager'))
{
    $note_repository = new ISPAG_Note_Repository();
    $note_renderer = new ISPAG_Note_Renderer();
    $deal_repo = new ISPAG_Crm_Deals_Repository();

    $deal = $deal_repo->get_project_by_project_num($project->NumCommande);
    

    $all_deal_identifiers = [];
    if (!empty($deal_id))
    {
        $all_deal_identifiers[] = $deal_id;
    }
    if ($deal && isset($deal->deal_group_ref))
    {
        $all_deal_identifiers[] = $deal->deal_group_ref;
    }

    if (!empty($all_deal_identifiers))
    {
        $activity_detail = $note_repository->get_activities_for_entity('deal', $all_deal_identifiers);
        $notes_list_full = $note_renderer->render_activities_list($activity_detail);
        
    }
    else
    {
        $notes_list_full = "Aucune activité trouvée.";
    }
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="ispag-detail-container ispag-company-detail">


            <!-- Colonne de gauche -->
            <div class="ispag-left-panel" data-panel="left">
                <div class="ispag-card ispag-header-card">
                    <div class="ispag-header-top-row">
                        <div class="ispag-header-info">
                            <h4
                                id="editable-project-title"
                                
                                contenteditable="true"
                                spellcheck="false"
                                data-name="ObjetCommande"
                                data-value="<?php echo esc_html(stripslashes($project->ObjetCommande)); ?>"
                                data-project-id="<?php echo esc_attr($deal_id); ?>"
                                data-deal="<?php echo esc_attr($deal_id); ?>"
                                data-source="project"
                                data-is-quotation="<?php echo $project->isQotation; ?>"
                                style="margin-top:0; font-size:1.8rem; border-bottom: 1px dashed transparent; cursor: pointer;">
                                <?php echo esc_html($project->ObjetCommande); ?>
                            </h4>
                            <div  class="fields-prices">
                            <p>
                                <?php echo __('Amount', 'ispag-crm'); ?> : <?php echo number_format_i18n($project_revenue['revenu'], 2) . ' CHF'; ?>
                            </p>
                            </div>
                            <p>
                                <?php echo __('Creation date', 'ispag-crm'); ?> : <?php echo date_i18n( 'd.m.Y', strtotime( $project->date_creation ) ); ?> 
                            </p>
                            <?php 
                            $pm = get_userdata($project->project_manager);
                            $pm_name = $pm ? $pm->display_name : __('Unknown', 'ispag-crm');
                            ?>
                            <p>
                                
                                <?php echo __('Project Manager', 'ispag-crm'); ?> : 
                                <span
                                    class="ispag-editable-field"
                                    data-type="select"
                                    data-name="project_manager"
                                    data-project-id="<?php echo esc_attr($deal_id); ?>"
                                    data-value="<?php echo esc_attr($project->project_manager); ?>"
                                    data-options="<?php echo esc_attr($owner_list_source); ?>"
                                    >
                                        <?php echo esc_html($pm_name); ?>
                                </span>
                            </p>
                            <p>
                                <?php echo __('Next step', 'ispag-crm'); ?> : <?php echo $next_step_badge; ?>
                            </p>
                            <?php if ($can_manage_order): ?>
                                <p>
                                    <button id="ispag-force-show-prices" class="button button-primary">
                                        👁️ <?php _e('Force price display', 'creation-reservoir'); ?>
                                    </button>
                                    <button id="ispag-force-hide-prices" class="button button-secondary">
                                        🔒 <?php _e('Hide prices', 'creation-reservoir'); ?>
                                    </button>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="ispag-card ispag-project-btn-card">

                    <?php if ($can_manage_order): ?>
                        <a href="<?= esc_url(home_url( '/liste-des-achats/?search=' . $deal_id . '' )) ?>" target="_blank" class="ispag-btn ispag-btn-secondary-outlined"><?= esc_html(__('To purchase', 'ispag-crm')) ?></a>
                    <?php endif; ?>
                    <?php
                    echo $article_renderer->render_project_action_button($deal_id);
                    ?>
                </div>

            </div>

            <!-- Contenu principal -->
            <div class="ispag-main-content" data-panel="main">
                <div class="ispag-tabs-navigation">
                    <button class="ispag-tab-btn active" data-tab="overview">
                        <?php esc_html_e('Overview', 'ispag-crm'); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="activity">
                        <?php esc_html_e('Activities', 'ispag-crm'); ?>
                    </button>
                    <?php if ($can_manage_order): ?>
                        <button class="ispag-tab-btn" data-tab="details">
                            <?php esc_html_e('Details', 'ispag-crm'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <button class="ispag-tab-btn" data-tab="fallowup">
                        <?php esc_html_e('Follow up', 'ispag-crm'); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="document">
                        <?php esc_html_e('Documents', 'ispag-crm'); ?>
                    </button>
                </div>

                <div class="ispag-tabs-content">
                    <div id="ispag-tab-overview" class="ispag-tab-pane active">
                        <?php
                        $datas['deal_id'] = $deal_id;
                        $datas['can_view_prices'] = $can_view_prices;
                        ispag_get_template( 'ispag-project-articles', [ 'datas' => $datas ] );  
                        ?>
                    </div>

                    <?php if ($can_manage_order): ?>
                        <div id="ispag-tab-activity" class="ispag-tab-pane">

                            <?php echo $notes_list_full; ?>
                        </div>
                    <?php endif; ?>

                    <div id="ispag-tab-details" class="ispag-tab-pane">
                        <div class="ispag-card">
                            <?php
                            $article_renderer->display_ispag_project_details($deal_id, 999);
                            ?>
                        </div>
                    </div>

                    <div id="ispag-tab-fallowup" class="ispag-tab-pane"></div>

                    <div id="ispag-tab-document" class="ispag-tab-pane">
                        <div class="ispag-card ispag-docu-card"
                            data-view="list"
                            data-entity-type="project"
                            data-entity-id="<?php echo esc_attr($deal_id); ?>">
                            <!-- ----------Affichage des documents ------->
                            <?php
                            if(class_exists('ISPAG_Attachments_Repository') AND class_exists('ISPAG_Attachments_Card_Renderer')){
                                $repository = new ISPAG_Attachments_Repository($wpdb);
                                $renderer   = new ISPAG_Attachments_Card_Renderer($repository);

                                // --- Sur une fiche Deal / Projet ---

                                echo $renderer->render_doc_list('project', $deal_id, -1, true);
                            }
                            ?>
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
                    $datas['associated_companies_list_full'] = $associated_companies_list_full;
                    $datas['deal_id'] = $deal_id;
                    ispag_get_template( 'ispag-template-company-card', [ 'datas' => $datas ] ); 
                    ?>

                    <?php
                    $datas['associated_contacts_list_full'] = $contacts;
                    $datas['company_id'] = $company_id;
                    $datas['deal_id'] = $deal_id;
                    ispag_get_template( 'ispag-template-contact-card', [ 'datas' => $datas ] ); 
                    ?>


                    <!-- ----------Affichage des documents ------->
                    <?php
                    if(class_exists('ISPAG_Attachments_Repository') AND class_exists('ISPAG_Attachments_Card_Renderer')){
                        $repository = new ISPAG_Attachments_Repository($wpdb);
                        $renderer   = new ISPAG_Attachments_Card_Renderer($repository);

                        // --- Sur une fiche Deal / Projet ---
                        
                        echo $renderer->render('project', $deal_id);
                    }
                    ?>
                    <div id="ispag-modal-container"></div>
                    <!-- Poignée de redimensionnement pour la colonne de droite -->
                    <!-- <div class="resize-handle right-handle" data-resize="right"></div> -->
                </div>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();