<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
websitePermissionGuard('customer_orders');
$user = currentUser();
$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM ' . table('orders') . ' WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([(int)($_GET['id'] ?? 0), $user['id']]);
$order = $stmt->fetch();
if (!$order) { flash('error', 'Order not found.'); redirect(SITE_URL . '/user/orders.php'); }
$stmt = $pdo->prepare('SELECT * FROM ' . table('order_items') . ' WHERE order_id = ?');
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();
siteHeader('Order Details', 'login');
?>
<h1 class="mb-4">Order <?php echo esc($order['order_number']); ?></h1><div class="row g-4"><div class="col-lg-8"><div class="table-card table-responsive"><table class="table"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?php echo esc($item['product_name']); ?></td><td><?php echo (int)$item['quantity']; ?></td><td><?php echo money($item['price']); ?></td><td><?php echo money($item['total']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-lg-4"><div class="card-clean p-4"><p><strong>Status:</strong> <?php echo esc($order['order_status']); ?></p><p><strong>Payment:</strong> <?php echo esc($order['payment_status']); ?></p><p><strong>Total:</strong> <?php echo money($order['total']); ?></p></div></div></div>
<?php siteFooter(); ?>