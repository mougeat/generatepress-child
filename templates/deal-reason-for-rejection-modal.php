<?php
/**
 * Template pour afficher une modal de selection des raisons de pertes du projet
 * Variables attendues : ???
 */
function get_ispag_rejection_reasons() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ispag_rejection_reasons';

    // On récupère uniquement les raisons actives
    $results = $wpdb->get_results("SELECT reason_key, label_en FROM $table_name WHERE is_active = 1", ARRAY_A);

    $reasons = [];
    if (!empty($results)) {
        foreach ($results as $row) {
            // On utilise la clé comme index et le label comme valeur
            // On passe par __() au cas où vous auriez des fichiers de traduction .mo/.po
            $reasons[$row['reason_key']] = __($row['label_en'], 'ispag-crm');
        }
    }

    return $reasons;
}

$rejection_reasons = get_ispag_rejection_reasons();
?>

<div id="ispag-lost-reason-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:400px; margin:15% auto; padding:20px; border-radius:8px; position:relative;">
        <h3><?php _e('Reason for loss', 'ispag-crm'); ?></h3>
        <p><?php _e('Why was this project lost?', 'ispag-crm'); ?></p>
        
        <div class="ispag-field-group">
            <label for="ispag-rejection-reason" style="display:block; margin-bottom:5px; font-weight:bold;">
                <?php _e('Reason for loss', 'ispag-crm'); ?>
            </label>
            
            <select id="ispag-rejection-reason" name="rejection_reason" style="width:100%; margin-bottom:20px; padding:8px; border-radius:4px; border:1px solid #ccc;">
                <option value=""><?php _e('-- Select a reason --', 'ispag-crm'); ?></option>
                
                <?php foreach ( $rejection_reasons as $key => $label ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
                
            </select>
        </div>

        <div style="text-align:right;">
            <button id="ispag-cancel-lost" class="button"><?php _e('Cancel', 'ispag-crm'); ?></button>
            <button id="ispag-confirm-lost" class="button button-primary"><?php _e('Save', 'ispag-crm'); ?></button>
        </div>
    </div>
</div>