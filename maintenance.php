<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Jika maintenance mode sudah dimatikan, arahkan kembali ke beranda
if (!defined('MAINTENANCE_MODE') || !MAINTENANCE_MODE) {
    header('Location: ' . APP_URL);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Dalam Perbaikan - <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="icon" href="<?= getLogoUrl() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .maintenance-container {
            max-width: 600px;
            padding: 40px 20px;
        }
        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 16px;
            color: #1e293b;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            color: #64748b;
            margin-bottom: 32px;
        }
        .btn-admin {
            display: inline-block;
            background-color: #0047cc;
            color: #fff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-admin:hover {
            background-color: #0036a3;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>

<div class="maintenance-container">
    <?php if (!empty($__globalSettings['site_logo'])): ?>
        <img src="<?= getLogoUrl() ?>" alt="Logo" style="max-height: 100px; margin-bottom: 20px;">
    <?php else: ?>
        <div class="maintenance-icon" style="display:flex;justify-content:center;color:#0047cc;">
            <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
    <?php endif; ?>
    <h1>Kami Sedang Memperbarui Sistem</h1>
    <p>Mohon maaf atas ketidaknyamanan ini. <strong><?= htmlspecialchars(APP_NAME) ?></strong> sedang dalam proses perbaikan dan peningkatan layanan. Kami akan segera kembali beroperasi secepatnya!</p>
    
    <a href="<?= APP_URL ?>/admin/login.php" class="btn-admin">Login Administrator</a>
</div>

</body>
</html>
