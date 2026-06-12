<?php
$pageTitle='Mobile App Readiness';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('mobile_app_readiness');
$pdo=getDB();
$counts=mobileDashboardCounts($pdo);
$readiness=[
 'manifest_file'=>file_exists(dirname(__DIR__,2).'/manifest.webmanifest'),
 'service_worker'=>file_exists(dirname(__DIR__,2).'/service-worker.js') && setting('pwa_service_worker_enabled','1')==='1',
 'offline_page'=>file_exists(dirname(__DIR__,2).'/offline.php'),
 'pwa_defaults'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('pwa_settings'))->fetchColumn()>0,
 'cache_assets'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('pwa_cache_assets'))->fetchColumn()>0,
 'mobile_pages'=>file_exists(dirname(__DIR__,2).'/mobile/index.php'),
];
$score=0;foreach($readiness as $ok){$score += $ok?16:0;}$score=min(100,$score);
$devices=$pdo->query('SELECT d.*,u.email FROM '.table('mobile_device_sessions').' d LEFT JOIN '.table('users').' u ON u.id=d.user_id ORDER BY d.last_seen_at DESC,d.created_at DESC LIMIT 100')->fetchAll();
$installs=$pdo->query('SELECT e.*,u.email FROM '.table('mobile_app_install_events').' e LEFT JOIN '.table('users').' u ON u.id=e.user_id ORDER BY e.created_at DESC LIMIT 100')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Mobile Deployment</div><h2 class="h4 mb-1">Mobile App / PWA Readiness</h2><p class="text-secondary mb-0">Check installability, offline readiness, mobile pages, device sessions and usage counters.</p></div><a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/mobile/index.php" target="_blank">Open Mobile App</a></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Readiness Score</div><div class="metric-sm"><?php echo (int)$score; ?>%</div></div></div><?php foreach($counts as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Readiness Checklist</h2><table class="table"><tbody><?php foreach($readiness as $k=>$ok): ?><tr><td><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></td><td><span class="badge bg-<?php echo $ok?'success':'danger'; ?>"><?php echo $ok?'Ready':'Missing'; ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Device Sessions</h2><table class="table"><tbody><?php foreach($devices as $d): ?><tr><td><strong><?php echo esc($d['device_number']); ?></strong><div class="small text-secondary"><?php echo esc(($d['email']?:'Guest').' · '.$d['platform'].' · '.$d['device_name']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($d['status'])); ?>"><?php echo esc($d['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Install Events</h2><table class="table"><tbody><?php foreach($installs as $i): ?><tr><td><strong><?php echo esc($i['event_number']); ?></strong><div class="small text-secondary"><?php echo esc(($i['email']?:'Guest').' · '.$i['event_type'].' · '.$i['platform']); ?></div></td><td><?php echo esc($i['status']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>