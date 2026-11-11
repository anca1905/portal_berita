<?php
// ============================================================
// XyronNews – Database Connection (PDO)
// ============================================================

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error secara aman tanpa expose credential
            error_log('DB Connection failed: ' . $e->getMessage());
            http_response_code(503);
            die('<div style="font:16px sans-serif;text-align:center;padding:50px;color:#c0392b">
                <div style="display:flex;justify-content:center;margin-bottom:12px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h2 style="margin:0 0 8px 0">Koneksi Database Gagal</h2>
                <p style="color:#555">Pastikan server database berjalan dan konfigurasi di <code>includes/config.php</code> sudah benar.</p>
            </div>');
        }
    }
    return $pdo;
}
