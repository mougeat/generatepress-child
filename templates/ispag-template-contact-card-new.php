<?php
/**
 * Template pour l'affichage des contacts d'un deal
 * Variables attendues : $datas (array)
 */

$contacts        = $datas['contacts'] ?? array();
$associated_ids  = $datas['associated_ids'] ?? array();
$company_viag_id = $datas['company_viag_id'] ?? '';
$deal_id         = $datas['deal_id'] ?? '';
$deal_group_ref  = $datas['deal_group_ref'] ?? '';

if (!defined('NB_TRANSACTIONS_RIGHT')) {
    define('NB_TRANSACTIONS_RIGHT', 5);
}

$total_contacts = count($contacts);
?>

<h5>
    <?php _e('Contacts', 'ispag-crm'); ?> (<?php echo $total_contacts; ?>)
    <span id="open-add-contact-modal" class="add_relation-btn"
        data-company-id="<?php echo absint($company_viag_id); ?>"
        data-deal-group-ref="<?php echo esc_attr($deal_group_ref); ?>"
        data-deal-id="<?php echo absint($deal_id); ?>">
        + <?php _e('Add', 'ispag-crm'); ?>
    </span>
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
    if ($displayed_count > NB_TRANSACTIONS_RIGHT) {
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
            </strong>
            <span
                class="ispag-remove-association"
                data-action="remove-contact-from-company"
                data-contact-id="<?php echo absint($contact_id); ?>"
                data-company-id="<?php echo absint($company_viag_id); ?>"
                title="<?php esc_attr_e('Remove association', 'ispag-crm'); ?>"
                style="color: #e74c3c; cursor: pointer;">
                <span class="dashicons dashicons-trash"></span>
            </span>
        </div>
        <p style="margin: 5px 0 0;"><?php _e('Function', 'ispag-crm'); ?>: <?php echo esc_html($contact->lead_function ?? ''); ?></p>
        <p style="margin: 5px 0 0;"><?php _e('Last Contact', 'ispag-crm'); ?>: <?php echo !empty($contact->last_contact_date) ? date_i18n(get_option('date_format'), strtotime($contact->last_contact_date)) : '-'; ?></p>
        <p style="margin: 5px 0 0;"><?php _e('Phone number', 'ispag-crm'); ?>: <a href="tel:<?php echo esc_attr($contact->phone ?? ''); ?>" class="contact_link ispag-phone-display"><?php echo esc_html($contact->phone ?? ''); ?></a></p>
        <p style="margin: 5px 0 0;"><?php _e('Email', 'ispag-crm'); ?>: <a href="mailto:<?php echo esc_attr($contact->email ?? ''); ?>" class="contact_link"><?php echo esc_html($contact->email ?? ''); ?></a></p>
    </div>
<?php
endforeach;

// Affichage du bouton "Voir tout" si le nombre total dépasse la limite
if ($total_contacts > NB_TRANSACTIONS_RIGHT) :
    $company_url = home_url('/listes-des-contacts/?filter_company=' . $company_viag_id . '/');
    ?>
    <a href="<?php echo esc_url($company_url); ?>" class="ispag-button-link"><?php _e('Show all contacts', 'ispag-crm'); ?></a>
<?php endif; ?>