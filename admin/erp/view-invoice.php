<?php
$pageTitle='View Invoice';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('invoices');
$pdo=getDB();
$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='payment'){
  $pdo->beginTransaction();
  try{
    $stmt=$pdo->prepare('SELECT * FROM ' . table('invoices') . ' WHERE id=? LIMIT 1 FOR UPDATE');$stmt->execute([$id]);$invoice=$stmt->fetch();
    if(!$invoice){throw new RuntimeException('Invoice not found.');}
    enforceScopeAllowed($pdo,(int)($invoice['company_id']??0),(int)($invoice['branch_id']??0),(int)($invoice['warehouse_id']??0),true);
    if(!in_array($invoice['status'],['approved','sent','partial'],true)){throw new RuntimeException('Payments can be recorded only for approved, sent, or partially paid invoices.');}
    $amount=max(0,(float)($_POST['amount']??0));
    if($amount<=0){throw new RuntimeException('Payment amount must be greater than zero.');}
    if($amount>(float)$invoice['balance_due']+0.001){throw new RuntimeException('Payment exceeds the invoice balance.');}
    $paymentScope=['company_id'=>(int)($invoice['company_id']??0),'branch_id'=>(int)($invoice['branch_id']??0),'warehouse_id'=>(int)($invoice['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $paymentNumber=nextScopedDocumentNumber($pdo,'payment',(string)setting('payment_prefix','PAY'),$paymentScope);
    $stmt=$pdo->prepare('INSERT INTO ' . table('payments') . ' (company_id,branch_id,payment_number,invoice_id,customer_id,amount,method,reference,status,paid_at,notes) VALUES (?,?,?,?,?,?,?,?,"received",NOW(),?)');
    $stmt->execute([
      (int)($invoice['company_id']??0)?:null,
      (int)($invoice['branch_id']??0)?:null,
      $paymentNumber,
      $id,
      $invoice['customer_id'],
      $amount,
      $_POST['method']??'Cash',
      $_POST['reference']??'',
      $_POST['notes']??''
    ]);
    $paymentId=(int)$pdo->lastInsertId();
    postPaymentAccounting($pdo,$paymentId);
    $paid=(float)$invoice['amount_paid']+$amount;$balance=max(0,(float)$invoice['total']-$paid);$status=$balance<=0.001?'paid':'partial';
    $stmt=$pdo->prepare('UPDATE ' . table('invoices') . ' SET amount_paid=?,balance_due=?,status=?,paid_at=CASE WHEN ?="paid" THEN NOW() ELSE paid_at END WHERE id=?');$stmt->execute([$paid,$balance,$status,$status,$id]);
    logActivity($pdo,'Finance','payment_record','Payment '.$paymentNumber.' recorded against invoice '.$invoice['invoice_number'].' and posted to accounting.','payment',$paymentId);
    $pdo->commit();flash('success','Payment recorded.');redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$id);
  }catch(Throwable $e){$pdo->rollBack();recordSystemError($pdo,$e,['page'=>'view-invoice','invoice_id'=>$id,'action'=>'payment']);flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$id);}
}
$stmt=$pdo->prepare('SELECT i.*,c.customer_code,c.phone,c.tax_number FROM ' . table('invoices') . ' i LEFT JOIN ' . table('customers') . ' c ON c.id=i.customer_id WHERE i.id=? LIMIT 1');$stmt->execute([$id]);$invoice=$stmt->fetch();if(!$invoice){flash('error','Invoice not found.');redirect(ADMIN_URL.'/erp/invoices.php');}
try{enforceScopeAllowed($pdo,(int)($invoice['company_id']??0),(int)($invoice['branch_id']??0),(int)($invoice['warehouse_id']??0),false);}catch(Throwable $e){flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/invoices.php');}
$stmt=$pdo->prepare('SELECT ii.*,p.sku FROM ' . table('invoice_items') . ' ii LEFT JOIN ' . table('products') . ' p ON p.id=ii.product_id WHERE ii.invoice_id=? ORDER BY ii.id');$stmt->execute([$id]);$items=$stmt->fetchAll();
$stmt=$pdo->prepare('SELECT * FROM ' . table('payments') . ' WHERE invoice_id=? ORDER BY paid_at DESC,created_at DESC');$stmt->execute([$id]);$payments=$stmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4"><div><div class="erp-kicker">Invoice</div><h2 class="mb-0"><?php echo esc($invoice['invoice_number']); ?></h2></div><div class="d-flex gap-2 align-items-start"><?php if($invoice['status']==='draft'): ?><a class="btn btn-success" href="<?php echo esc(ADMIN_URL); ?>/erp/approve-invoice.php?id=<?php echo (int)$invoice['id']; ?>">Approve & Reserve Stock</a><?php endif; ?><a class="btn btn-outline-dark" target="_blank" href="<?php echo esc(ADMIN_URL); ?>/erp/print-invoice.php?id=<?php echo (int)$invoice['id']; ?>">Print</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/invoices.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-xl-8"><div class="card-admin p-4"><div class="row g-3"><div class="col-md-6"><h3 class="h6">Customer</h3><div class="fw-semibold"><?php echo esc($invoice['customer_name']); ?> <span class="badge <?php echo $invoice['customer_type']==='b2b'?'badge-b2b':'badge-b2c'; ?>"><?php echo strtoupper(esc($invoice['customer_type'])); ?></span></div><div><?php echo esc($invoice['customer_email']); ?></div><div class="small-muted"><?php echo esc($invoice['customer_code']); ?></div><div class="mt-2"><?php echo nl2br(esc($invoice['billing_address'])); ?></div></div><div class="col-md-6"><h3 class="h6">Status & Terms</h3><div><strong>Status:</strong> <?php echo esc($invoice['status']); ?></div><div><strong>Due:</strong> <?php echo esc($invoice['due_date']); ?></div><div><strong>Approved:</strong> <?php echo esc($invoice['approved_at']); ?></div><div><strong>Paid At:</strong> <?php echo esc($invoice['paid_at']); ?></div></div></div></div></div><div class="col-xl-4"><div class="card-admin p-4 invoice-summary"><div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong><?php echo money($invoice['subtotal']); ?></strong></div><div class="d-flex justify-content-between mb-2"><span>Discount</span><strong><?php echo money($invoice['discount']); ?></strong></div><div class="d-flex justify-content-between mb-2"><span>Tax</span><strong><?php echo money($invoice['tax']); ?></strong></div><div class="d-flex justify-content-between mb-2"><span>Shipping</span><strong><?php echo money($invoice['shipping']); ?></strong></div><hr><div class="d-flex justify-content-between h4"><span>Total</span><strong><?php echo money($invoice['total']); ?></strong></div><div class="d-flex justify-content-between"><span>Paid</span><strong class="money-positive"><?php echo money($invoice['amount_paid']); ?></strong></div><div class="d-flex justify-content-between"><span>Balance</span><strong class="money-negative"><?php echo money($invoice['balance_due']); ?></strong></div></div></div></div>
<div class="table-wrap table-responsive mb-4"><h3 class="h5 mb-3">Invoice Items</h3><table class="table"><thead><tr><th>Description</th><th>SKU</th><th>Qty</th><th>Unit</th><th>Tax %</th><th>Line</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><?php echo esc($item['description']); ?></td><td><?php echo esc($item['sku']); ?></td><td><?php echo esc($item['quantity']); ?></td><td><?php echo money($item['unit_price']); ?></td><td><?php echo esc($item['tax_rate']); ?></td><td><?php echo money($item['line_total']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="row g-4"><div class="col-xl-5"><form method="post" class="card-admin p-4"><input type="hidden" name="form_type" value="payment"><h3 class="h5">Record Payment</h3><?php if(in_array($invoice['status'],['approved','sent','partial'],true)): ?><div class="row g-3"><div class="col-md-6"><label class="form-label">Amount</label><input class="form-control" type="number" step="0.01" min="0.01" max="<?php echo esc($invoice['balance_due']); ?>" name="amount" required></div><div class="col-md-6"><label class="form-label">Method</label><select class="form-select" name="method"><option>Cash</option><option>Bank Transfer</option><option>Card</option><option>Cheque</option><option>Online Payment</option></select></div><div class="col-12"><label class="form-label">Reference</label><input class="form-control" name="reference"></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div><div class="col-12"><button class="btn btn-brand">Save Payment</button></div></div><?php else: ?><div class="empty-state">Approve the invoice before recording payments.</div><?php endif; ?></form></div><div class="col-xl-7"><div class="table-wrap table-responsive"><h3 class="h5 mb-3">Payment History</h3><table class="table"><thead><tr><th>Payment</th><th>Amount</th><th>Method</th><th>Reference</th><th>Date</th></tr></thead><tbody><?php foreach($payments as $payment): ?><tr><td><?php echo esc($payment['payment_number']); ?></td><td><?php echo money($payment['amount']); ?></td><td><?php echo esc($payment['method']); ?></td><td><?php echo esc($payment['reference']); ?></td><td><?php echo esc($payment['paid_at']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>