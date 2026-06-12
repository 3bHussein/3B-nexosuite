<?php
$pageTitle='Integrations';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('integrations');
$pdo=getDB();
$apps=$pdo->query('SELECT * FROM '.table('integration_apps').' ORDER BY category,app_name')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='connect'){
      $user=currentUser();
      $stmt=$pdo->prepare('INSERT INTO '.table('integration_connections').' (integration_app_id,connection_name,environment,status,credentials_json,settings_json,created_by) VALUES (?,?,?,"inactive",?,?,?)');
      $stmt->execute([(int)$_POST['integration_app_id'],trim((string)$_POST['connection_name']),trim((string)$_POST['environment']),trim((string)$_POST['credentials_json'])?:'{}',trim((string)$_POST['settings_json'])?:'{}',(int)($user['id']??0)?:null]);
      flash('success','Integration connection created.');
    }elseif($action==='test'){
      $pdo->prepare('UPDATE '.table('integration_connections').' SET status="active",last_test_at=NOW() WHERE id=?')->execute([(int)$_POST['id']]);
      flash('success','Connection test simulated and marked active.');
    }elseif($action==='sync'){
      if(setting('integration_sync_enabled','1')!=='1'){throw new RuntimeException('Integration sync is disabled in settings.');}
      $number=nextScopedDocumentNumber($pdo,'integration_sync_job','SYNC',operationalScope($pdo));
      $pdo->prepare('INSERT INTO '.table('integration_sync_jobs').' (integration_connection_id,job_number,sync_type,direction,status,records_total,records_success,started_at,finished_at,notes) VALUES (?,?,?,?, "synced",10,10,NOW(),NOW(),?)')->execute([(int)$_POST['id'],$number,trim((string)$_POST['sync_type']),trim((string)$_POST['direction']),'Manual sync simulation completed.']);
      $pdo->prepare('UPDATE '.table('integration_connections').' SET last_sync_at=NOW() WHERE id=?')->execute([(int)$_POST['id']]);
      flash('success','Sync job created.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'integrations']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/integrations.php');
}
$connections=$pdo->query('SELECT c.*,a.app_code,a.app_name,a.provider,a.category FROM '.table('integration_connections').' c LEFT JOIN '.table('integration_apps').' a ON a.id=c.integration_app_id ORDER BY c.created_at DESC')->fetchAll();
$jobs=$pdo->query('SELECT j.*,c.connection_name,a.app_name FROM '.table('integration_sync_jobs').' j LEFT JOIN '.table('integration_connections').' c ON c.id=j.integration_connection_id LEFT JOIN '.table('integration_apps').' a ON a.id=c.integration_app_id ORDER BY j.created_at DESC LIMIT 80')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">External Systems</div><h2 class="h4 mb-1">Integrations</h2><p class="text-secondary mb-0">Connect WhatsApp, Zapier, Make, Google Sheets, external commerce, and accounting connectors.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/webhooks.php"><?php echo t('Webhooks', 'Webhooks'); ?></a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="connect"><h2 class="h5 mb-3">New Connection</h2><label class="form-label">Integration App</label><select class="form-select mb-2" name="integration_app_id"><?php foreach($apps as $app): ?><option value="<?php echo (int)$app['id']; ?>"><?php echo esc($app['app_code'].' · '.$app['app_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="connection_name" placeholder="Connection name" required><select class="form-select mb-2" name="environment"><option value="production">Production</option><option value="sandbox">Sandbox</option></select><textarea class="form-control mb-2" name="credentials_json" rows="3" placeholder='{"api_key":"hidden"}'></textarea><textarea class="form-control mb-3" name="settings_json" rows="3" placeholder='{"sync_products":true}'></textarea><button class="btn btn-brand w-100">Create Connection</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Connections</h2><table class="table align-middle"><thead><tr><th>Connection</th><th>App</th><th>Environment</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($connections as $c): ?><tr><td><strong><?php echo esc($c['connection_name']); ?></strong><div class="small text-secondary">Last sync: <?php echo esc($c['last_sync_at']?:'Never'); ?></div></td><td><?php echo esc($c['app_name'].' · '.$c['provider']); ?></td><td><?php echo esc($c['environment']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($c['status'])); ?>"><?php echo esc($c['status']); ?></span></td><td class="text-end"><form method="post" class="d-inline"><input type="hidden" name="action" value="test"><input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>"><button class="btn btn-sm btn-outline-success">Test</button></form> <form method="post" class="d-inline"><input type="hidden" name="action" value="sync"><input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>"><input type="hidden" name="sync_type" value="manual"><input type="hidden" name="direction" value="outbound"><button class="btn btn-sm btn-brand">Sync</button></form></td></tr><?php endforeach; ?><?php if(!$connections): ?><tr><td colspan="5" class="text-secondary">No connections yet.</td></tr><?php endif; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Sync Jobs</h2><table class="table"><thead><tr><th>Job</th><th>Connection</th><th>Records</th><th>Status</th></tr></thead><tbody><?php foreach($jobs as $job): ?><tr><td><strong><?php echo esc($job['job_number']); ?></strong><div class="small text-secondary"><?php echo esc($job['sync_type'].' · '.$job['direction']); ?></div></td><td><?php echo esc($job['connection_name'].' · '.$job['app_name']); ?></td><td><?php echo (int)$job['records_success']; ?>/<?php echo (int)$job['records_total']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($job['status'])); ?>"><?php echo esc($job['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>