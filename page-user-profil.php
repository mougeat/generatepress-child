<?php
/**
 * Template Name: ISPAG Page profil
 */
get_header();

// Vérifier que l'utilisateur est connecté
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$user = wp_get_current_user();
$user_id = $user->ID;
$error = '';
$success = '';

// Gestion des messages de succès/erreur
if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
    $success = __('Your profile has been updated successfully!', 'ispag-crm');
}
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}

// Récupérer les données pour les préférences de notifications
$available_types = ISPAG_Notifications_Manager::get_available_notification_types();
$available_channels = ISPAG_Notifications_Manager::get_available_channels();
$notification_prefs = get_user_meta($user_id, 'ispag_notif_prefs', true);

// Regrouper les types par groupe
$grouped_types = [];
foreach ($available_types as $type_key => $type_info) {
    if (!user_can($user_id, $type_info['capability'])) {
        continue;
    }
    $group_key = isset($type_info['group']) ? $type_info['group'] : __('General', 'ispag-crm');
    $grouped_types[$group_key][$type_key] = $type_info;
}

// Récupérer les préférences de déconnexion
$allow_weekend = (bool) get_user_meta($user_id, 'ispag_allow_weekend_notifications', true);
$holiday_periods_json = get_user_meta($user_id, 'ispag_holiday_periods', true);
$holiday_periods = json_decode($holiday_periods_json, true) ?: [];
?>

<div class="wrap user-profile-ispag">
    <h1><?php _e('My Profile', 'ispag-crm'); ?></h1>

    <?php if ($error) : ?>
        <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
    <?php endif; ?>

    <?php if ($success) : ?>
        <div class="notice notice-success"><p><?php echo esc_html($success); ?></p></div>
    <?php endif; ?>

    <!-- Formulaire pour l'email et le nom d'affichage -->
    <div class="ispag-profile-section">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="profile-form">
            <?php wp_nonce_field('update_user_profile', 'user_profile_nonce'); ?>
            <input type="hidden" name="action" value="update_user_profile">

            <h2><?php _e('Basic Information', 'ispag-crm'); ?></h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="display_name"><?php _e('Display Name', 'ispag-crm'); ?></label></th>
                        <td>
                            <input type="text" name="display_name" id="display_name" value="<?php echo esc_attr($user->display_name); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="email"><?php _e('Email', 'ispag-crm'); ?></label></th>
                        <td>
                            <input type="email" name="user_email" id="email" value="<?php echo esc_attr($user->user_email); ?>" class="regular-text" required>
                            <p class="description"><?php _e('Make sure to use a valid email address.', 'ispag-crm'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Update', 'ispag-crm'); ?></button>
            </p>
        </form>
    </div>

    <!-- Formulaire pour le mot de passe -->
    <div class="ispag-profile-section">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="profile-form">
            <?php wp_nonce_field('update_user_password', 'user_password_nonce'); ?>
            <input type="hidden" name="action" value="update_user_password">

            <h2><?php _e('Change Password', 'ispag-crm'); ?></h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="current_password"><?php _e('Current Password', 'ispag-crm'); ?></label></th>
                        <td>
                            <input type="password" name="current_password" id="current_password" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="new_password"><?php _e('New Password', 'ispag-crm'); ?></label></th>
                        <td>
                            <input type="password" name="new_password" id="new_password" class="regular-text" required>
                            <p class="description"><?php _e('Use a strong password.', 'ispag-crm'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="confirm_password"><?php _e('Confirm New Password', 'ispag-crm'); ?></label></th>
                        <td>
                            <input type="password" name="confirm_password" id="confirm_password" class="regular-text" required>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Change Password', 'ispag-crm'); ?></button>
            </p>
        </form>
    </div>

    <!-- Formulaire pour l'avatar -->
    <div class="ispag-profile-section">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="profile-form">
            <?php wp_nonce_field('update_user_avatar', 'user_avatar_nonce'); ?>
            <input type="hidden" name="action" value="update_user_avatar">

            <h2><?php _e('Avatar', 'ispag-crm'); ?></h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><?php _e('Current Avatar', 'ispag-crm'); ?></th>
                        <td>
                            <?php echo get_avatar($user_id, 96); ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="avatar_upload"><?php _e('Upload New Avatar', 'ispag-crm'); ?></label></th>
                        <td>
                            <input type="file" name="avatar_upload" id="avatar_upload" accept="image/*">
                            <p class="description"><?php _e('Files must be in JPG, PNG, or GIF format.', 'ispag-crm'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Update Avatar', 'ispag-crm'); ?></button>
            </p>
        </form>
    </div>

    <!-- Formulaire pour les préférences de notifications -->
    <div class="ispag-profile-section">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="profile-form">
            <?php wp_nonce_field('update_notification_prefs', 'notification_prefs_nonce'); ?>
            <input type="hidden" name="action" value="update_notification_prefs">

            <h2><?php _e('Notification Preferences', 'ispag-crm'); ?></h2>
            <p class="description">
                <?php _e('Choose the method(s) by which you wish to receive each type of notification.', 'ispag-crm'); ?>
            </p>

            <!-- Tableau des préférences -->
            <div class="ispag-notification-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Notification type', 'ispag-crm'); ?></th>
                            <?php foreach ($available_channels as $channel_key => $channel_label) : ?>
                                <th style="text-align: center;"><?php echo esc_html($channel_label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped_types as $group_name => $types) : ?>
                            <tr class="ispag-notification-group-header">
                                <td colspan="<?php echo count($available_channels) + 1; ?>">
                                    <strong><?php echo esc_html($group_name); ?></strong>
                                </td>
                            </tr>
                            <?php foreach ($types as $type_key => $type_info) :
                                $current_channels = ISPAG_Notifications_Manager::get_user_channel_preferences($user_id, $type_key);
                            ?>
                                <tr>
                                    <td><?php echo esc_html($type_info['label']); ?></td>
                                    <?php foreach ($available_channels as $channel_key => $channel_label) :
                                        $is_checked = in_array($channel_key, $current_channels);
                                    ?>
                                        <td style="text-align: center;">
                                            <input type="checkbox"
                                                name="ispag_notif_prefs[<?php echo esc_attr($type_key); ?>][]"
                                                value="<?php echo esc_attr($channel_key); ?>"
                                                <?php checked($is_checked, true); ?> />
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Section pour le droit à la déconnexion -->
            <div class="ispag-disconnection-settings">
                <h3><?php _e('Right to Disconnect', 'ispag-crm'); ?></h3>

                <div class="ispag-disconnection-option">
                    <label>
                        <input type="checkbox"
                            name="ispag_allow_weekend_notifications"
                            value="1"
                            <?php checked($allow_weekend, true); ?> />
                        <?php _e('Receive notifications on weekends (Saturday and Sunday)', 'ispag-crm'); ?>
                    </label>
                    <p class="description">
                        <?php _e('If unchecked, notifications will be delayed until Monday.', 'ispag-crm'); ?>
                    </p>
                </div>

                <div class="ispag-holiday-periods">
                    <h4><?php _e('Holiday Periods', 'ispag-crm'); ?></h4>
                    <p class="description">
                        <?php _e('Add periods during which you do not want to receive notifications.', 'ispag-crm'); ?>
                    </p>
                    <div id="ispag-holiday-periods-container">
                        <?php if (!empty($holiday_periods)) : ?>
                            <?php foreach ($holiday_periods as $index => $period) : ?>
                                <div class="ispag-holiday-period" data-index="<?php echo $index; ?>">
                                    <div class="ispag-holiday-period-fields">
                                        <label>
                                            <?php _e('Start Date:', 'ispag-crm'); ?>
                                            <input type="date"
                                                name="ispag_holiday_periods[<?php echo $index; ?>][start]"
                                                value="<?php echo esc_attr($period['start']); ?>" />
                                        </label>
                                        <label>
                                            <?php _e('End Date:', 'ispag-crm'); ?>
                                            <input type="date"
                                                name="ispag_holiday_periods[<?php echo $index; ?>][end]"
                                                value="<?php echo esc_attr($period['end']); ?>" />
                                        </label>
                                        <button type="button" class="button button-secondary ispag-remove-holiday-period">
                                            <?php _e('Remove', 'ispag-crm'); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="ispag-holiday-period" data-index="0">
                                <div class="ispag-holiday-period-fields">
                                    <label>
                                        <?php _e('Start Date:', 'ispag-crm'); ?>
                                        <input type="date" name="ispag_holiday_periods[0][start]" />
                                    </label>
                                    <label>
                                        <?php _e('End Date:', 'ispag-crm'); ?>
                                        <input type="date" name="ispag_holiday_periods[0][end]" />
                                    </label>
                                    <button type="button" class="button button-secondary ispag-remove-holiday-period">
                                        <?php _e('Remove', 'ispag-crm'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="ispag-add-holiday-period" class="button button-primary">
                        <?php _e('Add Holiday Period', 'ispag-crm'); ?>
                    </button>
                </div>
            </div>

            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Save preferences', 'ispag-crm'); ?></button>
            </p>
        </form>
    </div>
</div>

<!-- JavaScript pour gérer les périodes de vacances -->
<script>
jQuery(document).ready(function($) {
    // Ajouter une nouvelle période de vacances
    $('#ispag-add-holiday-period').on('click', function() {
        const index = $('#ispag-holiday-periods-container .ispag-holiday-period').length;
        const newPeriodHtml = `
            <div class="ispag-holiday-period" data-index="${index}">
                <div class="ispag-holiday-period-fields">
                    <label>
                        <?php _e('Start Date:', 'ispag-crm'); ?>
                        <input type="date" name="ispag_holiday_periods[${index}][start]" />
                    </label>
                    <label>
                        <?php _e('End Date:', 'ispag-crm'); ?>
                        <input type="date" name="ispag_holiday_periods[${index}][end]" />
                    </label>
                    <button type="button" class="button button-secondary ispag-remove-holiday-period">
                        <?php _e('Remove', 'ispag-crm'); ?>
                    </button>
                </div>
            </div>
        `;
        $('#ispag-holiday-periods-container').append(newPeriodHtml);
    });

    // Supprimer une période de vacances
    $(document).on('click', '.ispag-remove-holiday-period', function() {
        $(this).closest('.ispag-holiday-period').remove();
        // Réindexer les périodes
        $('#ispag-holiday-periods-container .ispag-holiday-period').each(function(i) {
            $(this).attr('data-index', i);
            $(this).find('input[name^="ispag_holiday_periods"]').each(function() {
                const name = $(this).attr('name');
                $(this).attr('name', name.replace(/ispag_holiday_periods\[\d+\]/, `ispag_holiday_periods[${i}]`));
            });
        });
    });
});
</script>

<?php

get_footer(); ?>