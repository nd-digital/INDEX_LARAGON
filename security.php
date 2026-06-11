<?php
/**
 * Centralized security guard for INDEX_LARAGON
 * Provides IP checking, access logging, and localhost enforcement.
 */

define('SECURITY_LOG_DIR', __DIR__ . '/LOG');

// Consistent timezone across every entry point (logs, live clock, etc.).
date_default_timezone_set('Europe/Paris');

// Never expose PHP errors (and absolute server paths) to the browser; log them
// server-side instead. Scoped to the dashboard (this file is only loaded here).
ini_set('display_errors', '0');
ini_set('log_errors', '1');

function get_client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function is_localhost(): bool {
    return in_array(get_client_ip(), ['127.0.0.1', '::1']);
}

/**
 * Log an access attempt to the appropriate log file.
 *
 * @param string $type   Access type tag: INDEX, ADMINER
 * @param string $status 'OK' for authorized, 'KO' for blocked/failed
 * @param array  $extra  Additional key-value pairs (URI, Agent, Detail...)
 */
function log_access(string $type, string $status = 'OK', array $extra = []): void {
    $ip   = get_client_ip();
    $line = date('Y-m-d H:i:s') . " - [{$type}] {$status} - IP: {$ip}";

    foreach ($extra as $key => $value) {
        $line .= " - {$key}: {$value}";
    }

    $line .= PHP_EOL;

    $file = ($status === 'OK')
        ? SECURITY_LOG_DIR . '/access.log'
        : SECURITY_LOG_DIR . '/intrusions.log';

    file_put_contents($file, $line, FILE_APPEND);
}

/**
 * Enforce localhost-only access. If the client is not localhost:
 * - Logs a KO entry with URI and User-Agent
 * - Returns a fake Apache 404 page
 * - Exits immediately
 *
 * If the client IS localhost, logs an OK entry and returns normally.
 *
 * @param string $type Access type tag for logging
 */
function require_localhost(string $type): void {
    if (!is_localhost()) {
        log_access($type, 'KO', [
            'URI'   => $_SERVER['REQUEST_URI']     ?? '/',
            'Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        header('HTTP/1.1 404 Not Found');
        include __DIR__ . '/Assets/Includes/_False_404.php';
        exit;
    }
    log_access($type, 'OK');
}
