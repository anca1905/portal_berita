<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$db = getDB();
$posts = $db->query(
    "SELECT p.*, c.name AS category_name, u.full_name AS author_name 
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.author_id = u.id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC
     LIMIT 25"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title><?= e(APP_NAME) ?></title>
    <link><?= APP_URL ?>/</link>
    <description><?= e(APP_TAGLINE) ?></description>
    <language>id-ID</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <generator><?= e(APP_NAME) ?> News Engine</generator>
    
    <?php foreach ($posts as $post): 
        $url = APP_URL . '/berita.php?slug=' . urlencode($post['slug']);
        $img = postImage($post['image']);
    ?>
    <item>
      <title><![CDATA[<?= $post['title'] ?>]]></title>
      <link><?= $url ?></link>
      <guid isPermaLink="true"><?= $url ?></guid>
      <pubDate><?= date(DATE_RSS, strtotime($post['published_at'])) ?></pubDate>
      <dc:creator><![CDATA[<?= $post['author_name'] ?? 'Redaksi' ?>]]></dc:creator>
      <category><![CDATA[<?= $post['category_name'] ?? 'Berita' ?>]]></category>
      <description><![CDATA[<?= excerpt($post['summary'] ?: $post['content'], 250) ?>]]></description>
      <?php if ($img): ?>
      <enclosure url="<?= $img ?>" type="image/jpeg" length="0" />
      <?php endif; ?>
    </item>
    <?php endforeach; ?>
  </channel>
</rss>
