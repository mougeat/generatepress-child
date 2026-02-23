<?php
/**
 * Template Name: ISPAG Deal Detail Viewer
 * Template Post Type: page
 * Description: Affiche la vue détaillée d'une transaction (Deal/Projet) ISPAG.
 */

// S'assurer que la classe existe avant d'enregistrer l'action
if ( class_exists( 'ISPAG_Contact_Note_Manager' ) ) {
    // Le modal sera ajouté au wp_footer si et seulement si ce template est chargé.
    add_action( 'wp_footer', array( new ISPAG_Contact_Note_Manager(), 'render_note_modal_html' ) );

} 

get_header();

// ====================================================
// --- SIMULATION DES DÉPENDANCES (À REMPLACER) ---
// Note : Le code ci-dessous est une simulation simplifiée pour que le template PHP soit valide.
// Vous devez vous assurer que les classes ISPAG_Crm_Deal_Model et ISPAG_Crm_Contacts_Repository 
// existent réellement et implémentent les méthodes utilisées.

if ( ! function_exists( 'ispag_get_all_deal_owners' ) ) {
    function ispag_get_all_deal_owners() {
        // Cette fonction doit retourner un tableau associatif [user_id => 'Display Name']
        // Exemple simplifié :
        return [
            1 => 'Cyril Barthel',
            2 => 'Stéphane Martin',
            3 => 'Sophie Dubois',
        ];
    }
}
// ====================================================



// ----------------------------------------------------
// 1. Initialisation des données et Condition A (Extérieure)
// ----------------------------------------------------
$deal_id = absint( get_query_var( 'ispag_deal_id' ) ); 
$deal    = null;

// error_log('[DEBUG] Deal_id ' . $deal_id);
// error_log('[DEBUG] class_exists( \'ISPAG_Crm_Deal_Model\' ) ' . class_exists( 'ISPAG_Crm_Deal_Model' ));




// Vérification A : L'ID est présent ET la classe modèle existe.
if ( $deal_id > 0 && class_exists( 'ISPAG_Crm_Deal_Model' ) && class_exists( 'ISPAG_Crm_Deals_Repository' ) ) :

    $repository = new ISPAG_Crm_Deals_Repository();
    $deal_data  = $repository->get_deal_by_id( $deal_id );

    
    // NOTE: Si ISPAG_Crm_Deal_Model est un singleton ou nécessite un Repository, ajustez l'instanciation.
    $deal = new ISPAG_Crm_Deal_Model( $deal_data );
    
    // ----------------------------------------------------
    // Condition B (Intérieure)
    // ----------------------------------------------------
    // Vérification B : Le Deal a été chargé avec succès (ID > 0).
    if ( $deal->id > 0 ) :

        // --- Définition des variables pour le template ---
        $associated_contacts_list = $deal->get_associated_contacts_list();
        
        $associated_companies_list = $deal->get_associated_company_list();
        error_log(print_r($associated_companies_list, true));

        $contacts_count           = count( $associated_contacts_list );
        $companies_count           = count( $associated_companies_list );
        $company_id               = absint( $deal->associated_company_id );
        
        // NOUVEAUTÉ : Récupération de la compagnie et de la dernière activité (basé sur le modèle)
        $company_name             = $deal->associated_company_name ?? __('N/A', 'ispag-crm');
        // Assurez-vous que cette méthode est disponible dans ISPAG_Crm_Deal_Model
        
        $base_url = get_home_url(); 
        $company_link = trailingslashit( $base_url . '/company/' . $company_id );
        
        
        // Stage actuel (Doit être rempli dans le constructeur/méthode de chargement du modèle)
        $current_stage_label      = $deal->stage_label ?? __('Non défini', 'ispag-crm');
        $current_stage_color      = $deal->stage_color ?? '#cccccc';
        
        // Date de dernière activité. Assurez-vous que last_activity_date est rempli dans $deal
        $last_activity_date       = $deal->last_activity_date ? date_i18n( 'd.m.Y', strtotime( $deal->last_activity_date ) ) : __('N/A', 'ispag-crm');
 
        // Liens avec le projet de cuves 
        if($deal->project_num){
            $hubspot_deal_id = $repository->get_hubspot_id_by_project_num( $deal->project_num );
        }
        else{
            $hubspot_deal_id = $repository->get_hubspot_id_by_project_name( $deal->project_name );
        }
        // --- PRÉPARATION DU SÉLECTEUR DEAL OWNER ---
        $owner_options_json = '[]'; 
        // NOTE: On utilise la fonction simulée ou votre repository réel
        if ( function_exists( 'ispag_get_all_deal_owners' ) ) {
            $owner_options_array = ispag_get_all_deal_owners();
            // JSON_FORCE_OBJECT est important pour garantir la structure { "id": "Nom" }
            $owner_options_json = json_encode( $owner_options_array, JSON_FORCE_OBJECT | JSON_HEX_APOS ); 
        }
        // --- FIN PRÉPARATION SÉLECTEUR ---

        // Variables de lien
        $contact_list_page = get_page_by_path( 'listes-des-contacts' );
        $link_contact_list = $contact_list_page ? get_permalink( $contact_list_page ) : '#';
        $contact_page = get_page_by_path( 'listes-des-contacts' );
        $link_contact = $contact_page ? get_permalink( $contact_page ) : '#';
        $link_user_page    = $link_contact;


        // Création des datas pour les actions button

        //**** Company */
        $company_ids_arr = [];
        $company_names_arr = []; 

        // On boucle directement sur les objets déjà présents
        foreach ($associated_companies_list as $company_obj) {
            
            // On vérifie si c'est bien un objet et s'il a un nom
            // Note : Dans ton log, l'ID est "Id" (majuscule) et le nom est "company_name"
            if ( is_object($company_obj) && !empty($company_obj->company_name) ) {
                
                $company_ids_arr[]   = $company_obj->Id; // On utilise l'Id de l'objet
                
                // On retire les virgules et les retours à la ligne (vu dans ton log)
                $clean_name = str_replace([",", "\r", "\n"], " ", $company_obj->company_name);
                $company_names_arr[] = trim($clean_name); 
            }
        }

        // On transforme les tableaux en chaînes propres pour le JS
        $company_ids   = implode(',', $company_ids_arr);
        $company_names = implode(',', $company_names_arr);

        //**** Contacts */
        // Création des tableaux pour les attributs data
        $contact_ids_arr = [];
        $contact_names_arr = [];
        $contact_emails_arr = [];
        $contact_phones_arr = [];

        if (!empty($associated_contacts_list) && is_array($associated_contacts_list)) {
            foreach ($associated_contacts_list as $contact_obj) {
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

        // // Verifie ici dans tes logs si cette ligne est toujours vide
        // error_log("RESULTAT FINAL CONTACTS : " . $contact_ids);
        // error_log("RESULTAT FINAL CONTACTS : " . $contact_names);
        // error_log("RESULTAT FINAL CONTACTS : " . $contact_emails);
        // error_log("RESULTAT FINAL CONTACTS : " . $contact_phones);

        /********************************************** */
        // Chargement des notes
        /********************************************** */
        // ⚠️ Assurez-vous que la classe Deals Repository existe.
        if ( class_exists( 'ISPAG_Note_Manager' ) ) {
            $note_repository = new ISPAG_Note_Repository();
            $note_renderer = new ISPAG_Note_Renderer();
            // error_log('[DEBUG] Note Deal_id ' . $deal->deal_group_ref);
            $activity_detail = $note_repository->get_activities_for_entity('deal', $deal->deal_group_ref);
            // error_log('-> activity_detail ', print_r($activity_detail, true));

            // On charge la liste et on l'affecte à la variable destinée au template
            $notes_list_full = $note_renderer->render_activities_list( $activity_detail);
        } else {
            // Sinon, on s'assure qu'elle est un tableau vide pour éviter les erreurs dans la vue.
            $notes_list_full = '<p>' . __( 'No registered activity', 'ispag-crm' ) . '</p>';
        }
                    
    ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">


        <div class="ispag-detail-container ispag-company-detail" data-deal-id="<?php echo absint($deal->id); ?>">
            
            <div class="ispag-left-panel">
                <div class="ispag-card ispag-header-card">
                    <div class="ispag-header-info">
                        <h4><?php echo esc_html( $deal->project_name ); ?></h4>
                        <p>
                            <?php echo __('Amount', 'ispag-crm'); ?> 
                            <?php echo number_format( (float)$deal->total_excl_vat, 2, '.', '\'' ); ?> CHF
                        </p>
                        <p>
                            <?php echo __('Close date', 'ispag-crm'); ?> <?php echo date_i18n( 'd.m.Y', strtotime( $deal->closing_date ) ); ?> 
                        </p>
                        <p>
                            <?php echo __('Stage', 'ispag-crm'); ?> 

                            <span class="ispag-badge-container" style="position: relative; display: inline-block; margin-left: 5px;">
                                
                                <span class="ispag-status-badge" style="background-color: <?php echo esc_attr($current_stage_color); ?>; color: #fff; padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                    <?php echo esc_html( $current_stage_label ); ?> 
                                </span>

                                <?php 
                                $stage_repo = new ISPAG_Crm_Deal_Stages_Repository();
                                $all_stages = $stage_repo->get_all_stages(); 
                                ?>
                                <select class="ispag-stage-updater" 
                                        data-deal-id="<?php echo esc_attr($deal->id); ?>" 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                                    
                                    <?php foreach ($all_stages as $stage) : ?>
                                        <option value="<?php echo esc_attr($stage->stage_key); ?>" 
                                                data-label="<?php echo esc_html($stage->stage_label); ?>"
                                                data-color="<?php echo esc_attr($stage->stage_color); ?>"
                                                <?php selected($current_stage_key, $stage->stage_key); ?>>
                                            <?php echo esc_html($stage->stage_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                        </p>
                    </div>
                </div>
            
            
                <div class="ispag-actions-bar">
                    <?php 
                        $actions['company_ids']       = $company_ids;
                        $actions['company_names']     = $company_names;
                        $actions['user_id']           = $user_id;
                        $actions['contact_name']      = $contact_name;
                        $actions['contact_ids']       = $contact_ids;
                        $actions['contact_names']     = $contact_names;
                        $actions['contact_emails']    = $contact_emails;
                        $actions['contact_phones']    = $contact_phones;
                        $actions['deal_ids']          = $deal_id;
                        $actions['deal_names']        = $deal->project_name;
                        $actions['offer_num']         = $deal->deal_group_ref;
                        $actions['project_nums']      = $project_nums;
                        $actions['closing_date']      = $deal->closing_date;
                        $actions['total_excl_vat']    = $deal->total_excl_vat;
                                                

                        // Appelle le template et lui passe les données
                        ispag_get_template( 'action-bar', [ 'actions' => $actions ] ); 
                    ?>
                    
                </div>
                
                
                <div class="ispag-card ispag-header-card">
                    <div class="ispag-header-info">
                        <h4><?php echo __('About this deal', 'ispag-crm'); ?></h4>
                        <dl class="ispag-key-info-list">
                            <dt><?php _e( 'Deal owner', 'ispag-crm' ); ?></dt>
                            <dd 
                                class="ispag-editable-field" 
                                data-id="<?php echo $deal->id; ?>" 
                                data-name="deal_owner" 
                                data-value="<?php echo esc_attr($deal->deal_owner); ?>"
                                title="<?php _e('Click to edit', 'ispag-crm'); ?>"
                                data-type="select"
                                data-options='<?php echo esc_attr($owner_options_json); ?>'
                            >
                                <?php echo $deal->get_deal_owner_display_name(); ?>
                                <span class="edit-icon">✏️</span>
                            </dd>
                        </dl>

                        <dl class="ispag-key-info-list">
                            <dt><?php _e( 'Last contacted', 'ispag-crm' ); ?></dt>
                            <dd>
                                
                                <?php echo $last_activity_date; ?>
                            </dd>
                        </dl>

                        <dl class="ispag-key-info-list">
                            <dt><?php _e( 'Record source', 'ispag-crm' ); ?></dt>
                            <dd>
                                <?php echo $deal->record_source; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        

            <div class="ispag-main-content">
                <div class="ispag-tabs-navigation">
                    <button class="ispag-tab-btn active" data-tab="overview">
                        <?php esc_html_e( 'Overview', 'ispag-crm' ); ?>
                    </button>
                    <button class="ispag-tab-btn" data-tab="activity">
                        <?php esc_html_e( 'Activities', 'ispag-crm' ); ?>
                    </button>
                    <?php if ( $hubspot_deal_id ) : ?>
                        <button class="ispag-tab-btn" data-tab="articles">
                            <?php esc_html_e( 'Articles', 'ispag-crm' ); ?>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="ispag-tabs-content">
                    
                    <div id="ispag-tab-overview" class="ispag-tab-pane active">
                        <div class="ispag-card">
                            <h5><?php _e( 'Data highlights', 'ispag-crm'); ?></h5>
                            <div data-deal-id="<?php echo $deal->id; ?>" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; font-size: 14px;">
                                
                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Create date', 'ispag-crm'); ?> :</strong>
                                    <span><?php echo date_i18n( 'd.m.Y', strtotime( $deal->date_creation ) ); ?></span>
                                </div>

                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Deal stage', 'ispag-crm'); ?> :</strong>
                                    
                                    <span><?php echo esc_html( $current_stage_label ); ?></span>
                                </div>

                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Last activity date', 'ispag-crm'); ?> :</strong>
                                    
                                    <span><?php echo $last_activity_date; ?></span>
                                </div>

                                <div class="ispag-field-container">
                                </div>  
                                
                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Offer number', 'ispag-crm'); ?> :</strong>
                                    
                                    <span><?php echo $deal->offer_num; ?></span>
                                </div>

                                <div class="ispag-field-container">
                                    <strong><?php _e( 'Project number', 'ispag-crm'); ?> :</strong>
                                    
                                    <span><?php echo $deal->project_num; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div id="ispag-tab-activity" class="ispag-tab-pane">
                        <?php echo $notes_list_full; ?>
                    </div>
                    <?php if ( $hubspot_deal_id ) : ?>

                    <div id="ispag-tab-articles" class="ispag-tab-pane">
                        <a href="<?php echo esc_url( home_url( '/details-du-projet/?deal_id=' . $hubspot_deal_id ) ); ?>" class="button" target="_blank">
                            <?php _e( 'View project details', 'ispag-crm' ); ?>
                        </a>
                        <?php echo display_ispag_project_articles($hubspot_deal_id); ?>
                        
                    </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="ispag-right-panel">
                
                
                <?php if ( $company_id > 0 ) : ?>
                    <div class="ispag-card ispag-company-card">
                        <h5>
                            <?php _e( 'Associated Company', 'ispag-crm'); ?> 
                            <span style="font-size: 12px;">(ID: <?php echo $company_id; ?>)</span>
                        </h5>
                        <?php if ( ! empty( $associated_companies_list ) ): ?>
                        
                            <?php foreach ( $associated_companies_list as $company ): 
                                $last_contact_date_display = date_i18n( 'd.m.Y - h:m', strtotime( $company->last_contact_date ) );
                                ?>
                                <div class="ispag-card" style="font-size: 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <strong style="color: #007bff;">
                                            <a href="<?php echo $company_link; ?>" > <?php echo esc_html( $company->company_name ); ?></a>
                                        </strong>
                                        
                                        <span 
                                            class="ispag-remove-association" 
                                            data-contact-id="<?php echo absint($company->Id); ?>"
                                            data-deal-id="<?php echo absint($deal->id); ?>"
                                            title="<?php esc_attr_e( 'Remove association', 'ispag-crm' ); ?>"
                                            style="color: #e74c3c; cursor: pointer;"
                                        >
                                            <span class="dashicons dashicons-trash"></span>
                                        </span>
                                    </div>
                                    <p style="margin: 5px 0 0;"><?php _e( 'Last contact', 'ispag-crm'); ?>: <?php echo $last_contact_date_display; ?></p>
                                    <p style="margin: 5px 0 0;"><?php _e( 'Phone', 'ispag-crm'); ?>: <?php echo esc_html( $company->phone ); ?></p>
                                    <p style="margin: 5px 0 0;"><?php _e( 'Email', 'ispag-crm'); ?>: <?php echo esc_html( $company->email ); ?></p>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ( $contacts_count >= 5 ): ?>
                                <a href="<?php echo $link_contact_list; ?>?filter_company=<?php echo $company_id; ?>" class="ispag-button-link" target="_blank"><?php _e( 'View all associated Contacts', 'ispag-crm'); ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p style="font-size: 14px; color: #777;"><?php _e( 'No contacts associated with this deal.', 'ispag-crm'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                
                <div class="ispag-card ispag-contacts-card">
                    <h5>
                        <?php _e( 'Contacts', 'ispag-crm'); ?> (<?php echo $contacts_count; ?>) 
                        <span id="open-add-contact-modal" 
                            style="font-size: 12px; color: #007bff; cursor: pointer;" 
                            data-company-id="<?php echo $company_id; ?>"
                            data-deal-group-ref="<?php echo $deal->deal_group_ref; ?>"
                            >
                            + <?php _e( 'Add', 'ispag-crm'); ?>
                        </span>
                    </h5> 
                    
                    <?php if ( ! empty( $associated_contacts_list ) ): ?>
                        
                        <?php foreach ( $associated_contacts_list as $contact ): 
                            $last_contact_date_display = date_i18n( 'd.m.Y - h:m', strtotime( $contact->last_contact_date ) );
                            $contact_link = trailingslashit( $base_url . '/contact/' . $contact->ID );
                            ?>
                            <div class="ispag-card" style="font-size: 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="color: #007bff;">
                                        <a href="<?php echo esc_url($contact_link); ?>" ><?php echo esc_html( $contact->display_name ); ?></a>
                                    </strong>
                                    
                                    <span 
                                        class="ispag-remove-association" 
                                        data-contact-id="<?php echo absint($contact->ID); ?>"
                                        data-deal-id="<?php echo absint($deal->id); ?>"
                                        title="<?php esc_attr_e( 'Remove association', 'ispag-crm' ); ?>"
                                        style="color: #e74c3c; cursor: pointer;"
                                    >
                                        <span class="dashicons dashicons-trash"></span>
                                    </span>
                                </div> 
                                <p style="margin: 5px 0 0;"><?php _e( 'Function', 'ispag-crm'); ?>: <?php echo esc_html( $contact->lead_function ); ?></p>
                                <p style="margin: 5px 0 0;"><?php _e( 'Last contact', 'ispag-crm'); ?>: <?php echo $last_contact_date_display; ?></p>
                                <p style="margin: 5px 0 0;"><?php _e( 'Phone', 'ispag-crm'); ?>: <?php echo esc_html( $contact->phone ); ?></p>
                                <p style="margin: 5px 0 0;"><?php _e( 'Email', 'ispag-crm'); ?>: <?php echo esc_html( $contact->email ); ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ( $contacts_count >= 5 ): ?>
                            <a href="<?php echo $link_contact_list; ?>?filter_company=<?php echo $company_id; ?>" class="ispag-button-link" target="_blank"><?php _e( 'View all associated Contacts', 'ispag-crm'); ?></a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="font-size: 14px; color: #777;"><?php _e( 'No contacts associated with this deal.', 'ispag-crm'); ?></p>
                    <?php endif; ?>
                </div>
                <div id="ispag-modal-container"></div>
            </div>
        </div>

    </div>
        
    
    <?php 
    // ---------------------------------------------------------
    // FERMETURE DE LA CONDITION B (INTÉRIEURE)
    // ---------------------------------------------------------
    else : // Deal ID > 0, mais get_row() a échoué.
    ?>
        <div class="ispag-error-message"><h1>Transaction Introuvable (ID: <?php echo $deal_id; ?>)</h1></div>
    <?php 
    endif; // FIN de la condition B (intérieure)
    
// -----------------------------------------------------------------
// FERMETURE DE LA CONDITION A (EXTÉRIEURE)
// -----------------------------------------------------------------
// ID manquant ou classe Model non trouvée.
else : 
?>
    <div class="ispag-info-message"><h1>Erreur ou Page d'accueil des Transactions.</h1></div>
<?php 
endif; // FIN de la condition A (extérieure)

ispag_get_template( 'deal-reason-for-rejection-modal', [] ); 
get_footer();