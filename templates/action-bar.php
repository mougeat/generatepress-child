<?php
/**
 * Template pour l'affichage du contenu de l'action bar
 * Variables attendues : $actions (array)
 */
?>
<button class="ispag-action-btn"
        data-action="note"
        data-company-ids="<?php echo ($actions['company_ids']); ?>"
        data-company-names="<?php echo ($actions['company_names']); ?>"
        data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
        data-contact-names="<?php echo ($actions['contact_names']); ?>"
        data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
        data-deal-names="<?php echo ($actions['deal_names']); ?>"
    title="<?php esc_attr_e( 'Add Note', 'ispag-crm' ); ?>"
>
    <span class="dashicons dashicons-text-page"></span>
    <?php esc_html_e( 'Note', 'ispag-crm' ); ?>
</button>

<?php if ( ! empty( $actions['contact_phone'] ) ) : ?>
    <a 
        href="tel:<?php echo esc_attr( $actions['contact_phone'] ); ?>" 
        title="<?php esc_attr_e( 'Call this number', 'ispag-crm' ); ?>"
    >
    <button class="ispag-action-btn"
        data-action="call"
        data-company-ids="<?php echo ($actions['company_ids']); ?>"
        data-company-names="<?php echo ($actions['company_names']); ?>"
        data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
        data-contact-names="<?php echo ($actions['contact_names']); ?>"
        data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
        data-deal-names="<?php echo ($actions['deal_names']); ?>"
        title="<?php esc_attr_e( 'Make a phone call', 'ispag-crm' ); ?>"
    >
        <span class="dashicons dashicons-phone"></span>
        <?php esc_html_e( 'Call', 'ispag-crm' ); ?>
    </button>
    </a>
<?php 
else:
?>
    <button class="ispag-action-btn"
        data-action="call"
        data-company-ids="<?php echo ($actions['company_ids']); ?>"
        data-company-names="<?php echo ($actions['company_names']); ?>"
        data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
        data-contact-names="<?php echo ($actions['contact_names']); ?>"
        data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
        data-deal-names="<?php echo ($actions['deal_names']); ?>"
        title="<?php esc_attr_e( 'Log a Call', 'ispag-crm' ); ?>"
    >
        <span class="dashicons dashicons-phone"></span>
        <?php esc_html_e( 'Call', 'ispag-crm' ); ?>
    </button>
<?php endif; ?>

<button class="ispag-action-btn"
    data-action="email"
    data-company-ids="<?php echo ($actions['company_ids']); ?>"
    data-company-names="<?php echo ($actions['company_names']); ?>"
    data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
    data-contact-names="<?php echo ($actions['contact_names']); ?>"
    data-contact-emails="<?php echo ($actions['contact_emails']); ?>"
    data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
    data-deal-names="<?php echo ($actions['deal_names']); ?>"
    
    data-deal-offer-num="<?php echo $actions['offer_num']; ?>"
    data-deal-project-num="<?php echo $actions['project_num']; ?>"
    data-deal-deal-date="<?php echo date_i18n( 'd.m.Y', strtotime( $actions['closing_date'] ) ); ?>"
    data-deal-total=" <?php echo number_format( (float)$actions['total_excl_vat'], 2, '.', '\'' ); ?> CHF"
    title="<?php esc_attr_e( 'Send an Email', 'ispag-crm' ); ?>"
>
    <span class="dashicons dashicons-email"></span>
    <?php esc_html_e( 'Email', 'ispag-crm' ); ?>
</button>

<button class="ispag-action-btn"
    data-action="task"
    data-company-ids="<?php echo ($actions['company_ids']); ?>"
    data-company-names="<?php echo ($actions['company_names']); ?>"
    data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
    data-contact-names="<?php echo ($actions['contact_names']); ?>"
    data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
    data-deal-names="<?php echo ($actions['deal_names']); ?>"
    title="<?php esc_attr_e( 'Create Task', 'ispag-crm' ); ?>"
>
    <span class="dashicons dashicons-list-view"></span>
    <?php esc_html_e( 'Task', 'ispag-crm' ); ?>
</button>

<button class="ispag-action-btn"
    data-action="meeting"
    data-company-ids="<?php echo ($actions['company_ids']); ?>"
    data-company-names="<?php echo ($actions['company_names']); ?>"
    data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
    data-contact-names="<?php echo ($actions['contact_names']); ?>"
    data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
    data-deal-names="<?php echo ($actions['deal_names']); ?>"
    title="<?php esc_attr_e( 'Log Meeting', 'ispag-crm' ); ?>"
>
    <span class="dashicons dashicons-calendar-alt"></span>
    <?php esc_html_e( 'Meeting', 'ispag-crm' ); ?>
</button>
<div class="ispag-dropdown">
    <button class="ispag-action-btn ispag-dropdown-toggle" title="<?php esc_attr_e( 'More actions', 'ispag-crm' ); ?>">
        <span class="dashicons dashicons-ellipsis"></span>
        <?php esc_html_e( 'More', 'ispag-crm' ); ?>
    </button>
    <div class="ispag-dropdown-menu">
        
        <button class="ispag-dropdown-item"
            data-action="log_email"
            data-company-ids="<?php echo ($actions['company_ids']); ?>"
            data-company-names="<?php echo ($actions['company_names']); ?>"
            data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
            data-contact-names="<?php echo ($actions['contact_names']); ?>"
            data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
            data-deal-names="<?php echo ($actions['deal_names']); ?>"
            title="<?php esc_attr_e( 'Log an email', 'ispag-crm' ); ?>"
        >
            <span class="ispag-icon-svg emaillog-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    
                    <circle cx="4" cy="4" r="3.5" fill="white" stroke="none"/> <path d="M1 4H7M4 1V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <?php esc_html_e( 'Log an email', 'ispag-crm' ); ?>
        </button>

        <button class="ispag-dropdown-item"
            data-action="whatsapp"
            data-company-ids="<?php echo ($actions['company_ids']); ?>"
            data-company-names="<?php echo ($actions['company_names']); ?>"
            data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
            data-contact-names="<?php echo ($actions['contact_names']); ?>"
            data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
            data-deal-names="<?php echo ($actions['deal_names']); ?>"
            title="<?php esc_attr_e( 'Send Whatsapp', 'ispag-crm' ); ?>"
        >
            <span class="ispag-icon-svg whatsapp-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.14l-.3-.17-3.12.82.83-3.04-.19-.3a8.13 8.13 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m-3.61 4.75c-.2-.45-.4-.46-.59-.47-.15 0-.32 0-.49 0-.17 0-.45.06-.68.32-.24.25-.91.89-.91 2.16 0 1.27.92 2.5 1.05 2.67.13.17 1.81 2.77 4.39 3.88.61.27 1.09.43 1.47.55.62.2 1.18.17 1.62.1.5-.08 1.52-.62 1.73-1.22.21-.6.21-1.12.15-1.22-.06-.11-.23-.17-.49-.3-.26-.13-1.52-.75-1.75-.84-.23-.09-.4-.13-.56.13-.17.26-.65.82-.8 1-.15.17-.29.19-.55.06-.26-.13-1.1-.41-2.1-1.3-.77-.69-1.29-1.54-1.44-1.8-.15-.26-.02-.4.11-.53.12-.11.26-.3.39-.45.13-.15.17-.26.26-.43.08-.17.04-.32-.02-.45-.06-.13-.56-1.35-.77-1.85z"/>
                </svg>
            </span>
            <?php esc_html_e( 'Send Whatsapp', 'ispag-crm' ); ?>
        </button>

        <button class="ispag-dropdown-item"
            data-action="sms"
            data-company-ids="<?php echo ($actions['company_ids']); ?>"
            data-company-names="<?php echo ($actions['company_names']); ?>"
            data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
            data-contact-names="<?php echo ($actions['contact_names']); ?>"
            data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
            data-deal-names="<?php echo ($actions['deal_names']); ?>"
            title="<?php esc_attr_e( 'Log SMS', 'ispag-crm' ); ?>"
        >
            <span class="dashicons dashicons-smartphone"></span>
            <?php esc_html_e( 'Log SMS', 'ispag-crm' ); ?>
        </button>

        <button class="ispag-dropdown-item"
            data-action="linkedin"
            data-company-ids="<?php echo ($actions['company_ids']); ?>"
            data-company-names="<?php echo ($actions['company_names']); ?>"
            data-contact-ids="<?php echo ($actions['contact_ids']); ?>"
            data-contact-names="<?php echo ($actions['contact_names']); ?>"
            data-deal-ids="<?php echo ($actions['deal_ids']); ?>"
            data-deal-names="<?php echo ($actions['deal_names']); ?>"
            title="<?php esc_attr_e( 'Log a LinkedIn message', 'ispag-crm' ); ?>"
        >
            <span class="ispag-icon-svg linkedin-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
                </svg>
            </span>
            <?php esc_html_e( 'Log a LinkedIn message', 'ispag-crm' ); ?>
        </button>
        
        <div class="ispag-dropdown-divider"></div>
        <button class="ispag-dropdown-item ispag-item-danger"
            data-action="delete"
            data-user-id="<?php echo absint($user_id); ?>"
            data-contact-ids="<?php echo absint($user_id); ?>"
        >
            <span class="dashicons dashicons-trash"></span>
            <?php esc_html_e( 'Delete', 'ispag-crm' ); ?>
        </button>
    </div>
</div>