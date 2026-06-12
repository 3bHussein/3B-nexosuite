<?php
$pageTitle='Profit & Loss';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$from=trim((string)($_GET['from']??''));
$to=trim((string)($_GET['to']??''));
$join=' LEFT JOIN ' . table('journal_lines') . ' jl ON jl.account_id=a.id LEFT JOIN ' . table('journal_entries') . ' je ON je.id=jl.journal_entry_id AND je.status IN ("posted","reversed") ';
$params=[];
if($from!==''){$join.=' AND je.entry_date>=? ';$params[]=$from;}
if($to!==''){$join.=' AND je.entry_date<=? ';$params[]=$to;}
$join.=selectedScopeCondition('je',$params,$filters,['company_id','branch_id']);
$join.=scopeQueryCondition($pdo,'je',$params,false);
$stmt=$pdo->prepare('SELECT a.id account_id,a.account_code,a.account_name,a.account_type,COALESCE(SUM(CASE WHEN je.id IS NOT NULL THEN jl.debit ELSE 0 END),0) debits,COALESCE(SUM(CASE WHEN je.id IS NOT NULL THEN jl.credit ELSE 0 END),0) credits FROM ' . table('accounts') . ' a '.$join.' WHERE a.account_type IN ("revenue","expense") GROUP BY a.id ORDER BY a.account_code ASC');
$stmt->execute($params);
$rows=$stmt->fetchAll();
$revenue=[];$expenses=[];$revenueTotal=0;$expenseTotal=0;
foreach($rows as $row){
  if($row['account_type']==='revenue'){$amount=(float)$row['credits']-(float)$row['debits'];$revenue[]=$row+['amount'=>$amount];$revenueTotal+=$amount;}
  if($row['account_type']==='expense'){$amount=(float)$row['debits']-(float)$row['credits'];$expenses[]=$row+['amount'=>$amount];$expenseTotal+=$amount;}
}
$profit=$revenueTotal-$expenseTotal;
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Accounting Report</div><h2 class="h4 mb-1">Profit & Loss</h2><p class="text-secondary mb-0">Revenue less expenses from posted and reversed journals.</p></div><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">From</label><input class="form-control" type="date" name="from" value="<?php echo esc($from); ?>"></div><div><label class="form-label">To</label><input class="form-control" type="date" name="to" value="<?php echo esc($to); ?>"></div><button class="btn btn-brand">Filter</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Revenue</div><div class="metric-sm money-positive"><?php echo money($revenueTotal); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Expenses</div><div class="metric-sm money-negative"><?php echo money($expenseTotal); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Net Profit</div><div class="metric-sm <?php echo $profit>=0?'money-positive':'money-negative'; ?>"><?php echo money($profit); ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-6"><div class="table-wrap table-responsive"><h3 class="h5 mb-3">Revenue</h3><table class="table"><thead><tr><th>Code</th><th>Account</th><th>Amount</th></tr></thead><tbody><?php foreach($revenue as $row): ?><tr><td><?php echo esc($row['account_code']); ?></td><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/account-ledger.php?account_id=<?php echo (int)$row['account_id']; ?>"><?php echo esc($row['account_name']); ?></a></td><td><?php echo money($row['amount']); ?></td></tr><?php endforeach; ?><tr class="table-light fw-bold"><td colspan="2">Total Revenue</td><td><?php echo money($revenueTotal); ?></td></tr></tbody></table></div></div><div class="col-xl-6"><div class="table-wrap table-responsive"><h3 class="h5 mb-3">Expenses</h3><table class="table"><thead><tr><th>Code</th><th>Account</th><th>Amount</th></tr></thead><tbody><?php foreach($expenses as $row): ?><tr><td><?php echo esc($row['account_code']); ?></td><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/account-ledger.php?account_id=<?php echo (int)$row['account_id']; ?>"><?php echo esc($row['account_name']); ?></a></td><td><?php echo money($row['amount']); ?></td></tr><?php endforeach; ?><tr class="table-light fw-bold"><td colspan="2">Total Expenses</td><td><?php echo money($expenseTotal); ?></td></tr></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>