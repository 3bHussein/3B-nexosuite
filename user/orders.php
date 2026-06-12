<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
websitePermissionGuard('customer_orders');
$user = currentUser();
$stmt = getDB()->prepare('SELECT * FROM ' . table('orders') . ' WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();
siteHeader('My Orders', 'login');
?>
<h1 class="mb-4">My Orders</h1><div class="table-card table-responsive"><table class="table"><thead><tr><th>Order</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?php echo esc($order['order_number']); ?></td><td><?php echo money($order['total']); ?></td><td><?php echo esc($order['payment_status']); ?></td><td><?php echo esc($order['order_status']); ?></td><td><?php echo esc(date('M d, Y', strtotime($order['created_at']))); ?></td><td><a class="btn btn-outline-primary btn-sm" href="<?php echo esc(SITE_URL); ?>/user/order-details.php?id=<?php echo (int)$order['id']; ?>">Details</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php siteFooter(); ?>