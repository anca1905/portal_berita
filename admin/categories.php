<?php
// XyronNews Admin – Categories CRUD
$adminPageTitle = 'Manajemen Kategori';
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

// Restrict to admin/editor
requireRole('admin', 'editor');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id    = (int) ($_POST['id'] ?? 0);
        $name  = sanitize($_POST['name'] ?? '');
        $slug  = sanitize($_POST['slug'] ?? '') ?: slugify($name);
        $order = (int) ($_POST['menu_order'] ?? 0);

        if (strlen($name) < 2) { flashMessage('error', 'Nama kategori minimal 2 karakter.'); }
        else {
            if ($id) {
                $db->prepare("UPDATE categories SET name=?, slug=?, menu_order=? WHERE id=?")->execute([$name, $slug, $order, $id]);
                flashMessage('success', 'Kategori berhasil diperbarui.');
            } else {
                $db->prepare("INSERT INTO categories (name, slug, menu_order) VALUES (?,?,?)")->execute([$name, $slug, $order]);
                flashMessage('success', 'Kategori berhasil ditambahkan.');
            }
        }
        header('Location: ' . APP_URL . '/admin/categories.php'); exit;
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        // Check if category has posts
        $count = (int) $db->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?")->execute([$id]) ? 0 : 0;
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = (int) $stmt->fetchColumn();
        if ($count > 0) {
            flashMessage('error', "Kategori tidak bisa dihapus karena memiliki {$count} artikel. Pindahkan artikel terlebih dahulu.");
        } else {
            $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            flashMessage('success', 'Kategori berhasil dihapus.');
        }
        header('Location: ' . APP_URL . '/admin/categories.php'); exit;
    }
}

$categories = $db->query(
    "SELECT c.*, COUNT(p.id) AS post_count
     FROM categories c
     LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
     GROUP BY c.id
     ORDER BY c.menu_order ASC"
)->fetchAll();

// Get category for editing
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $editCat = $stmt->fetch();
}
?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start">

  <!-- Categories Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Daftar Kategori (<?= count($categories) ?>)</span>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Slug</th>
            <th>Urutan Menu</th>
            <th>Artikel</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
          <tr>
            <td style="font-size:12px;color:var(--admin-text-muted)"><?= $cat['id'] ?></td>
            <td style="font-weight:600"><?= e($cat['name']) ?></td>
            <td>
              <code style="font-size:11px;background:var(--admin-bg);padding:2px 6px;border-radius:4px"><?= e($cat['slug']) ?></code>
            </td>
            <td style="text-align:center"><?= $cat['menu_order'] ?></td>
            <td>
              <a href="<?= APP_URL ?>/kategori.php?slug=<?= e($cat['slug']) ?>" target="_blank"
                 style="font-size:12px;color:var(--admin-blue);font-weight:600">
                <?= $cat['post_count'] ?> artikel
              </a>
            </td>
            <td>
              <div style="display:flex;gap:6px">
                <a href="?edit=<?= $cat['id'] ?>" class="action-btn edit">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>
                <button class="action-btn delete"
                        onclick="confirmDelete(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', '<?= APP_URL ?>/admin/categories.php')">
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

  <!-- Add / Edit Form -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><?= $editCat ? 'Edit Kategori' : 'Tambah Kategori' ?></span>
      <?php if ($editCat): ?><a href="<?= APP_URL ?>/admin/categories.php" class="btn btn-outline btn-sm">Batal</a><?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $editCat['id'] ?? '' ?>">

        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Nama Kategori <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" required
                 value="<?= e($editCat['name'] ?? '') ?>"
                 placeholder="contoh: Teknologi"
                 oninput="autoSlugCat(this.value)">
        </div>

        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Slug (URL)</label>
          <input type="text" name="slug" id="catSlug" class="form-control form-control-sm"
                 value="<?= e($editCat['slug'] ?? '') ?>"
                 placeholder="auto-generated">
          <div class="form-hint">Contoh: teknologi → /kategori.php?slug=teknologi</div>
        </div>

        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Urutan Menu</label>
          <input type="number" name="menu_order" class="form-control form-control-sm"
                 value="<?= $editCat['menu_order'] ?? 0 ?>" min="0" max="100">
          <div class="form-hint">Angka lebih kecil = muncul lebih dulu di menu.</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
          <?= $editCat ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
        </button>
      </form>
    </div>
  </div>

</div>

<script>
let catSlugManual = false;
document.getElementById('catSlug')?.addEventListener('input', () => catSlugManual = true);
function autoSlugCat(name) {
  if (!catSlugManual) {
    const slug = name.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').trim();
    document.getElementById('catSlug').value = slug;
  }
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
