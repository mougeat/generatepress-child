document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('ispag-kanban-search');
    var ownerFilter = document.getElementById('ispag-kanban-owner-filter');
    var closingDateFilter = document.getElementById('ispag-kanban-closing-date-filter'); 
    var createDateFilter = document.getElementById('ispag-kanban-create-date-filter'); 
    var clearFiltersBtn = document.getElementById('ispag-clear-filters-btn'); // NOUVEAU

    if (!searchInput) {
        return;
    }

    // Fonction pour mettre à jour l'URL avec les paramètres de filtre actuels
    function updateFiltersInUrl() {
        var currentUrl = new URL(window.location.href);
        
        // Nettoyage de tous les paramètres de filtre pour repartir d'une base propre
        currentUrl.searchParams.delete('search');
        currentUrl.searchParams.delete('owner');
        currentUrl.searchParams.delete('closing_date');
        currentUrl.searchParams.delete('create_date');

        // 1. Ajouter le terme de recherche si présent
        var searchTerm = searchInput.value.trim();
        if (searchTerm.length > 0) {
            currentUrl.searchParams.append('search', searchTerm);
        }
        
        // 2. Ajouter le filtre de date de clôture si présent
        if (closingDateFilter && closingDateFilter.value !== 'all') {
            currentUrl.searchParams.append('closing_date', closingDateFilter.value);
        }
        
        // 3. Ajouter le filtre de date de création si présent
        if (createDateFilter && createDateFilter.value !== 'all') {
            currentUrl.searchParams.append('create_date', createDateFilter.value);
        }

        // 4. Ajouter le filtre de owner si présent
        if (ownerFilter && ownerFilter.value !== 'all') {
            currentUrl.searchParams.append('owner', ownerFilter.value);
        }

        // Rediriger pour appliquer les filtres (maintient le chemin de base et ajoute les paramètres)
        window.location.href = currentUrl.pathname + currentUrl.search;
    }
    
    // NOUVEAU: Fonction pour effacer tous les filtres (y compris les filtres par chemin)
    function clearFilters() {
        var pathname = window.location.pathname;
        
        // Supprime les segments de chemin dynamiques : /company/ID ou /contact/ID
        // On suppose que la base est le slug de la page deals
        var cleanedPathname = pathname.replace(/\/(company|contact)\/\d+$/, ''); 

        // Rediriger vers l'URL canonique sans aucun paramètre de requête
        var targetUrl = window.location.origin + cleanedPathname;
        
        // On redirige seulement s'il y a des filtres actifs (query ou path)
        if (window.location.search || pathname !== cleanedPathname) {
            window.location.href = targetUrl;
        } else if (searchInput.value.trim() !== '') {
            // Si le champ de recherche n'est pas vide mais les paramètres d'URL sont clairs
            window.location.href = targetUrl;
        }
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearFilters);
    }
    
    // Gestion du changement des filtres de date et propriétaire
    if (closingDateFilter) {
        closingDateFilter.addEventListener('change', updateFiltersInUrl);
    }
    if (createDateFilter) {
        createDateFilter.addEventListener('change', updateFiltersInUrl);
    }
    if (ownerFilter) {
        ownerFilter.addEventListener('change', updateFiltersInUrl); // Le filtre Owner doit aussi utiliser updateFiltersInUrl
    }


    // Soumettre la recherche lorsque l'utilisateur appuie sur Entrée (dans le champ de recherche)
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            updateFiltersInUrl(); 
        }
    });

    // Soumettre la recherche lorsqu'on quitte le champ (blur) si la valeur a changé
    var initialValue = searchInput.value;
    searchInput.addEventListener('blur', function() {
        if (searchInput.value.trim() !== initialValue.trim()) {
            updateFiltersInUrl(); 
        }
    });
});