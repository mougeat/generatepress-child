<?php
/**
 * Template Name: ISPAG Deals List Viewer
 * Description: Affiche la liste des transactions ISPAG sous forme de tableau sécurisé.
 */

get_header();

// Valeurs par défaut
$kanban_filters = [
    'owner'  => get_current_user_id(), // Filtre par défaut: utilisateur courant (si non écrasé)
    'status' => 'open',                  // Filtre par défaut: seulement les deals ouverts
    'closing_date' => 'all', 
    'create_date'  => 'all',
    'search'       => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
];

$company_id_filter = null;
$contact_id_filter = null;
$current_owner_filter = absint( $kanban_filters['owner'] ); // Utilisateur courant par défaut
$search_term = '';

// 1. GESTION DES FILTRES BASÉS SUR L'URL (URL_DU_SITE/deals/...)
$request_uri = $_SERVER['REQUEST_URI'];

// a) Analyse des filtres Company/Contact (basés sur le chemin)
if ( preg_match( '#/company/(\d+)#i', $request_uri, $matches ) ) {
    // URL_DU_SITE/deals/company/company_id
    $company_id_filter = absint( $matches[1] );
    // NOTE: Le Deal Repository devra gérer ce filtre spécifique.
    
} elseif ( preg_match( '#/contact/(\d+)#i', $request_uri, $matches ) ) {
    // URL_DU_SITE/deals/contact/contact_id
    $contact_id_filter = absint( $matches[1] );
    // NOTE: Le Deal Repository devra gérer ce filtre spécifique.
}

// b) Analyse du filtre Owner (basé sur $_GET['owner'])
if ( isset( $_GET['owner'] ) ) {
    $owner_param = sanitize_text_field( $_GET['owner'] );
    if ( $owner_param === 'all' ) {
        $current_owner_filter = 'all';
    } else {
        $current_owner_filter = absint( $owner_param );
    }
}
// c) NOUVEAU: Analyse du filtre de Recherche (basé sur $_GET['search'])
if ( isset( $_GET['search'] ) ) {
    // On sanitise le terme de recherche
    $search_term = sanitize_text_field( $_GET['search'] );
}

// d) NOUVEAU: Analyse des filtres de dates
if ( isset( $_GET['closing_date'] ) ) {
    $kanban_filters['closing_date'] = sanitize_key( $_GET['closing_date'] );
}
if ( isset( $_GET['create_date'] ) ) {
    $kanban_filters['create_date'] = sanitize_key( $_GET['create_date'] );
}

// Mise à jour finale du filtre Owner
$kanban_filters['owner'] = $current_owner_filter; 

// Ajout du terme de recherche si présent
$kanban_filters['search'] = null;
if ( ! empty( $search_term ) ) {
    $kanban_filters['search'] = $search_term;
}

// 2. RÉCUPÉRATION DES DONNÉES VIA REPOSITORY
$deals_list = [];
if ( class_exists( 'ISPAG_Crm_Deals_Repository' ) ) {
    $deal_repo = new ISPAG_Crm_Deals_Repository();
    
    // On récupère les deals groupés puis on les aplatit pour le tableau
    $grouped_deals = $deal_repo->get_all_deals_grouped_by_stage( $kanban_filters );
    
    if ( ! empty( $grouped_deals ) ) {
        foreach ( $grouped_deals as $stage_key => $stage_deals ) {
            if ( is_array( $stage_deals ) ) {
                $deals_list = array_merge( $deals_list, $stage_deals );
            }
        }
    }
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <article class="page type-page status-publish hentry">
            <header class="entry-header ispag-kanban-header">
                <h1 class="entry-title"><?php _e('Suivi des Projets (Liste)', 'ispag-crm'); ?></h1>
                
                <div class="ispag-board-controls">
                    <?php 
                        // Appelle le template et lui passe les données
                        ispag_get_template( 'deal-search', [ 'kanban_filters' => $kanban_filters ] ); 
                    ?>
                    
                    
                    <a href="<?php echo home_url('/deals/'); ?>" class="button ispag-btn-secondary-outlined">
                        <span class="dashicons dashicons-columns"></span> <?php _e('Kanban view', 'ispag-crm'); ?>
                    </a>
                </div>
            </header>

            <div class="entry-content">
                <div class="ispag-table-container">
                    <?php 
                        // Appelle le template et lui passe les données
                        ispag_get_template( 'deal-table', [ 'transactions' => $deals_list ] ); 
                    ?>
                    
                </div>
            </div>
        </article>
    </main>
</div>
<!-- 
<style>
.ispag-crm-table td{position: relative; padding: 12px 15px;}
.ispag-badge {
    display: inline-flex;    /* Utilise flex pour aligner le texte au centre du badge */
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
    
    /* Sécurités cruciales */
    position: static !important; /* Force le retour dans le flux normal */
    float: none !important;      /* Empêche le badge de s'échapper à droite */
    line-height: 1.4;
    margin: 0;
}
.ispag-table-container { background: #fff; border: 1px solid #cbd6e2; border-radius: 8px; overflow: hidden; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.ispag-crm-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.ispag-crm-table th { background: #f5f8fa; text-align: left; padding: 12px 15px; border-bottom: 2px solid #cbd6e2; color: #33475b; font-weight: 600; }
.ispag-crm-table td { padding: 12px 15px; border-bottom: 1px solid #eaf0f6; vertical-align: middle; }
.ispag-crm-table tr:hover { background-color: #f9fbfd; }
.amount-cell { font-family: 'Courier New', Courier, monospace; font-weight: bold; text-align: right; color: #2d3e50; }
/* .ispag-badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; white-space: nowrap; } */
.small-text { font-size: 11px; color: #7c98b6; margin-top: 2px; }
.company-name { font-weight: 500; color: #33475b; }
.dashicons { vertical-align: middle; margin-right: 5px; font-size: 18px; }
</style> -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ispag-kanban-search');
    const closingDateFilter = document.getElementById('ispag-kanban-closing-date-filter');
    const clearBtn = document.getElementById('ispag-clear-filters-btn');

    function applyFilters() {
        const url = new URL(window.location.href);
        
        if (searchInput.value.trim()) {
            url.searchParams.set('search', searchInput.value.trim());
        } else {
            url.searchParams.delete('search');
        }

        if (closingDateFilter.value !== 'all') {
            url.searchParams.set('closing_date', closingDateFilter.value);
        } else {
            url.searchParams.delete('closing_date');
        }

        window.location.href = url.pathname + url.search;
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', e => { if (e.key === 'Enter') applyFilters(); });
        searchInput.addEventListener('blur', applyFilters);
    }
    
    if (closingDateFilter) closingDateFilter.addEventListener('change', applyFilters);
    
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            window.location.href = window.location.pathname;
        });
    }
});
</script>

<?php get_footer(); ?>