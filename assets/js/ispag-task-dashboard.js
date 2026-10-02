/**
 * Tableau des tâches : regroupement par urgence, filtres, recherche, report rapide.
 * Les lignes viennent de templates/task-row.php (data-due, data-type, data-search).
 */
jQuery(function ($) {
    const $table = $('.ispag-task-table');
    if (!$table.length) return;

    const $list      = $('#the-list');
    const $empty     = $list.find('.empty-state-row');
    const todayEnd   = parseInt($table.data('today-end'), 10);
    const weekEnd    = parseInt($table.data('week-end'), 10);
    const labels     = $table.data('labels') || {};
    const state      = { filter: 'all', type: '', search: '' };
    const groupOrder = ['overdue', 'today', 'week', 'later'];

    function toast(message, type) {
        const $t = $('<div class="ispag-kanban-toast"></div>').addClass(type || 'success').text(message).appendTo('body');
        setTimeout(() => $t.addClass('visible'), 10);
        setTimeout(() => { $t.removeClass('visible'); setTimeout(() => $t.remove(), 300); }, 2500);
    }

    function groupOf($row) {
        if ($row.hasClass('row-overdue')) return 'overdue';
        const due = parseInt($row.attr('data-due'), 10);
        if (due <= todayEnd) return 'today';
        if (due <= weekEnd) return 'week';
        return 'later';
    }

    /** Trie, filtre, regroupe sous des en-têtes de section et met à jour les compteurs. */
    function refresh() {
        $list.find('.group-row').remove();
        const $rows = $list.find('tr.task-row').get().map(el => $(el));
        $rows.sort((a, b) => parseInt(a.attr('data-due'), 10) - parseInt(b.attr('data-due'), 10));

        const counts = { all: 0, overdue: 0, today: 0, week: 0, later: 0 };
        const buckets = { overdue: [], today: [], week: [], later: [] };
        const q = state.search.trim().toLowerCase();

        $rows.forEach($r => {
            const g = groupOf($r);
            counts.all++; counts[g]++;
            // Les puces comptent toutes les tâches ; la recherche et le type s'appliquent par-dessus
            const visible = (state.filter === 'all' || state.filter === g || (state.filter === 'week' && g === 'today'))
                && (!state.type || $r.attr('data-type') === state.type)
                && (!q || ($r.attr('data-search') || '').indexOf(q) !== -1);
            $r.toggle(visible);
            if (visible) buckets[g].push($r);
        });

        // Réordonne dans le DOM, avec un en-tête avant chaque section non vide
        let shown = 0;
        groupOrder.forEach(g => {
            if (!buckets[g].length) return;
            $list.append(
                $('<tr class="group-row group-' + g + '"><td colspan="6"></td></tr>')
                    .find('td').text(labels[g] + ' · ' + buckets[g].length).end()
            );
            buckets[g].forEach($r => $list.append($r));
            shown += buckets[g].length;
        });
        // Lignes masquées : en fin de liste, pour ne pas casser l'ordre visible
        $rows.forEach($r => { if (!$r.is(':visible')) $list.append($r); });
        $list.append($empty);
        $empty.toggle(shown === 0);

        $('#task-total-count').text(counts.all);
        // Cumul « This week » = aujourd'hui + reste de la semaine ; « All » = tout
        $('[data-count="all"]').text(counts.all);
        $('[data-count="overdue"]').text(counts.overdue);
        $('[data-count="today"]').text(counts.today);
        $('[data-count="week"]').text(counts.today + counts.week);
    }

    // --- Filtres ---
    $('.ispag-task-chips').on('click', '.ispag-chip', function () {
        $('.ispag-task-chips .ispag-chip').removeClass('is-active');
        $(this).addClass('is-active');
        state.filter = $(this).data('filter');
        refresh();
    });
    $('#taskTypeFilter').on('change', function () { state.type = $(this).val(); refresh(); });
    let searchTimer;
    $('#taskSearch').on('input', function () {
        const v = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { state.search = v; refresh(); }, 150);
    });

    // --- Report rapide ---
    $list.on('click', '.snooze-toggle', function (e) {
        e.stopPropagation();
        const $menu = $(this).siblings('.snooze-menu');
        $('.snooze-menu').not($menu).removeClass('open');
        $menu.toggleClass('open');
    });
    $(document).on('click', () => $('.snooze-menu').removeClass('open'));

    $list.on('click', '.snooze-option', function (e) {
        e.stopPropagation();
        const $row = $(this).closest('tr.task-row');
        const id = $row.attr('id').replace('task-', '');
        $('.snooze-menu').removeClass('open');
        $row.css('opacity', 0.5);

        $.post(ispagNoteData.ajaxurl, {
            action: 'ispag_snooze_task',
            security: ispagNoteData.nonce,
            activity_id: id,
            days: $(this).data('days')
        }).done(function (r) {
            if (r && r.success && r.data.row_html) {
                $row.replaceWith(r.data.row_html);
                refresh();
                toast(labels.snoozed);
            } else {
                $row.css('opacity', 1);
                toast((r && r.data && r.data.message) || labels.error, 'error');
            }
        }).fail(function () {
            $row.css('opacity', 1);
            toast(labels.error, 'error');
        });
    });

    // --- Réactions aux changements venus des autres scripts (création, édition, complétion) ---
    $(document).on('ispag:tasks-changed', refresh);
    $(document).on('ispag:task-completed', () => toast(labels.done));

    refresh();
});
