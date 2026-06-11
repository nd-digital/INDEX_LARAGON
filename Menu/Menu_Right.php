  <aside class="sidebar" id="sidebar">
    <header>
      <?php echo __('sidebar.header'); ?>
    </header>
    <nav class="sidebar-nav" aria-label="<?php echo __('sidebar.header'); ?>">
      <ul>
        <?php
        // Auto-load every Sub_Menu_*.php in this folder, so adding or removing
        // a category requires NO edit here (and leaves no dead include).
        // "Laragon" is pinned first; the rest are alphabetical (case-insensitive).
        $subMenus = glob(__DIR__ . '/Sub_Menu_*.php') ?: [];
        usort($subMenus, function ($a, $b) {
            $aFirst = strcasecmp(basename($a), 'Sub_Menu_Laragon.php') === 0;
            $bFirst = strcasecmp(basename($b), 'Sub_Menu_Laragon.php') === 0;
            if ($aFirst !== $bFirst) {
                return $aFirst ? -1 : 1;
            }
            return strcasecmp($a, $b);
        });
        foreach ($subMenus as $subMenu) {
            include $subMenu;
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
