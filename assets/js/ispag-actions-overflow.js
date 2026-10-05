/* Barre d'actions (Note, Appel, E-mail, Tâche, Réunion… + « Plus ») des fiches projet, contact, entreprise et deal :
 *  - les boutons qui ne tiennent pas dans la largeur de la colonne passent dans le menu « Plus » (gardent leur comportement) ;
 *  - le menu « Plus » s'ouvre au clic (il se positionne en fixe : jamais coupé par la colonne) et se ferme en cliquant ailleurs ou sur Échap.
 */
(function () {
  'use strict';

  const BARS = '.ispag-actions-bar, .ispag-quick-actions';

  function primaries(bar, dropdown) {
    return Array.prototype.filter.call(bar.children, function (c) { return c !== dropdown && (c.matches('.ispag-action-btn') || c.querySelector(':scope > .ispag-action-btn')); });
  }

  function layout(bar) {
    const dropdown = bar.querySelector(':scope > .ispag-dropdown');
    if (!dropdown) return;
    const menu = dropdown.querySelector('.ispag-dropdown-menu');
    const toggle = dropdown.querySelector('.ispag-dropdown-toggle');
    if (!menu || !toggle) return;

    // 1. tout remettre dans la barre, dans l'ordre d'origine
    const moved = bar._ispagMoved || [];
    moved.forEach(function (n) { n.classList.remove('ispag-in-overflow'); bar.insertBefore(n, dropdown); });
    const all = primaries(bar, dropdown);
    bar._ispagMoved = [];
    dropdown.classList.remove('has-overflow');

    // 2. déplacer dans le menu, depuis la fin, tant que la barre dépasse sa largeur
    const first = menu.firstElementChild;
    let guard = all.length;
    while (guard-- > 0 && bar.scrollWidth > bar.clientWidth + 1) {
      const last = primaries(bar, dropdown).pop();
      if (!last) break;
      last.classList.add('ispag-in-overflow');
      menu.insertBefore(last, bar._ispagMoved.length ? bar._ispagMoved[0] : first);
      bar._ispagMoved.unshift(last);
      dropdown.classList.add('has-overflow');
    }
  }

  function layoutAll() { document.querySelectorAll(BARS).forEach(layout); }

  // ------------------------------------------------------------------ ouverture / fermeture du menu « Plus »
  function closeMenus(except) {
    document.querySelectorAll('.ispag-dropdown-menu.show').forEach(function (m) { if (m !== except) m.classList.remove('show'); });
  }

  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.ispag-dropdown-toggle');
    if (toggle) {
      e.preventDefault();
      e.stopPropagation();
      const menu = toggle.closest('.ispag-dropdown').querySelector('.ispag-dropdown-menu');
      const open = !menu.classList.contains('show');
      closeMenus(open ? menu : null);
      menu.classList.toggle('show', open);
      if (open) {
        // position fixe, sous le bouton (au-dessus s'il n'y a pas la place) : jamais coupé par la colonne
        const r = toggle.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.minWidth = '200px';
        const h = menu.offsetHeight, w = menu.offsetWidth;
        const below = window.innerHeight - r.bottom, top = below >= h + 8 || below >= r.top ? r.bottom + 4 : r.top - h - 4;
        menu.style.top = Math.max(8, top) + 'px';
        menu.style.bottom = 'auto';
        menu.style.left = Math.max(8, Math.min(r.right - w, window.innerWidth - w - 8)) + 'px';
        menu.style.right = 'auto';
        menu.style.margin = '0';
      }
      return;
    }
    // un clic sur une entrée du menu (ou ailleurs) le referme ; l'action elle-même est traitée par son propre gestionnaire
    if (!e.target.closest('.ispag-dropdown-menu') || e.target.closest('.ispag-dropdown-item, .ispag-action-btn')) closeMenus();
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenus(); });
  window.addEventListener('scroll', function () { closeMenus(); }, true);

  // ------------------------------------------------------------------ mise en page (chargement, redimensionnement, contenu ajouté)
  let raf = null;
  function schedule() { cancelAnimationFrame(raf); raf = requestAnimationFrame(layoutAll); }
  window.addEventListener('resize', schedule);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', schedule); else schedule();
  window.addEventListener('load', schedule);
  if (window.ResizeObserver) {
    const ro = new ResizeObserver(schedule);
    const watch = function () { document.querySelectorAll(BARS).forEach(function (b) { if (!b._ispagWatched) { b._ispagWatched = true; ro.observe(b); } }); };
    watch();
    new MutationObserver(function () { watch(); schedule(); }).observe(document.body, { childList: true, subtree: true });
  }
})();
