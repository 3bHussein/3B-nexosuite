<?php
$pageTitle='Create Supplier Debit Note';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$suppliers=$pdo->query('SELECT id,supplier_code,company_name,email FROM ' . table('suppliers') . ' ORDER BY company_name ASC')->fetchAll();
$expenses=$pdo->query('SELECT id,expense_number,supplier_id,vendor_name,balance_due FROM ' . table('expenses') . ' WHERE balance_due>0 ORDER BY created_at DESC')->fetchAll();
$selectedSupplierId=(int)($_GET['supplier_id']??0);
$selectedExpenseId=(int)($_GET['expense_id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $supplierId=(int)($_POST['supplier_id']??0) ?: null;
  $expenseId=(int)($_POST['expense_id']??0) ?: null;
  $supplierName='';
  if($supplierId){$stmt=$pdo->prepare('SELECT company_name FROM ' . table('suppliers') . ' WHERE id=? LIMIT 1');$stmt->execute([$supplierId]);$supplierName=(string)$stmt->fetchColumn();}
  $subtotal=0;$tax=0;$items=[];
  foreach(($_POST['description']??[]) as $i=>$description){
    $amount=max(0,(float)(($_POST['amount'][$i]??0)));
    $rate=max(0,(float)(($_POST['tax_rate'][$i]??0)));
    if(trim((string)$description)==='' || $amount<=0){continue;}
    $lineTax=round($amount*$rate/100,2);$lineTotal=round($amount+$lineTax,2);
    $subtotal+=$amount;$tax+=$lineTax;
    $items[]=['description'=>trim((string)$description),'amount'=>$amount,'tax_rate'=>$rate,'line_total'=>$lineTotal];
  }
  $subtotal=round($subtotal,2);$tax=round($tax,2);$total=round($subtotal+$tax,2);
  if(!$items || $total<=0){flash('error','Add at least one valid debit note item.');redirect(ADMIN_URL.'/erp/create-debit-note.php');}
  try{
    $pdo->beginTransaction();
    $number=nextDocumentNumber($pdo,'debit_notes',(string)setting('debit_note_prefix','DNO'));
    $stmt=$pdo->prepare('INSERT INTO ' . table('debit_notes') . ' (debit_note_number,supplier_id,expense_id,supplier_name,subtotal,tax,total,issue_date,reason,status) VALUES (?,?,?,?,?,?,?,?,?,"draft")');
    $stmt->execute([$number,$supplierId,$expenseId,$supplierName,$subtotal,$tax,$total,trim((string)($_POST['issue_date']??date('Y-m-d'))) ?: date('Y-m-d'),trim((string)($_POST['reason']??''))]);
    $id=(int)$pdo->lastInsertId();
    $itemStmt=$pdo->prepare('INSERT INTO ' . table('debit_note_items') . ' (debit_note_id,description,amount,tax_rate,line_total) VALUES (?,?,?,?,?)');
    foreach($items as $item){$itemStmt->execute([$id,$item['description'],$item['amount'],$item['tax_rate'],$item['line_total']]);}
    logActivity($pdo,'Accounting','debit_note_created','Debit note '.$number.' created.','debit_note',$id);
    $pdo->commit();
    flash('success','Debit note created as draft.');
    redirect(ADMIN_URL.'/erp/view-debit-note.php?id='.$id);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-debit-note.php');}
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="card-admin p-4">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Supplier Adjustment</div><h2 class="h4 mb-1">Create Supplier Debit Note</h2><p class="text-secondary mb-0">Draft first, then approve from the document view to reduce AP, expenses, and input VAT.</p></div><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/debit-notes.php">Back</a></div>
  <div class="row g-3 mb-4">
    <div class="col-lg-4"><label class="form-label">Supplier</label><select class="form-select" name="supplier_id"><option value="0">Select supplier</option><?php foreach($suppliers as $supplier): ?><option value="<?php echo (int)$supplier['id']; ?>" <?php echo (int)$supplier['id']===$selectedSupplierId?'selected':''; ?>><?php echo esc($supplier['supplier_code'].' · '.$supplier['company_name']); ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-4"><label class="form-label">Linked expense</label><select class="form-select" name="expense_id"><option value="0">Unapplied debit note</option><?php foreach($expenses as $expense): ?><option value="<?php echo (int)$expense['id']; ?>" <?php echo (int)$expense['id']===$selectedExpenseId?'selected':''; ?>><?php echo esc($expense['expense_number'].' · '.$expense['vendor_name'].' · Balance '.money($expense['balance_due'])); ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-4"><label class="form-label">Issue date</label><input class="form-control" type="date" name="issue_date" value="<?php echo date('Y-m-d'); ?>"></div>
    <div class="col-12"><label class="form-label">Reason</label><input class="form-control" name="reason" placeholder="Supplier return, pricing correction, rejected quantity..."></div>
  </div>
  <div class="table-responsive"><table class="table align-middle" id="debitLines"><thead><tr><th>Description</th><th style="width:170px">Net Amount</th><th style="width:150px">VAT %</th><th style="width:80px"></th></tr></thead><tbody><?php for($i=0;$i<3;$i++): ?><tr class="debit-line"><td><input class="form-control" name="description[]" placeholder="Debit note item description"></td><td><input class="form-control" type="number" step="0.01" min="0" name="amount[]" value="0"></td><td><input class="form-control" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><button class="btn btn-sm btn-outline-danger remove-debit-line" type="button">×</button></td></tr><?php endfor; ?></tbody></table></div>
  <div class="d-flex justify-content-between gap-3 flex-wrap mt-3"><button class="btn btn-outline-primary" type="button" id="addDebitLine">Add Item</button><button class="btn btn-brand btn-lg">Create Debit Note</button></div>
</form>
<template id="debitLineTemplate"><tr class="debit-line"><td><input class="form-control" name="description[]" placeholder="Debit note item description"></td><td><input class="form-control" type="number" step="0.01" min="0" name="amount[]" value="0"></td><td><input class="form-control" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><button class="btn btn-sm btn-outline-danger remove-debit-line" type="button">×</button></td></tr></template>
<script>const debitBody=document.querySelector('#debitLines tbody');const debitTpl=document.getElementById('debitLineTemplate');document.getElementById('addDebitLine').addEventListener('click',()=>debitBody.appendChild(debitTpl.content.cloneNode(true)));document.addEventListener('click',e=>{if(e.target.matches('.remove-debit-line')){const rows=document.querySelectorAll('.debit-line');if(rows.length>1)e.target.closest('tr').remove();}});</script>
<?php include dirname(__DIR__).'/footer.php'; ?>