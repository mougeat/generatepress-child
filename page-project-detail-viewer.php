<?php
/**
 * Template Name: ISPAG Project Detail Viewer
 * Template Post Type: page
 * Description: Affiche la vue détaillée d'un project (Deal/Projet) ISPAG.
 */

// 1. Récupération de l'URL exacte en cours (avec les arguments de requête s'il y en a)
global $wp;
$current_url = home_url( add_query_arg( $_GET, $wp->request ) );

// 2. Si l'utilisateur n'est pas connecté, redirection vers le login avec l'URL de retour
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( $current_url ) );
    exit;
}

if ( ! current_user_can( 'read_orders' ) ) {
    get_header();
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="ispag-alert ispag-alert-danger" style="margin: 50px auto; max-width: 600px; padding: 20px;">
                <span class="dashicons dashicons-lock"></span>
                <strong><?php esc_html_e('Restricted access', 'ispag-crm'); ?> :</strong>
                <?php esc_html_e('You do not have the necessary rights to view this order.', 'ispag-crm'); ?>
            </div>
        </main>
    </div>
    <?php
    get_footer();
    exit; // Utiliser exit pour stopper définitivement le script PHP
}

// 2. Si la permission est OK, on charge le header et le reste du code
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

$can_manage_order = current_user_can('manage_order');
$edit_content = current_user_can('manage_order') ? 'true' : 'false';

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
//On créé les datas minimale pour le projet
//-----------------------------------------------------------------------
if(class_exists('ISPAG_Projet_Repository')){
    $project_repo = new ISPAG_Projet_Repository();
    $project = $project_repo->get_minimal_project_detail_by_hubspot_deal_id($deal_id);
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
                                
                                contenteditable="<?php echo esc_attr($edit_content); ?>"
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
                            
                            <p><?php _e('version', 'creation-reservoir'); ?> <?php echo esc_attr($project->version); ?></p>
                            
                            <p>
                                <?php echo __('Amount', 'ispag-crm'); ?> :  <span class="ispag-skeleton-wrapper ispag-skeleton-line ispag-w-40" id="ispag_project_amount" data-deal-id="<?php echo esc_attr($deal_id); ?>"></span>
                            </p>
                            </div>
                            <p>
                                <?php echo __('Creation date', 'ispag-crm'); ?> :  <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project->date_creation))); ?>
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
                                <?php echo __('Next step', 'ispag-crm'); ?> :  <span class="ispag-next-step-badge step-badge"><span class="ispag-skeleton-wrapper ispag-skeleton-line ispag-w-80" id="ispag_project_next_step"></span></span>
                            </p>
                            <?php if ($can_manage_order): ?>
                                <p>
                                    <button id="ispag-force-show-prices" class="button button-primary">
                                        👁️ <?php _e('Force price display', 'ispag-crm'); ?>
                                    </button>
                                    <button id="ispag-force-hide-prices" class="button button-secondary">
                                        🔒 <?php _e('Hide prices', 'ispag-crm'); ?>
                                    </button>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="ispag-card ispag-project-btn-card"  data-deal-id="<?php echo esc_attr($deal_id); ?>">

                    <?php if ($can_manage_order): ?>
                        <a href="<?= esc_url(home_url( '/liste-des-achats/?search=' . $deal_id . '' )) ?>" target="_blank" class="ispag-btn ispag-btn-secondary-outlined"><span class="dashicons dashicons-cart"></span> <?= esc_html(__('To purchase', 'ispag-crm')) ?></a>
                        <br>
                    <?php endif; ?>
                    <?php
                    // echo $article_renderer->render_project_action_button($deal_id, $project->isQotation);
                    ?>
                </div> 

                <div class="ispag-card ispag-bulk-actions"  data-deal-id="<?php echo esc_attr($deal_id); ?>">

                </div>

                 <?php
                // echo $article_renderer->bulk_selected_article($deal_id, $project->isQotation);
                ?>
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
                        //Affichage des statistiques du projet
                        if (current_user_can('manage_order')){
                            ?>
                            <div id="ispag-bloc-stat-projet" class="fields-prices prices-visible">
                                <div id="ispag_project_stat" class="ispag-stats-container ispag-skeleton-wrapper">
                                    <h4 class="ispag-stats-title"><?php echo esc_html__('Project Dashboard', 'creation-reservoir'); ?></h4>
                                    <div class="ispag-stats-grid">
                                        <div class="ispag-stat-card">
                                            <span class="stat-label ispag-skeleton-line ispag-w-80"></span>
                                            <span class="stat-value ispag-skeleton-line ispag-w-80"></span>
                                        </div>

                                        <div class="ispag-stat-card">
                                            <span class="stat-label ispag-skeleton-line ispag-w-80"></span>
                                            <span class="stat-value ispag-skeleton-line ispag-w-80"></span>
                                        </div>

                                        <div class="ispag-stat-card">
                                            <span class="stat-label ispag-skeleton-line ispag-w-80"></span>
                                            <span class="stat-value ispag-skeleton-line ispag-w-80"></span>
                                        </div>

                                        <div class="ispag-stat-card">
                                            <span class="stat-label ispag-skeleton-line ispag-w-80"></span>
                                            <span class="stat-value ispag-skeleton-line ispag-w-80"></span>
                                        </div>

                                        <div class="ispag-stat-card">
                                            <span class="stat-label ispag-skeleton-line ispag-w-80"></span>
                                            <span class="stat-value ispag-skeleton-line ispag-w-80"></span>
                                        </div>

                                        <div class="ispag-stat-card">
                                            <span class="stat-label ispag-skeleton-line ispag-w-80"></span>
                                            <span class="stat-value ispag-skeleton-line ispag-w-80"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="ispag-coef-notice" data-deal-id="<?php echo esc_attr($deal_id); ?>" class="fields-prices prices-visible">

                                
                            </div>

                        <?php
                        }
                        ?>
                        <!--    ------------------------------ -->
                        <div id="display_article_page">
                            <div class="ispag-article-header-global" style="margin-bottom: 1rem;">
                                <input type="checkbox" id="select-all-articles" class="ispag-article-checkbox">
                                <label for="select-all-articles"><?php _e('Select all', 'creation-reservoir'); ?></label>
                            </div>
                            <div class="ispag-articles-content" id="display_ispag_article_list">
                                <div class="ispag-articles-list" data-deal-id="<?php echo esc_attr($deal_id); ?>">
                                    <?php
                                    echo ispag_get_template( 'ispag-render-article-block-skeleton', [ ] );  
                                    ?>
                                </div>
                            </div>
                        </div>
                        <!--    ------------------------------ -->
                        <?php
                        // $datas['deal_id'] = $deal_id;
                        // $datas['can_view_prices'] = $can_view_prices;
                        // echo ispag_get_template( 'ispag-project-articles', [ 'datas' => $datas ] );  
                        ?>
                    </div>

                    <?php if ($can_manage_order): ?>
                        <div id="ispag-tab-activity" class="ispag-tab-pane" data-deal-id="<?php echo esc_attr($deal_id); ?>">
                            <?php
                            echo ispag_get_template( 'ispag-activity-squeleton', [] );
                            ?>
                        </div>
                    <?php endif; ?>

                    <div id="ispag-tab-details" class="ispag-tab-pane" data-deal-id="<?php echo esc_attr($deal_id); ?>">
                        <div class="ispag-detail-section">
                            <div class="ispag-box ispag-skeleton-wrapper">
                               <div class="ispag-skeleton-header">
                                    <div class="ispag-skeleton-circle"></div>
                                    <div class="ispag-skeleton-line ispag-w-40"></div>
                                </div>
                                <div class="ispag-skeleton-line ispag-w-80"></div>
                                <div class="ispag-skeleton-line ispag-w-90"></div>
                                <div class="ispag-skeleton-line ispag-w-60"></div>
                            </div>

                            <div class="ispag-box ispag-skeleton-wrapper">
                               <div class="ispag-skeleton-header">
                                    <div class="ispag-skeleton-circle"></div>
                                    <div class="ispag-skeleton-line ispag-w-40"></div>
                                </div>
                                <div class="ispag-skeleton-line ispag-w-80"></div>
                                <div class="ispag-skeleton-line ispag-w-90"></div>
                                <div class="ispag-skeleton-line ispag-w-60"></div>
                            </div>

                            <div class="ispag-box ispag-skeleton-wrapper">
                               <div class="ispag-skeleton-header">
                                    <div class="ispag-skeleton-circle"></div>
                                    <div class="ispag-skeleton-line ispag-w-40"></div>
                                </div>
                                <div class="ispag-skeleton-line ispag-w-80"></div>
                                <div class="ispag-skeleton-line ispag-w-90"></div>
                                <div class="ispag-skeleton-line ispag-w-60"></div>
                            </div>
                        </div>
                    </div>

                    <div id="ispag-tab-fallowup" class="ispag-tab-pane" data-deal-id="<?php echo esc_attr($deal_id); ?>"></div>

                    <div id="ispag-tab-document" class="ispag-tab-pane">
                        
                        <div class="ispag-card ispag-docu-card"
                            data-view="list"
                            data-entity-type="project"
                            data-entity-id="<?php echo esc_attr($deal_id); ?>">

                            <?php
                                echo ispag_get_template( 'ispag-activity-squeleton', [] );
                            ?>
                            <!-- ----------Affichage des documents ------->
                            <?php
                            // if(class_exists('ISPAG_Attachments_Repository') AND class_exists('ISPAG_Attachments_Card_Renderer')){
                            //     $repository = new ISPAG_Attachments_Repository($wpdb);
                            //     $renderer   = new ISPAG_Attachments_Card_Renderer($repository);

                            //     // --- Sur une fiche Deal / Projet ---

                            //     echo $renderer->render_doc_list('project', $deal_id, -1, true);
                            // }
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

                    <div class="ispag-card ispag-company-card" data-deal-id="<?php echo absint($deal_id); ?>">
                        <h5>
                            <?php _e( 'Company', 'ispag-crm' ); ?> 
                        </h5>

                        <!-- Contenu squelette affiché par défaut avant l'AJAX -->
                        <div class="ispag-skeleton-wrapper ispag-card">
                            <div class="ispag-skeleton-header">
                                <div class="ispag-skeleton-circle"></div>
                                <div class="ispag-skeleton-line ispag-w-40"></div>
                            </div>
                            <div class="ispag-skeleton-line ispag-w-80"></div>
                            <div class="ispag-skeleton-line ispag-w-90"></div>
                            <div class="ispag-skeleton-line ispag-w-60"></div>
                        </div>
                        
                    </div>
                    
                    <?php
                    // $datas['associated_companies_list_full'] = $associated_companies_list_full;
                    // $datas['deal_id'] = $deal_id;
                    // ispag_get_template( 'ispag-template-company-card', [ 'datas' => $datas ] ); 
                    ?> 


                    <div class="ispag-card ispag-contact-card" data-deal-id="<?php echo absint($deal_id); ?>">
                        <h5>
                            <?php _e('Contacts', 'ispag-crm'); ?>
                        </h5>
                        <!-- Contenu squelette affiché par défaut avant l'AJAX -->
                        <div class="ispag-skeleton-wrapper ispag-card"> 
                            <div class="ispag-skeleton-header">
                                <div class="ispag-skeleton-circle"></div>
                                <div class="ispag-skeleton-line ispag-w-40"></div>
                            </div>
                            <div class="ispag-skeleton-line ispag-w-80"></div>
                            <div class="ispag-skeleton-line ispag-w-90"></div>
                            <div class="ispag-skeleton-line ispag-w-60"></div>
                        </div>
                    </div>
                    <?php
                    // $datas['associated_contacts_list_full'] = $contacts;
                    // $datas['company_id'] = $company_id;
                    // $datas['deal_id'] = $deal_id;
                    // ispag_get_template( 'ispag-template-contact-card', [ 'datas' => $datas ] ); 
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