<?php
/**
 * Template Name: ISPAG Template Dashboard
 * Template Post Type: page
 * Description: Interface de gestion des templates d'emails pour ISPAG.
 */

get_header();

if ( ! is_user_logged_in() ) {
    echo '<p class="ispag-error">' . esc_html__( 'You must be logged in to view templates.', 'ispag-crm' ) . '</p>';
    get_footer();
    exit;
}

// Initialisation des classes
$repo  = new ISPAG_Template_Repository();
$modal = new ISPAG_Template_Modal();

$current_user_id = get_current_user_id();
$is_admin = current_user_can('administrator');

// Récupération des templates (par défaut en français)
$templates_list = $repo->get_templates_for_user($current_user_id, '');
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <header class="entry-header ispag-kanban-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                </header>

                <div class="ispag-task-dashboard-container">
                    
                    <div class="ispag-task-controls-header">
                        <div class="ispag-search-bar">
                            <input type="text" id="tpl-search" placeholder="<?php esc_attr_e('Search templates...', 'ispag-crm'); ?>" class="ispag-search-input">
                            <button class="ispag-search-button"><span class="dashicons dashicons-search"></span></button>
                        </div>

                        <div class="ispag-filter-group" style="display:flex; gap:10px;">
                            <select id="tpl-filter-owner" class="ispag-filter-select">
                                <option value="all"><?php _e('All Owners', 'ispag-crm'); ?></option>
                                <option value="common"><?php _e('Shared (Admin)', 'ispag-crm'); ?></option>
                                <option value="mine"><?php _e('My Templates', 'ispag-crm'); ?></option>
                            </select>

                            <button type="button" class="button" id="ispag-add-new-folder" style="background-color: #f1f1f1; border-color: #ccc; color: #333;">
                                <span class="dashicons dashicons-category" style="vertical-align: middle;"></span> 
                                <?php _e('New Folder', 'ispag-crm'); ?>
                            </button>

                            <button type="button" class="button button-primary" id="ispag-add-new-tpl" style="background-color: #800000; border-color: #600000;">
                                <span class="dashicons dashicons-plus" style="vertical-align: middle;"></span> 
                                <?php _e('Add New Template', 'ispag-crm'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="ispag-task-table-wrapper">
                        <table class="widefat fixed striped ispag-task-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column column-title"><?php _e('Template Name', 'ispag-crm'); ?></th>
                                    <th scope="col" class="manage-column"><?php _e('Folder', 'ispag-crm'); ?></th>
                                    <th scope="col" class="manage-column"><?php _e('Language', 'ispag-crm'); ?></th>
                                    <th scope="col" class="manage-column"><?php _e('Owner', 'ispag-crm'); ?></th>
                                    <th scope="col" class="manage-column column-due-date"><?php _e('Last Modified', 'ispag-crm'); ?></th>
                                    <th scope="col" style="width: 100px;"></th>
                                </tr>
                            </thead>
                            
                            <tbody id="the-list">
                                <?php if ( ! empty( $templates_list ) ) : ?>
                                    
                                    <?php foreach ( $templates_list as $tpl ) : 
                                        $can_edit = $is_admin || ($tpl->owner_id == $current_user_id);
                                        $owner_name = $tpl->owner_id ? get_userdata($tpl->owner_id)->display_name : __('Shared (Admin)', 'ispag-crm');
                                        $folder_name = $tpl->folder_name ? $tpl->folder_name : '—';
                                        
                                        // On définit une classe pour le filtrage JS
                                        $row_class = is_null($tpl->owner_id) ? 'ispag-tpl-common' : 'ispag-tpl-personal';
                                    ?>
                                        <tr class="<?php echo $row_class; ?>">
                                            <td class="title column-title has-row-actions column-primary">
                                                <strong>
                                                    <?php if ($can_edit) : ?>
                                                        <a href="#" class="ispag-edit-template row-title" data-id="<?php echo $tpl->id; ?>">
                                                            <?php echo esc_html( $tpl->name ); ?>
                                                        </a>
                                                    <?php else : ?>
                                                        <span style="color:#666; cursor:not-allowed;">
                                                            <?php echo esc_html( $tpl->name ); ?> <span class="dashicons dashicons-lock" style="font-size:14px;"></span>
                                                        </span>
                                                    <?php endif; ?>
                                                </strong>
                                            </td>
                                            
                                            <td><span class="ispag-folder-badge"><?php echo esc_html($folder_name); ?></span></td>
                                            
                                            <td><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/flags/' . $tpl->language . '.png'; ?>" alt="" style="width:16px; vertical-align:middle;"> <?php echo strtoupper($tpl->language); ?></td>
                                            
                                            <td><?php echo esc_html($owner_name); ?></td>
                                            
                                            <td class="due-date">
                                                <?php echo human_time_diff(strtotime($tpl->updated_at), current_time('timestamp')) . ' ' . __('ago', 'ispag-crm'); ?>
                                            </td>

                                            <td style="text-align:right;">
                                                <?php if ($can_edit) : ?>
                                                    <button class="button button-small ispag-edit-template" data-id="<?php echo $tpl->id; ?>">
                                                        <?php _e('Edit', 'ispag-crm'); ?>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6"><?php _e('No templates found.', 'ispag-crm'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="ispag-horizontal-scroll-hint"></div>
                </div>
            </article>

        <?php endwhile; ?>

    </main>
</div>

<?php 
// CHARGEMENT DE LA MODAL
$modal->render();


// Modal pour editer les Folders
?>
<div id="ispag-folder-modal" class="ispag-modal" style="display:none;">
    <div class="ispag-modal-content" style="max-width: 400px; height: auto;">
        <div class="ispag-modal-header">
            <h3><?php _e('Create New Folder', 'ispag-crm'); ?></h3>
            <span class="ispag-close-folder-modal">&times;</span>
        </div>
        <div class="ispag-modal-body" style="padding: 20px;">
            <div class="ispag-form-group">
                <label><?php _e('Folder Name', 'ispag-crm'); ?></label>
                <input type="text" id="new-folder-name" placeholder="ex: Relances Offres" style="width:100%;">
            </div>

            <div class="ispag-form-group" style="margin-top:15px; display:flex; align-items:center; gap:10px;">
                <?php 
                $is_admin = current_user_can('administrator');
                $checked = !$is_admin ? 'checked' : 'checked'; // Par défaut coché pour tout le monde
                $disabled = !$is_admin ? 'disabled' : '';
                ?>
                <input type="checkbox" id="folder-is-personal" <?php echo $checked; ?> <?php echo $disabled; ?>>
                <label for="folder-is-personal" style="margin:0; cursor:pointer; font-size:13px;">
                    <?php _e('Private Folder', 'ispag-crm'); ?>
                </label>
            </div>

            <div id="folder-status-msg" style="margin-top:10px; font-size:12px;"></div>
        </div>
        <div class="ispag-modal-footer" style="padding: 15px; text-align: right; border-top: 1px solid #eee;">
            <button class="button" id="cancel-folder"><?php _e('Cancel', 'ispag-crm'); ?></button>
            <button class="button button-primary" id="save-new-folder"><?php _e('Create', 'ispag-crm'); ?></button>
        </div>
    </div>
</div>

<?php

get_footer();