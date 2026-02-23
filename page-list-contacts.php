<?php
/**
 * Template Name: ISPAG Contact List Viewer
 * Template Post Type: page
 * Description: Affiche la liste paginée et filtrable des contacts (ISPAG CRM) - Version Optimisée avec Indicateurs de Tri.
 */

get_header();

if ( ! class_exists( 'ISPAG_Crm_Contacts_Repository' ) || ! class_exists( 'ISPAG_Crm_Contact_Constants' ) ) {
    echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
    echo '<div class="ispag-error-message">' . __( 'Technical error: Required CRM classes are missing.', 'ispag-crm' ) . '</div>';
    echo '</main></div>';
    get_footer();
    return;
}

$contacts_repo = new ISPAG_Crm_Contacts_Repository();
global $wpdb; 

// --- GESTION DES URLS ET PAGINATION ---
$current_url = remove_query_arg( array( 'paged', 'bulk_result', 'count' ), $_SERVER['REQUEST_URI'] );

$a = shortcode_atts( array(
    'orderby' => 'display_name',
    'order'   => 'ASC',
    'limit'   => 50,
), $atts ?? array() );

$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : $a['orderby'];
$order   = isset( $_GET['order'] ) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';
$search  = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

$limit  = absint( $a['limit'] );
$paged  = ( isset( $_GET['paged'] ) && absint($_GET['paged']) > 1 ) ? absint( $_GET['paged'] ) : 1; 
$offset = ( $paged - 1 ) * $limit;

// Filtres
$filter_owner_id      = isset( $_GET['filter_owner'] ) ? absint( $_GET['filter_owner'] ) : 0;
$filter_company_id    = isset( $_GET['filter_company'] ) ? absint( $_GET['filter_company'] ) : 0;
$filter_lifecycle_key = isset( $_GET['filter_lifecycle'] ) ? sanitize_key( $_GET['filter_lifecycle'] ) : '';
$filter_status_key    = isset( $_GET['filter_status'] ) ? sanitize_key( $_GET['filter_status'] ) : '';

// --- EXÉCUTION VIA REPOSITORY ---
$repo_args = array(
    'orderby'          => $orderby,
    'order'            => $order,
    'search'           => $search,
    'limit'            => $limit,
    'offset'           => $offset,
    'filter_owner'     => $filter_owner_id,
    'filter_company'   => $filter_company_id,
    'filter_lifecycle' => $filter_lifecycle_key,
    'filter_status'    => $filter_status_key,
);

$results = $contacts_repo->get_contacts_list_optimized( $repo_args );
$contacts    = $results['contacts'];
$total_users = $results['total'];
$total_pages = ceil( $total_users / $limit );

?>
<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <h1><?php the_title(); ?> (<?php echo $total_users; ?>)</h1>

        <form method="get" class="ispag-contact-filter-form" action="<?php echo esc_url( $current_url ); ?>">
            <div class="filter-group">
                <input type="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e('Name or Email', 'ispag-crm'); ?>" />
                
                <select name="filter_owner">
                    <option value="0"><?php esc_html_e( 'All Owners', 'ispag-crm' ); ?></option>
                    <?php 
                    $owners_options = $contacts_repo->get_ispag_owners_options();
                    foreach ( $owners_options as $id => $name ) : 
                        if ($id === '') continue;
                        echo "<option value='".esc_attr($id)."' ".selected($filter_owner_id, $id, false).">".esc_html($name)."</option>";
                    endforeach;
                    ?>
                </select>
            
                <button type="submit" class="button button-secondary"><?php esc_html_e( 'Filter / Search', 'ispag-crm' ); ?></button>

                <?php if ( ! empty( $search ) || $filter_owner_id > 0 ) : ?>
                    <a href="<?php echo esc_url( remove_query_arg( array( 'orderby', 'order', 'search', 'filter_owner', 'paged' ) ) ); ?>" class="button ispag-btn-grey"><?php esc_html_e( 'Reset filters', 'ispag-crm' ); ?></a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ( empty( $contacts ) ) : ?>
            <p class="ispag-no-results"><?php esc_html_e( 'No contacts found.', 'ispag-crm' ); ?></p>
        <?php else : ?>

        <table class="ispag-contact-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="ispag-select-all" /></th>
                    <?php 
                    $sortable_columns = array(
                        'display_name'                                      => __( 'Contact Name', 'ispag-crm' ),
                        ISPAG_Crm_Contact_Constants::META_LEAD_FUNCTION     => __( 'Function', 'ispag-crm' ), 
                        ISPAG_Crm_Contact_Constants::PRIORITY_LEVEL         => __( 'Priority', 'ispag-crm' ), 
                        ISPAG_Crm_Contact_Constants::META_COMPANY_ID        => __( 'Company', 'ispag-crm' ), 
                        ISPAG_Crm_Contact_Constants::META_LEAD_STATUS       => __( 'Status', 'ispag-crm' ), 
                        ISPAG_Crm_Contact_Constants::META_LIFECYCLE_PHASE   => __( 'Lifecycle', 'ispag-crm' ), 
                        ISPAG_Crm_Contact_Constants::META_LAST_CONTACT_DATE => __( 'Last Contact', 'ispag-crm' ), 
                        ISPAG_Crm_Contact_Constants::META_OWNER             => __( 'Owner', 'ispag-crm' ), 
                        'user_email'                                        => __( 'Email', 'ispag-crm' ),
                    );
                    
                    foreach ( $sortable_columns as $key => $label ) : 
                        $is_current = ( $orderby === $key );
                        $new_order  = ( $is_current && $order === 'ASC' ) ? 'DESC' : 'ASC';
                        
                        // Classes WordPress pour les indicateurs visuels
                        $th_classes = array( 'manage-column', 'column-' . $key, 'sortable' );
                        if ( $is_current ) {
                            $th_classes[] = 'sorted';
                            $th_classes[] = strtolower( $order ); 
                        } else {
                            $th_classes[] = 'desc'; // Par défaut flèche vers le bas pour le prochain clic
                        }
                    ?>
                        <th class="<?php echo esc_attr( implode( ' ', $th_classes ) ); ?>">
                            <a href="<?php echo esc_url( add_query_arg( array( 'orderby' => $key, 'order' => $new_order, 'paged' => 1 ) ) ); ?>">
                                <span><?php echo esc_html( $label ); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $company_repo = new ISPAG_Crm_Company_Repository();
                foreach ( $contacts as $contact ) : 
                    $custom_contact_url = site_url( '/contact/' . $contact->ID . '/' );
                    
                    // Récupération dynamique des propriétés
                    $status_key     = ISPAG_Crm_Contact_Constants::META_LEAD_STATUS;
                    $lifecycle_key  = ISPAG_Crm_Contact_Constants::META_LIFECYCLE_PHASE;
                    $owner_key      = ISPAG_Crm_Contact_Constants::META_OWNER;
                    $date_key       = ISPAG_Crm_Contact_Constants::META_LAST_CONTACT_DATE;
                    $comp_id_key    = ISPAG_Crm_Contact_Constants::META_COMPANY_ID;
                    $lead_function  = ISPAG_Crm_Contact_Constants::META_LEAD_FUNCTION;
                    $priority_level  = ISPAG_Crm_Contact_Constants::PRIORITY_LEVEL;

                    $owner_display_name = !empty($contact->$owner_key) ? get_the_author_meta( 'display_name', $contact->$owner_key ) : __('Non assigné', 'ispag-crm');
                    $last_contact_date  = !empty($contact->$date_key) ? date_i18n( get_option( 'date_format' ), strtotime( $contact->$date_key ) ) : '—';
                    $avatar_url         = $contact->avatar_url;
                    
                    $company_name = '—';
                    if ( ! empty( $contact->$comp_id_key ) ) {
                        $company = $company_repo->get_company_by_viag_id( $contact->$comp_id_key );
                        if ( $company ) $company_name = $company->company_name;
                    }
                ?> 
                <tr>
                    <th class="check-column"><input type="checkbox" name="contact_id[]" value="<?php echo $contact->ID; ?>" /></th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="ispag-company-icon-container <?php echo ($avatar_url) ? 'has-favicon' : 'no-favicon'; ?>">
                                <?php if ($avatar_url) : ?>
                                    <img src="<?php echo esc_url($avatar_url); ?>" 
                                        alt="Avatar" 
                                        class="ispag-avatar-img"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    
                                    <span class="ispag-initials" style="display: none;"><?php echo esc_html($initials); ?></span>
                                <?php else : ?>
                                    <span class="ispag-initials"><?php echo esc_html($initials); ?></span>
                                <?php endif; ?>
                            </div>
                            <strong><a href="<?php echo esc_url( $custom_contact_url ); ?>"><?php echo esc_html( $contact->display_name ); ?></a></strong>
                        </div>
                        
                    </td>
                    <td><?php echo esc_html( $contact->$lead_function ?? '—' ); ?></td>
                    <td><?php echo esc_html( $contact->priority_level ?? '—' ); ?></td>
                    <td><?php echo esc_html( $company_name ); ?></td>
                    <td><?php echo $contacts_repo->get_lead_status_badge( $contact->$status_key ); ?></td>
                    <td><?php echo $contacts_repo->get_lifecycle_phase_badge( $contact->$lifecycle_key ); ?></td>
                    <td><?php echo esc_html( $last_contact_date ); ?></td> 
                    <td><?php echo esc_html( $owner_display_name ); ?></td>
                    <td><a href="mailto:<?php echo esc_attr( $contact->user_email ); ?>"><?php echo esc_html( $contact->user_email ); ?></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <ul class="page-numbers">
                <?php 
                $links = paginate_links( array(
                    'base'    => add_query_arg( 'paged', '%#%' ),
                    'total'   => $total_pages,
                    'current' => $paged,
                    'type'    => 'array'
                ) );
                if ( $links ) foreach ( $links as $link ) echo '<li>' . $link . '</li>'; 
                ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
    </main>
</div>

<style>
    /* CSS pour forcer l'affichage des Dashicons de tri si absent du thème */
    .manage-column.sortable a .sorting-indicator:before {
        content: "\f156";
        font-family: dashicons;
        display: inline-block;
        color: #ccd0d4;
    }
    .manage-column.sorted.asc .sorting-indicator:before { content: "\f142"; color: #444; }
    .manage-column.sorted.desc .sorting-indicator:before { content: "\f140"; color: #444; }
    
    .tablenav .page-numbers { display: flex; list-style: none; padding: 0; gap: 5px; }
    .tablenav .page-numbers li span.current { background: #2271b1; color: #fff; padding: 5px 10px; }
    .tablenav .page-numbers li a { text-decoration: none; padding: 5px 10px; border: 1px solid #ccd0d4; }
</style>

<?php get_footer(); ?>