<?php
$pageTitle='Tax Automation 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('tax_automation_2');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'create');
    if($action==='file'){$pdo->prepare('UPDATE '.table('tax_returns').' SET status="filed",filed_at=NOW(),filing_reference=? WHERE id=?')->execute([trim((string)$_POST['filing_reference']),(int)$_POST['return_id']]);flash('success','Tax return marked filed.');}
    else{createTaxReturn($pdo,trim((string)$_POST['period_name']),trim((string)$_POST['date_from']),trim((string)$_POST['date_to']),trim((string)$_POST['notes']));flash('success','Tax return generated.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'tax-automation-2']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/tax-automation-2.php');
}
$returns=$pdo->query('SELECT * FROM '.table('tax_returns').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
$selected=(int)($_GET['id']??($returns[0]['id']??0));$lines=[];
if($selected){$stmt=$pdo->prepare('SELECT * FROM '.table('tax_return_lines').' WHERE tax_return_id=? ORDER BY direction,source_type');$stmt->execute([$selected]);$lines=$stmt->fetchAll();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Tax Control</div><h2 class="h4 mb-1">Tax Automation 2.0</h2><p class="text-secondary mb-0">Generate VAT/tax returns from invoices and expenses, then track filing reference.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Generate Tax Return</h2><input class="form-control mb-2" name="period_name" value="<?php echo date('F Y'); ?>"><div class="row g-2"><div class="col-6"><input class="form-control" type="date" name="date_from" required></div><div class="col-6"><input class="form-control" type="date" name="date_to" required></div></div><textarea class="form-control my-3" name="notes" rows="2"></textarea><button class="btn btn-brand w-100">Generate</button></form><div class="table-wrap table-responsive"><table class="table"><tbody><?php foreach($returns as $r): ?><tr><td><a href="?id=<?php echo (int)$r['id']; ?>"><strong><?php echo esc($r['return_number']); ?></strong></a><div class="small text-secondary"><?php echo esc($r['period_name'].' · '.$r['status']); ?></div></td><td><?php echo money($r['net_tax']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Tax Lines</h2><table class="table"><thead><tr><th>Direction</th><th>Source</th><th>Number</th><th>Taxable</th><th>Tax</th></tr></thead><tbody><?php foreach($lines as $l): ?><tr><td><?php echo esc($l['direction']); ?></td><td><?php echo esc($l['source_type']); ?></td><td><?php echo esc($l['source_number']); ?></td><td><?php echo money($l['taxable_amount']); ?></td><td><?php echo money($l['tax_amount']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php if($selected): ?><form method="post" class="card-admin p-3"><input type="hidden" name="action" value="file"><input type="hidden" name="return_id" value="<?php echo (int)$selected; ?>"><label class="form-label">Filing Reference</label><div class="d-flex gap-2"><input class="form-control" name="filing_reference"><button class="btn btn-success">Mark Filed</button></div></form><?php endif; ?></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>