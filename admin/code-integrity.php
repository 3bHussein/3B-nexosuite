<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$user = currentUser();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'rebuild') {
        saveSetting('license_integrity_manifest_json', json_encode(hardeningBuildIntegrityManifest(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        hardeningAudit('integrity_manifest_rebuilt', 'Code integrity baseline was rebuilt by admin.');
        $message = 'Integrity baseline rebuilt from the current files.';
    } elseif ($action === 'enable') {
        saveSetting('license_integrity_enabled', '1');
        $message = 'Integrity checking enabled.';
    } elseif ($action === 'disable') {
        saveSetting('license_integrity_enabled', '0');
        hardeningAudit('integrity_disabled', 'Code integrity checking was disabled by admin.');
        $message = 'Integrity checking disabled.';
    } elseif ($action === 'export') {
        $manifest = hardeningIntegrityManifest();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="integrity-manifest-' . date('Ymd-His') . '.json"');
        echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($action === 'import') {
        $json = trim((string)($_POST['manifest_json'] ?? ''));
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $error = 'Invalid manifest JSON.';
        } else {
            saveSetting('license_integrity_manifest_json', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            hardeningAudit('integrity_manifest_imported', 'Code integrity manifest was imported by admin.');
            $message = 'Manifest imported.';
        }
    }
}

$integrity = hardeningVerifyIntegrity();
$manifest = hardeningIntegrityManifest();
$current = hardeningBuildIntegrityManifest();
adminHeader('Code Integrity Tool', $user);
?>
<div class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Code Integrity Tool</h1>
            <p class="text-secondary mb-0">Detect changed, missing, or tampered critical files using SHA-256 manifest verification.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-primary" href="license-activation.php">Activation Tool</a>
            <a class="btn btn-outline-dark" href="license-security.php">Security Center</a>
        </div>
    </div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <h2 class="h5">Integrity Status</h2>
            <p><strong>Enabled:</strong> <?= setting('license_integrity_enabled','1')==='1' ? 'Yes' : 'No' ?></p>
            <p><strong>Status:</strong> <?= $integrity['ok'] ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-danger">Tamper warning</span>' ?></p>
            <p><strong>Changed files:</strong> <?= count($integrity['changed']) ?></p>
            <p><strong>Missing files:</strong> <?= count($integrity['missing']) ?></p>
            <p><strong>Watermark:</strong><br><code><?= esc(hardeningWatermark()) ?></code></p>
            <form method="post" class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" name="action" value="rebuild" onclick="return confirm('This will trust current files as the new baseline. Continue?')">Rebuild Baseline</button>
                <button class="btn btn-outline-success" name="action" value="enable">Enable</button>
                <button class="btn btn-outline-warning" name="action" value="disable" onclick="return confirm('Disable integrity checking?')">Disable</button>
                <button class="btn btn-outline-dark" name="action" value="export">Export Manifest</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card mb-4">
            <h2 class="h5">Changed / Missing Files</h2>
            <?php if ($integrity['ok']): ?>
                <div class="alert alert-success">No tampering detected in the current manifest.</div>
            <?php else: ?>
                <?php if ($integrity['changed']): ?>
                    <h3 class="h6">Changed</h3>
                    <ul><?php foreach ($integrity['changed'] as $file): ?><li><code><?= esc($file) ?></code></li><?php endforeach; ?></ul>
                <?php endif; ?>
                <?php if ($integrity['missing']): ?>
                    <h3 class="h6">Missing</h3>
                    <ul><?php foreach ($integrity['missing'] as $file): ?><li><code><?= esc($file) ?></code></li><?php endforeach; ?></ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="admin-card">
            <h2 class="h5">Import Manifest</h2>
            <form method="post">
                <textarea class="form-control font-monospace" rows="8" name="manifest_json" placeholder="Paste exported manifest JSON here"></textarea>
                <button class="btn btn-primary mt-3" name="action" value="import">Import Manifest</button>
            </form>
        </div>
    </div>
</div>
<div class="admin-card mt-4">
    <h2 class="h5">Manifest Details</h2>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>File</th><th>Baseline Hash</th><th>Current Hash</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($manifest as $file => $hash): $actual = $current[$file] ?? null; ?>
                    <tr>
                        <td><code><?= esc($file) ?></code></td>
                        <td><code style="word-break:break-all"><?= esc((string)$hash) ?></code></td>
                        <td><code style="word-break:break-all"><?= esc((string)$actual) ?></code></td>
                        <td><?= $actual === null ? '<span class="badge bg-danger">Missing</span>' : (($actual === $hash) ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-warning text-dark">Changed</span>') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminFooter(); ?>