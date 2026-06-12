<?php
$pageTitle='Tenant Usage';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('tenant_usage');
$pdo=getDB();
$companyId=(int)($_GET['company_id']??(operationalScope($pdo)['company_id']??0));
$companies=$pdo->query('SELECT id,company_code,company_name FROM '.table('companies').' WHERE status="active" ORDER BY company_name')->fetchAll();
if(isset($_POST['capture'])){
  try{captureTenantUsage($pdo,$companyId);flash('success','Usage snapshot captured.');}catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'tenant-usage']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/tenant-usage.php?company_id='.$companyId);
}
$status=tenantUsageLimitStatus($pdo,$companyId);
$historyStmt=$pdo->prepare('SELECT * FROM '.table('tenant_usage_snapshots').' WHERE company_id=? ORDER BY snapshot_date DESC LIMIT 30');$historyStmt->execute([$companyId]);$history=$historyStmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">SaaS Usage Metering</div><h2 class="h4 mb-1">Tenant Usage</h2><p class="text-secondary mb-0">Compare current company usage against subscription plan limits.</p></div>
  <form class="d-flex gap-2"><select class="form-select" name="company_id"><?php foreach($companies as $c): ?><option value="<?php echo (int)$c['id']; ?>" <?php echo $companyId===(int)$c['id']?'selected':''; ?>><?php echo esc($c['company_code'].' · '.$c['company_name']); ?></option><?php endforeach; ?></select><button class="btn btn-brand">Load</button></form>
</div>
<div class="row g-4 mb-4">
<?php foreach(($status['limits']??[]) as $key=>$limit): $used=(float)($status['usage'][$key]??0); $pct=$limit>0?min(100,round($used/$limit*100)):0; ?>
  <div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(str_replace('_',' ',ucwords($key,'_'))); ?></div><div class="metric-sm"><?php echo esc($used); ?> / <?php echo esc($limit); ?></div><div class="progress mt-2" style="height:8px"><div class="progress-bar" style="width:<?php echo (int)$pct; ?>%"></div></div></div></div>
<?php endforeach; ?>
</div>
<?php if(!empty($status['violations'])): ?><div class="alert alert-danger"><strong>Limit warnings:</strong> <?php echo esc(implode(' | ',$status['violations'])); ?></div><?php else: ?><div class="alert alert-success">No active plan limit violations detected.</div><?php endif; ?>
<form method="post" class="mb-4"><button class="btn btn-outline-primary" name="capture" value="1">Capture Usage Snapshot Now</button></form>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Usage History</div><h2 class="h5 mb-0">Daily Snapshots</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Users</th><th>Branches</th><th>Warehouses</th><th>Products</th><th>Storage MB</th><th>API Calls</th></tr></thead><tbody><?php foreach($history as $row): ?><tr><td><?php echo esc($row['snapshot_date']); ?></td><td><?php echo (int)$row['user_count']; ?></td><td><?php echo (int)$row['branch_count']; ?></td><td><?php echo (int)$row['warehouse_count']; ?></td><td><?php echo (int)$row['product_count']; ?></td><td><?php echo number_format((float)$row['storage_used_mb'],2); ?></td><td><?php echo (int)$row['api_calls_month']; ?></td></tr><?php endforeach; ?><?php if(!$history): ?><tr><td colspan="7" class="text-secondary">No snapshots yet.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>