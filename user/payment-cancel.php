<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
$user = currentUser();
$pdo = getDB();
$orderId = (int)($_GET['order'] ?? 0);
$stmt = $pdo->prepare('UPDATE ' . table('orders') . ' SET payment_status="cancelled" WHERE id = ? AND user_id = ? AND payment_status="pending"');
$stmt->execute([$orderId, (int)$user['id']]);
flash('error', 'Payment was cancelled. The order remains visible for admin review.');
redirect(SITE_URL . '/user/orders.php');