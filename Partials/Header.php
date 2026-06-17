    <header>
        <div>
            <span style="display:inline-flex; align-items:center; gap:.6rem;">
                <img src="./INDEX_LARAGON/Assets/Picture/elephant.svg" alt="Laragon" class="laragon-logo">
                <h1 class="title"><?php echo __('header.title'); ?></h1>
            </span>
            <span>
            <p class="byline"><?php echo __('header.by'); ?> <a href="https://nicolas-degabriel.digital">www.nicolas-degabriel.digital</a></p>
            </span>
        </div>
        <div class="header-two-cols">
          <div class="header-info-col">
            <div class="date_time"><b><?php echo __('header.date_label'); ?></b> <span id="live_clock"><?php echo date("D. d-F-Y H:i:s"); ?></span></div>
            <div><b><?php echo __('header.server_label'); ?></b> <?php print($_SERVER['SERVER_SOFTWARE']); ?></div>
            <div><b><?php echo __('header.php_version'); ?></b> <?php print phpversion(); ?></div>
            <div><b><?php echo __('header.document_root'); ?></b> <i>(<?php echo __('header.absolute_path'); ?>)</i>: <?php print($_SERVER['DOCUMENT_ROOT']); ?>/</div>
          </div>
        </div>
        <script>
            (function () {
                const days   = [__('clock.days.0'),__('clock.days.1'),__('clock.days.2'),__('clock.days.3'),__('clock.days.4'),__('clock.days.5'),__('clock.days.6')];
                const months = [__('clock.months.0'),__('clock.months.1'),__('clock.months.2'),__('clock.months.3'),__('clock.months.4'),__('clock.months.5'),__('clock.months.6'),__('clock.months.7'),__('clock.months.8'),__('clock.months.9'),__('clock.months.10'),__('clock.months.11')];
                function pad(n) { return String(n).padStart(2, '0'); }
                function tick() {
                    const now = new Date();
                    const str = days[now.getDay()] + '. '
                              + pad(now.getDate()) + '-'
                              + months[now.getMonth()] + '-'
                              + now.getFullYear() + ' '
                              + pad(now.getHours()) + ':'
                              + pad(now.getMinutes()) + ':'
                              + pad(now.getSeconds());
                    document.getElementById('live_clock').textContent = str;
                }
                tick();
                setInterval(tick, 1000);
            })();
        </script>
    </header>

    <!-- Language selector -->
    <nav class="lang-selector" aria-label="<?php echo __('header.language'); ?>">
      <?php foreach (getSupportedLangs() as $code): ?>
        <a href="<?php echo langUrl($code); ?>" hreflang="<?php echo $code; ?>" lang="<?php echo $code; ?>" class="lang-option<?php echo getLang() === $code ? ' active' : ''; ?>"<?php echo getLang() === $code ? ' aria-current="true"' : ''; ?>><?php echo strtoupper($code); ?></a>
      <?php endforeach; ?>
    </nav>

    <!-- Fixed burger button -->
    <button class="btn btn-outline-secondary burger-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTools" aria-controls="offcanvasTools" aria-label="<?php echo __('header.tools_info'); ?>" title="<?php echo __('header.tools_info'); ?>">
      <span style="font-size:1.4rem; line-height:1;" aria-hidden="true">&#9776;</span>
    </button>

    <!-- Offcanvas (side panel) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasTools" aria-labelledby="offcanvasToolsLabel" style="background:var(--surface); color:var(--text); max-width:280px; width:85vw;">
      <div class="offcanvas-header" style="border-bottom:1px solid var(--border);">
        <h5 class="offcanvas-title" id="offcanvasToolsLabel"><?php echo __('header.tools_info'); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="<?php echo __('common.close'); ?>"></button>
      </div>
      <div class="offcanvas-body p-0">
        <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#phpinfoModal">
          <i class="ion-ios-medical-outline" aria-hidden="true"></i> <?php echo __('burger.server_info'); ?>
        </button>
        <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#adminerModal">
          <i class="ion-ios-browsers-outline" aria-hidden="true"></i> <?php echo __('burger.adminer'); ?>
        </button>
        <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#journalModal">
          <i class="ion-ios-list-outline" aria-hidden="true"></i> <?php echo __('burger.connection_log'); ?>
          <?php if (count($log_intrusions) > 0): ?>
            <span class="badge bg-danger ms-auto"><?php echo count($log_intrusions); ?></span>
          <?php endif; ?>
        </button>
        <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#teachingModal">
          <i class="ion-ios-circle-outline" aria-hidden="true"></i> <?php echo __('burger.learning_cycle'); ?>
        </button>
        <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#menuPrefsModal">
          <i class="ion-ios-gear-outline" aria-hidden="true"></i> <?php echo __('burger.customize_menu'); ?>
        </button>
        <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#a11yModal">
          <i class="ion-ios-body-outline" aria-hidden="true"></i> <?php echo __('burger.accessibility'); ?>
        </button>
        <div style="border-top:2px solid var(--border); margin-top:.25rem; padding-top:.25rem;">
          <button type="button" class="offcanvas-tools-link" data-bs-toggle="modal" data-bs-target="#readmeModal">
            <i class="ion-ios-bookmarks-outline" aria-hidden="true"></i> <?php echo __('burger.readme'); ?>
          </button>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/Modals.php'; ?>

    <?php include __DIR__ . '/Scripts.php'; ?>

    <hr>
