<?php
// Cache-busting based on the file's modification time (filemtime):
// the URL changes ONLY when the CSS file changes -> instant refresh while
// developing, but normal browser caching otherwise (no flash on POST).
if (!function_exists('asset_v')) {
    function asset_v(string $relPath): int {
        $fs = __DIR__ . '/../' . ltrim($relPath, '/');
        return is_file($fs) ? (int) filemtime($fs) : 0;
    }
}
?>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="./INDEX_LARAGON/Assets/Picture/Favicons/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="./INDEX_LARAGON/Assets/Picture/Favicons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="./INDEX_LARAGON/Assets/Picture/Favicons/favicon-16x16.png">
  <link href="https://fonts.googleapis.com/css?family=Karla:400" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="./INDEX_LARAGON/Assets/Bootstrap/css/bootstrap.css?v=<?php echo asset_v('Assets/Bootstrap/css/bootstrap.css'); ?>">
  <link rel="stylesheet" href="./INDEX_LARAGON/Assets/Css/Index.css?v=<?php echo asset_v('Assets/Css/Index.css'); ?>">
  <link rel="stylesheet" href="./INDEX_LARAGON/Assets/Css/Menu.css?v=<?php echo asset_v('Assets/Css/Menu.css'); ?>">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link href="https://fonts.googleapis.com/css?family=Lato:400,300,700" rel="stylesheet" type="text/css">
  <link rel="manifest" href="./INDEX_LARAGON/Assets/Picture/Favicons/site.webmanifest">
  <meta name="theme-color" content="#212529">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="./INDEX_LARAGON/Assets/Picture/Favicons/apple-touch-icon.png">
  <title>Laragon by Nicolas-Degabriel.digital</title>
  <script src="./INDEX_LARAGON/Lang/js_translations.php"></script>
</head>