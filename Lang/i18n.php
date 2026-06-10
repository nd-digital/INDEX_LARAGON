<?php
/**
 * i18n — Internationalization helper
 * Supports: en, fr, es, it
 */

defined('I18N_LOADED') || define('I18N_LOADED', true);

$GLOBALS['__i18n_supported'] = ['en', 'fr', 'es', 'it'];

/**
 * Detect and set the current language.
 * Priority: $_GET['lang'] > $_COOKIE['lang'] > 'en'
 */
function _i18n_detect_lang(): string {
    $supported = $GLOBALS['__i18n_supported'];

    // Switch via query string
    if (!empty($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
        $lang = $_GET['lang'];
        setcookie('lang', $lang, time() + 86400 * 30, '/');
        return $lang;
    }

    // Persist via cookie
    if (!empty($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported, true)) {
        return $_COOKIE['lang'];
    }

    return 'en';
}

/**
 * Load translations for the given language.
 * Falls back to en.json for missing keys.
 */
function _i18n_load(string $lang): array {
    static $cache = [];
    if (isset($cache[$lang])) return $cache[$lang];

    $dir = __DIR__;
    $translations = [];

    // Load English as base (fallback)
    $en_file = $dir . '/en.json';
    if (file_exists($en_file)) {
        $translations = json_decode(file_get_contents($en_file), true) ?: [];
    }

    // Overlay target language
    if ($lang !== 'en') {
        $lang_file = $dir . '/' . $lang . '.json';
        if (file_exists($lang_file)) {
            $lang_data = json_decode(file_get_contents($lang_file), true) ?: [];
            $translations = array_merge($translations, $lang_data);
        }
    }

    $cache[$lang] = $translations;
    return $translations;
}

// Initialize
$GLOBALS['__i18n_lang'] = _i18n_detect_lang();
$GLOBALS['__i18n_data'] = _i18n_load($GLOBALS['__i18n_lang']);

/**
 * Translate a key. Supports {placeholder} interpolation.
 * Usage: __('header.title') or __('folder.created', ['name' => 'mysite'])
 */
function __(string $key, array $params = []): string {
    $text = $GLOBALS['__i18n_data'][$key] ?? $key;
    if (!empty($params)) {
        foreach ($params as $k => $v) {
            $text = str_replace('{' . $k . '}', $v, $text);
        }
    }
    return $text;
}

/**
 * Get current language code.
 */
function getLang(): string {
    return $GLOBALS['__i18n_lang'];
}

/**
 * Get all translations (for JS bridge).
 */
function getTranslations(): array {
    return $GLOBALS['__i18n_data'];
}

/**
 * Get supported languages list.
 */
function getSupportedLangs(): array {
    return $GLOBALS['__i18n_supported'];
}

/**
 * Build a URL that switches to the given language.
 */
function langUrl(string $code): string {
    $params = $_GET;
    $params['lang'] = $code;
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    return $path . '?' . http_build_query($params);
}
