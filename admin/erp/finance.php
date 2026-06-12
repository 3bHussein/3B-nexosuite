<?php
$pageTitle='Finance & Cash Control';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('finance');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$defaultScope=operationalScope($pdo);
$supplierParams=[];$supplierCondition=selectedScopeCondition('s',$supplierParams,$filters,['company_id','branch_id']);$supplierCondition.=scopeQueryCondition($pdo,'s',$supplierParams,false);$supplierWhere=' WHERE s.status="active"'.$supplierCondition;
$supplierStmt=$pdo->prepare('SELECT s.id,s.supplier_code,s.company_name,s.payment_terms_days FROM ' . table('suppliers') . ' s'.$supplierWhere.' ORDER BY s.company_name ASC');$supplierStmt->execute($supplierParams);$suppliers=$supplierStmt->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='expense'){
  $scope=[
    'company_id'=>(int)($_POST['company_id']??$defaultScope['company_id']),
    'branch_id'=>(int)($_POST['branch_id']??$defaultScope['branch_id']),
    'warehouse_id'=>(int)($_POST['warehouse_id']??$defaultScope['warehouse_id']),
    'location_id'=>(int)setting('default_location_id','0'),
  ];
  enforceScopeAllowed($pdo,(int)$scope['company_id'],(int)$scope['branch_id'],(int)$scope['warehouse_id'],true);
  $amount=max(0,(float)($_POST['amount']??0));
  $tax=max(0,(float)($_POST['tax']??0));
  $total=round($amount+$tax,2);
  $supplierId=(int)($_POST['supplier_id']??0) ?: null;
  $vendorName=trim((string)($_POST['vendor_name']??''));
  $terms=0;
  if($supplierId){
    $stmt=$pdo->prepare('SELECT company_name,payment_terms_days FROM ' . table('suppliers') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$supplierId]);
    $supplier=$stmt->fetch();
    if($supplier){$vendorName=trim((string)$supplier['company_name']);$terms=(int)$supplier['payment_terms_days'];}
  }
  $expenseDate=trim((string)($_POST['expense_date']??date('Y-m-d'))) ?: date('Y-m-d');
  $dueDate=trim((string)($_POST['due_date']??''));
  if($dueDate==='' && $terms>0){$dueDate=date('Y-m-d',strtotime($expenseDate.' +'.$terms.' days'));}
  $requestedStatus=strtolower(trim((string)($_POST['payment_status']??'pending')));
  $amountPaid=$requestedStatus==='paid' ? $total : max(0,min($total,(float)($_POST['amount_paid']??0)));
  $balanceDue=round(max(0,$total-$amountPaid),2);
  $status=$balanceDue<=0?'paid':($amountPaid>0?'partial':'pending');
  $stmt=$pdo->prepare('INSERT INTO ' . table('expenses') . ' (company_id,branch_id,warehouse_id,expense_number,supplier_id,category,vendor_name,amount,tax,total,amount_paid,balance_due,payment_status,approval_status,expense_date,due_date,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,"not_required",?,?,?)');
  $expenseNumber='EXP-'.date('YmdHis').'-'.random_int(100,999);
  $stmt->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,(int)($scope['warehouse_id']??0)?:null,$expenseNumber,$supplierId,trim((string)($_POST['category']??'')),$vendorName,$amount,$tax,$total,$amountPaid,$balanceDue,$status,$expenseDate,$dueDate?:null,trim((string)($_POST['notes']??''))]);
  $expenseId=(int)$pdo->lastInsertId();
  $request=createApprovalRequestForDocument($pdo,'expense',$expenseId,'approve','Expense approval request for '.$expenseNumber.'.');
  if($request){
      $pdo->prepare('UPDATE '.table('expenses').' SET approval_status="pending_approval" WHERE id=?')->execute([$expenseId]);
      logActivity($pdo,'Finance','expense_submitted_for_approval','Expense '.$expenseNumber.' submitted to approval workflow.','expense',$expenseId);
      flash('success','Expense created and submitted to approval center: '.$request['request_number'].'.');
  }else{
      postExpenseAccounting($pdo,$expenseId);
      logActivity($pdo,'Finance','expense_create','Expense '.$expenseNumber.' recorded and posted to accounting for '.($vendorName?:'vendor').'.','expense',$expenseId);
      flash('success','Expense recorded and posted to accounting.');
  }
  redirect(ADMIN_URL.'/erp/finance.php');
}
$paymentParams=[];$paymentCondition=selectedScopeCondition('p',$paymentParams,$filters,['company_id','branch_id']);$paymentCondition.=scopeQueryCondition($pdo,'p',$paymentParams,false);$paidStmt=$pdo->prepare('SELECT COALESCE(SUM(p.amount),0) FROM '.table('payments').' p WHERE p.status="received"'.$paymentCondition);$paidStmt->execute($paymentParams);$paid=(float)$paidStmt->fetchColumn();
$invoiceParams=[];$invoiceCondition=selectedScopeCondition('i',$invoiceParams,$filters,['company_id','branch_id']);$invoiceCondition.=scopeQueryCondition($pdo,'i',$invoiceParams,false);$receivableStmt=$pdo->prepare('SELECT COALESCE(SUM(i.balance_due),0) FROM '.table('invoices').' i WHERE i.status IN ("approved","sent","partial")'.$invoiceCondition);$receivableStmt->execute($invoiceParams);$receivable=(float)$receivableStmt->fetchColumn();
$issuedStmt=$pdo->prepare('SELECT COALESCE(SUM(i.total),0) FROM '.table('invoices').' i WHERE i.status<>"cancelled"'.$invoiceCondition);$issuedStmt->execute($invoiceParams);$issued=(float)$issuedStmt->fetchColumn();
$expenseParams=[];$expenseCondition=selectedScopeCondition('e',$expenseParams,$filters,['company_id','branch_id']);$expenseCondition.=scopeQueryCondition($pdo,'e',$expenseParams,false);$cashBase=$expenseCondition!==''?' WHERE '.substr($expenseCondition,5).' AND e.approval_status IN ("approved","not_required")':' WHERE e.approval_status IN ("approved","not_required")';$cashStmt=$pdo->prepare('SELECT COALESCE(SUM(e.amount_paid),0) FROM '.table('expenses').' e'.$cashBase);$cashStmt->execute($expenseParams);$cashExpenses=(float)$cashStmt->fetchColumn();
$payableStmt=$pdo->prepare('SELECT COALESCE(SUM(e.balance_due),0) FROM '.table('expenses').' e WHERE e.balance_due>0 AND e.approval_status IN ("approved","not_required")'.$expenseCondition);$payableStmt->execute($expenseParams);$payables=(float)$payableStmt->fetchColumn();
$net=$paid-$cashExpenses;
$listPaymentParams=[];$listPaymentCondition=selectedScopeCondition('p',$listPaymentParams,$filters,['company_id','branch_id']);$listPaymentCondition.=scopeQueryCondition($pdo,'p',$listPaymentParams,false);$listPaymentWhere=$listPaymentCondition!==''?' WHERE '.substr($listPaymentCondition,5):'';$paymentListStmt=$pdo->prepare('SELECT p.*,i.invoice_number,c.customer_code FROM '.table('payments').' p LEFT JOIN '.table('invoices').' i ON i.id=p.invoice_id LEFT JOIN '.table('customers').' c ON c.id=p.customer_id'.$listPaymentWhere.' ORDER BY p.paid_at DESC,p.created_at DESC LIMIT 12');$paymentListStmt->execute($listPaymentParams);$payments=$paymentListStmt->fetchAll();
$outstandingParams=[];$outstandingCondition=selectedScopeCondition('i',$outstandingParams,$filters,['company_id','branch_id']);$outstandingCondition.=scopeQueryCondition($pdo,'i',$outstandingParams,false);$outstandingStmt=$pdo->prepare('SELECT i.invoice_number,i.customer_name,i.balance_due,i.due_date,i.status FROM '.table('invoices').' i WHERE i.balance_due>0 AND i.status<>"cancelled"'.$outstandingCondition.' ORDER BY i.due_date ASC LIMIT 12');$outstandingStmt->execute($outstandingParams);$outstanding=$outstandingStmt->fetchAll();
$listExpenseParams=[];$listExpenseCondition=selectedScopeCondition('e',$listExpenseParams,$filters,['company_id','branch_id']);$listExpenseCondition.=scopeQueryCondition($pdo,'e',$listExpenseParams,false);$listExpenseWhere=$listExpenseCondition!==''?' WHERE '.substr($listExpenseCondition,5):'';$expenseListStmt=$pdo->prepare('SELECT e.*,s.supplier_code FROM '.table('expenses').' e LEFT JOIN '.table('suppliers').' s ON s.id=e.supplier_id'.$listExpenseWhere.' ORDER BY e.expense_date DESC,e.created_at DESC LIMIT 12');$expenseListStmt->execute($listExpenseParams);$expenses=$expenseListStmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="card-admin p-3 mb-4"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Apply Scope</button></form></div>
<div class="card-admin p-4 mb-4"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><div><div class="erp-kicker"><?php echo t('Accounting Core', 'المحاسبة الأساسية'); ?></div><h2 class="h5 mb-1">Finance now supports supplier-linked AP expenses.</h2><p class="text-secondary mb-0">Paid, pending, and partial expenses post automatically into Cash & Bank and Accounts Payable.</p></div><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/ap-supplier-ledger.php">Supplier AP Ledger</a><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/ap-aging.php"><?php echo t('AP Aging', 'أعمار الموردين'); ?></a><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-debit-note.php">Supplier Debit Note</a></div></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Issued Invoices</div><div class="metric-sm"><?php echo money($issued); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Cash Collected</div><div class="metric-sm money-positive"><?php echo money($paid); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Receivables</div><div class="metric-sm money-negative"><?php echo money($receivable); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Payables</div><div class="metric-sm money-negative"><?php echo money($payables); ?></div></div></div></div>
<div class="row g-4 mb-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <input type="hidden" name="form_type" value="expense">
      <h2 class="h5">Record Expense / Supplier Payable</h2>
      <div class="row g-2">
        <div class="col-12 mb-2"><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-12 mb-2"><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-12 mb-2"><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$defaultScope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-12 mb-2"><select class="form-select" name="supplier_id"><option value="0">Optional linked supplier</option><?php foreach($suppliers as $supplier): ?><option value="<?php echo (int)$supplier['id']; ?>"><?php echo esc($supplier['supplier_code'].' · '.$supplier['company_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6 mb-2"><input class="form-control" name="category" placeholder="Category" required></div>
        <div class="col-md-6 mb-2"><input class="form-control" name="vendor_name" placeholder="Vendor / supplier name"></div>
      </div>
      <div class="row g-2"><div class="col-md-6 mb-2"><input class="form-control" type="number" step="0.01" min="0" name="amount" placeholder="Net amount" required></div><div class="col-md-6 mb-2"><input class="form-control" type="number" step="0.01" min="0" name="tax" placeholder="VAT / tax" value="0"></div></div>
      <div class="row g-2"><div class="col-md-6 mb-2"><input class="form-control" type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>"></div><div class="col-md-6 mb-2"><input class="form-control" type="date" name="due_date" placeholder="Due date"></div></div>
      <div class="row g-2"><div class="col-md-6 mb-2"><select class="form-select" name="payment_status"><option value="paid">paid</option><option value="pending">pending</option><option value="partial">partial</option></select></div><div class="col-md-6 mb-2"><input class="form-control" type="number" step="0.01" min="0" name="amount_paid" placeholder="Amount already paid"></div></div>
      <div class="mb-3"><textarea class="form-control" name="notes" rows="3" placeholder="Notes"></textarea></div>
      <button class="btn btn-brand">Save Expense</button>
    </form>
  </div>
  <div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Outstanding Receivables</h2><table class="table"><thead><tr><th>Invoice</th><th>Customer</th><th>Balance</th><th>Due</th><th>Status</th></tr></thead><tbody><?php foreach($outstanding as $row): ?><tr><td><?php echo esc($row['invoice_number']); ?></td><td><?php echo esc($row['customer_name']); ?></td><td><?php echo money($row['balance_due']); ?></td><td><?php echo esc($row['due_date']); ?></td><td><?php echo esc($row['status']); ?></td></tr><?php endforeach; ?><?php if(!$outstanding): ?><tr><td colspan="5" class="text-secondary">No outstanding receivables.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-6"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Payments</h2><table class="table"><thead><tr><th>Payment</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody><?php foreach($payments as $row): ?><tr><td><?php echo esc($row['payment_number']); ?></td><td><?php echo esc($row['invoice_number']); ?></td><td><?php echo money($row['amount']); ?></td><td><?php echo esc($row['method']); ?></td><td><?php echo esc(substr((string)$row['paid_at'],0,10)); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
  <div class="col-xl-6"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Expenses / Payables</h2><table class="table"><thead><tr><th>Expense</th><th>Supplier</th><th>Total</th><th>Due</th><th>Payment</th><th>Approval</th></tr></thead><tbody><?php foreach($expenses as $row): ?><tr><td><?php echo esc($row['expense_number']); ?></td><td><?php echo esc(($row['supplier_code']? $row['supplier_code'].' · ' : '') . $row['vendor_name']); ?></td><td><?php echo money($row['total']); ?><div class="small text-secondary">Balance <?php echo money($row['balance_due']); ?></div></td><td><?php echo esc($row['due_date'] ?: '—'); ?></td><td><?php echo esc($row['payment_status']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['approval_status']??'not_required')); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['approval_status']??'not_required','_'))); ?></span><?php if(($row['approval_status']??'')==='pending_approval'): ?><?php $expenseApproval=activeApprovalRequest($pdo,'expense',(int)$row['id'],'approve'); ?><?php if($expenseApproval): ?><div><a class="small" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$expenseApproval['id']; ?>">Open request</a></div><?php endif; ?><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>