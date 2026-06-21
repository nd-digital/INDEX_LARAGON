<?php
/**
 * JS Bridge — Outputs translation data as JavaScript.
 * Loaded via <script src="./INDEX_LARAGON/Lang/js_translations.php"></script>
 */
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-cache');

require_once __DIR__ . '/i18n.php';

$lang = getLang();
$all  = getTranslations();

// Only send keys needed by JavaScript (clock, common, log, folder confirms, etc.)
$js_prefixes = ['clock.', 'common.', 'log.', 'main.', 'folder.', 'api.', 'burger.', 'header.', 'learning.', 'sidebar.', 'menuedit.', 'phonefake.'];

$js_data = [];
foreach ($all as $key => $value) {
    foreach ($js_prefixes as $prefix) {
        if (strpos($key, $prefix) === 0) {
            $js_data[$key] = $value;
            break;
        }
    }
}

$json = json_encode($js_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);

echo "window.__i18n={lang:" . json_encode($lang) . ",t:{$json}};\n";
echo <<<'JS'
window.__=function(key,params){
    var text=window.__i18n.t[key]||key;
    if(params){
        for(var k in params){
            if(params.hasOwnProperty(k)){
                text=text.split('{'+k+'}').join(params[k]);
            }
        }
    }
    return text;
};
JS;
