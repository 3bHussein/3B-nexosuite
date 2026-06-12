<?php
/**
 * Safe Admin Dashboard
 * Rebuilt to avoid white screen and fail gracefully when optional ERP/license functions are missing.
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$pageTitle = 'Dashboard';

require_once dirname(__DIR__) . '/includes/functions.php';

if (function_exists('requireAdmin')) {
    requireAdmin();
} else {
    $user = function_exists('currentUser') ? currentUser() : null;
    if (!$user || (($user['role'] ?? '') !== 'admin')) {
        if (function_exists('redirect') && defined('SITE_URL')) {
            redirect(SITE_URL . '/employee/login.php');
        }
        header('Location: /employee/login.php');
        exit;
    }
}

$pdo = function_exists('getDB') ? getDB() : null;

function dashTableExists(PDO $pdo, string $table): bool
{
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function dashCount(?PDO $pdo, string $table): int
{
    if (!$pdo) {
        return 0;
    }

    try {
        $realTable = function_exists('table') ? table($table) : $table;
        if (!dashTableExists($pdo, $realTable)) {
            return 0;
        }

        return (int)$pdo->query('SELECT COUNT(*) FROM ' . $realTable)->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function dashMoney(float $amount): string
{
    if (function_exists('money')) {
        return money($amount);
    }

    $symbol = function_exists('setting') ? (string)setting('currency_symbol', '$') : '$';
    return $symbol . number_format($amount, 2);
}

function dashSum(?PDO $pdo, string $table, string $column): float
{
    if (!$pdo) {
        return 0.0;
    }

    try {
        $realTable = function_exists('table') ? table($table) : $table;
        if (!dashTableExists($pdo, $realTable)) {
            return 0.0;
        }

        return (float)$pdo->query('SELECT COALESCE(SUM(' . $column . '),0) FROM ' . $realTable)->fetchColumn();
    } catch (Throwable $e) {
        return 0.0;
    }
}

function dashLicenseSummary(?PDO $pdo): array
{
    if ($pdo && function_exists('licenseStatusSummary')) {
        try {
            return licenseStatusSummary($pdo);
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    return [];
}

$stats = [
    'products' => dashCount($pdo, 'products'),
    'categories' => dashCount($pdo, 'categories'),
    'customers' => dashCount($pdo, 'customers'),
    'orders' => dashCount($pdo, 'orders'),
    'users' => dashCount($pdo, 'users'),
    'branches' => dashCount($pdo, 'branches'),
    'warehouses' => dashCount($pdo, 'warehouses'),
    'invoices' => dashCount($pdo, 'invoices'),
];

$revenue = dashSum($pdo, 'orders', 'total');
$invoiceTotal = dashSum($pdo, 'invoices', 'total');
$license = dashLicenseSummary($pdo);

try {
    include __DIR__ . '/header.php';
} catch (Throwable $e) {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Dashboard</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body class="bg-light"><div class="container py-4">';
    echo '<div class="alert alert-danger"><strong>Header error:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
}
?>

<div class="container-fluid py-4">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
      <div class="text-uppercase text-secondary small fw-bold">Administrator Console</div>
      <h1 class="mb-1">Dashboard</h1>
      <p class="text-secondary mb-0">System overview, license status, ERP counts and sales activity.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-primary" href="<?php echo function_exists('esc') ? esc(SITE_URL ?? '/') : '/'; ?>" target="_blank">View Storefront</a>
      <a class="btn btn-primary" href="<?php echo function_exists('esc') ? esc(ADMIN_URL ?? '/admin') : '/admin'; ?>/erp/create-invoice.php">New Invoice</a>
      <a class="btn btn-dark" href="<?php echo function_exists('esc') ? esc(ADMIN_URL ?? '/admin') : '/admin'; ?>/logout.php">Logout</a>
    </div>
  </div>

  <?php if (!empty($license['error'])): ?>
    <div class="alert alert-warning">
      <strong>License summary warning:</strong>
      <?php echo htmlspecialchars((string)$license['error'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <?php if ($license): ?>
    <div class="card-admin p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="h5 mb-1">License Status</h2>
          <div class="text-secondary">
            Plan: <strong><?php echo htmlspecialchars((string)($license['plan'] ?? 'trial'), ENT_QUOTES, 'UTF-8'); ?></strong>
            <?php if (!empty($license['expires_at'])): ?>
              · Expires: <strong><?php echo htmlspecialchars((string)$license['expires_at'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <?php endif; ?>
          </div>
        </div>
        <span class="badge bg-<?php echo !empty($license['active']) ? 'success' : 'warning text-dark'; ?> fs-6">
          <?php echo !empty($license['active']) ? 'Activated' : 'Trial / Not Active'; ?>
        </span>
      </div>

      <?php if (!empty($license['limit_locked_entities'])): ?>
        <div class="alert alert-danger mt-3 mb-0">
          Limit reached:
          <?php echo htmlspecialchars(implode(', ', (array)$license['limit_locked_entities']), ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <?php
      $cards = [
        ['Products', $stats['products'], 'admin/products.php'],
        ['Categories', $stats['categories'], 'admin/categories.php'],
        ['Customers', $stats['customers'], 'admin/erp/customers.php'],
        ['Orders', $stats['orders'], 'admin/orders.php'],
        ['Users', $stats['users'], 'admin/users.php'],
        ['Branches', $stats['branches'], 'admin/erp/branches.php'],
        ['Warehouses', $stats['warehouses'], 'admin/erp/warehouses.php'],
        ['ERP Invoices', $stats['invoices'], 'admin/erp/invoices.php'],
      ];
    ?>
    <?php foreach ($cards as $card): ?>
      <div class="col-sm-6 col-xl-3">
        <a class="text-decoration-none text-dark" href="<?php echo htmlspecialchars((defined('SITE_URL') ? SITE_URL : '') . '/' . $card[2], ENT_QUOTES, 'UTF-8'); ?>">
          <div class="card-admin p-4 h-100">
            <div class="text-secondary small"><?php echo htmlspecialchars($card[0], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="display-6 fw-bold"><?php echo (int)$card[1]; ?></div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <div class="col-xl-6">
      <div class="card-admin p-4 h-100">
        <h2 class="h5 mb-3">Sales Summary</h2>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-secondary small">Online Orders Revenue</div>
              <div class="fs-3 fw-bold"><?php echo dashMoney($revenue); ?></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-secondary small">ERP Invoice Total</div>
              <div class="fs-3 fw-bold"><?php echo dashMoney($invoiceTotal); ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-6">
      <div class="card-admin p-4 h-100">
        <h2 class="h5 mb-3">Quick Actions</h2>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-outline-primary" href="<?php echo (defined('ADMIN_URL') ? ADMIN_URL : '/admin'); ?>/add-product.php">Add Product</a>
          <a class="btn btn-outline-primary" href="<?php echo (defined('ADMIN_URL') ? ADMIN_URL : '/admin'); ?>/erp/create-invoice.php">Create ERP Invoice</a>
          <a class="btn btn-outline-primary" href="<?php echo (defined('ADMIN_URL') ? ADMIN_URL : '/admin'); ?>/erp/branches.php">Branches</a>
          <a class="btn btn-outline-primary" href="<?php echo (defined('ADMIN_URL') ? ADMIN_URL : '/admin'); ?>/erp/warehouses.php">Warehouses</a>
          <a class="btn btn-outline-primary" href="<?php echo (defined('ADMIN_URL') ? ADMIN_URL : '/admin'); ?>/license-activation.php">License Tools</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
try {
    include __DIR__ . '/footer.php';
} catch (Throwable $e) {
    echo '</div></body></html>';
}
?>