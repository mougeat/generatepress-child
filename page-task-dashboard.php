<?php
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
$current_time = time();

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
                    <span class="task-count-badge"><?php echo count($tasks_list); ?> <?php _e('Active tasks', 'ispag-crm'); ?></span>
                </div>
                
                <div class="header-actions">
                    <div class="ispag-search-container">
                        <i class="dashicons dashicons-search"></i>
                        <input type="text" id="taskSearch" placeholder="<?php _e('Search...', 'ispag-crm'); ?>">
                    </div>
                    <button class="ispag-btn-primary">
                        <i class="dashicons dashicons-plus"></i> <?php _e('New task', 'ispag-crm'); ?>
                    </button>
                </div>
            </header>

            <div class="ispag-card">
                <div class="table-responsive">
                    <table class="ispag-table">
                        <thead>
                            <tr>
                                <th class="col-check"></th>
                                <th class="col-task"><?php _e('Task', 'ispag-crm'); ?></th>
                                <th class="col-rel"><?php _e('Relationships', 'ispag-crm'); ?></th>
                                <th class="col-type"><?php _e('Type', 'ispag-crm'); ?></th>
                                <th class="col-date"><?php _e('Due date', 'ispag-crm'); ?></th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php if ( ! empty( $tasks_list ) ) : ?>
                                <?php foreach ( $tasks_list as $task ) : 
                                    $due_ts = strtotime( $task->due_date );
                                    $is_overdue = ( ! $task->is_completed && $due_ts < $current_time );
                                    $type_class = 'type-' . sanitize_title($task->task_type);
                                ?>
                                <tr id="task-<?php echo $task->id; ?>" class="task-row <?php echo $is_overdue ? 'row-overdue' : ''; ?>">
                                    
                                    <td class="col-check">
                                        <div class="custom-checkbox">
                                            <input type="checkbox" id="check-<?php echo $task->id; ?>" class="complete-task-btn" data-activity-id="<?php echo esc_attr( $task->id ); ?>">
                                            <label for="check-<?php echo $task->id; ?>"></label>
                                        </div>
                                    </td>

                                    <td class="col-task">
                                        <span class="task-title-link open-task-sidebar" data-task-id="<?php echo $task->id; ?>">
                                            <?php echo esc_html( $task->title ); ?>
                                        </span>
                                    </td>

                                    <td class="col-rel">
                                        <div class="rel-box">
                                            <?php if($task->contact_name): ?>
                                                <a href="<?php echo home_url('/contact/'.$task->contact_id.'/'); ?>" class="rel-item contact">
                                                    <i class="dashicons dashicons-admin-users"></i> <?php echo esc_html($task->contact_name); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if($task->company_name): ?>
                                                
                                            <a href="<?php echo home_url('/company/'.$task->company_id.'/'); ?>" class="rel-item company">
                                                <i class="dashicons dashicons-bank"></i> <?php echo esc_html($task->company_name); ?>
                                            </a>
                                            <?php endif; ?>
                                            <?php if($task->deal_id): ?>
                                                
                                            <a href="<?php echo home_url('/deal/'.$task->deal_id.'/'); ?>" class="rel-item deal">
                                                <i class="dashicons dashicons-bank"></i> <?php echo esc_html($task->deal_name); ?>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="col-type">
                                        <span class="">
                                            <?php echo esc_html( $task->task_type ); ?>
                                        </span>
                                    </td>

                                    <td class="col-date">
                                        <div class="due-date-wrapper <?php echo $is_overdue ? 'is-late' : ''; ?>">
                                            <i class="dashicons dashicons-calendar-alt"></i>
                                            <?php echo date_i18n( 'j M Y', $due_ts ); ?>
                                        </div>
                                    </td>

                                    <td class="col-actions">
                                        <div class="btn-group">
                                            <button class="icon-btn edit-activity" data-activity-id="<?php echo $task->id; ?>" title="Modifier">
                                                <i class="dashicons dashicons-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="6" class="empty-msg"><?php _e('No active tasks at the moment.', 'ispag-crm'); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>