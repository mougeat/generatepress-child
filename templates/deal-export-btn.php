<?php
/**
 * Template pour l'affichage d'un bouton d'export et de la modal
 * Variables attendues : $transactions (array)
 */
?> 
<div class="ispag-table-actions" style="margin-bottom: 15px; text-align: right;">
    <button type="button" id="ispag-open-export-modal" class="button">
        <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: 4px;"></span>
        <?php _e('Export list', 'ispag-crm'); ?>
    </button>
</div>

<div id="ispag-export-modal" class="ispag-modal" style="display:none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="ispag-modal-content" style="background: #fff; margin: 10% auto; padding: 20px; width: 400px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;"><?php _e('Export Settings', 'ispag-crm'); ?></h3>
        <hr>
        <form id="ispag-export-form">
            <p>
                <label><strong><?php _e('Filename', 'ispag-crm'); ?></strong></label><br>
                <input type="text" id="export_filename" name="filename" style="width:100%;" placeholder="deals_export_<?php echo date('Y-m-d-h-m-s'); ?>">
            </p>
            <p>
                <label><strong><?php _e('Format', 'ispag-crm'); ?></strong></label><br>
                <select name="format" id="export_format" style="width:100%;">
                    <option value="pdf" disabled style="color: #999;">PDF (.pdf)</option>
                    <option value="csv" selected>CSV (.csv)</option>
                </select>
            </p>
            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="button ispag-modal-close"><?php _e('Cancel', 'ispag-crm'); ?></button>
                <button type="submit" class="button button-primary"><?php _e('Download', 'ispag-crm'); ?></button>
            </div>
        </form>
    </div>
</div>