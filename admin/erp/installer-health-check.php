<?php
$pageTitle='Installer Health Check';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('installer_health_check');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{p34LogInstallerEvent($pdo,'installer_health_check','Installer health check reviewed from admin panel.','info',p34InstallerHealthSummary($pdo));flash('success','Installer health event recorded.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'installer-health-check']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/installer-health-check.php');
}
$summary=p34InstallerHealthSummary($pdo);$events=$pdo->query('SELECT * FROM '.table('production_installer_events').' ORDER BY created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Installer Safety</div><h2 class="h4 mb-1">Installer Health Check</h2><p class="text-secondary mb-0">Check PHP, extension, upload, backup, manifest, service-worker and repair table readiness.</p></div><form method="post"><button class="btn btn-brand">Record Health Event</button></form></div>
<div class="row g-4"><div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">Health Summary</h2><?php foreach($summary as $k=>$v): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></span><strong><?php echo esc((string)$v); ?></strong></div><?php endforeach; ?></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Installer Events</h2><table class="table"><thead><tr><th>Event</th><th>Type</th><th>Severity</th><th>Message</th><th>Status</th></tr></thead><tbody><?php foreach($events as $e): ?><tr><td><strong><?php echo esc($e['event_number']); ?></strong><div class="small text-secondary"><?php echo esc($e['created_at']); ?></div></td><td><?php echo esc($e['event_type']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($e['severity'])); ?>"><?php echo esc($e['severity']); ?></span></td><td><?php echo esc($e['message']); ?></td><td><?php echo esc($e['status']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>