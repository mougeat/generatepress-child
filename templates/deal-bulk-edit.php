<?php
/**
 * Template pour l'affichage du bulk edit
 * Variables attendues : $transactions (array)
 */
?> 
<div id="ispag-bulk-edit-bar" style="display: none; background: #f0f0f1; border: 1px solid #ccd0d4; padding: 15px; margin-bottom: 15px; border-radius: 4px; align-items: center; gap: 15px;">
    <strong><?php echo sprintf(__('%s selected projects', 'ispag-crm'), '<span id="selected-count">0</span>'); ?></strong>
    
    <div class="bulk-action-item">
        <label style="margin-right: 10px;"><?php _e('Move to stage :', 'ispag-crm'); ?></label>
        <span class="ispag-badge-container" style="position: relative; display: inline-block;">
            <span id="bulk-stage-badge" class="ispag-status-badge" style="background-color: #cbd6e2; color: #fff; padding: 5px 12px; border-radius: 4px; display: inline-block; min-width: 100px; text-align: center;">
                <?php _e('Select...', 'ispag-crm'); ?>
            </span>
            <select id="ispag-bulk-stage-updater" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                <option value=""><?php _e('Select a stage', 'ispag-crm'); ?></option>
                <?php 
                $stage_repo = new ISPAG_Crm_Deal_Stages_Repository();
                $all_stages = $stage_repo->get_all_stages();
                foreach ($all_stages as $stage) : ?>
                    <option value="<?php echo esc_attr($stage->stage_key); ?>" 
                            data-label="<?php echo esc_html($stage->stage_label); ?>"
                            data-color="<?php echo esc_attr($stage->stage_color); ?>">
                        <?php echo esc_html($stage->stage_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </span>
    </div>

    <div class="bulk-action-item" style="display: flex; align-items: center;">
        <label style="margin-right: 10px; font-weight: 600;"><?php _e('Last contact :', 'ispag-crm'); ?></label>
        <input type="date" id="ispag-bulk-date-contact" class="regular-text" style="width: auto; padding: 3px 8px;">
    </div>

    
    <button type="button" id="ispag-bulk-submit" class="button button-primary"><?php _e('Apply to selected items', 'ispag-crm'); ?></button>
</div>