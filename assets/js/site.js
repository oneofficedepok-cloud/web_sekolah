(function () {
  // Mobile menu
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('mainnav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  document.querySelectorAll('.sub-toggle').forEach(function (b) {
    b.addEventListener('click', function () {
      var li = b.closest('li');
      var open = li.classList.toggle('open');
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  // Staff filter
  document.querySelectorAll('[data-filter-group]').forEach(function (group) {
    var target = group.closest('.stack').querySelector('[data-filter-target]');
    group.addEventListener('click', function (ev) {
      var btn = ev.target.closest('[data-filter]');
      if (!btn) return;
      group.querySelectorAll('.chip').forEach(function (c) { c.classList.toggle('on', c === btn); });
      var v = btn.getAttribute('data-filter');
      target.querySelectorAll('[data-group]').forEach(function (p) {
        p.hidden = v !== '' && p.getAttribute('data-group') !== v;
      });
    });
  });

  // Countdown
  document.querySelectorAll('[data-deadline]').forEach(function (el) {
    var end = new Date(el.getAttribute('data-deadline').replace(' ', 'T')).getTime();
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function tick() {
      var s = Math.max(0, Math.floor((end - Date.now()) / 1000));
      el.querySelector('[data-d]').textContent = pad(Math.floor(s / 86400));
      el.querySelector('[data-h]').textContent = pad(Math.floor(s % 86400 / 3600));
      el.querySelector('[data-m]').textContent = pad(Math.floor(s % 3600 / 60));
      el.querySelector('[data-s]').textContent = pad(s % 60);
    }
    tick();
    setInterval(tick, 1000);
  });

  // Lightbox
  var lb = document.querySelector('.lightbox');
  if (!lb) return;
  var img = lb.querySelector('img'), cap = lb.querySelector('figcaption');
  var items = [], idx = 0, last = null;
  function show(i) {
    idx = (i + items.length) % items.length;
    img.src = items[idx].getAttribute('href');
    img.alt = items[idx].getAttribute('data-caption') || '';
    cap.textContent = items[idx].getAttribute('data-caption') || '';
  }
  function close() { lb.hidden = true; img.src = ''; if (last) last.focus(); }
  document.addEventListener('click', function (ev) {
    var a = ev.target.closest('[data-lightbox]');
    if (!a) return;
    ev.preventDefault();
    last = a;
    items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox="' + a.getAttribute('data-lightbox') + '"]'));
    show(items.indexOf(a));
    lb.hidden = false;
    lb.querySelector('.lb-close').focus();
  });
  lb.querySelector('.lb-close').addEventListener('click', close);
  lb.querySelector('.lb-prev').addEventListener('click', function () { show(idx - 1); });
  lb.querySelector('.lb-next').addEventListener('click', function () { show(idx + 1); });
  lb.addEventListener('click', function (ev) { if (ev.target === lb) close(); });
  document.addEventListener('keydown', function (ev) {
    if (lb.hidden) return;
    if (ev.key === 'Escape') close();
    if (ev.key === 'ArrowLeft') show(idx - 1);
    if (ev.key === 'ArrowRight') show(idx + 1);
  });
})();
