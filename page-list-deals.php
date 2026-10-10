<?php
defined('ABSPATH') || exit;
/**
 * Template Name: ISPAG Deals List Viewer
 * Description: Affiche la liste des transactions ISPAG sous forme de tableau sécurisé.
 */

// get_header();

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
// Le tableau est paginé côté serveur : seules les lignes de la page affichée sont chargées et enrichies
// (étapes, contacts, avatars), au lieu des 4000 deals d'un coup.
$per_page    = 50;
$current_pg  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$total_deals = 0;
$deals_list  = [];
if ( class_exists( 'ISPAG_Crm_Deals_Repository' ) ) {
    $deal_repo = new ISPAG_Crm_Deals_Repository();

    $total_deals = $deal_repo->count_deals( $kanban_filters );
    $total_pages = max( 1, (int) ceil( $total_deals / $per_page ) );
    $current_pg  = min( $current_pg, $total_pages );

    $deals_list = $deal_repo->get_all_deals_grouped_by_stage( array_merge( $kanban_filters, [
        'flat'   => true,
        'limit'  => $per_page,
        'offset' => ( $current_pg - 1 ) * $per_page,
    ] ) );
    if ( ! is_array( $deals_list ) ) $deals_list = [];
}
$total_pages = $total_pages ?? 1;

$page_name = __('Deals', 'ispag-crm');
add_filter('pre_get_document_title', function($title) use ($page_name) {
    if (!empty($page_name)) {
        $site_name = get_bloginfo('name');
        return $page_name . ' | ' . $site_name;
    }
    return $title;
}, 999);

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <article class="page type-page status-publish hentry">
            <header class="entry-header ispag-kanban-header">
                <h1 class="entry-title"><?php _e('Project tracking (list)', 'ispag-crm'); ?></h1>
                
                <div class="ispag-board-controls">
                    <?php 
                        // Appelle le template et lui passe les données
                        echo ispag_get_template( 'deal-search', [ 'kanban_filters' => $kanban_filters ] ); 
                    ?>
                    
                    
                    <a href="<?php echo home_url('/deals/'); ?>" class="ispag-btn ispag-btn-secondary-outlined">
                        <span class="dashicons dashicons-columns"></span> <?php _e('Kanban view', 'ispag-crm'); ?>
                    </a>
                </div>
            </header>

            <div class="entry-content">
                <div class="ispag-table-container">
                    <?php 
                        // Appelle le template et lui passe les données
                        echo ispag_get_template( 'deal-table', [ 'transactions' => $deals_list ] ); 
                    ?>

                    <?php if ( $total_pages > 1 ) : ?>
                        <nav class="ispag-pagination" style="display:flex;gap:6px;align-items:center;justify-content:center;margin:20px 0;flex-wrap:wrap;">
                            <?php
                            $page_url = function ( $n ) { return esc_url( add_query_arg( 'paged', $n ) ); };
                            if ( $current_pg > 1 ) echo '<a class="ispag-btn ispag-btn-secondary-outlined" href="' . $page_url( $current_pg - 1 ) . '">‹</a>';
                            $from = max( 1, $current_pg - 2 ); $to = min( $total_pages, $current_pg + 2 );
                            if ( $from > 1 ) echo '<a class="ispag-btn ispag-btn-secondary-outlined" href="' . $page_url( 1 ) . '">1</a>' . ( $from > 2 ? '<span>…</span>' : '' );
                            for ( $i = $from; $i <= $to; $i++ ) {
                                echo $i === $current_pg
                                    ? '<strong class="ispag-btn ispag-btn-primary">' . $i . '</strong>'
                                    : '<a class="ispag-btn ispag-btn-secondary-outlined" href="' . $page_url( $i ) . '">' . $i . '</a>';
                            }
                            if ( $to < $total_pages ) echo ( $to < $total_pages - 1 ? '<span>…</span>' : '' ) . '<a class="ispag-btn ispag-btn-secondary-outlined" href="' . $page_url( $total_pages ) . '">' . $total_pages . '</a>';
                            if ( $current_pg < $total_pages ) echo '<a class="ispag-btn ispag-btn-secondary-outlined" href="' . $page_url( $current_pg + 1 ) . '">›</a>';
                            ?>
                            <span style="color:#666;margin-left:10px;"><?php printf( esc_html__( '%d deals', 'ispag-crm' ), $total_deals ); ?></span>
                        </nav>
                    <?php elseif ( $total_deals > 0 ) : ?>
                        <p style="text-align:center;color:#666;"><?php printf( esc_html__( '%d deals', 'ispag-crm' ), $total_deals ); ?></p>
                    <?php endif; ?>
                    
                </div>
            </div>
        </article>
    </main>
</div>

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

        url.searchParams.delete('paged');
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

<?php 
echo ispag_get_template( 'deal-reason-for-rejection-modal', [] ); 

get_footer(); ?>