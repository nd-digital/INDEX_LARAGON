<!-- BEGIN Form to create site structure -->
        <div id="Create_Folder" class="Border_Box create-folder-row">
          <?php
            // Sanitize the requested folder name (defense against path traversal):
            // basename() strips any directory component, then we whitelist safe chars.
            $rawname      = isset($_POST['foldername']) ? trim($_POST['foldername']) : '';
            $foldername   = $rawname !== '' ? basename($rawname) : '';
            $folder_valid = $foldername !== ''
                          && $foldername !== '.' && $foldername !== '..'
                          && preg_match('/^[A-Za-z0-9 _.-]+$/', $foldername);
            $filename     = $folder_valid ? $foldername : null;
            $path         = __DIR__ . "/../..";        // = www/ (Partials/ -> INDEX_LARAGON/ -> www/)
            $fullPath     = $path . "/" . $filename;
          ?>
          <div class="create-folder-label"><?php echo __('folder.instruction'); ?></div>
          <form class="create-folder-form" onsubmit="return confirm(__('folder.confirm_create', {name: document.getElementById('foldername').value}));" action="./index.php#Create_Folder" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" name="foldername" id="foldername" value="" required="" placeholder="<?php echo __('folder.placeholder'); ?>">
            <div class="form-check form-switch" title="<?php echo __('folder.subfolder_tooltip'); ?>">
              <label class="form-check-label" for="SubFolderCreation" title="<?php echo __('folder.subfolder_tooltip'); ?>"><?php echo __('folder.enable_subfolder'); ?></label>
              <input class="form-check-input" type="checkbox" id="SubFolderCreation" name="SubFolderCreation" value="1" checked>
            </div>
            <input class="btn btn-success" type="submit" value="<?php echo __('common.submit'); ?>">
            <input class="btn btn-danger" type="reset" value="<?php echo __('common.reset'); ?>">
          </form>
        </div>
        <?php
        // --- Project creation (only on POST submit) ---
        // Result is buffered so it can be shown in a modal instead of inline.
        ob_start();
        if ($rawname !== '') {

            if (!$folder_valid) {
                // Invalid name (failed sanitization / whitelist)
                echo "<p class='Danger_Zone'>" . __('folder.invalid_name') . "</p><hr>";

            } elseif (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token'])
                      || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // CSRF protection
                http_response_code(403);
                echo "<p class='Danger_Zone'>" . __('api.invalid_csrf') . "</p>";

            } else {
                // Defense in depth: the resolved target must stay inside www/
                $www_real    = realpath($path);
                $parent_real = realpath(dirname($fullPath));
                if ($www_real === false || $parent_real !== $www_real) {
                    http_response_code(400);
                    echo "<p class='Danger_Zone'>" . __('folder.invalid_name') . "</p>";

                } else {
                    $createSubFolders = !empty($_POST['SubFolderCreation']);
                    $safeName         = htmlspecialchars($filename, ENT_QUOTES);

                    if (file_exists($fullPath)) {
                        echo "<p class='Danger_Zone'>" . __('folder.already_exists', ['name' => $safeName]) . " —
                              <a href='" . $safeName . "/index.php'>" . __('folder.access_project') . "</a></p>";
                        echo '<hr>';

                    } else {
                        mkdir($fullPath, 0777, true);

                        echo '<div class="Border_Box" style="border-color:limegreen; color:limegreen;">
                                ' . __('folder.created', ['name' => $safeName]) . ' —
                                <a href="' . $safeName . '/index.php" style="color:cyan;">' . $safeName . '/index.php</a>
                              </div>';
                        echo '<hr>';

                        // --- Subfolders (only if checkbox is checked) ---
                        if ($createSubFolders) {
                            $dirs = ['/Assets/Css', '/Assets/Js', '/Assets/Img/Svg', '/Assets/Fonts', '/Includes', '/Uploads'];
                            foreach ($dirs as $dir) {
                                mkdir($fullPath . $dir, 0777, true);
                            }

                            // --- .gitignore ---
                            $gitignore = "# Dependencies
node_modules/
vendor/

# Environment
.env
.env.local

# User uploads
Uploads/

# IDE
.vscode/
.idea/
*.swp
.DS_Store
Thumbs.db
";
                            file_put_contents($fullPath . '/.gitignore', $gitignore);

                            // --- Includes/header.php ---
                            $headerContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./Assets/Css/style.css">
    <title>' . $safeName . '</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">' . $safeName . '</a>
        </div>
    </nav>
    <div class="container mt-4">
';
                            file_put_contents($fullPath . '/Includes/header.php', $headerContent);

                            // --- Includes/footer.php ---
                            $footerContent = '    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; <?php echo date("Y"); ?> ' . $safeName . '</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./Assets/Js/main.js"></script>
</body>
</html>
';
                            file_put_contents($fullPath . '/Includes/footer.php', $footerContent);

                            // --- index.php with includes ---
                            $indexContent = '<?php include_once "./Includes/header.php"; ?>

    <h1>Welcome to ' . $safeName . '</h1>
    <p class="text-muted">Project created via INDEX_LARAGON</p>

<?php include_once "./Includes/footer.php"; ?>
';
                            file_put_contents($fullPath . '/index.php', $indexContent);

                            // --- Starter CSS / JS files ---
                            file_put_contents($fullPath . '/Assets/Css/style.css', '/* ' . $filename . " — Styles */\n");
                            file_put_contents($fullPath . '/Assets/Js/main.js',    '// ' . $filename . " — Scripts\n");

                            echo '<div style="color:cyan; margin-top:5px;">
                                    ' . __('folder.structure_created') . '
                                  </div>';

                        } else {
                            // --- Simple index.php (without subfolders) ---
                            $indexContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>' . $safeName . '</title>
</head>
<body>
    <div class="container mt-4">
        <h1>' . $safeName . '</h1>
        <p class="text-muted">Project created via INDEX_LARAGON</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>';
                            file_put_contents($fullPath . '/index.php', $indexContent);
                        }
                    }
                }
            }
        }
        $createResult = trim(ob_get_clean());
        ?>
        <?php if ($createResult !== ''): ?>
        <!-- Project creation result: shown in a modal; the page refreshes when it closes -->
        <div class="modal fade" id="createResultModal" tabindex="-1" aria-labelledby="createResultLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="createResultLabel"><?php echo __('folder.result_title'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body"><?php echo $createResult; ?></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal"><?php echo __('common.close'); ?></button>
              </div>
            </div>
          </div>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('createResultModal');
            // Refresh the index (clean GET) on close so the new folder appears and no re-POST occurs.
            el.addEventListener('hidden.bs.modal', function () { window.location.href = './index.php'; });
            new bootstrap.Modal(el).show();
          });
        </script>
        <?php endif; ?>
<!-- END Form to create site structure -->
