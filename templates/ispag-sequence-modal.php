<div id="modal-enroll-sequence" class="ispag-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:400px; margin:15% auto; padding:20px; border-radius:8px;">
        <h3>Lancer une séquence pour <span id="display-contact-name"></span></h3>
        
        <select id="select-sequence-id" style="width:100%; margin:15px 0;">
            <option value="">-- Choisir une séquence --</option>
            </select>

        <div style="text-align:right;">
            <button class="ispag-btn ispag-btn-secondary ispag-modal-cancel"><?php _e( 'Cancel', 'ispag-crm' ); ?></button>
            <button type="button" id="confirm-enroll" class="ispag-btn ispag-btn-primary" ><?php _e( 'Start', 'ispag-crm' ); ?></button>
        </div>
    </div>
</div>