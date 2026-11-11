<?php
// ============================================================
// XyronNews – Environment Configuration
// Salin .env.example menjadi .env dan sesuaikan nilainya
// ============================================================

// Load .env file jika tersedia
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'xnn');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// App URL (Otomatis mendeteksi domain / subfolder atau membaca dari .env)
if (!defined('APP_URL')) {
    $envAppUrl = getenv('APP_URL');
    if (!empty($envAppUrl)) {
        define('APP_URL', rtrim($envAppUrl, '/'));
    } else {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $scheme = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if (str_ends_with($scriptDir, '/admin')) {
            $scriptDir = substr($scriptDir, 0, -6);
        }
        $baseDir = rtrim($scriptDir, '/');
        define('APP_URL', $scheme . $host . $baseDir);
    }
}
define('MAINTENANCE_MODE', filter_var(getenv('MAINTENANCE_MODE') ?: false, FILTER_VALIDATE_BOOLEAN));
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('UPLOADS_URL', APP_URL . '/uploads/');

// Session
define('SESSION_NAME', 'xyronnews_session');
define('ADMIN_SESSION_KEY', 'xnn_admin_user');

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Pagination
define('POSTS_PER_PAGE', 12);
define('ADMIN_POSTS_PER_PAGE', 20);

// Default image jika artikel tidak punya foto
define('DEFAULT_POST_IMAGE', APP_URL . '/assets/default-post.jpg');
