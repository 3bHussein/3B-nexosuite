<?php
$pageTitle = 'Add User';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo = getDB();

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

        $stmt=$pdo->prepare('INSERT INTO ' . table('users') . ' (email,password,first_name,last_name,phone,address,role,erp_role_id,can_login_erp,status) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            trim((string)$_POST['email']),
            password_hash((string)$_POST['password'],PASSWORD_DEFAULT),
            trim((string)$_POST['first_name']),
            trim((string)$_POST['last_name']),
            trim((string)($_POST['phone'] ?? '')),
            trim((string)($_POST['address'] ?? '')),
            $role,
            $erpRoleId,
            $canLoginErp,
            trim((string)($_POST['status'] ?? 'active')) ?: 'active'
        ]);
        flash('success','User created.');
        redirect(ADMIN_URL.'/users.php');
    } catch (Throwable $e) {
        flash('error',$e->getMessage());
    }
}
include __DIR__ . '/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div>
    <h1 class="h4 mb-1">Add User</h1>
    <p class="text-secondary mb-0">Create website user types for e-commerce permissions without mixing them with ERP permissions.</p>
  </div>
</div>

<form method="post" class="card-admin p-4">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
    <div class="col-md-6"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
    <div class="col-md-6"><label class="form-label">Password</label><input type="password" class="form-control" name="password" minlength="8" required></div>
    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
    <div class="col-md-6">
      <label class="form-label">User Type / Role</label>
      <select class="form-select" name="role" id="user-role-select">
        <?php foreach($roles as $key=>$label): ?>
          <option value="<?php echo esc($key); ?>"><?php echo esc($label); ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">Vendor and Technician are website/portal user types. They do not receive ERP access.</div>
    </div>
    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
    <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option>active</option><option>inactive</option></select></div>

    <div class="col-12"><hr><h2 class="h5">ERP Access for Employee Only</h2></div>
    <div class="col-md-4">
      <label class="form-label">Allow ERP Login</label>
      <select class="form-select" name="can_login_erp">
        <option value="0">No ERP access</option>
        <option value="1">Allow ERP login</option>
      </select>
      <div class="form-text">Ignored for Customer, Vendor and Technician.</div>
    </div>
    <div class="col-md-8">
      <label class="form-label">ERP Role</label>
      <select class="form-select" name="erp_role_id">
        <option value="">No ERP role</option>
        <?php foreach($erpRoles as $role): ?>
          <option value="<?php echo (int)$role['id']; ?>"><?php echo esc($role['name'].' ('.$role['slug'].')'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-12">
      <div class="alert alert-info border mb-0">
        <strong>Permission separation:</strong> Vendor and Technician roles are used by E-commerce Website Permissions only. ERP permissions remain controlled separately through Employee ERP roles and module bundle rules.
      </div>
    </div>
    <div class="col-12"><button class="btn btn-brand">Create User</button></div>
  </div>
</form>
<?php include __DIR__ . '/footer.php'; ?>