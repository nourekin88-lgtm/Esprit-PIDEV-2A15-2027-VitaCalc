/* VitaCalc — effets d'interaction (apparition au défilement, barre de remplissage).
   Aucune dépendance, aucune modification de la logique existante. */
(function () {
    'use strict';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* 1. Barre de remplissage de la page détail : part de 0 puis monte à sa valeur */
    function animateCapacityBar() {
        var bar = document.querySelector('.capacity-bar');
        if (!bar || reduce) return;
        var target = bar.style.width;
        if (!target) return;
        bar.style.width = '0%';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { bar.style.width = target; });
        });
    }

    /* 2. Apparition progressive des sections de l'accueil au défilement */
    function initScrollReveal() {
        if (reduce || !('IntersectionObserver' in window)) return;
        if (document.body.getAttribute('data-page') !== 'home') return;

        var targets = [];
        document.querySelectorAll('main > section:not(.hero), main .section').forEach(function (el) {
            if (el.closest('.hero') || targets.indexOf(el) !== -1) return;
            targets.push(el);
        });
        document.querySelectorAll('main .tilegrid .tile').forEach(function (el, i) {
            el.style.setProperty('--fx-delay', (i % 4) * 90 + 'ms');
            targets.push(el);
        });
        if (!targets.length) return;

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('fx-in');
                io.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        targets.forEach(function (el) { el.classList.add('fx-reveal'); io.observe(el); });
        document.documentElement.classList.add('fx-ready');
    }

    function init() { animateCapacityBar(); initScrollReveal(); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
