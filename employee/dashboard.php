<?php
require_once dirname(__DIR__) . '/includes/functions.php';
employeePortalGuard();
$user = currentUser();
if (isModuleBundleDeveloper($user)) {
    redirect(ADMIN_URL . '/erp/module-bundle-manager.php');
}
$permissions = rolePermissions($user);
$pdo = getDB();
$roleName = 'Administrator';
if (($user['role'] ?? '') !== 'admin' && !empty($user['erp_role_id'])) {
    $stmt = $pdo->prepare('SELECT name FROM ' . table('erp_roles') . ' WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$user['erp_role_id']]);
    $roleName = (string)($stmt->fetchColumn() ?: 'Assigned Employee Role');
}
$cards = [
    ['access_erp','My Attendance','View attendance history and upcoming shifts.', SITE_URL . '/employee/attendance.php','bi-clock-history'],
    ['access_erp','My Leave','Submit leave requests and check balances.', SITE_URL . '/employee/leave.php','bi-calendar-check'],
    ['access_erp','My Expenses','Submit expense claims and receipt uploads.', SITE_URL . '/employee/expenses.php','bi-wallet2'],
    ['access_erp','My Payslips','View approved payroll run items and payslips.', SITE_URL . '/employee/payslips.php','bi-file-earmark-pdf'],
    ['access_erp','My Documents','Upload and review employee documents.', SITE_URL . '/employee/documents.php','bi-folder2-open'],
    ['access_erp','My Goals','Review goals and performance reviews.', SITE_URL . '/employee/goals.php','bi-bullseye'],
    ['dashboard','ERP Command Center','Analytics, KPIs, and high-level business operations.', ADMIN_URL . '/erp/dashboard.php','bi-speedometer2'],
    ['crm','CRM Leads','Customer lead stages, follow-ups, and conversion opportunities.', ADMIN_URL . '/erp/crm.php','bi-person-lines-fill'],
    ['customers','ERP Customers','Company and retail account records.', ADMIN_URL . '/erp/customers.php','bi-buildings'],
    ['quotations','Quotations','Commercial quotes, approvals, and invoice conversion.', ADMIN_URL . '/erp/quotations.php','bi-file-earmark-text'],
    ['invoices','Invoices','Receivables, approvals, payments, and commercial sales.', ADMIN_URL . '/erp/invoices.php','bi-receipt'],
    ['finance','Finance','Payments, expenses, cash position, and receivables.', ADMIN_URL . '/erp/finance.php','bi-cash-coin'],
    ['accounting','Accounting Core','Chart of accounts, journals, trial balance, P&L, and balance sheet.', ADMIN_URL . '/erp/trial-balance.php','bi-journal-check'],
    ['inventory','Inventory','Stock balances and movement controls.', ADMIN_URL . '/erp/inventory.php','bi-box-seam'],
    ['suppliers','Suppliers','Supplier records and commercial procurement data.', ADMIN_URL . '/erp/suppliers.php','bi-truck'],
    ['purchase_orders','Purchase Orders','Procurement, receiving, and replenishment.', ADMIN_URL . '/erp/purchase-orders.php','bi-clipboard-check'],
    ['hr','HR Workspace','Employees and leave request workflows.', ADMIN_URL . '/erp/employees.php','bi-people'],
    ['reports','Reports','Business performance and operational reporting.', ADMIN_URL . '/erp/reports.php','bi-bar-chart'],
    ['activity_log','Activity Log','Trace important ERP actions and operational events.', ADMIN_URL . '/erp/activity-log.php','bi-clock-history'],
    ['online_sales_orders','Online Orders','Website order and booking operations.', ADMIN_URL . '/orders.php','bi-bag-check'],
    ['online_products','Website Products','Products and categories visible on the storefront.', ADMIN_URL . '/products.php','bi-shop'],
];
siteHeader('Employee Workspace', 'login');
?>
<section class="employee-portal-hero mb-4">
  <div>
    <span class="eyebrow-light">Employee Access</span>
    <h1>Welcome, <?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['email'] ?? 'Employee')) ?></h1>
    <p>Your assigned role is <strong><?= esc($roleName) ?></strong>. Only approved modules appear below.</p>
  </div>
  <a class="btn btn-light btn-lg" href="<?= esc(SITE_URL) ?>/employee/logout.php">Logout</a>
</section>

<section class="employee-access-grid">
  <?php foreach ($cards as [$permission,$title,$description,$url,$icon]): ?>
    <?php if (hasPermission($permission)): ?>
      <a class="employee-access-card" href="<?= esc($url) ?>">
        <i class="bi <?= esc($icon) ?>"></i>
        <h2><?= esc($title) ?></h2>
        <p><?= esc($description) ?></p>
        <span>Open module <i class="bi bi-arrow-right"></i></span>
      </a>
    <?php endif; ?>
  <?php endforeach; ?>
</section>
<?php siteFooter(); ?>