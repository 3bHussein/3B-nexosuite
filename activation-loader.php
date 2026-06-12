<?php
$pageTitle = 'Software Activation';
require_once __DIR__ . '/includes/functions.php';

$user = currentUser();
if (!$user) {
    redirect(SITE_URL . '/employee/login.php');
}
$activationEmail = strtolower(trim((string)($user['email'] ?? '')));
if (($user['role'] ?? '') !== 'admin' && $activationEmail !== '3b@me.com') {
    flash('error', 'Only an administrator can activate the software.');
    redirect(ADMIN_URL . '/dashboard.php');
}

$pdo = getDB();
$state = licenseStatusSummary($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activationCode = trim((string)($_POST['activation_code'] ?? ''));
    if (licenseApplyCode($activationCode)) {
        flash('success', 'Software activated successfully. Products, categories, customers and orders are now unlocked.');
        redirect(SITE_URL . '/activation-loader.php');
    }
    flash('error', 'Invalid activation code. The activation code must be generated from this exact customer request code.');
    redirect(SITE_URL . '/activation-loader.php');
}

include __DIR__ . '/admin/header.php';
$state = licenseStatusSummary($pdo);
?>
<div class="row justify-content-center">
  <div class="col-xl-9">
    <div class="card p-4 shadow-sm border-0">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
        <div>
          <h1 class="mb-1">Software Activation</h1>
          <p class="text-secondary mb-0">Trial mode allows up to 5 products, categories, customers and orders. This request code is unique to this customer installation.</p>
        </div>
        <span class="badge bg-<?= $state['active'] ? 'success' : 'warning text-dark' ?> fs-6"><?= $state['active'] ? 'Activated' : 'Trial Mode' ?></span>
      </div>

      <hr>

      <div class="row g-3 mb-4">
        <?php foreach ($state['entities'] as $entity): ?>
          <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100 <?= !$state['active'] && !empty($entity['locked']) ? 'border-danger' : '' ?>">
              <div class="text-secondary small"><?= esc($entity['label']) ?></div>
              <div class="fs-3 fw-bold"><?= (int)$entity['count'] ?> / <?= $state['active'] ? '∞' : (int)$entity['limit'] ?></div>
              <?php if (!$state['active']): ?>
                <div class="small <?= !empty($entity['locked']) ? 'text-danger' : 'text-secondary' ?>"><?= !empty($entity['locked']) ? 'Activation required' : ((int)$entity['remaining'] . ' remaining') ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($state['active']): ?>
        <div class="alert alert-success">
          <strong>Activated.</strong> This installation is active. Activated at: <?= esc($state['activated_at'] ?: 'saved') ?>.<?php if (!empty($state['expires_at'])): ?><br><strong>License Expires:</strong> <?= esc($state['expires_at']) ?><?php if ($state['days_remaining'] !== null): ?> (<?= (int)$state['days_remaining'] ?> days remaining)<?php endif; ?><?php endif; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">
          <strong>Please activate code.</strong> Send the request code below to the software owner. The owner must generate the activation code with the private activation generator.
        </div>

        <label class="form-label fw-bold">Unique Customer Request Code</label>
        <div class="input-group mb-3">
          <input class="form-control font-monospace" id="requestCode" value="<?= esc($state['request_code']) ?>" readonly>
          <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('requestCode').value)">Copy</button>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Installation UID</label>
            <input class="form-control font-monospace" value="<?= esc($state['installation_uid']) ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Customer Fingerprint</label>
            <input class="form-control font-monospace" value="<?= esc(substr($state['customer_fingerprint'],0,32)) ?>" readonly>
          </div>
        </div>

        <form method="post" class="mt-3">
          <label class="form-label fw-bold">Activation Code</label>
          <textarea name="activation_code" class="form-control font-monospace" rows="5" required placeholder="Paste activation code here"></textarea>
          <button class="btn btn-primary mt-3">Activate Software</button>
        </form>
      <?php endif; ?>

      <div class="small text-muted mt-4">
        Trial mode is non-destructive. It blocks creation of new products, categories, customers and orders after the trial limit. Existing files and database records are not deleted or encrypted.
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/admin/footer.php'; ?>