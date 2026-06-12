<?php
$pageTitle='Employee ERP Access';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
adminGuard();
$pdo = getDB();
$developerEmail = moduleBundleDeveloperEmail();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_employee_login') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $roleId = (int)($_POST['erp_role_id'] ?? 0);

    if ($email === $developerEmail) {
        flash('error','Developer Module Controller account cannot be created or modified from Employee Access Accounts.');
        redirect(ADMIN_URL . '/erp/access-control.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error','Enter a valid employee email.');
        redirect(ADMIN_URL . '/erp/access-control.php');
    }
    if (strlen($password) < 8) {
        flash('error','Employee password must be at least 8 characters.');
        redirect(ADMIN_URL . '/erp/access-control.php');
    }
    if (isProtectedDeveloperRoleId($pdo, $roleId)) {
        flash('error','Developer Module Controller role cannot be assigned to Employee Access Accounts.');
        redirect(ADMIN_URL . '/erp/access-control.php');
    }

    $stmt = $pdo->prepare('INSERT INTO ' . table('users') . ' (email,password,first_name,last_name,role,erp_role_id,can_login_erp,status) VALUES (?,?,?,?, "employee",?,?,?)');
    try {
        $stmt->execute([
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            trim((string)($_POST['first_name'] ?? 'Employee')),
            trim((string)($_POST['last_name'] ?? 'User')),
            $roleId ?: null,
            !empty($_POST['can_login_erp']) ? 1 : 0,
            trim((string)($_POST['status'] ?? 'active')) ?: 'active'
        ]);
        logActivity($pdo,'Access Control','employee_created','Employee ERP login created for ' . $email,'user',(int)$pdo->lastInsertId());
        flash('success','Employee ERP login created.');
    } catch (Throwable $e) {
        flash('error','Unable to create employee login. Email may already exist.');
    }
    redirect(ADMIN_URL . '/erp/access-control.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_employee_login') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $roleId = (int)($_POST['erp_role_id'] ?? 0);

    $stmt = $pdo->prepare('SELECT email FROM ' . table('users') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$userId]);
    $targetEmail = strtolower(trim((string)$stmt->fetchColumn()));

    if ($targetEmail === $developerEmail) {
        flash('error','Developer Module Controller account cannot be edited from Employee Access Accounts.');
        redirect(ADMIN_URL . '/erp/access-control.php');
    }
    if (isProtectedDeveloperRoleId($pdo, $roleId)) {
        flash('error','Developer Module Controller role cannot be assigned to Employee Access Accounts.');
        redirect(ADMIN_URL . '/erp/access-control.php');
    }

    $stmt = $pdo->prepare('UPDATE ' . table('users') . ' SET erp_role_id=?, can_login_erp=?, status=? WHERE id=? AND role="employee" AND LOWER(email)<>?');
    $stmt->execute([
        $roleId ?: null,
        !empty($_POST['can_login_erp']) ? 1 : 0,
        trim((string)($_POST['status'] ?? 'active')) ?: 'active',
        $userId,
        $developerEmail
    ]);
    logActivity($pdo,'Access Control','employee_updated','Employee ERP access updated.','user',$userId);
    flash('success','Employee access updated.');
    redirect(ADMIN_URL . '/erp/access-control.php');
}

$roles = $pdo->query('SELECT * FROM ' . table('erp_roles') . ' WHERE active=1 AND slug<>"module_bundle_developer" ORDER BY name ASC')->fetchAll();
$employeeUsersStmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM ' . table('users') . ' u LEFT JOIN ' . table('erp_roles') . ' r ON r.id=u.erp_role_id WHERE u.role="employee" AND LOWER(u.email)<>? ORDER BY u.created_at DESC');
$employeeUsersStmt->execute([$developerEmail]);
$employeeUsers = $employeeUsersStmt->fetchAll();

include dirname(__DIR__) . '/header.php';
?>
<div class="alert alert-warning border-0 shadow-sm">
  <strong>Protected developer account:</strong>
  <code><?php echo esc($developerEmail); ?></code>
  is hidden from Employee Access Accounts. Its protected role cannot be assigned to employees.
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <input type="hidden" name="action" value="create_employee_login">
      <h2 class="h4 mb-1">Create Employee Login</h2>
      <p class="text-secondary">Grant ERP portal access through the ERP login switch and a normal permission role.</p>
      <div class="mb-3"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
      <div class="mb-3"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
      <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
      <div class="mb-3"><label class="form-label">Temporary Password</label><input class="form-control" type="password" name="password" minlength="8" required></div>
      <div class="mb-3">
        <label class="form-label">Permission Role</label>
        <select class="form-select" name="erp_role_id" required>
          <?php foreach($roles as $role): ?><option value="<?php echo (int)$role['id']; ?>"><?php echo esc($role['name']); ?></option><?php endforeach; ?>
        </select>
        <div class="form-text">Developer Module Controller role is intentionally excluded.</div>
      </div>
      <div class="mb-3 form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="can_login_erp" value="1" checked id="can_login_erp_create"><label class="form-check-label" for="can_login_erp_create">Enable ERP portal login</label></div>
      <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      <button class="btn btn-brand w-100">Create Employee Access</button>
    </form>
  </div>
  <div class="col-xl-8">
    <div class="card-admin p-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div><h2 class="h4 mb-1">Employee Access Accounts</h2><p class="text-secondary mb-0">Edit employee ERP status and normal permission role. Developer account is excluded.</p></div>
        <a class="btn btn-outline-dark" href="<?php echo esc(SITE_URL); ?>/employee/login.php" target="_blank">Open Employee Login</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Employee</th><th>Role / Status / ERP Login / Save</th></tr></thead>
          <tbody>
          <?php foreach($employeeUsers as $employee): ?>
            <tr>
              <td><strong><?php echo esc(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))); ?></strong><br><small><?php echo esc($employee['email']); ?></small></td>
              <td>
                <form method="post" class="row g-2 align-items-center">
                  <input type="hidden" name="action" value="update_employee_login">
                  <input type="hidden" name="user_id" value="<?php echo (int)$employee['id']; ?>">
                  <div class="col-lg-4"><select class="form-select form-select-sm" name="erp_role_id"><?php foreach($roles as $role): ?><option value="<?php echo (int)$role['id']; ?>" <?php echo (int)$employee['erp_role_id']===(int)$role['id']?'selected':''; ?>><?php echo esc($role['name']); ?></option><?php endforeach; ?></select></div>
                  <div class="col-lg-2"><select class="form-select form-select-sm" name="status"><option value="active" <?php echo ($employee['status']??'')==='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo ($employee['status']??'')==='inactive'?'selected':''; ?>>Inactive</option></select></div>
                  <div class="col-lg-3"><label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="can_login_erp" value="1" <?php echo !empty($employee['can_login_erp'])?'checked':''; ?>><span class="form-check-label">ERP login</span></label></div>
                  <div class="col-lg-3"><button class="btn btn-sm btn-brand w-100">Save</button></div>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$employeeUsers): ?><tr><td colspan="2" class="text-secondary">No employee login accounts created yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include dirname(__DIR__) . '/footer.php'; ?>