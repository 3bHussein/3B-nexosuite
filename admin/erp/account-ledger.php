<?php
$pageTitle='Account Ledger';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$accounts=$pdo->query('SELECT id,account_code,account_name,normal_balance FROM ' . table('accounts') . ' ORDER BY account_code ASC')->fetchAll();
$accountId=(int)($_GET['account_id']??($accounts[0]['id']??0));
$from=trim((string)($_GET['from']??''));
$to=trim((string)($_GET['to']??''));
$account=null;
foreach($accounts as $candidate){if((int)$candidate['id']===$accountId){$account=$candidate;break;}}
if(!$account && $accounts){$account=$accounts[0];$accountId=(int)$account['id'];}

$opening=0.0;
$rows=[];
if($accountId>0){
  $openSql='SELECT COALESCE(SUM(jl.debit-jl.credit),0) FROM ' . table('journal_lines') . ' jl INNER JOIN ' . table('journal_entries') . ' je ON je.id=jl.journal_entry_id AND je.status IN ("posted","reversed") WHERE jl.account_id=?';
  $openParams=[$accountId];
  if($from!==''){$openSql.=' AND je.entry_date<?';$openParams[]=$from;}
  else{$openSql.=' AND 1=0';}
  $openSql.=selectedScopeCondition('je',$openParams,$filters,['company_id','branch_id']);
  $openSql.=scopeQueryCondition($pdo,'je',$openParams,false);
  $stmt=$pdo->prepare($openSql);$stmt->execute($openParams);$opening=(float)$stmt->fetchColumn();

  $sql='SELECT je.id journal_id,je.journal_number,je.entry_date,je.reference_type,je.reference_id,je.memo,je.status,jl.description,jl.debit,jl.credit FROM ' . table('journal_lines') . ' jl INNER JOIN ' . table('journal_entries') . ' je ON je.id=jl.journal_entry_id AND je.status IN ("posted","reversed") WHERE jl.account_id=?';
  $params=[$accountId];
  if($from!==''){$sql.=' AND je.entry_date>=?';$params[]=$from;}
  if($to!==''){$sql.=' AND je.entry_date<=?';$params[]=$to;}
  $sql.=selectedScopeCondition('je',$params,$filters,['company_id','branch_id']);
  $sql.=scopeQueryCondition($pdo,'je',$params,false);
  $sql.=' ORDER BY je.entry_date ASC,je.id ASC,jl.id ASC';
  $stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
}
$running=$opening;
$totalDebit=0;$totalCredit=0;
foreach($rows as &$row){$totalDebit+=(float)$row['debit'];$totalCredit+=(float)$row['credit'];$running+=(float)$row['debit']-(float)$row['credit'];$row['running_balance']=$running;}unset($row);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Ledger Drilldown</div><h2 class="h4 mb-1"><?php echo esc(($account['account_code']??'').' · '.($account['account_name']??'Account')); ?></h2><p class="text-secondary mb-0">Running debit-credit ledger balance with direct journal links.</p></div>
  <form class="d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label">Account</label><select class="form-select" name="account_id"><?php foreach($accounts as $option): ?><option value="<?php echo (int)$option['id']; ?>" <?php echo (int)$option['id']===$accountId?'selected':''; ?>><?php echo esc($option['account_code'].' · '.$option['account_name']); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">From</label><input class="form-control" type="date" name="from" value="<?php echo esc($from); ?>"></div>
    <div><label class="form-label">To</label><input class="form-control" type="date" name="to" value="<?php echo esc($to); ?>"></div>
    <button class="btn btn-brand">Filter</button>
  </form>
</div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Opening</div><div class="metric-sm"><?php echo money($opening); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Debits</div><div class="metric-sm"><?php echo money($totalDebit); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Credits</div><div class="metric-sm"><?php echo money($totalCredit); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Closing</div><div class="metric-sm"><?php echo money($running); ?></div></div></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Journal</th><th>Reference</th><th>Description</th><th>Debit</th><th>Credit</th><th>Running</th></tr></thead><tbody><tr class="table-light fw-bold"><td colspan="6">Opening Balance</td><td><?php echo money($opening); ?></td></tr><?php foreach($rows as $row): ?><tr><td><?php echo esc($row['entry_date']); ?></td><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/view-journal-entry.php?id=<?php echo (int)$row['journal_id']; ?>"><?php echo esc($row['journal_number']); ?></a></td><td><?php echo esc(($row['reference_type']?:'manual').(!empty($row['reference_id'])?' #'.$row['reference_id']:'')); ?></td><td><?php echo esc($row['description'] ?: $row['memo']); ?></td><td><?php echo money($row['debit']); ?></td><td><?php echo money($row['credit']); ?></td><td><?php echo money($row['running_balance']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No ledger lines found for the selected filter.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>