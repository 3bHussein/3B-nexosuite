<?php
$pageTitle='Customer Feedback';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('customer_feedback');
$pdo=getDB();
$rows=$pdo->query('SELECT f.*,u.email,csr.request_number,jc.job_card_number FROM '.table('customer_service_feedback').' f LEFT JOIN '.table('users').' u ON u.id=f.user_id LEFT JOIN '.table('customer_service_requests').' csr ON csr.id=f.customer_service_request_id LEFT JOIN '.table('job_cards').' jc ON jc.id=f.job_card_id ORDER BY f.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Voice of Customer</div><h2 class="h4 mb-1">Customer Feedback</h2><p class="text-secondary mb-0">Review customer ratings, NPS score, and comments by request or job card.</p></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Customer</th><th>Reference</th><th>Rating</th><th>NPS</th><th>Feedback</th><th>Date</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?php echo esc($r['email']); ?></td><td><?php echo esc($r['request_number']?:$r['job_card_number']); ?></td><td><?php echo (int)$r['rating']; ?>/5</td><td><?php echo (int)$r['nps_score']; ?></td><td><?php echo esc($r['feedback_text']); ?></td><td><?php echo esc($r['created_at']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>