<?php
$pageTitle='API Dashboard 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('api_dashboard_2');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='install'){$e=installApiEndpointCatalog($pdo);$s=installApiScopePolicies($pdo);$c=installIntegrationConnectorTemplates($pdo);flash('success','Installed endpoint catalog: '.$e.', scopes: '.$s.', connector templates: '.$c.'.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'api-dashboard-2']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/api-dashboard-2.php');
}
$stats=[
 'api_keys'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('api_keys').' WHERE status="active"')->fetchColumn(),
 'webhooks'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('webhook_subscriptions').' WHERE status="active"')->fetchColumn(),
 'connections'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('integration_connections'))->fetchColumn(),
 'errors'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('integration_error_logs').' WHERE status="open"')->fetchColumn(),
];
$logs=$pdo->query('SELECT l.*,k.key_name FROM '.table('api_access_logs').' l LEFT JOIN '.table('api_keys').' k ON k.id=l.api_key_id ORDER BY l.created_at DESC LIMIT 10')->fetchAll();
$jobs=$pdo->query('SELECT j.*,c.connection_name FROM '.table('integration_sync_jobs').' j LEFT JOIN '.table('integration_connections').' c ON c.id=j.integration_connection_id ORDER BY j.created_at DESC LIMIT 10')->fetchAll();
$webhooks=$pdo->query('SELECT * FROM '.table('webhook_events').' ORDER BY created_at DESC LIMIT 10')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Developer Platform</div><h2 class="h4 mb-1">API, Webhooks & External Integrations 2.0</h2><p class="text-secondary mb-0">REST API catalog, API tokens, usage limits, webhooks, connector templates, marketplace sync and accounting exports.</p></div><form method="post"><input type="hidden" name="action" value="install"><button class="btn btn-brand">Install API Defaults</button></form></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">API Access Logs</h2><table class="table"><tbody><?php foreach($logs as $l): ?><tr><td><strong><?php echo esc($l['method'].' '.$l['endpoint']); ?></strong><div class="small text-secondary"><?php echo esc($l['key_name'].' · '.$l['status_code'].' · '.$l['created_at']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Sync Jobs</h2><table class="table"><tbody><?php foreach($jobs as $j): ?><tr><td><strong><?php echo esc($j['job_number']); ?></strong><div class="small text-secondary"><?php echo esc($j['connection_name'].' · '.$j['sync_type'].' · '.$j['status']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Webhook Events</h2><table class="table"><tbody><?php foreach($webhooks as $w): ?><tr><td><strong><?php echo esc($w['event_number']); ?></strong><div class="small text-secondary"><?php echo esc($w['event_type'].' · '.$w['status']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>