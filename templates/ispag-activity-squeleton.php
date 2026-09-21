<?php
/**
 * Template pour le squelette de chargement des activités
 */
?>
<div class="ispag-activities-timeline ispag-skeleton-wrapper" style="padding: 10px;">
    <?php for ($i = 0; $i < 3; $i++): ?>
    <div class="ispag-timeline-entry" style="display: flex; gap: 15px; margin-bottom: 15px;">
        <!-- Cercle de gauche -->
        <div class="ispag-skeleton-circle" style="flex-shrink: 0; width: 32px; height: 32px; background-color: #e0e0e0; border-radius: 50%;"></div>
        
        <!-- Carte principale -->
        <div class="ispag-activity-item" style="flex-grow: 1; background: #fff; border: 1px solid #eee; border-radius: 6px; padding: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div class="ispag-skeleton-line ispag-w-60" style="height: 16px;"></div>
                <div class="ispag-skeleton-line ispag-w-20" style="height: 14px;"></div>
            </div>
            <div class="ispag-skeleton-line ispag-w-80" style="height: 14px; margin-top: 8px;"></div>
        </div>
    </div>
    <?php endfor; ?>
</div>