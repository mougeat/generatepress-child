<?php
/**
 * Template Name: Product Data Sheet (QR)
 */

get_header();

wp_enqueue_style('ispag-qr-style', get_stylesheet_directory_uri() . '/assets/css/qr-style.css');

global $wpdb;
$target_table = ISPAG_Crm_Deal_Constants::TABLE_NAME; 
$serial = isset($_GET['serial']) ? sanitize_text_field($_GET['serial']) : null;

$product = null; $project = null; $is_warranty_active = false;

if ( $serial ) {
    $parts = explode('-', $serial);
    $project_no = isset($parts[1]) ? $parts[1] : null;
    $article_id = isset($parts[3]) ? $parts[3] : null;

    if ( $project_no ) {
        $project = $wpdb->get_row($wpdb->prepare("SELECT * FROM $target_table WHERE project_num = %s", $project_no));
    }
    if ( $article_id ) {
        $product = apply_filters('ispag_get_article_by_id', null, $article_id);
        if ( $product && !empty($product->date_livraison) ) {
            $date_exp = (new DateTime($product->date_livraison))->modify('+1 years');
            $is_warranty_active = (new DateTime() < $date_exp);
        }
    }
}
?>

<div class="ispag-qr-container">
    <?php if ($serial && $product): ?>
        
        <header class="section-identity">
            <div class="badge-status <?php echo $is_warranty_active ? 'active' : 'expired'; ?>">
                <span class="dashicons <?php echo $is_warranty_active ? 'dashicons-shield-alt' : 'dashicons-warning'; ?>"></span>
                <?php echo $is_warranty_active ? __('Warranty Active', 'ispag-crm') : __('Warranty Inactive', 'ispag-crm'); ?>
            </div>
            <h1><?php echo esc_html($product->Article); ?></h1>
            <p class="serial-main">S/N: <strong><?php echo esc_html($serial); ?></strong></p>
        </header>

        <hr class="section-divider">

        <section class="info-section">
            <h2 class="section-title"><?php _e('Project Information', 'ispag-crm'); ?></h2>
            <div class="info-grid">
                <div class="info-block">
                    <label><?php _e('Project Name', 'ispag-crm'); ?></label>
                    <span><?php echo esc_html($project->project_name); ?></span>
                </div>
                <div class="info-block">
                    <label><?php _e('Project Number', 'ispag-crm'); ?></label>
                    <span><?php echo esc_html($project_no); ?></span>
                </div>
                <div class="info-block">
                    <label><?php _e('Delivery Date', 'ispag-crm'); ?></label>
                    <span><?php echo date('d.m.Y', strtotime($product->date_livraison)); ?></span>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <section class="info-section">
            <h2 class="section-title"><?php _e('Technical Specifications', 'ispag-crm'); ?></h2>
            
            <?php if(!empty($product->description)): ?>
                <div class="product-description">
                    <span class="dashicons dashicons-info"></span>
                    <div><?php echo wpautop(esc_html($product->description)); ?></div>
                </div>
            <?php endif; ?>

            <ul class="spec-list">
                <li>
                    <span class="label"><?php _e('Article Designation', 'ispag-crm'); ?></span>
                    <span class="value"><?php echo esc_html($product->Article); ?></span>
                </li>
                <?php if(!empty($product->Volume)): ?>
                <li>
                    <span class="label"><?php _e('Nominal Volume', 'ispag-crm'); ?></span>
                    <span class="value"><?php echo esc_html($product->Volume); ?> Liters</span>
                </li>
                <?php endif; ?>
                <li>
                    <span class="label"><?php _e('Article ID', 'ispag-crm'); ?></span>
                    <span class="value"><?php echo esc_html($article_id); ?></span>
                </li>
            </ul>
        </section>

        <hr class="section-divider">

        <section class="info-section">
            <h2 class="section-title"><?php _e('Documentation', 'ispag-crm'); ?></h2>
            <ul class="clean-doc-list">
                <?php if (!empty($product->last_drawing_url)): ?>
                <li>
                    <a href="<?php echo esc_url($product->last_drawing_url); ?>" target="_blank" class="doc-link">
                        <span class="dashicons dashicons-pdf icon-main"></span>
                        <div class="doc-info">
                            <strong><?php _e('Technical Drawing', 'ispag-crm'); ?></strong>
                            <small><?php _e('Download PDF version', 'ispag-crm'); ?></small>
                        </div>
                        <span class="dashicons dashicons-external icon-external"></span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="#" target="_blank" class="doc-link">
                        <span class="dashicons dashicons-clipboard icon-main"></span>
                        <div class="doc-info">
                            <strong><?php _e('Maintenance Manual', 'ispag-crm'); ?></strong>
                            <small><?php _e('Safety and upkeep guidelines', 'ispag-crm'); ?></small>
                        </div>
                        <span class="dashicons dashicons-external icon-external"></span>
                    </a>
                </li>
            </ul>
        </section>

        <hr class="section-divider">

        <section class="info-section">
            <h2 class="section-title"><?php _e('Assistance & Support', 'ispag-crm'); ?></h2>
            <div class="support-content">
                <p><?php _e('Our technical team in Vaulruz is available to assist you with maintenance or spare parts.', 'ispag-crm'); ?></p>
                <div class="contact-buttons">
                    <a href="tel:+41269125672" class="btn-main">
                        <span class="dashicons dashicons-phone"></span> <?php _e('Call Support', 'ispag-crm'); ?>
                    </a>
                    <a href="mailto:info@ispag-asp.ch" class="btn-sub">
                        <span class="dashicons dashicons-email"></span> <?php _e('Contact by Email', 'ispag-crm'); ?>
                    </a>
                </div>
            </div>
        </section>

    <?php else: ?>
        <div class="error-msg">
            <span class="dashicons dashicons-warning"></span>
            <h2><?php _e('Product not found', 'ispag-crm'); ?></h2>
            <p><?php _e('Please check the serial number on the nameplate.', 'ispag-crm'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>