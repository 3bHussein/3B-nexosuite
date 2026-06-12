<?php
$pageTitle='System Error Logs';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('error_logs');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='resolve'){
  $id=(int)($_POST['id']??0);$user=currentUser();
  $pdo->prepare('UPDATE '.table('system_error_logs').' SET status="resolved",resolved_by=?,resolved_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,$id]);
  logActivity($pdo,'System','error_log_resolved','System error log #'.$id.' marked resolved.','system_error_log',$id);
  flash('success','Error log marked resolved.');redirect(ADMIN_URL.'/erp/error-logs.php');
}
$status=trim((string)($_GET['status']??'open'));
$params=[];$where='';
if($status!==''){$where=' WHERE sel.status=?';$params[]=$status;}
$stmt=$pdo->prepare('SELECT sel.*,u.email resolved_by_email FROM '.table('system_error_logs').' sel LEFT JOIN '.table('users').' u ON u.id=sel.resolved_by'.$where.' ORDER BY sel.created_at DESC,sel.id DESC LIMIT 200');
$stmt->execute($params);$rows=$stmt->fetchAll();
$count=$pdo->query('SELECT status,COUNT(*) total FROM '.table('system_error_logs').' GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Operational Diagnostics</div><h2 class="h4 mb-1">System Error Logs</h2><p class="text-secondary mb-0">Review captured exceptions, resolve incidents, and keep production issues traceable.</p></div><form class="d-flex gap-2"><select class="form-select" name="status"><option value="">All</option><option value="open" <?php echo $status==='open'?'selected':''; ?>>Open</option><option value="resolved" <?php echo $status==='resolved'?'selected':''; ?>>Resolved</option></select><button class="btn btn-brand">Filter</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-6"><div class="card-admin p-4"><div class="erp-kicker">Open</div><div class="metric-sm money-negative"><?php echo (int)($count['open']??0); ?></div></div></div><div class="col-md-6"><div class="card-admin p-4"><div class="erp-kicker">Resolved</div><div class="metric-sm money-positive"><?php echo (int)($count['resolved']??0); ?></div></div></div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Error Register</div><h2 class="h5 mb-0">Captured Errors</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Error</th><th>File</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo esc($row['created_at']); ?></td><td><strong><?php echo esc($row['message']); ?></strong><div class="small text-secondary"><?php echo esc($row['context_json']); ?></div></td><td><?php echo esc($row['file_path']); ?><div class="small text-secondary">Line <?php echo (int)$row['line_number']; ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span><?php if($row['resolved_by_email']): ?><div class="small text-secondary"><?php echo esc($row['resolved_by_email']); ?></div><?php endif; ?></td><td class="text-end"><?php if($row['status']==='open'): ?><form method="post"><input type="hidden" name="action" value="resolve"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="btn btn-sm btn-outline-success">Resolve</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-secondary">No error logs found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>