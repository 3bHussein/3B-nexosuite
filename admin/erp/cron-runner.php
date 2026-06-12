<?php
$pageTitle='Cron Runner';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('cron_runner');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$result=runAutomationCycle($pdo,'manual_ui');recordSecurityEvent($pdo,'cron_manual_run','Automation runner executed manually.','info');flash('success','Automation completed: '.$result['summary']);}catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/cron-runner.php');
}
$rows=$pdo->query('SELECT cr.*,u.email created_by_email FROM '.table('cron_runs').' cr LEFT JOIN '.table('users').' u ON u.id=cr.created_by ORDER BY cr.created_at DESC,cr.id DESC LIMIT 120')->fetchAll();
$cronUrl=ADMIN_URL.'/erp/cron-endpoint.php?token='.urlencode((string)setting('cron_secret',''));
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Scheduled Automation</div><h2 class="h4 mb-1">Cron Runner</h2><p class="text-secondary mb-0">Run automated alerts for low stock, aged approvals, and operational notifications.</p></div><form method="post"><button class="btn btn-brand">Run Automation Now</button></form></div>
<div class="card-admin p-4 mb-4"><div class="erp-kicker">Server Cron URL</div><p class="mb-2">Use this tokenized endpoint from server cron. Keep the token private.</p><code class="d-block p-3 bg-light rounded-4"><?php echo esc($cronUrl); ?></code><div class="small text-secondary mt-2">Example schedule: every 15 minutes or hourly depending on business volume.</div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Run History</div><h2 class="h5 mb-0">Automation Runs</h2></div></div><table class="table align-middle"><thead><tr><th>Run</th><th>Status</th><th>Summary</th><th>User</th><th>Started</th><th>Finished</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['run_key']); ?></strong></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td><?php echo esc($row['summary']); ?></td><td><?php echo esc($row['created_by_email']?:'Endpoint/System'); ?></td><td><?php echo esc($row['started_at']); ?></td><td><?php echo esc($row['finished_at']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No automation runs yet.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>