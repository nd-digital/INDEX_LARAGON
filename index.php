<?php
session_start();

// Localhost-only guard (defense in depth: protects this file even when accessed
// directly, not only through www/index.php). Non-local IPs get a fake 404.
require_once __DIR__ . '/security.php';
require_localhost('INDEX');

if (!empty($_GET['q'])) {
  switch ($_GET['q']) {
    case 'info':
      // Loaded extensions shown as styled pills above the full phpinfo() output.
      $exts = get_loaded_extensions();
      sort($exts, SORT_STRING | SORT_FLAG_CASE);
      echo '<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;max-width:980px;margin:1.5rem auto 0;padding:1rem 1.25rem;background:#16213e;color:#e0e0e0;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.3);">';
      echo '<h2 style="margin:0 0 .8rem;color:#e94560;font-size:1.1rem;">Loaded PHP extensions <span style="color:#88aacc;font-weight:400;">(' . count($exts) . ')</span></h2>';
      echo '<div style="display:flex;flex-wrap:wrap;gap:.4rem;">';
      foreach ($exts as $ext) {
        echo '<span style="background:#0f3460;color:#9fd0ff;padding:.22rem .65rem;border-radius:999px;font-size:.8rem;">' . htmlspecialchars($ext) . '</span>';
      }
      echo '</div></div>';
      phpinfo();
      exit;
  }
}
// i18n
require_once __DIR__ . '/Lang/i18n.php';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Log clearing (CSRF protected)
if (!empty($_POST['clear_log'])) {
  if (empty($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Unauthorized request.');
  }
  $allowed_logs = ['access', 'intrusions'];
  if (in_array($_POST['clear_log'], $allowed_logs)) {
    file_put_contents('./INDEX_LARAGON/LOG/' . $_POST['clear_log'] . '.log', '');
  }
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
  exit;
}

// Read log lines for display in modals
function read_log_lines(string $path): array {
  if (!file_exists($path)) return [];
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  return $lines ?: [];
}
$log_access     = read_log_lines('./INDEX_LARAGON/LOG/access.log');
$log_intrusions = read_log_lines('./INDEX_LARAGON/LOG/intrusions.log');
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>">

<?php
include('./INDEX_LARAGON/Partials/Head.php');
?>

<body>
  <a class="skip-link" href="#main-content"><?php echo __('a11y.skip_to_content'); ?></a>
  <?php
  include_once './INDEX_LARAGON/Menu/Menu_Right.php';
  ?>
  <main id="main-content">
    <?php
    include_once './INDEX_LARAGON/Partials/Header.php';
    ?>
    <!-- BEGIN create project -->
    <div class="Info">
      <?php include_once './INDEX_LARAGON/Partials/Create_Folder.php'; ?>
    </div>
    <!-- END create project -->

    <!-- BEGIN list files -->
    <div class="Info">
      <h2 class="Title_List"><?php echo __('main.title_files'); ?></h2>
      <section class="Border_Box_Simple">
        <?php
        $dirPath     = '.'; // current directory (www/) to list
        $files       = [];
        $directories = [];

        $dir = opendir($dirPath);
        if ($dir === false) {
          echo '<p class="Danger_Zone">' . __('main.dir_error') . '</p>';
        } else {
          while (($element = readdir($dir)) !== false) {
            // Skip dotfiles and the project's own entries
            if ($element[0] === '.' || $element === 'index.php' || $element === 'INDEX_LARAGON' || $element === 'Assets') {
              continue;
            }
            if (is_dir($dirPath . '/' . $element)) {
              $directories[] = mb_strtolower($element);
            } else {
              $files[] = $element;
            }
          }
          closedir($dir);
        }

        // --- Files ---
        if (!empty($files)) {
          sort($files);
          echo '<div class="Border_Box" style="clear:both; text-align:center; font-weight:bold; color:#57d98e; border:1px solid #57d98e;">'
             . __('main.accessible_files', ['count' => count($files)])
             . '</div><ul id="Color_Link">';
          foreach ($files as $entry) {
            echo '<li class="File_Link"><a href="' . $dirPath . '/' . $entry . '">' . $entry . '</a></li>';
          }
          echo '</ul>';
        }

        // --- Directories (grouped by first letter) ---
        if (!empty($directories)) {
          sort($directories, SORT_STRING | SORT_FLAG_CASE);
          echo '<div style="clear:both; text-align:center; font-weight:bold; color:#7fb8ff; border:1px solid #57d98e;">'
             . __('main.web_sites', ['count' => count($directories)])
             . '</div><ul>';
          $currentLetter = '';
          foreach ($directories as $entry) {
            if ($entry[0] !== $currentLetter) {
              $currentLetter = $entry[0];
              echo '<div class="Flag">' . strtoupper($currentLetter) . '</div>';
            }
            echo '<li><a href="' . $dirPath . '/' . $entry . '">' . $entry . '</a></li>';
          }
          echo '</ul>';
        }
        ?>
      </section>
    </div>
  </main>
  <script src='./INDEX_LARAGON/Assets/JQuery/Jquery-3-6-0.js'></script>
  <script src="./INDEX_LARAGON/Assets/Bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- Toggle sidebar (mobile/tablet) ---
    (function() {
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

      toggle.addEventListener('click', function() {
        if (sidebar.classList.contains('sidebar-open')) {
          closeSidebar(true);
        } else {
          openSidebar();
        }
      });

      overlay.addEventListener('click', function() { closeSidebar(false); });

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('sidebar-open')) {
          closeSidebar(true);
        }
      });
    })();
  </script>

</body>

</html>