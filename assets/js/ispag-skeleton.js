/**
 * ISPAG — socle skeleton + chargement AJAX.
 *
 *   ISPAGSkeleton.rows(cols, rows)      -> HTML de <tr> skeleton (pour un <tbody>)
 *   ISPAGSkeleton.lines(n)              -> HTML de n lignes de texte skeleton
 *   ISPAGSkeleton.card(n)               -> HTML d'une carte skeleton
 *   ISPAGLoad(target, options)          -> affiche le skeleton, appelle admin-ajax, injecte le HTML
 *
 * ISPAGLoad options :
 *   action    (string, requis)  action wp_ajax_*
 *   data      (object)          paramètres POST additionnels
 *   skeleton  (string)          HTML skeleton (défaut : ISPAGSkeleton.lines(4))
 *   mode      ('replace'|'append', défaut 'replace')
 *             replace : le contenu de target est remplacé (skeleton pendant l'attente)
 *             append  : le HTML est ajouté à la fin (skeleton ajouté puis retiré)
 *   onDone    (function(data))  appelé après injection, reçoit response.data
 *   onError   (function(err))   appelé en cas d'échec
 *
 * Réponse serveur attendue (wp_send_json_success) : { html: '...', ...extra }
 * Retourne une Promise résolue avec response.data.
 * Une nouvelle requête sur la même cible annule la précédente (pas de réponse obsolète).
 */
(function (window, $) {
    'use strict';

    var SEL = 'ispag-skeleton';

    function repeat(str, n) {
        var out = '';
        for (var i = 0; i < n; i++) out += str;
        return out;
    }

    var Skeleton = {
        rows: function (cols, rows) {
            var widths = ['ispag-w-20', 'ispag-w-60', 'ispag-w-40', 'ispag-w-80', 'ispag-w-50'];
            var cells = '';
            for (var c = 0; c < cols; c++) {
                cells += '<td><span class="ispag-skeleton-line ' + widths[c % widths.length] + '"></span></td>';
            }
            return repeat('<tr class="ispag-skeleton-row ' + SEL + '-wrapper" aria-hidden="true">' + cells + '</tr>', rows || 5);
        },
        lines: function (n) {
            var widths = ['ispag-w-90', 'ispag-w-80', 'ispag-w-60', 'ispag-w-40'];
            var out = '<div class="ispag-skeleton-wrapper ' + SEL + '-block" aria-hidden="true">';
            for (var i = 0; i < (n || 4); i++) {
                out += '<span class="ispag-skeleton-line ' + widths[i % widths.length] + '"></span>';
            }
            return out + '</div>';
        },
        card: function (n) {
            return '<div class="ispag-skeleton-card ' + SEL + '-block" aria-hidden="true">' +
                '<span class="ispag-skeleton-line ispag-w-40"></span>' + Skeleton.lines(n || 3) + '</div>';
        }
    };

    function ajaxUrl() {
        if (window.ispagSkeletonVars && window.ispagSkeletonVars.ajaxurl) return window.ispagSkeletonVars.ajaxurl;
        return window.ajaxurl || '/wp-admin/admin-ajax.php';
    }

    function ISPAGLoad(target, options) {
        var $target = $(target);
        options = options || {};
        if (!$target.length || !options.action) return $.Deferred().reject('ISPAGLoad: cible ou action manquante').promise();

        var mode = options.mode === 'append' ? 'append' : 'replace';
        var skeletonHtml = options.skeleton || Skeleton.lines(4);

        // Annule la requête précédente sur cette cible : évite qu'une réponse lente écrase la plus récente
        var previous = $target.data('ispagXhr');
        if (previous && previous.readyState !== 4) previous.abort();

        var $skeleton = $(skeletonHtml);
        if (mode === 'replace') {
            $target.html($skeleton);
        } else {
            $target.append($skeleton);
        }
        $target.addClass('ispag-is-loading').attr('aria-busy', 'true');

        var payload = $.extend({ action: options.action }, options.data || {});
        var xhr = $.ajax({ url: ajaxUrl(), type: 'POST', data: payload });
        $target.data('ispagXhr', xhr);

        var deferred = $.Deferred();

        xhr.done(function (response) {
            if (response && response.success) {
                var data = response.data || {};
                if (mode === 'replace') {
                    $target.html(data.html || '');
                } else {
                    $skeleton.remove();
                    $target.append(data.html || '');
                }
                if (typeof options.onDone === 'function') options.onDone(data);
                deferred.resolve(data);
            } else {
                $skeleton.remove();
                if (typeof options.onError === 'function') options.onError(response && response.data);
                deferred.reject(response && response.data);
            }
        }).fail(function (jq, status) {
            if (status === 'abort') { deferred.reject('abort'); return; } // remplacée par une requête plus récente
            $skeleton.remove();
            if (typeof options.onError === 'function') options.onError(status);
            deferred.reject(status);
        }).always(function () {
            if ($target.data('ispagXhr') === xhr) {
                $target.removeClass('ispag-is-loading').removeAttr('aria-busy');
            }
        });

        return deferred.promise();
    }

    window.ISPAGSkeleton = Skeleton;
    window.ISPAGLoad = ISPAGLoad;
})(window, window.jQuery);
