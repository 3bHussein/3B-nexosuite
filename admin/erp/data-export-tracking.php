<?php
$pageTitle='Data Export Tracking';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('data_export_tracking');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{recordDataExport($pdo,trim((string)$_POST['module']),trim((string)$_POST['export_type']),trim((string)$_POST['file_name']),max(0,(int)$_POST['row_count']),['manual'=>true]);flash('success','Export log created.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'data-export-tracking']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/data-export-tracking.php');
}
$rows=$pdo->query('SELECT d.*,u.email FROM '.table('data_export_logs').' d LEFT JOIN '.table('users').' u ON u.id=d.user_id ORDER BY d.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Data Governance</div><h2 class="h4 mb-1">Data Export Tracking</h2><p class="text-secondary mb-0">Track CSV, PDF and external exports for compliance and data-loss visibility.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Manual Export Log</h2><input class="form-control mb-2" name="module" placeholder="Invoices / Customers / Reports"><select class="form-select mb-2" name="export_type"><option>csv</option><option>pdf</option><option>xlsx</option><option>api</option></select><input class="form-control mb-2" name="file_name" placeholder="export.csv"><input class="form-control mb-3" type="number" name="row_count" placeholder="Row count"><button class="btn btn-brand w-100">Log Export</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Export</th><th>User</th><th>Module</th><th>File</th><th>Rows</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['export_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['created_at'].' · '.$r['ip_address']); ?></div></td><td><?php echo esc($r['email']); ?></td><td><?php echo esc($r['module']); ?></td><td><?php echo esc($r['file_name'].' · '.$r['export_type']); ?></td><td><?php echo (int)$r['row_count']; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>