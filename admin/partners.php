<?php
// XyronNews Admin – Partners Management
$adminPageTitle = 'Manajemen Mitra';
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();
requireRole('admin', 'editor');

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $db->prepare("DELETE FROM partners WHERE id=?")->execute([$id]);
        flashMessage('success', 'Mitra berhasil dihapus.'); header('Location: '.APP_URL.'/admin/partners.php'); exit;
    }
    if ($action === 'toggle' && $id) {
        $db->prepare("UPDATE partners SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        header('Location: '.APP_URL.'/admin/partners.php'); exit;
    }
    if ($action === 'save') {
        $partnerId = (int) ($_POST['partner_id'] ?? 0);
        $name      = sanitize($_POST['name'] ?? '');
        $targetUrl = sanitize($_POST['target_url'] ?? '');
        $menuOrder = (int) ($_POST['menu_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if (!$name) { flashMessage('error', 'Nama Mitra wajib diisi.'); header('Location: '.APP_URL.'/admin/partners.php'); exit; }

        // Image
        $logoImage = $_POST['current_logo'] ?? '';
        if (!empty($_FILES['logo_file']['name'])) {
            $file = $_FILES['logo_file'];
            $allowedExts = ['jpg','jpeg','png','webp','svg'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts) || $file['size'] > 5*1024*1024) {
                flashMessage('error', 'Format atau ukuran logo tidak valid (Maks 5MB, format JPG/PNG/WebP/SVG).'); header('Location: '.APP_URL.'/admin/partners.php'); exit;
            }
            if ($ext === 'svg') {
                $content = file_get_contents($file['tmp_name']);
                if (stripos($content, '<script') !== false || stripos($content, 'javascript:') !== false || stripos($content, 'onload=') !== false) {
                    flashMessage('error', 'File SVG mengandung skrip berbahaya yang tidak diizinkan.'); header('Location: '.APP_URL.'/admin/partners.php'); exit;
                }
            }
            $name_file = 'partner_' . uniqid() . '.' . $ext;
            $dir  = UPLOADS_DIR . 'partners/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            move_uploaded_file($file['tmp_name'], $dir . $name_file);
            $logoImage = UPLOADS_URL . 'partners/' . $name_file;
        }

        if (!$logoImage) { flashMessage('error', 'Logo Mitra wajib diisi.'); header('Location: '.APP_URL.'/admin/partners.php'); exit; }

        if ($partnerId) {
            $db->prepare("UPDATE partners SET name=?,logo_image=?,target_url=?,is_active=?,menu_order=? WHERE id=?")
               ->execute([$name, $logoImage, $targetUrl, $isActive, $menuOrder, $partnerId]);
            flashMessage('success', 'Data Mitra diperbarui.');
        } else {
            $db->prepare("INSERT INTO partners (name,logo_image,target_url,is_active,menu_order) VALUES (?,?,?,?,?)")
               ->execute([$name, $logoImage, $targetUrl, $isActive, $menuOrder]);
            flashMessage('success', 'Mitra ditambahkan.');
        }
        header('Location: '.APP_URL.'/admin/partners.php'); exit;
    }
}

$partners = $db->query("SELECT * FROM partners ORDER BY menu_order ASC, id DESC")->fetchAll();
$editPartner = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM partners WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editPartner = $stmt->fetch();
}
?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start">

  <!-- Partners List -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Daftar Mitra (<?= count($partners) ?>)</span>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Logo</th>
            <th>Nama Mitra</th>
            <th>Urutan</th>
            <th>Aktif</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($partners as $partner): ?>
          <tr>
            <td>
              <?php if ($partner['logo_image']): ?>
              <img src="<?= e($partner['logo_image']) ?>" alt="" style="max-width:80px;max-height:48px;object-fit:contain;border-radius:4px;border:1px solid var(--admin-border);padding:4px;background:#fff">
              <?php else: ?>
              <div style="width:80px;height:48px;background:var(--admin-bg);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:10px;color:var(--admin-text-muted)">No image</div>
              <?php endif; ?>
            </td>
            <td style="font-weight:600"><?= e($partner['name']) ?></td>
            <td><?= (int)$partner['menu_order'] ?></td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $partner['id'] ?>">
                <button type="submit" class="badge <?= $partner['is_active']?'active':'inactive' ?>"
                        style="cursor:pointer;border:none">
                  <?= $partner['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                </button>
              </form>
            </td>
            <td>
              <div style="display:flex;gap:4px">
                <a href="?edit=<?= $partner['id'] ?>" class="action-btn edit">Edit</a>
                <button class="action-btn delete"
                        onclick="confirmDelete(<?= $partner['id'] ?>,'<?= e(addslashes($partner['name'])) ?>','<?= APP_URL ?>/admin/partners.php')">
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
      <span class="admin-card-title"><?= $editPartner ? 'Edit Mitra' : 'Tambah Mitra' ?></span>
      <?php if ($editPartner): ?><a href="<?= APP_URL ?>/admin/partners.php" class="btn btn-outline btn-sm">Batal</a><?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="partner_id" value="<?= $editPartner['id'] ?? '' ?>">
        <input type="hidden" name="current_logo" value="<?= e($editPartner['logo_image'] ?? '') ?>">

        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Nama Mitra <span class="req">*</span></label>
          <input type="text" name="name" class="form-control form-control-sm" required value="<?= e($editPartner['name'] ?? '') ?>" placeholder="contoh: PT Xyron Teknologi">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">URL Website Mitra</label>
          <input type="url" name="target_url" class="form-control form-control-sm" value="<?= e($editPartner['target_url'] ?? '') ?>" placeholder="https://...">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Urutan Tampil (Order)</label>
          <input type="number" name="menu_order" class="form-control form-control-sm" value="<?= e($editPartner['menu_order'] ?? '0') ?>" style="max-width:80px">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Logo Mitra <?= !$editPartner ? '<span class="req">*</span>' : '' ?></label>
          <?php if ($editPartner && $editPartner['logo_image']): ?>
          <img src="<?= e($editPartner['logo_image']) ?>" style="max-width:100%;max-height:80px;border-radius:6px;margin-bottom:8px;display:block;background:#fff;border:1px solid #ddd;padding:5px">
          <?php endif; ?>
          <input type="file" name="logo_file" class="form-control form-control-sm" accept="image/*" <?= !$editPartner ? 'required' : '' ?>>
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="toggle-switch">
            <input type="checkbox" name="is_active" <?= ($editPartner['is_active'] ?? 1) ? 'checked' : '' ?>>
            <div class="toggle-slider"></div>
            <span style="font-size:12px;font-weight:600;color:var(--admin-text-muted)">Aktifkan Mitra</span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%"><?= $editPartner ? 'Simpan' : 'Tambah' ?></button>
      </form>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
