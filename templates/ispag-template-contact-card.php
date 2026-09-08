<?php
/**
 * Template pour l'affichage du contenu desarticles d'un projet
 * Variables attendues : $datas (array)
 */

$associated_contacts_list_full  = $datas['associated_contacts_list_full']    ?? '';
$company_id                     = $datas['company_id']    ?? '';
$company_viag_id                = $datas['company_viag_id']    ?? '';
$user_id                        = $datas['user_id']    ?? '';
$deal_id                        = $datas['deal_id']    ?? '';
$deal_group_ref                 = $datas['deal_group_ref']    ?? '';


?>

<div class="ispag-card ispag-company-card">
    <h5>
        <?php _e('Contacts', 'ispag-crm'); ?> (<?php echo count($associated_contacts_list_full); ?>)
        <span id="open-add-contact-modal" class="add_relation-btn"
            
            data-company-id="<?php echo absint($company_viag_id); ?>"
            data-deal-group-ref="<?php echo esc_attr($deal_group_ref); ?>"
            data-deal-id="<?php echo absint($deal_id); ?>">
            + <?php _e('Add', 'ispag-crm'); ?>
        </span>
    </h5>
    <?php
    $date_format = get_option('date_format');
    if (!defined('NB_TRANSACTIONS_RIGHT')) {
        define('NB_TRANSACTIONS_RIGHT', 5);
    }
    $nb_contact = 0;
    foreach ($associated_contacts_list_full as $contact) :
        $nb_contact++;
        if ($nb_contact > NB_TRANSACTIONS_RIGHT) {
            break;
        }
        $contact_deal_url = home_url('/contact/' . $contact->ID . '/');
        ?>
        <div class="ispag-card" style="font-size: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <?php if ($contact->avatar_url) : ?>
                        <img src="<?php echo $contact->avatar_url; ?>" alt="<?php echo esc_attr($contact->display_name); ?>" class="ispag-mini-profile-pic">
                    <?php else :
                        $initials = strtoupper(substr($contact->display_name, 0, 1) . substr($contact->display_name, strpos($contact->display_name, ' ') + 1, 1)); ?>
                        <span style="font-size: 12px;"><?php echo esc_html($initials); ?></span>
                    <?php endif; ?>
                </div>
                <strong>
                    <a href="<?php echo $contact_deal_url; ?>"><?php echo $contact->display_name; ?></a>
                </strong>
                <span
                    class="ispag-remove-association"
                    data-action="remove-contact-from-company"
                    data-contact-id="<?php echo absint($contact->ID); ?>"
                    data-company-id="<?php echo absint($company_viag_id); ?>"
                    title="<?php esc_attr_e('Remove association', 'ispag-crm'); ?>"
                    style="color: #e74c3c; cursor: pointer;">
                    <span class="dashicons dashicons-trash"></span>
                </span>
            </div>
            <p style="margin: 5px 0 0;"><?php _e('Function', 'ispag-crm'); ?>: <?php echo esc_html($contact->lead_function ?? ''); ?></p>
            <p style="margin: 5px 0 0;"><?php _e('Last Contact', 'ispag-crm'); ?>: <?php echo date_i18n(get_option('date_format'), strtotime($contact->last_contact_date)); ?></p>
            <p style="margin: 5px 0 0;"><?php _e( 'Phone number', 'ispag-crm'); ?>: <a href="tel:<?php echo esc_html( $contact->phone ); ?>" class="contact_link ispag-phone-display"><?php echo esc_html( $contact->phone ); ?></a></p>
            <p style="margin: 5px 0 0;"><?php _e( 'Email', 'ispag-crm'); ?>: <a href="mailto:<?php echo esc_html( $contact->email ); ?>" class="contact_link"><?php echo esc_html( $contact->email ); ?></a></p>
        </div>
    <?php
    endforeach;

    if ($nb_contact > NB_TRANSACTIONS_RIGHT) {
        $company_url = home_url('/listes-des-contacts/?filter_company=' . $company_viag_id . '/');
        ?>
        <a href="<?php echo $company_url; ?>" class="ispag-button-link"><?php _e('Show all contacts', 'ispag-crm'); ?></a>
    <?php
    }
    ?>
</div>