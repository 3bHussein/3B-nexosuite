<?php
$pageTitle='Executive BI Dashboard';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('executive_bi');
$pdo=getDB();

/*
  Runtime hotfix:
  This page is an executive consolidation screen and may be opened on databases that
  were upgraded from older priorities. A missing optional table/column should not
  crash the whole dashboard. These helpers safely return defaults and keep the page alive.
*/
$dashboardWarnings = [];

$safeScalar = function(string $sql, float $default = 0) use ($pdo, &$dashboardWarnings) {
    try {
        return (float)$pdo->query($sql)->fetchColumn();
    } catch (Throwable $e) {
        $dashboardWarnings[] = $e->getMessage();
        if (function_exists('recordSystemError')) {
            try { recordSystemError($pdo, $e, ['page'=>'executive-dashboard','sql'=>$sql]); } catch (Throwable $ignored) {}
        }
        return $default;
    }
};

$safeInt = function(string $sql, int $default = 0) use ($safeScalar) {
    return (int)$safeScalar($sql, $default);
};

$safeRows = function(string $sql) use ($pdo, &$dashboardWarnings) {
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (Throwable $e) {
        $dashboardWarnings[] = $e->getMessage();
        if (function_exists('recordSystemError')) {
            try { recordSystemError($pdo, $e, ['page'=>'executive-dashboard','sql'=>$sql]); } catch (Throwable $ignored) {}
        }
        return [];
    }
};

$revenue = $safeScalar('SELECT COALESCE(SUM(total),0) FROM '.table('invoices').' WHERE status IN ("approved","sent","partial","paid")');
$receivables = $safeScalar('SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE balance_due>0 AND status<>"cancelled"');
$cash = $safeScalar('SELECT COALESCE(SUM(amount),0) FROM '.table('payments').' WHERE status="received"');
$ap = $safeScalar('SELECT COALESCE(SUM(balance_due),0) FROM '.table('supplier_invoices').' WHERE balance_due>0 AND status<>"cancelled"');
$inventoryValue = $safeScalar('SELECT COALESCE(SUM(stock_value),0) FROM '.table('warehouse_stock'));
$pipeline = $safeScalar('SELECT COALESCE(SUM(value_amount*probability/100),0) FROM '.table('sales_opportunities').' WHERE status="open"');
if ($pipeline <= 0) {
    $pipeline = $safeScalar('SELECT COALESCE(SUM(estimated_value*probability/100),0) FROM '.table('crm_leads').' WHERE status NOT IN ("won","lost")');
}
$openApprovals = $safeInt('SELECT COUNT(*) FROM '.table('approval_requests').' WHERE status="pending"');
$approvalValue = $safeScalar('SELECT COALESCE(SUM(request_amount),0) FROM '.table('approval_requests').' WHERE status="pending"');
$jobProfit = $safeScalar('SELECT COALESCE(SUM(total-actual_cost),0) FROM '.table('job_cards').' WHERE status IN ("completed","invoiced")');
$projectMargin = $safeScalar('SELECT COALESCE(SUM(margin_amount),0) FROM '.table('projects').' WHERE status<>"cancelled"');
$budgetVariance = $safeScalar('SELECT COALESCE(SUM(variance_amount),0) FROM '.table('budget_periods'));

// Prefer warehouse stock if available, then fallback to older inventory table.
$stockAlerts = $safeRows('SELECT p.sku,p.name,ws.quantity,ws.reorder_level FROM '.table('warehouse_stock').' ws LEFT JOIN '.table('products').' p ON p.id=ws.product_id WHERE ws.quantity<=ws.reorder_level ORDER BY ws.quantity ASC LIMIT 8');
if (!$stockAlerts) {
    $stockAlerts = $safeRows('SELECT p.sku,p.name,i.quantity,i.reorder_level FROM '.table('inventory').' i LEFT JOIN '.table('products').' p ON p.id=i.product_id WHERE i.quantity<=i.reorder_level ORDER BY i.quantity ASC LIMIT 8');
}

$approvalRows = $safeRows('SELECT request_number,document_type,document_number,request_amount,current_step,submitted_at FROM '.table('approval_requests').' WHERE status="pending" ORDER BY submitted_at ASC LIMIT 8');
$budgetRows = $safeRows('SELECT bp.*,cc.cost_center_code,cc.cost_center_name FROM '.table('budget_periods').' bp LEFT JOIN '.table('cost_centers').' cc ON cc.id=bp.cost_center_id ORDER BY bp.variance_amount ASC LIMIT 8');
$salesRows = $safeRows('SELECT DATE(created_at) order_day,COALESCE(SUM(total),0) total_value,COUNT(*) order_count FROM '.table('sales_orders').' GROUP BY DATE(created_at) ORDER BY order_day DESC LIMIT 10');

$dashboardWarnings = array_values(array_unique(array_slice($dashboardWarnings, 0, 6)));

$healthScore=100;
if($receivables>$revenue*0.45 && $revenue>0){$healthScore-=15;}
if($openApprovals>10){$healthScore-=10;}
if($budgetVariance<0){$healthScore-=10;}
if($inventoryValue>0 && count($stockAlerts)>5){$healthScore-=5;}
$healthScore=max(0,min(100,$healthScore));
include dirname(__DIR__).'/header.php';
?>
<?php if(!empty($dashboardWarnings)): ?><div class="alert alert-warning"><strong>Dashboard warning:</strong> Some optional module data could not be loaded, but the dashboard is running. Latest issue: <?php echo esc($dashboardWarnings[0]); ?></div><?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Executive Intelligence</div><h2 class="h4 mb-1">Executive BI Dashboard</h2><p class="text-secondary mb-0">Consolidated commercial, finance, inventory, service, project, and governance indicators.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/report-builder.php"><?php echo t('Report Builder', 'منشئ التقارير'); ?></a><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/data-import-export.php">Export Data</a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Business Health Score</div><div class="metric <?php echo $healthScore>=75?'money-positive':($healthScore>=50?'':'money-negative'); ?>"><?php echo (int)$healthScore; ?>%</div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Revenue</div><div class="metric-sm"><?php echo money($revenue); ?></div><div class="small text-secondary">Cash received <?php echo money($cash); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Receivables</div><div class="metric-sm money-negative"><?php echo money($receivables); ?></div><div class="small text-secondary">AP exposure <?php echo money($ap); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Inventory Value</div><div class="metric-sm"><?php echo money($inventoryValue); ?></div><div class="small text-secondary">Low stock lines <?php echo count($stockAlerts); ?></div></div></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Pipeline</div><div class="metric-sm"><?php echo money($pipeline); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Pending Approvals</div><div class="metric-sm"><?php echo (int)$openApprovals; ?></div><div class="small text-secondary"><?php echo money($approvalValue); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Service Gross Profit</div><div class="metric-sm <?php echo $jobProfit>=0?'money-positive':'money-negative'; ?>"><?php echo money($jobProfit); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Project Margin</div><div class="metric-sm <?php echo $projectMargin>=0?'money-positive':'money-negative'; ?>"><?php echo money($projectMargin); ?></div><div class="small text-secondary">Budget variance <?php echo money($budgetVariance); ?></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-6"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Governance Queue</div><h2 class="h5 mb-0">Oldest Pending Approvals</h2></div></div><table class="table align-middle"><thead><tr><th>Request</th><th>Document</th><th>Value</th><th>Step</th></tr></thead><tbody><?php foreach($approvalRows as $row): ?><tr><td><?php echo esc($row['request_number']); ?></td><td><?php echo esc(approvalDocumentLabel($row['document_type']).' '.$row['document_number']); ?></td><td><?php echo money($row['request_amount']); ?></td><td><?php echo (int)$row['current_step']; ?></td></tr><?php endforeach; ?><?php if(!$approvalRows): ?><tr><td colspan="4" class="text-secondary">No pending approvals.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="col-xl-6"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Budget Risk</div><h2 class="h5 mb-0">Lowest Variance Lines</h2></div></div><table class="table align-middle"><thead><tr><th>Cost Center</th><th>Budget</th><th>Actual</th><th>Variance</th></tr></thead><tbody><?php foreach($budgetRows as $row): ?><tr><td><?php echo esc(($row['cost_center_code']?:'').' · '.($row['cost_center_name']?:'')); ?></td><td><?php echo money($row['budget_amount']); ?></td><td><?php echo money($row['actual_amount']); ?></td><td class="<?php echo (float)$row['variance_amount']>=0?'money-positive':'money-negative'; ?>"><?php echo money($row['variance_amount']); ?></td></tr><?php endforeach; ?><?php if(!$budgetRows): ?><tr><td colspan="4" class="text-secondary">No budget lines configured.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="col-xl-6"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Low Stock Watch</div><h2 class="h5 mb-0">Inventory Alerts</h2></div></div><table class="table align-middle"><thead><tr><th>Product</th><th>Qty</th><th>Reorder</th></tr></thead><tbody><?php foreach($stockAlerts as $row): ?><tr><td><?php echo esc(($row['sku']?:'').' · '.($row['name']?:'')); ?></td><td><?php echo number_format((float)$row['quantity'],2); ?></td><td><?php echo number_format((float)$row['reorder_level'],2); ?></td></tr><?php endforeach; ?><?php if(!$stockAlerts): ?><tr><td colspan="3" class="text-secondary">No low-stock alerts.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="col-xl-6"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Recent Sales Order Trend</div><h2 class="h5 mb-0">Daily Order Value</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Orders</th><th>Value</th></tr></thead><tbody><?php foreach($salesRows as $row): ?><tr><td><?php echo esc($row['order_day']); ?></td><td><?php echo (int)$row['order_count']; ?></td><td><?php echo money($row['total_value']); ?></td></tr><?php endforeach; ?><?php if(!$salesRows): ?><tr><td colspan="3" class="text-secondary">No sales order trend yet.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>