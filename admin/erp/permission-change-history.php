<?php
$pageTitle='Permission Change History';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('permission_change_history');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{recordPermissionChange($pdo,(int)($_POST['role_id']??0)?:null,(int)($_POST['user_id']??0)?:null,trim((string)$_POST['change_type']),trim((string)$_POST['old_permissions']),trim((string)$_POST['new_permissions']),trim((string)$_POST['description']));flash('success','Permission change logged.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'permission-change-history']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/permission-change-history.php');
}
$rows=$pdo->query('SELECT p.*,r.name role_name,u.email user_email,a.email actor_email FROM '.table('permission_change_history').' p LEFT JOIN '.table('erp_roles').' r ON r.id=p.role_id LEFT JOIN '.table('users').' u ON u.id=p.user_id LEFT JOIN '.table('users').' a ON a.id=p.changed_by ORDER BY p.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Access Governance</div><h2 class="h4 mb-1">Permission Change History</h2><p class="text-secondary mb-0">Track role, user, and permission updates for internal audit.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Manual Change Log</h2><input class="form-control mb-2" type="number" name="role_id" placeholder="Role ID optional"><input class="form-control mb-2" type="number" name="user_id" placeholder="User ID optional"><input class="form-control mb-2" name="change_type" value="permission_update"><textarea class="form-control mb-2" name="old_permissions" rows="3" placeholder="Old permissions JSON/text"></textarea><textarea class="form-control mb-2" name="new_permissions" rows="3" placeholder="New permissions JSON/text"></textarea><textarea class="form-control mb-3" name="description" rows="3"></textarea><button class="btn btn-brand w-100">Log Change</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Change</th><th>Role/User</th><th>Actor</th><th>Description</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['change_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['change_type'].' · '.$r['created_at']); ?></div></td><td><?php echo esc($r['role_name']?:$r['user_email']); ?></td><td><?php echo esc($r['actor_email']); ?></td><td><?php echo esc($r['description']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>