<?php
$pageTitle='Accounts Payable Aging';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$asOf=trim((string)($_GET['as_of']??date('Y-m-d')));
$stmt=$pdo->query('SELECT e.id,e.expense_number,e.vendor_name,e.category,e.total,e.balance_due,e.payment_status,e.expense_date,e.due_date,e.created_at,s.supplier_code,s.company_name supplier_company FROM ' . table('expenses') . ' e LEFT JOIN ' . table('suppliers') . ' s ON s.id=e.supplier_id WHERE e.balance_due>0 ORDER BY COALESCE(e.due_date,e.expense_date,DATE(e.created_at)) ASC');
$rows=$stmt->fetchAll();
$buckets=['current'=>0,'1_30'=>0,'31_60'=>0,'61_90'=>0,'90_plus'=>0];
foreach($rows as &$row){
  $base=$row['due_date'] ?: ($row['expense_date'] ?: substr((string)$row['created_at'],0,10));
  $days=(int)floor((strtotime($asOf)-strtotime($base))/86400);
  $row['days_open']=$days;
  if($days<=0){$bucket='current';}
  elseif($days<=30){$bucket='1_30';}
  elseif($days<=60){$bucket='31_60';}
  elseif($days<=90){$bucket='61_90';}
  else{$bucket='90_plus';}
  $row['bucket']=$bucket;
  $buckets[$bucket]+=(float)$row['balance_due'];
}unset($row);
$total=array_sum($buckets);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Payables Control</div><h2 class="h4 mb-1">AP Aging</h2><p class="text-secondary mb-0">Supplier-linked expenses and payable balances grouped by due-age bucket.</p></div><form class="d-flex gap-2 align-items-end"><div><label class="form-label">As of</label><input class="form-control" type="date" name="as_of" value="<?php echo esc($asOf); ?>"></div><button class="btn btn-brand">Refresh</button></form></div>
<div class="row g-4 mb-4"><?php foreach(['current'=>'Current','1_30'=>'1–30 Days','31_60'=>'31–60 Days','61_90'=>'61–90 Days','90_plus'=>'90+ Days'] as $key=>$label): ?><div class="col-md-4 col-xl"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc($label); ?></div><div class="metric-sm"><?php echo money($buckets[$key]); ?></div></div></div><?php endforeach; ?></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><h3 class="h5 mb-0">Open Payables</h3><div class="small text-secondary">Total open AP: <?php echo money($total); ?></div></div><div class="d-flex gap-2"><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/ap-supplier-ledger.php">Supplier AP Ledger</a><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-debit-note.php">Create Debit Note</a></div></div><table class="table align-middle"><thead><tr><th>Expense</th><th>Supplier</th><th>Category</th><th>Due</th><th>Days</th><th>Bucket</th><th>Balance</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['expense_number']); ?></strong></td><td><?php echo esc(($row['supplier_code']? $row['supplier_code'].' · ' : '') . ($row['supplier_company'] ?: $row['vendor_name'])); ?></td><td><?php echo esc($row['category']); ?></td><td><?php echo esc($row['due_date'] ?: ($row['expense_date'] ?: substr((string)$row['created_at'],0,10))); ?></td><td><?php echo (int)$row['days_open']; ?></td><td><span class="badge bg-<?php echo $row['bucket']==='current'?'success':($row['bucket']==='90_plus'?'danger':'warning text-dark'); ?>"><?php echo esc(str_replace('_','-',$row['bucket'])); ?></span></td><td><?php echo money($row['balance_due']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No open payables found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>