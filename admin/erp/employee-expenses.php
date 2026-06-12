<?php
$pageTitle='Employee Expenses';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('employee_expenses');
$pdo=getDB();$employees=$pdo->query('SELECT * FROM '.table('employees').' WHERE status="active" ORDER BY first_name,last_name')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'create');
    if($action==='create'){
      $number=nextScopedDocumentNumber($pdo,'employee_expense_claim',setting('employee_expense_prefix','EXPCL'),operationalScope($pdo));
      $total=0.0;$items=[];
      foreach(($_POST['category']??[]) as $i=>$cat){$amount=max(0,(float)($_POST['amount'][$i]??0));$tax=max(0,(float)($_POST['tax'][$i]??0));$desc=trim((string)($_POST['description'][$i]??''));if($amount<=0 && $desc===''){continue;}$total+=$amount+$tax;$items[]=[$cat,$desc,$amount,$tax];}
      if(!$items){throw new RuntimeException('Add at least one expense line.');}
      $pdo->prepare('INSERT INTO '.table('employee_expense_claims').' (claim_number,employee_id,claim_date,total_amount,status,approval_status,notes) VALUES (?,?,?,?,"draft","pending",?)')->execute([$number,(int)$_POST['employee_id'],trim((string)$_POST['claim_date']),round($total,2),trim((string)$_POST['notes'])]);
      $claimId=(int)$pdo->lastInsertId();$line=$pdo->prepare('INSERT INTO '.table('employee_expense_claim_items').' (employee_expense_claim_id,category,description,amount,tax) VALUES (?,?,?,?,?)');foreach($items as $item){$line->execute([$claimId,$item[0],$item[1],$item[2],$item[3]]);}
      flash('success','Expense claim created.');
    }elseif($action==='approve'){
      $pdo->prepare('UPDATE '.table('employee_expense_claims').' SET status="approved",approval_status="approved" WHERE id=?')->execute([(int)$_POST['id']]);flash('success','Expense claim approved.');
    }elseif($action==='pay'){
      $pdo->prepare('UPDATE '.table('employee_expense_claims').' SET status="paid",paid_at=NOW() WHERE id=? AND approval_status="approved"')->execute([(int)$_POST['id']]);flash('success','Expense claim marked paid.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'employee-expenses']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/employee-expenses.php');
}
$rows=$pdo->query('SELECT c.*,e.employee_code,e.first_name,e.last_name FROM '.table('employee_expense_claims').' c LEFT JOIN '.table('employees').' e ON e.id=c.employee_id ORDER BY c.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Reimbursements</div><h2 class="h4 mb-1">Employee Expenses</h2><p class="text-secondary mb-0">Capture, approve, and mark employee expense claims as paid.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/payroll-runs.php"><?php echo t('Payroll Runs', 'تشغيل الرواتب'); ?></a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="create"><h2 class="h5 mb-3">Create Claim</h2><label class="form-label">Employee</label><select class="form-select mb-2" name="employee_id"><?php foreach($employees as $e): ?><option value="<?php echo (int)$e['id']; ?>"><?php echo esc(employeeLabel($e)); ?></option><?php endforeach; ?></select><label class="form-label">Claim Date</label><input class="form-control mb-2" type="date" name="claim_date" value="<?php echo esc(date('Y-m-d')); ?>"><?php for($i=0;$i<3;$i++): ?><div class="border rounded-4 p-2 mb-2"><input class="form-control form-control-sm mb-1" name="category[]" placeholder="Category"><input class="form-control form-control-sm mb-1" name="description[]" placeholder="Description"><div class="row g-1"><div class="col-6"><input class="form-control form-control-sm" type="number" step="0.01" name="amount[]" placeholder="Amount"></div><div class="col-6"><input class="form-control form-control-sm" type="number" step="0.01" name="tax[]" placeholder="Tax"></div></div></div><?php endfor; ?><label class="form-label">Notes</label><textarea class="form-control mb-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Create Claim</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Claim</th><th>Employee</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['claim_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['notes']); ?></div></td><td><?php echo esc(($row['employee_code']?:'').' '.$row['first_name'].' '.$row['last_name']); ?></td><td><?php echo esc($row['claim_date']); ?></td><td><?php echo money($row['total_amount']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td class="text-end"><?php if($row['approval_status']!=='approved'): ?><form method="post" class="d-inline"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="btn btn-sm btn-outline-success">Approve</button></form><?php endif; ?><?php if($row['status']==='approved'): ?><form method="post" class="d-inline"><input type="hidden" name="action" value="pay"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="btn btn-sm btn-brand">Pay</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No expense claims yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>