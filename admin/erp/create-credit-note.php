<?php
$pageTitle='Create Credit Note';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$customers=$pdo->query('SELECT id,customer_code,company_name,contact_name,email FROM ' . table('customers') . ' ORDER BY company_name,contact_name')->fetchAll();
$invoices=$pdo->query('SELECT id,invoice_number,customer_id,customer_name,balance_due FROM ' . table('invoices') . ' WHERE balance_due>0 AND status IN ("approved","sent","partial") ORDER BY created_at DESC')->fetchAll();
$selectedCustomerId=(int)($_GET['customer_id']??0);
$selectedInvoiceId=(int)($_GET['invoice_id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $customerId=(int)($_POST['customer_id']??0) ?: null;
  $invoiceId=(int)($_POST['invoice_id']??0) ?: null;
  $customerName='';$customerEmail='';
  if($customerId){$stmt=$pdo->prepare('SELECT company_name,contact_name,email FROM ' . table('customers') . ' WHERE id=? LIMIT 1');$stmt->execute([$customerId]);$customer=$stmt->fetch();if($customer){$customerName=trim((string)($customer['company_name'] ?: $customer['contact_name']));$customerEmail=(string)$customer['email'];}}
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
  if(!$items || $total<=0){flash('error','Add at least one valid credit note item.');redirect(ADMIN_URL.'/erp/create-credit-note.php');}
  try{
    $pdo->beginTransaction();
    $number=nextDocumentNumber($pdo,'credit_notes',(string)setting('credit_note_prefix','CNO'));
    $stmt=$pdo->prepare('INSERT INTO ' . table('credit_notes') . ' (credit_note_number,customer_id,invoice_id,customer_name,customer_email,subtotal,tax,total,issue_date,reason,status) VALUES (?,?,?,?,?,?,?,?,?,?,"draft")');
    $stmt->execute([$number,$customerId,$invoiceId,$customerName,$customerEmail,$subtotal,$tax,$total,trim((string)($_POST['issue_date']??date('Y-m-d'))) ?: date('Y-m-d'),trim((string)($_POST['reason']??''))]);
    $id=(int)$pdo->lastInsertId();
    $itemStmt=$pdo->prepare('INSERT INTO ' . table('credit_note_items') . ' (credit_note_id,description,amount,tax_rate,line_total) VALUES (?,?,?,?,?)');
    foreach($items as $item){$itemStmt->execute([$id,$item['description'],$item['amount'],$item['tax_rate'],$item['line_total']]);}
    logActivity($pdo,'Accounting','credit_note_created','Credit note '.$number.' created.','credit_note',$id);
    $pdo->commit();
    flash('success','Credit note created as draft.');
    redirect(ADMIN_URL.'/erp/view-credit-note.php?id='.$id);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-credit-note.php');}
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="card-admin p-4">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Customer Credit</div><h2 class="h4 mb-1">Create Credit Note</h2><p class="text-secondary mb-0">Draft first, then approve from the document view to post AR, revenue, and VAT impact.</p></div><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/credit-notes.php">Back</a></div>
  <div class="row g-3 mb-4">
    <div class="col-lg-4"><label class="form-label">Customer</label><select class="form-select" name="customer_id"><option value="0">Select customer</option><?php foreach($customers as $customer): ?><option value="<?php echo (int)$customer['id']; ?>" <?php echo (int)$customer['id']===$selectedCustomerId?'selected':''; ?>><?php echo esc($customer['customer_code'].' · '.($customer['company_name'] ?: $customer['contact_name'])); ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-4"><label class="form-label">Linked invoice</label><select class="form-select" name="invoice_id"><option value="0">Unapplied credit</option><?php foreach($invoices as $invoice): ?><option value="<?php echo (int)$invoice['id']; ?>" <?php echo (int)$invoice['id']===$selectedInvoiceId?'selected':''; ?>><?php echo esc($invoice['invoice_number'].' · '.$invoice['customer_name'].' · Balance '.money($invoice['balance_due'])); ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-4"><label class="form-label">Issue date</label><input class="form-control" type="date" name="issue_date" value="<?php echo date('Y-m-d'); ?>"></div>
    <div class="col-12"><label class="form-label">Reason</label><input class="form-control" name="reason" placeholder="Return, commercial adjustment, goodwill credit..."></div>
  </div>
  <div class="table-responsive"><table class="table align-middle" id="creditLines"><thead><tr><th>Description</th><th style="width:170px">Net Amount</th><th style="width:150px">VAT %</th><th style="width:80px"></th></tr></thead><tbody><?php for($i=0;$i<3;$i++): ?><tr class="credit-line"><td><input class="form-control" name="description[]" placeholder="Credit item description"></td><td><input class="form-control" type="number" step="0.01" min="0" name="amount[]" value="0"></td><td><input class="form-control" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><button class="btn btn-sm btn-outline-danger remove-credit-line" type="button">×</button></td></tr><?php endfor; ?></tbody></table></div>
  <div class="d-flex justify-content-between gap-3 flex-wrap mt-3"><button class="btn btn-outline-primary" type="button" id="addCreditLine">Add Item</button><button class="btn btn-brand btn-lg">Create Credit Note</button></div>
</form>
<template id="creditLineTemplate"><tr class="credit-line"><td><input class="form-control" name="description[]" placeholder="Credit item description"></td><td><input class="form-control" type="number" step="0.01" min="0" name="amount[]" value="0"></td><td><input class="form-control" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><button class="btn btn-sm btn-outline-danger remove-credit-line" type="button">×</button></td></tr></template>
<script>const creditBody=document.querySelector('#creditLines tbody');const creditTpl=document.getElementById('creditLineTemplate');document.getElementById('addCreditLine').addEventListener('click',()=>creditBody.appendChild(creditTpl.content.cloneNode(true)));document.addEventListener('click',e=>{if(e.target.matches('.remove-credit-line')){const rows=document.querySelectorAll('.credit-line');if(rows.length>1)e.target.closest('tr').remove();}});</script>
<?php include dirname(__DIR__).'/footer.php'; ?>