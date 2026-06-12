<?php
$pageTitle='Supplier Payment Runs';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('supplier_payment_runs');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'create');
    if($action==='approve'){$user=currentUser();$pdo->prepare('UPDATE '.table('supplier_payment_runs').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,(int)$_POST['run_id']]);flash('success','Payment run approved.');}
    elseif($action==='mark_paid'){$pdo->prepare('UPDATE '.table('supplier_payment_runs').' SET status="paid",paid_at=NOW() WHERE id=?')->execute([(int)$_POST['run_id']]);$pdo->prepare('UPDATE '.table('supplier_payment_run_items').' SET status="paid" WHERE supplier_payment_run_id=?')->execute([(int)$_POST['run_id']]);flash('success','Payment run marked paid.');}
    else{createSupplierPaymentRun($pdo,trim((string)$_POST['run_date']),trim((string)$_POST['date_to']),trim((string)$_POST['notes']));flash('success','Supplier payment run created.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'supplier-payment-runs']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/supplier-payment-runs.php');
}
$runs=$pdo->query('SELECT * FROM '.table('supplier_payment_runs').' ORDER BY created_at DESC LIMIT 80')->fetchAll();
$selected=(int)($_GET['id']??($runs[0]['id']??0));$items=[];
if($selected){$stmt=$pdo->prepare('SELECT * FROM '.table('supplier_payment_run_items').' WHERE supplier_payment_run_id=? ORDER BY due_date,expense_number');$stmt->execute([$selected]);$items=$stmt->fetchAll();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Payables Automation</div><h2 class="h4 mb-1">Supplier Payment Runs</h2><p class="text-secondary mb-0">Group due supplier expenses into approval-ready payment runs.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Create Payment Run</h2><input class="form-control mb-2" type="date" name="run_date" value="<?php echo date('Y-m-d'); ?>"><input class="form-control mb-2" type="date" name="date_to" value="<?php echo date('Y-m-d'); ?>"><textarea class="form-control mb-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Create Run</button></form><div class="table-wrap table-responsive"><table class="table"><tbody><?php foreach($runs as $r): ?><tr><td><a href="?id=<?php echo (int)$r['id']; ?>"><strong><?php echo esc($r['payment_run_number']); ?></strong></a><div class="small text-secondary"><?php echo esc($r['run_date'].' · '.$r['status']); ?></div></td><td><?php echo money($r['total_amount']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Selected Run Items</h2><table class="table"><thead><tr><th>Expense</th><th>Vendor</th><th>Due</th><th>Amount</th><th>Status</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?php echo esc($i['expense_number']); ?></td><td><?php echo esc($i['vendor_name']); ?></td><td><?php echo esc($i['due_date']); ?></td><td><?php echo money($i['amount_due']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($i['status'])); ?>"><?php echo esc($i['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php if($selected): ?><form method="post" class="d-flex gap-2"><input type="hidden" name="run_id" value="<?php echo (int)$selected; ?>"><button class="btn btn-success" name="action" value="approve">Approve Run</button><button class="btn btn-outline-primary" name="action" value="mark_paid">Mark Paid</button></form><?php endif; ?></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>