<?php
require_once dirname(__DIR__) . '/includes/functions.php';
if (isLoggedIn() && hasPermission('access_erp')) {
    redirect(SITE_URL . '/employee/dashboard.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getDB()->prepare('SELECT * FROM ' . table('users') . ' WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->execute([trim((string)($_POST['email'] ?? ''))]);
    $user = $stmt->fetch();
    $canEnter = $user && password_verify((string)($_POST['password'] ?? ''), $user['password'])
        && (($user['role'] ?? '') === 'admin' || (($user['role'] ?? '') === 'employee' && (int)($user['can_login_erp'] ?? 0) === 1));
    if ($canEnter) {
        $_SESSION['user_id'] = (int)$user['id'];
        flash('success', 'Employee access granted.');
        if (strtolower(trim((string)($user['email'] ?? ''))) === moduleBundleDeveloperEmail()) {
            redirect(ADMIN_URL . '/erp/module-bundle-manager.php');
        }
        redirect(SITE_URL . '/employee/dashboard.php');
    }
    flash('error', 'Invalid employee login or ERP access is disabled.');
    redirect(SITE_URL . '/employee/login.php');
}
siteHeader('Employee ERP Login', 'login');
?>
<section class="employee-login-shell">
  <div class="employee-login-copy">
    <span class="eyebrow">Employee ERP Portal</span>
    <h1>Access ERP tools, online sales, and role-based workspaces.</h1>
    <p>Employees sign in here when an administrator has created an ERP account and assigned a permission role.</p>
    <div class="employee-login-points">
      <span><i class="bi bi-shield-lock"></i> Permission controlled</span>
      <span><i class="bi bi-grid-1x2"></i> Module-specific access</span>
      <span><i class="bi bi-bag-check"></i> Website sales permissions</span>
    </div>
  </div>
  <form method="post" class="form-card employee-login-card">
    <h2 class="h4 mb-3">Employee Sign In</h2>
    <div class="mb-3"><label class="form-label">Work Email</label><input class="form-control" type="email" name="email" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
    <button class="btn btn-brand w-100">Open Employee Workspace</button>
  </form>
</section>
<?php siteFooter(); ?>