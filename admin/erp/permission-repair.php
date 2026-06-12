<?php
$pageTitle='Permission Repair';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('permission_repair');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$n=p34RepairPermissions($pdo);flash('success','Permission repair completed. Added '.$n.' permission(s).');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'permission-repair']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/permission-repair.php');
}
$role=$pdo->query('SELECT id,name,slug,permissions FROM '.table('erp_roles').' WHERE slug="admin" OR name="Administrator" OR name="Admin" ORDER BY id LIMIT 1')->fetch();
$current=$role?json_decode((string)$role['permissions'],true):[];if(!is_array($current)){$current=[];}$perms=p34ProductionPermissions();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Access Auto-Repair</div><h2 class="h4 mb-1">Permission Repair</h2><p class="text-secondary mb-0">Check production permissions and repair missing admin role access.</p></div><form method="post"><button class="btn btn-brand">Repair Admin Permissions</button></form></div>
<div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Permission</th><th>Description</th><th>Admin Role</th></tr></thead><tbody><?php foreach($perms as $k=>$label): ?><tr><td><code><?php echo esc($k); ?></code></td><td><?php echo esc($label); ?></td><td><span class="badge bg-<?php echo !empty($current[$k])?'success':'danger'; ?>"><?php echo !empty($current[$k])?'Present':'Missing'; ?></span></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>