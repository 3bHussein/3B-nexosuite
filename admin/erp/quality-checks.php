<?php
$pageTitle='Quality Checks';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('quality_checks');
$pdo=getDB();$user=currentUser();
$orders=$pdo->query('SELECT * FROM '.table('manufacturing_work_orders').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $number=nextScopedDocumentNumber($pdo,'quality_check',setting('quality_check_prefix','QC'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('manufacturing_quality_checks').' (quality_number,manufacturing_work_order_id,check_type,result,checked_quantity,passed_quantity,failed_quantity,checked_by,checked_at,notes) VALUES (?,?,?,?,?,?,?,?,NOW(),?)')->execute([$number,(int)$_POST['wo_id'],trim((string)$_POST['check_type']),trim((string)$_POST['result']),max(0,(float)$_POST['checked_quantity']),max(0,(float)$_POST['passed_quantity']),max(0,(float)$_POST['failed_quantity']),(int)($user['id']??0)?:null,trim((string)$_POST['notes'])]);
    flash('success','Quality check saved.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'quality-checks']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/quality-checks.php');
}
$checks=$pdo->query('SELECT q.*,wo.work_order_number FROM '.table('manufacturing_quality_checks').' q LEFT JOIN '.table('manufacturing_work_orders').' wo ON wo.id=q.manufacturing_work_order_id ORDER BY q.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Production QA</div><h2 class="h4 mb-1">Quality Checks</h2><p class="text-secondary mb-0">Record inspection results, passed quantity, failed quantity, and QA notes.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">New Quality Check</h2><select class="form-select mb-2" name="wo_id"><?php foreach($orders as $wo): ?><option value="<?php echo (int)$wo['id']; ?>"><?php echo esc($wo['work_order_number']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="check_type" value="final"><select class="form-select mb-2" name="result"><option value="passed">Passed</option><option value="failed">Failed</option><option value="rework">Rework</option><option value="pending">Pending</option></select><input class="form-control mb-2" type="number" step="0.0001" name="checked_quantity" placeholder="Checked qty"><input class="form-control mb-2" type="number" step="0.0001" name="passed_quantity" placeholder="Passed qty"><input class="form-control mb-2" type="number" step="0.0001" name="failed_quantity" placeholder="Failed qty"><textarea class="form-control mb-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Save Check</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>QC</th><th>WO</th><th>Result</th><th>Checked</th><th>Passed</th><th>Failed</th></tr></thead><tbody><?php foreach($checks as $q): ?><tr><td><strong><?php echo esc($q['quality_number']); ?></strong></td><td><?php echo esc($q['work_order_number']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($q['result'])); ?>"><?php echo esc($q['result']); ?></span></td><td><?php echo number_format((float)$q['checked_quantity'],4); ?></td><td><?php echo number_format((float)$q['passed_quantity'],4); ?></td><td><?php echo number_format((float)$q['failed_quantity'],4); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>