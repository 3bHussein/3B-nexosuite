<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
websitePermissionGuard('customer_downloads');
$stmt = getDB()->prepare('SELECT * FROM ' . table('downloads') . ' WHERE id = ? AND active = 1 LIMIT 1');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$download = $stmt->fetch();
if (!$download) { flash('error', 'Download not found.'); redirect(SITE_URL . '/user/downloads.php'); }
$path = dirname(__DIR__) . '/uploads/downloads/' . basename($download['file_name']);
if (!is_file($path)) { flash('error', 'The file placeholder exists in the database, but the physical file has not been uploaded yet.'); redirect(SITE_URL . '/user/downloads.php'); }
$stmt = getDB()->prepare('UPDATE ' . table('downloads') . ' SET download_count = download_count + 1 WHERE id = ?');
$stmt->execute([$download['id']]);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;