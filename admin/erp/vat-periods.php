<?php
$pageTitle='VAT Periods';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$prefillFrom=trim((string)($_GET['from']??date('Y-m-01')));
$prefillTo=trim((string)($_GET['to']??date('Y-m-t')));
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create_period'){
  $from=trim((string)($_POST['date_from']??''));$to=trim((string)($_POST['date_to']??''));$name=trim((string)($_POST['period_name']??'VAT Period'));
  if($from==='' || $to===''){flash('error','VAT period dates are required.');redirect(ADMIN_URL.'/erp/vat-periods.php');}
  $summary=vatLedgerSummary($pdo,$from,$to);
  $stmt=$pdo->prepare('INSERT INTO ' . table('vat_periods') . ' (period_name,date_from,date_to,output_vat,input_vat,net_vat,status,notes) VALUES (?,?,?,?,?,?,"draft",?)');
  $stmt->execute([$name,$from,$to,$summary['output_vat'],$summary['input_vat'],$summary['net_vat'],trim((string)($_POST['notes']??''))]);
  logActivity($pdo,'Accounting','vat_period_created','VAT period snapshot '.$name.' created.','vat_period',(int)$pdo->lastInsertId());
  flash('success','VAT period snapshot created.');
  redirect(ADMIN_URL.'/erp/vat-periods.php');
}
if(isset($_GET['file'])){
  $id=(int)$_GET['file'];$pdo->prepare('UPDATE ' . table('vat_periods') . ' SET status="filed",filed_at=NOW() WHERE id=?')->execute([$id]);logActivity($pdo,'Accounting','vat_period_filed','VAT period marked as filed.','vat_period',$id);flash('success','VAT period marked as filed.');redirect(ADMIN_URL.'/erp/vat-periods.php');
}
$rows=$pdo->query('SELECT * FROM ' . table('vat_periods') . ' ORDER BY date_from DESC,id DESC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
  <div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="create_period"><div class="erp-kicker">VAT Filing Snapshot</div><h2 class="h5 mb-3">Create VAT Period</h2><div class="mb-3"><label class="form-label">Period name</label><input class="form-control" name="period_name" value="VAT <?php echo date('M Y',strtotime($prefillFrom)); ?>"></div><div class="row g-2"><div class="col-md-6"><label class="form-label">From</label><input class="form-control" type="date" name="date_from" value="<?php echo esc($prefillFrom); ?>" required></div><div class="col-md-6"><label class="form-label">To</label><input class="form-control" type="date" name="date_to" value="<?php echo esc($prefillTo); ?>" required></div></div><div class="mt-3"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div><button class="btn btn-brand mt-3">Create Snapshot</button></form></div>
  <div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Tax Reporting Periods</div><h2 class="h5 mb-0">VAT Snapshots</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/vat-report.php">Open VAT Report</a></div><table class="table align-middle"><thead><tr><th>Period</th><th>Date Range</th><th>Output</th><th>Input</th><th>Net VAT</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['period_name']); ?></strong></td><td><?php echo esc($row['date_from'].' → '.$row['date_to']); ?></td><td><?php echo money($row['output_vat']); ?></td><td><?php echo money($row['input_vat']); ?></td><td><?php echo money($row['net_vat']); ?></td><td><span class="badge bg-<?php echo $row['status']==='filed'?'success':'secondary'; ?>"><?php echo esc($row['status']); ?></span></td><td class="text-end"><?php if($row['status']!=='filed'): ?><a class="btn btn-sm btn-outline-success" href="?file=<?php echo (int)$row['id']; ?>">Mark Filed</a><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No VAT periods saved yet.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>