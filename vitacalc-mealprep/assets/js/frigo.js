/* VitaCalc - « Qu'est-ce que j'ai dans mon frigo ? » :
   porte du frigo qui s'ouvre, ingrédients à toucher, recettes classées en direct (prêtes / presque prêtes / idées). */
(function () {
    'use strict';

    var section = document.getElementById('fridge');
    var dataEl = document.getElementById('fridgeData');
    if (!section || !dataEl) { return; }

    var recipes = [];
    try { recipes = JSON.parse(dataEl.textContent) || []; } catch (e) { recipes = []; }

    var box       = document.getElementById('fridgeBox');
    var inside    = document.getElementById('fridgeInside');
    var toggleBtn = document.getElementById('fridgeToggle');
    var closeBtn  = document.getElementById('fridgeClose');
    var search    = document.getElementById('fridgeSearch');
    var noMatch   = document.getElementById('fridgeNoMatch');
    var counter   = document.getElementById('fridgeCount');
    var clearBtn  = document.getElementById('fridgeClear');
    var noteN     = document.getElementById('doorNoteN');
    var noteT     = document.getElementById('doorNoteT');
    var results   = document.getElementById('fridgeResults');
    var summary   = document.getElementById('fridgeSummary');
    var empty     = document.getElementById('fridgeEmpty');
    var emptyTitle = document.getElementById('fridgeEmptyTitle');
    var emptyText  = document.getElementById('fridgeEmptyText');
    var filterBtns = Array.prototype.slice.call(section.querySelectorAll('[data-fridge-filter]'));
    var storeKey  = section.dataset.storage || 'vc_fridge';
    var filter    = 'all';

    if (!box || !toggleBtn) { return; }

    var items = Array.prototype.slice.call(section.querySelectorAll('.fi'));
    var boxes = Array.prototype.slice.call(section.querySelectorAll('.fi input[type="checkbox"]'));
    var cards = {};
    Array.prototype.forEach.call(results.querySelectorAll('.fridge-card'), function (c) { cards[c.dataset.id] = c; });

    // numérotation pour l'animation « les ingrédients sortent un à un »
    Array.prototype.forEach.call(box.querySelectorAll('.fi'), function (el, k) { el.style.setProperty('--n', Math.min(k, 24)); });

    function norm(s) {
        return String(s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/œ/g, 'oe');
    }

    /* ---------- porte du frigo ---------- */
    function setOpen(open) {
        box.classList.toggle('is-open', open);
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggleBtn.setAttribute('aria-label', open ? 'Fermer le frigo' : 'Ouvrir le frigo');
        toggleBtn.hidden = open;           // porte ouverte : le bouton « Fermer » est sous le frigo
        closeBtn.hidden = !open;
        if (open) { inside.removeAttribute('inert'); } else { inside.setAttribute('inert', ''); }
    }
    toggleBtn.addEventListener('click', function () { setOpen(true); });
    closeBtn.addEventListener('click', function () { setOpen(false); toggleBtn.hidden = false; toggleBtn.focus(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && box.classList.contains('is-open') && document.activeElement && inside.contains(document.activeElement)) {
            setOpen(false); toggleBtn.hidden = false; toggleBtn.focus();
        }
    });

    /* ---------- mémorisation des cases cochées ---------- */
    function saveSelection(ids) {
        try { window.localStorage.setItem(storeKey, JSON.stringify(ids)); } catch (e) { /* stockage indisponible */ }
    }
    function loadSelection() {
        try {
            var v = JSON.parse(window.localStorage.getItem(storeKey) || '[]');
            return Array.isArray(v) ? v.map(String) : [];
        } catch (e) { return []; }
    }
    function selectedIds() {
        return boxes.filter(function (b) { return b.checked; }).map(function (b) { return b.value; });
    }

    /* ---------- petits éléments d'interface ---------- */
    function chip(ing, have) {
        var s = document.createElement('span');
        s.className = 'fc-ing ' + (have ? 'have' : 'miss');
        var th = document.createElement('span');
        th.className = 'th';
        th.textContent = ing.e || '';
        if (ing.p) {
            var im = document.createElement('img');
            im.src = ing.p; im.alt = ''; im.loading = 'lazy';
            im.onerror = function () { im.remove(); };
            th.appendChild(im);
        }
        s.appendChild(th);
        s.appendChild(document.createTextNode(ing.n));
        s.title = have ? 'Vous l\'avez' : 'Il vous manque';
        return s;
    }

    function groupHead(cls, icon, label, n) {
        var h = document.createElement('div');
        h.className = 'fr-group ' + cls;
        h.setAttribute('data-fr-group', '1');
        h.innerHTML = '<i class="bi ' + icon + '"></i><span></span><span class="n"></span>';
        h.children[1].textContent = label;
        h.children[2].textContent = n;
        return h;
    }

    function plural(n, one, many) { return n + ' ' + (n > 1 ? many : one); }

    /* ---------- calcul + affichage ---------- */
    function render() {
        var ids = selectedIds();
        var have = {};
        ids.forEach(function (id) { have[id] = true; });
        saveSelection(ids);

        counter.textContent = ids.length + (ids.length > 1 ? ' cochés' : ' coché');
        counter.classList.toggle('on', ids.length > 0);
        noteN.textContent = ids.length;
        noteT.innerHTML = ids.length > 1 ? 'ingrédients<br>au frigo' : 'ingrédient<br>au frigo';

        var scored = recipes.map(function (r) {
            var total = r.ings.length;
            var got = r.ings.filter(function (x) { return have[String(x.i)]; });
            var missing = r.ings.filter(function (x) { return !have[String(x.i)]; });
            var pct = total ? Math.round(got.length / total * 100) : 0;
            return { id: String(r.id), total: total, got: got, missing: missing, pct: pct };
        }).filter(function (s) { return s.got.length > 0; });

        var nFull = scored.filter(function (s) { return s.pct === 100; }).length;
        var nAlmost = scored.filter(function (s) { return s.pct < 100 && s.missing.length <= 2; }).length;

        if (filter === 'full') { scored = scored.filter(function (s) { return s.pct === 100; }); }

        // les plus proches d'abord, puis celles qu'il manque le moins d'ingrédients, puis les plus rapides
        scored.sort(function (a, b) {
            if (b.pct !== a.pct) { return b.pct - a.pct; }
            if (a.missing.length !== b.missing.length) { return a.missing.length - b.missing.length; }
            return (parseInt(cards[a.id] && cards[a.id].dataset.time, 10) || 0) - (parseInt(cards[b.id] && cards[b.id].dataset.time, 10) || 0);
        });

        Object.keys(cards).forEach(function (k) { cards[k].hidden = true; });
        Array.prototype.forEach.call(results.querySelectorAll('[data-fr-group]'), function (h) { h.remove(); });

        var groups = [
            { cls: 'full',   icon: 'bi-check-circle-fill', label: 'Prêtes à cuisiner',   test: function (s) { return s.pct === 100; } },
            { cls: 'almost', icon: 'bi-basket2-fill',      label: 'Presque prêtes (il manque 1 ou 2 ingrédients)', test: function (s) { return s.pct < 100 && s.missing.length <= 2; } },
            { cls: 'ideas',  icon: 'bi-lightbulb-fill',    label: 'Autres idées avec vos ingrédients', test: function (s) { return s.pct < 100 && s.missing.length > 2; } }
        ];

        groups.forEach(function (g) {
            var list = scored.filter(g.test);
            if (!list.length) { return; }
            results.appendChild(groupHead(g.cls, g.icon, g.label, list.length));
            list.forEach(function (s) {
                var card = cards[s.id];
                if (!card) { return; }
                card.hidden = false;
                results.appendChild(card);   // réordonne

                card.querySelector('[data-role="pct"]').textContent = s.pct + ' %';
                var bar = card.querySelector('[data-role="bar"]');
                bar.style.width = s.pct + '%';
                bar.className = s.pct === 100 ? 'full' : (s.pct >= 60 ? 'good' : 'low');
                card.querySelector('.fc-bar').setAttribute('aria-valuenow', s.pct);
                card.classList.toggle('is-full', s.pct === 100);

                var ingsBox = card.querySelector('[data-role="ings"]');
                ingsBox.innerHTML = '';
                s.got.forEach(function (x) { ingsBox.appendChild(chip(x, true)); });
                s.missing.forEach(function (x) { ingsBox.appendChild(chip(x, false)); });

                card.querySelector('[data-role="have"]').innerHTML = s.missing.length === 0
                    ? '<i class="bi bi-stars"></i> Vous avez tout ! Prêt à cuisiner 🎉'
                    : '<i class="bi bi-check-circle-fill"></i> Vous avez ' + s.got.length + ' / ' + s.total + ' · il manque ' + s.missing.length;

                var shop = card.querySelector('[data-role="shop"]');
                shop.hidden = s.missing.length === 0;
                shop.querySelector('span').textContent = 'Acheter ce qui manque';
                card.querySelector('[data-role="cook"]').hidden = s.pct !== 100;
            });
        });

        // résumé en une phrase
        if (ids.length && (nFull || nAlmost)) {
            var parts = [];
            if (nFull) { parts.push(plural(nFull, 'recette prête', 'recettes prêtes')); }
            if (nAlmost) { parts.push(plural(nAlmost, 'presque prête', 'presque prêtes')); }
            summary.textContent = parts.join(' · ');
            summary.hidden = false;
        } else {
            summary.hidden = true;
        }

        if (scored.length === 0) {
            empty.hidden = false;
            if (ids.length === 0) {
                emptyTitle.textContent = 'Ouvrez le frigo et choisissez vos ingrédients';
                emptyText.textContent = 'Les recettes que vous pouvez préparer (ou presque) apparaîtront ici, avec ce qu\'il vous manque.';
            } else if (filter === 'full') {
                emptyTitle.textContent = 'Aucune recette complète pour le moment';
                emptyText.textContent = 'Rien ne peut être préparé uniquement avec ces ingrédients. Cochez-en d\'autres, ou affichez « Toutes » pour voir les recettes les plus proches.';
            } else {
                emptyTitle.textContent = 'Aucune recette ne correspond';
                emptyText.textContent = 'Aucune recette n\'utilise ces ingrédients. Essayez d\'en cocher d\'autres.';
            }
        } else {
            empty.hidden = true;
        }
    }

    /* ---------- recherche d'un ingrédient ---------- */
    function applySearch() {
        var q = norm(search.value.trim());
        var shown = 0;
        items.forEach(function (it) {
            var ok = q === '' || it.dataset.name.indexOf(q) !== -1;
            it.hidden = !ok;
            if (ok) { shown++; }
        });
        Array.prototype.forEach.call(section.querySelectorAll('.shelf'), function (sh) {
            sh.hidden = !sh.querySelector('.fi:not([hidden])');
        });
        if (noMatch) { noMatch.classList.toggle('d-none', shown !== 0); }
        if (q !== '' && !box.classList.contains('is-open')) { setOpen(true); }
    }

    /* ---------- événements ---------- */
    section.addEventListener('change', function (e) { if (e.target && e.target.type === 'checkbox') { render(); } });
    search.addEventListener('input', applySearch);
    clearBtn.addEventListener('click', function () {
        boxes.forEach(function (b) { b.checked = false; });
        render();
    });
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filter = btn.dataset.fridgeFilter;
            filterBtns.forEach(function (b) { b.classList.toggle('on', b === btn); });
            render();
        });
    });

    // reprise de la dernière sélection
    var saved = loadSelection();
    boxes.forEach(function (b) { b.checked = saved.indexOf(b.value) !== -1; });
    setOpen(false);
    toggleBtn.hidden = false;
    render();
})();
