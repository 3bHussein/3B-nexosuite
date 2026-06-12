<?php
$pageTitle='AR Customer Ledger';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$q=trim((string)($_GET['q']??''));
$params=[];$where='';
if($q!==''){$where=' WHERE (c.customer_code LIKE ? OR c.company_name LIKE ? OR c.contact_name LIKE ? OR c.email LIKE ?) ';$like='%'.$q.'%';$params=[$like,$like,$like,$like];}
$condition=selectedScopeCondition('c',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'c',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$sql='SELECT c.*,
  COALESCE((SELECT SUM(i.total) FROM ' . table('invoices') . ' i WHERE i.customer_id=c.id AND i.status<>"cancelled"),0) invoice_total,
  COALESCE((SELECT SUM(p.amount) FROM ' . table('payments') . ' p WHERE p.customer_id=c.id AND p.status="received"),0) payments_total,
  COALESCE((SELECT SUM(n.total) FROM ' . table('credit_notes') . ' n WHERE n.customer_id=c.id AND n.status="approved"),0) credit_total
  FROM ' . table('customers') . ' c '.$where.' ORDER BY c.company_name ASC,c.contact_name ASC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$grandBalance=0;
foreach($rows as &$row){$row['balance']=(float)$row['invoice_total']-(float)$row['payments_total']-(float)$row['credit_total'];$grandBalance+=$row['balance'];}unset($row);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Accounts Receivable</div><h2 class="h4 mb-1">Customer AR Ledger</h2><p class="text-secondary mb-0">Invoice, payment, and credit-note balances by customer.</p></div><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Search</label><input class="form-control" name="q" value="<?php echo esc($q); ?>" placeholder="Search customers"></div><button class="btn btn-brand">Search</button></form></div>
<div class="card-admin p-4 mb-4"><div class="erp-kicker">Total AR Balance</div><div class="metric"><?php echo money($grandBalance); ?></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Customer</th><th>Invoices</th><th>Payments</th><th>Credit Notes</th><th>Balance</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['customer_code'].' · '.($row['company_name'] ?: $row['contact_name'])); ?></strong><div class="small text-secondary"><?php echo esc($row['email']); ?></div></td><td><?php echo money($row['invoice_total']); ?></td><td><?php echo money($row['payments_total']); ?></td><td><?php echo money($row['credit_total']); ?></td><td class="<?php echo $row['balance']>0?'money-negative':'money-positive'; ?>"><?php echo money($row['balance']); ?></td><td class="text-end"><div class="d-flex gap-1 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-statement.php?id=<?php echo (int)$row['id']; ?>">Statement</a><a class="btn btn-sm btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/create-credit-note.php?customer_id=<?php echo (int)$row['id']; ?>">Credit Note</a></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No customers found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>