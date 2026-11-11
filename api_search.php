<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode(['data' => []]);
    exit;
}

try {
    $db = getDB();
    $like = '%' . $q . '%';
    $stmt = $db->prepare(
        "SELECT p.id, p.title, p.slug, p.image, p.published_at, c.name AS category_name
         FROM posts p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.status = 'published' AND (p.title LIKE ? OR p.tags LIKE ?)
         ORDER BY p.published_at DESC
         LIMIT 6"
    );
    $stmt->execute([$like, $like]);
    $posts = $stmt->fetchAll();

    $results = array_map(function($p) {
        return [
            'id'       => (int)$p['id'],
            'title'    => $p['title'],
            'slug'     => $p['slug'],
            'url'      => APP_URL . '/berita.php?slug=' . urlencode($p['slug']),
            'category' => $p['category_name'] ?? 'Berita',
            'image'    => postImage($p['image']),
            'time'     => timeAgo($p['published_at']),
        ];
    }, $posts);

    echo json_encode(['data' => $results]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
}
