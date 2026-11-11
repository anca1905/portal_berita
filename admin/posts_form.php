<?php
// XyronNews Admin – Create / Edit Post
$isEdit = isset($_GET['id']);
$postId = (int) ($_GET['id'] ?? 0);
$adminPageTitle = $isEdit ? 'Edit Artikel' : 'Tambah Artikel Baru';

// Load config & functions BEFORE any output
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdminLogin();

$db = getDB();

// AUTO-FIX: Ubah kolom 'content' menjadi LONGTEXT agar bisa menampung gambar base64 dari Summernote
try {
    $db->exec("ALTER TABLE posts MODIFY content LONGTEXT");
} catch (Exception $e) {
    // Abaikan jika sudah LONGTEXT atau tabel belum ada
}

$categories = getCategories();
$errors = [];
$post = null;

// Load existing post for edit
if ($isEdit && $postId) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if (!$post) { flashMessage('error', 'Artikel tidak ditemukan.'); header('Location: ' . APP_URL . '/admin/posts.php'); exit; }
}

// Handle form submission — BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
    verifyCsrf();

    $title      = sanitize($_POST['title'] ?? '');
    $slug       = sanitize($_POST['slug'] ?? '') ?: slugify($title);
    $summary    = sanitize($_POST['summary'] ?? '');
    $content    = $_POST['content'] ?? '';  // HTML from TinyMCE (not sanitized to preserve formatting)
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $tags       = sanitize($_POST['tags'] ?? '');
    $status     = in_array($_POST['status'] ?? '', ['draft','pending','published']) ? $_POST['status'] : 'draft';
    $publishedAt= $status === 'published' ? ($_POST['published_at'] ?: date('Y-m-d H:i:s')) : ($post['published_at'] ?? null);

    // Validate
    if (strlen($title) < 5) $errors[] = 'Judul minimal 5 karakter.';
    if (!$categoryId) $errors[] = 'Pilih kategori.';
    if (strlen($content) < 20) $errors[] = 'Konten terlalu singkat.';

    // Slug uniqueness
    if (!$errors) {
        $existStmt = $db->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
        $existStmt->execute([$slug, $postId]);
        if ($existStmt->fetch()) {
            $slug .= '-' . time();
        }
    }

    // Image upload
    $imagePath = $post['image'] ?? '';
    if (!empty($_FILES['image']['name']) && !$errors) {
        $file = $_FILES['image'];
        $allowedMimes = ['image/jpeg','image/png','image/webp','image/gif'];
        $allowedExts  = ['jpg','jpeg','png','webp','gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $detectedMime = @mime_content_type($file['tmp_name']);
        
        if ($file['error'] === UPLOAD_ERR_INI_SIZE) {
            $errors[] = 'Ukuran file terlalu besar melebihi batas konfigurasi server (php.ini).';
        } elseif (!in_array($ext, $allowedExts) || !in_array($detectedMime, $allowedMimes)) {
            $errors[] = 'File yang diunggah bukan gambar valid (hanya JPG, PNG, WebP, GIF).';
        } else {
            $newName = 'post_' . uniqid() . '.' . $ext;
            $destDir = UPLOADS_DIR . 'posts/';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            
            $destPath = $destDir . $newName;
            
            // Auto compress jika file > 5MB dan format didukung
            if ($file['size'] > 5 * 1024 * 1024 && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $img = null;
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $img = @imagecreatefromjpeg($file['tmp_name']);
                } elseif ($ext === 'png') {
                    $img = @imagecreatefrompng($file['tmp_name']);
                } elseif ($ext === 'webp') {
                    $img = @imagecreatefromwebp($file['tmp_name']);
                }
                
                if ($img) {
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $maxWidth = 1920; // Resize untuk optimasi
                    
                    if ($width > $maxWidth) {
                        $newHeight = (int) ($height * ($maxWidth / $width));
                        $newImg = imagecreatetruecolor($maxWidth, $newHeight);
                        
                        if ($ext === 'png' || $ext === 'webp') {
                            imagealphablending($newImg, false);
                            imagesavealpha($newImg, true);
                            $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
                            imagefilledrectangle($newImg, 0, 0, $maxWidth, $newHeight, $transparent);
                        }
                        
                        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                        imagedestroy($img);
                        $img = $newImg;
                    }
                    
                    $success = false;
                    if ($ext === 'jpg' || $ext === 'jpeg') {
                        $success = imagejpeg($img, $destPath, 75);
                    } elseif ($ext === 'png') {
                        $success = imagepng($img, $destPath, 8); 
                    } elseif ($ext === 'webp') {
                        $success = imagewebp($img, $destPath, 75);
                    }
                    
                    imagedestroy($img);
                    
                    if ($success) {
                        $imagePath = $newName;
                    } else {
                        // Fallback jika kompresi fungsi gagal
                        if (move_uploaded_file($file['tmp_name'], $destPath)) {
                            $imagePath = $newName;
                        } else {
                            $errors[] = 'Gagal menyimpan gambar.';
                        }
                    }
                } else {
                    // Fallback jika gagal dibaca GD
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $imagePath = $newName;
                    } else {
                        $errors[] = 'Gagal menyimpan gambar.';
                    }
                }
            } else {
                // File di bawah 5MB atau GIF (langsung pindahkan)
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $imagePath = $newName;
                } else {
                    $errors[] = 'Gagal mengupload gambar.';
                }
            }
        }
    }

    if (!$errors) {
        $admin = getCurrentAdmin();
        if ($isEdit) {
            $db->prepare(
                "UPDATE posts SET title=?, slug=?, content=?, summary=?, category_id=?,
                 tags=?, status=?, image=?, editor_id=?, published_at=? WHERE id=?"
            )->execute([$title, $slug, $content, $summary, $categoryId, $tags, $status, $imagePath, $admin['id'], $publishedAt, $postId]);
            flashMessage('success', 'Artikel berhasil diperbarui.');
            header('Location: ' . APP_URL . '/admin/posts_form.php?id=' . $postId); exit;
        } else {
            $db->prepare(
                "INSERT INTO posts (title, slug, content, summary, category_id, tags, author_id, status, image, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            )->execute([$title, $slug, $content, $summary, $categoryId, $tags, $admin['id'], $status, $imagePath, $publishedAt]);
            $newId = $db->lastInsertId();
            flashMessage('success', 'Artikel berhasil disimpan.');
            header('Location: ' . APP_URL . '/admin/posts_form.php?id=' . $newId); exit;
        }
    }
}

// Prefill values (from POST or existing post)
$v = fn(string $key, $default = '') => $_POST[$key] ?? $post[$key] ?? $default;

// NOW load the header (which outputs HTML)
require_once __DIR__ . '/includes/admin_header.php';
?>

<?php foreach ($errors as $err): ?>
<div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="POST" enctype="multipart/form-data" id="postForm">
  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
  <input type="hidden" name="save_post" value="1">

  <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:flex-start">

    <!-- Left Column -->
    <div style="display:flex;flex-direction:column;gap:16px">

      <!-- Title -->
      <div class="admin-card">
        <div class="admin-card-body">
          <div class="form-group">
            <label class="form-label">Judul Artikel <span class="req">*</span></label>
            <input type="text" name="title" id="postTitle" class="form-control"
                   value="<?= e($v('title')) ?>"
                   placeholder="Masukkan judul artikel yang menarik..."
                   style="font-size:16px;font-weight:600;padding:12px 14px"
                   oninput="autoSlug(this.value)" required>
          </div>
          <div class="form-group" style="margin-top:10px">
            <label class="form-label">Slug (URL) <span class="req">*</span></label>
            <div style="display:flex;gap:8px;align-items:center">
              <span style="font-size:12px;color:var(--admin-text-muted);white-space:nowrap"><?= APP_URL ?>/berita.php?slug=</span>
              <input type="text" name="slug" id="postSlug" class="form-control form-control-sm"
                     value="<?= e($v('slug')) ?>" placeholder="auto-generated">
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="admin-card">
        <div class="admin-card-header"><span class="admin-card-title">Konten Artikel <span class="req">*</span></span></div>
        <div class="admin-card-body">
          <textarea id="postContent" name="content" class="form-control"
                    style="min-height:400px;font-family:monospace"><?= htmlspecialchars($v('content')) ?></textarea>
        </div>
      </div>

      <!-- Summary -->
      <div class="admin-card">
        <div class="admin-card-body">
          <div class="form-group">
            <label class="form-label">Ringkasan / Excerpt</label>
            <textarea name="summary" class="form-control" rows="3" placeholder="Ringkasan singkat artikel (opsional, maks. 300 karakter)..."><?= e($v('summary')) ?></textarea>
            <div class="form-hint">Jika dikosongkan, ringkasan diambil otomatis dari konten.</div>
          </div>
        </div>
      </div>

    </div><!-- end left col -->

    <!-- Right Column (Sidebar) -->
    <div style="display:flex;flex-direction:column;gap:16px">

      <!-- Publish Settings -->
      <div class="admin-card">
        <div class="admin-card-header"><span class="admin-card-title">Publikasi</span></div>
        <div class="admin-card-body" style="display:flex;flex-direction:column;gap:12px">
          <div class="form-group">
            <label class="form-label">Status <span class="req">*</span></label>
            <select name="status" class="form-control">
              <option value="draft" <?= $v('status','draft')==='draft'?'selected':'' ?>>Draft</option>
              <option value="pending" <?= $v('status')==='pending'?'selected':'' ?>>Pending Review</option>
              <option value="published" <?= $v('status')==='published'?'selected':'' ?>>Published</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Publish</label>
            <input type="datetime-local" name="published_at" class="form-control form-control-sm"
                   value="<?= e($post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i')) ?>">
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary" style="flex:1">
              <?= $isEdit ? 'Simpan Perubahan' : 'Buat Artikel' ?>
            </button>
            <?php if ($isEdit && $post['status'] === 'published'): ?>
            <a href="<?= APP_URL ?>/berita.php?slug=<?= e($post['slug']) ?>" target="_blank"
               class="btn btn-outline btn-sm">Preview</a>
            <?php endif; ?>
          </div>
          <a href="<?= APP_URL ?>/admin/posts.php" class="btn btn-outline btn-sm" style="text-align:center">← Kembali</a>
        </div>
      </div>

      <!-- Category -->
      <div class="admin-card">
        <div class="admin-card-header"><span class="admin-card-title">Kategori <span class="req">*</span></span></div>
        <div class="admin-card-body">
          <select name="category_id" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $v('category_id') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Featured Image -->
      <div class="admin-card">
        <div class="admin-card-header"><span class="admin-card-title">Gambar Featured</span></div>
        <div class="admin-card-body">
          <div class="img-preview-box" id="imgPreviewBox" onclick="document.getElementById('imageInput').click()">
            <?php $currentImg = $v('image', '') ?: ($post['image'] ?? ''); ?>
            <?php if ($currentImg): ?>
            <img src="<?= e(postImage($currentImg)) ?>" alt="Preview" id="imgPreview">
            <?php else: ?>
            <div class="img-preview-placeholder" id="imgPlaceholder">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <div>Klik untuk upload gambar</div>
              <div style="font-size:10px;margin-top:4px">JPG, PNG, WebP · >5MB akan dikompresi otomatis</div>
            </div>
            <?php endif; ?>
          </div>
          <input type="file" name="image" id="imageInput" accept="image/*" style="display:none" onchange="previewImage(this)">
          <?php if ($currentImg): ?>
          <div style="font-size:11px;color:var(--admin-text-muted);margin-top:6px;text-align:center">
            Klik gambar untuk ganti foto
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tags -->
      <div class="admin-card">
        <div class="admin-card-header"><span class="admin-card-title">Tags</span></div>
        <div class="admin-card-body">
          <input type="text" name="tags" class="form-control form-control-sm"
                 value="<?= e($v('tags')) ?>"
                 placeholder="tag1, tag2, tag3...">
          <div class="form-hint" style="margin-top:6px">Pisahkan dengan koma.</div>
        </div>
      </div>

    </div><!-- end right col -->
  </div><!-- end grid -->
</form>

<!-- jQuery & Summernote Lite -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
  $('#postContent').summernote({
    height: 450,
    tabsize: 2,
    placeholder: 'Ketik konten artikel di sini...',
    fontNames: [
      'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
      'Georgia', 'Impact', 'Times New Roman', 'Trebuchet MS',
      'Verdana', 'Inter', 'Roboto', 'Open Sans'
    ],
    fontNamesIgnoreCheck: ['Inter', 'Roboto', 'Open Sans'],
    fontSizes: ['8','9','10','11','12','14','16','18','20','22','24','28','32','36','48','64'],
    toolbar: [
      ['style', ['style']],
      ['font', ['fontname', 'fontsize']],
      ['fontstyle', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'video', 'hr']],
      ['view', ['fullscreen', 'codeview', 'help']]
    ]
  });
});

// Auto slug
function autoSlug(title) {
  if (!document.getElementById('postSlug').dataset.manual) {
    const slug = title.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/[\s]+/g, '-')
      .replace(/-+/g, '-')
      .trim();
    document.getElementById('postSlug').value = slug;
  }
}

document.getElementById('postSlug').addEventListener('input', function() {
  this.dataset.manual = 'true';
});

// Image preview
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const box = document.getElementById('imgPreviewBox');
      box.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width:100%;height:100%;object-fit:cover">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
