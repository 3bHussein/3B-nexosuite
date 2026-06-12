<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$user = currentUser();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save_settings') {
        saveSetting('license_server_url', trim((string)($_POST['license_server_url'] ?? '')));
        saveSetting('license_heartbeat_enabled', isset($_POST['license_heartbeat_enabled']) ? '1' : '0');
        saveSetting('license_bind_domain', isset($_POST['license_bind_domain']) ? '1' : '0');
        saveSetting('license_bind_fingerprint', isset($_POST['license_bind_fingerprint']) ? '1' : '0');
        saveSetting('license_readonly_when_invalid', isset($_POST['license_readonly_when_invalid']) ? '1' : '0');
        saveSetting('license_grace_days', (string)max(0, (int)($_POST['license_grace_days'] ?? 3)));
        saveSetting('license_heartbeat_interval_hours', (string)max(1, (int)($_POST['license_heartbeat_interval_hours'] ?? 12)));
        hardeningAudit('activation_settings_saved', 'License activation settings were updated.');
        $message = 'Activation settings saved.';
    } elseif ($action === 'activate') {
        $code = trim((string)($_POST['activation_code'] ?? ''));
        if ($code === '') {
            $error = 'Activation code is required.';
        } elseif (licenseApplyCode($code)) {
            saveSetting('license_remote_status', 'active');
            saveSetting('license_activated_at', date('Y-m-d H:i:s'));
            hardeningAudit('license_activated', 'License activation code was accepted.');
            $message = 'License activated successfully.';
        } else {
            hardeningAudit('license_activation_failed', 'Invalid activation code submitted.');
            $error = 'Invalid activation code or this code is not valid for this domain/fingerprint.';
        }
    } elseif ($action === 'deactivate') {
        saveSetting('license_activation_code', '');
        saveSetting('license_payload_json', '');
        saveSetting('license_activated', '0');
        saveSetting('license_remote_status', 'readonly');
        hardeningAudit('license_deactivated', 'License was manually deactivated.');
        $message = 'License deactivated and installation switched to read-only mode.';
    } elseif ($action === 'heartbeat') {
        $result = function_exists('hardeningRemoteHeartbeat') ? hardeningRemoteHeartbeat(true) : licenseRunHeartbeat(true);
        $message = (string)($result['message'] ?? 'Heartbeat completed.');
        if (empty($result['ok'])) {
            $error = $message;
            $message = '';
        }
    }
}

$status = function_exists('licenseStatusSummary') ? licenseStatusSummary() : ['valid'=>false,'status'=>'unknown','message'=>'Unknown'];
$payload = function_exists('licenseStoredPayload') ? licenseStoredPayload() : [];
adminHeader('License Activation Tool', $user);
?>
<div class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">License Activation Tool</h1>
            <p class="text-secondary mb-0">Activate this installation, copy the request code, manage binding rules, and test license server heartbeat.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-danger" href="code-integrity.php">Code Integrity Tool</a>
            <a class="btn btn-outline-dark" href="license-security.php">Security Center</a>
        </div>
    </div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="admin-card h-100">
            <h2 class="h5">Installation Request Code</h2>
            <p class="text-secondary">Send this code to your license server/admin to generate a signed activation code bound to this installation.</p>
            <textarea class="form-control font-monospace" rows="4" readonly onclick="this.select()"><?= esc(licenseRequestCode()) ?></textarea>
            <div class="mt-3 small">
                <strong>Status:</strong> <?= esc((string)($status['status'] ?? 'unknown')) ?><br>
                <strong>Valid:</strong> <?= !empty($status['valid']) ? 'Yes' : 'No' ?><br>
                <strong>Message:</strong> <?= esc((string)($status['message'] ?? '')) ?><br>
                <strong>Remote Status:</strong> <?= esc(function_exists('hardeningRemoteStatus') ? hardeningRemoteStatus() : setting('license_remote_status','active')) ?><br>
                <strong>Fingerprint:</strong><br><code style="word-break:break-all"><?= esc(function_exists('hardeningStrongFingerprint') ? hardeningStrongFingerprint() : licenseCustomerFingerprint()) ?></code>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="admin-card mb-4">
            <h2 class="h5">Activate License</h2>
            <form method="post">
                <input type="hidden" name="action" value="activate">
                <label class="form-label">Signed Activation Code</label>
                <textarea class="form-control font-monospace" rows="7" name="activation_code" placeholder="LIC2.payload.signature"></textarea>
                <div class="form-text">Supported: signed LIC2 payload, or legacy signed request-code signature.</div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary">Activate</button>
                    <button class="btn btn-outline-warning" name="action" value="heartbeat">Force Heartbeat</button>
                    <button class="btn btn-outline-danger" name="action" value="deactivate" onclick="return confirm('Deactivate license and switch to read-only?')">Deactivate</button>
                </div>
            </form>
        </div>
        <div class="admin-card">
            <h2 class="h5">Activation Settings</h2>
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="save_settings">
                <div class="col-md-12">
                    <label class="form-label">License Server URL</label>
                    <input class="form-control" name="license_server_url" value="<?= esc(setting('license_server_url','')) ?>" placeholder="https://yourdomain.com/api/license/validate">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Grace Days</label>
                    <input class="form-control" type="number" min="0" name="license_grace_days" value="<?= esc(setting('license_grace_days','3')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Heartbeat Interval Hours</label>
                    <input class="form-control" type="number" min="1" name="license_heartbeat_interval_hours" value="<?= esc(setting('license_heartbeat_interval_hours','12')) ?>">
                </div>
                <div class="col-md-6 form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="hb" name="license_heartbeat_enabled" <?= setting('license_heartbeat_enabled','0')==='1'?'checked':'' ?>>
                    <label class="form-check-label" for="hb">Enable heartbeat</label>
                </div>
                <div class="col-md-6 form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="bd" name="license_bind_domain" <?= setting('license_bind_domain','1')==='1'?'checked':'' ?>>
                    <label class="form-check-label" for="bd">Bind to domain</label>
                </div>
                <div class="col-md-6 form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="bf" name="license_bind_fingerprint" <?= setting('license_bind_fingerprint','1')==='1'?'checked':'' ?>>
                    <label class="form-check-label" for="bf">Bind to fingerprint</label>
                </div>
                <div class="col-md-6 form-check ms-2">
                    <input class="form-check-input" type="checkbox" id="ro" name="license_readonly_when_invalid" <?= setting('license_readonly_when_invalid','1')==='1'?'checked':'' ?>>
                    <label class="form-check-label" for="ro">Read-only when invalid</label>
                </div>
                <div class="col-12"><button class="btn btn-primary">Save Settings</button></div>
            </form>
        </div>
    </div>
</div>
<?php if ($payload): ?>
<div class="admin-card mt-4">
    <h2 class="h5">Decoded License Payload</h2>
    <pre class="bg-light border rounded p-3" style="white-space:pre-wrap"><?= esc(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
</div>
<?php endif; ?>
<?php adminFooter(); ?>