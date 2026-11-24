(function () {
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.tab'));
  if (!tabs.length) return;

  function show(key) {
    tabs.forEach(function (t) {
      var on = t.dataset.target === key;
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.tabIndex = on ? 0 : -1;
    });
    document.querySelectorAll('.panel').forEach(function (p) {
      p.hidden = p.id !== 'panel-' + key;
    });
  }

  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { show(t.dataset.target); });
    t.addEventListener('keydown', function (e) {
      var d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
      if (!d) return;
      var next = tabs[(i + d + tabs.length) % tabs.length];
      next.focus();
      show(next.dataset.target);
    });
  });

  // Boutons « Voir les conseils » de la section « Recommandé pour vous » : ouvrent l'onglet de la catégorie.
  document.querySelectorAll('[data-goto]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var key = link.dataset.goto;
      if (!document.getElementById('panel-' + key)) return;
      e.preventDefault();
      show(key);
      history.replaceState(null, '', '#' + key);
      var reduit = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      document.querySelector('.tabs').scrollIntoView({ behavior: reduit ? 'auto' : 'smooth', block: 'start' });
    });
  });

  var hash = location.hash.slice(1);
  if (hash && document.getElementById('panel-' + hash)) {
    show(hash);
    document.querySelector('.tabs').scrollIntoView();   // arrivée depuis une recherche : on amène l'onglet à l'écran
  }
})();

// Questionnaire en étapes (sans JavaScript, toutes les questions s'affichent d'un coup et le formulaire reste utilisable).
(function () {
  var form = document.querySelector('.quiz');
  if (!form) return;
  var steps = Array.prototype.slice.call(form.querySelectorAll('.step'));
  if (steps.length < 2) return;

  var prev = form.querySelector('.btn-prev');
  var next = form.querySelector('.btn-next');
  var send = form.querySelector('.btn-submit');
  var err = form.querySelector('.step-error');
  var bar = form.querySelector('.progress-bar');
  var current = form.querySelector('.js-current');
  var total = form.querySelector('.js-total');
  var index = 0;

  form.classList.add('is-stepper');
  form.noValidate = true;            // la vérification est faite étape par étape ci-dessous
  total.textContent = steps.length;

  function valid(step) {
    var groups = {};
    Array.prototype.forEach.call(step.querySelectorAll('input[type="radio"]'), function (r) {
      groups[r.name] = groups[r.name] || r.checked;
    });
    return Object.keys(groups).every(function (name) { return groups[name]; });
  }

  function render() {
    steps.forEach(function (s, i) { s.hidden = i !== index; });
    prev.hidden = index === 0;
    next.hidden = index === steps.length - 1;
    send.hidden = index !== steps.length - 1;
    bar.style.width = ((index + 1) / steps.length * 100) + '%';
    current.textContent = index + 1;
    err.hidden = true;
  }

  next.addEventListener('click', function () {
    if (!valid(steps[index])) { err.hidden = false; return; }
    index += 1;
    render();
  });
  prev.addEventListener('click', function () { index -= 1; render(); });

  form.addEventListener('submit', function (e) {
    for (var i = 0; i < steps.length; i++) {
      if (!valid(steps[i])) {
        e.preventDefault();
        index = i;
        render();
        err.hidden = false;
        return;
      }
    }
  });

  render();
})();
