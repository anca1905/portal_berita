<?php
// ============================================================
// XyronNews – Helper Functions
// ============================================================

require_once __DIR__ . '/db.php';

/* ---------- SANITIZE & FORMAT ---------- */

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(strip_tags($str));
}

function slugify(string $str): string {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60)          return $diff . ' detik lalu';
    if ($diff < 3600)        return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)       return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800)      return floor($diff / 86400) . ' hari lalu';
    if ($diff < 2592000)     return floor($diff / 604800) . ' minggu lalu';
    return date('d M Y', $time);
}

function formatDate(string $datetime, string $format = 'd M Y, H:i'): string {
    return date($format, strtotime($datetime));
}

function excerpt(string $html, int $length = 160): string {
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function readingTime(string $html): string {
    $words = str_word_count(strip_tags($html));
    $minutes = max(1, ceil($words / 200));
    return $minutes . ' menit baca';
}

function formatViews(int $views): string {
    if ($views >= 1000000) return round($views / 1000000, 1) . 'jt';
    if ($views >= 1000)    return round($views / 1000, 1) . 'rb';
    return (string) $views;
}

function postUrl(string $slug): string {
    return APP_URL . '/berita/' . urlencode($slug);
}

function categoryUrl(string $slug, int $page = 1): string {
    if ($page > 1) {
        return APP_URL . '/kategori/' . urlencode($slug) . '/halaman-' . $page;
    }
    return APP_URL . '/kategori/' . urlencode($slug);
}

function postImage(?string $image): string {
    if (!$image) return DEFAULT_POST_IMAGE;
    // If it's already a full URL (external), return as-is
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }
    return UPLOADS_URL . 'posts/' . $image;
}

function adImage(?string $image): string {
    if (!$image) return '';
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }
    return UPLOADS_URL . 'ads/' . $image;
}

/* ---------- SETTINGS ---------- */

function getSettings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    
    $settings = [
        'site_name' => 'XyronNews',
        'site_tagline' => ' ',
        'site_logo' => '',
        'ad_whatsapp' => '081234567890',
        'ad_email' => 'iklan@xyronnews.com',
        'ad_price_advertorial' => '250.000',
        'ad_price_header' => '500.000',
        'ad_price_sidebar' => '300.000',
    ];

    try {
        $db = getDB();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $t) {
        // Fallback to default array
    }
    
    $cache = $settings;
    return $cache;
}

function getLogoUrl(): string {
    $settings = getSettings();
    if (!empty($settings['site_logo'])) {
        return UPLOADS_URL . 'logo/' . $settings['site_logo'];
    }
    // Fallback if no logo is uploaded (used as favicon or fallback image)
    return APP_URL . '/assets/default-logo.png'; 
}

// Inisialisasi konstanta dinamis
$__globalSettings = getSettings();
if (!defined('APP_NAME')) define('APP_NAME', $__globalSettings['site_name'] ?: 'XyronNews');
if (!defined('APP_TAGLINE')) define('APP_TAGLINE', $__globalSettings['site_tagline'] ?: ' ');

/* ---------- CATEGORIES ---------- */

function getCategories(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories ORDER BY menu_order ASC");
    $cache = $stmt->fetchAll();
    return $cache;
}

function getCategoryBySlug(string $slug): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function getCategoryById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/* ---------- POSTS ---------- */

function getPublishedPosts(int $limit = 10, int $offset = 0, ?int $categoryId = null): array {
    $db = getDB();
    $where = "p.status = 'published'";
    $params = [];
    if ($categoryId) {
        $where .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }
    $params[] = $limit;
    $params[] = $offset;
    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   u.full_name AS author_name
            FROM posts p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.author_id = u.id
            WHERE {$where}
            ORDER BY p.published_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getPostBySlug(string $slug): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                                  u.full_name AS author_name
                           FROM posts p
                           LEFT JOIN categories c ON p.category_id = c.id
                           LEFT JOIN users u ON p.author_id = u.id
                           WHERE p.slug = ? AND p.status = 'published'");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function getPostById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                                  u.full_name AS author_name
                           FROM posts p
                           LEFT JOIN categories c ON p.category_id = c.id
                           LEFT JOIN users u ON p.author_id = u.id
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function incrementViews(int $postId): void {
    $db = getDB();
    $db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$postId]);
}

function getRelatedPosts(int $postId, int $categoryId, int $limit = 4): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM posts p
                           LEFT JOIN categories c ON p.category_id = c.id
                           WHERE p.category_id = ? AND p.id != ? AND p.status = 'published'
                           ORDER BY p.published_at DESC LIMIT ?");
    $stmt->execute([$categoryId, $postId, $limit]);
    return $stmt->fetchAll();
}

function getPopularPosts(int $limit = 5): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM posts p
                           LEFT JOIN categories c ON p.category_id = c.id
                           WHERE p.status = 'published'
                           ORDER BY p.views DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function searchPosts(string $keyword, int $limit = 12, int $offset = 0): array {
    $db = getDB();
    $like = '%' . $keyword . '%';
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                                  u.full_name AS author_name
                           FROM posts p
                           LEFT JOIN categories c ON p.category_id = c.id
                           LEFT JOIN users u ON p.author_id = u.id
                           WHERE p.status = 'published'
                             AND (p.title LIKE ? OR p.content LIKE ? OR p.tags LIKE ?)
                           ORDER BY p.published_at DESC
                           LIMIT ? OFFSET ?");
    $stmt->execute([$like, $like, $like, $limit, $offset]);
    return $stmt->fetchAll();
}

function countPosts(?int $categoryId = null, string $status = 'published'): int {
    $db = getDB();
    $where = "status = ?";
    $params = [$status];
    if ($categoryId) {
        $where .= " AND category_id = ?";
        $params[] = $categoryId;
    }
    $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE {$where}");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function countSearchResults(string $keyword): int {
    $db = getDB();
    $like = '%' . $keyword . '%';
    $stmt = $db->prepare("SELECT COUNT(*) FROM posts 
                           WHERE status = 'published'
                             AND (title LIKE ? OR content LIKE ? OR tags LIKE ?)");
    $stmt->execute([$like, $like, $like]);
    return (int) $stmt->fetchColumn();
}

/* ---------- COMMENTS ---------- */

function getApprovedComments(int $postId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM comments 
                           WHERE post_id = ? AND status = 'approved'
                           ORDER BY created_at ASC");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function countComments(int $postId): int {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ? AND status = 'approved'");
    $stmt->execute([$postId]);
    return (int) $stmt->fetchColumn();
}

function addComment(int $postId, string $name, string $comment): bool {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO comments (post_id, name, comment, status, created_at) 
                           VALUES (?, ?, ?, 'approved', NOW())");
    return $stmt->execute([$postId, sanitize($name), sanitize($comment)]);
}

/* ---------- ADS & MONETISASI ---------- */

function getAdSetting(string $key, string $default = ''): string {
    global $__globalSettings;
    if (isset($__globalSettings[$key]) && $__globalSettings[$key] !== '') {
        return (string) $__globalSettings[$key];
    }
    return $default;
}

function getAdWhatsAppNumber(): string {
    $phone = getAdSetting('ad_whatsapp', '081234567890');
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($clean, '0')) {
        $clean = '62' . substr($clean, 1);
    }
    return $clean ?: '6281234567890';
}

function getAdWhatsAppLink(string $package = ''): string {
    $phone = getAdWhatsAppNumber();
    $appName = defined('APP_NAME') ? APP_NAME : 'Portal Berita';
    $msg = "Halo Tim Redaksi " . $appName . ", saya tertarik untuk pasang iklan / rilis berita";
    if ($package) {
        $msg .= " untuk paket: " . $package;
    }
    $msg .= ". Mohon info ketersediaan slot & persyaratannya. Terima kasih.";
    return "https://wa.me/{$phone}?text=" . rawurlencode($msg);
}

function getAd(string $position): ?array {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM ads WHERE position = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$position]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $t) {
        return null;
    }
}

function renderAd(string $position, string $class = '', bool $showPlaceholder = true): string {
    $ad = getAd($position);
    if ($ad) {
        $imgSrc = e($ad['image_url']);
        $target = e($ad['target_url'] ?? '#');
        $title  = e($ad['title']);
        return "<a href=\"{$target}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"ad-link {$class}\" title=\"{$title}\">
                    <img src=\"{$imgSrc}\" alt=\"{$title}\" loading=\"lazy\" style=\"max-width:100%;height:auto;border-radius:var(--radius-md);display:block;margin:0 auto;\">
                </a>";
    }

    if (!$showPlaceholder) return '';

    $dimensionLabels = [
        'header_728x90'     => ['728x90', 'Header Utama'],
        'standard_468x60'   => ['468x60', 'Tengah Berita'],
        'standard_468x100'  => ['468x100', 'Bawah Berita'],
        'standard_300x250'  => ['300x250', 'Sidebar Rekomendasi'],
        'home_left_160x200' => ['160x200', 'Banner Samping'],
        'home_left_160x600' => ['160x600', 'Skyscraper Kiri'],
    ];

    $dim = $dimensionLabels[$position][0] ?? 'Display';
    $iklanUrl = APP_URL . '/pasang-iklan.php';
    $appName = defined('APP_NAME') ? APP_NAME : 'Portal Berita';

    if ($position === 'standard_300x250') {
        return "<div class=\"ad-placeholder-box ad-box-square {$class}\">
            <div class=\"ad-placeholder-tag\">RUANG IKLAN TERSEDIA</div>
            <div class=\"ad-placeholder-title\">Pasang Banner Usaha Anda di Sini ({$dim})</div>
            <p class=\"ad-placeholder-desc\">Tingkatkan omset & brand awareness bisnis Anda ke ribuan pembaca setia setiap hari.</p>
            <a href=\"{$iklanUrl}\" class=\"ad-placeholder-btn\">
                <span>Lihat Tarif & Pasang</span>
                <svg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\"><polyline points=\"9 18 15 12 9 6\"/></svg>
            </a>
        </div>";
    }

    return "<a href=\"{$iklanUrl}\" class=\"ad-placeholder-box ad-box-horizontal {$class}\" title=\"Sewa slot iklan {$dim} di " . e($appName) . "\">
        <div class=\"ad-placeholder-content\">
            <span class=\"ad-placeholder-badge\">SLOT IKLAN TERSEDIA ({$dim})</span>
            <span class=\"ad-placeholder-text\"><strong>Promosikan Bisnis & Instansi Anda di Sini!</strong> Jangkau ribuan audiens potensial setiap hari.</span>
        </div>
        <span class=\"ad-placeholder-action\">Pasang Iklan →</span>
    </a>";
}

/* ---------- PAGINATION ---------- */

function paginate(int $total, int $perPage, int $currentPage, string $urlPattern): string {
    $pages = (int) ceil($total / $perPage);
    if ($pages <= 1) return '';

    $html = '<nav class="pagination-nav" aria-label="Navigasi halaman"><ul class="pagination">';
    // Prev
    if ($currentPage > 1) {
        $prevUrl = str_replace('{page}', $currentPage - 1, $urlPattern);
        $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$prevUrl}\">← Sebelumnya</a></li>";
    }
    // Pages
    for ($i = max(1, $currentPage - 2); $i <= min($pages, $currentPage + 2); $i++) {
        $url = str_replace('{page}', $i, $urlPattern);
        $active = $i === $currentPage ? ' active' : '';
        $html .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"{$url}\">{$i}</a></li>";
    }
    // Next
    if ($currentPage < $pages) {
        $nextUrl = str_replace('{page}', $currentPage + 1, $urlPattern);
        $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$nextUrl}\">Selanjutnya →</a></li>";
    }
    $html .= '</ul></nav>';
    return $html;
}

/* ---------- CSRF ---------- */

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

/* ---------- FLASH MESSAGES ---------- */

function flashMessage(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function renderFlash(): string {
    if (empty($_SESSION['flash'])) return '';
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = in_array($flash['type'], ['success','error','warning','info']) ? $flash['type'] : 'info';
    $map = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    $cls = $map[$type];
    return "<div class=\"alert alert-{$cls} alert-dismissible fade show\" role=\"alert\">
                " . e($flash['message']) . "
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
            </div>";
}

/* ---------- PARTNERS ---------- */

function getPartners(): array {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM partners WHERE is_active = 1 ORDER BY menu_order ASC, id DESC");
    return $stmt->fetchAll();
}
