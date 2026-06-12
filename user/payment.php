<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
websitePermissionGuard('customer_invoices');
$user = currentUser();
$stmt = getDB()->prepare('SELECT * FROM ' . table('orders') . ' WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([(int)($_GET['order'] ?? $_SESSION['last_order_id'] ?? 0), $user['id']]);
$order = $stmt->fetch();
if (!$order) { flash('error', 'Order not found.'); redirect(SITE_URL . '/user/orders.php'); }
siteHeader('Payment', 'checkout');
?>
<div class="card-clean p-5 mx-auto" style="max-width:760px"><h1 class="h2">Payment Step</h1><p>Order <strong><?php echo esc($order['order_number']); ?></strong> is awaiting payment confirmation.</p><p class="display-6 fw-bold"><?php echo money($order['total']); ?></p><div class="d-flex flex-wrap gap-2"><a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/user/payment-success.php?order=<?php echo (int)$order['id']; ?>">Mark as Paid (Demo)</a><a class="btn btn-outline-danger" href="<?php echo esc(SITE_URL); ?>/user/payment-cancel.php?order=<?php echo (int)$order['id']; ?>">Cancel Payment</a></div><p class="muted-small mt-3 mb-0">Replace this demo page with Stripe, PayPal, Telr, Network, or your chosen gateway integration.</p></div>
<?php siteFooter(); ?>