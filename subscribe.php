<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) { flash('error', 'Enter a valid email address.'); redirect(SITE_URL . '/index.php'); }
    $stmt = $pdo->prepare('INSERT INTO ' . table('newsletter') . ' (email,status) VALUES (?,"subscribed") ON DUPLICATE KEY UPDATE status="subscribed"');
    $stmt->execute([$email]);
    flash('success', 'You are subscribed.');
    redirect(SITE_URL . '/index.php');
}
redirect(SITE_URL . '/index.php');