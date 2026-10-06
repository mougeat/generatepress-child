<?php
defined('ABSPATH') || exit;
/**
 * Template pour l'affichage des entreprises associées à un projet/deal
 * Variables attendues : $datas (array)
 */

$companies                       = $datas['companies']                       ?? array();
$associated_companies_list_full = $datas['associated_ids']                  ?? array();
$user_id                         = $datas['user_id']                         ?? '';
$deal_id                         = $datas['deal_id']                         ?? '';
?>

<?php $can_edit_assoc = $deal_id && current_user_can('manage_order'); ?>
<!-- <div class="ispag-card ispag-company-card" data-deal-id="<?php echo esc_attr($deal_id); ?>"> -->
    <h5>
        <?php _e( 'Company', 'ispag-crm' ); ?> (<?php echo count($associated_companies_list_full); ?>) 
        <?php if ($can_edit_assoc) : ?>
        <span class="add_relation-btn ispag-proj-assoc-add" data-type="company" data-deal-id="<?php echo absint($deal_id); ?>">
            + <?php _e( 'Add', 'ispag-crm' ); ?>
        </span>
        <?php endif; ?>
    </h5>

    <?php 
    if (!empty($companies)) {
        foreach ($companies as $company) {
            if (!$company) continue;

            $company_app_url = home_url( '/company/' . $company->Id . '/' );
            $company_name    = $company->company_name ?? '';
            $favicon         = $company->favicon ?? null;

            // Calcul automatique des initiales si pas de favicon
            $initials = '';
            if (!empty($company_name)) {
                $words = explode(' ', trim($company_name));
                if (count($words) >= 2) {
                    $initials = strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
                } else {
                    $initials = strtoupper(mb_substr($company_name, 0, 2));
                }
            }
            ?>
            <div class="ispag-card" style="font-size: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <?php if ($favicon) : ?>
                            <img src="<?php echo esc_url( $favicon ); ?>" 
                                 alt="<?php echo esc_attr( $company_name ); ?>"
                                 class="ispag-mini-profile-pic"> 
                        <?php else : ?>
                            <span class="ispag-company-initials"><?php echo esc_html( $initials ); ?></span>
                        <?php endif; ?>
                    </div>
                    <strong>
                        <a href="<?php echo esc_url($company_app_url); ?>"><?php echo esc_html($company_name); ?></a>
                    </strong>
                    <?php if ($can_edit_assoc) : ?>
                    <span 
                        class="ispag-proj-assoc-remove" 
                        data-type="company"
                        data-id="<?php echo absint($company->Id); ?>"
                        data-deal-id="<?php echo absint($deal_id); ?>"
                        title="<?php esc_attr_e( 'Remove from this project', 'ispag-crm' ); ?>"
                        style="color: #e74c3c; cursor: pointer;"
                    >
                        <span class="dashicons dashicons-trash"></span>
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php
                $address_parts = array_filter(array(
                    trim((string) ($company->address ?? '')),
                    trim(trim((string) ($company->postal_code ?? '')) . ' ' . trim((string) ($company->city ?? ''))),
                    trim((string) ($company->country ?? '')),
                ));
                ?>
                <?php if (!empty($address_parts)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Address', 'ispag-crm' ); ?>: <?php echo esc_html(implode(', ', $address_parts)); ?></p>
                <?php endif; ?>

                <?php if (!empty($company->compagny_domain)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Website', 'ispag-crm' ); ?>: <a href="<?php echo esc_url('https://' . preg_replace('#^https?://#i', '', $company->compagny_domain)); ?>" target="_blank" rel="noopener" class="contact_link"><?php echo esc_html($company->compagny_domain); ?></a></p>
                <?php endif; ?>
                
                <?php if (!empty($company->last_contact_date)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Last Contact', 'ispag-crm'); ?>: <?php echo (!empty($company->last_contact_date) ? date_i18n(get_option('date_format'), strtotime($company->last_contact_date)) : '—'); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($company->phone)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Phone number', 'ispag-crm' ); ?>: <a href="<?php echo esc_attr( ispag_phone_href( $company->phone ) ); ?>" class="contact_link ispag-phone-display"><?php echo esc_html( ispag_format_phone( $company->phone ) ); ?></a></p>
                <?php endif; ?>
                
                <?php if (!empty($company->email)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Email', 'ispag-crm' ); ?>: <a href="mailto:<?php echo esc_attr( $company->email ); ?>" class="contact_link"><?php echo esc_html($company->email); ?></a></p>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        echo '<p class="ispag-no-company">' . __( 'No associated company.', 'ispag-crm' ) . '</p>';
    }
    ?>
    
    <?php if (!empty($companies)) : ?>
        <input type="hidden" id="hidden_company_name" value="<?php echo esc_attr($companies[0]->company_name ?? ''); ?>"/>
    <?php endif; ?>
<!-- </div> -->