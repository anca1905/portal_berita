<?php
ob_start();
// Admin Header – included in all admin pages
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/db.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Badge counts
$db = getDB();
$pendingComments = (int) $db->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
$draftPosts      = (int) $db->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
$pendingPosts    = (int) $db->query("SELECT COUNT(*) FROM posts WHERE status='pending'")->fetchColumn();

function sidebarLink(string $href, string $label, string $icon, string $page, string $current, int $badge = 0): string {
    $active = ($page === $current) ? ' active' : '';
    $badgeHtml = $badge > 0 ? "<span class='sidebar-badge'>{$badge}</span>" : '';
    return "<a href='{$href}' class='sidebar-link{$active}'>{$icon} {$label}{$badgeHtml}</a>";
}

$icons = [
    'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
    'posts'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
    'plus'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
    'category'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
    'comments'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    'ads'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>',
    'users'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'settings'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'home'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'partners'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($adminPageTitle ?? 'Admin') ?> – <?= APP_NAME ?> Admin</title>
  <link rel="icon" href="<?= getLogoUrl() ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/admin/admin.css">
  <?php if (!empty($adminExtraCss)) echo $adminExtraCss; ?>
</head>
<body>
<div class="admin-wrapper">

<!-- SIDEBAR -->
<nav class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-logo" style="align-items: center; display: flex;">
    <img src="<?= getLogoUrl() ?>" alt="Logo" style="width:36px; height:36px; object-fit:contain; border-radius:8px; margin-right:12px;">
    <div>
      <div class="sidebar-logo-text"><?= APP_NAME ?></div>
      <div class="sidebar-logo-sub">Admin Dashboard</div>
    </div>
  </div>

  <div class="sidebar-section-title">Main</div>
  <div class="sidebar-nav">
    <?= sidebarLink(APP_URL . '/admin/', 'Dashboard', $icons['dashboard'], 'index', $currentPage) ?>
    <?= sidebarLink(APP_URL . '/admin/posts.php', 'Semua Artikel', $icons['posts'], 'posts', $currentPage, $pendingPosts) ?>
    <?= sidebarLink(APP_URL . '/admin/posts_form.php', 'Tambah Artikel', $icons['plus'], 'posts_form', $currentPage) ?>
  </div>

  <div class="sidebar-section-title">Konten</div>
  <div class="sidebar-nav">
    <?= sidebarLink(APP_URL . '/admin/categories.php', 'Kategori', $icons['category'], 'categories', $currentPage) ?>
    <?= sidebarLink(APP_URL . '/admin/comments.php', 'Komentar', $icons['comments'], 'comments', $currentPage, $pendingComments) ?>
    <?= sidebarLink(APP_URL . '/admin/ads.php', 'Iklan', $icons['ads'], 'ads', $currentPage) ?>
    <?= sidebarLink(APP_URL . '/admin/partners.php', 'Mitra', $icons['partners'], 'partners', $currentPage) ?>
    <?= sidebarLink(APP_URL . '/admin/tv_channels.php', 'Live TV', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>', 'tv_channels', $currentPage) ?>
  </div>

  <?php if (hasRole('admin')): ?>
  <div class="sidebar-section-title">Admin</div>
  <div class="sidebar-nav">
    <?= sidebarLink(APP_URL . '/admin/users.php', 'Pengguna', $icons['users'], 'users', $currentPage) ?>
    <?= sidebarLink(APP_URL . '/admin/settings.php', 'Pengaturan', $icons['settings'], 'settings', $currentPage) ?>
  </div>
  <?php endif; ?>

  <div class="sidebar-section-title">Navigasi</div>
  <div class="sidebar-nav">
    <?= sidebarLink(APP_URL . '/', 'Lihat Website', $icons['home'], '', $currentPage) ?>
  </div>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="sidebar-user-name"><?= e($admin['name'] ?? 'Admin') ?></div>
        <div class="sidebar-user-role"><?= e(ucfirst($admin['role'] ?? 'admin')) ?></div>
      </div>
      <a href="<?= APP_URL ?>/admin/logout.php" class="sidebar-user-logout" title="Logout">↩</a>
    </div>
  </div>
</nav>

<!-- MAIN -->
<div class="admin-main">
  <!-- TOP BAR -->
  <div class="admin-topbar">
    <button onclick="document.getElementById('adminSidebar').classList.toggle('open')"
            style="display:none;background:none;border:none;cursor:pointer;padding:4px"
            id="sidebarToggle">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <span class="topbar-title"><?= e($adminPageTitle ?? 'Dashboard') ?></span>
    <div class="topbar-actions">
      <a href="<?= APP_URL ?>/admin/posts_form.php" class="topbar-btn primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Artikel Baru
      </a>
      <a href="<?= APP_URL ?>/" target="_blank" class="topbar-btn outline">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Website
      </a>
    </div>
  </div>
  <!-- CONTENT START -->
  <div class="admin-content">
  <?= renderFlash() ?>
