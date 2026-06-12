<?php
$pageTitle='Handover Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('handover_center');
$pdo=getDB();
$counts=p35ReadinessCounts($pdo);
$docs=['README.md','INSTALLATION.md','USER-MANUAL.md','ADMIN-MANUAL.md','DEVELOPER-HANDOVER.md','COMMERCIAL-PACKAGE.md'];
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Final Product Handover</div><h2 class="h4 mb-1">Handover Center</h2><p class="text-secondary mb-0">Developer, admin, commercial and training handover checklist for the ERP package.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/production-hardening-dashboard.php">Open Production Hardening</a></div>
<div class="row g-4 mb-4"><?php foreach($counts as $k=>$v): ?><div class="col-md-2"><div class="card-admin p-3"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-6"><div class="card-admin p-4"><h2 class="h5 mb-3">Generated Documentation Files</h2><div class="list-group"><?php foreach($docs as $d): ?><a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?php echo esc(SITE_URL.'/'.$d); ?>" target="_blank"><span><?php echo esc($d); ?></span><span>Open</span></a><?php endforeach; ?></div></div></div><div class="col-xl-6"><div class="card-admin p-4"><h2 class="h5 mb-3">Final Release Checklist</h2><ol class="text-secondary"><li>Run Production Hardening scan.</li><li>Run Table & Column Checker.</li><li>Repair settings and permissions.</li><li>Install demo data and verify flows.</li><li>Review documentation articles.</li><li>Open storefront, mobile, admin and portal pages.</li><li>Create release backup before moving to hosting.</li></ol></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>