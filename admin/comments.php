<?php
// XyronNews Admin – Comments Moderation
$adminPageTitle = 'Moderasi Komentar';
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

// Handle actions (GET quick approve/delete for convenience)
if (isset($_GET['approve'])) {
    if (($_GET['token'] ?? '') !== csrfToken()) { die('Invalid CSRF token.'); }
    $id = (int) $_GET['approve'];
    $db->prepare("UPDATE comments SET status='approved' WHERE id=?")->execute([$id]);
    flashMessage('success', 'Komentar disetujui.');
    header('Location: ' . APP_URL . '/admin/comments.php'); exit;
}
if (isset($_GET['reject'])) {
    if (($_GET['token'] ?? '') !== csrfToken()) { die('Invalid CSRF token.'); }
    $id = (int) $_GET['reject'];
    $db->prepare("UPDATE comments SET status='pending' WHERE id=?")->execute([$id]);
    flashMessage('success', 'Komentar dikembalikan ke pending.');
    header('Location: ' . APP_URL . '/admin/comments.php'); exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $db->prepare("DELETE FROM comments WHERE id=?")->execute([$id]);
        flashMessage('success', 'Komentar berhasil dihapus.');
        header('Location: ' . APP_URL . '/admin/comments.php'); exit;
    }
    if ($action === 'bulk_approve') {
        $ids = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
        if ($ids) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("UPDATE comments SET status='approved' WHERE id IN ({$ph})")->execute($ids);
            flashMessage('success', count($ids) . ' komentar disetujui.');
        }
        header('Location: ' . APP_URL . '/admin/comments.php'); exit;
    }
    if ($action === 'bulk_delete') {
        $ids = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
        if ($ids) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM comments WHERE id IN ({$ph})")->execute($ids);
            flashMessage('success', count($ids) . ' komentar dihapus.');
        }
        header('Location: ' . APP_URL . '/admin/comments.php'); exit;
    }
}

// Filters
$filterStatus = $_GET['status'] ?? 'all';
$search       = sanitize($_GET['q'] ?? '');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$where = ['1=1'];
$params = [];
if ($filterStatus !== 'all') { $where[] = 'c.status = ?'; $params[] = $filterStatus; }
if ($search) { $where[] = '(c.name LIKE ? OR c.comment LIKE ?)'; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
$whereStr = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM comments c WHERE {$whereStr}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$dataStmt = $db->prepare(
    "SELECT c.*, p.title AS post_title, p.slug AS post_slug
     FROM comments c
     LEFT JOIN posts p ON c.post_id = p.id
     WHERE {$whereStr}
     ORDER BY c.created_at DESC LIMIT ? OFFSET ?"
);
$dataStmt->execute(array_merge($params, [$perPage, $offset]));
$comments = $dataStmt->fetchAll();

$totalPages = (int) ceil($total / $perPage);
?>

<!-- Filter Bar -->
<form method="GET" class="filter-bar admin-card" style="padding:14px 16px;margin-bottom:16px">
  <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Cari komentar atau nama...">
  <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="all" <?= $filterStatus==='all'?'selected':'' ?>>Semua Status</option>
    <option value="pending" <?= $filterStatus==='pending'?'selected':'' ?>>Pending</option>
    <option value="approved" <?= $filterStatus==='approved'?'selected':'' ?>>Approved</option>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="<?= APP_URL ?>/admin/comments.php" class="btn btn-outline btn-sm">Reset</a>
  <span style="margin-left:auto;font-size:12px;color:var(--admin-text-muted)"><?= number_format($total) ?> komentar</span>
</form>

<form method="POST" id="commentsForm">
  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
  <input type="hidden" name="action" value="">

  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Daftar Komentar</span>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('bulk_approve')">Setujui Dipilih</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('bulk_delete')">Hapus Dipilih</button>
      </div>
    </div>

    <?php if (!empty($comments)): ?>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:36px"><input type="checkbox" onchange="this.closest('table').querySelectorAll('input[name=\'selected_ids[]\']').forEach(cb=>cb.checked=this.checked)"></th>
            <th>Nama</th>
            <th>Komentar</th>
            <th>Artikel</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comments as $cmt): ?>
          <tr>
            <td><input type="checkbox" name="selected_ids[]" value="<?= $cmt['id'] ?>"></td>
            <td style="font-weight:600;white-space:nowrap"><?= e($cmt['name']) ?></td>
            <td style="max-width:300px">
              <div style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:12px;color:var(--admin-text-muted)">
                <?= e(mb_strimwidth($cmt['comment'], 0, 120, '...')) ?>
              </div>
            </td>
            <td>
              <a href="<?= APP_URL ?>/berita.php?slug=<?= e($cmt['post_slug'] ?? '') ?>#komentar" target="_blank"
                 style="font-size:11px;color:var(--admin-blue);display:block;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                <?= e(mb_strimwidth($cmt['post_title'] ?? 'N/A', 0, 40, '...')) ?>
              </a>
            </td>
            <td><span class="badge <?= $cmt['status'] ?>"><?= $cmt['status'] ?></span></td>
            <td style="font-size:11px;color:var(--admin-text-muted);white-space:nowrap"><?= timeAgo($cmt['created_at']) ?></td>
            <td>
              <div style="display:flex;gap:4px;flex-wrap:nowrap">
                <?php if ($cmt['status'] === 'pending'): ?>
                <a href="?approve=<?= $cmt['id'] ?>&token=<?= csrfToken() ?>" class="action-btn approve" title="Setujui" style="padding:3px 6px;display:inline-flex;align-items:center;justify-content:center"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></a>
                <?php else: ?>
                <a href="?reject=<?= $cmt['id'] ?>&token=<?= csrfToken() ?>" class="action-btn" title="Pending" style="padding:3px 6px;display:inline-flex;align-items:center;justify-content:center;background:rgba(217,119,6,0.08);color:var(--admin-warning);border:1px solid rgba(217,119,6,0.15)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></a>
                <?php endif; ?>
                <button class="action-btn delete"
                        onclick="confirmDelete(<?= $cmt['id'] ?>, 'komentar dari <?= e(addslashes($cmt['name'])) ?>', '<?= APP_URL ?>/admin/comments.php')">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="padding:14px 16px;border-top:1px solid var(--admin-border);display:flex;align-items:center;gap:8px">
      <span style="font-size:12px;color:var(--admin-text-muted)">Halaman <?= $page ?> dari <?= $totalPages ?></span>
      <div style="margin-left:auto" class="admin-pagination">
        <?php for ($i = max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
        <a href="?status=<?= e($filterStatus) ?>&q=<?= urlencode($search) ?>&page=<?= $i ?>" class="admin-page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon" style="display:flex;justify-content:center;margin-bottom:8px;color:var(--admin-text-muted)">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <h3>Tidak Ada Komentar</h3>
      <p>Belum ada komentar yang sesuai filter.</p>
    </div>
    <?php endif; ?>
  </div>
</form>

<script>
function bulkAction(action) {
  const selected = document.querySelectorAll('input[name="selected_ids[]"]:checked');
  if (!selected.length) { alert('Pilih minimal 1 komentar.'); return; }
  if (action === 'bulk_delete' && !confirm('Hapus ' + selected.length + ' komentar?')) return;
  const form = document.getElementById('commentsForm');
  form.querySelector('input[name="action"]').value = action;
  form.submit();
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
