<?php
// XyronNews – Kategori + Pencarian
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * POSTS_PER_PAGE;

$category = $slug ? getCategoryBySlug($slug) : null;
if ($slug && !$category) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$categoryId = $category['id'] ?? null;
$totalPosts = countPosts($categoryId);
$posts = getPublishedPosts(POSTS_PER_PAGE, $offset, $categoryId);
$popularPosts = getPopularPosts(5);
$categories = getCategories();

$pageTitle = ($category ? e($category['name']) . ' – ' : '') . APP_NAME;
$canonicalUrl = $category ? categoryUrl($category['slug'], $page) : (APP_URL . '/kategori.php');
$urlPattern = $category ? (APP_URL . '/kategori/' . urlencode($slug) . '/halaman-{page}') : (APP_URL . '/kategori.php?page={page}');

include __DIR__ . '/includes/header.php';
?>

<?php if ($category): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Beranda",
      "item": <?= json_encode(APP_URL . '/') ?>
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": <?= json_encode($category['name']) ?>,
      "item": <?= json_encode(categoryUrl($category['slug'])) ?>
    }
  ]
}
</script>
<?php endif; ?>

<section style="background:var(--bg-secondary);padding:20px 0;border-bottom:1px solid var(--border-light)">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="width:4px;height:32px;background:var(--primary-blue);border-radius:2px;flex-shrink:0"></div>
      <div>
        <h1 style="font-size:22px;font-weight:800;color:var(--text-primary);margin-bottom:2px">
          <?= $category ? e($category['name']) : 'Semua Berita' ?>
        </h1>
        <div style="font-size:12px;color:var(--text-muted)"><?= number_format($totalPosts) ?> artikel</div>
      </div>
    </div>
  </div>
</section>

<main class="section-padding">
  <div class="container">
    <div class="content-layout">
      <div class="main-col">
        <?php if (!empty($posts)): ?>
        <div class="news-grid-3" style="margin-bottom:32px">
          <?php foreach ($posts as $post): ?>
          <article class="news-card" tabindex="0">
            <a href="<?= e(postUrl($post['slug'])) ?>" class="news-card-img-wrap">
              <img src="<?= e(postImage($post['image'])) ?>" alt="<?= e($post['title']) ?>" class="news-card-img" width="300" height="188" loading="lazy" onerror="this.src='<?= APP_URL ?>/thumb1.png'">
              <span class="news-card-badge"><?= e(strtoupper($post['category_name'] ?? '')) ?></span>
            </a>
            <div class="news-card-body">
              <a href="<?= e(postUrl($post['slug'])) ?>">
                <div class="news-card-title"><?= e($post['title']) ?></div>
              </a>
              <div class="news-card-excerpt"><?= e(excerpt($post['summary'] ?: $post['content'], 120)) ?></div>
              <div class="news-card-footer">
                <div class="author-avatar"><?= strtoupper(substr($post['author_name'] ?? 'R', 0, 1)) ?></div>
                <span class="news-card-author"><?= e($post['author_name'] ?? 'Redaksi') ?></span>
                <span class="news-card-dot">·</span>
                <span class="news-card-time"><?= timeAgo($post['published_at']) ?></span>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?= paginate($totalPosts, POSTS_PER_PAGE, $page, $urlPattern) ?>
        <?php else: ?>
        <div style="text-align:center;padding:60px 20px;color:var(--text-muted)">
          <div style="margin-bottom:16px;color:var(--text-light);display:flex;justify-content:center;">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="9" y1="14" x2="15" y2="14"></line></svg>
          </div>
          <h2 style="font-size:20px;margin-bottom:8px">Belum Ada Artikel</h2>
          <p>Belum ada artikel yang dipublikasikan pada kategori ini.</p>
        </div>
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
          <div class="sidebar-widget-header"><span class="sidebar-widget-title" style="display:flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary-blue)"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>Semua Kategori</span></div>
          <div style="padding:12px;">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= e(categoryUrl($cat['slug'])) ?>"
               style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:6px;font-size:13px;font-weight:600;color:var(--text-secondary);transition:all .2s;margin-bottom:2px;<?= ($slug === $cat['slug']) ? 'background:var(--primary-blue-light);color:var(--primary-blue);' : '' ?>"
               onmouseover="this.style.background='var(--primary-blue-light)';this.style.color='var(--primary-blue)'"
               onmouseout="this.style.background='<?= ($slug === $cat['slug']) ? 'var(--primary-blue-light)' : 'transparent' ?>';this.style.color='<?= ($slug === $cat['slug']) ? 'var(--primary-blue)' : 'var(--text-secondary)' ?>'">
              <?= e($cat['name']) ?>
              <span style="font-size:10px;color:var(--text-light)">›</span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="sidebar-widget" style="overflow:hidden">
          <div class="sidebar-widget-header"><span class="sidebar-widget-title">IKLAN & PROMOSI</span></div>
          <div style="padding:8px;text-align:center"><?= renderAd('standard_300x250') ?></div>
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
