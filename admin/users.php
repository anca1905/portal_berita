<?php
// XyronNews Admin – Users Management
$adminPageTitle = 'Manajemen Pengguna';
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();
requireRole('admin');

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $currentAdmin = getCurrentAdmin();
        if ($id === (int) $currentAdmin['id']) {
            flashMessage('error', 'Tidak bisa menghapus akun Anda sendiri.');
        } else {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            flashMessage('success', 'Pengguna berhasil dihapus.');
        }
        header('Location: '.APP_URL.'/admin/users.php'); exit;
    }

    if ($action === 'save') {
        $userId   = (int) ($_POST['user_id'] ?? 0);
        $username = sanitize($_POST['username'] ?? '');
        $name     = sanitize($_POST['name'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['admin','editor','reporter']) ? $_POST['role'] : 'reporter';
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (strlen($name) < 2) $errors[] = 'Nama minimal 2 karakter.';
        if (!$userId && strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
        if ($password && strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';

        // Check username uniqueness
        if (!$errors) {
            $uniqStmt = $db->prepare("SELECT id FROM users WHERE username=? AND id!=?");
            $uniqStmt->execute([$username, $userId]);
            if ($uniqStmt->fetch()) $errors[] = 'Username sudah digunakan.';
        }

        if ($errors) {
            flashMessage('error', implode(' | ', $errors));
            header('Location: '.APP_URL.'/admin/users.php?edit='.$userId); exit;
        }

        if ($userId) {
            $sql = "UPDATE users SET username=?, full_name=?, email=?, role=?" . ($password ? ", password=?" : "") . " WHERE id=?";
            $params = [$username, $name, $email, $role];
            if ($password) $params[] = password_hash($password, PASSWORD_BCRYPT);
            $params[] = $userId;
        } else {
            $sql = "INSERT INTO users (username, full_name, email, role, password) VALUES (?,?,?,?,?)";
            $params = [$username, $name, $email, $role, password_hash($password, PASSWORD_BCRYPT)];
        }
        $db->prepare($sql)->execute($params);
        flashMessage('success', $userId ? 'Pengguna diperbarui.' : 'Pengguna ditambahkan.');
        header('Location: '.APP_URL.'/admin/users.php'); exit;
    }
}

$users = $db->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editUser = $stmt->fetch();
}
?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:flex-start">

  <!-- Users Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Daftar Pengguna (<?= count($users) ?>)</span>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama / Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td style="font-size:12px;color:var(--admin-text-muted)"><?= $user['id'] ?></td>
            <td>
              <div style="font-weight:600"><?= e($user['full_name']) ?></div>
              <div style="font-size:11px;color:var(--admin-text-muted)">@<?= e($user['username']) ?></div>
            </td>
            <td style="font-size:12px;color:var(--admin-text-muted)"><?= e($user['email'] ?? '-') ?></td>
            <td><span class="badge <?= $user['role'] ?>"><?= e($user['role']) ?></span></td>
            <td>
              <div style="display:flex;gap:4px">
                <a href="?edit=<?= $user['id'] ?>" class="action-btn edit">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>
                <?php if ($user['id'] !== (int) getCurrentAdmin()['id']): ?>
                <button class="action-btn delete"
                        onclick="confirmDelete(<?= $user['id'] ?>,'<?= e(addslashes($user['full_name'])) ?>','<?= APP_URL ?>/admin/users.php')">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Edit User Form -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title"><?= $editUser ? 'Edit Pengguna' : 'Tambah Pengguna' ?></span>
      <?php if ($editUser): ?><a href="<?= APP_URL ?>/admin/users.php" class="btn btn-outline btn-sm">Batal</a><?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="user_id" value="<?= $editUser['id'] ?? '' ?>">

        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Nama Lengkap <span class="req">*</span></label>
          <input type="text" name="name" class="form-control form-control-sm" required value="<?= e($editUser['full_name'] ?? '') ?>" placeholder="Nama Lengkap">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Username <span class="req">*</span></label>
          <input type="text" name="username" class="form-control form-control-sm" required value="<?= e($editUser['username'] ?? '') ?>" placeholder="username_unik">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control form-control-sm" value="<?= e($editUser['email'] ?? '') ?>" placeholder="email@contoh.com">
        </div>
        <div class="form-group" style="margin-bottom:12px">
          <label class="form-label">Role <span class="req">*</span></label>
          <select name="role" class="form-control form-control-sm" required>
            <option value="reporter" <?= ($editUser['role']??'reporter')==='reporter'?'selected':'' ?>>Reporter</option>
            <option value="editor" <?= ($editUser['role']??'')==='editor'?'selected':'' ?>>Editor</option>
            <option value="admin" <?= ($editUser['role']??'')==='admin'?'selected':'' ?>>Admin</option>
          </select>
          <div class="form-hint">Admin: akses penuh. Editor: kelola semua konten. Reporter: hanya artikel sendiri.</div>
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Password <?= $editUser ? '(kosongkan jika tidak diubah)' : '<span class="req">*</span>' ?></label>
          <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimal 6 karakter" <?= $editUser ? '' : 'required' ?>>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%"><?= $editUser ? 'Simpan Perubahan' : 'Tambah Pengguna' ?></button>
      </form>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
