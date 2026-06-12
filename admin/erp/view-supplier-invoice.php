<?php
$pageTitle='View Supplier Invoice';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('supplier_invoices');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT si.*,po.po_number,gr.grn_number,s.supplier_code FROM '.table('supplier_invoices').' si LEFT JOIN '.table('purchase_orders').' po ON po.id=si.purchase_order_id LEFT JOIN '.table('goods_receipts').' gr ON gr.id=si.goods_receipt_id LEFT JOIN '.table('suppliers').' s ON s.id=si.supplier_id WHERE si.id=? LIMIT 1');
$stmt->execute([$id]);$invoice=$stmt->fetch();
if(!$invoice){flash('error','Supplier invoice not found.');redirect(ADMIN_URL.'/erp/supplier-invoices.php');}
enforceScopeAllowed($pdo,(int)($invoice['company_id']??0),(int)($invoice['branch_id']??0),(int)($invoice['warehouse_id']??0),false);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('supplier_invoices').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('Supplier invoice not found during update.');}
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    if($action==='rematch'){
      $match=supplierInvoiceMatchSummary($pdo,$id,true);
      flash('success','Three-way match recalculated: '.($match['match_status']??'pending').'.');
    }elseif($action==='approve_matched'){
      if(($current['match_status']??'')!=='matched'){throw new RuntimeException('Only fully matched supplier invoices can be approved directly.');}
      $pdo->prepare('UPDATE '.table('supplier_invoices').' SET status="approved",approval_status="approved",approved_at=NOW() WHERE id=?')->execute([$id]);
      logActivity($pdo,'Accounts Payable','supplier_invoice_approved','Matched supplier invoice '.$current['internal_number'].' approved.','supplier_invoice',$id);
      flash('success','Matched supplier invoice approved.');
    }elseif($action==='submit_variance'){
      if(($current['match_status']??'')!=='variance'){throw new RuntimeException('Only invoices with variance can be submitted for approval.');}
      $request=createApprovalRequestForDocument($pdo,'supplier_invoice',$id,'approve','Supplier invoice match variance approval.');
      if(!$request){throw new RuntimeException('No active approval rule matched this variance; adjust rules or resolve the mismatch.');}
      $pdo->prepare('UPDATE '.table('supplier_invoices').' SET status="pending_approval",approval_status="pending_approval" WHERE id=?')->execute([$id]);
      flash('success','Variance submitted to approval center: '.$request['request_number'].'.');
    }elseif($action==='post'){
      if(($current['status']??'')!=='approved'){throw new RuntimeException('Approve the supplier invoice before posting to Accounts Payable.');}
      postSupplierInvoiceAccounting($pdo,$id);
      flash('success','Supplier invoice posted to Accounts Payable.');
    }
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-supplier-invoice.php?id='.$id);
}
$items=$pdo->prepare('SELECT sii.*,poi.quantity po_quantity,poi.received_quantity po_received,p.sku,p.name product_name FROM '.table('supplier_invoice_items').' sii LEFT JOIN '.table('purchase_order_items').' poi ON poi.id=sii.purchase_order_item_id LEFT JOIN '.table('products').' p ON p.id=sii.product_id WHERE sii.supplier_invoice_id=? ORDER BY sii.id ASC');$items->execute([$id]);$lines=$items->fetchAll();
$approval=activeApprovalRequest($pdo,'supplier_invoice',$id,'approve');
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Supplier Invoice</div><h2 class="h4 mb-1"><?php echo esc($invoice['internal_number']); ?></h2><p class="text-secondary mb-0">Vendor invoice <?php echo esc($invoice['supplier_invoice_number']?:'—'); ?> · PO <?php echo esc($invoice['po_number']?:'—'); ?> · GRN <?php echo esc($invoice['grn_number']?:'Any'); ?></p></div><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=supplier_invoice&id=<?php echo (int)$invoice['id']; ?>">Attachments</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-invoices.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Total</div><div class="metric-sm"><?php echo money($invoice['total']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Matched Total</div><div class="metric-sm"><?php echo money($invoice['matched_total']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Difference</div><div class="metric-sm <?php echo (float)$invoice['difference_amount']>0?'money-negative':'money-positive'; ?>"><?php echo money($invoice['difference_amount']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Status</div><div><span class="badge fs-6 bg-<?php echo esc(statusTone($invoice['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($invoice['status'],'_'))); ?></span></div><div class="small mt-2"><span class="badge bg-<?php echo esc(statusTone($invoice['match_status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($invoice['match_status'],'_'))); ?></span></div></div></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Three-Way Match</div><h2 class="h5 mb-0">Invoice Lines vs PO / GRN</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>PO Qty</th><th>GRN Qty</th><th>Invoice Qty</th><th>Matched Qty</th><th>Line Total</th><th>Variance</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong></td><td><?php echo number_format((float)$line['po_quantity'],2); ?></td><td><?php echo number_format((float)$line['po_received'],2); ?></td><td><?php echo number_format((float)$line['quantity'],2); ?></td><td><?php echo number_format((float)$line['matched_quantity'],2); ?></td><td><?php echo money($line['line_total']); ?></td><td><?php echo money($line['variance_amount']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4"><div class="erp-kicker">Actions</div><div class="d-grid gap-2 mt-3"><form method="post"><button class="btn btn-outline-primary w-100" name="action" value="rematch">Recalculate Match</button></form><?php if($invoice['status']==='matched'): ?><form method="post"><button class="btn btn-success w-100" name="action" value="approve_matched">Approve Matched Invoice</button></form><?php endif; ?><?php if($invoice['match_status']==='variance' && $invoice['approval_status']!=='pending_approval'): ?><form method="post"><button class="btn btn-warning w-100" name="action" value="submit_variance">Submit Variance Approval</button></form><?php endif; ?><?php if($approval): ?><a class="btn btn-warning" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$approval['id']; ?>">Open Approval Request</a><?php endif; ?><?php if($invoice['status']==='approved'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="post">Post to Accounts Payable</button></form><?php endif; ?></div></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>