<?php
$pageTitle='Security Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('security_center');
$pdo=getDB();
$stats=[
  'active_users'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('users').' WHERE status="active"')->fetchColumn(),
  'erp_users'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('users').' WHERE can_login_erp=1 AND status="active"')->fetchColumn(),
  'active_api_keys'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('api_keys').' WHERE status="active"')->fetchColumn(),
  'open_errors'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('system_error_logs').' WHERE status="open"')->fetchColumn(),
];
$events=$pdo->query('SELECT se.*,u.email FROM '.table('security_events').' se LEFT JOIN '.table('users').' u ON u.id=se.user_id ORDER BY se.created_at DESC,se.id DESC LIMIT 80')->fetchAll();
$apiKeys=$pdo->query('SELECT key_name,key_prefix,status,last_used_at,expires_at,created_at FROM '.table('api_keys').' ORDER BY created_at DESC LIMIT 8')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Security Hardening</div><h2 class="h4 mb-1">Security Center</h2><p class="text-secondary mb-0">Review ERP access posture, API keys, security events, and production hardening settings.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/system-health.php">Run Health Checks</a><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/settings.php">Security Settings</a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Active Users</div><div class="metric-sm"><?php echo $stats['active_users']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">ERP Login Users</div><div class="metric-sm"><?php echo $stats['erp_users']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Active API Keys</div><div class="metric-sm"><?php echo $stats['active_api_keys']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open Error Logs</div><div class="metric-sm <?php echo $stats['open_errors']>0?'money-negative':'money-positive'; ?>"><?php echo $stats['open_errors']; ?></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-7"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Security Events</div><h2 class="h5 mb-0">Access & Hardening Audit</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Event</th><th>User</th><th>Severity</th><th>IP</th></tr></thead><tbody><?php foreach($events as $event): ?><tr><td><?php echo esc($event['created_at']); ?></td><td><strong><?php echo esc($event['event_type']); ?></strong><div class="small text-secondary"><?php echo esc($event['description']); ?></div></td><td><?php echo esc($event['email']?:'System'); ?></td><td><span class="badge bg-<?php echo esc(statusTone($event['severity'])); ?>"><?php echo esc($event['severity']); ?></span></td><td><?php echo esc($event['ip_address']); ?></td></tr><?php endforeach; ?><?php if(!$events): ?><tr><td colspan="5" class="text-secondary">No security events have been logged yet.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="col-xl-5"><div class="card-admin p-4 mb-4"><div class="erp-kicker">Production Controls</div><h2 class="h5 mb-3">Current Hardening Settings</h2><div class="d-grid gap-2 small"><div><strong>Maintenance mode:</strong> <?php echo setting('maintenance_mode_enabled','0')==='1'?'Enabled':'Disabled'; ?></div><div><strong>Login max attempts:</strong> <?php echo esc(setting('login_max_attempts','5')); ?></div><div><strong>Session timeout:</strong> <?php echo esc(setting('session_timeout_minutes','120')); ?> minutes</div><div><strong>Error logging:</strong> <?php echo setting('system_error_logging_enabled','1')==='1'?'Enabled':'Disabled'; ?></div><div><strong>Cron secret:</strong> <code><?php echo esc(substr(setting('cron_secret',''),0,8)); ?>...</code></div></div></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">API Key Snapshot</h2><table class="table"><tbody><?php foreach($apiKeys as $key): ?><tr><td><strong><?php echo esc($key['key_name']); ?></strong><div class="small text-secondary"><?php echo esc($key['key_prefix']); ?> · expires <?php echo esc($key['expires_at']?:'never'); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($key['status'])); ?>"><?php echo esc($key['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$apiKeys): ?><tr><td class="text-secondary">No API keys created yet.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>