<?php
/**
 * Ligne du tableau des tâches.
 * Variable attendue : $task (objet formaté par ISPAG_Note_Repository::format_tasks)
 */
$now_ts      = current_time( 'timestamp' );
$today_start = strtotime( date( 'Y-m-d', $now_ts ) );
$due_ts      = strtotime( $task->due_date ) ?: $now_ts;
$due_day     = strtotime( date( 'Y-m-d', $due_ts ) );
$days_diff   = (int) round( ( $due_day - $today_start ) / DAY_IN_SECONDS );   // <0 = en retard
$has_time    = date( 'H:i', $due_ts ) !== '00:00';
// Sans heure précise (00:00), une tâche du jour n'est pas en retard avant demain
$is_overdue  = $has_time ? $due_ts < $now_ts : $due_day < $today_start;

if ( $days_diff < 0 )       $due_label = sprintf( _n( '%d day late', '%d days late', -$days_diff, 'ispag-crm' ), -$days_diff );
elseif ( $days_diff === 0 ) $due_label = __( 'Today', 'ispag-crm' ) . ( $has_time ? ' ' . date( 'H:i', $due_ts ) : '' );
elseif ( $days_diff === 1 ) $due_label = __( 'Tomorrow', 'ispag-crm' ) . ( $has_time ? ' ' . date( 'H:i', $due_ts ) : '' );
else                        $due_label = date_i18n( 'D j M', $due_ts );

$type_key   = strtolower( $task->task_type );
$type_icons = [ 'call' => 'phone', 'email' => 'email-alt', 'mail' => 'email-alt', 'meeting' => 'groups', 'whatsapp' => 'format-chat' ];
$type_icon  = $type_icons[ $type_key ] ?? 'yes-alt';

$first_id = fn( $v ) => absint( explode( ',', (string) $v )[0] );
$search   = strtolower( wp_strip_all_tags( $task->title . ' ' . $task->contact_name . ' ' . $task->company_name . ' ' . $task->deal_name ) );
?>
<tr id="task-<?php echo (int) $task->id; ?>"
    class="task-row <?php echo $is_overdue ? 'row-overdue' : ''; ?>"
    data-due="<?php echo (int) $due_ts; ?>"
    data-type="<?php echo esc_attr( $type_key ); ?>"
    data-search="<?php echo esc_attr( $search ); ?>">

    <td class="col-check" data-label="Done?">
        <div class="custom-checkbox">
            <input type="checkbox" id="check-<?php echo (int) $task->id; ?>" class="complete-task-btn" data-activity-id="<?php echo esc_attr( $task->id ); ?>">
            <label for="check-<?php echo (int) $task->id; ?>" title="<?php esc_attr_e( 'Mark as done', 'ispag-crm' ); ?>"></label>
        </div>
    </td>

    <td class="col-task" data-label="<?php esc_attr_e( 'Task', 'ispag-crm' ); ?>">
        <span class="task-title-link open-task-sidebar" data-task-id="<?php echo (int) $task->id; ?>"><?php echo esc_html( $task->title ?: __( '(no title)', 'ispag-crm' ) ); ?></span>
    </td>

    <td class="col-rel" data-label="<?php esc_attr_e( 'Relationships', 'ispag-crm' ); ?>">
        <div class="rel-box">
            <?php if ( $task->contact_name && $task->contact_name !== __( 'Not defined', 'ispag-crm' ) && $first_id( $task->contact_id ) ) : ?>
                <a href="<?php echo esc_url( home_url( '/contact/' . $first_id( $task->contact_id ) . '/' ) ); ?>" class="rel-item contact">
                    <i class="dashicons dashicons-admin-users"></i> <?php echo wp_kses( $task->contact_name, [ 'span' => [ 'class' => [] ], 'a' => [ 'href' => [], 'class' => [] ] ] ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $task->company_name && $task->company_name !== __( 'Not defined', 'ispag-crm' ) && $first_id( $task->company_id ) ) : ?>
                <a href="<?php echo esc_url( home_url( '/company/' . $first_id( $task->company_id ) . '/' ) ); ?>" class="rel-item company">
                    <i class="dashicons dashicons-building"></i> <?php echo esc_html( $task->company_name ); ?>
                </a>
            <?php endif; ?>
            <?php if ( ! empty( $task->deal_id ) ) : ?>
                <a href="<?php echo esc_url( home_url( '/deal/' . absint( $task->deal_id ) . '/' ) ); ?>" class="rel-item deal">
                    <i class="dashicons dashicons-portfolio"></i> <?php echo esc_html( $task->deal_name ); ?>
                </a>
            <?php endif; ?>
        </div>
    </td>

    <td class="col-type" data-label="<?php esc_attr_e( 'Type', 'ispag-crm' ); ?>">
        <span class="task-type-badge type-<?php echo esc_attr( sanitize_title( $task->task_type ) ); ?>">
            <i class="dashicons dashicons-<?php echo esc_attr( $type_icon ); ?>"></i> <?php echo esc_html( $task->task_type ); ?>
        </span>
    </td>

    <td class="col-date" data-label="<?php esc_attr_e( 'Due date', 'ispag-crm' ); ?>">
        <div class="due-date-wrapper <?php echo $is_overdue ? 'is-late' : ( $days_diff === 0 ? 'is-today' : '' ); ?>" title="<?php echo esc_attr( date_i18n( 'j F Y H:i', $due_ts ) ); ?>">
            <i class="dashicons dashicons-calendar-alt"></i>
            <?php echo esc_html( $due_label ); ?>
        </div>
    </td>

    <td class="col-actions" data-label="<?php esc_attr_e( 'Actions', 'ispag-crm' ); ?>">
        <div class="btn-group">
            <div class="task-snooze">
                <button type="button" class="icon-btn snooze-toggle" title="<?php esc_attr_e( 'Postpone', 'ispag-crm' ); ?>" aria-haspopup="true">
                    <i class="dashicons dashicons-clock"></i>
                </button>
                <div class="snooze-menu" role="menu">
                    <button type="button" class="snooze-option" data-days="1"><?php _e( 'Tomorrow', 'ispag-crm' ); ?></button>
                    <button type="button" class="snooze-option" data-days="3"><?php _e( 'In 3 days', 'ispag-crm' ); ?></button>
                    <button type="button" class="snooze-option" data-days="7"><?php _e( 'In 1 week', 'ispag-crm' ); ?></button>
                </div>
            </div>
            <button type="button" class="icon-btn edit-activity" data-activity-id="<?php echo (int) $task->id; ?>" title="<?php esc_attr_e( 'Edit', 'ispag-crm' ); ?>">
                <i class="dashicons dashicons-edit"></i>
            </button>
        </div>
    </td>
</tr>
