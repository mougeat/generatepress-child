<?php
/**
 * Template pour l'affichage des entreprises associées à un projet/deal
 * Variables attendues : $datas (array)
 */

$companies                       = $datas['companies']                       ?? array();
$associated_companies_list_full = $datas['associated_ids']                  ?? array();
$user_id                         = $datas['user_id']                         ?? '';
$deal_id                         = $datas['deal_id']                         ?? '';
?>

<!-- <div class="ispag-card ispag-company-card" data-deal-id="<?php echo esc_attr($deal_id); ?>"> -->
    <h5>
        <?php _e( 'Company', 'ispag-crm' ); ?> (<?php echo count($associated_companies_list_full); ?>) 
        <span id="open-add-company-modal" class="add_relation-btn" data-contact-id="<?php echo absint($user_id); ?>">
            + <?php _e( 'Add', 'ispag-crm' ); ?>
        </span>
    </h5>

    <?php 
    if (!empty($companies)) {
        foreach ($companies as $company) {
            if (!$company) continue;

            $company_app_url = home_url( '/company/' . $company->viag_id . '/' );
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
                    <span 
                        class="ispag-remove-association" 
                        data-action="remove-contact-from-company"
                        data-contact-id="<?php echo absint($user_id); ?>"
                        data-company-id="<?php echo absint($company->viag_id); ?>"
                        data-deal-id="<?php echo absint($deal_id); ?>"
                        title="<?php esc_attr_e( 'Remove association', 'ispag-crm' ); ?>"
                        style="color: #e74c3c; cursor: pointer;"
                    >
                        <span class="dashicons dashicons-trash"></span>
                    </span>
                </div>
                
                <?php if (!empty($company->city)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'City', 'ispag-crm' ); ?>: <?php echo esc_html($company->city); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($company->last_contact_date)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Last Contact', 'ispag-crm'); ?>: <?php echo date_i18n(get_option('date_format'), strtotime($company->last_contact_date)); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($company->phone)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Phone number', 'ispag-crm' ); ?>: <a href="tel:<?php echo esc_attr( $company->phone ); ?>" class="contact_link ispag-phone-display"><?php echo esc_html($company->phone); ?></a></p>
                <?php endif; ?>
                
                <?php if (!empty($company->email)) : ?>
                    <p style="margin: 5px 0 0;"><?php _e( 'Email', 'ispag-crm' ); ?>: <a href="mailto:<?php echo esc_attr( $company->email ); ?>" class="contact_link"><?php echo esc_html($company->email); ?></a></p>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        echo '<p class="ispag-no-company">' . __( 'Aucune entreprise associée.', 'ispag-crm' ) . '</p>';
    }
    ?>
    
    <?php if (!empty($companies)) : ?>
        <input type="hidden" id="hidden_company_name" value="<?php echo esc_attr($companies[0]->company_name ?? ''); ?>"/>
    <?php endif; ?>
<!-- </div> -->