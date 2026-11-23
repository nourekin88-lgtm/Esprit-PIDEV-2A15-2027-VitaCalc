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

  var hash = location.hash.slice(1);
  if (hash && document.getElementById('panel-' + hash)) show(hash);
})();
