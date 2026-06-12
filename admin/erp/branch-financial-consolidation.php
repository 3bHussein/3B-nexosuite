<?php
$pageTitle='Branch Financial Consolidation';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('consolidation');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);
$companyId=(int)($_GET['company_id']??$defaultScope['company_id']);
$from=trim((string)($_GET['from']??date('Y-01-01')));
$to=trim((string)($_GET['to']??date('Y-m-d')));
if($companyId>0 && !scopeAllowed($pdo,$companyId,0,0,false)){flash('error','You do not have access to this company consolidation view.');redirect(ADMIN_URL.'/erp/company-dashboard.php');}

$branchParams=[];$branchWhere=' WHERE b.company_id=?';$branchParams[]=$companyId;
$branchAccess=branchTableAccessCondition($pdo,'b',$branchParams,false);$branchWhere.=$branchAccess;
$branchStmt=$pdo->prepare('SELECT b.*,c.company_name FROM '.table('branches').' b LEFT JOIN '.table('companies').' c ON c.id=b.company_id'.$branchWhere.' ORDER BY b.branch_name ASC');
$branchStmt->execute($branchParams);$branchRows=$branchStmt->fetchAll();

$dateSql=' AND je.entry_date>=? AND je.entry_date<=? ';
$ledgerStmt=$pdo->prepare('SELECT COALESCE(SUM(CASE WHEN a.account_type="revenue" THEN jl.credit-jl.debit ELSE 0 END),0) revenue,COALESCE(SUM(CASE WHEN a.account_type="expense" THEN jl.debit-jl.credit ELSE 0 END),0) expenses FROM '.table('journal_entries').' je LEFT JOIN '.table('journal_lines').' jl ON jl.journal_entry_id=je.id LEFT JOIN '.table('accounts').' a ON a.id=jl.account_id WHERE je.branch_id=? AND je.status IN ("posted","reversed")'.$dateSql);
$inventoryStmt=$pdo->prepare('SELECT COALESCE(SUM(stock_value),0),COALESCE(SUM(quantity),0) FROM '.table('warehouse_stock').' WHERE branch_id=?');
$transitOutStmt=$pdo->prepare('SELECT COALESCE(SUM(total_value),0) FROM '.table('in_transit_stock').' WHERE from_branch_id=? AND status="in_transit"');
$transitInStmt=$pdo->prepare('SELECT COALESCE(SUM(total_value),0) FROM '.table('in_transit_stock').' WHERE to_branch_id=? AND status="in_transit"');
$dueFromStmt=$pdo->prepare('SELECT COALESCE(SUM(total_value),0) FROM '.table('intercompany_transactions').' WHERE from_branch_id=? AND status="recognized"');
$dueToStmt=$pdo->prepare('SELECT COALESCE(SUM(total_value),0) FROM '.table('intercompany_transactions').' WHERE to_branch_id=? AND status="recognized"');

$rows=[];$totals=['revenue'=>0,'expenses'=>0,'net'=>0,'stock_value'=>0,'stock_units'=>0,'transit_out'=>0,'transit_in'=>0,'due_from'=>0,'due_to'=>0];
foreach($branchRows as $branch){
  $branchId=(int)$branch['id'];
  $ledgerStmt->execute([$branchId,$from,$to]);$ledger=$ledgerStmt->fetch()?:['revenue'=>0,'expenses'=>0];
  $inventoryStmt->execute([$branchId]);[$stockValue,$stockUnits]=$inventoryStmt->fetch(PDO::FETCH_NUM);
  $transitOutStmt->execute([$branchId]);$transitOut=(float)$transitOutStmt->fetchColumn();
  $transitInStmt->execute([$branchId]);$transitIn=(float)$transitInStmt->fetchColumn();
  $dueFromStmt->execute([$branchId]);$dueFrom=(float)$dueFromStmt->fetchColumn();
  $dueToStmt->execute([$branchId]);$dueTo=(float)$dueToStmt->fetchColumn();
  $revenue=(float)($ledger['revenue']??0);$expenses=(float)($ledger['expenses']??0);$net=$revenue-$expenses;
  $row=['branch'=>$branch,'revenue'=>$revenue,'expenses'=>$expenses,'net'=>$net,'stock_value'=>(float)$stockValue,'stock_units'=>(float)$stockUnits,'transit_out'=>$transitOut,'transit_in'=>$transitIn,'due_from'=>$dueFrom,'due_to'=>$dueTo];
  $rows[]=$row;
  foreach(['revenue','expenses','net','stock_value','stock_units','transit_out','transit_in','due_from','due_to'] as $key){$totals[$key]+=(float)$row[$key];}
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Consolidation Layer</div><h2 class="h4 mb-1">Branch Financial Consolidation</h2><p class="text-secondary mb-0">Branch-level revenue, expense contribution, stock valuation, in-transit exposure, and recognized intercompany balances.</p></div><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===$companyId?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">From</label><input class="form-control" type="date" name="from" value="<?php echo esc($from); ?>"></div><div><label class="form-label">To</label><input class="form-control" type="date" name="to" value="<?php echo esc($to); ?>"></div><button class="btn btn-brand">Apply</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Consolidated Revenue</div><div class="metric-sm"><?php echo money($totals['revenue']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Consolidated Expenses</div><div class="metric-sm"><?php echo money($totals['expenses']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Net Contribution</div><div class="metric-sm <?php echo $totals['net']>=0?'money-positive':'money-negative'; ?>"><?php echo money($totals['net']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Inventory Value</div><div class="metric-sm"><?php echo money($totals['stock_value']); ?></div></div></div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Branch Comparison</div><h2 class="h5 mb-0">Financial + Inventory Consolidation</h2></div><div class="d-flex gap-2"><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/company-dashboard.php?company_id=<?php echo (int)$companyId; ?>"><?php echo t('Company Dashboard', 'لوحة الشركة'); ?></a><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/intercompany-transactions.php"><?php echo t('Intercompany', 'بين الشركات'); ?></a></div></div><table class="table align-middle"><thead><tr><th>Branch</th><th>Revenue</th><th>Expenses</th><th>Net</th><th>Stock Value</th><th>In-Transit Out / In</th><th>Due From / Due To</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['branch']['branch_code'].' · '.$row['branch']['branch_name']); ?></strong><div class="small text-secondary"><?php echo number_format((float)$row['stock_units'],2); ?> stock units</div></td><td><?php echo money($row['revenue']); ?></td><td><?php echo money($row['expenses']); ?></td><td class="<?php echo $row['net']>=0?'money-positive':'money-negative'; ?>"><?php echo money($row['net']); ?></td><td><?php echo money($row['stock_value']); ?></td><td><?php echo money($row['transit_out']); ?><div class="small text-secondary">Incoming: <?php echo money($row['transit_in']); ?></div></td><td><?php echo money($row['due_from']); ?><div class="small text-secondary">Due To: <?php echo money($row['due_to']); ?></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No branch consolidation rows are available for this scope.</td></tr><?php endif; ?></tbody><tfoot><tr><th>Consolidated Total</th><th><?php echo money($totals['revenue']); ?></th><th><?php echo money($totals['expenses']); ?></th><th><?php echo money($totals['net']); ?></th><th><?php echo money($totals['stock_value']); ?></th><th><?php echo money($totals['transit_out']); ?><div class="small text-secondary">Incoming: <?php echo money($totals['transit_in']); ?></div></th><th><?php echo money($totals['due_from']); ?><div class="small text-secondary">Due To: <?php echo money($totals['due_to']); ?></div></th></tr></tfoot></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>