<?php
/**
 * Template Name: ISPAG Create Contact Sidebar
 */
$contact_manager = new ISPAG_Crm_Contacts_Repository(); // ou ta classe actuelle
$all_ispag_users = $contact_manager->get_ispag_owners_options(); // Récupère tes membres d'équipe

?>
<div id="ispag-contact-sidebar-modal" class="ispag-sidebar-overlay">
    <div class="ispag-sidebar-content">
        <div class="ispag-modal-header">
            <h3><?php _e('Create a new contact', 'ispag-crm'); ?></h3>
            <span class="ispag-modal-close" id="close-contact-sidebar">&times;</span>
        </div>

        <form id="ispag-create-contact-form">
            <div class="ispag-modal-body ispag-task-modal-body">
                <div class="ispag-field-group">
                    <label for="c_email"><?php _e('Email', 'ispag-crm'); ?> <span style="color:red">*</span></label>
                    <input type="email" id="c_email" name="email" required placeholder="<?php esc_attr_e('ex: john.doe@company.com', 'ispag-crm'); ?>">
                    <span id="email-exists-error" style="color: #de350b; font-size: 12px; display: none; margin-top: 5px;"></span>
                </div>

                <div style="display:flex; gap:15px; margin-top:10px;">
                    <div style="flex:1;">
                        <label for="c_first_name"><?php _e('First name', 'ispag-crm'); ?></label>
                        <input type="text" id="c_first_name" name="first_name">
                    </div>
                    <div style="flex:1;">
                        <label for="c_last_name"><?php _e('Last name', 'ispag-crm'); ?></label>
                        <input type="text" id="c_last_name" name="last_name">
                    </div>
                </div>

                <hr style="margin:25px 0; border:0; border-top:1px solid #eee;">

                <div id="contact-extra-fields" style="opacity: 0.4; pointer-events: none; transition: opacity 0.3s;">
                    <label for="c_owner"><?php _e('Contact owner', 'ispag-crm'); ?></label>
                    <select name="owner_id" id="c_owner">
                        <?php 
                        $current_logged_in_user_id = get_current_user_id();
                        if ( ! empty( $all_ispag_users ) ) :
                            foreach ( $all_ispag_users as $user_id => $user_name ) : 
                                // On ignore l'option vide "Not assigned" pour forcer une attribution au current user
                                if ( empty( $user_id ) ) continue; 

                                // On vérifie si cet ID est celui de l'utilisateur connecté
                                $is_selected = ( (int)$user_id === (int)$current_logged_in_user_id ) ? 'selected' : '';
                                ?>
                                <option value="<?php echo esc_attr( $user_id ); ?>" <?php echo $is_selected; ?>>
                                    <?php echo esc_html( $user_name ); ?>
                                </option>
                            <?php endforeach;
                        endif;  ?>
                    </select>

                    <label for="c_function"><?php _e('Job title / Function', 'ispag-crm'); ?></label>
                    <input type="text" id="c_function" name="lead_function" placeholder="<?php esc_attr_e('ex: Technical Director', 'ispag-crm'); ?>">

                    <div class="ispag-field-group">
                        <label for="c_phone"><?php _e('Phone number', 'ispag-crm'); ?></label>
                        <input type="tel" id="c_phone" name="phone" style="width: 100%;">
                    </div>
                </div>
            </div>


            <div class="ispag-modal-footer">
                <!-- <button class="ispag-btn ispag-btn-secondary ispag-modal-cancel"><?php _e( 'Cancel', 'ispag-crm' ); ?></button>
                <button type="submit" id="btn-submit-contact" class="ispag-btn ispag-btn-primary" ><?php _e( 'Save', 'ispag-crm' ); ?></button> -->

                <button class="ispag-btn ispag-btn-secondary ispag-modal-cancel"><?php _e( 'Cancel', 'ispag-crm' ); ?></button>
                <button class="ispag-btn ispag-btn-primary" type="submit" id="btn-submit-contact" ><?php _e( 'Save', 'ispag-crm' ); ?></button>
            </div>
        </form>
    </div>
</div> 