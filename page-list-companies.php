<?php
/**
 * Template Name: ISPAG Company List Viewer
 * Template Post Type: page
 * Description: Affiche la liste paginée, triable et filtrable des entreprises (ISPAG CRM).
 */

get_header();

if ( ! class_exists( 'ISPAG_Crm_Company_Repository' ) ) {
    echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
    echo '<div class="ispag-error-message">' . __( 'Technical error: Required CRM classes are missing.', 'ispag-crm' ) . '</div>';
    echo '</main></div>';
    get_footer();
    return;
}

/**
 * Génère un lien de tri pour les en-têtes de colonnes en conservant les filtres actuels.
 */
function get_ispag_sort_link($col_name, $current_orderby, $current_order) {
    $new_order = ($current_orderby === $col_name && $current_order === 'ASC') ? 'DESC' : 'ASC';
    return add_query_arg( array( 
        'orderby' => $col_name, 
        'order'   => $new_order,
        'paged'   => 1 // Reset à la page 1 lors d'un changement de tri
    ) );
}

// 1. Initialisation des paramètres de requête
$paged    = absint( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
$limit    = 50; 
$offset   = ($paged - 1) * $limit;
$orderby  = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'company_name';
$order    = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
$search   = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$priority = isset($_GET['priority']) ? sanitize_text_field($_GET['priority']) : '';
$city     = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
$owner_id = isset($_GET['owner_id']) ? absint($_GET['owner_id']) : 0;

// 2. Récupération des données via le Repository
$repository = new ISPAG_Crm_Company_Repository();
$data = $repository->get_companies_list( compact( 
    'orderby', 'order', 'search', 'priority', 'city', 'owner_id', 'limit', 'offset' 
) );
$distinct_cities = $repository->get_distinct_cities();

$companies       = $data['companies'];
$total_companies = $data['total'];
$num_pages       = ceil( $total_companies / $limit );

// 3. Préparation des arguments de pagination
$pagination_args = array(
    'base'      => add_query_arg( 'paged', '%#%' ),
    'format'    => '',
    'prev_text' => __( '« Précédent', 'ispag-crm' ),
    'next_text' => __( 'Suivant »', 'ispag-crm' ),
    'total'     => $num_pages,
    'current'   => $paged,
    'type'      => 'array',
    'add_args'  => array_filter( array(
        'search'   => $search,
        'priority' => $priority,
        'city'     => $city,
        'owner_id' => $owner_id,
        'orderby'  => $orderby,
        'order'    => $order
    ) )
);


// --- CONFIGURATION DU FILTRE OWNER ---
$target_dept_filter = 'vaulruz_ispag'; // Le département fixe pour cette liste
$meta_key_dept = ISPAG_Crm_Contact_Constants::USER_DEPARTMENT;  // La clé meta que vous utilisez pour les users

// 1. Récupérer uniquement les utilisateurs du département Vaulruz
$users_vaulruz = get_users( array( 
    'fields'     => array( 'ID', 'display_name' ), 
    'orderby'    => 'display_name',
    'meta_query' => array(
        array(
            'key'     => $meta_key_dept, 
            'value'   => $target_dept_filter, 
            'compare' => '=' 
        ),
    ),
) );

// 2. Récupérer l'ID sélectionné dans l'URL pour garder le filtre actif
$selected_owner = isset($_GET['owner_id']) ? absint($_GET['owner_id']) : 0;
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title"><?php _e( 'Liste des Entreprises', 'ispag-crm' ); ?> (<?php echo $total_companies; ?>)</h1>
        </header>

        <div class="ispag-toolbar" style="background: #f6f7f7; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
            <form method="get" class="ispag-search-form">
                <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>" />
                <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>" />

                <input type="search" name="search" placeholder="Rechercher..." value="<?php echo esc_attr( $search ); ?>" style="line-height: 28px;" />

                <select name="owner_id" onchange="this.form.submit()" style="line-height: 28px;">
                    <option value=""><?php _e( 'Responsable (Tous)', 'ispag-crm' ); ?></option>
                    <?php foreach ( $users_vaulruz as $u ) : ?>
                        <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $selected_owner, $u->ID ); ?>>
                            <?php echo esc_html( $u->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="priority" onchange="this.form.submit()">
                    <option value=""><?php _e( 'Priorité (Toutes)', 'ispag-crm' ); ?></option>
                    <option value="A" <?php selected( $priority, 'A' ); ?>>A - High</option>
                    <option value="B" <?php selected( $priority, 'B' ); ?>>B - Medium</option>
                    <option value="C" <?php selected( $priority, 'C' ); ?>>C - Low</option>
                </select>

                <select name="city" onchange="this.form.submit()">
                    <option value=""><?php _e( 'Ville (Toutes)', 'ispag-crm' ); ?></option>
                    <?php foreach ( $distinct_cities as $c ) : ?>
                        <option value="<?php echo esc_attr($c); ?>" <?php selected( $city, $c ); ?>><?php echo esc_html($c); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="button button-primary"><?php _e( 'Filtrer', 'ispag-crm' ); ?></button>
                
                <?php if ( ! empty( $search ) || ! empty( $priority ) || ! empty( $city ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="button button-secondary"><?php _e( 'Réinitialiser', 'ispag-crm' ); ?></a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ( $num_pages > 1 ) : ?>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <?php 
                    $page_links = paginate_links( $pagination_args );
                    if ( is_array( $page_links ) ) {
                        echo '<ul class="page-numbers">';
                        foreach ( $page_links as $link ) { echo '<li>' . $link . '</li>'; }
                        echo '</ul>';
                    }
                    ?>
                </div>
                <br class="clear" />
            </div>
        <?php endif; ?>

        <table class="ispag-company-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" /></th>
                    
                    <th class="manage-column <?php echo $orderby === 'company_name' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('company_name', $orderby, $order) ); ?>">
                            <span><?php _e( 'Nom', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'compagny_domain' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('compagny_domain', $orderby, $order) ); ?>">
                            <span><?php _e( 'Domain', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'priority_level' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('priority_level', $orderby, $order) ); ?>">
                            <span><?php _e( 'Priorité', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'last_contact_date' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('last_contact_date', $orderby, $order) ); ?>">
                            <span><?php _e( 'Dernier contact', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'city' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('city', $orderby, $order) ); ?>">
                            <span><?php _e( 'Ville', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'department_owner' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <?php _e( 'Responsable', 'ispag-crm' ); ?>
                    </th>
                    
                    <th style="text-align: center;" class="manage-column <?php echo $orderby === 'nb_contacts' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('nb_contacts', $orderby, $order) ); ?>">
                            <span><?php _e( 'Contacts', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th style="text-align: center;" class="manage-column <?php echo $orderby === 'nb_transactions' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('nb_transactions', $orderby, $order) ); ?>">
                            <span><?php _e( 'Deals ouverts', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $companies ) ) : ?>
                    <?php foreach ( $companies as $company ) : 
                        $company_app_url = home_url( '/company/' . $company->viag_id . '/' );
                    ?>
                        <tr>
                            <th class="check-column"><input type="checkbox" name="ispag_company_ids[]" value="<?php echo absint($company->Id); ?>" /></th>
                            
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php  
                                    // 1. Récupération des données (à adapter selon tes variables d'objet)
                                    $favicon = $company->favicon ?? ''; // URL de l'image
                                    $c_name  = $company->company_name;
                                    
                                    // 2. Calcul des initiales (2 premières lettres ou première lettre de chaque mot)
                                    $words = explode(' ', trim($c_name));
                                    $initials = (count($words) > 1) 
                                        ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                        : strtoupper(substr($c_name, 0, 2));
                                    ?>
 
                                    <div class="ispag-company-icon-container <?php echo ($company->favicon) ? 'has-favicon' : 'no-favicon'; ?>">
                                        <?php if ($company->favicon) : ?>
                                            <img src="<?php echo esc_url($company->favicon); ?>" alt="Favicon">
                                        <?php else : ?>
                                            <span class="ispag-initials"><?php echo esc_html($initials); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <a href="<?php echo esc_url( $company_app_url ); ?>">
                                        <strong><?php echo esc_html( $c_name ); ?></strong>
                                    </a>
                                </div>
                            </td>
                            <td><?php echo esc_html( $company->compagny_domain ); ?></td>
                            <td>
                                <?php 
                                $prio = strtoupper($company->priority_level);
                                $colors = ['A' => '#d63031', 'B' => '#e67e22', 'C' => '#2980b9'];
                                $color = $colors[$prio] ?? '#f0f0f0';
                                ?>
                                <span class="ispag-status-badge" style="background-color: <?php echo $color; ?>; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                    <?php echo $prio ?: '—'; ?>
                                </span>
                            </td>
                            <td><?php echo $company->last_contact_date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $company->last_contact_date ) ) ) : '—'; ?></td>
                            <td><?php echo esc_html( $company->city ); ?></td>
                            <td>
                                <?php if ( ! empty( $company->current_owner_name ) ) : ?>
                                    <span class="ispag-owner-badge">
                                        <span class="dashicons dashicons-admin-users" style="font-size: 14px; vertical-align: middle;"></span>
                                        <?php echo esc_html( $company->current_owner_name ); ?>
                                    </span>
                                <?php else : ?>
                                    <span style="color: #999; font-style: italic;"><?php _e( 'Non assigné', 'ispag-crm' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><span class="ispag-count-bubble"><?php echo absint( $company->nb_contacts ); ?></span></td>
                            <td style="text-align: center;"><span class="ispag-count-bubble"><?php echo absint( $company->nb_transactions ); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7" style="padding: 20px; text-align: center;"><?php _e( 'Aucun résultat.', 'ispag-crm' ); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ( $num_pages > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php 
                    $page_links_bottom = paginate_links( $pagination_args );
                    if ( is_array( $page_links_bottom ) ) {
                        echo '<ul class="page-numbers">';
                        foreach ( $page_links_bottom as $link ) { echo '<li>' . $link . '</li>'; }
                        echo '</ul>';
                    }
                    ?>
                </div>
                <br class="clear" />
            </div>
        <?php endif; ?>

    </main>
</div>

<style>
    /* Styles pour la pagination et les indicateurs de tri */
    ul.page-numbers { display: flex; list-style: none; padding: 0; margin: 10px 0; gap: 2px; }
    ul.page-numbers li { margin: 0; padding: 0; }
    ul.page-numbers a, ul.page-numbers span { padding: 6px 12px; border: 1px solid #ccd0d4; background: #fff; text-decoration: none; color: #2271b1; }
    ul.page-numbers .current { background: #2271b1; color: #fff; border-color: #2271b1; }
    .ispag-count-bubble { background: #eee; border-radius: 10px; padding: 2px 8px; font-size: 11px; font-weight: 600; }
    .manage-column.sortable a span { float: left; }
    .sorting-indicator:before { content: "\f156"; font-family: dashicons; color: #ccd0d4; padding-left: 5px; }
    .sorted.asc .sorting-indicator:before { content: "\f142"; color: #444; }
    .sorted.desc .sorting-indicator:before { content: "\f140"; color: #444; }
</style>

<?php get_footer(); ?>