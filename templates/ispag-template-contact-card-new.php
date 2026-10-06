<?php
defined('ABSPATH') || exit;
/**
 * Template pour l'affichage des contacts d'un deal
 * Variables attendues : $datas (array)
 */

$contacts        = $datas['contacts'] ?? array();
$associated_ids  = $datas['associated_ids'] ?? array();
$company_id_ref = $datas['company_id'] ?? '';
$deal_id         = $datas['deal_id'] ?? '';
$deal_group_ref  = $datas['deal_group_ref'] ?? '';

if (!defined('NB_TRANSACTIONS_RIGHT')) {
    define('NB_TRANSACTIONS_RIGHT', 5);
}
// Contacts d'un projet : tous affichés (sinon impossible de retirer ceux au-delà de la limite)
$contact_display_limit = $deal_id ? 50 : NB_TRANSACTIONS_RIGHT;
$can_edit_assoc = $deal_id && current_user_can('manage_order');

$total_contacts = count($contacts);
?>

<h5>
    <?php _e('Contacts', 'ispag-crm'); ?> (<?php echo $total_contacts; ?>)
    <?php if ($can_edit_assoc) : ?>
    <span class="add_relation-btn ispag-proj-assoc-add" data-type="contact" data-deal-id="<?php echo absint($deal_id); ?>">
        + <?php _e('Add', 'ispag-crm'); ?>
    </span>
    <?php endif; ?>
</h5>

<?php
$displayed_count = 0;

foreach ($contacts as $contact) :
    // Cast en objet si c'est un tableau
    if (is_array($contact)) {
        $contact = (object) $contact;
    }

    if (!is_object($contact)) {
        continue;
    }

    $displayed_count++;
    if ($displayed_count > $contact_display_limit) {
        break; // On stoppe l'affichage au-delà de 5
    }

    $contact_id       = $contact->ID ?? $contact->id ?? 0;
    $contact_deal_url = home_url('/contact/' . $contact_id . '/');
    $display_name     = $contact->display_name ?? '';
    ?>
    <div class="ispag-card" style="font-size: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <?php if (!empty($contact->avatar_url)) : ?>
                    <img src="<?php echo esc_url($contact->avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="ispag-mini-profile-pic">
                <?php else :
                    $initials = !empty($display_name) ? strtoupper(substr($display_name, 0, 1) . substr($display_name, strpos($display_name, ' ') + 1, 1)) : '?'; ?>
                    <span style="font-size: 12px;"><?php echo esc_html($initials); ?></span>
                <?php endif; ?>
            </div>
            <strong>
                <a href="<?php echo esc_url($contact_deal_url); ?>"><?php echo esc_html($display_name); ?></a>
                <?php if ($deal_id && $displayed_count === 1) : // le premier de la liste est le contact principal ?>
                    <span class="ispag-main-contact-badge" title="<?php esc_attr_e('Main contact', 'ispag-crm'); ?>">★ <?php _e('Main contact', 'ispag-crm'); ?></span>
                <?php elseif ($can_edit_assoc) : ?>
                    <span class="ispag-proj-assoc-primary" data-id="<?php echo absint($contact_id); ?>" data-deal-id="<?php echo absint($deal_id); ?>" title="<?php esc_attr_e('Set as main contact', 'ispag-crm'); ?>">☆</span>
                <?php endif; ?>
            </strong>
            <?php if ($can_edit_assoc) : ?>
            <span
                class="ispag-proj-assoc-remove"
                data-type="contact"
                data-id="<?php echo absint($contact_id); ?>"
                data-deal-id="<?php echo absint($deal_id); ?>"
                title="<?php esc_attr_e('Remove from this project', 'ispag-crm'); ?>"
                style="color: #e74c3c; cursor: pointer;">
                <span class="dashicons dashicons-trash"></span>
            </span>
            <?php endif; ?>
        </div>
        <p style="margin: 5px 0 0;"><?php _e('Function', 'ispag-crm'); ?>: <?php echo esc_html($contact->lead_function ?? ''); ?></p>
        <p style="margin: 5px 0 0;"><?php _e('Last Contact', 'ispag-crm'); ?>: <?php echo !empty($contact->last_contact_date) ? date_i18n(get_option('date_format'), strtotime($contact->last_contact_date)) : '-'; ?></p>
        <p style="margin: 5px 0 0;"><?php _e('Phone number', 'ispag-crm'); ?>: <a href="<?php echo esc_attr(ispag_phone_href($contact->phone ?? '')); ?>" class="contact_link ispag-phone-display"><?php echo esc_html(ispag_format_phone($contact->phone ?? '')); ?></a></p>
        <p style="margin: 5px 0 0;"><?php _e('Email', 'ispag-crm'); ?>: <a href="mailto:<?php echo esc_attr($contact->email ?? ''); ?>" class="contact_link"><?php echo esc_html($contact->email ?? ''); ?></a></p>
    </div>
<?php
endforeach;

// Affichage du bouton "Voir tout" si le nombre total dépasse la limite
if (!$deal_id && $total_contacts > NB_TRANSACTIONS_RIGHT) :
    $company_url = home_url('/listes-des-contacts/?filter_company=' . $company_id_ref . '/');
    ?>
    <a href="<?php echo esc_url($company_url); ?>" class="ispag-button-link"><?php _e('Show all contacts', 'ispag-crm'); ?></a>
<?php endif; ?>