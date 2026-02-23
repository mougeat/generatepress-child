jQuery(document).ready(function($) {
    // --- VARIABLES ---
    const $bulkBar = $('#ispag-bulk-edit-bar');
    const $selectedCount = $('#selected-count');
    const $bulkSelect = $('#ispag-bulk-stage-updater');
    const $bulkBadge = $('#bulk-stage-badge');
    const $bulkDateInput = $('#ispag-bulk-date-contact'); // Le nouveau champ date

    // =========================================================
    // 1. CHANGEMENT INDIVIDUEL
    // =========================================================
    $('body').on('change', '.ispag-stage-updater', function(e) {
        const $select = $(this);
        const dealId = $select.data('deal-id');
        const newStageKey = $select.val();
        const selectedOption = $select.find('option:selected');

        if (dealId && newStageKey) {
            if (newStageKey === 'closed_lost') {
                const $modal = $('#ispag-lost-reason-modal');
                $modal.data({
                    'deal-id': dealId,
                    'stage-key': newStageKey,
                    'label': selectedOption.data('label'),
                    'color': selectedOption.data('color'),
                    'target-select': $select
                }).show();
            } else {
                updateBadgeVisuals($select, selectedOption.data('label'), selectedOption.data('color'));
                sendUpdateStage(dealId, newStageKey); // Ta fonction existante
            }
        }
    });

    // =========================================================
    // 2. GESTION DU BULK EDIT
    // =========================================================
    
    $('body').on('change', '#ispag-select-all-projects', function() {
        $('.ispag-project-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkBarVisibility();
    });

    $('body').on('change', '.ispag-project-checkbox', function() {
        updateBulkBarVisibility();
    });

    function updateBulkBarVisibility() {
        const checkedCount = $('.ispag-project-checkbox:checked').length;
        if (checkedCount > 0) {
            $bulkBar.css('display', 'flex');
            $selectedCount.text(checkedCount);
        } else {
            $bulkBar.hide();
        }
    }

    $bulkSelect.on('change', function() {
        const $option = $(this).find('option:selected');
        if ($option.val()) {
            $bulkBadge.text($option.data('label')).css('background-color', $option.data('color'));
        }
    });

    // BOUTON APPLIQUER BULK
    $('#ispag-bulk-submit').on('click', function() {
        const stageKey = $bulkSelect.val();
        const contactDate = $bulkDateInput.val(); // Récupération de la date
        const projectIds = $('.ispag-project-checkbox:checked').map(function() { return $(this).val(); }).get();

        if (!stageKey || !contactDate) { 
            alert('Veuillez choisir une étape ou une date de contact.'); 
            return; 
        }

        if (confirm('Appliquer les modifications à ' + projectIds.length + ' projets ?')) {
             sendBulkUpdate(projectIds, stageKey, contactDate);
        }
    });

    // =========================================================
    // 3. FONCTIONS DE PASSAGE
    // =========================================================

    function sendBulkUpdate(ids, stageKey, contactDate) {
        const $option = $bulkSelect.find('option:selected');
        const label = $option.data('label') || '';
        const color = $option.data('color') || '';

        if (stageKey === 'closed_lost') {
            const $modal = $('#ispag-lost-reason-modal');
            $modal.data({
                'is-bulk': true,
                'deal-ids': ids,
                'stage-key': stageKey,
                'contact-date': contactDate, // On stocke la date pour plus tard
                'label': label,
                'color': color
            }).show();
        } else {
            // APPEL DE LA FONCTION AJAX (dans l'autre fichier)
            executeBulkAjax(ids, stageKey, contactDate, '');
        }
    }

    function updateBadgeVisuals($select, label, color) {
        const $badge = $select.closest('.ispag-badge-container').find('.ispag-status-badge');
        $badge.text(label).css({
            'background-color': color + '15',
            'color': color,
            'border-color': color
        });
    }

    // Modal Lost Reason
    $(document).on('click', '#ispag-confirm-lost', function() {
        const $modal = $('#ispag-lost-reason-modal');
        const reason = $('#ispag-rejection-reason').val();
        if ($modal.data('is-bulk')) {
            executeBulkAjax($modal.data('deal-ids'), $modal.data('stage-key'), $modal.data('contact-date'), reason);
        } else {
            updateBadgeVisuals($modal.data('target-select'), $modal.data('label'), $modal.data('color'));
            sendUpdateStage($modal.data('deal-id'), $modal.data('stage-key'), reason);
        }
        $modal.hide();
    });
});