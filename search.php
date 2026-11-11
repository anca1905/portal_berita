<?php
// XyronNews – Search Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$q    = sanitize($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * POSTS_PER_PAGE;

$posts = [];
$total = 0;
if (strlen($q) >= 2) {
    $posts = searchPosts($q, POSTS_PER_PAGE, $offset);
    $total = countSearchResults($q);
}
$popularPosts = getPopularPosts(5);
$categories = getCategories();

$pageTitle = ($q ? 'Pencarian: "' . e($q) . '" – ' : 'Cari Berita – ') . APP_NAME;
$urlPattern = APP_URL . '/search.php?q=' . urlencode($q) . '&page={page}';

include __DIR__ . '/includes/header.php';
?>

<section style="background:linear-gradient(135deg,var(--primary-blue),var(--primary-blue-dark));padding:40px 0;">
  <div class="container">
    <form action="<?= APP_URL ?>/search.php" method="GET">
      <div style="max-width:600px;margin:0 auto;text-align:center">
        <h1 style="color:#fff;font-size:22px;font-weight:800;margin-bottom:16px">Cari Berita</h1>
        <div style="display:flex;background:var(--bg-primary);border:1px solid var(--border-light);border-radius:50px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.2)">
          <input type="text" name="q" value="<?= e($q) ?>"
                 placeholder="Ketik kata kunci..."
                 style="flex:1;border:none;outline:none;padding:14px 20px;font-size:15px;background:transparent;color:var(--text-primary)">
          <button type="submit"
                  style="background:var(--primary-blue);color:#fff;border:none;padding:14px 24px;font-size:14px;font-weight:700;cursor:pointer">
            Cari →
          </button>
        </div>
      </div>
    </form>
  </div>
</section>

<main class="section-padding">
  <div class="container">
    <div class="content-layout">
      <div class="main-col">
        <?php if ($q): ?>
        <div style="margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border-light)">
          <?php if ($total > 0): ?>
          <p style="color:var(--text-muted);font-size:14px">
            Ditemukan <strong style="color:var(--text-primary)"><?= number_format($total) ?> artikel</strong> untuk pencarian
            "<strong style="color:var(--primary-blue)"><?= e($q) ?></strong>"
          </p>
          <?php else: ?>
          <div style="text-align:center;padding:60px 20px;color:var(--text-muted)">
            <div style="margin-bottom:16px;color:var(--text-light);display:flex;justify-content:center;">
              <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <h2 style="font-size:20px;margin-bottom:8px">Tidak Ada Hasil</h2>
            <p>Tidak ditemukan artikel untuk "<strong><?= e($q) ?></strong>". Coba kata kunci lain.</p>
          </div>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:60px 20px;color:var(--text-muted)">
          <div style="margin-bottom:16px;color:var(--text-light);display:flex;justify-content:center;">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </div>
          <p>Masukkan kata kunci untuk mulai mencari.</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($posts)): ?>
        <div class="news-list">
          <?php foreach ($posts as $post): ?>
          <article class="news-list-item" tabindex="0">
            <a href="<?= e(postUrl($post['slug'])) ?>">
              <img src="<?= e(postImage($post['image'])) ?>" alt="<?= e($post['title']) ?>" class="news-list-img" width="120" height="80" loading="lazy" onerror="this.src='<?= APP_URL ?>/thumb1.png'" style="width:120px;height:80px">
            </a>
            <div class="news-list-content">
              <div style="display:flex;gap:8px;margin-bottom:4px">
                <span style="font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--primary-blue)"><?= e($post['category_name'] ?? '') ?></span>
              </div>
              <a href="<?= e(postUrl($post['slug'])) ?>" class="news-list-title"><?= e($post['title']) ?></a>
              <div class="news-list-time"><?= timeAgo($post['published_at']) ?> · <?= e($post['author_name'] ?? 'Redaksi') ?></div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?= paginate($total, POSTS_PER_PAGE, $page, $urlPattern) ?>
        <?php endif; ?>
      </div>

      <aside class="sidebar">
        <div class="sidebar-widget">
          <div class="sidebar-widget-header"><span class="sidebar-widget-title" style="display:flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--accent-red)"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Terpopuler</span></div>
          <div class="sidebar-widget-body">
            <div class="trending-list">
              <?php foreach ($popularPosts as $i => $pp): ?>
              <div class="trending-item">
                <span class="trending-rank"><?= $i + 1 ?></span>
                <a href="<?= e(postUrl($pp['slug'])) ?>" class="trending-title"><?= e($pp['title']) ?></a>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="sidebar-widget">
          <div class="sidebar-widget-header"><span class="sidebar-widget-title" style="display:flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary-blue)"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>Kategori</span></div>
          <div class="tags-cloud">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="tag-chip"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>
    </div>
  </div>
</main>

<style>
.pagination-nav { margin-top: 24px; }
.pagination { display: flex; gap: 6px; list-style: none; flex-wrap: wrap; }
.page-item .page-link { display: block; padding: 8px 14px; border: 1px solid var(--border-light); border-radius: 6px; font-size: 13px; font-weight: 600; color: var(--text-secondary); transition: all .2s; }
.page-item.active .page-link { background: var(--primary-blue); color: #fff; border-color: var(--primary-blue); }
.page-item .page-link:hover { background: var(--primary-blue-light); color: var(--primary-blue); border-color: var(--primary-blue); }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
