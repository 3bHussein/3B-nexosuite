<?php
$pageTitle='View Sales Order';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('sales_orders');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT so.*,c.customer_code,c.credit_limit,c.credit_status,i.invoice_number,dn.delivery_number FROM '.table('sales_orders').' so LEFT JOIN '.table('customers').' c ON c.id=so.customer_id LEFT JOIN '.table('invoices').' i ON i.id=so.converted_invoice_id LEFT JOIN '.table('delivery_notes').' dn ON dn.id=so.delivery_note_id WHERE so.id=? LIMIT 1');
$stmt->execute([$id]);$order=$stmt->fetch();
if(!$order){flash('error','Sales order not found.');redirect(ADMIN_URL.'/erp/sales-orders.php');}
enforceScopeAllowed($pdo,(int)($order['company_id']??0),(int)($order['branch_id']??0),(int)($order['warehouse_id']??0),false);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('sales_orders').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('Sales order not found during workflow update.');}
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    if($action==='approve'){
      if(($current['status']??'')!=='draft'){throw new RuntimeException('Only draft sales orders can be approved.');}
      $credit=salesOrderCreditDecision($pdo,$current);
      $status=(string)($credit['status']??'not_required');
      $isBlocked=in_array($status,['exceeded','hold','zero_limit'],true) && setting('customer_credit_block_when_exceeded','1')==='1';
      if($isBlocked){
        $request=createApprovalRequestForDocument($pdo,'sales_order',$id,'credit_override','Sales order credit limit override: shortfall '.money($credit['shortfall']??0).'.');
        if(!$request){throw new RuntimeException('Credit control blocked this order and no approval rule matched for override.');}
        $pdo->prepare('UPDATE '.table('sales_orders').' SET status="pending_approval",credit_check_status="blocked" WHERE id=?')->execute([$id]);
        flash('success','Credit override request submitted: '.$request['request_number'].'.');
      }else{
        $pdo->prepare('UPDATE '.table('sales_orders').' SET status="approved",credit_check_status=?,approved_at=NOW() WHERE id=?')->execute([$status==='passed'?'passed':'not_required',$id]);
        flash('success','Sales order approved.');
      }
      logActivity($pdo,'Sales','sales_order_approval_review','Sales order '.$current['sales_order_number'].' evaluated for approval and credit control.','sales_order',$id);
    }elseif($action==='convert_invoice'){
      $invoiceId=createInvoiceFromSalesOrder($pdo,$id);
      $pdo->commit();flash('success','Invoice created from sales order.');redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$invoiceId);
    }elseif($action==='create_delivery'){
      $deliveryId=createDeliveryNoteFromSalesOrder($pdo,$id);
      $pdo->commit();flash('success','Delivery note created from sales order.');redirect(ADMIN_URL.'/erp/view-delivery-note.php?id='.$deliveryId);
    }elseif($action==='cancel'){
      if(in_array((string)$current['status'],['converted','fulfilled','cancelled'],true)){throw new RuntimeException('This sales order can no longer be cancelled.');}
      $pdo->prepare('UPDATE '.table('sales_orders').' SET status="cancelled" WHERE id=?')->execute([$id]);
      logActivity($pdo,'Sales','sales_order_cancelled','Sales order '.$current['sales_order_number'].' cancelled.','sales_order',$id);
      flash('success','Sales order cancelled.');
    }
    if($pdo->inTransaction()){$pdo->commit();}
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-sales-order.php?id='.$id);
}
$items=$pdo->prepare('SELECT soi.*,p.sku,p.name product_name FROM '.table('sales_order_items').' soi LEFT JOIN '.table('products').' p ON p.id=soi.product_id WHERE soi.sales_order_id=? ORDER BY soi.id ASC');$items->execute([$id]);$lines=$items->fetchAll();
$credit=salesOrderCreditDecision($pdo,$order);
$approval=activeApprovalRequest($pdo,'sales_order',$id,'credit_override');
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Sales Order</div><h2 class="h4 mb-1"><?php echo esc($order['sales_order_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc(($order['customer_code']?:'').' · '.($order['customer_name']?:'Customer')); ?></p></div><div class="d-flex flex-wrap gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($order['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($order['status'],'_'))); ?></span><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=sales_order&id=<?php echo (int)$order['id']; ?>">Attachments</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-orders.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Order Total</div><div class="metric-sm"><?php echo money($order['total']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Credit Limit</div><div class="metric-sm"><?php echo money($credit['limit']??0); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Exposure</div><div class="metric-sm"><?php echo money($credit['exposure']??0); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Shortfall</div><div class="metric-sm <?php echo (float)($credit['shortfall']??0)>0?'money-negative':'money-positive'; ?>"><?php echo money($credit['shortfall']??0); ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Order Lines</div><h2 class="h5 mb-0">Commercial Items</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th>Line</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong></td><td><?php echo number_format((float)$line['quantity'],2); ?></td><td><?php echo money($line['unit_price']); ?></td><td><?php echo number_format((float)$line['tax_rate'],2); ?>%</td><td><?php echo money($line['line_total']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4 mb-4"><div class="erp-kicker"><?php echo t('Credit Control', 'التحكم الائتماني'); ?></div><h3 class="h5 mb-3"><?php echo esc(str_replace('_',' ',ucwords((string)($credit['status']??'not_required'),'_'))); ?></h3><div class="small d-grid gap-2"><div><strong>Projected:</strong> <?php echo money($credit['projected']??0); ?></div><div><strong>Available before order:</strong> <?php echo money($credit['available']??0); ?></div><div><strong>Credit state:</strong> <?php echo esc($order['credit_check_status']); ?></div></div><?php if($approval): ?><a class="btn btn-warning w-100 mt-3" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$approval['id']; ?>">Open Credit Override Approval</a><?php endif; ?></div><div class="card-admin p-4"><div class="erp-kicker">Actions</div><div class="d-grid gap-2 mt-3"><?php if($order['status']==='draft'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="approve">Approve / Credit Check</button></form><?php endif; ?><?php if(in_array($order['status'],['approved','fulfilled'],true) && empty($order['converted_invoice_id'])): ?><form method="post"><button class="btn btn-success w-100" name="action" value="convert_invoice">Create Invoice</button></form><?php endif; ?><?php if(in_array($order['status'],['approved','converted','fulfilled'],true) && empty($order['delivery_note_id'])): ?><form method="post"><button class="btn btn-outline-primary w-100" name="action" value="create_delivery">Create Delivery Note</button></form><?php endif; ?><?php if(!in_array($order['status'],['converted','fulfilled','cancelled'],true)): ?><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel Sales Order</button></form><?php endif; ?><?php if(!empty($order['invoice_number'])): ?><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/view-invoice.php?id=<?php echo (int)$order['converted_invoice_id']; ?>">Open Invoice</a><?php endif; ?><?php if(!empty($order['delivery_number'])): ?><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/view-delivery-note.php?id=<?php echo (int)$order['delivery_note_id']; ?>">Open Delivery Note</a><?php endif; ?></div></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>