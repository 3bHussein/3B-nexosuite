<?php
$pageTitle='Employee Loans';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('employee_loans');
$pdo=getDB();$employees=$pdo->query('SELECT * FROM '.table('employees').' WHERE status="active" ORDER BY first_name,last_name')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'loan');
    if($action==='repayment'){
      $loanId=(int)$_POST['loan_id'];$amount=max(0,(float)$_POST['amount']);
      $pdo->prepare('INSERT INTO '.table('employee_loan_repayments').' (employee_loan_id,repayment_date,amount,status,notes) VALUES (?,?,?,"posted",?)')->execute([$loanId,trim((string)$_POST['repayment_date'])?:date('Y-m-d'),$amount,trim((string)$_POST['notes'])]);
      $pdo->prepare('UPDATE '.table('employee_loans').' SET balance_amount=GREATEST(balance_amount-?,0),status=CASE WHEN GREATEST(balance_amount-?,0)<=0 THEN "closed" ELSE status END WHERE id=?')->execute([$amount,$amount,$loanId]);
      flash('success','Loan repayment posted.');
    }else{
      createEmployeeLoan($pdo,(int)$_POST['employee_id'],trim((string)$_POST['loan_type']),max(0,(float)$_POST['principal_amount']),max(0,(float)$_POST['installment_amount']),trim((string)$_POST['start_date']),trim((string)$_POST['notes']));
      flash('success','Employee loan created.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'employee-loans']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/employee-loans.php');
}
$loans=$pdo->query('SELECT l.*,CONCAT(e.first_name," ",e.last_name) employee_name,e.employee_code FROM '.table('employee_loans').' l LEFT JOIN '.table('employees').' e ON e.id=l.employee_id ORDER BY l.created_at DESC LIMIT 150')->fetchAll();
$repay=$pdo->query('SELECT r.*,l.loan_number FROM '.table('employee_loan_repayments').' r LEFT JOIN '.table('employee_loans').' l ON l.id=r.employee_loan_id ORDER BY r.created_at DESC LIMIT 100')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Salary Advances</div><h2 class="h4 mb-1">Employee Loans</h2><p class="text-secondary mb-0">Track employee salary advances, installment deductions, balances and repayments.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Create Loan / Advance</h2><select class="form-select mb-2" name="employee_id"><?php foreach($employees as $e): ?><option value="<?php echo (int)$e['id']; ?>"><?php echo esc($e['employee_code'].' · '.$e['first_name'].' '.$e['last_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="loan_type" value="advance"><input class="form-control mb-2" type="number" step="0.01" name="principal_amount" placeholder="Principal amount"><input class="form-control mb-2" type="number" step="0.01" name="installment_amount" placeholder="Installment amount"><input class="form-control mb-2" type="date" name="start_date"><textarea class="form-control mb-3" name="notes" rows="2"></textarea><button class="btn btn-brand w-100">Create Loan</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="repayment"><h2 class="h5 mb-3">Post Repayment</h2><select class="form-select mb-2" name="loan_id"><?php foreach($loans as $l): ?><option value="<?php echo (int)$l['id']; ?>"><?php echo esc($l['loan_number'].' · '.$l['employee_name'].' · Balance '.money($l['balance_amount'])); ?></option><?php endforeach; ?></select><input class="form-control mb-2" type="number" step="0.01" name="amount" placeholder="Amount"><input class="form-control mb-2" type="date" name="repayment_date"><textarea class="form-control mb-3" name="notes" rows="2"></textarea><button class="btn btn-outline-primary w-100">Post Repayment</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Loans</h2><table class="table"><thead><tr><th>Loan</th><th>Employee</th><th>Principal</th><th>Installment</th><th>Balance</th><th>Status</th></tr></thead><tbody><?php foreach($loans as $l): ?><tr><td><strong><?php echo esc($l['loan_number']); ?></strong><div class="small text-secondary"><?php echo esc($l['loan_type']); ?></div></td><td><?php echo esc($l['employee_code'].' · '.$l['employee_name']); ?></td><td><?php echo money($l['principal_amount']); ?></td><td><?php echo money($l['installment_amount']); ?></td><td><?php echo money($l['balance_amount']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($l['status'])); ?>"><?php echo esc($l['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Repayments</h2><table class="table"><thead><tr><th>Loan</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody><?php foreach($repay as $r): ?><tr><td><?php echo esc($r['loan_number']); ?></td><td><?php echo esc($r['repayment_date']); ?></td><td><?php echo money($r['amount']); ?></td><td><?php echo esc($r['status']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>