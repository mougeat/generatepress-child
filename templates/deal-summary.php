<?php
/**
 * Bandeau « chiffres clés » d'un deal : de quoi décider de la suite en un coup d'œil.
 * Variables attendues : $summary (array) préparé par page-deal-detail-viewer.php
 */
$tiles = [];

// Montant + pondéré
$tiles[] = [
    'icon'  => 'money-alt',
    'label' => __( 'Amount', 'ispag-crm' ),
    'value' => number_format( $summary['amount'], 0, '.', '\'' ) . ' CHF',
    'sub'   => sprintf( __( 'Weighted %s CHF (%s%%)', 'ispag-crm' ), number_format( $summary['weighted'], 0, '.', '\'' ), (float) $summary['probability'] ),
    'level' => '',
];

// Clôture prévue
$d = $summary['closing_days'];
$tiles[] = [
    'icon'  => 'calendar-alt',
    'label' => __( 'Close date', 'ispag-crm' ),
    'value' => $summary['closing_date'] ?: '—',
    'sub'   => $d === null ? '' : ( $d < 0 ? sprintf( __( 'Overdue by %d d', 'ispag-crm' ), -$d ) : ( $d === 0 ? __( 'Today', 'ispag-crm' ) : sprintf( __( 'In %d d', 'ispag-crm' ), $d ) ) ),
    'level' => ( $d !== null && $d < 0 ) ? 'danger' : ( ( $d !== null && $d <= 7 ) ? 'warn' : '' ),
];

// Dernier contact
$c = $summary['contact_days'];
$tiles[] = [
    'icon'  => 'backup',
    'label' => __( 'Last contacted', 'ispag-crm' ),
    'value' => $summary['last_contact'] ?: __( 'Never', 'ispag-crm' ),
    'sub'   => $c === null ? __( 'No activity logged', 'ispag-crm' ) : sprintf( __( '%d days ago', 'ispag-crm' ), $c ),
    'level' => ( $c === null || $c > 30 ) ? 'danger' : ( $c > 14 ? 'warn' : '' ),
];

// Prochaine tâche
$t = $summary['next_task'];
$tiles[] = [
    'icon'  => 'yes-alt',
    'label' => __( 'Next task', 'ispag-crm' ),
    'value' => $t ? ( $t['title'] ?: __( 'Task', 'ispag-crm' ) ) : __( 'None planned', 'ispag-crm' ),
    'sub'   => $t ? $t['due_label'] : __( 'Plan the next step', 'ispag-crm' ),
    'level' => $t ? ( $t['overdue'] ? 'danger' : '' ) : 'warn',
];
?>
<div class="ispag-deal-summary">
    <div class="ispag-deal-summary-tiles">
        <?php foreach ( $tiles as $tile ) : ?>
            <div class="ispag-kpi <?php echo esc_attr( $tile['level'] ? 'is-' . $tile['level'] : '' ); ?>">
                <span class="ispag-kpi-label"><span class="dashicons dashicons-<?php echo esc_attr( $tile['icon'] ); ?>"></span> <?php echo esc_html( $tile['label'] ); ?></span>
                <span class="ispag-kpi-value" title="<?php echo esc_attr( $tile['value'] ); ?>"><?php echo esc_html( $tile['value'] ); ?></span>
                <?php if ( $tile['sub'] !== '' ) : ?><span class="ispag-kpi-sub"><?php echo esc_html( $tile['sub'] ); ?></span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( ! empty( $summary['alerts'] ) ) : ?>
        <ul class="ispag-deal-alerts">
            <?php foreach ( $summary['alerts'] as $alert ) : ?>
                <li class="is-<?php echo esc_attr( $alert['level'] ); ?>"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( $alert['text'] ); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
