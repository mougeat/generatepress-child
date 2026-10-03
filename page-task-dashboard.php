<?php
defined('ABSPATH') || exit;
/**
 * Template Name: ISPAG Task Dashboard Modern
 * Template Post Type: page
 */

if ( ! is_user_logged_in() ) {
    get_header();
    echo '<div class="ispag-container"><p class="ispag-error">' . esc_html__( 'Please log in to view your tasks.', 'ispag-crm' ) . '</p></div>';
    get_footer();
    exit;
}

$task_repo = class_exists( 'ISPAG_Note_Repository' ) ? new ISPAG_Note_Repository() : null;
$tasks_list = $task_repo ? $task_repo->get_active_tasks() : [];
$current_time = current_time('timestamp');

// Titre de la page
$page_name = __('Task dashboard', 'ispag-crm');
add_filter('pre_get_document_title', function($title) use ($page_name) {
    return $page_name . ' | ' . get_bloginfo('name');
}, 999);

get_header();
?>

<div id="primary" class="content-area ispag-dark-mode-ready">
    <main id="main" class="site-main">
        <div class="ispag-dashboard-wrapper">

            <header class="ispag-dash-header">
                <div class="header-left">
                    <h1 class="ispag-page-title"><?php the_title(); ?></h1>
                    <span class="task-count-badge"><span id="task-total-count"><?php echo count( $tasks_list ); ?></span> <?php _e( 'Active tasks', 'ispag-crm' ); ?></span>
                </div>

                <div class="header-actions">
                    <div class="ispag-search-container">
                        <i class="dashicons dashicons-search"></i>
                        <input type="search" id="taskSearch" placeholder="<?php esc_attr_e( 'Search...', 'ispag-crm' ); ?>">
                    </div>
                    <button class="ispag-action-btn ispag-btn ispag-btn-primary" data-action="task">
                        <i class="dashicons dashicons-plus"></i> <?php _e( 'New task', 'ispag-crm' ); ?>
                    </button>
                </div>
            </header>

            <div class="ispag-task-filters" role="toolbar">
                <div class="ispag-task-chips">
                    <button type="button" class="ispag-chip is-active" data-filter="all"><?php _e( 'All', 'ispag-crm' ); ?> <span class="chip-count" data-count="all">0</span></button>
                    <button type="button" class="ispag-chip chip-danger" data-filter="overdue"><?php _e( 'Overdue', 'ispag-crm' ); ?> <span class="chip-count" data-count="overdue">0</span></button>
                    <button type="button" class="ispag-chip" data-filter="today"><?php _e( 'Today', 'ispag-crm' ); ?> <span class="chip-count" data-count="today">0</span></button>
                    <button type="button" class="ispag-chip" data-filter="week"><?php _e( 'This week', 'ispag-crm' ); ?> <span class="chip-count" data-count="week">0</span></button>
                </div>
                <span class="ispag-kanban-filter-wrapper">
                    <select id="taskTypeFilter" aria-label="<?php esc_attr_e( 'Type', 'ispag-crm' ); ?>">
                        <option value=""><?php _e( 'All types', 'ispag-crm' ); ?></option>
                        <?php foreach ( array_unique( array_map( fn( $t ) => strtolower( $t->task_type ), $tasks_list ) ) as $type ) : ?>
                            <option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( ucfirst( $type ) ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>
            </div>

            <div class="ispag-card">
                <div class="table-responsive">
                    <table class="ispag-table ispag-task-table"
                           data-today-end="<?php echo (int) ( strtotime( date( 'Y-m-d', $current_time ) ) + DAY_IN_SECONDS - 1 ); ?>"
                           data-week-end="<?php echo (int) ( strtotime( date( 'Y-m-d', strtotime( 'sunday this week', $current_time ) ) ) + DAY_IN_SECONDS - 1 ); ?>"
                           data-labels='<?php echo esc_attr( wp_json_encode( [
                               'overdue' => __( 'Overdue', 'ispag-crm' ),
                               'today'   => __( 'Today', 'ispag-crm' ),
                               'week'    => __( 'This week', 'ispag-crm' ),
                               'later'   => __( 'Later', 'ispag-crm' ),
                               'done'    => __( 'Task completed', 'ispag-crm' ),
                               'snoozed' => __( 'Task postponed', 'ispag-crm' ),
                               'error'   => __( 'Something went wrong', 'ispag-crm' ),
                           ] ) ); ?>'>
                        <thead>
                            <tr>
                                <th class="col-check"></th>
                                <th class="col-task"><?php _e( 'Task', 'ispag-crm' ); ?></th>
                                <th class="col-rel"><?php _e( 'Relationships', 'ispag-crm' ); ?></th>
                                <th class="col-type"><?php _e( 'Type', 'ispag-crm' ); ?></th>
                                <th class="col-date"><?php _e( 'Due date', 'ispag-crm' ); ?></th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php foreach ( $tasks_list as $task ) {
                                echo ispag_get_template( 'task-row', [ 'task' => $task ] );
                            } ?>
                            <tr class="empty-state-row" <?php echo $tasks_list ? 'style="display:none"' : ''; ?>>
                                <td colspan="6" class="empty-msg">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php _e( 'No active tasks at the moment.', 'ispag-crm' ); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php 
if ( is_active_sidebar( 'ispag_crm_sidebar' ) ) {
    dynamic_sidebar( 'ispag_crm_sidebar' );
}
?>
<?php

get_footer(); ?>