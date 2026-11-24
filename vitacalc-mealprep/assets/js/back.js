/* VitaCalc - Back Office */
(function () {
    'use strict';

    // Preloader
    var pre = document.getElementById('preloader');
    function hidePre() { if (pre) { pre.style.display = 'none'; } }
    window.addEventListener('load', hidePre);
    setTimeout(hidePre, 3000);

    // Menu latéral : replier / afficher
    var toggle = document.getElementById('menu-toggle');
    var overlay = document.querySelector('.overlay');
    var isMobile = function () { return window.innerWidth < 992; };
    if (toggle) {
        toggle.addEventListener('click', function () {
            document.body.classList.toggle(isMobile() ? 'sidebar-open' : 'sidebar-hidden');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function () { document.body.classList.remove('sidebar-open'); });
    }

    // Confirmation avant suppression : <form data-confirm="Message ?">
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });

    // Filtre instantané d'un tableau : <input data-filter="#idTable">
    document.querySelectorAll('input[data-filter]').forEach(function (input) {
        var rows = document.querySelectorAll(input.getAttribute('data-filter') + ' tbody tr[data-name]');
        var empty = document.querySelector(input.getAttribute('data-filter-empty') || '#none');
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase();
            var shown = 0;
            rows.forEach(function (row) {
                var ok = row.getAttribute('data-name').indexOf(q) !== -1;
                row.hidden = !ok;
                if (ok) { shown++; }
            });
            if (empty) { empty.hidden = shown > 0; }
        });
    });

    // Aperçu d'une photo choisie : <input type="file" data-preview="#box">
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var box = document.querySelector(input.getAttribute('data-preview'));
            if (box && input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) { box.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu">'; };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
})();
