<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$user = currentUser();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_manifest'])) {
        saveSetting('license_integrity_manifest_json', json_encode(hardeningBuildIntegrityManifest(), JSON_UNESCAPED_SLASHES));
        $message = 'Integrity manifest rebuilt.';
    } elseif (isset($_POST['force_heartbeat'])) {
        $result = hardeningRemoteHeartbeat(true);
        $message = $result['message'] ?? 'Heartbeat completed.';
    } else {
        saveSetting('license_remote_status', $_POST['license_remote_status'] ?? 'active');
        saveSetting('license_server_url', trim((string)($_POST['license_server_url'] ?? '')));
        saveSetting('license_alert_email_enabled', isset($_POST['license_alert_email_enabled']) ? '1' : '0');
        saveSetting('license_alert_email', trim((string)($_POST['license_alert_email'] ?? '')));
        saveSetting('license_integrity_enabled', isset($_POST['license_integrity_enabled']) ? '1' : '0');
        saveSetting('license_allowed_modules_json', trim((string)($_POST['license_allowed_modules_json'] ?? '[]')) ?: '[]');
        $message = 'License security settings saved.';
    }
}

$status = function_exists('licenseStatusSummary') ? licenseStatusSummary() : ['valid' => true, 'status' => 'unknown'];
$integrity = hardeningVerifyIntegrity();
adminHeader('License Security Center', $user);
?>
<div class="admin-card mb-4">
    <div class="d-flex justify-content-between flex-wrap gap-2 align-items-start">
        <div>
            <h1 class="h3 mb-1">License Security Center</h1>
            <p class="text-secondary mb-0">Manage anti-piracy controls, remote status, integrity checks, fingerprinting, and watermarking.</p>
        </div>
        <a class="btn btn-outline-primary" href="license-activation.php">Activation Tool</a>
        <a class="btn btn-outline-danger" href="code-integrity.php">Code Integrity Tool</a>
        <a class="btn btn-outline-dark" href="settings.php">Settings</a>
    </div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <h2 class="h5">Current Status</h2>
            <p><strong>Local license:</strong> <?= esc((string)($status['status'] ?? 'unknown')) ?></p>
            <p><strong>Remote status:</strong> <?= esc(hardeningRemoteStatus()) ?></p>
            <p><strong>Read-only:</strong> <?= hardeningIsReadOnly() ? 'Yes' : 'No' ?></p>
            <p><strong>Watermark:</strong><br><code><?= esc(hardeningWatermark()) ?></code></p>
            <p><strong>Fingerprint:</strong><br><code style="word-break:break-all"><?= esc(hardeningStrongFingerprint()) ?></code></p>
            <p><strong>Integrity:</strong> <?= $integrity['ok'] ? 'OK' : 'Warning' ?></p>
            <?php if (!$integrity['ok']): ?>
                <div class="alert alert-warning small">
                    Changed: <?= esc(implode(', ', $integrity['changed'])) ?><br>
                    Missing: <?= esc(implode(', ', $integrity['missing'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card">
            <h2 class="h5">Security Controls</h2>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Remote Status</label>
                    <select class="form-select" name="license_remote_status">
                        <?php foreach (['active'=>'Active','readonly'=>'Read-only','revoked'=>'Revoked','killed'=>'Killed'] as $value=>$label): ?>
                            <option value="<?= esc($value) ?>" <?= hardeningRemoteStatus()===$value?'selected':'' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">License Server URL</label>
                    <input class="form-control" name="license_server_url" value="<?= esc(setting('license_server_url','')) ?>" placeholder="https://yourdomain.com/api/license/validate">
                </div>
                <div class="col-md-6 form-check mt-4 ms-2">
                    <input class="form-check-input" type="checkbox" name="license_integrity_enabled" id="integrity" <?= setting('license_integrity_enabled','1')==='1'?'checked':'' ?>>
                    <label class="form-check-label" for="integrity">Enable file integrity checks</label>
                </div>
                <div class="col-md-6 form-check mt-4 ms-2">
                    <input class="form-check-input" type="checkbox" name="license_alert_email_enabled" id="alerts" <?= setting('license_alert_email_enabled','0')==='1'?'checked':'' ?>>
                    <label class="form-check-label" for="alerts">Enable email alerts</label>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Alert Email</label>
                    <input class="form-control" name="license_alert_email" value="<?= esc(setting('license_alert_email', SHOP_EMAIL)) ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Allowed Modules JSON</label>
                    <textarea class="form-control" rows="5" name="license_allowed_modules_json"><?= esc(setting('license_allowed_modules_json','[]')) ?></textarea>
                    <div class="form-text">Empty array means all modules are locally allowed until a remote license server sends restrictions.</div>
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary">Save Security Settings</button>
                    <button class="btn btn-outline-dark" name="force_heartbeat" value="1">Force Heartbeat</button>
                    <button class="btn btn-outline-danger" name="reset_manifest" value="1" onclick="return confirm('Rebuild integrity baseline from current files?')">Rebuild Integrity Manifest</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php adminFooter(); ?>