<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
websitePermissionGuard('customer_downloads');

$pdo = getDB();
$user = currentUser();
$userId = (int)($user['id'] ?? 0);
$userEmail = trim((string)($user['email'] ?? ''));
$productId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT p.*,
                              o.id AS order_id,
                              o.order_number,
                              o.payment_status,
                              o.order_status
                       FROM ' . table('products') . ' p
                       INNER JOIN ' . table('order_items') . ' oi ON oi.product_id = p.id
                       INNER JOIN ' . table('orders') . ' o ON o.id = oi.order_id
                       WHERE p.id = ?
                         AND p.active = 1
                         AND (p.downloadable = 1 OR p.download_file <> "")
                         AND (o.user_id = ? OR o.customer_email = ?)
                         AND COALESCE(o.order_status, "") NOT IN ("cancelled","refunded","void")
                       ORDER BY o.created_at DESC
                       LIMIT 1');
$stmt->execute([$productId, $userId, $userEmail]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'This product download is not available for your account.');
    redirect(SITE_URL . '/user/downloads.php');
}

$downloadFile = trim((string)($product['download_file'] ?? ''));
if ($downloadFile === '') {
    flash('error', 'This product is marked downloadable, but no download file is configured.');
    redirect(SITE_URL . '/user/downloads.php');
}

if (preg_match('~^https?://~i', $downloadFile)) {
    header('Location: ' . $downloadFile);
    exit;
}

$root = dirname(__DIR__);
$raw = ltrim(str_replace('\\', '/', $downloadFile), '/');
$base = basename($raw);
$candidates = [
    $root . '/uploads/downloads/' . $base,
    $root . '/uploads/products/' . $base,
];

if (str_starts_with($raw, 'uploads/')) {
    $candidates[] = $root . '/' . $raw;
}

$filePath = null;
$uploadRoot = realpath($root . '/uploads');
foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if ($real && is_file($real)) {
        if ($uploadRoot && str_starts_with($real, $uploadRoot)) {
            $filePath = $real;
            break;
        }
    }
}

if (!$filePath) {
    flash('error', 'The product download file is configured, but the physical file was not found in uploads/downloads or uploads/products.');
    redirect(SITE_URL . '/user/downloads.php');
}

$fileName = basename($filePath);
$mime = 'application/octet-stream';
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detected = $finfo->file($filePath);
    if ($detected) {
        $mime = $detected;
    }
}

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;