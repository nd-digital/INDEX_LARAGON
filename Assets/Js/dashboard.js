/* INDEX_LARAGON — dashboard behaviour.
 * All user preferences are stored locally (localStorage); nothing is written
 * server-side. The current version is provided by the page via
 * window.INDEX_LARAGON_VERSION. */

// --- Toggle sidebar (mobile/tablet) ---
(function () {
  var sidebar = document.getElementById('sidebar');
  var toggle  = document.getElementById('sidebarToggle');
  var overlay = document.getElementById('sidebarOverlay');
  if (!sidebar || !toggle || !overlay) return;

  function openSidebar() {
    sidebar.classList.add('sidebar-open');
    toggle.classList.add('shifted');
    overlay.classList.add('active');
    toggle.setAttribute('aria-expanded', 'true');
    sidebar.setAttribute('tabindex', '-1');
    sidebar.focus();
  }
  function closeSidebar(returnFocus) {
    sidebar.classList.remove('sidebar-open');
    toggle.classList.remove('shifted');
    overlay.classList.remove('active');
    toggle.setAttribute('aria-expanded', 'false');
    if (returnFocus) toggle.focus();
  }

  toggle.addEventListener('click', function () {
    if (sidebar.classList.contains('sidebar-open')) {
      closeSidebar(true);
    } else {
      openSidebar();
    }
  });

  overlay.addEventListener('click', function () { closeSidebar(false); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar.classList.contains('sidebar-open')) {
      closeSidebar(true);
    }
  });
})();

// --- Update check (READ-ONLY): compares the local VERSION to the latest GitHub
// release and shows a banner. Cached 24h in localStorage, async and non-blocking
// — silently skipped when offline. Never modifies anything. ---
(function () {
  var current = window.INDEX_LARAGON_VERSION || '0.0.0';
  var REPO = 'nd-digital/INDEX_LARAGON';
  var KEY = 'index_laragon_update';
  var DAY = 86400000;
  function cmp(a, b) {
    var pa = String(a).replace(/^v/, '').split('.').map(Number);
    var pb = String(b).replace(/^v/, '').split('.').map(Number);
    for (var i = 0; i < 3; i++) { var d = (pa[i] || 0) - (pb[i] || 0); if (d) return d; }
    return 0;
  }
  function show(latest, url) {
    var b = document.getElementById('updateBanner'); if (!b) return;
    document.getElementById('updateLatest').textContent = 'v' + String(latest).replace(/^v/, '');
    if (url) document.getElementById('updateLink').href = url;
    b.classList.add('show');
  }
  var dismiss = document.getElementById('updateDismiss');
  if (dismiss) dismiss.addEventListener('click', function () {
    document.getElementById('updateBanner').classList.remove('show');
  });
  try {
    var c = JSON.parse(localStorage.getItem(KEY) || 'null');
    if (c && (Date.now() - c.t) < DAY) {
      if (cmp(c.latest, current) > 0) show(c.latest, c.url);
    } else {
      // /releases returns 200 with [] when there is no release yet
      // (avoids a console 404 that /releases/latest would produce).
      fetch('https://api.github.com/repos/' + REPO + '/releases?per_page=1', { headers: { 'Accept': 'application/vnd.github+json' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) {
          var rel = Array.isArray(d) && d.length ? d[0] : null;
          if (!rel || !rel.tag_name) return;
          localStorage.setItem(KEY, JSON.stringify({ t: Date.now(), latest: rel.tag_name, url: rel.html_url }));
          if (cmp(rel.tag_name, current) > 0) show(rel.tag_name, rel.html_url);
        })
        .catch(function () {});
    }
  } catch (e) {}
})();

// --- Side-menu customization (preferences stored locally; no server write).
// Enable/disable the whole menu and choose which categories are shown. ---
(function () {
  var KEY = 'index_laragon_menu_prefs';
  var sidebar = document.getElementById('sidebar');
  var toggleBtn = document.getElementById('sidebarToggle');
  function items() { return Array.prototype.slice.call(document.querySelectorAll('#sidebar .sidebar-nav > ul > li[data-menu]')); }
  function get() { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } }
  function save(p) { localStorage.setItem(KEY, JSON.stringify(p)); }
  function apply() {
    var p = get();
    var hidden = p.hidden || [];
    items().forEach(function (li) {
      li.style.display = hidden.indexOf(li.getAttribute('data-menu')) > -1 ? 'none' : '';
    });
    var showSidebar = p.sidebar !== false;
    if (sidebar) sidebar.style.display = showSidebar ? '' : 'none';
    if (toggleBtn) toggleBtn.style.display = showSidebar ? '' : 'none';
    document.body.classList.toggle('menu-hidden', !showSidebar);
  }
  apply();

  var modal = document.getElementById('menuPrefsModal');
  if (!modal) return;
  var list = modal.querySelector('#menuPrefsList');
  var sidebarSwitch = modal.querySelector('#prefSidebarToggle');
  var selectAll = modal.querySelector('#prefSelectAll');

  function build() {
    var p = get();
    var hidden = p.hidden || [];
    sidebarSwitch.checked = p.sidebar !== false;
    list.innerHTML = '';
    items().forEach(function (li) {
      var key = li.getAttribute('data-menu');
      var labelEl = li.querySelector('a span') || li.querySelector('a');
      var label = labelEl ? labelEl.textContent.trim() : key;
      var id = 'pref_' + key;
      var row = document.createElement('div');
      row.className = 'form-check';
      var cb = document.createElement('input');
      cb.className = 'form-check-input'; cb.type = 'checkbox'; cb.id = id;
      cb.checked = hidden.indexOf(key) === -1;
      cb.addEventListener('change', function () {
        var pp = get(); var h = pp.hidden || []; var i = h.indexOf(key);
        if (cb.checked) { if (i > -1) h.splice(i, 1); } else if (i === -1) { h.push(key); }
        pp.hidden = h; save(pp); apply();
      });
      var lab = document.createElement('label');
      lab.className = 'form-check-label'; lab.htmlFor = id; lab.textContent = label;
      row.appendChild(cb); row.appendChild(lab); list.appendChild(row);
    });
  }
  modal.addEventListener('show.bs.modal', build);
  sidebarSwitch.addEventListener('change', function () {
    var p = get(); p.sidebar = sidebarSwitch.checked; save(p); apply();
  });
  selectAll.addEventListener('click', function () {
    var p = get(); p.hidden = []; save(p); apply(); build();
  });
})();

// --- Accessibility options panel (preferences stored locally, applied on load;
// no server write). ---
(function () {
  var KEY = 'index_laragon_a11y';
  var body = document.body;
  var ZOOM = { 1: 1, 2: 1.2, 3: 1.45 };
  var CB = ['protanopia', 'deuteranopia', 'tritanopia'];
  function get() { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } }
  function save(p) { localStorage.setItem(KEY, JSON.stringify(p)); }
  function apply() {
    var p = get();
    body.style.zoom = ZOOM[p.text] || 1;
    body.classList.toggle('theme-light', p.theme === 'light');
    body.classList.toggle('a11y-contrast', !!p.contrast);
    body.classList.toggle('a11y-dyslexia', !!p.dyslexia);
    body.classList.toggle('a11y-spacing', !!p.spacing);
    body.classList.toggle('a11y-cursor', !!p.cursor);
    body.classList.toggle('a11y-reduce-motion', p.animations === false);
    CB.forEach(function (t) { body.classList.toggle('a11y-cb-' + t, p.colorblind === t); });
  }
  apply();

  var modal = document.getElementById('a11yModal');
  if (!modal) return;
  var $ = function (id) { return modal.querySelector(id); };
  var theme = $('#a11yTheme'), lang = $('#a11yLang'), text = $('#a11yTextSize'),
      contrast = $('#a11yContrast'), dys = $('#a11yDyslexia'), spc = $('#a11ySpacing'),
      cur = $('#a11yCursor'), anim = $('#a11yAnimations'), cb = $('#a11yColorblind');
  function sync() {
    var p = get();
    theme.value = p.theme || 'dark';
    text.value = String(p.text || 1);
    contrast.checked = !!p.contrast;
    dys.checked = !!p.dyslexia;
    spc.checked = !!p.spacing;
    cur.checked = !!p.cursor;
    anim.checked = p.animations !== false;
    cb.value = p.colorblind || 'none';
  }
  modal.addEventListener('show.bs.modal', sync);
  function bind(el, fn) { if (el) el.addEventListener('change', fn); }
  bind(theme,    function () { var p = get(); p.theme = theme.value;        save(p); apply(); });
  bind(text,     function () { var p = get(); p.text = +text.value;         save(p); apply(); });
  bind(contrast, function () { var p = get(); p.contrast = contrast.checked; save(p); apply(); });
  bind(dys,      function () { var p = get(); p.dyslexia = dys.checked;      save(p); apply(); });
  bind(spc,      function () { var p = get(); p.spacing = spc.checked;       save(p); apply(); });
  bind(cur,      function () { var p = get(); p.cursor = cur.checked;        save(p); apply(); });
  bind(anim,     function () { var p = get(); p.animations = anim.checked;   save(p); apply(); });
  bind(cb,       function () { var p = get(); p.colorblind = cb.value;       save(p); apply(); });
  bind(lang,     function () { window.location.href = '?lang=' + encodeURIComponent(lang.value); });
  $('#a11yReset').addEventListener('click', function () { localStorage.removeItem(KEY); apply(); sync(); });
})();

// --- Accessible disclosure for the side-menu categories (screen reader /
// keyboard). Each category becomes a button with aria-haspopup and an
// aria-expanded state kept in sync with the focus-driven reveal; Escape
// returns focus to the category, and the "#" jump-to-top is suppressed. ---
(function () {
  var cats = document.querySelectorAll('#sidebar .sidebar-nav > ul > li[data-menu]');
  cats.forEach(function (li) {
    var link = li.querySelector('a');
    var flyout = li.querySelector('.nav-flyout');
    if (!link) return;
    link.setAttribute('role', 'button');
    if (flyout) {
      if (!flyout.id) flyout.id = 'flyout-' + li.getAttribute('data-menu');
      link.setAttribute('aria-haspopup', 'true');
      link.setAttribute('aria-expanded', 'false');
      link.setAttribute('aria-controls', flyout.id);
    }
    link.addEventListener('click', function (e) { e.preventDefault(); });
    // Space would scroll the page on a link; neutralize it (the reveal is focus-driven).
    link.addEventListener('keydown', function (e) { if (e.key === ' ') e.preventDefault(); });
    li.addEventListener('focusin', function () { if (flyout) link.setAttribute('aria-expanded', 'true'); });
    li.addEventListener('focusout', function () {
      setTimeout(function () {
        if (flyout && !li.contains(document.activeElement)) link.setAttribute('aria-expanded', 'false');
      }, 0);
    });
    li.addEventListener('keydown', function (e) { if (e.key === 'Escape') link.focus(); });
  });
})();

// --- Menu editor: edit Menu/menu.json (categories + links) from the UI.
// localhost-only + CSRF + server-side validation/sanitization (see index.php
// save_menu handler). Built-in categories keep their i18n key (label read-only,
// shown translated); custom ones use a free-text label. ---
(function () {
  var modal = document.getElementById('menuEditorModal');
  if (!modal || !Array.isArray(window.INDEX_LARAGON_MENU)) return;

  var listEl   = modal.querySelector('#menuEditorList');
  var statusEl = modal.querySelector('#menuEditorStatus');
  var addCatBtn = modal.querySelector('#menuEditorAddCat');
  var saveBtn  = modal.querySelector('#menuEditorSave');
  var reloadBtn = modal.querySelector('#menuEditorReload');
  var labels   = window.INDEX_LARAGON_MENU_LABELS || {};
  var model    = [];

  function t(k) { return window.__ ? window.__(k) : k; }
  function clone(o) { return JSON.parse(JSON.stringify(o)); }
  function status(msg, kind) {
    statusEl.textContent = msg || '';
    statusEl.className = 'menu-editor-status' + (kind ? ' is-' + kind : '');
  }
  function move(arr, i, d) {
    var j = i + d;
    if (j < 0 || j >= arr.length) return false;
    var tmp = arr[i]; arr[i] = arr[j]; arr[j] = tmp; return true;
  }

  // Small DOM helpers ------------------------------------------------------
  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }
  function input(value, placeholder, onChange) {
    var i = el('input', 'form-control form-control-sm');
    i.type = 'text';
    i.value = value || '';
    if (placeholder) i.placeholder = placeholder;
    i.addEventListener('input', function () { onChange(i.value); });
    return i;
  }
  function iconBtn(symbol, label, onClick) {
    var b = el('button', 'btn btn-sm btn-outline-light menu-editor-icbtn');
    b.type = 'button';
    b.innerHTML = symbol;
    b.title = label;
    b.setAttribute('aria-label', label);
    b.addEventListener('click', onClick);
    return b;
  }

  // Render -----------------------------------------------------------------
  function render() {
    listEl.innerHTML = '';
    if (!model.length) {
      listEl.appendChild(el('p', 'text-muted', t('menuedit.no_links')));
    }
    model.forEach(function (cat, ci) {
      listEl.appendChild(renderCategory(cat, ci));
    });
  }

  function renderCategory(cat, ci) {
    var isBuiltin = !!cat.key;
    var card = el('div', 'menu-editor-cat');

    // Header row: order controls + label + icon + delete
    var head = el('div', 'menu-editor-cat-head');
    head.appendChild(iconBtn('&#9650;', t('menuedit.move_up'), function () {
      if (move(model, ci, -1)) render();
    }));
    head.appendChild(iconBtn('&#9660;', t('menuedit.move_down'), function () {
      if (move(model, ci, 1)) render();
    }));

    var labelField;
    if (isBuiltin) {
      labelField = el('span', 'menu-editor-cat-label');
      labelField.textContent = labels[cat.key] || cat.key;
      var badge = el('span', 'menu-editor-badge', t('menuedit.builtin'));
      labelField.appendChild(badge);
    } else {
      labelField = input(cat.label, t('menuedit.category_label'), function (v) { cat.label = v; });
      labelField.classList.add('menu-editor-cat-label-input');
    }
    head.appendChild(labelField);

    var icon = input(cat.icon, t('menuedit.icon'), function (v) { cat.icon = v; });
    icon.classList.add('menu-editor-icon-input');
    head.appendChild(icon);

    head.appendChild(iconBtn('&times;', t('menuedit.delete'), function () {
      if (confirm(t('menuedit.confirm_delete_cat'))) { model.splice(ci, 1); render(); }
    }));
    card.appendChild(head);

    // Links
    var links = cat.links || (cat.links = []);
    var linksWrap = el('div', 'menu-editor-links');
    if (!links.length) {
      linksWrap.appendChild(el('p', 'text-muted menu-editor-empty', t('menuedit.no_links')));
    }
    links.forEach(function (lnk, li) {
      linksWrap.appendChild(renderLink(links, lnk, li));
    });
    card.appendChild(linksWrap);

    var addLink = el('button', 'btn btn-sm btn-outline-light mt-1');
    addLink.type = 'button';
    addLink.textContent = '+ ' + t('menuedit.add_link');
    addLink.addEventListener('click', function () {
      links.push({ label: '', url: '', title: '' });
      render();
    });
    card.appendChild(addLink);
    return card;
  }

  function renderLink(links, lnk, li) {
    var row = el('div', 'menu-editor-link');
    var ctrls = el('div', 'menu-editor-link-ctrls');
    ctrls.appendChild(iconBtn('&#9650;', t('menuedit.move_up'), function () {
      if (move(links, li, -1)) render();
    }));
    ctrls.appendChild(iconBtn('&#9660;', t('menuedit.move_down'), function () {
      if (move(links, li, 1)) render();
    }));
    ctrls.appendChild(iconBtn('&times;', t('menuedit.delete'), function () {
      links.splice(li, 1); render();
    }));
    row.appendChild(ctrls);

    var fields = el('div', 'menu-editor-link-fields');
    fields.appendChild(input(lnk.label, t('menuedit.link_label'), function (v) { lnk.label = v; }));
    fields.appendChild(input(lnk.url, t('menuedit.link_url'), function (v) { lnk.url = v; }));
    fields.appendChild(input(lnk.title, t('menuedit.link_title'), function (v) { lnk.title = v; }));
    row.appendChild(fields);
    return row;
  }

  // Actions ----------------------------------------------------------------
  function loadFromServer() {
    model = clone(window.INDEX_LARAGON_MENU);
    render();
    status('');
  }

  addCatBtn.addEventListener('click', function () {
    model.push({ label: '', icon: '', links: [] });
    render();
    // Focus the new category label input.
    var inputs = listEl.querySelectorAll('.menu-editor-cat-label-input');
    if (inputs.length) inputs[inputs.length - 1].focus();
  });

  reloadBtn.addEventListener('click', function () {
    if (confirm(t('menuedit.confirm_reload'))) loadFromServer();
  });

  saveBtn.addEventListener('click', function () {
    saveBtn.disabled = true;
    status(t('common.loading'));
    var body = 'save_menu=' + encodeURIComponent(JSON.stringify(model)) +
               '&csrf_token=' + encodeURIComponent(window.INDEX_LARAGON_CSRF || '');
    fetch('./index.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body
    })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (d) {
        saveBtn.disabled = false;
        if (d && d.ok) {
          status(t('menuedit.saved'), 'ok');
          // Reflect the saved state and refresh the live sidebar.
          window.INDEX_LARAGON_MENU = clone(model);
          setTimeout(function () { window.location.reload(); }, 600);
        } else {
          status((d && d.error) || t('menuedit.save_error'), 'error');
        }
      })
      .catch(function () {
        saveBtn.disabled = false;
        status(t('menuedit.save_error'), 'error');
      });
  });

  modal.addEventListener('show.bs.modal', loadFromServer);
})();
