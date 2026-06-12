<?php
$pageTitle='HR 2.0 Dashboard';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('hr_dashboard_2');
$pdo=getDB();
$stats=[
 'employees'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('employees').' WHERE status="active"')->fetchColumn(),
 'pending_leave'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('leave_requests').' WHERE status="pending"')->fetchColumn(),
 'today_attendance'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('attendance_records').' WHERE work_date=CURDATE()')->fetchColumn(),
 'open_loans'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('employee_loans').' WHERE status="open"')->fetchColumn(),
];
$leave=$pdo->query('SELECT l.*,CONCAT(e.first_name," ",e.last_name) employee_name FROM '.table('leave_requests').' l LEFT JOIN '.table('employees').' e ON e.id=l.employee_id ORDER BY l.created_at DESC LIMIT 10')->fetchAll();
$payroll=$pdo->query('SELECT pr.*,pp.period_name FROM '.table('payroll_runs').' pr LEFT JOIN '.table('payroll_periods').' pp ON pp.id=pr.payroll_period_id ORDER BY pr.created_at DESC LIMIT 8')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">People Operations</div><h2 class="h4 mb-1">HR, Payroll & ESS 2.0</h2><p class="text-secondary mb-0">Workforce status, attendance, leave, payroll, loans, documents, and employee self-service.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/payroll-runs.php"><?php echo t('Payroll Runs', 'تشغيل الرواتب'); ?></a></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-7"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Leave Requests</h2><table class="table"><thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Status</th></tr></thead><tbody><?php foreach($leave as $r): ?><tr><td><?php echo esc($r['employee_name']); ?></td><td><?php echo esc($r['leave_type']); ?></td><td><?php echo esc($r['start_date'].' → '.$r['end_date']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-5"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Payroll Runs</h2><table class="table"><thead><tr><th>Run</th><th>Net</th><th>Status</th></tr></thead><tbody><?php foreach($payroll as $p): ?><tr><td><strong><?php echo esc($p['payroll_number']); ?></strong><div class="small text-secondary"><?php echo esc($p['period_name']); ?></div></td><td><?php echo money($p['net_total']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($p['status'])); ?>"><?php echo esc($p['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>