<?php
// XyronNews Admin – Login
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/');
    exit;
}

$error = '';

// Check lockout
$lockoutTime = $_SESSION['login_lockout'] ?? 0;
if ($lockoutTime > time()) {
    $remainingMinutes = ceil(($lockoutTime - time()) / 60);
    $error = "Terlalu banyak percobaan gagal. Akun dikunci sementara. Silakan coba lagi dalam {$remainingMinutes} menit.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lockoutTime <= time()) {
    verifyCsrf();
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi.';
    } elseif (loginAdmin($username, $password)) {
        $redirect = sanitize($_GET['redirect'] ?? APP_URL . '/admin/');
        // Prevent open redirect
        if (!str_starts_with($redirect, APP_URL) && !str_starts_with($redirect, '/')) {
            $redirect = APP_URL . '/admin/';
        }
        header('Location: ' . $redirect);
        exit;
    } else {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_lockout'] = time() + (15 * 60); // 15 mins lockout
            $error = 'Terlalu banyak percobaan gagal (5x). Silakan coba lagi dalam 15 menit.';
        } else {
            $remaining = 5 - $_SESSION['login_attempts'];
            $error = "Username atau password salah. Sisa percobaan: {$remaining} kali.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Login Admin – <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;min-height:100vh;background:linear-gradient(135deg,#0D1117 0%,#1a2744 50%,#0D1117 100%);display:flex;align-items:center;justify-content:center;padding:20px}
    .login-card{background:#fff;border-radius:16px;width:100%;max-width:400px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.4)}
    .login-header{background:linear-gradient(135deg,#0047CC,#003399);padding:32px 28px;text-align:center}
    .login-logo{width:52px;height:52px;background:rgba(255,255,255,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:900;color:#fff;margin:0 auto 12px}
    .login-title{font-size:20px;font-weight:800;color:#fff;margin-bottom:4px}
    .login-sub{font-size:12px;color:rgba(255,255,255,0.7)}
    .login-body{padding:28px}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:12px;font-weight:600;color:#6B7280;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px}
    .form-control{width:100%;padding:11px 14px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:14px;color:#111827;outline:none;transition:all .2s;font-family:'Inter',sans-serif}
    .form-control:focus{border-color:#0047CC;box-shadow:0 0 0 3px rgba(0,71,204,0.1)}
    .btn-login{width:100%;padding:12px;background:#0047CC;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;transition:background .2s;font-family:'Inter',sans-serif;margin-top:4px}
    .btn-login:hover{background:#003399}
    .btn-login:active{transform:scale(0.98)}
    .error-box{background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
    .login-footer{text-align:center;font-size:12px;color:#9CA3AF;margin-top:16px;padding-top:16px;border-top:1px solid #F3F4F6}
    .login-footer a{color:#0047CC;font-weight:600}
    .input-wrap{position:relative}
    .toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#9CA3AF;background:none;border:none;padding:2px}
    .toggle-pw:hover{color:#6B7280}
  </style>
</head>
<body>
<div class="login-card">
  <div class="login-header">
    <div class="login-logo">X</div>
    <div class="login-title"><?= APP_NAME ?> Admin</div>
    <div class="login-sub">Masuk ke panel administrasi</div>
  </div>
  <div class="login-body">
    <?php if ($error): ?>
    <div class="error-box">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" name="username" id="username" class="form-control"
               placeholder="Masukkan username" autocomplete="username" required
               value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="password" class="form-control"
                 placeholder="Masukkan password" autocomplete="current-password" required>
          <button type="button" class="toggle-pw" onclick="togglePw()" title="Tampilkan/sembunyikan password">
            <svg id="pwEye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-login">Masuk ke Dashboard →</button>
    </form>

    <div class="login-footer">
      <a href="<?= APP_URL ?>/">← Kembali ke Website</a>
    </div>
  </div>
</div>

<script>
function togglePw() {
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
