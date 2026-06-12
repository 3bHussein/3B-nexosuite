<?php
$pageTitle='Product Comparison Control';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('product_comparison_control');
$pdo=getDB();
$sessions=$pdo->query('SELECT s.*,u.email FROM '.table('product_comparison_sessions').' s LEFT JOIN '.table('users').' u ON u.id=s.user_id ORDER BY s.created_at DESC LIMIT 200')->fetchAll();
$items=$pdo->query('SELECT ci.*,cs.compare_number,p.sku,p.name,p.price,p.stock FROM '.table('product_comparison_items').' ci LEFT JOIN '.table('product_comparison_sessions').' cs ON cs.id=ci.product_comparison_session_id LEFT JOIN '.table('products').' p ON p.id=ci.product_id ORDER BY ci.created_at DESC LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="mb-4"><div class="erp-kicker">Buying Comparison</div><h2 class="h4 mb-1">Product Comparison Control</h2><p class="text-secondary mb-0">Review comparison sessions and products customers compare before enquiry or order.</p></div>
<div class="row g-4"><div class="col-xl-5"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Sessions</h2><table class="table"><thead><tr><th>Compare</th><th>User</th><th>Status</th></tr></thead><tbody><?php foreach($sessions as $s): ?><tr><td><strong><?php echo esc($s['compare_number']); ?></strong><div class="small text-secondary"><?php echo esc($s['created_at']); ?></div></td><td><?php echo esc($s['email']?:'Guest'); ?></td><td><span class="badge bg-<?php echo esc(statusTone($s['status'])); ?>"><?php echo esc($s['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-7"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Compared Products</h2><table class="table"><thead><tr><th>Compare</th><th>Product</th><th>Price</th><th>Stock</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?php echo esc($i['compare_number']); ?></td><td><?php echo esc($i['sku'].' · '.$i['name']); ?></td><td><?php echo money($i['price']); ?></td><td><?php echo (int)$i['stock']; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>