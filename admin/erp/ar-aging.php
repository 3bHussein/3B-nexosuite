<?php
$pageTitle='Accounts Receivable Aging';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$asOf=trim((string)($_GET['as_of']??date('Y-m-d')));
$stmt=$pdo->prepare('SELECT id,invoice_number,customer_name,customer_email,due_date,total,amount_paid,balance_due,status FROM ' . table('invoices') . ' WHERE balance_due>0 AND status IN ("approved","sent","partial") ORDER BY COALESCE(due_date,DATE(created_at)) ASC');
$stmt->execute();
$rows=$stmt->fetchAll();
$buckets=['current'=>0,'1_30'=>0,'31_60'=>0,'61_90'=>0,'90_plus'=>0];
foreach($rows as &$row){
  $due=$row['due_date'] ?: $asOf;
  $days=(int)floor((strtotime($asOf)-strtotime($due))/86400);
  $row['days_past_due']=$days;
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
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Receivables Control</div><h2 class="h4 mb-1">AR Aging</h2><p class="text-secondary mb-0">Outstanding customer invoice balances grouped by due-age bucket.</p></div><form class="d-flex gap-2 align-items-end"><div><label class="form-label">As of</label><input class="form-control" type="date" name="as_of" value="<?php echo esc($asOf); ?>"></div><button class="btn btn-brand">Refresh</button></form></div>
<div class="row g-4 mb-4"><?php foreach(['current'=>'Current','1_30'=>'1–30 Days','31_60'=>'31–60 Days','61_90'=>'61–90 Days','90_plus'=>'90+ Days'] as $key=>$label): ?><div class="col-md-4 col-xl"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc($label); ?></div><div class="metric-sm"><?php echo money($buckets[$key]); ?></div></div></div><?php endforeach; ?></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><h3 class="h5 mb-0">Outstanding Receivables</h3><div class="small text-secondary">Total open AR: <?php echo money($total); ?></div></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/invoices.php">Open Invoices</a></div><table class="table align-middle"><thead><tr><th>Invoice</th><th>Customer</th><th>Due Date</th><th>Days</th><th>Bucket</th><th>Balance</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/view-invoice.php?id=<?php echo (int)$row['id']; ?>"><strong><?php echo esc($row['invoice_number']); ?></strong></a></td><td><?php echo esc($row['customer_name']); ?><div class="small text-secondary"><?php echo esc($row['customer_email']); ?></div></td><td><?php echo esc($row['due_date'] ?: 'No due date'); ?></td><td><?php echo (int)$row['days_past_due']; ?></td><td><span class="badge bg-<?php echo $row['bucket']==='current'?'success':($row['bucket']==='90_plus'?'danger':'warning text-dark'); ?>"><?php echo esc(str_replace('_','-',$row['bucket'])); ?></span></td><td><?php echo money($row['balance_due']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No open receivables found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>