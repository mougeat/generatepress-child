<?php
/**
 * Template Name: ISPAG Company List Viewer
 * Template Post Type: page
 * Description: Displays a paginated, sortable, and filterable list of companies (ISPAG CRM).
 */

if ( ! class_exists( 'ISPAG_Crm_Company_Repository' ) ) {
    get_header();
    echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
    echo '<div class="ispag-error-message">' . __( 'Technical error: Required CRM classes are missing.', 'ispag-crm' ) . '</div>';
    echo '</main></div>';
    get_footer();
    return;
}

/**
 * Generates a sort link for column headers while maintaining current filters.
 */
function get_ispag_sort_link($col_name, $current_orderby, $current_order) {
    $new_order = ($current_orderby === $col_name && $current_order === 'ASC') ? 'DESC' : 'ASC';
    return add_query_arg( array( 
        'orderby' => $col_name, 
        'order'   => $new_order,
        'paged'   => 1 
    ) );
}

// 1. Query Parameters Initialization
$paged    = absint( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
$limit    = 50; 
$offset   = ($paged - 1) * $limit;
$orderby  = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'company_name';
$order    = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
$search   = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$priority = isset($_GET['priority']) ? sanitize_text_field($_GET['priority']) : '';
$city     = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
$owner_id = isset($_GET['owner_id']) ? absint($_GET['owner_id']) : 0;

// 2. Data Fetching via Repository
$repository = new ISPAG_Crm_Company_Repository();
$data = $repository->get_companies_list( compact( 
    'orderby', 'order', 'search', 'priority', 'city', 'owner_id', 'limit', 'offset' 
) );
$distinct_cities = $repository->get_distinct_cities();

$companies       = $data['companies'];
$total_companies = $data['total'];
$num_pages       = ceil( $total_companies / $limit );

// 3. Pagination Arguments
$pagination_args = array(
    'base'      => add_query_arg( 'paged', '%#%' ),
    'format'    => '',
    'prev_text' => __( '« Previous', 'ispag-crm' ),
    'next_text' => __( 'Next »', 'ispag-crm' ),
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

// --- OWNER FILTER CONFIGURATION ---
$target_dept_filter = 'vaulruz_ispag'; 
$meta_key_dept = ISPAG_Crm_Contact_Constants::USER_DEPARTMENT;

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

$selected_owner = isset($_GET['owner_id']) ? absint($_GET['owner_id']) : 0;

$page_name = __('Companies', 'ispag-crm');
add_filter('pre_get_document_title', function($title) use ($page_name) {
    if (!empty($page_name)) {
        $site_name = get_bloginfo('name');
        return $page_name . ' | ' . $site_name;
    }
    return $title;
}, 999);

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title">
                <?php _e( 'Company List', 'ispag-crm' ); ?> (<?php echo $total_companies; ?>)
            </h1>
        </header>

        <div class="ispag-toolbar" style="background: #f6f7f7; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
            <form method="get" class="ispag-search-form">
                <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>" />
                <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>" />

                <input type="search" name="search" placeholder="<?php esc_attr_e('Search...', 'ispag-crm'); ?>" value="<?php echo esc_attr( $search ); ?>" style="line-height: 28px;" />

                <select name="owner_id" onchange="this.form.submit()" style="line-height: 28px;">
                    <option value=""><?php _e( 'Owner (All)', 'ispag-crm' ); ?></option>
                    <?php foreach ( $users_vaulruz as $u ) : ?>
                        <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $selected_owner, $u->ID ); ?>>
                            <?php echo esc_html( $u->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="priority" onchange="this.form.submit()">
                    <option value=""><?php _e( 'Priority (All)', 'ispag-crm' ); ?></option>
                    <option value="A" <?php selected( $priority, 'A' ); ?>>A - High</option>
                    <option value="B" <?php selected( $priority, 'B' ); ?>>B - Medium</option>
                    <option value="C" <?php selected( $priority, 'C' ); ?>>C - Low</option>
                </select>

                <select name="city" onchange="this.form.submit()">
                    <option value=""><?php _e( 'City (All)', 'ispag-crm' ); ?></option>
                    <?php foreach ( $distinct_cities as $c ) : ?>
                        <option value="<?php echo esc_attr($c); ?>" <?php selected( $city, $c ); ?>><?php echo esc_html($c); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="ispag-btn button-primary"><?php _e( 'Filter', 'ispag-crm' ); ?></button>
                
                <?php if ( ! empty( $search ) || ! empty( $priority ) || ! empty( $city ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="ispag-btn button-secondary"><?php _e( 'Reset', 'ispag-crm' ); ?></a>
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
                            <span><?php _e( 'Name', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'is_active' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('is_active', $orderby, $order) ); ?>">
                            <span><?php _e( 'Active', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'isIngenieur' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('isIngenieur', $orderby, $order) ); ?>">
                            <span><?php _e( 'Engineer', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'compagny_domain' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('compagny_domain', $orderby, $order) ); ?>">
                            <span><?php _e( 'Domain', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'priority_level' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('priority_level', $orderby, $order) ); ?>">
                            <span><?php _e( 'Priority', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'last_contact_date' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('last_contact_date', $orderby, $order) ); ?>">
                            <span><?php _e( 'Last Contact', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'city' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('city', $orderby, $order) ); ?>">
                            <span><?php _e( 'City', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th class="manage-column <?php echo $orderby === 'department_owner' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <?php _e( 'Owner', 'ispag-crm' ); ?>
                    </th>
                    
                    <th style="text-align: center;" class="manage-column <?php echo $orderby === 'nb_contacts' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('nb_contacts', $orderby, $order) ); ?>">
                            <span><?php _e( 'Contacts', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>

                    <th style="text-align: center;" class="manage-column <?php echo $orderby === 'nb_transactions' ? 'sorted ' . strtolower($order) : 'sortable'; ?>">
                        <a href="<?php echo esc_url( get_ispag_sort_link('nb_transactions', $orderby, $order) ); ?>">
                            <span><?php _e( 'Deals', 'ispag-crm' ); ?></span><span class="sorting-indicator"></span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $companies ) ) : ?>
                    <?php foreach ( $companies as $company ) : 
                        $company_app_url = home_url( '/company/' . $company->viag_id . '/' );
                        $c_name = $company->company_name;
                        $words = explode(' ', trim($c_name));
                        $initials = (count($words) > 1) 
                            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                            : strtoupper(substr($c_name, 0, 2));
                    ?>
                        <tr>
                            <th class="check-column"><input type="checkbox" name="ispag_company_ids[]" value="<?php echo absint($company->Id); ?>" /></th>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
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
                            <td><?php echo ( $company->is_active == 1 ) ? '✅' : '❌'; ?></td>
                            <td><?php echo ( $company->isIngenieur == 1 ) ? '✅' : '❌'; ?></td>
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
                                    <span style="color: #999; font-style: italic;"><?php _e( 'Not assigned', 'ispag-crm' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;"><span class="ispag-count-bubble"><?php echo absint( $company->nb_contacts ); ?></span></td>
                            <td style="text-align: center;"><span class="ispag-count-bubble"><?php echo absint( $company->nb_transactions ); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="11" style="padding: 20px; text-align: center;"><?php _e( 'No results found.', 'ispag-crm' ); ?></td></tr>
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
<?php get_footer(); ?>