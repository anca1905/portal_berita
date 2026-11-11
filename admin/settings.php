<?php
$adminPageTitle = 'Pengaturan Situs';

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();

// Cek hak akses
if ($admin['role'] !== 'admin') {
    die('Anda tidak memiliki akses ke halaman ini.');
}

$db = getDB();

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $siteName = sanitize($_POST['site_name'] ?? '');
    $siteTagline = sanitize($_POST['site_tagline'] ?? '');
    $themePrimary = sanitize($_POST['theme_primary'] ?? '');
    $themeSecondary = sanitize($_POST['theme_secondary'] ?? '');
    
    // Update text settings
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute(['site_name', $siteName, $siteName]);
    $stmt->execute(['site_tagline', $siteTagline, $siteTagline]);
    if ($themePrimary) {
        $stmt->execute(['theme_primary', $themePrimary, $themePrimary]);
    }
    if ($themeSecondary) {
        $stmt->execute(['theme_secondary', $themeSecondary, $themeSecondary]);
    }
    
    $adWhatsapp = sanitize($_POST['ad_whatsapp'] ?? '');
    $adEmail = sanitize($_POST['ad_email'] ?? '');
    $adPriceAdvertorial = sanitize($_POST['ad_price_advertorial'] ?? '');
    $adPriceHeader = sanitize($_POST['ad_price_header'] ?? '');
    $adPriceSidebar = sanitize($_POST['ad_price_sidebar'] ?? '');

    if ($adWhatsapp) $stmt->execute(['ad_whatsapp', $adWhatsapp, $adWhatsapp]);
    if ($adEmail) $stmt->execute(['ad_email', $adEmail, $adEmail]);
    if ($adPriceAdvertorial) $stmt->execute(['ad_price_advertorial', $adPriceAdvertorial, $adPriceAdvertorial]);
    if ($adPriceHeader) $stmt->execute(['ad_price_header', $adPriceHeader, $adPriceHeader]);
    if ($adPriceSidebar) $stmt->execute(['ad_price_sidebar', $adPriceSidebar, $adPriceSidebar]);
    
    // Handle logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $uploadDir = UPLOADS_DIR . 'logo/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = 'logo_' . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', basename($_FILES['site_logo']['name']));
        $targetPath = $uploadDir . $fileName;
        
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $targetPath)) {
                $oldLogo = $__globalSettings['site_logo'] ?? '';
                if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                    unlink($uploadDir . $oldLogo);
                }
                $stmt->execute(['site_logo', $fileName, $fileName]);
            } else {
                flashMessage('error', 'Gagal mengunggah logo.');
            }
        } else {
            flashMessage('error', 'Format file logo tidak didukung.');
        }
    }
    
    flashMessage('success', 'Pengaturan situs berhasil diperbarui!');
    // Refresh halaman agar mengambil setting terbaru
    header('Location: ' . APP_URL . '/admin/settings.php');
    exit;
}

require_once __DIR__ . '/includes/admin_header.php';
$currentSettings = getSettings();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Pengaturan Situs</h1>
</div>



<div class="card">
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            
            <div class="mb-4">
                <label for="site_name" class="form-label">Nama Website</label>
                <input type="text" class="form-control" id="site_name" name="site_name" value="<?= e($currentSettings['site_name']) ?>" required>
            </div>
            
            <div class="mb-4">
                <label for="site_tagline" class="form-label">Tagline Website</label>
                <input type="text" class="form-control" id="site_tagline" name="site_tagline" value="<?= e($currentSettings['site_tagline']) ?>" required>
            </div>
            
            <div class="mb-4">
                <label for="site_logo" class="form-label">Logo Website</label>
                <?php if (!empty($currentSettings['site_logo'])): ?>
                    <div class="mb-2">
                        <img src="<?= UPLOADS_URL . 'logo/' . e($currentSettings['site_logo']) ?>" alt="Logo Saat Ini" style="max-height: 80px; border: 1px solid #ccc; padding: 5px; border-radius: 5px;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                <div class="form-text">Biarkan kosong jika tidak ingin mengubah logo. Logo ini juga akan otomatis digunakan sebagai Favicon.</div>
            </div>

            <div class="row mb-4" style="display:flex;gap:16px;">
                <div class="col" style="flex:1;">
                    <label for="theme_primary" class="form-label">Warna Tema Utama (Primary)</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" id="theme_primary_picker" value="<?= e($currentSettings['theme_primary'] ?? '#0047CC') ?>" onchange="document.getElementById('theme_primary').value = this.value" style="width:40px;height:38px;padding:2px;border:1px solid #ccc;border-radius:4px;cursor:pointer;">
                        <input type="text" class="form-control" id="theme_primary" name="theme_primary" value="<?= e($currentSettings['theme_primary'] ?? '#0047CC') ?>" onchange="document.getElementById('theme_primary_picker').value = this.value">
                    </div>
                </div>
                <div class="col" style="flex:1;">
                    <label for="theme_secondary" class="form-label">Warna Aksen Merah (Secondary)</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" id="theme_secondary_picker" value="<?= e($currentSettings['theme_secondary'] ?? '#E8000D') ?>" onchange="document.getElementById('theme_secondary').value = this.value" style="width:40px;height:38px;padding:2px;border:1px solid #ccc;border-radius:4px;cursor:pointer;">
                        <input type="text" class="form-control" id="theme_secondary" name="theme_secondary" value="<?= e($currentSettings['theme_secondary'] ?? '#E8000D') ?>" onchange="document.getElementById('theme_secondary_picker').value = this.value">
                    </div>
                </div>
            </div>
            
            <hr style="margin:24px 0;border:0;border-top:1px solid #e5e7eb;">
            <h4 style="font-size:16px;font-weight:700;margin-bottom:14px;color:#111827;">Pengaturan Iklan & Kemitraan (Rate Card)</h4>

            <div class="row mb-4" style="display:flex;gap:16px;">
                <div class="col" style="flex:1;">
                    <label for="ad_whatsapp" class="form-label">Nomor WhatsApp Pemasangan Iklan</label>
                    <input type="text" class="form-control" id="ad_whatsapp" name="ad_whatsapp" value="<?= e($currentSettings['ad_whatsapp'] ?? '081234567890') ?>" placeholder="08xxxxxxxxxx">
                    <div class="form-text">Digunakan saat calon klien klik tombol order/konsultasi via WhatsApp.</div>
                </div>
                <div class="col" style="flex:1;">
                    <label for="ad_email" class="form-label">Email Khusus Iklan</label>
                    <input type="email" class="form-control" id="ad_email" name="ad_email" value="<?= e($currentSettings['ad_email'] ?? 'iklan@xyronnews.com') ?>" placeholder="iklan@xyronnews.com">
                </div>
            </div>

            <div class="row mb-4" style="display:flex;gap:16px;">
                <div class="col" style="flex:1;">
                    <label for="ad_price_advertorial" class="form-label">Tarif Advertorial / Siaran Pers (Rp)</label>
                    <input type="text" class="form-control" id="ad_price_advertorial" name="ad_price_advertorial" value="<?= e($currentSettings['ad_price_advertorial'] ?? '250.000') ?>" placeholder="250.000">
                </div>
                <div class="col" style="flex:1;">
                    <label for="ad_price_header" class="form-label">Tarif Banner Header 728x90 (Rp/bln)</label>
                    <input type="text" class="form-control" id="ad_price_header" name="ad_price_header" value="<?= e($currentSettings['ad_price_header'] ?? '500.000') ?>" placeholder="500.000">
                </div>
                <div class="col" style="flex:1;">
                    <label for="ad_price_sidebar" class="form-label">Tarif Banner Sidebar 300x250 (Rp/bln)</label>
                    <input type="text" class="form-control" id="ad_price_sidebar" name="ad_price_sidebar" value="<?= e($currentSettings['ad_price_sidebar'] ?? '300.000') ?>" placeholder="300.000">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
