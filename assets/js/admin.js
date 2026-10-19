/* Admin panel scripts: page builder, media picker, rich text editor, small form helpers. */
(function () {
  'use strict';
  var CMS = window.CMS || { base: '', csrf: '' };

  function url(p) { return CMS.base + '/' + String(p).replace(/^\//, ''); }
  function media(p) { return !p ? '' : /^(https?:)?\/\//.test(p) ? p : url(p); }
  function el(tag, attrs, kids) {
    var n = document.createElement(tag);
    if (attrs) Object.keys(attrs).forEach(function (k) {
      if (k === 'class') n.className = attrs[k];
      else if (k === 'text') n.textContent = attrs[k];
      else if (k === 'html') n.innerHTML = attrs[k];
      else if (k.slice(0, 2) === 'on') n.addEventListener(k.slice(2), attrs[k]);
      else if (attrs[k] !== null && attrs[k] !== undefined && attrs[k] !== false) n.setAttribute(k, attrs[k] === true ? '' : attrs[k]);
    });
    (kids || []).forEach(function (c) { if (c) n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); });
    return n;
  }
  var dirty = false;
  function markDirty() { dirty = true; }

  /* ---------- Sidebar (mobile) ---------- */
  var menuBtn = document.querySelector('.menu-btn');
  if (menuBtn) menuBtn.addEventListener('click', function () {
    var open = document.body.classList.toggle('side-open');
    menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  /* ---------- Confirm before destructive submits ---------- */
  document.addEventListener('submit', function (ev) {
    var f = ev.target;
    if (f.hasAttribute('data-confirm') && !window.confirm(f.getAttribute('data-confirm'))) ev.preventDefault();
  });

  /* ---------- Unsaved changes guard ---------- */
  document.querySelectorAll('[data-dirty-guard], #page-form').forEach(function (f) {
    f.addEventListener('input', markDirty);
    f.addEventListener('change', markDirty);
    f.addEventListener('submit', function () { dirty = false; });
  });
  window.addEventListener('beforeunload', function (ev) {
    if (dirty) { ev.preventDefault(); ev.returnValue = ''; }
  });

  /* ---------- Slug from title ---------- */
  function slugify(s) {
    return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
  var slugSrc = document.querySelector('[data-slug-source]'), slugTgt = document.querySelector('[data-slug-target]');
  if (slugSrc && slugTgt) {
    var touched = slugTgt.value !== '';
    slugTgt.addEventListener('input', function () { touched = true; });
    slugSrc.addEventListener('input', function () { if (!touched) slugTgt.placeholder = slugify(slugSrc.value) || 'otomatis dari judul'; });
  }

  /* ---------- Copy buttons ---------- */
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-copy]');
    if (!b) return;
    var full = location.origin + b.getAttribute('data-copy');
    (navigator.clipboard ? navigator.clipboard.writeText(full) : Promise.reject()).then(function () {
      b.textContent = 'Tersalin ✓';
    }, function () { window.prompt('Salin alamat ini:', full); });
  });

  /* ---------- Color inputs ---------- */
  document.querySelectorAll('[data-color-for]').forEach(function (c) {
    var t = document.querySelector('[name="' + c.getAttribute('data-color-for') + '"]');
    c.addEventListener('input', function () { t.value = c.value; markDirty(); });
  });

  /* ---------- Menu form: show only the relevant target field ---------- */
  var mtype = document.querySelector('[data-menu-type]');
  if (mtype) {
    var sync = function () {
      document.querySelectorAll('[data-menu-form] [data-for]').forEach(function (l) { l.hidden = l.getAttribute('data-for') !== mtype.value; });
    };
    mtype.addEventListener('change', sync);
    sync();
  }

  /* ---------- New page: duplicate option ---------- */
  var dupRadio = document.querySelector('[data-dup-radio]'), dupSel = document.querySelector('[data-dup-select]');
  if (dupRadio && dupSel) {
    var form = dupRadio.form;
    dupSel.addEventListener('change', function () { dupRadio.checked = true; });
    dupSel.addEventListener('focus', function () { dupRadio.checked = true; });
    form.addEventListener('submit', function () { if (dupRadio.checked) dupRadio.value = dupSel.value; });
  }

  /* ---------- File input preview ---------- */
  document.querySelectorAll('[data-preview]').forEach(function (inp) {
    var out = document.querySelector('[data-preview-out]');
    inp.addEventListener('change', function () {
      out.innerHTML = '';
      Array.prototype.forEach.call(inp.files, function (f) {
        if (!/^image\//.test(f.type)) return;
        var img = el('img', { alt: '' });
        img.src = URL.createObjectURL(f);
        out.appendChild(img);
      });
    });
  });

  /* ---------- Media picker ---------- */
  var modal = document.getElementById('media-modal');
  var pickCb = null, mediaCache = null;
  function closeModal(m) { m.hidden = true; document.body.classList.remove('modal-open'); }
  function openModal(m) { m.hidden = false; document.body.classList.add('modal-open'); var f = m.querySelector('[data-close]'); if (f) f.focus(); }
  document.querySelectorAll('.modal').forEach(function (m) {
    m.addEventListener('click', function (ev) { if (ev.target === m || ev.target.closest('[data-close]')) closeModal(m); });
    m.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') closeModal(m); });
  });
  function loadMedia(selectPath) {
    var grid = modal.querySelector('[data-grid]');
    grid.innerHTML = '<p class="muted">Memuat…</p>';
    return fetch(url('admin/media/list'), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (list) {
      mediaCache = list;
      renderMedia('');
      if (selectPath && pickCb) { pickCb(selectPath); closeModal(modal); }
    });
  }
  function renderMedia(filter) {
    var grid = modal.querySelector('[data-grid]');
    grid.innerHTML = '';
    var items = (mediaCache || []).filter(function (m) { return !filter || (m.original || m.file).toLowerCase().indexOf(filter) >= 0; });
    if (!items.length) grid.appendChild(el('p', { class: 'muted', text: 'Belum ada gambar. Unggah gambar baru.' }));
    items.forEach(function (m) {
      grid.appendChild(el('button', { type: 'button', class: 'media-pick', title: m.original || m.file, onclick: function () { if (pickCb) pickCb(m.file); closeModal(modal); } }, [
        el('img', { src: m.url, alt: '', loading: 'lazy' }), el('small', { text: (m.original || '').slice(0, 24) })
      ]));
    });
  }
  function openPicker(cb) {
    if (!modal) return;
    pickCb = cb;
    openModal(modal);
    if (!mediaCache) loadMedia(); else renderMedia('');
  }
  if (modal) {
    modal.querySelector('[data-search]').addEventListener('input', function (ev) { renderMedia(ev.target.value.toLowerCase()); });
    modal.querySelector('[data-upload]').addEventListener('change', function (ev) {
      var files = ev.target.files;
      if (!files.length) return;
      var fd = new FormData();
      Array.prototype.forEach.call(files, function (f) { fd.append('file[]', f); });
      fd.append('_csrf', CMS.csrf);
      fd.append('ajax', '1');
      fd.append('images_only', '1');
      var status = modal.querySelector('[data-status]');
      status.textContent = 'Mengunggah…';
      fetch(url('admin/media/upload'), { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
        status.textContent = res.errors && res.errors.length ? res.errors.join('; ') : '';
        ev.target.value = '';
        loadMedia(res.files && res.files.length === 1 ? res.files[0].path : null);
      }).catch(function () { status.textContent = 'Gagal mengunggah.'; });
    });
  }

  /* Image field widget. get/set via callbacks. */
  function imageWidget(value, onChange) {
    var wrap = el('div', { class: 'imgw' });
    var preview = el('div', { class: 'imgw-preview' });
    var pick = el('button', { type: 'button', class: 'btn sm ghost', text: 'Pilih gambar' });
    var clear = el('button', { type: 'button', class: 'btn sm ghost', text: 'Kosongkan' });
    function draw(v) {
      preview.innerHTML = '';
      preview.appendChild(v ? el('img', { src: media(v), alt: '' }) : el('span', { class: 'muted', text: 'Belum ada gambar' }));
      clear.hidden = !v;
    }
    pick.addEventListener('click', function () { openPicker(function (p) { onChange(p); draw(p); markDirty(); }); });
    clear.addEventListener('click', function () { onChange(''); draw(''); markDirty(); });
    draw(value);
    wrap.appendChild(preview);
    wrap.appendChild(el('div', { class: 'row-btns' }, [pick, clear]));
    return wrap;
  }
  document.querySelectorAll('[data-img-field]').forEach(function (box) {
    var input = box.querySelector('input[type=hidden]');
    box.appendChild(imageWidget(input.value, function (v) { input.value = v; }));
  });

  /* ---------- Rich text editor ---------- */
  function richText(html, onChange) {
    var wrap = el('div', { class: 'rte' });
    var area = el('div', { class: 'rte-area prose', contenteditable: 'true', role: 'textbox', 'aria-multiline': 'true' });
    area.innerHTML = html || '';
    var src = el('textarea', { class: 'rte-src', rows: 10, hidden: true });
    var showingSrc = false;
    function emit() { onChange(showingSrc ? src.value : area.innerHTML); markDirty(); }
    function cmd(c, v) { area.focus(); document.execCommand(c, false, v || null); emit(); }
    var tools = [
      ['B', 'Tebal', function () { cmd('bold'); }], ['I', 'Miring', function () { cmd('italic'); }], ['U', 'Garis bawah', function () { cmd('underline'); }],
      ['H2', 'Judul', function () { cmd('formatBlock', 'h2'); }], ['H3', 'Sub judul', function () { cmd('formatBlock', 'h3'); }], ['¶', 'Paragraf', function () { cmd('formatBlock', 'p'); }],
      ['• Daftar', 'Daftar titik', function () { cmd('insertUnorderedList'); }], ['1. Daftar', 'Daftar nomor', function () { cmd('insertOrderedList'); }],
      ['❝', 'Kutipan', function () { cmd('formatBlock', 'blockquote'); }],
      ['Tautan', 'Sisipkan tautan', function () { var u = window.prompt('Alamat tautan (https://...)'); if (u) cmd('createLink', u); }],
      ['Gambar', 'Sisipkan gambar', function () { var r = saveSel(); openPicker(function (p) { restoreSel(r); cmd('insertImage', media(p)); }); }],
      ['YouTube', 'Sisipkan video YouTube', function () {
        var u = window.prompt('Tautan video YouTube'); if (!u) return;
        var m = u.match(/(?:youtu\.be\/|v=|embed\/|shorts\/)([A-Za-z0-9_-]{11})/); if (!m) return alert('Tautan YouTube tidak dikenali.');
        cmd('insertHTML', '<iframe src="https://www.youtube.com/embed/' + m[1] + '" title="Video" allowfullscreen></iframe><p></p>');
      }],
      ['⌫ Format', 'Hapus format', function () { cmd('removeFormat'); }],
      ['</>', 'Lihat kode HTML', function () {
        showingSrc = !showingSrc;
        if (showingSrc) { src.value = area.innerHTML; } else { area.innerHTML = src.value; }
        src.hidden = !showingSrc; area.hidden = showingSrc; emit();
      }]
    ];
    var bar = el('div', { class: 'rte-bar', role: 'toolbar', 'aria-label': 'Format teks' });
    tools.forEach(function (t) {
      bar.appendChild(el('button', { type: 'button', title: t[1], 'aria-label': t[1], text: t[0], onmousedown: function (e) { e.preventDefault(); }, onclick: t[2] }));
    });
    function saveSel() { var s = window.getSelection(); return s.rangeCount ? s.getRangeAt(0) : null; }
    function restoreSel(r) { if (!r) return; area.focus(); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); }
    area.addEventListener('input', emit);
    src.addEventListener('input', emit);
    wrap.appendChild(bar); wrap.appendChild(area); wrap.appendChild(src);
    return wrap;
  }
  document.querySelectorAll('[data-rte]').forEach(function (box) {
    var ta = box.querySelector('textarea');
    box.appendChild(richText(ta.value, function (v) { ta.value = v; }));
  });

  /* ---------- Page builder ---------- */
  var blocksEl = document.getElementById('blocks');
  if (!blocksEl || !window.BLOCK_SCHEMA) return;
  var SCHEMA = window.BLOCK_SCHEMA;
  var state = (window.PAGE_BLOCKS || []).map(function (b) { b.__open = false; return b; });
  var dragIndex = null;

  function fieldId() { return 'f' + Math.random().toString(36).slice(2, 9); }

  function renderField(f, obj) {
    var id = fieldId();
    var v = obj[f.name];
    var input;
    switch (f.type) {
      case 'note':
        return el('p', { class: 'muted small', text: f.label });
      case 'checkbox':
        input = el('input', { type: 'checkbox', id: id });
        input.checked = !!v;
        input.addEventListener('change', function () { obj[f.name] = input.checked; });
        return el('label', { class: 'check', for: id }, [input, ' ' + f.label]);
      case 'textarea':
        input = el('textarea', { id: id, rows: f.rows || 3 });
        input.value = v || '';
        break;
      case 'select':
        input = el('select', { id: id });
        f.options.forEach(function (o) { input.appendChild(el('option', { value: o[0], text: o[1] })); });
        input.value = v === undefined || v === null ? (f.options[0] ? f.options[0][0] : '') : String(v);
        if (v === undefined) obj[f.name] = input.value;
        break;
      case 'image':
        return el('div', { class: 'field' }, [el('span', { class: 'label', text: f.label }), imageWidget(v || '', function (p) { obj[f.name] = p; })]);
      case 'richtext':
        return el('div', { class: 'field' }, [el('span', { class: 'label', text: f.label }), richText(v || '', function (h) { obj[f.name] = h; })]);
      case 'list':
        return renderList(f, obj);
      default:
        input = el('input', { id: id, type: f.type === 'number' ? 'number' : f.type === 'datetime' ? 'datetime-local' : 'text' });
        input.value = f.type === 'datetime' && v ? String(v).replace(' ', 'T').slice(0, 16) : (v === undefined || v === null ? '' : v);
        if (f.type === 'url') input.placeholder = '/halaman, #bagian, atau https://...';
    }
    input.addEventListener('input', function () { obj[f.name] = input.value; });
    input.addEventListener('change', function () { obj[f.name] = input.value; });
    return el('label', { for: id }, [f.label, input]);
  }

  function renderList(f, obj) {
    if (!Array.isArray(obj[f.name])) obj[f.name] = [];
    var arr = obj[f.name];
    var box = el('div', { class: 'list-field' });
    var head = el('div', { class: 'list-head' }, [el('span', { class: 'label', text: f.label + ' (' + arr.length + ')' })]);
    var items = el('div', { class: 'list-items' });
    function draw() {
      items.innerHTML = '';
      head.firstChild.textContent = f.label + ' (' + arr.length + ')';
      arr.forEach(function (it, i) {
        var title = it.title || it.label || it.q || it.role || it.text || it.value || it.year || it.name || it.caption || ('Item ' + (i + 1));
        var body = el('div', { class: 'list-body' });
        f.fields.forEach(function (sf) { body.appendChild(renderField(sf, it)); });
        var det = el('details', { class: 'list-item' }, [
          el('summary', {}, [
            el('span', { class: 'li-title', text: String(title).replace(/<[^>]+>/g, '').slice(0, 60) }),
            el('span', { class: 'li-actions' }, [
              el('button', { type: 'button', class: 'icon', title: 'Naikkan', 'aria-label': 'Naikkan', text: '↑', onclick: function (e) { e.preventDefault(); if (i > 0) { arr.splice(i - 1, 0, arr.splice(i, 1)[0]); draw(); markDirty(); } } }),
              el('button', { type: 'button', class: 'icon', title: 'Turunkan', 'aria-label': 'Turunkan', text: '↓', onclick: function (e) { e.preventDefault(); if (i < arr.length - 1) { arr.splice(i + 1, 0, arr.splice(i, 1)[0]); draw(); markDirty(); } } }),
              el('button', { type: 'button', class: 'icon', title: 'Gandakan', 'aria-label': 'Gandakan', text: '⧉', onclick: function (e) { e.preventDefault(); arr.splice(i + 1, 0, JSON.parse(JSON.stringify(it))); draw(); markDirty(); } }),
              el('button', { type: 'button', class: 'icon danger-text', title: 'Hapus', 'aria-label': 'Hapus', text: '✕', onclick: function (e) { e.preventDefault(); if (confirm('Hapus item ini?')) { arr.splice(i, 1); draw(); markDirty(); } } })
            ])
          ]),
          body
        ]);
        items.appendChild(det);
      });
    }
    draw();
    var add = el('button', { type: 'button', class: 'btn sm ghost', text: '+ Tambah', onclick: function () {
      var it = {};
      f.fields.forEach(function (sf) { if (sf.type === 'list') it[sf.name] = []; });
      arr.push(it); draw(); items.lastChild.open = true; markDirty();
    } });
    box.appendChild(head); box.appendChild(items); box.appendChild(add);
    return box;
  }

  function blockTitle(b) {
    var t = b.title || b.eyebrow || b.arabic || b.label || '';
    return String(t).replace(/<[^>]+>/g, '').slice(0, 70);
  }

  function render() {
    blocksEl.innerHTML = '';
    if (!state.length) blocksEl.appendChild(el('p', { class: 'muted empty', text: 'Halaman ini belum punya blok. Klik "+ Tambah blok".' }));
    state.forEach(function (b, i) {
      var def = SCHEMA.types[b.type];
      if (!def) return;
      var body = el('div', { class: 'blk-body' });
      if (b.__open) {
        def.fields.forEach(function (f) { body.appendChild(renderField(f, b)); });
        var adv = el('details', { class: 'blk-adv' }, [el('summary', { text: 'Pengaturan bagian (latar, ID, sembunyikan)' })]);
        var advBody = el('div', { class: 'grid2' });
        SCHEMA.common.forEach(function (f) { advBody.appendChild(renderField(f, b)); });
        adv.appendChild(advBody);
        body.appendChild(adv);
      }
      var card = el('div', { class: 'blk-card' + (b._hidden ? ' is-hidden' : '') + (b.__open ? ' open' : ''), 'data-i': i });
      var handle = el('span', { class: 'drag', title: 'Seret untuk memindah', 'aria-hidden': 'true', text: '⠿', draggable: 'true' });
      handle.addEventListener('dragstart', function (e) { dragIndex = i; card.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', String(i)); e.dataTransfer.setDragImage(card, 20, 20); });
      handle.addEventListener('dragend', function () { card.classList.remove('dragging'); dragIndex = null; });
      card.addEventListener('dragover', function (e) { if (dragIndex !== null) { e.preventDefault(); card.classList.add('drop-target'); } });
      card.addEventListener('dragleave', function () { card.classList.remove('drop-target'); });
      card.addEventListener('drop', function (e) {
        e.preventDefault(); card.classList.remove('drop-target');
        if (dragIndex === null || dragIndex === i) return;
        state.splice(i, 0, state.splice(dragIndex, 1)[0]); dragIndex = null; render(); markDirty();
      });
      var toggle = el('button', { type: 'button', class: 'blk-toggle', 'aria-expanded': b.__open ? 'true' : 'false', onclick: function () { b.__open = !b.__open; render(); } }, [
        el('span', { class: 'blk-no', text: String(i + 1) }),
        el('span', { class: 'blk-type', text: def.label }),
        el('span', { class: 'blk-title', text: blockTitle(b) + (b._hidden ? ' · disembunyikan' : '') + (b._anchor ? '  #' + b._anchor : '') })
      ]);
      var actions = el('span', { class: 'blk-actions' }, [
        el('button', { type: 'button', class: 'icon', title: 'Naikkan', 'aria-label': 'Naikkan blok', text: '↑', onclick: function () { if (i > 0) { state.splice(i - 1, 0, state.splice(i, 1)[0]); render(); markDirty(); } } }),
        el('button', { type: 'button', class: 'icon', title: 'Turunkan', 'aria-label': 'Turunkan blok', text: '↓', onclick: function () { if (i < state.length - 1) { state.splice(i + 1, 0, state.splice(i, 1)[0]); render(); markDirty(); } } }),
        el('button', { type: 'button', class: 'icon', title: 'Gandakan blok', 'aria-label': 'Gandakan blok', text: '⧉', onclick: function () { var c = JSON.parse(JSON.stringify(b)); c._anchor = ''; state.splice(i + 1, 0, c); render(); markDirty(); } }),
        el('button', { type: 'button', class: 'icon', title: b._hidden ? 'Tampilkan' : 'Sembunyikan', 'aria-label': b._hidden ? 'Tampilkan blok' : 'Sembunyikan blok', text: b._hidden ? '◌' : '◉', onclick: function () { b._hidden = !b._hidden; render(); markDirty(); } }),
        el('button', { type: 'button', class: 'icon danger-text', title: 'Hapus blok', 'aria-label': 'Hapus blok', text: '✕', onclick: function () { if (confirm('Hapus blok "' + def.label + '"?')) { state.splice(i, 1); render(); markDirty(); } } })
      ]);
      card.appendChild(el('div', { class: 'blk-head' }, [handle, toggle, actions]));
      card.appendChild(body);
      blocksEl.appendChild(card);
    });
  }

  /* Block picker */
  var picker = document.getElementById('block-picker');
  var pickerBody = picker.querySelector('[data-picker]');
  var groups = {};
  Object.keys(SCHEMA.types).forEach(function (k) { var t = SCHEMA.types[k]; (groups[t.group] = groups[t.group] || []).push([k, t]); });
  Object.keys(groups).forEach(function (g) {
    pickerBody.appendChild(el('h3', { text: g }));
    var row = el('div', { class: 'picker-row' });
    groups[g].forEach(function (pair) {
      row.appendChild(el('button', { type: 'button', class: 'pick-btn', text: pair[1].label, onclick: function () {
        var nb = { type: pair[0], __open: true, _bg: '' };
        pair[1].fields.forEach(function (f) { if (f.type === 'list') nb[f.name] = []; });
        state.forEach(function (s) { s.__open = false; });
        state.push(nb); render(); closeModal(picker); markDirty();
        blocksEl.lastChild.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } }));
    });
    pickerBody.appendChild(row);
  });
  document.querySelector('[data-add-block]').addEventListener('click', function () { openModal(picker); });
  document.querySelector('[data-collapse-all]').addEventListener('click', function () { state.forEach(function (s) { s.__open = false; }); render(); });

  document.getElementById('page-form').addEventListener('submit', function () {
    var clean = state.map(function (b) { var c = {}; Object.keys(b).forEach(function (k) { if (k !== '__open') c[k] = b[k]; }); return c; });
    document.getElementById('blocks-json').value = JSON.stringify(clean);
    dirty = false;
  });
  render();
})();
