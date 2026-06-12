<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$user=currentUser();if(!$user || ($user['role']??'')!=='vendor'){redirect(SITE_URL.'/vendor/login.php');}
$pdo=getDB();$a=$pdo->prepare('SELECT supplier_id FROM '.table('supplier_user_access').' WHERE user_id=? AND status="active" LIMIT 1');$a->execute([$user['id']]);$supplierId=(int)($a->fetchColumn()?:0);
$stmt=$pdo->prepare('SELECT * FROM '.table('purchase_orders').' WHERE supplier_id=? ORDER BY created_at DESC');$stmt->execute([$supplierId]);$rows=$stmt->fetchAll();
siteHeader('Vendor Purchase Orders','login');
?>
<h1 class="mb-4">Purchase Orders</h1><div class="table-card table-responsive"><table class="table align-middle"><thead><tr><th>PO</th><th>Date</th><th>Expected</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $po): ?><tr><td><strong><?php echo esc($po['po_number']); ?></strong></td><td><?php echo esc($po['order_date']); ?></td><td><?php echo esc($po['expected_date']); ?></td><td><?php echo money($po['total']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($po['status'])); ?>"><?php echo esc($po['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-secondary">No purchase orders assigned.</td></tr><?php endif; ?></tbody></table></div>
<?php siteFooter(); ?>