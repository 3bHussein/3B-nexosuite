<?php
$pageTitle='Customer Portal 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('customer_portal_dashboard');
$pdo=getDB();
$stats=[
 'requests'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('customer_service_requests').' WHERE status IN ("open","in_progress","waiting_parts")')->fetchColumn(),
 'assets'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('customer_assets').' WHERE status="active"')->fetchColumn(),
 'documents'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('customer_document_uploads'))->fetchColumn(),
 'feedback'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('customer_service_feedback'))->fetchColumn(),
];
$requests=$pdo->query('SELECT csr.*,u.email user_email,ca.asset_name FROM '.table('customer_service_requests').' csr LEFT JOIN '.table('users').' u ON u.id=csr.user_id LEFT JOIN '.table('customer_assets').' ca ON ca.id=csr.customer_asset_id ORDER BY csr.created_at DESC LIMIT 12')->fetchAll();
$feedback=$pdo->query('SELECT f.*,csr.request_number,u.email FROM '.table('customer_service_feedback').' f LEFT JOIN '.table('customer_service_requests').' csr ON csr.id=f.customer_service_request_id LEFT JOIN '.table('users').' u ON u.id=f.user_id ORDER BY f.created_at DESC LIMIT 8')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Customer Experience</div><h2 class="h4 mb-1">Customer Portal 2.0 Dashboard</h2><p class="text-secondary mb-0">Customer assets, requests, documents, invoice disputes, promises, feedback, and notifications.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-announcements.php">Send Announcement</a></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords($k)); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Latest Requests</h2><table class="table"><thead><tr><th>Request</th><th>Customer</th><th>Asset</th><th>Priority</th><th>Status</th></tr></thead><tbody><?php foreach($requests as $r): ?><tr><td><strong><?php echo esc($r['request_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['subject']); ?></div></td><td><?php echo esc($r['user_email']); ?></td><td><?php echo esc($r['asset_name']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['priority'])); ?>"><?php echo esc($r['priority']); ?></span></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Feedback</h2><table class="table"><tbody><?php foreach($feedback as $f): ?><tr><td><strong><?php echo esc($f['request_number']?:'Feedback'); ?></strong><div class="small text-secondary"><?php echo esc($f['email']); ?></div><div>Rating: <?php echo (int)$f['rating']; ?>/5 · NPS <?php echo (int)$f['nps_score']; ?></div></td></tr><?php endforeach; ?><?php if(!$feedback): ?><tr><td class="text-secondary">No feedback yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>