/* VitaCalc - Liste de courses intelligente :
   le client coche des recettes et choisit les portions, la liste additionne les ingrédients identiques. */
(function () {
    'use strict';

    var app = document.getElementById('courses-app');
    var dataEl = document.getElementById('courses-data');
    if (!app || !dataEl) { return; }

    var recipes = {};
    JSON.parse(dataEl.textContent).forEach(function (r) { recipes[r.id] = r; });

    var rows = Array.prototype.slice.call(app.querySelectorAll('.pick-row'));
    var shopList = document.getElementById('shopList');
    var emptyState = document.getElementById('emptyState');
    var actions = document.getElementById('listActions');
    var countChip = document.getElementById('countChip');
    var copyMsg = document.getElementById('copyMsg');
    var fridgeToggle = document.getElementById('fridgeToggle');
    var useFridge = document.getElementById('useFridge');
    var fridgeHave = {}; // ingrédients cochés dans « Mon frigo »
    var fridgeCount = 0;
    var inFridge = 0;    // articles retirés de la liste parce qu'ils sont déjà au frigo
    var checked = {}; // articles déjà mis dans le panier (clé = ingrédient|unité)

    function fmt(n) { return String(parseFloat(n.toFixed(2))).replace('.', ','); }

    /** 1500 g -> "1,5 kg" ; 2000 ml -> "2 l" */
    function human(q, u) {
        if (u === 'g' && q >= 1000) { return fmt(q / 1000) + ' kg'; }
        if (u === 'ml' && q >= 1000) { return fmt(q / 1000) + ' l'; }
        return fmt(q) + ' ' + u;
    }

    function portionsOf(row) { return parseInt(row.querySelector('.p-val').textContent, 10) || 1; }

    function compute() {
        var totals = {};
        rows.forEach(function (row) {
            row.classList.toggle('selected', row.querySelector('.pick-box').checked);
            if (!row.querySelector('.pick-box').checked) { return; }
            var r = recipes[row.dataset.id];
            var factor = portionsOf(row) / (parseInt(row.dataset.base, 10) || 1);
            r.lignes.forEach(function (l) {
                var key = l.ing + '|' + l.u;
                if (!totals[key]) { totals[key] = { key: key, ing: String(l.ing), nom: l.nom, u: l.u, q: 0, img: l.img, emo: l.emo }; }
                totals[key].q += l.q * factor;
            });
        });
        var all = Object.keys(totals).map(function (k) { return totals[k]; });
        inFridge = 0;
        if (useFridge && useFridge.checked) {
            all = all.filter(function (it) { if (fridgeHave[it.ing]) { inFridge++; return false; } return true; });
        }
        return all
            .sort(function (a, b) { return a.nom.localeCompare(b.nom, 'fr'); });
    }

    function render() {
        var items = compute();
        shopList.innerHTML = '';
        items.forEach(function (it) {
            var li = document.createElement('li');
            if (checked[it.key]) { li.className = 'done'; }
            li.innerHTML = '<span class="l"><span class="check"><i class="bi bi-check-lg"></i></span><span class="th"></span><span class="n"></span></span><span class="q"></span>';
            var th = li.querySelector('.th');
            th.textContent = it.emo || '';
            if (it.img) {
                var im = document.createElement('img');
                im.src = it.img; im.alt = ''; im.loading = 'lazy';
                im.onerror = function () { im.remove(); };
                th.appendChild(im);
            }
            li.querySelector('.n').textContent = it.nom;
            li.querySelector('.q').textContent = human(it.q, it.u);
            li.addEventListener('click', function () {
                checked[it.key] = !checked[it.key];
                li.classList.toggle('done');
            });
            shopList.appendChild(li);
        });
        var anySelected = rows.some(function (r) { return r.querySelector('.pick-box').checked; });
        emptyState.hidden = items.length > 0;
        if (anySelected && items.length === 0 && inFridge > 0) {
            emptyState.hidden = false;
            emptyState.querySelector('p').innerHTML = 'Bonne nouvelle : vous avez déjà tout dans votre frigo 🎉<br>Rien à acheter pour ces recettes.';
        } else {
            emptyState.querySelector('p').innerHTML = 'Cochez une ou plusieurs recettes<br>pour générer votre liste.';
        }
        actions.hidden = items.length === 0;
        countChip.textContent = items.length + ' article' + (items.length > 1 ? 's' : '');
        copyMsg.textContent = '';
        if (fridgeToggle && fridgeCount) {
            document.getElementById('fridgeToggleN').textContent = (useFridge.checked && inFridge) ? '(' + inFridge + ' retiré' + (inFridge > 1 ? 's' : '') + ')' : '(' + fridgeCount + ' dans le frigo)';
        }
        return items;
    }

    rows.forEach(function (row) {
        var box = row.querySelector('.pick-box');
        var val = row.querySelector('.p-val');
        box.addEventListener('change', render);
        row.querySelector('.p-minus').addEventListener('click', function () {
            var n = portionsOf(row); if (n > 1) { val.textContent = n - 1; render(); }
        });
        row.querySelector('.p-plus').addEventListener('click', function () {
            var n = portionsOf(row); if (n < 50) { val.textContent = n + 1; render(); }
        });
    });

    // « Mes favoris » : coche d'un coup toutes les recettes favorites
    var btnFav = document.getElementById('btnFavoris');
    if (btnFav) {
        btnFav.addEventListener('click', function () {
            rows.forEach(function (row) { if (row.dataset.fav === '1') { row.querySelector('.pick-box').checked = true; } });
            render();
        });
    }

    // Copier la liste
    document.getElementById('btnCopy').addEventListener('click', function () {
        var lines = compute().map(function (it) { return '- ' + it.nom + ' : ' + human(it.q, it.u); });
        var text = 'Ma liste de courses VitaCalc\n' + lines.join('\n');
        var done = function () { copyMsg.textContent = 'Liste copiée dans le presse-papiers ✅'; };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done, function () { copyMsg.textContent = 'Copie impossible : sélectionnez la liste à la main.'; });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); done(); } catch (e) { copyMsg.textContent = 'Copie impossible : sélectionnez la liste à la main.'; }
            document.body.removeChild(ta);
        }
    });

    document.getElementById('btnPrint').addEventListener('click', function () { window.print(); });

    document.getElementById('btnReset').addEventListener('click', function () {
        rows.forEach(function (row) { row.querySelector('.pick-box').checked = false; });
        checked = {};
        render();
    });

    // Contenu du frigo (page « Mon frigo ») : permet de ne lister que ce qu'il reste à acheter
    try {
        var saved = JSON.parse(window.localStorage.getItem(app.dataset.fridgeKey) || '[]');
        if (Array.isArray(saved)) { saved.forEach(function (id) { fridgeHave[String(id)] = true; }); fridgeCount = saved.length; }
    } catch (e) { /* stockage indisponible */ }
    if (fridgeToggle && fridgeCount) {
        fridgeToggle.hidden = false;
        useFridge.checked = app.dataset.useFridge === '1';
        useFridge.addEventListener('change', render);
    }

    // Recette présélectionnée (depuis « Ajouter à ma liste de courses »)
    var pre = parseInt(app.dataset.preselect, 10);
    if (pre) {
        rows.forEach(function (row) {
            if (parseInt(row.dataset.id, 10) === pre) { row.querySelector('.pick-box').checked = true; }
        });
    }
    render();
})();
