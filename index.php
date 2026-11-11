<?php
// ============================================================
// XyronNews – Homepage (index.php)
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$pageTitle = APP_NAME . ' – Berita Terkini Indonesia';
$pageDescription = 'Portal berita terpercaya. Berita terkini, analisis mendalam tentang peristiwa nasional, internasional, ekonomi, teknologi, dan gaya hidup.';

// Hero: 1 artikel paling baru
$heroPost = $db->query(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.author_id = u.id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC LIMIT 1"
)->fetch();

// Top Stories: 6 artikel berikutnya
$topStories = $db->query(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC LIMIT 6 OFFSET 1"
)->fetchAll();

// Main articles grid: 8 artikel
$mainPosts = $db->query(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.author_id = u.id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC LIMIT 8 OFFSET 7"
)->fetchAll();

// Artikel populer
$popularPosts = getPopularPosts(5);

// Data mitra
$partners = getPartners();

// Per-kategori sections (ambil 3 artikel per kategori, untuk 3 kategori pertama by menu_order)
$categories = getCategories();
$categorySections = [];
foreach (array_slice($categories, 0, 4) as $cat) {
    $stmt = $db->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name
         FROM posts p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN users u ON p.author_id = u.id
         WHERE p.status = 'published' AND p.category_id = ?
         ORDER BY p.published_at DESC LIMIT 4"
    );
    $stmt->execute([$cat['id']]);
    $posts = $stmt->fetchAll();
    if (!empty($posts)) {
        $categorySections[] = ['category' => $cat, 'posts' => $posts];
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero-section">
  <div class="container">
    <div class="hero-grid">
      <?php if ($heroPost): ?>
      <!-- Main Hero -->
      <article class="hero-main animate-fade-up">
        <a href="<?= e(postUrl($heroPost['slug'])) ?>">
          <img
            src="<?= e(postImage($heroPost['image'])) ?>"
            alt="<?= e($heroPost['title']) ?>"
            class="hero-main-img"
            width="800" height="450"
            loading="eager"
            onerror="this.src='<?= APP_URL ?>/hero.png'"
          >
          <div class="hero-main-overlay"></div>
          <div class="hero-main-content">
            <span class="hero-category-badge"><?= e(strtoupper($heroPost['category_name'] ?? 'BERITA')) ?></span>
            <h1 class="hero-title"><?= e($heroPost['title']) ?></h1>
            <div class="hero-meta">
              <span>Oleh <strong><?= e($heroPost['author_name'] ?? 'Redaksi') ?></strong></span>
              <span class="hero-meta-dot">·</span>
              <span><?= timeAgo($heroPost['published_at']) ?></span>
              <span class="hero-meta-dot">·</span>
              <span><?= formatViews($heroPost['views']) ?> views</span>
            </div>
          </div>
        </a>
      </article>

      <!-- Sidebar Top Stories -->
      <aside class="hero-sidebar animate-fade-up animate-delay-1">
        <div class="hero-sidebar-header">TOP STORIES HARI INI</div>
        <?php foreach ($topStories as $i => $story): ?>
        <article class="hero-sidebar-item" tabindex="0">
          <div class="hero-sidebar-num"><?= $i + 1 ?></div>
          <div class="hero-sidebar-text">
            <a href="<?= e(postUrl($story['slug'])) ?>" class="hero-sidebar-title"><?= e($story['title']) ?></a>
            <div class="hero-sidebar-time"><?= timeAgo($story['published_at']) ?> · <?= e($story['category_name'] ?? '') ?></div>
          </div>
        </article>
        <?php endforeach; ?>
        <?php if (empty($topStories)): ?>
        <div style="padding:20px;color:var(--text-muted);font-size:13px;text-align:center">Belum ada artikel.</div>
        <?php endif; ?>
      </aside>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== MARKET DATA STRIP ===== -->
<section class="market-data-strip" aria-label="Data pasar real-time">
  <div class="market-data-inner" id="marketDataInner"></div>
</section>

<!-- ===== MAIN CONTENT + SIDEBAR ===== -->
<main class="section-padding" id="main-content">
  <div class="container">
    <div class="content-layout">

      <!-- LEFT MAIN COLUMN -->
      <div class="main-col">

        <!-- Ad Leaderboard -->
        <div style="margin-bottom:24px;text-align:center;">
          <?= renderAd('header_728x90') ?>
        </div>

        <!-- Berita Utama Grid -->
        <section aria-label="Berita Utama" class="animate-fade-up">
          <div class="section-header">
            <h2 class="section-title">BERITA <span>TERBARU</span></h2>
            <a href="<?= APP_URL ?>/search.php" class="section-more">Lihat Semua →</a>
          </div>

          <?php if (!empty($mainPosts)): ?>
          <div class="news-grid-2" style="margin-bottom:20px;">
            <?php foreach (array_slice($mainPosts, 0, 2) as $post): ?>
            <article class="news-card news-card-featured" tabindex="0">
              <a href="<?= e(postUrl($post['slug'])) ?>" class="news-card-img-wrap">
                <img
                  src="<?= e(postImage($post['image'])) ?>"
                  alt="<?= e($post['title']) ?>"
                  class="news-card-img"
                  width="400" height="267"
                  loading="lazy"
                  onerror="this.src='<?= APP_URL ?>/hero.png'"
                >
                <span class="news-card-badge"><?= e(strtoupper($post['category_name'] ?? 'BERITA')) ?></span>
              </a>
              <div class="news-card-body">
                <a href="<?= e(postUrl($post['slug'])) ?>">
                  <div class="news-card-title"><?= e($post['title']) ?></div>
                </a>
                <div class="news-card-excerpt"><?= e(excerpt($post['summary'] ?: $post['content'], 150)) ?></div>
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

          <div class="news-grid-4">
            <?php foreach (array_slice($mainPosts, 2, 4) as $post): ?>
            <article class="news-card" tabindex="0">
              <a href="<?= e(postUrl($post['slug'])) ?>" class="news-card-img-wrap">
                <img
                  src="<?= e(postImage($post['image'])) ?>"
                  alt="<?= e($post['title']) ?>"
                  class="news-card-img"
                  width="300" height="188"
                  loading="lazy"
                  onerror="this.src='<?= APP_URL ?>/hero.png'"
                >
                <span class="news-card-badge"><?= e(strtoupper($post['category_name'] ?? '')) ?></span>
              </a>
              <div class="news-card-body">
                <a href="<?= e(postUrl($post['slug'])) ?>">
                  <div class="news-card-title"><?= e($post['title']) ?></div>
                </a>
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
          <?php else: ?>
          <div class="news-card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
            </div>
            <p>Belum ada artikel yang dipublikasikan.</p>
            <a href="<?= APP_URL ?>/admin/posts_form.php" style="color:var(--primary-blue)">Buat artikel pertama →</a>
          </div>
          <?php endif; ?>
        </section>

        <hr class="divider">

        <!-- Per-Kategori Sections -->
        <?php foreach ($categorySections as $section): ?>
        <section aria-label="Berita <?= e($section['category']['name']) ?>" class="animate-fade-up">
          <div class="section-header">
            <h2 class="section-title"><?= e(strtoupper($section['category']['name'])) ?></h2>
            <a href="<?= e(categoryUrl($section['category']['slug'])) ?>" class="section-more">Lihat Semua →</a>
          </div>

          <div class="news-list">
            <?php foreach ($section['posts'] as $post): ?>
            <article class="news-list-item" tabindex="0">
              <a href="<?= e(postUrl($post['slug'])) ?>">
                <img
                  src="<?= e(postImage($post['image'])) ?>"
                  alt="<?= e($post['title']) ?>"
                  class="news-list-img"
                  width="96" height="64"
                  loading="lazy"
                  onerror="this.src='<?= APP_URL ?>/thumb1.png'"
                >
              </a>
              <div class="news-list-content">
                <div class="news-list-category"><?= e(strtoupper($post['category_name'] ?? '')) ?></div>
                <a href="<?= e(postUrl($post['slug'])) ?>" class="news-list-title"><?= e($post['title']) ?></a>
                <div class="news-list-time"><?= timeAgo($post['published_at']) ?> · <?= formatViews($post['views']) ?> views</div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>

          <div style="margin-top:20px;text-align:center;"><?= renderAd('standard_468x60') ?></div>
        </section>

        <hr class="divider">
        <?php endforeach; ?>

      </div><!-- end .main-col -->

      <!-- RIGHT SIDEBAR -->
      <aside class="sidebar" role="complementary">

        <!-- Trending Widget -->
        <div class="sidebar-widget">
          <div class="sidebar-widget-header">
            <span class="sidebar-widget-title" style="display:flex;align-items:center;gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              Terpopuler
            </span>
          </div>
          <div class="sidebar-widget-body">
            <div class="trending-list">
              <?php foreach ($popularPosts as $i => $pp): ?>
              <div class="trending-item" tabindex="0">
                <span class="trending-rank"><?= $i + 1 ?></span>
                <a href="<?= e(postUrl($pp['slug'])) ?>" class="trending-title"><?= e($pp['title']) ?></a>
              </div>
              <?php endforeach; ?>
              <?php if (empty($popularPosts)): ?>
              <p style="padding:8px;color:var(--text-muted);font-size:13px">Belum ada data.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Market Mini Widget -->
        <div class="sidebar-widget">
          <div class="sidebar-widget-header">
            <span class="sidebar-widget-title" style="display:flex;align-items:center;gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              Data Pasar
            </span>
            <span style="font-size:10px;color:var(--text-light)">Live</span>
          </div>
          <div class="sidebar-widget-body" style="padding:8px 12px">
            <div class="market-mini" id="marketMiniWidget"></div>
          </div>
        </div>

        <!-- Iklan Standard 300x250 -->
        <div class="sidebar-widget" style="overflow:hidden;">
          <div class="sidebar-widget-header"><span class="sidebar-widget-title">IKLAN & PROMOSI</span></div>
          <div style="padding:8px;text-align:center;"><?= renderAd('standard_300x250') ?></div>
        </div>

        <!-- Newsletter -->
        <div class="newsletter-widget">
          <div class="newsletter-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div class="newsletter-title">Newsletter Harian</div>
          <div class="newsletter-desc">Dapatkan ringkasan berita terpenting langsung ke inbox Anda setiap pagi.</div>
          <form class="newsletter-form" onsubmit="handleNewsletter(event)">
            <input type="email" class="newsletter-input" id="newsletterEmail" placeholder="Email Anda..." required>
            <button type="submit" class="newsletter-btn">Langganan Gratis →</button>
          </form>
        </div>

        <!-- Kategori Tags -->
        <div class="sidebar-widget">
          <div class="sidebar-widget-header">
            <span class="sidebar-widget-title" style="display:flex;align-items:center;gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              Kategori
            </span>
          </div>
          <div class="tags-cloud">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="tag-chip"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>

      </aside>
    </div><!-- end .content-layout -->
  </div><!-- end .container -->
</main>

<?php if (!empty($partners)): ?>
<!-- ===== MITRA & SPONSOR ===== -->
<section class="partners-section" aria-label="Mitra Kerjasama">
  <div class="container">
    <div class="section-header" style="justify-content:center;margin-bottom:24px">
      <h2 class="section-title">MITRA <span>KAMI</span></h2>
    </div>
    <div class="partners-grid">
      <?php foreach ($partners as $partner): ?>
      <div class="partner-logo">
        <?php if ($partner['target_url']): ?><a href="<?= e($partner['target_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= e($partner['name']) ?>"><?php endif; ?>
          <img src="<?= e($partner['logo_image']) ?>" alt="<?= e($partner['name']) ?>" loading="lazy">
        <?php if ($partner['target_url']): ?></a><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php 
$popupAd = getAd('popup_modal');
if ($popupAd && !empty($popupAd['image_url'])): 
?>
<!-- ===== POPUP PROMO MODAL ===== -->
<div id="popupModalAd" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px);">
  <div style="position:relative;max-width:520px;width:100%;background:var(--bg-primary);border:1px solid var(--border-light);border-radius:12px;overflow:hidden;box-shadow:0 25px 50px rgba(0,0,0,0.5);">
    <button type="button" onclick="closePopupModalAd()" aria-label="Tutup Iklan" 
            style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.65);color:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-weight:700;display:flex;align-items:center;justify-content:center;z-index:20;transition:background .2s;"
            onmouseover="this.style.background='rgba(232,0,13,0.9)'" onmouseout="this.style.background='rgba(0,0,0,0.65)'">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <a href="<?= e($popupAd['target_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer" style="display:block;">
      <img src="<?= e($popupAd['image_url']) ?>" alt="<?= e($popupAd['title']) ?>" style="width:100%;height:auto;display:block;">
    </a>
  </div>
</div>
<script>
function closePopupModalAd() {
  var m = document.getElementById('popupModalAd');
  if (m) m.style.display = 'none';
  sessionStorage.setItem('xnn_popup_closed', '1');
}
window.addEventListener('load', function() {
  if (!sessionStorage.getItem('xnn_popup_closed')) {
    setTimeout(function() {
      var m = document.getElementById('popupModalAd');
      if (m) m.style.display = 'flex';
    }, 2500);
  }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
