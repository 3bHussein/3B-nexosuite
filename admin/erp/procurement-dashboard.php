<?php
$pageTitle='Procurement Dashboard';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('procurement_dashboard');
$pdo=getDB();
$stats=[
 'suppliers'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('suppliers').' WHERE status="active"')->fetchColumn(),
 'open_rfqs'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('rfqs').' WHERE status IN ("draft","open","sent")')->fetchColumn(),
 'quotes'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('rfq_supplier_quotes').' WHERE status IN ("submitted","awarded")')->fetchColumn(),
 'onboarding'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('supplier_onboarding_requests').' WHERE status IN ("draft","submitted","review")')->fetchColumn(),
];
$rfqs=$pdo->query('SELECT r.*,COUNT(q.id) quote_count FROM '.table('rfqs').' r LEFT JOIN '.table('rfq_supplier_quotes').' q ON q.rfq_id=r.id GROUP BY r.id ORDER BY r.created_at DESC LIMIT 10')->fetchAll();
$scores=$pdo->query('SELECT sc.*,s.company_name FROM '.table('supplier_scorecards').' sc LEFT JOIN '.table('suppliers').' s ON s.id=sc.supplier_id ORDER BY sc.total_score DESC,sc.created_at DESC LIMIT 10')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Strategic Procurement</div><h2 class="h4 mb-1">Procurement Dashboard</h2><p class="text-secondary mb-0">Supplier sourcing, onboarding, RFQ comparison, awards, contracts, scorecards, and procurement control.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/rfq-comparison.php">Compare RFQs</a></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-7"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent RFQs</h2><table class="table"><thead><tr><th>RFQ</th><th>Status</th><th>Quotes</th><th>Awarded</th></tr></thead><tbody><?php foreach($rfqs as $r): ?><tr><td><strong><?php echo esc($r['rfq_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['title']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td><td><?php echo (int)$r['quote_count']; ?></td><td><?php echo esc($r['awarded_supplier_id']?'Yes':'No'); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-5"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Top Supplier Scores</h2><table class="table"><thead><tr><th>Supplier</th><th>Score</th><th>Rating</th></tr></thead><tbody><?php foreach($scores as $s): ?><tr><td><?php echo esc($s['company_name']); ?><div class="small text-secondary"><?php echo esc($s['period_label']); ?></div></td><td><?php echo number_format((float)$s['total_score'],2); ?></td><td><span class="badge bg-<?php echo esc(statusTone($s['rating']==='D'?'danger':'published')); ?>"><?php echo esc($s['rating']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>