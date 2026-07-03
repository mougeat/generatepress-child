/**
 * ISPAG CRM - Bulk Actions Management
 * File: assets/js/ispag-crm-contact-bulk-actions.js
 * Basé sur l'objet global : ispag_ajax
 */
jQuery(document).ready(function($) {
    
    // --- ÉLÉMENTS DU DOM ---
    const $selectAll = $('#ispag-select-all');
    const $actionSelector = $('#bulk-action-selector-top');
    const $extraFields = $('#bulk-extra-fields');
    const $bulkForm = $('#ispag-bulk-actions-form');

    // 1. GESTION DU "TOUT SÉLECTIONNER"
    if ($selectAll.length) {
        $selectAll.on('change', function() {
            const isChecked = $(this).prop('checked');
            $('input[name="contact_id[]"]').prop('checked', isChecked);
        });
    }

    // 2. AFFICHAGE DYNAMIQUE DES CHAMPS SELON L'ACTION
    if ($actionSelector.length && $extraFields.length) {
        $actionSelector.on('change', function() {
            const action = $(this).val();
            let html = '';

            // Sécurité : on vérifie que les données du plugin sont bien chargées
            if (typeof ispag_ajax === 'undefined' || !ispag_ajax.i18n) {
                console.error("ISPAG CRM: L'objet ispag_ajax est introuvable.");
                return;
            }

            switch (action) {
                case 'set_owner':
                    html = `<select name="new_owner_id" required class="ispag-bulk-inner-select">`;
                    html += `<option value="">${ispag_ajax.i18n.select_owner}</option>`;
                    
                    // On boucle sur les propriétaires fournis par le plugin
                    if (ispag_ajax.owners) {
                        $.each(ispag_ajax.owners, function(id, name) {
                            if (id && id !== "0") {
                                html += `<option value="${id}">${name}</option>`;
                            }
                        });
                    }
                    html += `</select>`;
                    break;

                case 'set_priority':
                    html = `<select name="new_priority" required class="ispag-bulk-inner-select">
                                <option value="P1">${ispag_ajax.i18n.high}</option>
                                <option value="P2">${ispag_ajax.i18n.medium}</option>
                                <option value="P3">${ispag_ajax.i18n.low}</option>
                            </select>`;
                    break;

                case 'set_company':
                    html = `<input type="number" name="new_company_id" 
                            placeholder="${ispag_ajax.i18n.company_id}" 
                            required class="ispag-bulk-inner-input">`;
                    break;

                case 'delete':
                    html = `<span class="ispag-bulk-warning">
                                ${ispag_ajax.i18n.confirm_delete}
                            </span>`;
                    break;

                default:
                    html = ''; // Vide si "Actions groupées" est sélectionné
            }

            $extraFields.html(html);
        });
    }

    // 3. VALIDATION AVANT SOUMISSION
    if ($bulkForm.length) {
        $bulkForm.on('submit', function(e) {
            const selectedAction = $actionSelector.val();
            const checkedCount = $('input[name="contact_id[]"]:checked').length;

            // Vérification qu'une action réelle est choisie
            if (selectedAction === '-1' || selectedAction === '') {
                alert(ispag_ajax.i18n.select_action);
                e.preventDefault();
                return;
            }

            // Vérification qu'au moins un contact est coché
            if (checkedCount === 0) {
                alert(ispag_ajax.i18n.select_contact);
                e.preventDefault();
                return;
            }

            // Confirmation finale pour la suppression
            if (selectedAction === 'delete') {
                if (!confirm(ispag_ajax.i18n.confirm_delete)) {
                    e.preventDefault();
                }
            }
        });
    }
});