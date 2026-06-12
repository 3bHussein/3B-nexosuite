<?php
$pageTitle='System Health';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('system_health');
$pdo=getDB();
if(isset($_POST['run_checks'])){
  try{runSystemHealthChecks($pdo);recordSecurityEvent($pdo,'system_health_check','System health checks were executed from the ERP UI.','info');flash('success','System health checks completed.');}catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'system-health']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/system-health.php');
}
$rows=$pdo->query('SELECT * FROM '.table('system_health_checks').' ORDER BY FIELD(status,"fail","warning","ok"),check_label')->fetchAll();
if(!$rows){$rows=runSystemHealthChecks($pdo);}
$summary=['ok'=>0,'warning'=>0,'fail'=>0];foreach($rows as $r){$summary[$r['status']]=$summary[$r['status']]??0;$summary[$r['status']]++;}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Deployment Readiness</div><h2 class="h4 mb-1">System Health Checks</h2><p class="text-secondary mb-0">Validate runtime, database, storage, backup path, SSL posture, and production readiness.</p></div><form method="post"><button class="btn btn-brand" name="run_checks" value="1">Run Checks Now</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">OK</div><div class="metric-sm money-positive"><?php echo (int)($summary['ok']??0); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Warnings</div><div class="metric-sm"><?php echo (int)($summary['warning']??0); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Failed</div><div class="metric-sm money-negative"><?php echo (int)($summary['fail']??0); ?></div></div></div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Health Matrix</div><h2 class="h5 mb-0">Latest Checks</h2></div></div><table class="table align-middle"><thead><tr><th>Check</th><th>Status</th><th>Value</th><th>Recommendation</th><th>Checked</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['check_label']); ?></strong><div class="small text-secondary"><?php echo esc($row['check_key']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(strtoupper($row['status'])); ?></span></td><td><?php echo esc($row['value_text']); ?></td><td><?php echo esc($row['recommendation']); ?></td><td><?php echo esc($row['checked_at']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>