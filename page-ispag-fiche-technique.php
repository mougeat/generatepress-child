<?php
/**
 * Template Name: Product Data Sheet (QR) - English
 */

get_header();

// Make sure the CSS path matches your actual file
wp_enqueue_style('ispag-qr-style', get_stylesheet_directory_uri() . '/assets/css/_qr-style.css');

global $wpdb;
$target_table = ISPAG_Crm_Deal_Constants::TABLE_NAME; 
$serial = isset($_GET['serial']) ? sanitize_text_field($_GET['serial']) : null;

$product = null; $project = null; $is_warranty_active = false; $days_left = 0;

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
            $date_livraison = new DateTime($product->date_livraison);
            // Updated to 5 years as per your latest request
            $date_exp = (clone $date_livraison)->modify('+5 years');
            $now = new DateTime();
            $is_warranty_active = ($now < $date_exp);
            $days_left = $now->diff($date_exp)->format("%r%a");
        }
    }
}
?>

<div class="ispag-qr-modern-container">
    <?php if ($serial && $product): ?>
        
        <div class="product-hero-card">
            <div class="badge-warranty <?php echo $is_warranty_active ? 'is-active' : 'is-expired'; ?>">
                <span class="dashicons <?php echo $is_warranty_active ? 'dashicons-shield-alt' : 'dashicons-warning'; ?>"></span>
                <?php 
                    if($is_warranty_active) {
                        printf(__('Active Warranty (%s days left)', 'ispag-crm'), $days_left);
                    } else {
                        _e('Warranty Expired', 'ispag-crm');
                    }
                ?>
            </div>
            <div class="hero-content">
                <span class="category-label"><?php echo esc_html($product->Type_produit ?: 'ISPAG Equipment'); ?></span>
                <h1><?php echo esc_html($product->Article); ?></h1>
                <div class="serial-chip">
                    <span class="label">S/N</span>
                    <span class="value"><?php echo esc_html($serial); ?></span>
                </div>
            </div>
        </div>

        <div class="quick-stats-grid">
            <div class="stat-card">
                <span class="dashicons dashicons-admin-home"></span>
                <div class="stat-data">
                    <label><?php _e('Project', 'ispag-crm'); ?></label>
                    <strong><?php echo esc_html($project->project_name); ?></strong>
                </div>
            </div>
            <div class="stat-card">
                <span class="dashicons dashicons-calendar-alt"></span>
                <div class="stat-data">
                    <label><?php _e('Delivery date', 'ispag-crm'); ?></label>
                    <strong><?php echo date('d.m.Y', strtotime($product->date_livraison)); ?></strong>
                </div>
            </div>
        </div>

        <section class="details-section">
            <h3 class="section-subtitle"><span class="dashicons dashicons-performance"></span> <?php _e('Technical Specifications', 'ispag-crm'); ?></h3>
            <div class="specs-card">
                <?php 
                $specs = [
                    __('Nominal Volume', 'ispag-crm') => $product->Volume ? $product->Volume . ' L' : null,
                    __('Material', 'ispag-crm') => $product->Matiere ?? null,
                    __('Operating Pressure', 'ispag-crm') => $product->Pression ?? null,
                    __('Article ID', 'ispag-crm') => $article_id
                ];
                foreach($specs as $label => $value): if($value): ?>
                    <div class="spec-row">
                        <span class="spec-label"><?php echo $label; ?></span>
                        <span class="spec-value"><?php echo esc_html($value); ?></span>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </section>

        <section class="details-section">
            <h3 class="section-subtitle"><span class="dashicons dashicons-media-document"></span> <?php _e('Documentation', 'ispag-crm'); ?></h3>
            <div class="doc-actions-grid">
                <?php if (!empty($product->last_drawing_url)): ?>
                    <a href="<?php echo esc_url($product->last_drawing_url); ?>" target="_blank" class="action-card">
                        <div class="icon-box pdf"><span class="dashicons dashicons-pdf"></span></div>
                        <div class="action-text">
                            <strong><?php _e('Technical Drawing', 'ispag-crm'); ?></strong>
                            <span>PDF Format</span>
                        </div>
                    </a>
                <?php endif; ?>
                
                <a href="#" class="action-card">
                    <div class="icon-box book"><span class="dashicons dashicons-clipboard"></span></div>
                    <div class="action-text">
                        <strong><?php _e('Maintenance Manual', 'ispag-crm'); ?></strong>
                        <span>View Online</span>
                    </div>
                </a>
            </div>
        </section>

        <div class="support-footer-card">
            <h4><?php _e('Need Assistance?', 'ispag-crm'); ?></h4>
            <p><?php _e('Our technical team in Vaulruz is here to help you.', 'ispag-crm'); ?></p>
            <div class="btn-group">
                <a href="tel:+41269125672" class="btn-primary"><span class="dashicons dashicons-phone"></span> <?php _e('Call Now', 'ispag-crm'); ?></a>
                <a href="mailto:info@ispag-asp.ch?subject=Support Request Serial <?php echo $serial; ?>" class="btn-secondary"><?php _e('Email Support', 'ispag-crm'); ?></a>
            </div>
        </div>

    <?php else: ?>
        <div class="modern-error-state">
            <div class="error-icon">!</div>
            <h2><?php _e('Product Not Found', 'ispag-crm'); ?></h2>
            <p><?php _e('The serial number is invalid or not found in our database.', 'ispag-crm'); ?></p>
            <a href="/" class="btn-back"><?php _e('Back to Home', 'ispag-crm'); ?></a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>