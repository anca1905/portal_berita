<?php
// XyronNews Admin – Ads Management
$adminPageTitle = 'Manajemen Iklan';
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();
requireRole('admin', 'editor');

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $db->prepare("DELETE FROM ads WHERE id=?")->execute([$id]);
        flashMessage('success', 'Iklan berhasil dihapus.'); header('Location: '.APP_URL.'/admin/ads.php'); exit;
    }
    if ($action === 'toggle' && $id) {
        $db->prepare("UPDATE ads SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        header('Location: '.APP_URL.'/admin/ads.php'); exit;
    }
    if ($action === 'save') {
        $adId      = (int) ($_POST['ad_id'] ?? 0);
        $title     = sanitize($_POST['title'] ?? '');
        $position  = sanitize($_POST['position'] ?? '');
        $targetUrl = sanitize($_POST['target_url'] ?? '');
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if (!$title || !$position) { flashMessage('error', 'Judul dan posisi wajib diisi.'); header('Location: '.APP_URL.'/admin/ads.php'); exit; }

        // Image
        $imageUrl = $_POST['current_image'] ?? '';
        if (!empty($_FILES['image_file']['name'])) {
            $file = $_FILES['image_file'];
            $allowedMimes = ['image/jpeg','image/png','image/webp','image/gif'];
            $allowedExts  = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $detectedMime = @mime_content_type($file['tmp_name']);

            if (!in_array($ext, $allowedExts) || !in_array($detectedMime, $allowedMimes) || $file['size'] > 5*1024*1024) {
                flashMessage('error', 'Format atau ukuran gambar tidak valid (Maks 5MB, format JPG/PNG/WebP/GIF).'); header('Location: '.APP_URL.'/admin/ads.php'); exit;
            }
            $name = 'ad_' . uniqid() . '.' . $ext;
            $dir  = UPLOADS_DIR . 'ads/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            move_uploaded_file($file['tmp_name'], $dir . $name);
            $imageUrl = UPLOADS_URL . 'ads/' . $name;
        }

        if ($adId) {
            $db->prepare("UPDATE ads SET title=?,position=?,image_url=?,target_url=?,is_active=? WHERE id=?")
               ->execute([$title, $position, $imageUrl, $targetUrl, $isActive, $adId]);
            flashMessage('success', 'Iklan diperbarui.');
        } else {
            $db->prepare("INSERT INTO ads (title,position,image_url,target_url,is_active) VALUES (?,?,?,?,?)")
               ->execute([$title, $position, $imageUrl, $targetUrl, $isActive]);
            flashMessage('success', 'Iklan ditambahkan.');
        }
        header('Location: '.APP_URL.'/admin/ads.php'); exit;
    }
}

$ads = $db->query("SELECT * FROM ads ORDER BY id DESC")->fetchAll();
$editAd = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM ads WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editAd = $stmt->fetch();
}

$positions = [
    'header_728x90'     => 'header_728x90 (Header Utama Beranda – 728x90 px)',
    'standard_300x250'  => 'standard_300x250 (Sidebar Kanan Semua Halaman – 300x250 px)',
    'standard_468x100'  => 'standard_468x100 (Bawah Isi Artikel Berita – 468x100 px)',
    'standard_468x60'   => 'standard_468x60 (Tengah Beranda – 468x60 px)',
    'popup_modal'       => 'popup_modal (Modal Popup Saat Halaman Dimuat)',
    'home_left_160x600' => 'home_left_160x600 (Skyscraper Kiri Layar – 160x600 px)',
    'home_left_160x200' => 'home_left_160x200 (Banner Kotak Kiri – 160x200 px)',
];
?>

<!-- BANNER INFO RATE CARD -->
<div style="background:linear-gradient(135deg, #0047CC 0%, #003399 100%);color:#fff;padding:16px 20px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 2px 8px rgba(0,71,204,0.2);">
  <div>
    <strong style="font-size:15px;display:block;margin-bottom:2px;">💰 Monetisasi Mandiri Portal Berita Aktif</strong>
    <span style="font-size:12px;opacity:0.9;">Halaman Rate Card & Slot Banner telah dipasang di beranda dan artikel untuk menarik pengiklan lokal, instansi, & advertorial.</span>
  </div>
  <a href="<?= APP_URL ?>/pasang-iklan.php" target="_blank" class="btn btn-sm" style="background:#fff;color:#0047CC;font-weight:700;border:none;padding:8px 16px;border-radius:6px;text-decoration:none;">
    Lihat Halaman Pasang Iklan →
  </a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start">

  <!-- Ads List -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Daftar Iklan (<?= count($ads) ?>)</span>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Preview</th>
            <th>Judul</th>
            <th>Posisi</th>
            <th>Aktif</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ads as $ad): ?>
          <tr>
            <td>
              <?php if ($ad['image_url']): ?>
              <img src="<?= e($ad['image_url']) ?>" alt="" style="max-width:80px;max-height:48px;object-fit:contain;border-radius:4px;border:1px solid var(--admin-border)">
              <?php else: ?>
              <div style="width:80px;height:48px;background:var(--admin-bg);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:10px;color:var(--admin-text-muted)">No image</div>
              <?php endif; ?>
            </td>
            <td style="font-weight:600"><?= e($ad['title']) ?></td>
            <td>
              <code style="font-size:10px;background:var(--admin-bg);padding:2px 6px;border-radius:4px"><?= e($ad['position']) ?></code>
            </td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                <button type="submit" class="badge <?= $ad['is_active']?'active':'inactive' ?>"
                        style="cursor:pointer;border:none">
                  <?= $ad['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                </button>
              </form>
            </td>
            <td>
              <div style="display:flex;gap:4px">
                <a href="?edit=<?= $ad['id'] ?>" class="action-btn edit">Edit</a>
                <button class="action-btn delete"
                        onclick="confirmDelete(<?= $ad['id'] ?>,'<?= e(addslashes($ad['title'])) ?>','<?= APP_URL ?>/admin/ads.php')">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Edit Form -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><?= $editAd ? 'Edit Iklan' : 'Tambah Iklan' ?></span>
      <?php if ($editAd): ?><a href="<?= APP_URL ?>/admin/ads.php" class="btn btn-outline btn-sm">Batal</a><?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="ad_id" value="<?= $editAd['id'] ?? '' ?>">
        <input type="hidden" name="current_image" value="<?= e($editAd['image_url'] ?? '') ?>">

        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Judul Iklan <span class="req">*</span></label>
          <input type="text" name="title" class="form-control form-control-sm" required value="<?= e($editAd['title'] ?? '') ?>" placeholder="contoh: Iklan 728x90">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Posisi <span class="req">*</span></label>
          <select name="position" class="form-control form-control-sm" required>
            <option value="">-- Pilih Posisi --</option>
            <?php foreach ($positions as $posKey => $posLabel): ?>
            <option value="<?= $posKey ?>" <?= ($editAd['position'] ?? '') === $posKey ? 'selected' : '' ?>><?= $posLabel ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">URL Tujuan</label>
          <input type="url" name="target_url" class="form-control form-control-sm" value="<?= e($editAd['target_url'] ?? '') ?>" placeholder="https://...">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Gambar Iklan</label>
          <?php if ($editAd && $editAd['image_url']): ?>
          <img src="<?= e($editAd['image_url']) ?>" style="max-width:100%;max-height:80px;border-radius:6px;margin-bottom:8px;display:block">
          <?php endif; ?>
          <input type="file" name="image_file" class="form-control form-control-sm" accept="image/*">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="toggle-switch">
            <input type="checkbox" name="is_active" <?= ($editAd['is_active'] ?? 1) ? 'checked' : '' ?>>
            <div class="toggle-slider"></div>
            <span style="font-size:12px;font-weight:600;color:var(--admin-text-muted)">Aktifkan Iklan</span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%"><?= $editAd ? 'Simpan' : 'Tambah' ?></button>
      </form>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
