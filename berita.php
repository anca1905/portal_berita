<?php
// ============================================================
// XyronNews – Detail Artikel (berita.php)
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$post = getPostBySlug($slug);
if (!$post) {
    http_response_code(404);
    $pageTitle = '404 – Artikel Tidak Ditemukan';
    include __DIR__ . '/includes/header.php';
    echo '<div class="container section-padding" style="text-align:center;min-height:300px">
            <div style="margin-bottom:16px;color:var(--text-muted);display:flex;justify-content:center;">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
            </div>
            <h1 style="color:var(--text-primary);margin-bottom:12px">Artikel Tidak Ditemukan</h1>
            <p style="color:var(--text-muted);margin-bottom:24px">Artikel yang Anda cari tidak tersedia atau telah dihapus.</p>
            <a href="'.APP_URL.'/" style="background:var(--primary-blue);color:#fff;padding:10px 24px;border-radius:6px;font-weight:600;">← Kembali ke Beranda</a>
          </div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Increment views
incrementViews($post['id']);

// Comments
$comments = getApprovedComments($post['id']);
$commentCount = count($comments);
$relatedPosts = getRelatedPosts($post['id'], $post['category_id'], 4);
$popularPosts = getPopularPosts(5);
$categories = getCategories();

// Handle comment submission
$commentError = '';
$commentSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!empty($_POST['website_hp'])) {
        // Honeypot bot protection
        $commentSuccess = 'Komentar Anda sedang diproses.';
    } else {
        verifyCsrf();
        $cName    = sanitize($_POST['commenter_name'] ?? '');
        $cComment = sanitize($_POST['comment_text'] ?? '');
        if (strlen($cName) < 2) {
            $commentError = 'Nama minimal 2 karakter.';
        } elseif (strlen($cComment) < 5) {
            $commentError = 'Komentar terlalu singkat.';
        } elseif (strlen($cComment) > 2000) {
            $commentError = 'Komentar terlalu panjang (maks. 2000 karakter).';
        } else {
            addComment($post['id'], $cName, $cComment);
            $commentSuccess = 'Komentar berhasil dikirim!';
            $comments = getApprovedComments($post['id']);
            $commentCount = count($comments);
        }
    }
}

$pageTitle = e($post['title']) . ' – ' . APP_NAME;
$pageDescription = excerpt($post['summary'] ?: $post['content'], 160);
$pageImage = postImage($post['image']);
$canonicalUrl = postUrl($post['slug']);

// Tags
$tags = array_filter(array_map('trim', explode(',', $post['tags'] ?? '')));

include __DIR__ . '/includes/header.php';
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": <?= json_encode($post['title']) ?>,
  "image": [<?= json_encode(postImage($post['image'])) ?>],
  "datePublished": <?= json_encode(date('c', strtotime($post['published_at']))) ?>,
  "dateModified": <?= json_encode(date('c', strtotime($post['published_at']))) ?>,
  "author": [{
      "@type": "Person",
      "name": <?= json_encode($post['author_name'] ?? 'Redaksi') ?>
  }],
  "publisher": {
      "@type": "Organization",
      "name": <?= json_encode(APP_NAME) ?>,
      "logo": {
        "@type": "ImageObject",
        "url": <?= json_encode(getLogoUrl()) ?>
      }
  },
  "description": <?= json_encode($pageDescription) ?>
}
</script>

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
      "name": <?= json_encode($post['category_name']) ?>,
      "item": <?= json_encode(categoryUrl($post['category_slug'])) ?>
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": <?= json_encode($post['title']) ?>,
      "item": <?= json_encode(postUrl($post['slug'])) ?>
    }
  ]
}
</script>

<!-- Reading progress bar -->
<div id="readProgress" style="position:fixed;top:0;left:0;height:3px;background:var(--accent-red);width:0%;z-index:99999;transition:width .1s;"></div>

<!-- BREADCRUMB -->
<div style="background:var(--bg-secondary);border-bottom:1px solid var(--border-light);padding:8px 0;">
  <div class="container" style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
    <a href="<?= APP_URL ?>/" style="color:var(--primary-blue);">Home</a>
    <span>›</span>
    <a href="<?= APP_URL ?>/kategori.php?slug=<?= e($post['category_slug']) ?>" style="color:var(--primary-blue);"><?= e($post['category_name']) ?></a>
    <span>›</span>
    <span style="color:var(--text-muted);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:300px"><?= e($post['title']) ?></span>
  </div>
</div>

<!-- ARTICLE CONTENT -->
<main class="section-padding">
  <div class="container">
    <div class="content-layout">

      <!-- ARTICLE -->
      <article class="main-col" itemscope itemtype="https://schema.org/NewsArticle">

        <!-- Category + Title -->
        <div style="margin-bottom:20px">
          <a href="<?= APP_URL ?>/kategori.php?slug=<?= e($post['category_slug']) ?>"
             style="display:inline-block;background:var(--primary-blue);color:#fff;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:4px 10px;border-radius:3px;margin-bottom:12px">
            <?= e($post['category_name']) ?>
          </a>
          <h1 itemprop="headline" class="article-headline">
            <?= e($post['title']) ?>
          </h1>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;align-items:center;gap:8px">
              <div class="author-avatar" style="width:32px;height:32px;font-size:12px"><?= strtoupper(substr($post['author_name'] ?? 'R', 0, 1)) ?></div>
              <span itemprop="author" style="font-size:13px;font-weight:600;color:var(--text-secondary)"><?= e($post['author_name'] ?? 'Redaksi') ?></span>
            </div>
            <span style="color:var(--border-medium)">·</span>
            <span style="font-size:12px;color:var(--text-muted)" itemprop="datePublished" content="<?= e($post['published_at']) ?>">
              <?= formatDate($post['published_at'], 'd M Y, H:i') ?> WIB
            </span>
            <span style="color:var(--border-medium)">·</span>
            <span style="font-size:12px;color:var(--text-muted)"><?= readingTime($post['content']) ?></span>
            <span style="color:var(--border-medium)">·</span>
            <span style="font-size:12px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <?= formatViews($post['views'] + 1) ?> views
            </span>
          </div>

          <!-- Share Buttons -->
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
            <span style="font-size:12px;font-weight:600;color:var(--text-muted);align-self:center">Bagikan:</span>
            <?php $shareUrl = urlencode(APP_URL . '/berita.php?slug=' . $post['slug']); $shareTitle = urlencode($post['title']); ?>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank"
               style="display:flex;align-items:center;gap:6px;background:#1877F2;color:#fff;font-size:12px;font-weight:600;padding:6px 12px;border-radius:4px;text-decoration:none">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
              Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank"
               style="display:flex;align-items:center;gap:6px;background:#000;color:#fff;font-size:12px;font-weight:600;padding:6px 12px;border-radius:4px;text-decoration:none">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              Twitter
            </a>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank"
               style="display:flex;align-items:center;gap:6px;background:#25D366;color:#fff;font-size:12px;font-weight:600;padding:6px 12px;border-radius:4px;text-decoration:none">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
              WhatsApp
            </a>
            <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank"
               style="display:flex;align-items:center;gap:6px;background:#0088cc;color:#fff;font-size:12px;font-weight:600;padding:6px 12px;border-radius:4px;text-decoration:none">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.458c.538-.196 1.006.128.832.939z"/></svg>
              Telegram
            </a>
            <button onclick="copyToClipboard('<?= APP_URL ?>/berita.php?slug=<?= e($post['slug']) ?>')"
               style="display:flex;align-items:center;gap:6px;background:var(--bg-secondary);color:var(--text-secondary);font-size:12px;font-weight:600;padding:6px 12px;border-radius:4px;border:1px solid var(--border-light);cursor:pointer">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Salin Link
            </button>
            <button onclick="window.print()"
               style="display:flex;align-items:center;gap:6px;background:var(--bg-secondary);color:var(--text-secondary);font-size:12px;font-weight:600;padding:6px 12px;border-radius:4px;border:1px solid var(--border-light);cursor:pointer">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Cetak
            </button>
            <div style="margin-left:auto;display:flex;align-items:center;gap:4px;">
              <span style="font-size:11px;color:var(--text-muted);font-weight:600;margin-right:4px;">Ukuran:</span>
              <button type="button" onclick="changeFontSize(-1)" style="padding:4px 8px;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;" title="Kecilkan Font">A-</button>
              <button type="button" onclick="changeFontSize(0)" style="padding:4px 8px;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;" title="Reset Font">A</button>
              <button type="button" onclick="changeFontSize(1)" style="padding:4px 8px;background:var(--bg-secondary);border:1px solid var(--border-light);border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;" title="Besarkan Font">A+</button>
            </div>
          </div>
        </div>

        <!-- Featured Image -->
        <?php if ($post['image']): ?>
        <div style="margin-bottom:24px;border-radius:var(--radius-lg);overflow:hidden;aspect-ratio:16/9;">
          <img
            src="<?= e(postImage($post['image'])) ?>"
            alt="<?= e($post['title']) ?>"
            style="width:100%;height:100%;object-fit:cover"
            loading="eager"
            itemprop="image"
            onerror="this.src='<?= APP_URL ?>/hero.png'"
          >
        </div>
        <?php endif; ?>

        <!-- Article Body -->
        <div class="article-body" itemprop="articleBody"
             style="font-size:16px;line-height:1.65;color:var(--text-secondary);margin-bottom:32px">
          <?= $post['content'] ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
        <div style="margin-bottom:28px;padding:16px;background:var(--bg-secondary);border-radius:var(--radius-md);">
          <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-right:8px;display:inline-flex;align-items:center;gap:4px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Tags:
          </span>
          <?php foreach ($tags as $tag): ?>
          <a href="<?= APP_URL ?>/search.php?q=<?= urlencode($tag) ?>" class="tag-chip" style="margin:2px"><?= e($tag) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Ad -->
        <div style="margin-bottom:28px;text-align:center;">
          <?= renderAd('standard_468x100') ?>
        </div>

        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
        <section style="margin-bottom:32px">
          <div class="section-header" style="margin-bottom:16px">
            <h2 class="section-title">BERITA <span>TERKAIT</span></h2>
          </div>
          <div class="news-grid-4">
            <?php foreach ($relatedPosts as $rp): ?>
            <article class="news-card" tabindex="0">
              <a href="<?= e(postUrl($rp['slug'])) ?>" class="news-card-img-wrap">
                <img src="<?= e(postImage($rp['image'])) ?>" alt="<?= e($rp['title']) ?>" class="news-card-img" width="200" height="125" loading="lazy" onerror="this.src='<?= APP_URL ?>/thumb1.png'">
              </a>
              <div class="news-card-body">
                <a href="<?= e(postUrl($rp['slug'])) ?>">
                  <div class="news-card-title"><?= e($rp['title']) ?></div>
                </a>
                <div class="news-card-footer">
                  <span class="news-card-time"><?= timeAgo($rp['published_at']) ?></span>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <!-- COMMENTS -->
        <section id="komentar" style="margin-top:32px;padding-top:28px;border-top:2px solid var(--border-light)">
          <h2 style="font-size:18px;font-weight:800;margin-bottom:20px;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Komentar (<?= $commentCount ?>)
          </h2>

          <?php if ($commentSuccess): ?>
          <div style="background:#D1FAE5;border:1px solid #6EE7B7;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span><?= e($commentSuccess) ?></span>
          </div>
          <?php endif; ?>

          <?php if (!empty($comments)): ?>
          <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:28px">
            <?php foreach ($comments as $comment): ?>
            <div style="background:var(--bg-secondary);padding:16px;border-radius:var(--radius-md);border-left:3px solid var(--primary-blue)">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <div class="author-avatar" style="width:32px;height:32px;font-size:12px"><?= strtoupper(substr($comment['name'], 0, 1)) ?></div>
                <div>
                  <div style="font-size:13px;font-weight:700;color:var(--text-primary)"><?= e($comment['name']) ?></div>
                  <div style="font-size:11px;color:var(--text-light)"><?= timeAgo($comment['created_at']) ?></div>
                </div>
              </div>
              <p style="font-size:14px;color:var(--text-secondary);line-height:1.6;margin:0"><?= nl2br(e($comment['comment'])) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Comment Form -->
          <div style="background:var(--bg-primary);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:24px">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;color:var(--text-primary)">Tulis Komentar</h3>
            <?php if ($commentError): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:13px;display:flex;align-items:center;gap:8px;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <span><?= e($commentError) ?></span>
            </div>
            <?php endif; ?>
            <form method="POST" action="#komentar">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <div style="display:none;" aria-hidden="true">
                <input type="text" name="website_hp" tabindex="-1" autocomplete="off">
              </div>
              <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px">Nama *</label>
                <input type="text" name="commenter_name" required maxlength="100"
                       style="width:100%;padding:10px 14px;border:1px solid var(--border-light);border-radius:8px;font-size:14px;outline:none;transition:border-color .2s"
                       placeholder="Nama Anda"
                       value="<?= e($_POST['commenter_name'] ?? '') ?>"
                       onfocus="this.style.borderColor='var(--primary-blue)'"
                       onblur="this.style.borderColor='var(--border-light)'">
              </div>
              <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px">Komentar *</label>
                <textarea name="comment_text" required maxlength="2000" rows="4"
                          style="width:100%;padding:10px 14px;border:1px solid var(--border-light);border-radius:8px;font-size:14px;outline:none;resize:vertical;transition:border-color .2s"
                          placeholder="Tulis komentar Anda..."
                          onfocus="this.style.borderColor='var(--primary-blue)'"
                          onblur="this.style.borderColor='var(--border-light)'"><?= e($_POST['comment_text'] ?? '') ?></textarea>
              </div>
              <button type="submit" name="submit_comment"
                      style="background:var(--primary-blue);color:#fff;font-weight:700;font-size:14px;padding:10px 24px;border-radius:8px;border:none;cursor:pointer;transition:background .2s"
                      onmouseover="this.style.background='var(--primary-blue-dark)'"
                      onmouseout="this.style.background='var(--primary-blue)'">
                Kirim Komentar →
              </button>
            </form>
          </div>
        </section>

      </article><!-- end article -->

      <!-- SIDEBAR -->
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
            </div>
          </div>
        </div>

        <!-- Iklan -->
        <div class="sidebar-widget" style="overflow:hidden">
          <div class="sidebar-widget-header"><span class="sidebar-widget-title">IKLAN & PROMOSI</span></div>
          <div style="padding:8px;text-align:center"><?= renderAd('standard_300x250') ?></div>
        </div>

        <!-- Kategori -->
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
    </div>
  </div>
</main>

<style>
/* Normalize article content from Summernote / MS Word */
.article-body p { 
    margin-top: 0 !important; 
    margin-bottom: 16px !important; 
}
.article-body p:last-child { margin-bottom: 0 !important; }
.article-body h2, .article-body h3, .article-body h4 { margin: 1.5em 0 0.8em; font-family: var(--font-display); color: var(--text-primary); }
.article-body img { border-radius: var(--radius-md); max-width: 100%; height: auto; margin: 1em 0; }
.article-body a { color: var(--primary-blue); text-decoration: none; }
.article-body a:hover { text-decoration: underline; }
.article-body blockquote { border-left: 4px solid var(--primary-blue); margin: 1.5em 0; padding: 12px 20px; background: var(--primary-blue-light); border-radius: 0 8px 8px 0; font-style: italic; color: var(--text-secondary); }
.article-body ul, .article-body ol { margin: 0 0 16px 24px; padding: 0; }
.article-body li { margin-bottom: 6px; }

/* Default base styling — diset ke 14px agar sama dengan default Summernote editor */
.article-body {
    font-size: 14px;
    line-height: 1.65;
}
/* JANGAN tambahkan font-size di sini agar inline style dari admin bisa override */
</style>

<?php
$extraJs = "<script>
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    if (window.showNotification) showNotification('Tautan Disalin', 'Tautan artikel telah disalin ke clipboard.');
    else alert('Link berhasil disalin!');
  });
}

var currentFontSize = 15;
function changeFontSize(step) {
  var body = document.querySelector('.article-body');
  if (!body) return;
  if (step === 0) currentFontSize = 15;
  else currentFontSize = Math.min(22, Math.max(12, currentFontSize + step * 2));
  body.style.fontSize = currentFontSize + 'px';
}

window.addEventListener('scroll', function() {
  var bar = document.getElementById('readProgress');
  if (!bar) return;
  var docHeight = document.documentElement.scrollHeight - window.innerHeight;
  if (docHeight > 0) {
    var pct = (window.scrollY / docHeight) * 100;
    bar.style.width = Math.min(100, Math.max(0, pct)) + '%';
  }
}, { passive: true });
</script>";
include __DIR__ . '/includes/footer.php';
?>
