<?php
// Admin – Manajemen Channel Live TV
$adminPageTitle = 'Live TV Channels';

// Load config BEFORE any output
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdminLogin();

$db     = getDB();
$errors = [];
$success = '';

// ---- CREATE TABLE jika belum ada ----
$db->exec("CREATE TABLE IF NOT EXISTS `tv_channels` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `name`        varchar(100) NOT NULL,
  `slug`        varchar(100) NOT NULL,
  `youtube_id`  varchar(50)  NOT NULL,
  `logo_letter` varchar(5)   NOT NULL DEFAULT 'TV',
  `color`       varchar(10)  NOT NULL DEFAULT '#E8000D',
  `description` varchar(255) DEFAULT NULL,
  `is_active`   tinyint(1)   NOT NULL DEFAULT 1,
  `sort_order`  int(11)      NOT NULL DEFAULT 0,
  `created_at`  datetime     DEFAULT current_timestamp(),
  `updated_at`  datetime     DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$action   = $_GET['action'] ?? 'list';
$editId   = (int) ($_GET['id'] ?? 0);
$editData = null;

// ---- DELETE ----
if ($action === 'delete' && $editId) {
    verifyCsrf();
    $db->prepare("DELETE FROM tv_channels WHERE id = ?")->execute([$editId]);
    flashMessage('success', 'Channel berhasil dihapus.');
    header('Location: ' . APP_URL . '/admin/tv_channels.php'); exit;
}

// ---- TOGGLE ACTIVE ----
if ($action === 'toggle' && $editId) {
    verifyCsrf();
    $db->prepare("UPDATE tv_channels SET is_active = NOT is_active WHERE id = ?")->execute([$editId]);
    header('Location: ' . APP_URL . '/admin/tv_channels.php'); exit;
}

// ---- SAVE (add/edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_channel'])) {
    verifyCsrf();

    $name    = trim($_POST['name'] ?? '');
    $slug    = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $slug    = strtolower(preg_replace('/[^a-z0-9-]/', '', $slug));
    $ytId    = trim($_POST['youtube_id'] ?? '');
    $letter  = strtoupper(substr(trim($_POST['logo_letter'] ?? $name), 0, 3));
    $color   = trim($_POST['color'] ?? '#E8000D');
    $desc    = trim($_POST['description'] ?? '');
    $order   = (int) ($_POST['sort_order'] ?? 0);
    $active  = isset($_POST['is_active']) ? 1 : 0;
    $saveId  = (int) ($_POST['channel_id'] ?? 0);

    // Extract YouTube ID dari URL jika perlu
    if (str_contains($ytId, 'youtube.com') || str_contains($ytId, 'youtu.be')) {
        // Cari ID video (11 karakter)
        if (preg_match('/(?:v=|\/embed\/|youtu\.be\/|\/live\/)([a-zA-Z0-9_-]{11})/', $ytId, $m)) {
            $ytId = $m[1];
        } 
        // Atau cari Channel ID untuk live stream 24 jam
        elseif (preg_match('/channel\/(UC[a-zA-Z0-9_-]{22})/', $ytId, $m)) {
            $ytId = "live_stream?channel=" . $m[1];
        }
    }

    // Jika masih terlalu panjang dan bukan ID yang valid, beri error alih-alih crash
    if (strlen($ytId) > 50) {
        $errors[] = 'URL tidak valid. Pastikan link YouTube yang dimasukkan benar.';
    }

    if (!$name) $errors[] = 'Nama channel wajib diisi.';
    if (!$ytId) $errors[] = 'YouTube ID / URL wajib diisi.';

    // Cek slug unik
    if (!$errors) {
        $chk = $db->prepare("SELECT id FROM tv_channels WHERE slug = ? AND id != ?");
        $chk->execute([$slug, $saveId]);
        if ($chk->fetch()) { $slug .= '-' . time(); }
    }

    if (!$errors) {
        if ($saveId) {
            $db->prepare("UPDATE tv_channels SET name=?, slug=?, youtube_id=?, logo_letter=?, color=?, description=?, is_active=?, sort_order=? WHERE id=?")
               ->execute([$name, $slug, $ytId, $letter, $color, $desc, $active, $order, $saveId]);
            flashMessage('success', 'Channel berhasil diperbarui.');
        } else {
            $db->prepare("INSERT INTO tv_channels (name, slug, youtube_id, logo_letter, color, description, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$name, $slug, $ytId, $letter, $color, $desc, $active, $order]);
            flashMessage('success', 'Channel berhasil ditambahkan.');
        }
        header('Location: ' . APP_URL . '/admin/tv_channels.php'); exit;
    }
}

// ---- LOAD EDIT DATA ----
if ($action === 'edit' && $editId) {
    $editData = $db->prepare("SELECT * FROM tv_channels WHERE id = ?")->execute([$editId])
        ? $db->prepare("SELECT * FROM tv_channels WHERE id = ?") : null;
    $st = $db->prepare("SELECT * FROM tv_channels WHERE id = ?");
    $st->execute([$editId]);
    $editData = $st->fetch();
}

// ---- LIST ----
$channels = $db->query("SELECT * FROM tv_channels ORDER BY sort_order ASC, id ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<style>
.channel-color-dot {
  width: 28px; height: 28px; border-radius: 8px;
  display: inline-flex; align-items: center; justify-content: center;
  color: #fff; font-size: 12px; font-weight: 800; flex-shrink: 0;
}
.yt-link { color: var(--admin-primary); font-size: 12px; display: flex; align-items: center; gap: 4px; }
.badge-active   { background:#d1fae5; color:#065f46; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; }
.badge-inactive { background:#fee2e2; color:#991b1b; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; }
.form-color-row { display: flex; align-items: center; gap: 10px; }
.form-color-row input[type=color] { width:48px; height:36px; padding:2px; border-radius:6px; border:1px solid var(--admin-border); cursor:pointer; }
.preview-badge { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; color:#fff; font-weight:800; font-size:14px; }
</style>

<?php foreach ($errors as $e): ?>
<div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<?php if ($action === 'list'): ?>

<!-- ===== LIST VIEW ===== -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <div>
    <h2 style="font-size:18px;font-weight:700;color:var(--admin-text)">Manajemen Live TV Channel</h2>
    <p style="font-size:12px;color:var(--admin-text-muted);margin-top:4px">Kelola channel YouTube yang ditampilkan di halaman Live TV</p>
  </div>
  <a href="?action=add" class="btn btn-primary" style="display:flex;align-items:center;gap:6px">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Tambah Channel
  </a>
</div>

<div class="admin-card">
  <div class="admin-card-body" style="padding:0">
    <?php if (empty($channels)): ?>
    <div style="text-align:center;padding:48px;color:var(--admin-text-muted)">
      <div style="display:flex;justify-content:center;margin-bottom:12px;color:var(--admin-text-muted)">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="15" x="2" y="7" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>
      </div>
      <p>Belum ada channel. <a href="?action=add">Tambah channel pertama</a></p>
    </div>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr style="background:var(--admin-bg);border-bottom:1px solid var(--admin-border)">
          <th style="padding:12px 16px;text-align:left;font-size:11px;color:var(--admin-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Channel</th>
          <th style="padding:12px 16px;text-align:left;font-size:11px;color:var(--admin-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">YouTube ID</th>
          <th style="padding:12px 16px;text-align:center;font-size:11px;color:var(--admin-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Urutan</th>
          <th style="padding:12px 16px;text-align:center;font-size:11px;color:var(--admin-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Status</th>
          <th style="padding:12px 16px;text-align:right;font-size:11px;color:var(--admin-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($channels as $ch): ?>
        <tr style="border-bottom:1px solid var(--admin-border)">
          <td style="padding:14px 16px">
            <div style="display:flex;align-items:center;gap:12px">
              <div class="channel-color-dot" style="background:<?= htmlspecialchars($ch['color']) ?>"><?= htmlspecialchars($ch['logo_letter']) ?></div>
              <div>
                <div style="font-weight:600;font-size:14px;color:var(--admin-text)"><?= htmlspecialchars($ch['name']) ?></div>
                <div style="font-size:11px;color:var(--admin-text-muted);margin-top:2px"><?= htmlspecialchars($ch['description'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td style="padding:14px 16px">
            <div style="display:flex;flex-direction:column;gap:4px">
              <code style="font-size:12px;background:var(--admin-bg);padding:2px 6px;border-radius:4px"><?= htmlspecialchars($ch['youtube_id']) ?></code>
              <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($ch['youtube_id']) ?>" target="_blank" class="yt-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Preview
              </a>
            </div>
          </td>
          <td style="padding:14px 16px;text-align:center;color:var(--admin-text-muted);font-size:13px"><?= (int)$ch['sort_order'] ?></td>
          <td style="padding:14px 16px;text-align:center">
            <form method="POST" action="?action=toggle&id=<?= $ch['id'] ?>" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
              <button type="submit" style="background:none;border:none;cursor:pointer" title="Toggle aktif/nonaktif">
                <?php if ($ch['is_active']): ?>
                <span class="badge-active">● Aktif</span>
                <?php else: ?>
                <span class="badge-inactive">○ Nonaktif</span>
                <?php endif; ?>
              </button>
            </form>
          </td>
          <td style="padding:14px 16px;text-align:right">
            <div style="display:flex;gap:6px;justify-content:flex-end">
              <a href="?action=edit&id=<?= $ch['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
              <form method="POST" action="?action=delete&id=<?= $ch['id'] ?>" onsubmit="return confirm('Hapus channel ini?')" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<div class="admin-card" style="margin-top:16px">
  <div class="admin-card-body">
    <h3 style="font-size:13px;font-weight:600;margin-bottom:8px">Panduan Mendapatkan YouTube ID / URL Live</h3>
    <ol style="font-size:12px;color:var(--admin-text-muted);padding-left:18px;line-height:1.8">
      <li>Buka channel YouTube TV yang ingin di-embed (misal: <strong>Metro TV</strong>)</li>
      <li>Klik video Live yang sedang tayang / atau video live khusus mereka</li>
      <li>Copy URL dari address bar browser (contoh: <code>https://www.youtube.com/watch?v=<strong>y60wDzZt71A</strong></code>)</li>
      <li>Paste URL atau ID-nya saja ke kolom "YouTube ID / URL" di form bawah</li>
    </ol>
  </div>
</div>

<?php else: ?>

<!-- ===== ADD / EDIT FORM ===== -->
<?php
$isEditMode = ($action === 'edit' && $editData);
$v = fn($key, $default = '') => $editData[$key] ?? ($_POST[$key] ?? $default);
?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
  <a href="?" class="btn btn-outline btn-sm">← Kembali</a>
  <h2 style="font-size:18px;font-weight:700;color:var(--admin-text)">
    <?= $isEditMode ? 'Edit Channel: ' . htmlspecialchars($editData['name']) : 'Tambah Channel Baru' ?>
  </h2>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:flex-start">

  <div class="admin-card">
    <div class="admin-card-header"><span class="admin-card-title">Informasi Channel</span></div>
    <div class="admin-card-body">
      <form method="POST" id="channelForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <input type="hidden" name="save_channel" value="1">
        <?php if ($isEditMode): ?>
        <input type="hidden" name="channel_id" value="<?= (int)$editData['id'] ?>">
        <?php endif; ?>

        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Nama Channel <span class="req">*</span></label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($v('name')) ?>"
                 placeholder="Contoh: METRO TV" oninput="autoFill(this.value)" required>
        </div>

        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Slug (URL Key)</label>
          <input type="text" name="slug" id="chSlug" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($v('slug')) ?>" placeholder="metro-tv">
          <div class="form-hint">Dibuat otomatis dari nama. Harus unik.</div>
        </div>

        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">YouTube ID / URL Live <span class="req">*</span></label>
          <input type="text" name="youtube_id" id="chYtId" class="form-control"
                 value="<?= htmlspecialchars($v('youtube_id')) ?>"
                 placeholder="y60wDzZt71A  atau  https://www.youtube.com/watch?v=..." required>
          <div class="form-hint">Masukkan ID video (11 karakter) atau paste URL YouTube langsung.</div>
        </div>

        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Deskripsi</label>
          <input type="text" name="description" class="form-control"
                 value="<?= htmlspecialchars($v('description')) ?>"
                 placeholder="Berita terkini dan terpercaya...">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
          <div class="form-group">
            <label class="form-label">Huruf Logo (maks. 3)</label>
            <input type="text" name="logo_letter" id="chLetter" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($v('logo_letter', 'TV')) ?>" maxlength="3"
                   placeholder="M" style="text-transform:uppercase">
          </div>
          <div class="form-group">
            <label class="form-label">Urutan Tampil</label>
            <input type="number" name="sort_order" class="form-control form-control-sm"
                   value="<?= (int)$v('sort_order', 0) ?>" min="0">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Warna Logo</label>
          <div class="form-color-row">
            <input type="color" name="color" id="chColor"
                   value="<?= htmlspecialchars($v('color', '#E8000D')) ?>"
                   oninput="updatePreview()">
            <input type="text" id="chColorText" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($v('color', '#E8000D')) ?>"
                   style="width:120px" oninput="syncColorPicker(this.value)">
            <span>Preview:</span>
            <div class="preview-badge" id="previewBadge"
                 style="background:<?= htmlspecialchars($v('color', '#E8000D')) ?>">
              <span id="previewLetter"><?= htmlspecialchars($v('logo_letter', 'TV')) ?></span>
            </div>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:20px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="is_active" value="1"
                   <?= $v('is_active', 1) ? 'checked' : '' ?> style="width:16px;height:16px">
            <span class="form-label" style="margin:0">Aktifkan channel (tampil di Live TV)</span>
          </label>
        </div>

        <div style="display:flex;gap:8px">
          <button type="submit" class="btn btn-primary">
            <?= $isEditMode ? 'Simpan Perubahan' : 'Tambah Channel' ?>
          </button>
          <a href="?" class="btn btn-outline">Batal</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Preview Panel -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="admin-card">
      <div class="admin-card-header"><span class="admin-card-title">Preview</span></div>
      <div class="admin-card-body" style="background:#0a0a0f;border-radius:0 0 12px 12px">
        <div style="display:flex;align-items:center;gap:10px;padding:10px;background:rgba(255,255,255,0.05);border-radius:8px;margin-bottom:12px">
          <div id="previewBadge2" class="preview-badge" style="background:<?= htmlspecialchars($v('color', '#E8000D')) ?>">
            <span id="previewLetter2"><?= htmlspecialchars($v('logo_letter', 'TV')) ?></span>
          </div>
          <div>
            <div id="previewName" style="font-size:13px;font-weight:700;color:#fff"><?= htmlspecialchars($v('name', 'Nama Channel')) ?></div>
            <div style="font-size:10px;color:rgba(255,255,255,0.4)">● LIVE</div>
          </div>
        </div>
        <?php if ($v('youtube_id')): ?>
        <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden">
          <iframe id="ytPreview"
            src="https://www.youtube.com/embed/<?= htmlspecialchars($v('youtube_id')) ?>?rel=0&controls=1"
            style="position:absolute;top:0;left:0;width:100%;height:100%;border:none"
            allowfullscreen loading="lazy"></iframe>
        </div>
        <div style="text-align:center;margin-top:8px">
          <button type="button" onclick="refreshYTPreview()" class="btn btn-outline btn-sm" style="font-size:11px;color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.15)">Refresh Preview</button>
        </div>
        <?php else: ?>
        <div id="ytPreviewPlaceholder" style="position:relative;overflow:hidden;background:rgba(255,255,255,0.05);border-radius:8px;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:12px">
          Preview muncul setelah isi YouTube ID
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-body">
        <p style="font-size:12px;color:var(--admin-text-muted);line-height:1.6">
          <strong>Tips:</strong><br>
          Gunakan ID video yang <strong>sedang live</strong> atau ID live stream permanen dari channel TV tersebut.
          Beberapa channel TV punya stream 24 jam yang ID-nya tidak berubah.
        </p>
      </div>
    </div>
  </div>

</div>

<script>
// Auto-fill slug dan huruf logo dari nama
function autoFill(val) {
  const slugEl = document.getElementById('chSlug');
  if (!slugEl.dataset.manual) {
    slugEl.value = val.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/[\s]+/g,'-').replace(/-+/g,'-').trim();
  }
  // Auto letter
  const letterEl = document.getElementById('chLetter');
  if (!letterEl.dataset.manual) {
    const words = val.trim().split(/\s+/);
    letterEl.value = words.map(w => w[0] || '').join('').toUpperCase().slice(0,3);
    updatePreview();
  }
  document.getElementById('previewName').textContent = val || 'Nama Channel';
}

document.getElementById('chSlug')?.addEventListener('input', function() { this.dataset.manual = 'true'; });
document.getElementById('chLetter')?.addEventListener('input', function() { this.dataset.manual = 'true'; updatePreview(); });

// Color preview sync
function updatePreview() {
  const color  = document.getElementById('chColor').value;
  const letter = document.getElementById('chLetter').value.toUpperCase();
  document.getElementById('chColorText').value = color;
  ['previewBadge','previewBadge2'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.background = color;
  });
  ['previewLetter','previewLetter2'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = letter;
  });
}

function syncColorPicker(val) {
  if (/^#[0-9a-f]{6}$/i.test(val)) {
    document.getElementById('chColor').value = val;
    updatePreview();
  }
}

// YouTube preview refresh
function refreshYTPreview() {
  const ytId = document.getElementById('chYtId')?.value.trim();
  if (!ytId) return;
  // Extract ID dari URL jika perlu
  let id = ytId;
  const m = ytId.match(/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
  if (m) id = m[1];

  const container = document.querySelector('[id=ytPreviewPlaceholder]') || document.querySelector('#ytPreview')?.parentElement;
  if (container) {
    container.innerHTML = `<iframe src="https://www.youtube.com/embed/${id}?rel=0&controls=1"
      style="position:absolute;top:0;left:0;width:100%;height:100%;border:none"
      allowfullscreen loading="lazy"></iframe>`;
  }
}

document.getElementById('chYtId')?.addEventListener('blur', refreshYTPreview);
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
