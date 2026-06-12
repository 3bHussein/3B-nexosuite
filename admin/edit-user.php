<?php
$pageTitle = 'Edit User';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();

$pdo=getDB();
$stmt=$pdo->prepare('SELECT * FROM ' . table('users') . ' WHERE id=?');
$stmt->execute([(int)($_GET['id']??0)]);
$edit=$stmt->fetch();

if(!$edit){
    flash('error','User not found.');
    redirect(ADMIN_URL.'/users.php');
}

if(strtolower(trim((string)$edit['email'])) === moduleBundleDeveloperEmail()){
    flash('error','Developer controller user cannot be edited from admin users.');
    redirect(ADMIN_URL.'/users.php');
}

$roles = websiteUserRoleOptions();
$erpRoles = $pdo->query('SELECT id,name,slug FROM ' . table('erp_roles') . ' WHERE active=1 AND slug<>"module_bundle_developer" ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        $role = normalizeWebsiteUserRole((string)($_POST['role'] ?? 'customer'));
        $canLoginErp = 0;
        $erpRoleId = null;

        if ($role === 'admin') {
            $canLoginErp = 1;
        } elseif ($role === 'employee' && !empty($_POST['can_login_erp'])) {
            $canLoginErp = 1;
            $erpRoleId = (int)($_POST['erp_role_id'] ?? 0) ?: null;
            if ($erpRoleId && isProtectedDeveloperRoleId($pdo, $erpRoleId)) { $erpRoleId = null; }
        }

        if (in_array($role, ['vendor','technician','customer'], true)) {
            $canLoginErp = 0;
            $erpRoleId = null;
        }

        $stmt=$pdo->prepare('UPDATE ' . table('users') . ' SET first_name=?,last_name=?,phone=?,address=?,role=?,erp_role_id=?,can_login_erp=?,status=? WHERE id=? AND LOWER(email)<>?');
        $stmt->execute([
            trim((string)$_POST['first_name']),
            trim((string)$_POST['last_name']),
            trim((string)($_POST['phone'] ?? '')),
            trim((string)($_POST['address'] ?? '')),
            $role,
            $erpRoleId,
            $canLoginErp,
            trim((string)($_POST['status'] ?? 'active')) ?: 'active',
            $edit['id'],
            moduleBundleDeveloperEmail()
        ]);

        if(!empty($_POST['password'])){
            $stmt=$pdo->prepare('UPDATE ' . table('users') . ' SET password=? WHERE id=? AND LOWER(email)<>?');
            $stmt->execute([password_hash((string)$_POST['password'],PASSWORD_DEFAULT),$edit['id'],moduleBundleDeveloperEmail()]);
        }
        flash('success','User updated.');
        redirect(ADMIN_URL.'/users.php');
    } catch (Throwable $e) {
        flash('error',$e->getMessage());
    }
}
include __DIR__ . '/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div>
    <h1 class="h4 mb-1">Edit User</h1>
    <p class="text-secondary mb-0">Assign website user type and optional employee ERP access separately.</p>
  </div>
</div>

<form method="post" class="card-admin p-4">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">First Name</label><input class="form-control" name="first_name" value="<?php echo esc($edit['first_name']); ?>" required></div>
    <div class="col-md-6"><label class="form-label">Last Name</label><input class="form-control" name="last_name" value="<?php echo esc($edit['last_name']); ?>" required></div>
    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo esc($edit['phone']); ?>"></div>
    <div class="col-md-6"><label class="form-label">New Password</label><input class="form-control" type="password" name="password"></div>
    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"><?php echo esc($edit['address']); ?></textarea></div>

    <div class="col-md-6">
      <label class="form-label">User Type / Role</label>
      <select class="form-select" name="role">
        <?php foreach($roles as $key=>$label): ?>
          <option value="<?php echo esc($key); ?>" <?php echo ($edit['role']??'customer')===$key?'selected':''; ?>><?php echo esc($label); ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Vendor and Technician connect with E-commerce Website Permissions.</div>
    </div>

    <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option <?php echo $edit['status']==='active'?'selected':''; ?>>active</option><option <?php echo $edit['status']==='inactive'?'selected':''; ?>>inactive</option></select></div>

    <div class="col-12"><hr><h2 class="h5">ERP Access for Employee Only</h2></div>
    <div class="col-md-4">
      <label class="form-label">Allow ERP Login</label>
      <select class="form-select" name="can_login_erp">
        <option value="0" <?php echo (int)($edit['can_login_erp']??0)===0?'selected':''; ?>>No ERP access</option>
        <option value="1" <?php echo (int)($edit['can_login_erp']??0)===1?'selected':''; ?>>Allow ERP login</option>
      </select>
      <div class="form-text">Only applies when role is Employee. Admin has admin access; Customer/Vendor/Technician are website-only.</div>
    </div>
    <div class="col-md-8">
      <label class="form-label">ERP Role</label>
      <select class="form-select" name="erp_role_id">
        <option value="">No ERP role</option>
        <?php foreach($erpRoles as $role): ?>
          <option value="<?php echo (int)$role['id']; ?>" <?php echo (int)($edit['erp_role_id']??0)===(int)$role['id']?'selected':''; ?>><?php echo esc($role['name'].' ('.$role['slug'].')'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-12">
      <div class="alert alert-info border mb-0">
        <strong>No permission conflict:</strong> Website roles affect e-commerce user-type permissions. ERP module permissions apply only to Admin and Employee users with ERP login enabled.
      </div>
    </div>
    <div class="col-12"><button class="btn btn-brand">Update User</button></div>
  </div>
</form>
<?php include __DIR__ . '/footer.php'; ?>