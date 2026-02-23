<?php
/**
 * Template Name: ISPAG Task Dashboard
 * Template Post Type: page
 * Description: Affiche le tableau des tâches ouvertent par user
 */


get_header(); // Charge l'en-tête du thème

if ( ! is_user_logged_in() ) {

    echo '<p class="ispag-error">' . esc_html__( 'You must be logged in to view your tasks.', 'ispag-crm' ) . '</p>';
    get_footer();
}

if ( class_exists( 'ISPAG_Note_Repository' )  ) {
    $task_repo  = new ISPAG_Note_Repository();
}
//récupération des tâches
if ( $task_repo ) {
    $tasks_list = $task_repo->get_active_tasks();
}

$current_date = time(); // Utiliser pour colorer les dates dépassées
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
                    if ( empty( $tasks_list ) ) {
                        // Ajoute l'ID pour le ciblage JS si la liste devient vide
                        echo '<p id="ispag-no-tasks-message">' . esc_html__( 'You have no active tasks.', 'ispag-crm' ) . '</p>'; 
                        get_footer();
                        return;
                    }
                    ?>
                    
                </header>
                
                
                <div class="ispag-task-dashboard-container">
    
                    <div class="ispag-task-controls-header">
                        <div class="ispag-search-bar">
                            <input type="text" placeholder="<?php _e('Search task title and note', 'ispag'); ?>" class="ispag-search-input">
                            <button class="ispag-search-button"><span class="dashicons dashicons-search"></span></button>
                        </div>
                        
                    </div>

                    <div class="ispag-task-table-wrapper">
                        <table class="widefat fixed striped ispag-task-table">
                            
                            <thead>
                                <tr>
                                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                                        

                                    </th>
                                    <th scope="col" id="title" class="manage-column column-title sortable desc">
                                        <a href="#"><?php _e('Title', 'ispag'); ?><span class="sorting-indicator"></span></a>
                                    </th>
                                    <th scope="col" id="contact" class="manage-column column-contact">
                                        <a href="#"><?php _e('Associated Contact', 'ispag'); ?><span class="sorting-indicator"></span></a>
                                    </th>
                                    <th scope="col" id="company" class="manage-column column-company">
                                        <a href="#"><?php _e('Associated Company', 'ispag'); ?><span class="sorting-indicator"></span></a>
                                    </th>

                                    <th scope="col" id="task_type" class="manage-column column-task-type sortable desc">
                                        <a href="#"><?php _e('Task Type', 'ispag'); ?><span class="sorting-indicator"></span></a>
                                    </th>
                                    <th scope="col" id="due_date" class="manage-column column-due-date sortable asc ispag-due-date-column">
                                        <a href="#"><?php _e('Due Date', 'ispag'); ?><span class="sorting-indicator"></span></a>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            
                            <tbody id="the-list">
                                
                                <?php if ( ! empty( $tasks_list ) ) : ?>
                                    
                                    <?php foreach ( $tasks_list as $task ) : 
                                        
                                        $task_due_timestamp = strtotime( $task->due_date );
                                        // On vérifie si la date existe et n'est pas vide
                                        $last_contacted_raw = !empty($task->last_contacted) ? $task->last_contacted : null;

                                        if ($last_contacted_raw) {
                                            $last_contacted_timestamp = strtotime($last_contacted_raw);
                                        } else {
                                            // Si pas de date, on met une date très ancienne (ex: 0) 
                                            // ou on gère l'affichage spécifique "Jamais"
                                            $last_contacted_timestamp = 0; 
                                        }
                                        $is_overdue = ( ! $task->is_completed && $task_due_timestamp < $current_date );
                                        
                                        // Récupération de la date au format souhaité (Mois Jour, Année) pour l'affichage
                                        $display_due_date = date_i18n( 'j F Y', $task_due_timestamp );
                                        $display_last_contacted = date_i18n( 'j F Y', $last_contacted_timestamp );
                                        
                                        // Définition de la classe CSS pour la date d'échéance (si dépassée)
                                        $due_date_class = $is_overdue ? 'ispag-overdue-date' : '';
                                        
                                        // Ajout d'un style pour le soulignement ou la couleur de la date (comme dans l'image)
                                        $due_date_style = $is_overdue ? 'style="color: #c00;"' : '';

                                        // En supposant que vous ayez une fonction pour générer les liens de détail
                                        $company_id     = absint( $task->company_id );
                                        $contact_link   = home_url( '/contact/' . $task->contact_id . '/' );
                                        $company_link   = $task->company_id ? home_url( '/company/' . $task->company_id . '/' ) : null;
                                        $deal_link      = home_url( '/deal/' . $task->deal_id . '/' );

                                        $task_link = '#task-detail-' . absint( $task->id );
                                        
                                        
                                        
                                        ?>
                                        <tr id="task-<?php echo absint( $task->id ); ?>" class="iedit author-self task-table-row task-<?php echo $task->is_completed ? 'completed' : 'open'; ?>">
                                            
                                            <th scope="row" class="check-column">
                                                    <div class="task-checkbox">
                                                        <input type="checkbox" id="task-<?php echo $task->id; ?>" class="task-input">
                                                        <label for="task-<?php echo $task->id; ?>" class="task-circle">
                                                            <span class="checkmark">✓</span>
                                                        </label>
                                                    </div>
                                                
                                            </th>
                                            
                                            <td class="title column-title has-row-actions column-primary" data-colname="<?php _e('Title', 'ispag'); ?>">
                                                
                                                <strong><a href="#" 
                                                    class="row-title open-task-sidebar"
                                                    data-task-id="<?php echo $task->id; ?>"
                                                    aria-label="Ouvrir les détails de la tâche <?php echo $task->id; ?>">
                                                        <?php echo esc_html( $task->title ); ?>
                                                </a></strong>
                                            </td>
                                            
                                            <td class="associated-contact column-contact" data-colname="<?php _e('Associated Contact', 'ispag'); ?>">
                                                <i class="fa fa-address-card" aria-hidden="true"></i> <a href="<?php echo $contact_link; ?>" target="_blank"><?php echo wp_kses_post( $task->contact_name ); ?></a>
                                            </td>
                                            
                                            <td class="associated-company column-company" data-colname="<?php _e('Associated Company', 'ispag'); ?>">
                                                <i class="fa fa-building" aria-hidden="true"></i> <a href="<?php echo $company_link; ?>" target="_blank"><?php echo wp_kses_post( $task->company_name ); ?></a>
                                            </td>
                                            
                                            <td class="task-type column-task-type" data-colname="<?php _e('Task Type', 'ispag'); ?>">
                                                <?php echo esc_html( $task->task_type ); ?>
                                            </td>
                                            
                                            <td class="due-date column-due-date <?php echo esc_attr( $due_date_class ); ?>" data-colname="<?php _e('Due Date', 'ispag'); ?>" <?php echo $due_date_style; ?>>
                                                <?php echo esc_html( $display_due_date ); ?>
                                            </td>
                                            <td>
                                                <button class="ispag-btn button-small edit-activity" data-activity-id="<?php echo esc_attr( $task->id ); ?>" ><?php echo esc_html__( 'Edit', 'ispag-crm' ); ?></button>
                                                <button class="ispag-btn complete-task-btn button-small button-primary" data-activity-id="<?php echo esc_attr( $task->id ); ?>"><?php echo esc_html__( 'Done', 'ispag-crm' ); ?></button>
                                                 
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                <?php else : ?>
                                    <tr>
                                        <td colspan="8"><?php _e('No tasks found.', 'ispag'); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                            </tbody>
                            
                        </table>
                    </div>
                    
                    <div class="ispag-horizontal-scroll-hint"></div>
                    
                </div>
            </article><?php endwhile; // Fin de la boucle WordPress ?>

    </main></div>


<?php 
get_footer();