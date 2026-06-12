<?php
$pageTitle='Migration Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('migration_center');
$pdo=getDB();
$rows=$pdo->query('SELECT * FROM '.table('migration_history').' ORDER BY installed_at DESC,id DESC')->fetchAll();
$tables=(int)$pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn();
$settings=(int)$pdo->query('SELECT COUNT(*) FROM '.table('settings'))->fetchColumn();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Installer & Migration Governance</div><h2 class="h4 mb-1">Migration Center</h2><p class="text-secondary mb-0">Review installed ERP suite version, schema footprint, and migration records.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/backup-restore.php">Create Backup Before Upgrade</a></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Installer Version</div><div class="h5 mb-0"><?php echo esc(INSTALLER_VERSION ?? 'Installed'); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Database Tables</div><div class="metric-sm"><?php echo $tables; ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker"><?php echo t('Settings', 'الإعدادات'); ?></div><div class="metric-sm"><?php echo $settings; ?></div></div></div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Migration History</div><h2 class="h5 mb-0">Installed Milestones</h2></div></div><table class="table align-middle"><thead><tr><th>Migration</th><th>Version</th><th>Description</th><th>Installed</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['migration_key']); ?></strong></td><td><?php echo esc($row['version_label']); ?></td><td><?php echo esc($row['description']); ?></td><td><?php echo esc($row['installed_at']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="4" class="text-secondary">No migration history found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>