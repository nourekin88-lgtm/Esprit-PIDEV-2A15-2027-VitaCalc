/* VitaCalc - Front Office + authentification */
(function () {
    'use strict';

    /** 1.5 -> "1,5"  /  200 -> "200" */
    function fmt(n) {
        return String(parseFloat(n.toFixed(2))).replace('.', ',');
    }

    // Spinner : disparaît dès que la page est chargée (sécurité : 3 s maximum)
    var spinner = document.getElementById('spinner');
    function hideSpinner() { if (spinner) { spinner.classList.remove('show'); } }
    window.addEventListener('load', hideSpinner);
    setTimeout(hideSpinner, 3000);

    // Bouton « retour en haut »
    var toTop = document.getElementById('backToTop');
    if (toTop) {
        window.addEventListener('scroll', function () {
            toTop.classList.toggle('show', window.scrollY > 300);
        }, { passive: true });
        toTop.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Page recette : portions => quantités recalculées + ingrédients à cocher
    var panel = document.getElementById('recipe-panel');
    if (panel) {
        var base = parseInt(panel.dataset.basePortions, 10) || 1;
        var current = base;
        var val = document.getElementById('portVal');
        var refresh = function () {
            val.textContent = current;
            panel.querySelectorAll('.qty').forEach(function (el) {
                el.textContent = fmt(parseFloat(el.dataset.base) * current / base);
            });
        };
        document.getElementById('portMinus').addEventListener('click', function () { if (current > 1) { current--; refresh(); } });
        document.getElementById('portPlus').addEventListener('click', function () { if (current < 50) { current++; refresh(); } });
        panel.querySelectorAll('.ing-list li').forEach(function (li) {
            li.addEventListener('click', function () { li.classList.toggle('done'); });
        });
    }

    // Confirmation avant une action sensible : <form data-confirm="Message ?">
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });

    // Formulaires : afficher / masquer le mot de passe
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (!input) { return; }
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="bi ' + (show ? 'bi-eye-slash' : 'bi-eye') + '"></i>';
            btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });

    // Inscription : indicateur de robustesse du mot de passe
    var pwd = document.querySelector('[data-strength]');
    if (pwd) {
        var meter = document.querySelector(pwd.getAttribute('data-strength'));
        var label = document.querySelector(pwd.getAttribute('data-strength-label'));
        var labels = ['', 'Trop faible', 'Moyen', 'Bien', 'Excellent'];
        pwd.addEventListener('input', function () {
            var v = pwd.value, score = 0;
            if (v.length >= 8) { score++; }
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) { score++; }
            if (/\d/.test(v)) { score++; }
            if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) { score++; }
            if (!v) { score = 0; }
            meter.setAttribute('data-level', score);
            if (label) { label.textContent = labels[score]; }
        });
    }

    // Connexion : remplir automatiquement un compte de démonstration
    document.querySelectorAll('[data-fill-email]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var email = document.getElementById('email');
            var pass = document.getElementById('motDePasse');
            if (email && pass) {
                email.value = btn.getAttribute('data-fill-email');
                pass.value = btn.getAttribute('data-fill-pass');
                pass.focus();
            }
        });
    });
})();
