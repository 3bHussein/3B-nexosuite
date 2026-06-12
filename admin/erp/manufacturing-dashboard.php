<?php
$pageTitle='Manufacturing Dashboard';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('manufacturing_dashboard');
$pdo=getDB();
$stats=[
 'boms'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('bill_of_materials'))->fetchColumn(),
 'planned'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('manufacturing_work_orders').' WHERE status="planned"')->fetchColumn(),
 'progress'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('manufacturing_work_orders').' WHERE status="in_progress"')->fetchColumn(),
 'completed'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('manufacturing_work_orders').' WHERE status="completed"')->fetchColumn(),
];
$workOrders=$pdo->query('SELECT wo.*,p.name product_name FROM '.table('manufacturing_work_orders').' wo LEFT JOIN '.table('products').' p ON p.id=wo.finished_product_id ORDER BY wo.created_at DESC LIMIT 15')->fetchAll();
$costs=$pdo->query('SELECT * FROM '.table('production_cost_rollups').' ORDER BY created_at DESC LIMIT 10')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Production Control</div><h2 class="h4 mb-1">Manufacturing Dashboard</h2><p class="text-secondary mb-0">BOMs, work orders, production progress, material issue, finished goods receipt, costing, and quality.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/manufacturing-work-orders.php">Create Work Order</a></div>
<div class="row g-4 mb-4"><?php foreach($stats as $label=>$value): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords($label)); ?></div><div class="metric-sm"><?php echo (int)$value; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Work Orders</h2><table class="table align-middle"><thead><tr><th>Work Order</th><th>Product</th><th>Qty</th><th>Completed</th><th>Status</th><th>Cost</th></tr></thead><tbody><?php foreach($workOrders as $wo): ?><tr><td><strong><?php echo esc($wo['work_order_number']); ?></strong><div class="small text-secondary"><?php echo esc($wo['priority']); ?></div></td><td><?php echo esc($wo['product_name']); ?></td><td><?php echo number_format((float)$wo['planned_quantity'],2); ?></td><td><?php echo number_format((float)$wo['completed_quantity'],2); ?></td><td><span class="badge bg-<?php echo esc(statusTone($wo['status'])); ?>"><?php echo esc($wo['status']); ?></span></td><td><?php echo money($wo['estimated_cost']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Latest Cost Rollups</h2><table class="table"><tbody><?php foreach($costs as $c): ?><tr><td><strong><?php echo esc($c['rollup_number']); ?></strong><div class="small text-secondary">Unit: <?php echo money($c['unit_cost']); ?></div></td><td><?php echo money($c['total_cost']); ?></td></tr><?php endforeach; ?><?php if(!$costs): ?><tr><td class="text-secondary">No cost rollups yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>