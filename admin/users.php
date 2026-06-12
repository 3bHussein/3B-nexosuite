<?php
$pageTitle = 'Users';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo = getDB();

$developerEmail = moduleBundleDeveloperEmail();

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $pdo->prepare('SELECT id,email,role FROM ' . table('users') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$deleteId]);
    $targetUser = $stmt->fetch();

    if (!$targetUser) {
        flash('error', 'User not found.');
    } elseif (strtolower(trim((string)$targetUser['email'])) === $developerEmail) {
        flash('error', 'Developer controller user cannot be deleted from admin users.');
    } elseif (($targetUser['role'] ?? '') === 'admin') {
        flash('error', 'Admin users cannot be deleted from this page.');
    } else {
        $stmt = $pdo->prepare('DELETE FROM ' . table('users') . ' WHERE id=? AND role<>"admin" AND LOWER(email)<>?');
        $stmt->execute([$deleteId, $developerEmail]);
        flash('success', 'User deleted when eligible.');
    }
    redirect(ADMIN_URL . '/users.php');
}

$users = $pdo->query('SELECT u.*, r.name AS erp_role_name FROM ' . table('users') . ' u LEFT JOIN ' . table('erp_roles') . ' r ON r.id=u.erp_role_id ORDER BY u.created_at DESC')->fetchAll();
include __DIR__ . '/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h1 class="h4 mb-1">Users</h1>
    <p class="text-secondary mb-0">Create Customer, Vendor, Technician, Employee and Admin users. Vendor/Technician are website permission roles, not ERP roles.</p>
  </div>
  <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/add-user.php">Add User</a>
</div>

<div class="alert alert-warning border-0 shadow-sm">
  <strong>Protected developer account:</strong>
  <code><?php echo esc($developerEmail); ?></code>
  <span class="ms-2">Edit and delete actions are disabled for this account.</span>
</div>

<div class="alert alert-info border-0 shadow-sm">
  <strong>Role separation:</strong>
  Vendor and Technician connect to <a href="<?php echo esc(ADMIN_URL); ?>/erp/ecommerce-permission-manager.php">E-commerce Website Permissions</a>. ERP permissions remain controlled separately by ERP role and module bundle settings.
</div>

<div class="table-wrap table-responsive">
  <table class="table align-middle">
    <thead>
      <tr><th>Name</th><th>Email</th><th>User Type</th><th>ERP Access</th><th>Status</th><th>Protection</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach($users as $user): ?>
        <?php
          $isProtectedDeveloper = strtolower(trim((string)$user['email'])) === $developerEmail;
          $role = normalizeWebsiteUserRole((string)($user['role'] ?? 'customer'));
          $erpAccess = ($role === 'admin') ? 'Admin ERP access' : (((int)($user['can_login_erp'] ?? 0) === 1 && $role === 'employee') ? ('Employee ERP: ' . ($user['erp_role_name'] ?: 'Role not set')) : 'Website/portal only');
        ?>
        <tr>
          <td><?php echo esc($user['first_name'].' '.$user['last_name']); ?></td>
          <td>
            <?php echo esc($user['email']); ?>
            <?php if($isProtectedDeveloper): ?>
              <span class="badge bg-danger ms-1">Developer Controller</span>
            <?php endif; ?>
          </td>
          <td><span class="badge bg-<?php echo esc(roleBadgeClass($role)); ?>"><?php echo esc(userRoleLabel($role)); ?></span></td>
          <td><span class="small text-secondary"><?php echo esc($erpAccess); ?></span></td>
          <td><?php echo esc($user['status']); ?></td>
          <td>
            <?php if($isProtectedDeveloper): ?>
              <span class="badge bg-warning text-dark">Edit/Delete Disabled</span>
            <?php else: ?>
              <span class="badge bg-light text-dark border">Normal</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <?php if($isProtectedDeveloper): ?>
              <button class="btn btn-outline-secondary btn-sm" disabled title="Developer controller cannot be edited by admin.">Edit Disabled</button>
              <button class="btn btn-outline-secondary btn-sm" disabled title="Developer controller cannot be deleted by admin.">Delete Disabled</button>
            <?php else: ?>
              <a class="btn btn-outline-primary btn-sm" href="<?php echo esc(ADMIN_URL); ?>/edit-user.php?id=<?php echo (int)$user['id']; ?>">Edit</a>
              <a class="btn btn-outline-danger btn-sm" data-confirm="Delete this non-admin user?" href="?delete=<?php echo (int)$user['id']; ?>">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/footer.php'; ?>