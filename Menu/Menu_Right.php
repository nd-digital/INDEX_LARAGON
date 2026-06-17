  <aside class="sidebar" id="sidebar">
    <header>
      <?php echo __('sidebar.header'); ?>
    </header>
    <nav class="sidebar-nav" aria-label="<?php echo __('sidebar.header'); ?>">
      <ul>
        <?php
        // Menu is data-driven: categories + links live in Menu/menu.json (editable
        // by hand or via the in-app editor). All values are escaped on output.
        $menu = json_decode(@file_get_contents(__DIR__ . '/menu.json'), true);
        if (!is_array($menu)) $menu = [];
        foreach ($menu as $cat) {
            $key   = isset($cat['key']) ? (string) $cat['key'] : '';
            // Built-in categories keep their i18n key; custom ones use a plain label.
            $label = $key !== '' ? __('menu.' . $key) : (isset($cat['label']) ? (string) $cat['label'] : '');
            $icon  = isset($cat['icon']) ? (string) $cat['icon'] : '';
            $dataMenu = $key !== '' ? $key : strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));
            $links = isset($cat['links']) && is_array($cat['links']) ? $cat['links'] : [];
            ?>
        <li data-menu="<?php echo htmlspecialchars($dataMenu, ENT_QUOTES); ?>">
          <a href="#"><?php if ($icon !== ''): ?><i aria-hidden="true" class="<?php echo htmlspecialchars($icon, ENT_QUOTES); ?>"></i><?php endif; ?><span><?php echo htmlspecialchars($label); ?></span></a>
          <?php if ($links): ?>
          <ul class="nav-flyout">
            <?php foreach ($links as $lnk): ?>
              <?php
                $url = isset($lnk['url']) ? (string) $lnk['url'] : '';
                // Block dangerous URL schemes (defence against javascript:/data: XSS).
                if (preg_match('~^\s*(?:javascript|data|vbscript):~i', $url)) continue;
                $lLabel = isset($lnk['label']) ? (string) $lnk['label'] : $url;
                $lTitle = isset($lnk['title']) && $lnk['title'] !== '' ? (string) $lnk['title'] : $lLabel;
              ?>
            <li><a title="<?php echo htmlspecialchars($lTitle, ENT_QUOTES); ?>" href="<?php echo htmlspecialchars($url, ENT_QUOTES); ?>" target="blank" rel="noopener"><?php echo htmlspecialchars($lLabel); ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>
            <?php
        }
        ?>
      </ul>
    </nav>
  </aside>
  <!-- Toggle sidebar (mobile/tablet) -->
  <button class="sidebar-toggle" id="sidebarToggle" aria-label="<?php echo __('sidebar.header'); ?>" aria-expanded="false" aria-controls="sidebar">
    <span class="sidebar-toggle-arrow" aria-hidden="true">&#10095;</span>
  </button>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
