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
