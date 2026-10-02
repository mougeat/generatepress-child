<?php
/**
 * Carte d'un deal dans le Kanban.
 * Variables attendues : $deal (ISPAG_Crm_Deal_Model), $stage_color (string déjà échappée)
 */
$last_activity_date = ! empty( $deal->last_activity_date ) ? date( 'd.m.Y', strtotime( $deal->last_activity_date ) ) : __( 'N/A', 'ispag-crm' );
$closing_ts         = ! empty( $deal->closing_date ) ? strtotime( $deal->closing_date ) : 0;
$closing_date       = $closing_ts ? date_i18n( 'd.m.Y', $closing_ts ) : '';
$is_overdue         = $closing_ts && $closing_ts < strtotime( 'today' );
?>
<div class="kanban-deal-card"
     data-deal-id="<?php echo absint( $deal->id ); ?>"
     data-amount="<?php echo esc_attr( (float) $deal->total_excl_vat ); ?>"
     style="border-left-color: <?php echo $stage_color; ?>;"
     draggable="true">
    <div class="deal-title">
        <a href="<?php echo $deal->get_deal_detail_link(); ?>" class="ispag-deal-title-link">
            <?php echo esc_html( $deal->project_name ); ?>
            <?php if ( ! empty( $deal->is_copie ) && $deal->is_copie == 1 ) : ?>
                <span class="dashicons dashicons-admin-page ispag-copy-icon"></span>
            <?php endif; ?>
        </a>
    </div>
    <p class="deal-info amount">
        <span class="dashicons dashicons-money-alt"></span>
        <?php echo number_format( (float) $deal->total_excl_vat, 0, '.', '\'' ); ?> CHF
    </p>
    <p class="deal-info close-date<?php echo $is_overdue ? ' is-overdue' : ''; ?>"
       title="<?php esc_attr_e( 'Closing date', 'ispag-crm' ); ?>">
        <span class="dashicons dashicons-calendar-alt"></span>
        <?php echo esc_html( $closing_date ?: '—' ); ?>
    </p>
    <p class="deal-info last-contact-date" title="<?php esc_attr_e( 'Last contact', 'ispag-crm' ); ?>">
        <span class="dashicons dashicons-backup"></span>
        <?php echo esc_html( $last_activity_date ); ?>
    </p>

    <div class="deal-relation">
        <?php if ( ! empty( $deal->associated_company_favicon ) ) : ?>
            <img src="<?php echo esc_url( $deal->associated_company_favicon ); ?>"
                 alt="<?php echo esc_attr( $deal->associated_company_name ); ?>"
                 title="<?php echo esc_attr( $deal->associated_company_name ); ?>"
                 class="ispag-kanban-mini-profile-pic" loading="lazy" decoding="async" draggable="false">
        <?php else : ?>
            <span class="ispag-company-initials" title="<?php echo esc_attr( $deal->associated_company_name ); ?>"><?php echo esc_html( $deal->associated_company_initials ); ?></span>
        <?php endif; ?>
        <?php foreach ( (array) $deal->associated_contacts as $contact ) : ?>
            <img src="<?php echo esc_url( $contact['avatar_url'] ); ?>"
                 alt="<?php echo esc_attr( $contact['name'] ); ?>"
                 title="<?php echo esc_attr( $contact['name'] ); ?>"
                 class="ispag-kanban-mini-profile-pic" loading="lazy" decoding="async" draggable="false">
        <?php endforeach; ?>
    </div>
</div>
