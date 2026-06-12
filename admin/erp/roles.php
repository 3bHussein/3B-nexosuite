<?php
$pageTitle='Permission Roles';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
adminGuard();
$pdo = getDB();

$labels = permissionLabels();
$visibleLabels = $labels;
unset($visibleLabels['access_erp']); // Access employee ERP portal is controlled from Access Control, not Permission Roles.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
    $roleId = (int)($_POST['role_id'] ?? 0);
    $roleStmt = $pdo->prepare('SELECT * FROM ' . table('erp_roles') . ' WHERE id=? LIMIT 1');
    $roleStmt->execute([$roleId]);
    $targetRole = $roleStmt->fetch();

    if (!$targetRole) {
        flash('error','Permission role not found.');
        redirect(ADMIN_URL . '/erp/roles.php');
    }

    if (isProtectedDeveloperRole($targetRole)) {
        flash('error','Developer Module Controller is a protected system role and cannot be edited from Permission Roles.');
        redirect(ADMIN_URL . '/erp/roles.php');
    }

    $permissions = [];
    foreach ($labels as $key => $label) {
        if ($key === 'access_erp') {
            $permissions[$key] = false;
            continue;
        }
        $permissions[$key] = !empty($_POST['perm'][$key]);
    }

    $stmt = $pdo->prepare('UPDATE ' . table('erp_roles') . ' SET name=?, description=?, permissions=?, active=? WHERE id=? AND slug<>"module_bundle_developer"');
    $stmt->execute([
        trim((string)($_POST['name'] ?? 'Permission Role')),
        trim((string)($_POST['description'] ?? '')),
        json_encode($permissions, JSON_UNESCAPED_SLASHES),
        !empty($_POST['active']) ? 1 : 0,
        $roleId
    ]);
    logActivity($pdo,'Access Control','role_updated','Permission role updated.','erp_role',$roleId);
    flash('success','Permission role updated. ERP portal access is managed separately from Access Control.');
    redirect(ADMIN_URL . '/erp/roles.php');
}

$roles = $pdo->query('SELECT * FROM ' . table('erp_roles') . ' ORDER BY id ASC')->fetchAll();
include dirname(__DIR__) . '/header.php';
?>
<div class="alert alert-info border-0 shadow-sm">
  <strong>ERP portal access is separated:</strong>
  The <code>access_erp</code> permission is hidden here. Employee ERP login is controlled only from <a href="<?php echo esc(ADMIN_URL); ?>/erp/access-control.php">Employee ERP Access</a> using the “ERP login” switch.
</div>

<div class="d-grid gap-4">
<?php foreach($roles as $role): $permissions = json_decode((string)$role['permissions'], true) ?: []; $isDeveloperRole = isProtectedDeveloperRole($role); ?>
<form method="post" class="card-admin p-4 <?php echo $isDeveloperRole ? 'border border-danger' : ''; ?>">
  <input type="hidden" name="action" value="update_role">
  <input type="hidden" name="role_id" value="<?php echo (int)$role['id']; ?>">
  <div class="row g-3 align-items-start">
    <div class="col-xl-4">
      <label class="form-label">Role Name</label>
      <input class="form-control mb-3" name="name" value="<?php echo esc($role['name']); ?>" required <?php echo $isDeveloperRole?'readonly':''; ?>>
      <label class="form-label">Description</label>
      <textarea class="form-control mb-3" name="description" rows="3" <?php echo $isDeveloperRole?'readonly':''; ?>><?php echo esc($role['description']); ?></textarea>

      <?php if($isDeveloperRole): ?>
        <div class="alert alert-warning border mb-3">
          <strong>Protected system role.</strong>
          <div class="small mt-1">This role is reserved for the developer account only and cannot be edited, disabled, or assigned from normal employee access tools.</div>
        </div>
      <?php else: ?>
        <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" value="1" <?php echo !empty($role['active'])?'checked':''; ?>><span class="form-check-label">Role active</span></label>
        <button class="btn btn-brand mt-3">Save Role</button>
      <?php endif; ?>
    </div>
    <div class="col-xl-8">
      <h3 class="h5 mb-3">Permissions</h3>
      <div class="row g-2">
        <?php foreach($visibleLabels as $key => $label): ?>
          <div class="col-md-6">
            <label class="permission-card <?php echo $isDeveloperRole ? 'opacity-75' : ''; ?>">
              <input type="checkbox" name="perm[<?php echo esc($key); ?>]" value="1" <?php echo !empty($permissions[$key])?'checked':''; ?> <?php echo $isDeveloperRole?'disabled':''; ?>>
              <span><strong><?php echo esc($label); ?></strong><small><?php echo esc($key); ?></small></span>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="alert alert-light border mt-3 mb-0">
        <strong>Hidden permission:</strong> <code>access_erp</code> / Access employee ERP portal is not editable from this page.
      </div>
    </div>
  </div>
</form>
<?php endforeach; ?>
</div>
<?php include dirname(__DIR__) . '/footer.php'; ?>