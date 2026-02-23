<?php
/**
 * Template pour les champs de recherche de deal
 * Variables attendues : $kanban_filters (array)
 */
?>

<input type="text" id="ispag-kanban-search" 
        placeholder="<?php _e('Search Project, Ref or Company...', 'ispag-crm'); ?>"
        class="ispag-search-field" 
        value="<?php echo esc_attr($kanban_filters['search']); ?>">

<span class="ispag-kanban-filter-wrapper">
    <select id="ispag-kanban-closing-date-filter" name="closing_date_filter">
        <option value="all" <?php selected( $kanban_filters['closing_date'] ?? 'all', 'all' ); ?>><?php _e('Close date', 'ispag-crm'); ?></option>
        <option value="older_than_last_year" <?php selected( $kanban_filters['closing_date'] ?? '', 'older_than_last_year' ); ?>><?php _e('Older than last year', 'ispag-crm'); ?></option>        
        <option value="last_year" <?php selected( $kanban_filters['closing_date'] ?? '', 'last_year' ); ?>><?php _e('Last year', 'ispag-crm'); ?></option>        
        <option value="last_month" <?php selected( $kanban_filters['closing_date'] ?? '', 'last_month' ); ?>><?php _e('Last month', 'ispag-crm'); ?></option>    
        <option value="last_week" <?php selected( $kanban_filters['closing_date'] ?? '', 'last_week' ); ?>><?php _e('Last week', 'ispag-crm'); ?></option>    
        <option value="yesterday" <?php selected( $kanban_filters['closing_date'] ?? '', 'yesterday' ); ?>><?php _e('Yesterday', 'ispag-crm'); ?></option>        
        <option value="today" <?php selected( $kanban_filters['closing_date'] ?? '', 'today' ); ?>><?php _e('Today', 'ispag-crm'); ?></option>
        <option value="this_week" <?php selected( $kanban_filters['closing_date'] ?? '', 'this_week' ); ?>><?php _e('This week', 'ispag-crm'); ?></option>
        <option value="next_week" <?php selected( $kanban_filters['closing_date'] ?? '', 'next_week' ); ?>><?php _e('Next week', 'ispag-crm'); ?></option>
        <option value="this_month" <?php selected( $kanban_filters['closing_date'] ?? '', 'this_month' ); ?>><?php _e('This month', 'ispag-crm'); ?></option>
        <option value="next_month" <?php selected( $kanban_filters['closing_date'] ?? '', 'next_month' ); ?>><?php _e('Next month', 'ispag-crm'); ?></option>
    </select>
</span>

<span class="ispag-kanban-filter-wrapper">
    <select id="ispag-kanban-create-date-filter" name="create_date_filter">
        <option value="all" <?php selected( $kanban_filters['create_date'] ?? 'all', 'all' ); ?>><?php _e('Create date', 'ispag-crm'); ?></option>
        <option value="older_than_last_year" <?php selected( $kanban_filters['create_date'] ?? '', 'older_than_last_year' ); ?>><?php _e('Older than last year', 'ispag-crm'); ?></option>        
        <option value="last_year" <?php selected( $kanban_filters['create_date'] ?? '', 'last_year' ); ?>><?php _e('Last year', 'ispag-crm'); ?></option>        
        <option value="last_month" <?php selected( $kanban_filters['create_date'] ?? '', 'last_month' ); ?>><?php _e('Last month', 'ispag-crm'); ?></option>
        <option value="last_week" <?php selected( $kanban_filters['create_date'] ?? '', 'last_week' ); ?>><?php _e('Last week', 'ispag-crm'); ?></option>
        <option value="today" <?php selected( $kanban_filters['create_date'] ?? '', 'today' ); ?>><?php _e('Today', 'ispag-crm'); ?></option>
        <option value="yesterday" <?php selected( $kanban_filters['create_date'] ?? '', 'yesterday' ); ?>><?php _e('Yesterday', 'ispag-crm'); ?></option>
        <option value="this_week" <?php selected( $kanban_filters['create_date'] ?? '', 'this_week' ); ?>><?php _e('This week', 'ispag-crm'); ?></option>
        <option value="this_month" <?php selected( $kanban_filters['create_date'] ?? '', 'this_month' ); ?>><?php _e('This month', 'ispag-crm'); ?></option>
    </select>
</span>

<span class="ispag-kanban-filter-wrapper">
    <select id="ispag-kanban-owner-filter">
        <option value="all"><?php _e('All deals Owners', 'ispag-crm'); ?></option>
        
        <?php
        // Récupération des utilisateurs ayant un département défini
        $owners = get_users([
            'meta_query' => [
                [
                    'key'     => ISPAG_Crm_Contact_Constants::USER_DEPARTMENT,
                    'compare' => 'EXISTS', // On s'assure que la meta existe
                ],
                [
                    'key'     => ISPAG_Crm_Contact_Constants::USER_DEPARTMENT,
                    'value'   => '',
                    'compare' => '!=', // On s'assure qu'elle n'est pas vide
                ]
            ],
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ]);

        if (!empty($owners)) :
            foreach ($owners as $owner) : 
                // Récupération de la valeur du département pour l'affichage (optionnel)
                $dept = get_user_meta($owner->ID, ISPAG_Crm_Contact_Constants::USER_DEPARTMENT, true);
                ?>
                <option value="<?php echo esc_attr($owner->ID); ?>">
                    <?php echo esc_html($owner->display_name); ?> 
                    <?php echo !empty($dept) ? '('.esc_html($dept).')' : ''; ?>
                </option>
            <?php endforeach;
        endif;
        ?>
    </select>
</span>

<button id="ispag-clear-filters-btn" class="button ispag-btn-secondary-outlined">
    <?php _e('Clear Filters', 'ispag-crm'); ?>
</button>