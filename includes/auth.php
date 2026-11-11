<?php
// ============================================================
// XyronNews – Authentication Helper
// ============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function getCurrentAdmin(): ?array {
    return $_SESSION[ADMIN_SESSION_KEY] ?? null;
}

function loginAdmin(string $username, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user) return false;
    if (!password_verify($password, $user['password'])) return false;
    // Upgrade hash if needed
    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user['id']]);
    }
    session_regenerate_id(true);
    $_SESSION[ADMIN_SESSION_KEY] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'name'     => $user['full_name'],
        'role'     => $user['role'],
        'email'    => $user['email'] ?? '',
    ];
    // Reset login attempts on success
    unset($_SESSION['login_attempts'], $_SESSION['login_lockout']);
    return true;
}

function logoutAdmin(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path']);
    }
    session_destroy();
}

function hasRole(string ...$roles): bool {
    $admin = getCurrentAdmin();
    return $admin && in_array($admin['role'], $roles, true);
}

function requireRole(string ...$roles): void {
    requireAdminLogin();
    if (!hasRole(...$roles)) {
        http_response_code(403);
        die('<h1>403 – Akses Ditolak</h1><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>');
    }
}
