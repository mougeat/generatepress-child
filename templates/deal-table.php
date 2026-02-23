<?php
/**
 * Template pour l'affichage du tableau des transactions ISPAG
 * Variables attendues : $transactions (array)
 */
?> 

<?php ispag_get_template( 'deal-bulk-edit', [] );  ?>

<?php ispag_get_template( 'deal-export-btn', [] );  ?>


<table class="ispag-crm-table">
    <thead>
        <tr>
            <th style="width: 30px;"><input type="checkbox" id="ispag-select-all-projects"></th>
            <th><?php _e('Project & ref', 'ispag-crm'); ?></th>
            <th><?php _e('Company & contact', 'ispag-crm'); ?></th> <th><?php _e('Current stage', 'ispag-crm'); ?></th>
            <th style="text-align:right;"><?php _e('Amount (CHF)', 'ispag-crm'); ?></th>
            <th><?php _e('Close date', 'ispag-crm'); ?></th>
            <th><?php _e('Last contacted', 'ispag-crm'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( ! empty( $transactions ) ) : ?>
            <?php foreach ( $transactions as $transaction ) : 
                $project_name     = $transaction->project_name ?? __('No name', 'ispag-crm');
                $ref              = $transaction->deal_group_ref ?? ''; 
                $last_act         = !empty($transaction->last_activity_date) ? date_i18n('d.m.Y', strtotime($transaction->last_activity_date)) : '-';
                $closing          = !empty($transaction->closing_date) ? date_i18n('d.m.Y', strtotime($transaction->closing_date)) : '-';
                $raw_amount       = isset($transaction->total_excl_vat) ? (float) $transaction->total_excl_vat : 0;
                $formatted_amount = number_format($raw_amount, 2, '.', '\'');
                $stage_label      = $transaction->stage_label ?? __('Unknow', 'ispag-crm');
                $stage_color      = $transaction->stage_color ?? '#cbd6e2';
                $company_name     = $transaction->associated_company_name ?? '-';

                // Récupération du contact (Firstname + Lastname)
                $contact_name     = trim(($transaction->associated_contact_names ?? '') );
            ?>
                <tr>
                    <td>
                        <input type="checkbox" class="ispag-project-checkbox" value="<?php echo esc_attr($transaction->id); ?>">
                    </td>
                    <td>
                        <strong>
                            <a href="<?php echo esc_url($transaction->get_deal_detail_link()); ?>" target="_blank">
                                <?php echo esc_html($project_name); ?>
                            </a>
                        </strong>
                        <div style="font-size: 0.85em; color: #666;"><?php echo esc_html($ref); ?></div>
                    </td>
                    <td>
                        <div class="company-name" style="font-weight: 600;"><?php echo esc_html($company_name); ?></div>
                        <?php if ( ! empty( $contact_name ) ) : ?>
                            <div class="contact-person" style="font-size: 0.85em; color: #0073aa;">
                                <span class="dashicons dashicons-admin-users" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                <?php echo esc_html($contact_name); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="ispag-badge-container" style="position: relative; display: inline-block;">
                            
                            <span class="ispag-status-badge individual-badge-<?php echo esc_attr($transaction->id); ?>" 
                                style="background-color: <?php echo esc_attr($stage_color); ?>15; color: <?php echo esc_attr($stage_color); ?>; border: 1px solid <?php echo esc_attr($stage_color); ?>; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; display: inline-block; min-width: 80px; text-align: center;">
                                <?php echo esc_html($stage_label); ?>
                            </span>

                            <select class="ispag-stage-updater" 
                                    data-deal-id="<?php echo esc_attr($transaction->id); ?>" 
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                                
                                <?php foreach ($all_stages as $stage) : ?>
                                    <option value="<?php echo esc_attr($stage->stage_key); ?>" 
                                            data-label="<?php echo esc_html($stage->stage_label); ?>"
                                            data-color="<?php echo esc_attr($stage->stage_color); ?>"
                                            <?php selected($transaction->stage_key, $stage->stage_key); ?>>
                                        <?php echo esc_html($stage->stage_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </span>
                    </td>
                    <td style="text-align:right; font-family: monospace; font-weight: bold;"><?php echo $formatted_amount; ?></td>
                    <td><?php echo $closing; ?></td>
                    <td><?php echo $last_act; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="7" style="text-align:center; padding: 40px; color: #999;"><?php _e('No deal found.', 'ispag-crm'); ?></td></tr>
        <?php endif; ?>
    </tbody>
</table>