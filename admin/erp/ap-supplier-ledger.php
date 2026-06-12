<?php
$pageTitle='AP Supplier Ledger';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$q=trim((string)($_GET['q']??''));
$params=[];$where='';
if($q!==''){$where=' WHERE (s.supplier_code LIKE ? OR s.company_name LIKE ? OR s.contact_name LIKE ? OR s.email LIKE ?) ';$like='%'.$q.'%';$params=[$like,$like,$like,$like];}
$condition=selectedScopeCondition('s',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'s',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$sql='SELECT s.*,
  COALESCE((SELECT SUM(e.total) FROM ' . table('expenses') . ' e WHERE e.supplier_id=s.id),0) expense_total,
  COALESCE((SELECT SUM(e.amount_paid) FROM ' . table('expenses') . ' e WHERE e.supplier_id=s.id),0) paid_total,
  COALESCE((SELECT SUM(d.total) FROM ' . table('debit_notes') . ' d WHERE d.supplier_id=s.id AND d.status="approved"),0) debit_note_total
  FROM ' . table('suppliers') . ' s '.$where.' ORDER BY s.company_name ASC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$grandBalance=0;foreach($rows as &$row){$row['balance']=(float)$row['expense_total']-(float)$row['paid_total']-(float)$row['debit_note_total'];$grandBalance+=$row['balance'];}unset($row);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Accounts Payable</div><h2 class="h4 mb-1">Supplier AP Ledger</h2><p class="text-secondary mb-0">Supplier expense obligations, cash paid, and approved supplier debit notes.</p></div><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Search</label><input class="form-control" name="q" value="<?php echo esc($q); ?>" placeholder="Search suppliers"></div><button class="btn btn-brand">Search</button></form></div>
<div class="card-admin p-4 mb-4"><div class="erp-kicker">Total AP Balance</div><div class="metric"><?php echo money($grandBalance); ?></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Supplier</th><th>Expenses</th><th>Paid</th><th>Debit Notes</th><th>Balance</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['supplier_code'].' · '.$row['company_name']); ?></strong><div class="small text-secondary"><?php echo esc($row['email']); ?></div></td><td><?php echo money($row['expense_total']); ?></td><td><?php echo money($row['paid_total']); ?></td><td><?php echo money($row['debit_note_total']); ?></td><td class="<?php echo $row['balance']>0?'money-negative':'money-positive'; ?>"><?php echo money($row['balance']); ?></td><td class="text-end"><div class="d-flex gap-1 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-statement.php?id=<?php echo (int)$row['id']; ?>">Statement</a><a class="btn btn-sm btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/create-debit-note.php?supplier_id=<?php echo (int)$row['id']; ?>">Debit Note</a></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No suppliers found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>