<?php
// XyronNews Admin – Posts Management
$adminPageTitle = 'Manajemen Artikel';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
        flashMessage('success', 'Artikel berhasil dihapus.');
        header('Location: ' . APP_URL . '/admin/posts.php'); exit;
    }
    if ($action === 'change_status' && $id) {
        $newStatus = in_array($_POST['status'], ['draft','pending','published']) ? $_POST['status'] : 'draft';
        $publishedAt = $newStatus === 'published' ? 'NOW()' : 'published_at';
        $db->prepare("UPDATE posts SET status = ?, published_at = IF(status != 'published', NOW(), published_at) WHERE id = ?")->execute([$newStatus, $id]);
        flashMessage('success', 'Status artikel diperbarui.');
        header('Location: ' . APP_URL . '/admin/posts.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); exit;
    }
    if ($action === 'bulk_delete') {
        $ids = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM posts WHERE id IN ({$placeholders})")->execute($ids);
            flashMessage('success', count($ids) . ' artikel berhasil dihapus.');
        }
        header('Location: ' . APP_URL . '/admin/posts.php'); exit;
    }
}

// Filters
$filterStatus = $_GET['status'] ?? '';
$filterCat    = (int) ($_GET['category'] ?? 0);
$search       = sanitize($_GET['q'] ?? '');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$perPage      = ADMIN_POSTS_PER_PAGE;
$offset       = ($page - 1) * $perPage;

$where = ['1=1'];
$params = [];
if ($filterStatus && in_array($filterStatus, ['draft','pending','published'])) {
    $where[] = 'p.status = ?'; $params[] = $filterStatus;
}
if ($filterCat) { $where[] = 'p.category_id = ?'; $params[] = $filterCat; }
if ($search) { $where[] = 'p.title LIKE ?'; $params[] = '%' . $search . '%'; }

$whereStr = implode(' AND ', $where);

// Count query
$countStmt = $db->prepare("SELECT COUNT(*) FROM posts p WHERE {$whereStr}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

// Data query
$dataParams = array_merge($params, [$perPage, $offset]);
$posts = $db->prepare(
    "SELECT p.*, c.name AS category_name, u.full_name AS author_name
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.author_id = u.id
     WHERE {$whereStr}
     ORDER BY p.id DESC LIMIT ? OFFSET ?"
);
$posts->execute($dataParams);
$posts = $posts->fetchAll();

$categories = getCategories();
$totalPages = (int) ceil($total / $perPage);

// URL builder
function buildUrl(array $params = []): string {
    $base = APP_URL . '/admin/posts.php';
    $q = array_merge(['status' => $_GET['status'] ?? '', 'category' => $_GET['category'] ?? '', 'q' => $_GET['q'] ?? ''], $params);
    $q = array_filter($q);
    return $base . ($q ? '?' . http_build_query($q) : '');
}
?>

<!-- Filter Bar -->
<form method="GET" class="filter-bar admin-card" style="padding:14px 16px;margin-bottom:16px">
  <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Cari judul...">
  <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">Semua Status</option>
    <option value="published" <?= $filterStatus==='published'?'selected':'' ?>>Published</option>
    <option value="pending" <?= $filterStatus==='pending'?'selected':'' ?>>Pending</option>
    <option value="draft" <?= $filterStatus==='draft'?'selected':'' ?>>Draft</option>
  </select>
  <select name="category" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">Semua Kategori</option>
    <?php foreach ($categories as $cat): ?>
    <option value="<?= $cat['id'] ?>" <?= $filterCat==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="<?= APP_URL ?>/admin/posts.php" class="btn btn-outline btn-sm">Reset</a>
  <span style="margin-left:auto;font-size:12px;color:var(--admin-text-muted)"><?= number_format($total) ?> artikel</span>
</form>

<form method="POST" id="postsForm">
  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
  <input type="hidden" name="action" value="">

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Daftar Artikel</span>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus Dipilih
        </button>
        <a href="<?= APP_URL ?>/admin/posts_form.php" class="btn btn-primary btn-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Artikel
        </a>
      </div>
    </div>

    <?php if (!empty($posts)): ?>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:36px">
              <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
            </th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Views</th>
            <th>Tanggal</th>
            <th style="width:150px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
          <tr>
            <td><input type="checkbox" name="selected_ids[]" value="<?= $post['id'] ?>"></td>
            <td>
              <a href="<?= APP_URL ?>/admin/posts_form.php?id=<?= $post['id'] ?>"
                 style="font-weight:600;color:var(--admin-blue);display:block;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                 title="<?= e($post['title']) ?>">
                <?= e(mb_strimwidth($post['title'], 0, 60, '...')) ?>
              </a>
              <div style="font-size:11px;color:var(--admin-text-muted);margin-top:2px">
                <?= e($post['author_name'] ?? 'Redaksi') ?> · ID: <?= $post['id'] ?>
              </div>
            </td>
            <td><span style="font-size:12px;color:var(--admin-text-muted)"><?= e($post['category_name'] ?? '-') ?></span></td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="change_status">
                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                <select name="status" class="form-control form-control-sm" style="min-width:110px;font-size:11px" onchange="this.form.submit()">
                  <option value="draft" <?= $post['status']==='draft'?'selected':'' ?>>Draft</option>
                  <option value="pending" <?= $post['status']==='pending'?'selected':'' ?>>Pending</option>
                  <option value="published" <?= $post['status']==='published'?'selected':'' ?>>Published</option>
                </select>
              </form>
            </td>
            <td style="font-size:12px;font-weight:600"><?= number_format($post['views']) ?></td>
            <td style="font-size:11px;color:var(--admin-text-muted);white-space:nowrap">
              <?= $post['published_at'] ? formatDate($post['published_at'], 'd M Y') : '-' ?>
            </td>
            <td>
              <div style="display:flex;gap:4px;flex-wrap:nowrap">
                <a href="<?= APP_URL ?>/admin/posts_form.php?id=<?= $post['id'] ?>" class="action-btn edit">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>
                <?php if ($post['status'] === 'published'): ?>
                <a href="<?= APP_URL ?>/berita.php?slug=<?= e($post['slug']) ?>" target="_blank" class="action-btn view">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
                <?php endif; ?>
                <button type="button" class="action-btn delete"
                        onclick="confirmDelete(<?= $post['id'] ?>, '<?= e(addslashes(mb_strimwidth($post['title'],0,40,'...'))) ?>', '<?= APP_URL ?>/admin/posts.php')">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="padding:14px 16px;border-top:1px solid var(--admin-border);display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <span style="font-size:12px;color:var(--admin-text-muted)">Halaman <?= $page ?> dari <?= $totalPages ?></span>
      <div style="margin-left:auto" class="admin-pagination">
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
        <a href="<?= buildUrl(['page' => $i]) ?>" class="admin-page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon" style="display:flex;justify-content:center;margin-bottom:8px;color:var(--admin-text-muted)">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="9" y1="14" x2="15" y2="14"></line></svg>
      </div>
      <h3>Tidak Ada Artikel</h3>
      <p>Belum ada artikel yang sesuai dengan filter Anda.</p>
    </div>
    <?php endif; ?>
  </div>
</form>

<script>
function toggleAll(el) {
  document.querySelectorAll('input[name="selected_ids[]"]').forEach(cb => cb.checked = el.checked);
}
function bulkDelete() {
  const selected = document.querySelectorAll('input[name="selected_ids[]"]:checked');
  if (!selected.length) { alert('Pilih minimal 1 artikel.'); return; }
  if (!confirm('Hapus ' + selected.length + ' artikel yang dipilih?')) return;
  const form = document.getElementById('postsForm');
  form.querySelector('input[name="action"]').value = 'bulk_delete';
  form.submit();
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
