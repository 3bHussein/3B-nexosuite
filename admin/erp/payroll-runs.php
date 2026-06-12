<?php
$pageTitle='Payroll Runs';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('payroll');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=(string)($_POST['action']??'');
  $pdo->beginTransaction();
  try{
    if($action==='create_period'){
      $number=nextScopedDocumentNumber($pdo,'payroll_period',setting('payroll_period_prefix','PAYP'),operationalScope($pdo));
      $pdo->prepare('INSERT INTO '.table('payroll_periods').' (period_number,period_name,start_date,end_date,status) VALUES (?,?,?,?, "open")')->execute([$number,trim((string)$_POST['period_name']),trim((string)$_POST['start_date']),trim((string)$_POST['end_date'])]);
      flash('success','Payroll period created.');
    }elseif($action==='generate_run'){
      $runId=generatePayrollRun($pdo,(int)$_POST['period_id']);flash('success','Payroll run generated.');
    }elseif($action==='approve_run'){
      approvePayrollRun($pdo,(int)$_POST['run_id']);flash('success','Payroll run approved.');
    }elseif($action==='post_run'){
      $pdo->prepare('UPDATE '.table('payroll_runs').' SET status="posted",posted_at=NOW() WHERE id=? AND status="approved"')->execute([(int)$_POST['run_id']]);flash('success','Payroll run posted.');
    }
    $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}recordSystemError($pdo,$e,['page'=>'payroll-runs']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/payroll-runs.php');
}
$periods=$pdo->query('SELECT * FROM '.table('payroll_periods').' ORDER BY start_date DESC LIMIT 100')->fetchAll();
$runs=$pdo->query('SELECT r.*,p.period_name,p.start_date,p.end_date,(SELECT COUNT(*) FROM '.table('payroll_run_items').' i WHERE i.payroll_run_id=r.id) employee_count FROM '.table('payroll_runs').' r LEFT JOIN '.table('payroll_periods').' p ON p.id=r.payroll_period_id ORDER BY r.created_at DESC LIMIT 100')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Payroll Processing</div><h2 class="h4 mb-1">Payroll Runs</h2><p class="text-secondary mb-0">Create payroll periods, generate payroll from employees, attendance, commissions, and approved expenses.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/payroll-dashboard.php">Dashboard</a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><input type="hidden" name="action" value="create_period"><h2 class="h5 mb-3">Create Payroll Period</h2><label class="form-label">Period Name</label><input class="form-control mb-2" name="period_name" value="<?php echo esc(date('F Y')); ?>"><div class="row g-2"><div class="col-6"><label class="form-label">Start</label><input class="form-control" type="date" name="start_date" value="<?php echo esc(date('Y-m-01')); ?>"></div><div class="col-6"><label class="form-label">End</label><input class="form-control" type="date" name="end_date" value="<?php echo esc(date('Y-m-t')); ?>"></div></div><button class="btn btn-brand w-100 mt-3">Create Period</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="generate_run"><h2 class="h5 mb-3">Generate Payroll</h2><select class="form-select mb-3" name="period_id"><?php foreach($periods as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo esc($p['period_number'].' · '.$p['period_name']); ?></option><?php endforeach; ?></select><button class="btn btn-success w-100">Generate Run</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Payroll Register</div><h2 class="h5 mb-0">Runs</h2></div></div><table class="table align-middle"><thead><tr><th>Payroll</th><th>Period</th><th>Employees</th><th>Gross</th><th>Net</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($runs as $run): ?><tr><td><strong><?php echo esc($run['payroll_number']); ?></strong></td><td><?php echo esc($run['period_name']); ?><div class="small text-secondary"><?php echo esc($run['start_date'].' → '.$run['end_date']); ?></div></td><td><?php echo (int)$run['employee_count']; ?></td><td><?php echo money($run['gross_total']); ?></td><td><?php echo money($run['net_total']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($run['status'])); ?>"><?php echo esc($run['status']); ?></span></td><td class="text-end"><?php if($run['status']==='draft'): ?><form method="post" class="d-inline"><input type="hidden" name="action" value="approve_run"><input type="hidden" name="run_id" value="<?php echo (int)$run['id']; ?>"><button class="btn btn-sm btn-outline-success">Approve</button></form><?php endif; ?><?php if($run['status']==='approved'): ?><form method="post" class="d-inline"><input type="hidden" name="action" value="post_run"><input type="hidden" name="run_id" value="<?php echo (int)$run['id']; ?>"><button class="btn btn-sm btn-brand">Post</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$runs): ?><tr><td colspan="7" class="text-secondary">No payroll runs yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>