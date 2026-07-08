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

// --- GitHub popularity (stars + release downloads), READ-ONLY.
// The browser reads PUBLIC repo data straight from the GitHub API (nothing about
// the user is sent — same idea as a shields.io badge). Cached 6h in localStorage,
// async and non-blocking: offline / rate-limited → badges simply stay hidden. ---
(function () {
  var REPO = 'nd-digital/INDEX_LARAGON';
  var KEY = 'index_laragon_ghstats';
  var TTL = 6 * 3600 * 1000;
  var starEl = document.getElementById('ghStars'), starN = document.getElementById('ghStarCount');
  var dlEl = document.getElementById('ghDownloads'), dlN = document.getElementById('ghDlCount');
  if (!starEl && !dlEl) return;
  function fmt(n) { return n >= 1000 ? (n / 1000).toFixed(n >= 10000 ? 0 : 1) + 'k' : String(n); }
  function render(stars, downloads) {
    if (starEl && typeof stars === 'number') { starN.textContent = fmt(stars); starEl.hidden = false; }
    if (dlEl && typeof downloads === 'number' && downloads > 0) { dlN.textContent = fmt(downloads); dlEl.hidden = false; }
  }
  try {
    var c = JSON.parse(localStorage.getItem(KEY) || 'null');
    if (c && (Date.now() - c.t) < TTL) { render(c.s, c.d); return; }
  } catch (e) {}
  function j(u) { return fetch(u, { headers: { 'Accept': 'application/vnd.github+json' } }).then(function (r) { return r.ok ? r.json() : Promise.reject(); }); }
  Promise.all([
    j('https://api.github.com/repos/' + REPO).then(function (d) { return d.stargazers_count; }).catch(function () { return null; }),
    // Sum download_count across all release assets (0 until assets are attached).
    j('https://api.github.com/repos/' + REPO + '/releases?per_page=100')
      .then(function (rs) { return rs.reduce(function (s, r) { return s + (r.assets || []).reduce(function (a, x) { return a + (x.download_count || 0); }, 0); }, 0); })
      .catch(function () { return null; })
  ]).then(function (res) {
    render(res[0], res[1]);
    try { localStorage.setItem(KEY, JSON.stringify({ s: res[0], d: res[1], t: Date.now() })); } catch (e) {}
  }).catch(function () {});
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
  var addCatTopBtn = modal.querySelector('#menuEditorAddCatTop');
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

    // Icon field with a live preview of the Ionicons glyph next to it.
    var iconWrap = el('div', 'menu-editor-icon-wrap');
    var iconPrev = el('i', 'menu-editor-icon-prev ' + (cat.icon || ''));
    iconPrev.setAttribute('aria-hidden', 'true');
    var icon = input(cat.icon, t('menuedit.icon'), function (v) {
      cat.icon = v;
      iconPrev.className = 'menu-editor-icon-prev ' + v;
    });
    icon.classList.add('menu-editor-icon-input');
    iconWrap.appendChild(iconPrev);
    iconWrap.appendChild(icon);
    var chooseBtn = el('button', 'btn btn-sm btn-outline-light menu-editor-icbtn');
    chooseBtn.type = 'button';
    chooseBtn.textContent = t('menuedit.choose_icon');
    chooseBtn.addEventListener('click', function () { openIconPicker(ci); });
    iconWrap.appendChild(chooseBtn);
    head.appendChild(iconWrap);

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

  function addCategory(toTop) {
    var cat = { label: '', icon: '', links: [] };
    if (toTop) { model.unshift(cat); } else { model.push(cat); }
    render();
    // Focus the new category's label input.
    var inputs = listEl.querySelectorAll('.menu-editor-cat-label-input');
    var target = toTop ? inputs[0] : inputs[inputs.length - 1];
    if (target) { target.focus(); target.scrollIntoView({ block: 'nearest' }); }
  }
  addCatBtn.addEventListener('click', function () { addCategory(false); });
  if (addCatTopBtn) addCatTopBtn.addEventListener('click', function () { addCategory(true); });

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

  // Icon picker (Ionicons grid) -------------------------------------------
  var picker = modal.querySelector('#menuEditorIconPicker');
  var pickerGrid = modal.querySelector('#iconPickerGrid');
  var pickerSearch = modal.querySelector('#iconPickerSearch');
  var pickerClose = modal.querySelector('#iconPickerClose');
  var pickerTarget = -1;
  var pickerBuilt = false;

  function buildIconGrid() {
    if (pickerBuilt || !pickerGrid) return;
    var frag = document.createDocumentFragment();
    (window.IONICONS || []).forEach(function (name) {
      var b = el('button', 'icon-pick');
      b.type = 'button';
      b.title = name;
      b.setAttribute('data-name', name);
      var i = el('i', name);
      i.setAttribute('aria-hidden', 'true');
      b.appendChild(i);
      frag.appendChild(b);
    });
    pickerGrid.appendChild(frag);
    pickerBuilt = true;
  }
  function filterIcons(q) {
    q = (q || '').toLowerCase();
    var btns = pickerGrid.children;
    for (var i = 0; i < btns.length; i++) {
      var name = btns[i].getAttribute('data-name');
      btns[i].style.display = (!q || name.indexOf(q) > -1) ? '' : 'none';
    }
  }
  function openIconPicker(ci) {
    if (!picker) return;
    buildIconGrid();
    pickerTarget = ci;
    picker.hidden = false;
    pickerSearch.value = '';
    filterIcons('');
    pickerSearch.focus();
  }
  function closeIconPicker() { if (picker) { picker.hidden = true; pickerTarget = -1; } }

  if (picker) {
    pickerGrid.addEventListener('click', function (e) {
      var b = e.target.closest('.icon-pick');
      if (!b || pickerTarget < 0) return;
      model[pickerTarget].icon = b.getAttribute('data-name');
      closeIconPicker();
      render();
    });
    pickerSearch.addEventListener('input', function () { filterIcons(pickerSearch.value); });
    pickerClose.addEventListener('click', closeIconPicker);
    picker.addEventListener('click', function (e) { if (e.target === picker) closeIconPicker(); });
    // Escape closes the picker first (without closing the whole modal).
    modal.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !picker.hidden) { e.stopPropagation(); closeIconPicker(); }
    });
  }

  modal.addEventListener('show.bs.modal', loadFromServer);
})();

// --- Cross-navigation between the "customize menu" and "menu editor" modals.
// Hide the current one, then open the other once it is fully hidden (avoids
// stacked backdrops). ---
(function () {
  function bridge(btnId, fromId, toId) {
    var btn = document.getElementById(btnId);
    var from = document.getElementById(fromId);
    var to = document.getElementById(toId);
    if (!btn || !from || !to || typeof bootstrap === 'undefined') return;
    btn.addEventListener('click', function () {
      var inst = bootstrap.Modal.getInstance(from) || new bootstrap.Modal(from);
      from.addEventListener('hidden.bs.modal', function handler() {
        from.removeEventListener('hidden.bs.modal', handler);
        (bootstrap.Modal.getInstance(to) || new bootstrap.Modal(to)).show();
      });
      inst.hide();
    });
  }
  bridge('menuEditorToPrefs', 'menuEditorModal', 'menuPrefsModal');
  bridge('menuPrefsToEditor', 'menuPrefsModal', 'menuEditorModal');
})();

// --- Responsive preview: load one URL into the 3 device iframes (desktop /
// tablet / mobile). Client-only; the last URL is remembered locally. ---
(function () {
  var modal = document.getElementById('responsiveModal');
  if (!modal) return;
  var form   = modal.querySelector('#rpForm');
  var input  = modal.querySelector('#rpUrl');
  var frames = modal.querySelectorAll('.rp-iframe');
  var KEY = 'index_laragon_rp_url';

  // Resolve user input to a loadable URL: full http(s) URL as-is, other schemes
  // rejected, anything else treated as a path under this origin (localhost).
  function normalize(v) {
    v = (v || '').trim();
    if (!v) return '';
    if (/^https?:\/\//i.test(v)) return v;
    if (/^[a-z][a-z0-9+.\-]*:\/\//i.test(v)) return '';
    if (v.charAt(0) !== '/') v = '/' + v;
    return window.location.origin + v;
  }
  function load(url) {
    if (!url) return;
    for (var i = 0; i < frames.length; i++) frames[i].src = url;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var url = normalize(input.value);
    if (!url) return;
    try { localStorage.setItem(KEY, input.value); } catch (err) {}
    load(url);
  });

  // Prefill with the last previewed URL (or the first project) when opening.
  modal.addEventListener('show.bs.modal', function () {
    if (input.value) return;
    var last = '';
    try { last = localStorage.getItem(KEY) || ''; } catch (err) {}
    if (!last) {
      var firstOpt = modal.querySelector('#rpProjects option');
      if (firstOpt) last = firstOpt.value;
    }
    input.value = last;
  });
  modal.addEventListener('shown.bs.modal', function () {
    input.focus();
    if (input.value && !frames[0].getAttribute('src')) load(normalize(input.value));
  });
})();

// --- README modal: make the in-doc anchor links (the language table of contents)
// scroll within the modal body instead of navigating the whole page. ---
(function () {
  var md = document.querySelector('#readmeFileModal .readme-md');
  if (!md) return;
  md.addEventListener('click', function (e) {
    var a = e.target.closest('a[href^="#"]');
    if (!a) return;
    var id = decodeURIComponent((a.getAttribute('href') || '').slice(1));
    if (!id) return;
    var target = md.querySelector('[id="' + id.replace(/(["\\])/g, '\\$1') + '"]');
    if (!target) return;
    e.preventDefault();
    target.scrollIntoView({ block: 'start' });
  });
})();

// --- Live usage counter: subscribes to a prod SSE endpoint and shows a
// split-flap "N coders currently using INDEX Laragon" badge in the footer.
// Anonymous (nothing about the user is sent) and non-blocking: when the server
// is unreachable it keeps the last value stored in localStorage, and only stays
// hidden when nothing has ever been received. Label is translated via window.__
// (usage.one / usage.many), falling back to French. ---
(function () {
  const COUNTER_URL = 'https://live.nicolas-degabriel.digital/events?app=index';
  const wrap = document.getElementById('usageCounter');
  const flaps = document.getElementById('ucFlaps');
  const label = document.getElementById('ucLabel');
  if (!wrap || !flaps || !label || !COUNTER_URL || typeof EventSource === 'undefined') return;
  const KEY = 'indexlaragon-usage';
  // Use the i18n bridge when a real translation exists, otherwise the fallback.
  function t(key, fallback) {
    return (typeof window.__ === 'function' && window.__(key) !== key) ? window.__(key) : fallback;
  }
  function setFlap(el, ch) {
    if (el.dataset.d === ch) return;
    el.dataset.d = ch;
    el.textContent = ch;
    el.classList.remove('flip');
    void el.offsetWidth;
    el.classList.add('flip');
  }
  function render(n) {
    if (!Number.isFinite(n) || n < 0) return;
    const s = String(n);
    while (flaps.children.length < s.length) { const f = document.createElement('span'); f.className = 'flap'; flaps.appendChild(f); }
    while (flaps.children.length > s.length) flaps.removeChild(flaps.lastChild);
    for (let i = 0; i < s.length; i++) setFlap(flaps.children[i], s[i]);
    label.textContent = (n <= 1)
      ? t('usage.one', 'codeur utilise actuellement INDEX Laragon')
      : t('usage.many', 'codeurs utilisent actuellement INDEX Laragon');
    wrap.hidden = false;
  }
  try { const v = parseInt(localStorage.getItem(KEY), 10); if (Number.isFinite(v)) render(v); } catch (e) {}
  try {
    const es = new EventSource(COUNTER_URL); let errs = 0;
    es.onmessage = (ev) => { errs = 0; try { const d = JSON.parse(ev.data); if (typeof d.count === 'number') { render(d.count); try { localStorage.setItem(KEY, String(d.count)); } catch (e) {} } } catch (e) {} };
    es.onerror = () => { if (++errs >= 6) es.close(); };
  } catch (e) {}
})();
