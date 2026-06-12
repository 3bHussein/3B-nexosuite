<?php
$pageTitle='Mobile ERP';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('mobile_erp');
$pdo=getDB();$user=currentUser();
$pendingApprovals=(int)$pdo->query('SELECT COUNT(*) FROM '.table('approval_requests').' WHERE status="pending"')->fetchColumn();
$openJobs=(int)$pdo->query('SELECT COUNT(*) FROM '.table('job_cards').' WHERE status IN ("draft","diagnosis","in_progress","waiting_parts")')->fetchColumn();
$openRequests=(int)$pdo->query('SELECT COUNT(*) FROM '.table('customer_service_requests').' WHERE status IN ("open","reviewing","scheduled","in_progress")')->fetchColumn();
$lowStock=(int)$pdo->query('SELECT COUNT(*) FROM '.table('products').' WHERE active=1 AND stock<=3')->fetchColumn();
$actions=[['Approvals','/erp/approvals.php','bi-check2-circle',$pendingApprovals],['Job Cards','/erp/job-cards.php','bi-tools',$openJobs],['Customer Requests','/erp/customer-portal-requests.php','bi-headset',$openRequests],['Low Stock','/erp/inventory.php','bi-box-seam',$lowStock],['Sales Orders','/erp/sales-orders.php','bi-cart-check',0],['Finance','/erp/finance.php','bi-cash-coin',0]];
include dirname(__DIR__).'/header.php';
?>
<style>@media(max-width:768px){.mobile-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.mobile-action{min-height:132px;border-radius:24px}.admin-content{padding:14px!important}}</style>
<div class="d-flex justify-content-between align-items-center mb-4"><div><div class="erp-kicker"><?php echo t('Mobile ERP', 'ERP الموبايل'); ?></div><h2 class="h4 mb-0">Quick Command Center</h2></div><a class="btn btn-sm btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/dashboard.php">Desktop</a></div>
<div class="mobile-grid row g-3"><?php foreach($actions as $a): ?><div class="col-6 col-md-4 col-xl-3"><a class="card-admin p-4 d-block text-decoration-none mobile-action" href="<?php echo esc(ADMIN_URL.$a[1]); ?>"><i class="bi <?php echo esc($a[2]); ?> fs-2"></i><div class="h6 mt-3 mb-1"><?php echo esc($a[0]); ?></div><?php if($a[3]>0): ?><span class="badge bg-danger"><?php echo (int)$a[3]; ?></span><?php else: ?><span class="small text-secondary">Open</span><?php endif; ?></a></div><?php endforeach; ?></div>
<?php include dirname(__DIR__).'/footer.php'; ?>