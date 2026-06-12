<?php
$pageTitle='API Keys';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('api_keys');
$pdo=getDB();
$user=currentUser();
$newToken='';

if(isset($_GET['toggle'])){
  $id=(int)$_GET['toggle'];
  $pdo->prepare('UPDATE '.table('api_keys').' SET status=CASE WHEN status="active" THEN "revoked" ELSE "active" END WHERE id=?')->execute([$id]);
  flash('success','API key status updated.');
  redirect(ADMIN_URL.'/erp/api-keys.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $token=generateApiPlainToken();
  $days=max(1,(int)($_POST['expiry_days']??setting('api_key_default_expiry_days','365')));
  $expires=date('Y-m-d H:i:s',time()+($days*86400));
  $stmt=$pdo->prepare('INSERT INTO '.table('api_keys').' (key_name,key_prefix,token_hash,scopes,status,created_by,expires_at) VALUES (?,?,?,?, "active", ?, ?)');
  $stmt->execute([trim((string)($_POST['key_name']??'Integration Key')),$token['prefix'],$token['hash'],trim((string)($_POST['scopes']??'read:reports,read:inventory')),(int)($user['id']??0)?:null,$expires]);
  $newToken=$token['plain'];
  flash('success','API key created. Copy the plain token now; it will not be shown again.');
}
$rows=$pdo->query('SELECT ak.*,u.email created_by_email FROM '.table('api_keys').' ak LEFT JOIN '.table('users').' u ON u.id=ak.created_by ORDER BY ak.created_at DESC')->fetchAll();
$logs=$pdo->query('SELECT al.*,ak.key_name FROM '.table('api_access_logs').' al LEFT JOIN '.table('api_keys').' ak ON ak.id=al.api_key_id ORDER BY al.created_at DESC LIMIT 50')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Integration Security</div><h2 class="h4 mb-1">API Keys</h2><p class="text-secondary mb-0">Issue and revoke token-based integration access for future middleware, dashboards, and external systems.</p></div></div>
<?php if($newToken): ?><div class="alert alert-warning"><strong>Copy this token now:</strong><br><code><?php echo esc($newToken); ?></code></div><?php endif; ?>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><div class="erp-kicker">New API Token</div><h2 class="h5 mb-3">Create Key</h2><label class="form-label">Key Name</label><input class="form-control mb-3" name="key_name" required><label class="form-label">Scopes</label><textarea class="form-control mb-3" rows="3" name="scopes">read:reports,read:inventory,read:orders</textarea><label class="form-label">Expiry Days</label><input class="form-control mb-3" type="number" min="1" name="expiry_days" value="<?php echo esc(setting('api_key_default_expiry_days','365')); ?>"><button class="btn btn-brand w-100">Generate API Key</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">Keys</div><h2 class="h5 mb-0">Issued API Keys</h2></div></div><table class="table align-middle"><thead><tr><th>Key</th><th>Prefix</th><th>Scopes</th><th>Status</th><th>Expiry</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['key_name']); ?></strong><div class="small text-secondary"><?php echo esc($row['created_by_email']?:'System'); ?></div></td><td><code><?php echo esc($row['key_prefix']); ?></code></td><td><?php echo esc($row['scopes']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(ucfirst($row['status'])); ?></span></td><td><?php echo esc($row['expires_at']?:'Never'); ?></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?toggle=<?php echo (int)$row['id']; ?>"><?php echo $row['status']==='active'?'Revoke':'Activate'; ?></a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No API keys yet.</td></tr><?php endif; ?></tbody></table></div><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Access Logs</div><h2 class="h5 mb-0">Recent API Activity</h2></div></div><table class="table align-middle"><thead><tr><th>Key</th><th>Endpoint</th><th>Method</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($logs as $log): ?><tr><td><?php echo esc($log['key_name']?:'Unknown'); ?></td><td><?php echo esc($log['endpoint']); ?></td><td><?php echo esc($log['method']); ?></td><td><?php echo (int)$log['status_code']; ?></td><td><?php echo esc($log['created_at']); ?></td></tr><?php endforeach; ?><?php if(!$logs): ?><tr><td colspan="5" class="text-secondary">No API logs yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>