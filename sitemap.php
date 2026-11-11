<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$db = getDB();
$posts = $db->query("SELECT title, slug, image, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 500")->fetchAll();
$categories = getCategories();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <!-- Homepage -->
  <url>
    <loc><?= APP_URL ?>/</loc>
    <changefreq>always</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Halaman Statis -->
  <url>
    <loc><?= APP_URL ?>/tv.php</loc>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= APP_URL ?>/tentang.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  <url>
    <loc><?= APP_URL ?>/kontak.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  <url>
    <loc><?= APP_URL ?>/pedoman.php</loc>
    <changefreq>yearly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc><?= APP_URL ?>/privasi.php</loc>
    <changefreq>yearly</changefreq>
    <priority>0.5</priority>
  </url>

  <!-- Kategori -->
  <?php foreach ($categories as $cat): ?>
  <url>
    <loc><?= e(categoryUrl($cat['slug'])) ?></loc>
    <changefreq>hourly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>

  <!-- Artikel Berita Terbit -->
  <?php foreach ($posts as $p): ?>
  <url>
    <loc><?= e(postUrl($p['slug'])) ?></loc>
    <lastmod><?= date('c', strtotime($p['published_at'])) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
    <?php if ($p['image']): ?>
    <image:image>
      <image:loc><?= e(postImage($p['image'])) ?></image:loc>
      <image:title><?= e($p['title']) ?></image:title>
    </image:image>
    <?php endif; ?>
  </url>
  <?php endforeach; ?>
</urlset>
