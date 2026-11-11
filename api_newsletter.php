<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = sanitize($input['email'] ?? $_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format alamat email tidak valid.']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
    echo json_encode(['success' => true, 'message' => 'Terima kasih! Email Anda telah terdaftar untuk buletin berita harian.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan langganan newsletter.']);
}
