<?php
/**
 * Template Name: ISPAG Deals Kanban Viewer
 * Template Post Type: page
 * Description: Affiche le tableau Kanban des transactions (Deals/Projets) ISPAG.
 */

get_header(); // Charge l'en-tête du thème

// --- DÉFINITION DES FILTRES ET DÉPENDANCES (Crucial!) ---

// Valeurs par défaut
$kanban_filters = [
    'owner'  => get_current_user_id(), // Filtre par défaut: utilisateur courant (si non écrasé)
    'status' => 'open',                  // Filtre par défaut: seulement les deals ouverts
    'closing_date' => 'all', 
    'create_date'  => 'all',
    'search'       => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
];

$company_id_filter = null;
$contact_id_filter = null;
$current_owner_filter = absint( $kanban_filters['owner'] ); // Utilisateur courant par défaut
$search_term = '';

// 1. GESTION DES FILTRES BASÉS SUR L'URL (URL_DU_SITE/deals/...)
$request_uri = $_SERVER['REQUEST_URI'];

// a) Analyse des filtres Company/Contact (basés sur le chemin)
if ( preg_match( '#/company/(\d+)#i', $request_uri, $matches ) ) {
    // URL_DU_SITE/deals/company/company_id
    $company_id_filter = absint( $matches[1] );
    // NOTE: Le Deal Repository devra gérer ce filtre spécifique.
    
} elseif ( preg_match( '#/contact/(\d+)#i', $request_uri, $matches ) ) {
    // URL_DU_SITE/deals/contact/contact_id
    $contact_id_filter = absint( $matches[1] );
    // NOTE: Le Deal Repository devra gérer ce filtre spécifique.
}

// b) Analyse du filtre Owner (basé sur $_GET['owner'])
if ( isset( $_GET['owner'] ) ) {
    $owner_param = sanitize_text_field( $_GET['owner'] );
    if ( $owner_param === 'all' ) {
        $current_owner_filter = 'all';
    } else {
        $current_owner_filter = absint( $owner_param );
    }
}
// c) NOUVEAU: Analyse du filtre de Recherche (basé sur $_GET['search'])
if ( isset( $_GET['search'] ) ) {
    // On sanitise le terme de recherche
    $search_term = sanitize_text_field( $_GET['search'] );
}

// d) NOUVEAU: Analyse des filtres de dates
if ( isset( $_GET['closing_date'] ) ) {
    $kanban_filters['closing_date'] = sanitize_key( $_GET['closing_date'] );
}
if ( isset( $_GET['create_date'] ) ) {
    $kanban_filters['create_date'] = sanitize_key( $_GET['create_date'] );
}

// Mise à jour finale du filtre Owner
$kanban_filters['owner'] = $current_owner_filter; 

// Ajout du terme de recherche si présent
$kanban_filters['search'] = null;
if ( ! empty( $search_term ) ) {
    $kanban_filters['search'] = $search_term;
}

// --- 2. Vérifications et Initialisation des Repositories ---
$deal_repo  = null;
$stage_repo = null;

if ( class_exists( 'ISPAG_Crm_Deals_Repository' ) && class_exists( 'ISPAG_Crm_Deal_Stages_Repository' ) ) {
    $deal_repo  = new ISPAG_Crm_Deals_Repository();
    $stage_repo = new ISPAG_Crm_Deal_Stages_Repository();
}

// --- 3. Récupération des données (avec le nouveau filtre d'entité) ---
if ( $deal_repo && $stage_repo ) {
    
    // Ajout des nouveaux filtres à la liste à passer au Repository
    if ( $company_id_filter ) {
        $kanban_filters['company_id'] = $company_id_filter;
    }
    if ( $contact_id_filter ) {
        $kanban_filters['contact_id'] = $contact_id_filter;
    }
    
    // Les étapes (colonnes) dans le bon ordre
    $stages_list = $stage_repo->get_all_stages( true ); 
    
    // Les deals groupés par leur étape
    // Le Repository devra être mis à jour pour traiter 'company_id' et 'contact_id'
    $deals_by_stage = $deal_repo->get_all_deals_grouped_by_stage( $kanban_filters );

    // error_log(print_r($deals_by_stage, true));
    
    

} else {
    // Si les classes ne sont pas trouvées, on affiche un message d'erreur.
    $stages_list = [];
    $deals_by_stage = [];
    echo '<p class="ispag-error">Erreur: Les composants du CRM (Repositories) sont indisponibles. Assurez-vous que les classes sont chargées.</p>';
}

?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php 
        while ( have_posts() ) : the_post(); 
        ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <header class="entry-header ispag-kanban-header">
                    <?php 
                    the_title( '<h1 class="entry-title">', '</h1>' ); 
                    ?>
                    <p class="ispag-board-controls">
                        
                        <?php 
                            // Appelle le template et lui passe les données
                            ispag_get_template( 'deal-search', [ 'kanban_filters' => $kanban_filters ] ); 
                        ?>

                        <a href="<?php echo home_url('/deals-list/'); ?>" class="button ispag-btn-secondary-outlined">
                            <span class="dashicons dashicons-list-view"></span> <?php _e('Table view', 'ispag-crm'); ?>
                        </a>
                        
                        </p>
                </header>
                
                <div class="entry-content ispag-kanban-content">
                    
                    <?php if ( ! empty( $stages_list ) ) : ?>
                    
                        <div class="ispag-kanban-board">
                            
                            <?php 
                            // Boucle sur toutes les étapes récupérées
                            foreach ( $stages_list as $stage_model ) : 
                                $stage_key = $stage_model->stage_key;

                                // Filtration pour n'afficher que les étapes 'ouvertes' si le filtre 'status' est 'open'
                                if ( $kanban_filters['status'] === 'open' && $stage_model->is_closed == 1 ) {
                                    continue; // Saute les colonnes "Closed Won" et "Closed Lost"
                                }
                                
                                // Récupération des deals pour cette étape (peut être un tableau vide)
                                // Utilisez l'opérateur null-coalescing pour éviter une erreur si l'étape n'a aucun deal.
                                $deals_in_stage = $deals_by_stage[ $stage_key ] ?? [];
                                $deals_count    = count( $deals_in_stage );
                                
                                // Calcul des totaux
                                $total_amount      = array_sum( array_column( $deals_in_stage, 'total_excl_vat' ) );
                                $weighted_amount   = $stage_model->get_weighted_amount( $total_amount );
                                $stage_color = esc_attr( $stage_model->stage_color );
                                ?>

                                <div class="kanban-column" data-stage-key="<?php echo $stage_key; ?>">
                                    
                                    <div class="kanban-column-header" style="border-top: 3px solid <?php echo $stage_color; ?>;">
                                        <h4>
                                            <?php echo esc_html( $stage_model->stage_label ); ?> 
                                            (<?php echo $deals_count; ?>)
                                        </h4>
                                    </div>
                                    
                                    <div class="kanban-column-body ispag-deals-dropzone" data-stage-key="<?php echo $stage_key; ?>">
                                        <?php if ( ! empty( $deals_in_stage ) ) : ?>
                                            <?php foreach ( $deals_in_stage as $deal ) : 
                                                // $deal est un objet ISPAG_Crm_Deal_Model chargé
                                                $last_activity_date       = $deal->last_activity_date ? date_i18n( 'd.m.Y', strtotime( $deal->last_activity_date ) ) : __('N/A', 'ispag-crm');
                                                ?>
                                                <div class="kanban-deal-card" 
                                                     data-deal-id="<?php echo absint( $deal->id ); ?>"
                                                     style="border-left-color: <?php echo $stage_color; ?>;"
                                                     draggable="true"
                                                >
                                                    <p class="deal-title">
                                                        <strong><a href="<?php echo $deal->get_deal_detail_link(); ?>"><?php echo esc_html( $deal->project_name ); ?></a></strong> 
                                                    </p>
                                                    <p class="deal-info amount">
                                                        <?php _e('Total amount', 'ispag-crm'); ?>: <?php echo number_format( (float) $deal->total_excl_vat, 0, '.', '\'' ); ?> CHF
                                                    </p>
                                                    <p class="deal-info close-date">
                                                        <?php _e('Closing date', 'ispag-crm'); ?>: <?php echo date_i18n( 'd.m.Y', strtotime( $deal->closing_date ) ); ?>
                                                    </p>
                                                    <p class="deal-info last-contact-date">
                                                        <?php _e('Last contact', 'ispag-crm'); ?>: <?php echo $last_activity_date; ?>
                                                    </p>
                                                    <p class="deal-owner"></p> 
                                                    <p class="deal-relation"><?php _e('Company', 'ispag-crm'); ?>: <?php echo $deal->associated_company_name; ?></p>
                                                    <p class="deal-relation"><?php _e('Contact', 'ispag-crm'); ?>: <?php echo $deal->associated_contact_names; ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="no-deals-message"><?php echo __( 'No deal', 'ispag-crm' ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="kanban-column-footer">
                                        <p class="total-amount"><?php _e('Total amount', 'ispag-crm'); ?>: **<?php echo number_format( $total_amount, 0, '.', '\'' ); ?> CHF**</p>
                                        <p class="weighted-amount">(<?php echo $stage_model->probability; ?>%) <?php _e('Weighted amount', 'ispag-crm'); ?>: **<?php echo number_format( $weighted_amount, 0, '.', '\'' ); ?> CHF**</p>
                                    </div>
                                </div>
                            <?php endforeach; // Fin de la boucle des étapes ?>
                        </div>
                    
                    <?php else: ?>
                        <div class="ispag-info-message"><p><?php _e('No stages configured for the Kanban board.', 'ispag-crm'); ?></p></div>
                    <?php endif; ?>
                    
                </div></article><?php endwhile; // Fin de la boucle WordPress ?>

    </main></div>
    
    <script>
    
    </script>

<?php 
get_footer();