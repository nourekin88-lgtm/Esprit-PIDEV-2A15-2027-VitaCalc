<script>
/* Composer une recette : ajouter / retirer des lignes d'ingrédients */
(function () {
    'use strict';
    var rows = document.getElementById('ingRows');
    var tpl = document.getElementById('ingRowTpl');
    if (!rows || !tpl) { return; }

    function addRow(focus) {
        rows.appendChild(tpl.content.cloneNode(true));
        if (focus) { rows.lastElementChild.querySelector('input').focus(); }
    }
    document.getElementById('addRow').addEventListener('click', function () { addRow(true); });

    rows.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove');
        if (!btn) { return; }
        if (rows.children.length > 1) {
            btn.closest('.ing-row').remove();
        } else { // dernière ligne : on la vide au lieu de la supprimer
            btn.closest('.ing-row').querySelectorAll('input').forEach(function (i) { i.value = ''; });
            btn.closest('.ing-row').querySelector('select').value = '';
        }
    });
})();
</script>
