<?php
defined('ABSPATH') || exit;

if ( ! function_exists( 'ispag_action_icon' ) ) {
    /** Icônes d'action : un seul jeu de pictos « trait » 24×24 (style Feather), couleur = currentColor. */
    function ispag_action_icon( $name ) {
        $paths = array(
            'note'     => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>',
            'call'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
            'email'    => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
            'task'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8 12 3 3 5-6"/>',
            'meeting'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'more'     => '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
            'log_email'=> '<path d="M12 19H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6"/><path d="m22 7-10 6L2 7"/><path d="M19 16v6M16 19h6"/>',
            'whatsapp' => '<path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 20.5l1.7-5.4A8.4 8.4 0 1 1 21 11.5z"/><path d="M9 9c0 3 3 6 6 6l1-1.5-2-1-1 .8c-.8-.4-1.5-1.1-1.9-1.9l.8-1-1-2z"/>',
            'sms'      => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/>',
            'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
            'delete'   => '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/>',
        );
        $p = $paths[ $name ] ?? '';
        return '<span class="ispag-icon-svg ispag-ai-' . esc_attr( $name ) . '" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $p . '</svg></span>';
    }
}
/**
 * Template pour l'affichage du contenu de l'action bar
 * Variables attendues : $actions (array)
 */

// Valeurs par défaut pour éviter les warnings sur les clés manquantes
$company_ids    = $actions['company_ids']    ?? '';
$company_names  = $actions['company_names']  ?? '';
$contact_ids    = $actions['contact_ids']    ?? '';
$contact_names  = $actions['contact_names']  ?? '';
$contact_emails = $actions['contact_emails'] ?? '';
$contact_phone  = $actions['contact_phone']  ?? '';
$deal_ids       = $actions['deal_ids']       ?? '';
$deal_names     = $actions['deal_names']     ?? '';
$offer_num      = $actions['offer_num']      ?? '';
$project_num    = $actions['project_num']    ?? '';
$closing_date   = $actions['closing_date']   ?? '';
$total_excl_vat = $actions['total_excl_vat'] ?? 0;
$user_id        = $actions['user_id']        ?? 0;
// Suppression : uniquement sur les fiches contact / entreprise (delete_entity + delete_id) et pour les administrateurs
$delete_entity  = $actions['delete_entity'] ?? '';
$delete_id      = absint( $actions['delete_id'] ?? 0 );
$show_delete    = $delete_entity && $delete_id && current_user_can( 'manage_options' );

$deal_date = $closing_date ? date_i18n( 'd.m.Y', strtotime( $closing_date ) ) : '';
$deal_total = number_format( (float) $total_excl_vat, 2, '.', '\'' ) . ' CHF';
?>
<button class="ispag-action-btn"
        data-action="note"
        data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
        data-company-names="<?php echo esc_attr( $company_names ); ?>"
        data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
        data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
        data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
        data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
        title="<?php esc_attr_e( 'Add Note', 'ispag-crm' ); ?>"
>
    <?php echo ispag_action_icon( 'note' ); ?>
    <?php esc_html_e( 'Note', 'ispag-crm' ); ?>
</button>

<?php if ( ! empty( $contact_phone ) ) : ?>
    <a
        href="tel:<?php echo esc_attr( $contact_phone ); ?>"
        title="<?php esc_attr_e( 'Call this number', 'ispag-crm' ); ?>"
    >
    <button class="ispag-action-btn"
        data-action="call"
        data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
        data-company-names="<?php echo esc_attr( $company_names ); ?>"
        data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
        data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
        data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
        data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
        title="<?php esc_attr_e( 'Make a phone call', 'ispag-crm' ); ?>"
    >
        <?php echo ispag_action_icon( 'call' ); ?>
        <?php esc_html_e( 'Call', 'ispag-crm' ); ?>
    </button>
    </a>
<?php else : ?>
    <button class="ispag-action-btn"
        data-action="call"
        data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
        data-company-names="<?php echo esc_attr( $company_names ); ?>"
        data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
        data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
        data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
        data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
        title="<?php esc_attr_e( 'Log a call', 'ispag-crm' ); ?>"
    >
        <?php echo ispag_action_icon( 'call' ); ?>
        <?php esc_html_e( 'Call', 'ispag-crm' ); ?>
    </button>
<?php endif; ?>

<button class="ispag-action-btn"
    data-action="email"
    data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
    data-company-names="<?php echo esc_attr( $company_names ); ?>"
    data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
    data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
    data-contact-emails="<?php echo esc_attr( $contact_emails ); ?>"
    data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
    data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
    data-deal-offer-num="<?php echo esc_attr( $offer_num ); ?>"
    data-deal-project-num="<?php echo esc_attr( $project_num ); ?>"
    data-deal-deal-date="<?php echo esc_attr( $deal_date ); ?>"
    data-deal-total="<?php echo esc_attr( $deal_total ); ?>"
    title="<?php esc_attr_e( 'Send an Email', 'ispag-crm' ); ?>"
>
    <?php echo ispag_action_icon( 'email' ); ?>
    <?php esc_html_e( 'Email', 'ispag-crm' ); ?>
</button>

<button class="ispag-action-btn"
    data-action="task"
    data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
    data-company-names="<?php echo esc_attr( $company_names ); ?>"
    data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
    data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
    data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
    data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
    title="<?php esc_attr_e( 'Create Task', 'ispag-crm' ); ?>"
>
    <?php echo ispag_action_icon( 'task' ); ?>
    <?php esc_html_e( 'Task', 'ispag-crm' ); ?>
</button>

<button class="ispag-action-btn"
    data-action="meeting"
    data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
    data-company-names="<?php echo esc_attr( $company_names ); ?>"
    data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
    data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
    data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
    data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
    title="<?php esc_attr_e( 'Log Meeting', 'ispag-crm' ); ?>"
>
    <?php echo ispag_action_icon( 'meeting' ); ?>
    <?php esc_html_e( 'Meeting', 'ispag-crm' ); ?>
</button>

<div class="ispag-dropdown">
    <button class="ispag-action-btn ispag-dropdown-toggle" title="<?php esc_attr_e( 'More actions', 'ispag-crm' ); ?>">
        <?php echo ispag_action_icon( 'more' ); ?>
        <?php esc_html_e( 'More', 'ispag-crm' ); ?>
    </button>
    <div class="ispag-dropdown-menu">

        <button class="ispag-dropdown-item"
            data-action="log_email"
            data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
            data-company-names="<?php echo esc_attr( $company_names ); ?>"
            data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
            data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
            data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
            data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
            title="<?php esc_attr_e( 'Log an email', 'ispag-crm' ); ?>"
        >
            <?php echo ispag_action_icon( 'log_email' ); ?>
            <?php esc_html_e( 'Log an email', 'ispag-crm' ); ?>
        </button>

        <button class="ispag-dropdown-item"
            data-action="whatsapp"
            data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
            data-company-names="<?php echo esc_attr( $company_names ); ?>"
            data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
            data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
            data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
            data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
            title="<?php esc_attr_e( 'Send Whatsapp', 'ispag-crm' ); ?>"
        >
            <?php echo ispag_action_icon( 'whatsapp' ); ?>
            <?php esc_html_e( 'Send Whatsapp', 'ispag-crm' ); ?>
        </button>

        <button class="ispag-dropdown-item"
            data-action="sms"
            data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
            data-company-names="<?php echo esc_attr( $company_names ); ?>"
            data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
            data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
            data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
            data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
            title="<?php esc_attr_e( 'Log SMS', 'ispag-crm' ); ?>"
        >
            <?php echo ispag_action_icon( 'sms' ); ?>
            <?php esc_html_e( 'Log SMS', 'ispag-crm' ); ?>
        </button>

        <button class="ispag-dropdown-item"
            data-action="linkedin"
            data-company-ids="<?php echo esc_attr( $company_ids ); ?>"
            data-company-names="<?php echo esc_attr( $company_names ); ?>"
            data-contact-ids="<?php echo esc_attr( $contact_ids ); ?>"
            data-contact-names="<?php echo esc_attr( $contact_names ); ?>"
            data-deal-ids="<?php echo esc_attr( $deal_ids ); ?>"
            data-deal-names="<?php echo esc_attr( $deal_names ); ?>"
            title="<?php esc_attr_e( 'Log a LinkedIn message', 'ispag-crm' ); ?>"
        >
            <?php echo ispag_action_icon( 'linkedin' ); ?>
            <?php esc_html_e( 'Log a LinkedIn message', 'ispag-crm' ); ?>
        </button>

        <?php if ( $show_delete ) : ?>
        <div class="ispag-dropdown-divider"></div>
        <button class="ispag-dropdown-item ispag-item-danger"
            data-action="delete"
            data-entity="<?php echo esc_attr( $delete_entity === 'company' ? 'company' : 'contact' ); ?>"
            data-id="<?php echo $delete_id; ?>"
        >
            <?php echo ispag_action_icon( 'delete' ); ?>
            <?php esc_html_e( 'Delete', 'ispag-crm' ); ?>
        </button>
        <?php endif; ?>
    </div>
</div>