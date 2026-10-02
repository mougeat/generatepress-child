<?php
/**
 * Bandeau « chiffres clés » générique (deal, company, contact).
 * Variables attendues : $tiles (array de [icon,label,value,sub,level]), $alerts (array de [level,text])
 */
?>
<div class="ispag-entity-summary">
    <div class="ispag-entity-summary-tiles">
        <?php foreach ( $tiles as $tile ) : ?>
            <div class="ispag-kpi <?php echo esc_attr( $tile['level'] ? 'is-' . $tile['level'] : '' ); ?>">
                <span class="ispag-kpi-label"><span class="dashicons dashicons-<?php echo esc_attr( $tile['icon'] ); ?>"></span> <?php echo esc_html( $tile['label'] ); ?></span>
                <span class="ispag-kpi-value" title="<?php echo esc_attr( $tile['value'] ); ?>"><?php echo esc_html( $tile['value'] ); ?></span>
                <?php if ( $tile['sub'] !== '' ) : ?><span class="ispag-kpi-sub"><?php echo esc_html( $tile['sub'] ); ?></span><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( ! empty( $alerts ) ) : ?>
        <ul class="ispag-entity-alerts">
            <?php foreach ( $alerts as $alert ) : ?>
                <li class="is-<?php echo esc_attr( $alert['level'] ); ?>"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( $alert['text'] ); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
