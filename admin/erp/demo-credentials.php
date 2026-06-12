<?php
$pageTitle='Demo Credentials';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('demo_credentials');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='install'){p35CreateDemoCredentialsIfEmpty($pdo);flash('success','Default demo credentials installed if missing.');}
    else{$number=nextScopedDocumentNumber($pdo,'demo_credential',setting('demo_credential_prefix','DEMOCR'),operationalScope($pdo));$pdo->prepare('INSERT INTO '.table('demo_credentials').' (credential_number,portal_name,role_label,login_url,username,password_hint,access_notes,status,created_by) VALUES (?,?,?,?,?,?,?,"active",?)')->execute([$number,trim((string)$_POST['portal_name']),trim((string)$_POST['role_label']),trim((string)$_POST['login_url']),trim((string)$_POST['username']),trim((string)$_POST['password_hint']),trim((string)$_POST['access_notes']),(int)(currentUser()['id']??0)?:null]);flash('success','Demo credential added.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'demo-credentials']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/demo-credentials.php');
}
$rows=$pdo->query('SELECT * FROM '.table('demo_credentials').' ORDER BY portal_name,role_label')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Demo Access</div><h2 class="h4 mb-1">Demo Credentials</h2><p class="text-secondary mb-0">Keep controlled demo login instructions for customers, staff and developers.</p></div><form method="post"><button name="action" value="install" class="btn btn-brand">Install Defaults</button></form></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Add Credential</h2><input class="form-control mb-2" name="portal_name" placeholder="Admin Portal"><input class="form-control mb-2" name="role_label" placeholder="Admin"><input class="form-control mb-2" name="login_url" placeholder="/admin/login.php"><input class="form-control mb-2" name="username" placeholder="user@example.com"><input class="form-control mb-2" name="password_hint" placeholder="Provided separately"><textarea class="form-control mb-3" name="access_notes" rows="3"></textarea><button class="btn btn-outline-primary w-100">Add Credential</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Portal</th><th>Role</th><th>Login</th><th>Username</th><th>Password Hint</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['credential_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['portal_name']); ?></div></td><td><?php echo esc($r['role_label']); ?></td><td><?php echo esc($r['login_url']); ?></td><td><?php echo esc($r['username']); ?></td><td><?php echo esc($r['password_hint']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>