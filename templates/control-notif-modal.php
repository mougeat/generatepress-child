<?php
/**
 * Template pour afficher une modal lors de contrôle de contact / company / deal
 * Variables attendues : 
 * - $modal_id (optionnel) : id unique si plusieurs modales
 * - $title : Titre de la notification
 */
?>

<div id="ispag-crm-modal" class="ispag-modal-overlay">
    <div class="ispag-modal-content">
        
        <div class="ispag-modal-header">
            <h4 id="ispag-modal-title"><?php _e('Notification', 'ispag-crm'); ?></h4>
            <span class="ispag-modal-close ispag-close-modal">&times;</span>
        </div>

        <div class="ispag-modal-body">
            <p id="ispag-modal-message" style="margin-bottom: 20px;"></p>

            <div id="ispag-modal-extra-content" class="ispag-meeting-fields" style="display:none;">
                <div class="ispag-meeting-field-row">
                    
                </div>
            </div>
        </div>

        <div class="ispag-modal-footer" style="text-align: right; border-top: 1px solid #eee;">
            <button id="ispag-cancel-lost" class="button ispag-modal-close ispag-close-modal"><?php _e('Close', 'ispag-crm'); ?></button>
        </div>
        
    </div>
</div>