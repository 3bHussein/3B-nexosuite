<?php
$pageTitle='Budgeting';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('budgeting');
$pdo=getDB();
$accounts=$pdo->query('SELECT id,account_code,account_name FROM '.table('accounts').' WHERE active=1 ORDER BY account_code')->fetchAll();
$budgets=$pdo->query('SELECT * FROM '.table('budget_versions').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'budget');
    if($action==='line'){
      $pdo->prepare('INSERT INTO '.table('budget_lines').' (budget_version_id,account_id,period_label,budget_amount) VALUES (?,?,?,?)')->execute([(int)$_POST['budget_id'],(int)$_POST['account_id'],trim((string)$_POST['period_label']),max(0,(float)$_POST['budget_amount'])]);
      refreshBudgetActuals($pdo,(int)$_POST['budget_id']);flash('success','Budget line added and actuals refreshed.');
    }elseif($action==='refresh'){
      refreshBudgetActuals($pdo,(int)$_POST['budget_id']);flash('success','Budget actuals refreshed.');
    }elseif($action==='approve'){
      $user=currentUser();$pdo->prepare('UPDATE '.table('budget_versions').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,(int)$_POST['budget_id']]);flash('success','Budget approved.');
    }else{
      createBudgetVersion($pdo,trim((string)$_POST['budget_name']),trim((string)$_POST['date_from']),trim((string)$_POST['date_to']),trim((string)$_POST['notes']));flash('success','Budget version created.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'budgeting']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/budgeting.php');
}
$lines=$pdo->query('SELECT l.*,b.budget_number,a.account_code,a.account_name FROM '.table('budget_lines').' l LEFT JOIN '.table('budget_versions').' b ON b.id=l.budget_version_id LEFT JOIN '.table('accounts').' a ON a.id=l.account_id ORDER BY l.created_at DESC LIMIT 180')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Planning & Control</div><h2 class="h4 mb-1">Budgeting</h2><p class="text-secondary mb-0">Create budgets, compare actuals, and track variances from posted journals.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Create Budget</h2><input class="form-control mb-2" name="budget_name" placeholder="FY Budget"><div class="row g-2"><div class="col-6"><input class="form-control" type="date" name="date_from" required></div><div class="col-6"><input class="form-control" type="date" name="date_to" required></div></div><textarea class="form-control my-3" name="notes" rows="2"></textarea><button class="btn btn-brand w-100">Create Budget</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="line"><h2 class="h5 mb-3">Add Budget Line</h2><select class="form-select mb-2" name="budget_id"><?php foreach($budgets as $b): ?><option value="<?php echo (int)$b['id']; ?>"><?php echo esc($b['budget_number'].' · '.$b['budget_name']); ?></option><?php endforeach; ?></select><select class="form-select mb-2" name="account_id"><?php foreach($accounts as $a): ?><option value="<?php echo (int)$a['id']; ?>"><?php echo esc($a['account_code'].' · '.$a['account_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="period_label" value="<?php echo date('Y-m'); ?>"><input class="form-control mb-3" type="number" step="0.01" name="budget_amount" placeholder="Budget amount"><button class="btn btn-outline-primary w-100">Add Line</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Budgets</h2><table class="table"><thead><tr><th>Budget</th><th>Dates</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($budgets as $b): ?><tr><td><strong><?php echo esc($b['budget_number']); ?></strong><div class="small text-secondary"><?php echo esc($b['budget_name']); ?></div></td><td><?php echo esc($b['date_from'].' → '.$b['date_to']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($b['status'])); ?>"><?php echo esc($b['status']); ?></span></td><td><form method="post" class="d-flex gap-1"><input type="hidden" name="budget_id" value="<?php echo (int)$b['id']; ?>"><button class="btn btn-sm btn-outline-primary" name="action" value="refresh">Refresh</button><?php if($b['status']!=='approved'): ?><button class="btn btn-sm btn-success" name="action" value="approve">Approve</button><?php endif; ?></form></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Budget Lines</h2><table class="table"><thead><tr><th>Budget</th><th>Account</th><th>Budget</th><th>Actual</th><th>Variance</th><th>%</th></tr></thead><tbody><?php foreach($lines as $l): ?><tr><td><?php echo esc($l['budget_number']); ?></td><td><?php echo esc($l['account_code'].' · '.$l['account_name']); ?></td><td><?php echo money($l['budget_amount']); ?></td><td><?php echo money($l['actual_amount']); ?></td><td><?php echo money($l['variance_amount']); ?></td><td><?php echo number_format((float)$l['variance_percent'],2); ?>%</td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>