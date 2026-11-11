<?php
// XyronNews – Public Header
require_once __DIR__ . '/functions.php';

// Cek Maintenance Mode
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    header('Location: ' . APP_URL . '/maintenance.php');
    exit;
}
$categories = getCategories();
$currentSlug = basename($_SERVER['PHP_SELF'], '.php');
// Breaking news: 5 latest posts
$db = getDB();
$breakingPosts = $db->query("SELECT title, slug FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#0047CC">
  <title><?= e($pageTitle ?? APP_NAME . ' – ' . APP_TAGLINE) ?></title>
  <link rel="icon" href="<?= getLogoUrl() ?>">
  <link rel="canonical" href="<?= e($canonicalUrl ?? (APP_URL . ($_SERVER['REQUEST_URI'] ?? '/'))) ?>">
  <meta name="description" content="<?= e($pageDescription ?? 'Portal berita terkini dan terpercaya dari seluruh penjuru Indonesia.') ?>">
  
  <!-- OpenGraph -->
  <meta property="og:site_name" content="<?= e(APP_NAME) ?>">
  <meta property="og:title" content="<?= e($pageTitle ?? APP_NAME) ?>">
  <meta property="og:description" content="<?= e($pageDescription ?? 'Portal berita terkini Indonesia') ?>">
  <?php if (!empty($pageImage)): ?>
  <meta property="og:image" content="<?= e($pageImage) ?>">
  <?php endif; ?>
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle ?? APP_NAME) ?>">
  <meta name="twitter:description" content="<?= e($pageDescription ?? 'Portal berita terkini Indonesia') ?>">
  <?php if (!empty($pageImage)): ?>
  <meta name="twitter:image" content="<?= e($pageImage) ?>">
  <?php endif; ?>

  <link rel="alternate" type="application/rss+xml" title="<?= e(APP_NAME) ?> RSS Feed" href="<?= APP_URL ?>/rss.xml">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": <?= json_encode(APP_NAME) ?>,
    "url": <?= json_encode(APP_URL . '/') ?>,
    "potentialAction": {
      "@type": "SearchAction",
      "target": <?= json_encode(APP_URL . '/search.php?q={search_term_string}') ?>,
      "query-input": "required name=search_term_string"
    }
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "NewsMediaOrganization",
    "name": <?= json_encode(APP_NAME) ?>,
    "url": <?= json_encode(APP_URL) ?>,
    "logo": <?= json_encode(getLogoUrl()) ?>
  }
  </script>

  <!-- Dark Mode Anti-FOUC -->
  <script>
  (function() {
    var t = localStorage.getItem('xnn_theme');
    if (t === 'dark' || (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/style.css?v=<?= file_exists(__DIR__ . '/../style.css') ? filemtime(__DIR__ . '/../style.css') : '1.1' ?>">
  <?php if (!empty($__globalSettings['theme_primary']) || !empty($__globalSettings['theme_secondary'])): ?>
  <style>
    :root {
      <?php if (!empty($__globalSettings['theme_primary'])): ?>--primary-blue: <?= e($__globalSettings['theme_primary']) ?>;<?php endif; ?>
      <?php if (!empty($__globalSettings['theme_secondary'])): ?>--accent-red: <?= e($__globalSettings['theme_secondary']) ?>;<?php endif; ?>
    }
  </style>
  <?php endif; ?>
  <script>window.APP_NAME = "<?= e(APP_NAME) ?>";</script>
  <?php if (!empty($extraCss)) echo $extraCss; ?>
</head>
<body>

<!-- Scroll to Top -->
<button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll ke atas">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<!-- Notification Bar -->
<div class="notif-bar" id="notifBar"></div>

<!-- Market Ticker -->
<div class="ticker-strip" aria-label="Data pasar">
  <div class="ticker-label">LIVE MARKET</div>
  <div class="ticker-track" id="tickerTrack"></div>
</div>

<!-- HEADER -->
<div class="header-wrapper">
  <div class="header-top">
    <div class="header-inner">
      <a href="<?= APP_URL ?>" class="logo-link" aria-label="<?= APP_NAME ?> Home" style="display: flex; align-items: center; text-decoration: none;">
        <?php if (!empty($__globalSettings['site_logo'])): ?>
          <img src="<?= getLogoUrl() ?>" alt="<?= e(APP_NAME) ?>" class="site-header-logo">
        <?php else: ?>
          <div class="logo-icon">X</div>
        <?php endif; ?>
        <div class="logo-text">
          <span class="logo-name"><span class="logo-word-merah"><?= explode(' ', APP_NAME)[0] ?? APP_NAME ?></span><?php if (strpos(APP_NAME, ' ') !== false): ?> <span class="logo-word-putih"><?= implode(' ', array_slice(explode(' ', APP_NAME), 1)) ?></span><?php endif; ?></span>
          <span class="logo-tagline"><?= APP_TAGLINE ?></span>
        </div>
      </a>

      <?php
      $primaryCats  = array_slice($categories, 0, 5);
      $dropdownCats = array_slice($categories, 5);
      ?>
      <nav class="main-nav" role="navigation" aria-label="Menu utama">
        <a href="<?= APP_URL ?>" class="nav-link <?= $currentSlug === 'index' ? 'active' : '' ?>">HOME</a>
        <?php foreach ($primaryCats as $cat): ?>
        <a href="<?= e(categoryUrl($cat['slug'])) ?>"
           class="nav-link <?= (isset($_GET['slug']) && $_GET['slug'] === $cat['slug']) ? 'active' : '' ?>">
          <?= e(strtoupper($cat['name'])) ?>
        </a>
        <?php endforeach; ?>
        <?php if (!empty($dropdownCats)): ?>
        <div class="nav-dropdown" id="navDropdown">
          <button type="button" class="nav-link nav-dropdown-btn" id="navDropdownBtn" aria-haspopup="true" aria-expanded="false">
            LAINNYA <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="nav-dropdown-menu" id="navDropdownMenu">
            <?php foreach ($dropdownCats as $cat): ?>
            <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="nav-dropdown-item <?= (isset($_GET['slug']) && $_GET['slug'] === $cat['slug']) ? 'active' : '' ?>">
              <?= e(strtoupper($cat['name'])) ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/tv.php" class="nav-link nav-live">
          <div class="live-dot"></div> LIVE TV
        </a>
        <a href="<?= APP_URL ?>/pasang-iklan.php" class="nav-link <?= $currentSlug === 'pasang-iklan' ? 'active' : '' ?>" style="color:var(--accent-red);font-weight:700;">
          PASANG IKLAN
        </a>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle tema gelap/terang" title="Mode Gelap / Terang">
          <svg class="theme-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
          </svg>
          <svg class="theme-icon-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
          </svg>
        </button>
        <button class="search-toggle" id="searchToggleBtn" aria-label="Cari berita">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Buka menu navigasi">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Drawer & Backdrop -->
  <div class="mobile-drawer-backdrop" id="mobileDrawerBackdrop"></div>
  <aside class="mobile-drawer" id="mobileDrawer" aria-label="Menu navigasi mobile" aria-hidden="true">
    <div class="mobile-drawer-header">
      <div class="mobile-drawer-brand">
        <span class="logo-name"><span class="logo-word-merah"><?= explode(' ', APP_NAME)[0] ?? APP_NAME ?></span><?php if (strpos(APP_NAME, ' ') !== false): ?> <span class="logo-word-putih"><?= implode(' ', array_slice(explode(' ', APP_NAME), 1)) ?></span><?php endif; ?></span>
      </div>
      <button type="button" class="mobile-drawer-close" id="mobileDrawerCloseBtn" aria-label="Tutup menu">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>
    <div class="mobile-drawer-body">
      <form action="<?= APP_URL ?>/search.php" method="GET" class="mobile-drawer-search">
        <input type="text" name="q" placeholder="Cari berita..." autocomplete="off">
        <button type="submit" aria-label="Cari">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
      </form>

      <div class="mobile-drawer-section">
        <div class="mobile-drawer-title">MENU UTAMA</div>
        <a href="<?= APP_URL ?>" class="mobile-drawer-link <?= $currentSlug === 'index' ? 'active' : '' ?>">
          <span style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Beranda
          </span>
        </a>
        <a href="<?= APP_URL ?>/tv.php" class="mobile-drawer-link">
          <span style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>
            Live TV Streaming
          </span>
          <span class="badge-live-sm">LIVE</span>
        </a>
      </div>

      <div class="mobile-drawer-section">
        <div class="mobile-drawer-title">KATEGORI BERITA</div>
        <div class="mobile-drawer-cats">
          <?php foreach ($categories as $cat): ?>
          <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="mobile-drawer-cat-item <?= (isset($_GET['slug']) && $_GET['slug'] === $cat['slug']) ? 'active' : '' ?>">
            <?= e($cat['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mobile-drawer-section">
        <div class="mobile-drawer-title">INFORMASI REDAKSI</div>
        <a href="<?= APP_URL ?>/pasang-iklan.php" class="mobile-drawer-link <?= $currentSlug === 'pasang-iklan' ? 'active' : '' ?>">
          <span style="display:flex;align-items:center;gap:8px;color:var(--accent-red);font-weight:700;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Pasang Iklan & Media Kit
          </span>
          <span style="font-size:10px;background:var(--accent-red);color:#fff;padding:2px 6px;border-radius:10px;font-weight:700;">HOT</span>
        </a>
        <a href="<?= APP_URL ?>/tentang.php" class="mobile-drawer-link">
          <span style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            Tentang Kami
          </span>
        </a>
        <a href="<?= APP_URL ?>/kontak.php" class="mobile-drawer-link">
          <span style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Hubungi Redaksi & Iklan
          </span>
        </a>
        <a href="<?= APP_URL ?>/pedoman.php" class="mobile-drawer-link">
          <span style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Pedoman Media Siber
          </span>
        </a>
        <a href="<?= APP_URL ?>/privasi.php" class="mobile-drawer-link">
          <span style="display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Kebijakan Privasi
          </span>
        </a>
      </div>
    </div>
  </aside>

  <!-- Sub Nav -->
  <nav class="sub-nav" aria-label="Sub menu">
    <div class="sub-nav-inner">
      <a href="<?= APP_URL ?>/search.php" class="sub-nav-link">CARI BERITA</a>
      <a href="<?= APP_URL ?>/pasang-iklan.php" class="sub-nav-link" style="color:var(--accent-red);font-weight:700;">TARIF IKLAN</a>
      <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
      <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="sub-nav-link"><?= e(strtoupper($cat['name'])) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
</div>

<!-- Search Overlay -->
<div class="search-overlay" id="searchOverlay" role="dialog" aria-modal="true">
  <div class="search-box">
    <form action="<?= APP_URL ?>/search.php" method="GET">
      <div class="search-input-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" class="search-input" id="mainSearchInput" placeholder="Cari berita, topik..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
        <button type="button" class="search-close-btn" id="searchCloseBtn">ESC</button>
      </div>
    </form>
    <div id="liveSearchResults" class="live-search-results" style="display:none;margin-top:14px;max-height:360px;overflow-y:auto;text-align:left;"></div>
    <div class="search-tags">
      <span class="search-tag-label">Trending:</span>
      <?php foreach ($categories as $cat): ?>
      <a href="<?= e(categoryUrl($cat['slug'])) ?>" class="search-tag"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Breaking News -->
<div class="breaking-banner" role="marquee">
  <div class="breaking-inner">
    <div class="breaking-label"><span class="live-dot" style="margin-right:2px;"></span> TERBARU</div>
    <div class="breaking-marquee">
      <div class="breaking-track">
        <?php foreach (array_merge($breakingPosts, $breakingPosts) as $bp): ?>
        <a href="<?= e(postUrl($bp['slug'])) ?>" class="breaking-item"><?= e($bp['title']) ?></a>
        <span class="breaking-sep">•</span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
