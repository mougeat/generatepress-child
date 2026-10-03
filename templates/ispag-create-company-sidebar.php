<?php
defined('ABSPATH') || exit;
/**
 * Panneau latéral « Créer une entreprise » (page publique du CRM).
 * Affiché dans le pied de page pour les utilisateurs autorisés (voir ispag_add_global_company_sidebar()).
 */
?>
<div id="ispag-company-sidebar-modal" class="ispag-sidebar-overlay" aria-hidden="true">
    <div class="ispag-sidebar-content" style="max-width:500px;" role="dialog" aria-modal="true" aria-labelledby="ispag-company-sidebar-title">
        <div class="ispag-modal-header">
            <h3 id="ispag-company-sidebar-title"><?php _e( 'Create a new company', 'ispag-crm' ); ?></h3>
            <span class="ispag-modal-close ispag-btn ispag-btn-red-outlined ispag-close-croix ispag-company-close" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Close', 'ispag-crm' ); ?>">&times;</span>
        </div>

        <form id="ispag-create-company-form" novalidate>
            <div class="ispag-modal-body ispag-task-modal-body">

                <div class="ispag-field-group">
                    <label for="co_name"><?php _e( 'Company name', 'ispag-crm' ); ?> <span style="color:red">*</span></label>
                    <input type="text" id="co_name" name="company_name" required autocomplete="organization">
                </div>

                <div class="ispag-field-group">
                    <label for="co_domain"><?php _e( 'Website / domain', 'ispag-crm' ); ?></label>
                    <input type="text" id="co_domain" name="compagny_domain" placeholder="<?php esc_attr_e( 'ex: company.com', 'ispag-crm' ); ?>" autocomplete="off">
                    <small style="color:#777;"><?php _e( 'Used to link contacts by their email domain and to avoid duplicates.', 'ispag-crm' ); ?></small>
                </div>

                <div class="ispag-company-row">
                    <div>
                        <label for="co_city"><?php _e( 'City', 'ispag-crm' ); ?></label>
                        <input type="text" id="co_city" name="city" autocomplete="address-level2">
                    </div>
                    <div>
                        <label for="co_phone"><?php _e( 'Phone number', 'ispag-crm' ); ?></label>
                        <input type="text" id="co_phone" name="phone" inputmode="tel" autocomplete="tel">
                    </div>
                </div>

                <div class="ispag-field-group">
                    <label for="co_email"><?php _e( 'Email', 'ispag-crm' ); ?></label>
                    <input type="text" id="co_email" name="email" inputmode="email" autocomplete="off">
                </div>

                <div class="ispag-field-group ispag-company-check">
                    <label><input type="checkbox" name="isIngenieur" value="1"> <?php _e( 'Engineering office', 'ispag-crm' ); ?></label>
                </div>

                <div id="company-form-error" role="alert" style="display:none;"></div>
            </div>

            <div class="ispag-modal-footer">
                <button type="button" class="ispag-btn ispag-btn-secondary ispag-company-close"><?php _e( 'Cancel', 'ispag-crm' ); ?></button>
                <button type="submit" class="ispag-btn ispag-btn-primary" id="btn-submit-company"><?php _e( 'Save', 'ispag-crm' ); ?></button>
            </div>
        </form>
    </div>
</div>
