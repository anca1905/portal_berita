<?php
// XyronNews Admin – Dashboard
$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Stats
$totalPublished = (int) $db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$totalDraft     = (int) $db->query("SELECT COUNT(*) FROM posts WHERE status IN ('draft','pending')")->fetchColumn();
$totalViews     = (int) $db->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();
$totalComments  = (int) $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$totalCategories = (int) $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalAds       = (int) $db->query("SELECT COUNT(*) FROM ads WHERE is_active=1")->fetchColumn();

// Recent posts
$recentPosts = $db->query(
  "SELECT p.*, c.name AS category_name, u.full_name AS author_name
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.author_id = u.id
     ORDER BY p.id DESC LIMIT 10"
)->fetchAll();

// Posts per month (last 6 months)
$chartData = $db->query(
  "SELECT DATE_FORMAT(published_at,'%b %Y') AS month,
            COUNT(*) AS count
     FROM posts
     WHERE published_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(published_at,'%Y-%m'), DATE_FORMAT(published_at,'%b %Y')
     ORDER BY MIN(published_at) ASC"
)->fetchAll();

// Recent comments pending
$pendingCommentsList = $db->query(
  "SELECT c.*, p.title AS post_title, p.slug AS post_slug
     FROM comments c
     LEFT JOIN posts p ON c.post_id = p.id
     WHERE c.status = 'pending'
     ORDER BY c.created_at DESC LIMIT 5"
)->fetchAll();

// Top posts by views
$topPosts = $db->query(
  "SELECT p.title, p.slug, p.views, c.name AS category_name
     FROM posts p LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.status = 'published'
     ORDER BY p.views DESC LIMIT 5"
)->fetchAll();
?>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <polyline points="14 2 14 8 20 8" />
      </svg>
    </div>
    <div>
      <div class="stat-value"><?= number_format($totalPublished) ?></div>
      <div class="stat-label">Artikel Terbit</div>
      <div class="stat-change up"><?= $totalDraft ?> draft/pending</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
        <circle cx="12" cy="12" r="3" />
      </svg>
    </div>
    <div>
      <div class="stat-value"><?= number_format($totalViews) ?></div>
      <div class="stat-label">Total Views</div>
      <div class="stat-change up">Seluruh artikel</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon orange">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
      </svg>
    </div>
    <div>
      <div class="stat-value"><?= number_format($totalComments) ?></div>
      <div class="stat-label">Total Komentar</div>
      <div class="stat-change <?= $pendingComments > 0 ? 'down' : 'up' ?>">
        <?= $pendingComments > 0 ? "{$pendingComments} pending" : 'Semua tersetujui' ?>
      </div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon purple">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" />
      </svg>
    </div>
    <div>
      <div class="stat-value"><?= $totalCategories ?></div>
      <div class="stat-label">Kategori</div>
      <div class="stat-change up"><?= $totalAds ?> iklan aktif</div>
    </div>
  </div>
</div>

<!-- Chart + Top Posts -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-bottom:20px">
  <!-- Chart -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Artikel per Bulan (6 Bulan Terakhir)</span>
    </div>
    <div class="admin-card-body">
      <div class="chart-container">
        <canvas id="postsChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Top Posts -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Artikel Terpopuler</span>
    </div>
    <div class="admin-card-body" style="padding:0">
      <?php foreach ($topPosts as $i => $tp): ?>
        <div style="display:flex;gap:12px;align-items:flex-start;padding:12px 16px;border-bottom:1px solid var(--admin-border)">
          <div style="width:24px;height:24px;border-radius:6px;background:<?= $i === 0 ? 'var(--admin-danger)' : ($i < 3 ? 'var(--admin-warning)' : 'var(--admin-border)') ?>;display:flex;align-items:center;justify-content:center;color:<?= $i < 3 ? '#fff' : 'var(--admin-text-muted)' ?>;font-size:11px;font-weight:700;flex-shrink:0"><?= $i + 1 ?></div>
          <div style="flex:1;min-width:0">
            <a href="<?= APP_URL ?>/berita.php?slug=<?= e($tp['slug']) ?>" target="_blank"
              style="font-size:12px;font-weight:600;color:var(--admin-text);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
              title="<?= e($tp['title']) ?>">
              <?= e($tp['title']) ?>
            </a>
            <div style="font-size:11px;color:var(--admin-text-muted);margin-top:2px">
              <?= e($tp['category_name'] ?? '') ?> · <?= number_format($tp['views']) ?> views
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Recent Posts + Pending Comments -->
<div style="display:grid;grid-template-columns:1fr 380px;gap:20px">

  <!-- Recent Posts Table -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Artikel Terbaru</span>
      <a href="<?= APP_URL ?>/admin/posts.php" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Views</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentPosts as $post): ?>
            <tr>
              <td>
                <a href="<?= APP_URL ?>/admin/posts_form.php?id=<?= $post['id'] ?>"
                  style="font-weight:600;color:var(--admin-blue);max-width:250px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                  title="<?= e($post['title']) ?>">
                  <?= e(mb_strimwidth($post['title'], 0, 50, '...')) ?>
                </a>
                <div style="font-size:11px;color:var(--admin-text-muted)"><?= e($post['author_name'] ?? 'Redaksi') ?></div>
              </td>
              <td><span style="font-size:11px;color:var(--admin-text-muted)"><?= e($post['category_name'] ?? '-') ?></span></td>
              <td><span class="badge <?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
              <td><?= number_format($post['views']) ?></td>
              <td style="font-size:11px;color:var(--admin-text-muted);white-space:nowrap"><?= $post['published_at'] ? formatDate($post['published_at'], 'd M Y') : '-' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pending Comments -->
  <div class="admin-card">
    <div class="admin-card-header">
      <span class="admin-card-title">Komentar Pending <?= $pendingComments > 0 ? "<span class='sidebar-badge' style='margin-left:8px'>{$pendingComments}</span>" : '' ?></span>
      <a href="<?= APP_URL ?>/admin/comments.php" class="btn btn-outline btn-sm">Kelola</a>
    </div>
    <div class="admin-card-body" style="padding:0">
      <?php if (!empty($pendingCommentsList)): ?>
        <?php foreach ($pendingCommentsList as $cmt): ?>
          <div style="padding:12px 16px;border-bottom:1px solid var(--admin-border)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:4px">
              <span style="font-size:12px;font-weight:700;color:var(--admin-text)"><?= e($cmt['name']) ?></span>
              <span style="font-size:10px;color:var(--admin-text-muted);white-space:nowrap"><?= timeAgo($cmt['created_at']) ?></span>
            </div>
            <p style="font-size:11px;color:var(--admin-text-muted);margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
              <?= e(mb_strimwidth($cmt['comment'], 0, 80, '...')) ?>
            </p>
            <div style="display:flex;gap:6px">
              <a href="<?= APP_URL ?>/admin/comments.php?approve=<?= $cmt['id'] ?>" class="action-btn approve" style="font-size:10px;padding:3px 8px">Setujui</a>
              <span style="font-size:11px;color:var(--admin-text-muted);align-self:center;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                <?= e(mb_strimwidth($cmt['post_title'] ?? '', 0, 25, '...')) ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state" style="padding:30px 20px">
          <div class="empty-icon" style="display:flex;justify-content:center;margin-bottom:8px;color:var(--admin-success)">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <h3>Semua Bersih!</h3>
          <p>Tidak ada komentar yang menunggu persetujuan.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
  const ctx = document.getElementById('postsChart');
  const chartData = <?= json_encode($chartData) ?>;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: chartData.map(d => d.month),
      datasets: [{
        label: 'Jumlah Artikel',
        data: chartData.map(d => d.count),
        backgroundColor: 'rgba(0,71,204,0.15)',
        borderColor: '#0047CC',
        borderWidth: 2,
        borderRadius: 6,
        fill: true,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
            font: {
              size: 11
            }
          },
          grid: {
            color: 'rgba(0,0,0,0.04)'
          }
        },
        x: {
          ticks: {
            font: {
              size: 11
            }
          },
          grid: {
            display: false
          }
        }
      }
    }
  });
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>