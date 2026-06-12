<?php
$pageTitle='Wishlist Control';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('wishlist_control');
$pdo=getDB();
$wishlists=$pdo->query('SELECT w.*,u.email user_email,c.customer_code,c.contact_name FROM '.table('wishlists').' w LEFT JOIN '.table('users').' u ON u.id=w.user_id LEFT JOIN '.table('customers').' c ON c.id=w.customer_id ORDER BY w.created_at DESC LIMIT 200')->fetchAll();
$items=$pdo->query('SELECT wi.*,w.wishlist_number,p.sku,p.name,p.price FROM '.table('wishlist_items').' wi LEFT JOIN '.table('wishlists').' w ON w.id=wi.wishlist_id LEFT JOIN '.table('products').' p ON p.id=wi.product_id ORDER BY wi.created_at DESC LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="mb-4"><div class="erp-kicker">Customer Intent</div><h2 class="h4 mb-1">Wishlist Control</h2><p class="text-secondary mb-0">Review wishlist sessions and products customers saved for later.</p></div>
<div class="row g-4"><div class="col-xl-5"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Wishlists</h2><table class="table"><thead><tr><th>Wishlist</th><th>User/Customer</th><th>Status</th></tr></thead><tbody><?php foreach($wishlists as $w): ?><tr><td><strong><?php echo esc($w['wishlist_number']); ?></strong><div class="small text-secondary"><?php echo esc($w['wishlist_name'].' · '.$w['created_at']); ?></div></td><td><?php echo esc($w['user_email']?:$w['customer_code']?:'Guest'); ?></td><td><span class="badge bg-<?php echo esc(statusTone($w['status'])); ?>"><?php echo esc($w['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-7"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Wishlist Items</h2><table class="table"><thead><tr><th>Wishlist</th><th>Product</th><th>Qty</th><th>Price</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?php echo esc($i['wishlist_number']); ?></td><td><?php echo esc($i['sku'].' · '.$i['name']); ?></td><td><?php echo esc($i['quantity']); ?></td><td><?php echo money($i['price']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>