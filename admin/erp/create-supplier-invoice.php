<?php
$pageTitle='Create Supplier Invoice';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('supplier_invoices');
$pdo=getDB();
$poId=(int)($_GET['po_id']??($_POST['po_id']??0));
$eligiblePos=$pdo->query('SELECT id,po_number,supplier_name,total,status FROM '.table('purchase_orders').' WHERE status IN ("approved","partially_received","received") ORDER BY created_at DESC,id DESC LIMIT 250')->fetchAll();
$po=null;$items=[];$grns=[];
if($poId>0){
  $stmt=$pdo->prepare('SELECT * FROM '.table('purchase_orders').' WHERE id=? LIMIT 1');$stmt->execute([$poId]);$po=$stmt->fetch();
  if($po){
    enforceScopeAllowed($pdo,(int)($po['company_id']??0),(int)($po['branch_id']??0),(int)($po['warehouse_id']??0),false);
    $itemStmt=$pdo->prepare('SELECT poi.*,p.sku,p.name product_name FROM '.table('purchase_order_items').' poi LEFT JOIN '.table('products').' p ON p.id=poi.product_id WHERE poi.purchase_order_id=? ORDER BY poi.id ASC');$itemStmt->execute([$poId]);$items=$itemStmt->fetchAll();
    $grnStmt=$pdo->prepare('SELECT id,grn_number,receipt_date,total_value FROM '.table('goods_receipts').' WHERE purchase_order_id=? AND status="posted" ORDER BY receipt_date DESC,id DESC');$grnStmt->execute([$poId]);$grns=$grnStmt->fetchAll();
  }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->beginTransaction();
  try{
    if(!$po){throw new RuntimeException('Select a valid purchase order first.');}
    enforceScopeAllowed($pdo,(int)($po['company_id']??0),(int)($po['branch_id']??0),(int)($po['warehouse_id']??0),true);
    $scope=['company_id'=>(int)($po['company_id']??0),'branch_id'=>(int)($po['branch_id']??0),'warehouse_id'=>(int)($po['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $internal=nextScopedDocumentNumber($pdo,'supplier_invoice','SIN',$scope);
    $selectedGrn=(int)($_POST['goods_receipt_id']??0)?:null;
    $lineMap=[];foreach($items as $item){$lineMap[(int)$item['id']]=$item;}
    $prepared=[];$subtotal=0.0;$tax=0.0;
    foreach(($_POST['quantity']??[]) as $poItemIdRaw=>$qtyRaw){
      $poItemId=(int)$poItemIdRaw;$source=$lineMap[$poItemId]??null;if(!$source){continue;}
      $qty=max(0,(float)$qtyRaw);if($qty<=0){continue;}
      $cost=max(0,(float)($_POST['unit_cost'][$poItemId]??$source['unit_cost']??0));
      $taxRate=max(0,(float)($_POST['tax_rate'][$poItemId]??$source['tax_rate']??0));
      $line=round($qty*$cost,2);$lineTax=round($line*$taxRate/100,2);
      $subtotal+=$line;$tax+=$lineTax;
      $prepared[]=['po_item_id'=>$poItemId,'product_id'=>(int)($source['product_id']??0)?:null,'description'=>(string)($source['description']??$source['product_name']??''),'quantity'=>$qty,'unit_cost'=>$cost,'tax_rate'=>$taxRate,'line_total'=>$line];
    }
    if(!$prepared){throw new RuntimeException('Enter at least one supplier invoice line quantity.');}
    $subtotal=round($subtotal,2);$tax=round($tax,2);$total=round($subtotal+$tax,2);
    $stmt=$pdo->prepare('INSERT INTO '.table('supplier_invoices').' (company_id,branch_id,warehouse_id,internal_number,supplier_invoice_number,supplier_id,supplier_name,purchase_order_id,goods_receipt_id,invoice_date,due_date,subtotal,tax,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,"draft",?)');
    $stmt->execute([(int)($po['company_id']??0)?:null,(int)($po['branch_id']??0)?:null,(int)($po['warehouse_id']??0)?:null,$internal,trim((string)($_POST['supplier_invoice_number']??'')),(int)($po['supplier_id']??0)?:null,$po['supplier_name'],$poId,$selectedGrn,trim((string)($_POST['invoice_date']??date('Y-m-d')))?:date('Y-m-d'),trim((string)($_POST['due_date']??''))?:null,$subtotal,$tax,$total,trim((string)($_POST['notes']??''))]);
    $invoiceId=(int)$pdo->lastInsertId();
    $itemInsert=$pdo->prepare('INSERT INTO '.table('supplier_invoice_items').' (supplier_invoice_id,purchase_order_item_id,goods_receipt_item_id,product_id,description,quantity,unit_cost,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach($prepared as $line){$itemInsert->execute([$invoiceId,$line['po_item_id'],null,$line['product_id'],$line['description'],$line['quantity'],$line['unit_cost'],$line['tax_rate'],$line['line_total']]);}
    $match=supplierInvoiceMatchSummary($pdo,$invoiceId,true);
    if(($match['match_status']??'')==='matched'){
      $pdo->prepare('UPDATE '.table('supplier_invoices').' SET status="matched",approval_status="not_required" WHERE id=?')->execute([$invoiceId]);
      flash('success','Supplier invoice created and matched to PO/GRN.');
    }else{
      $request=createApprovalRequestForDocument($pdo,'supplier_invoice',$invoiceId,'approve','Supplier invoice variance requires AP review.');
      if($request){
        $pdo->prepare('UPDATE '.table('supplier_invoices').' SET status="pending_approval",approval_status="pending_approval" WHERE id=?')->execute([$invoiceId]);
        flash('success','Supplier invoice created with variance and submitted to approval center: '.$request['request_number'].'.');
      }else{
        $pdo->prepare('UPDATE '.table('supplier_invoices').' SET status="variance",approval_status="not_required" WHERE id=?')->execute([$invoiceId]);
        flash('success','Supplier invoice created with match variance. Review before approval/posting.');
      }
    }
    logActivity($pdo,'Accounts Payable','supplier_invoice_created','Supplier invoice '.$internal.' recorded and checked by three-way match.','supplier_invoice',$invoiceId);
    $pdo->commit();redirect(ADMIN_URL.'/erp/view-supplier-invoice.php?id='.$invoiceId);
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-supplier-invoice.php?po_id='.$poId);
  }
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">AP Invoice Capture</div><h2 class="h4 mb-1">Create Supplier Invoice</h2><p class="text-secondary mb-0">Select an approved PO, enter supplier bill values, and run automatic PO ↔ GRN ↔ invoice matching.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-invoices.php">Back</a></div>
<?php if(!$po): ?><form class="card-admin p-4"><label class="form-label">Purchase Order</label><div class="d-flex gap-2"><select class="form-select" name="po_id"><option value="0">Select approved / received PO</option><?php foreach($eligiblePos as $option): ?><option value="<?php echo (int)$option['id']; ?>"><?php echo esc($option['po_number'].' · '.$option['supplier_name'].' · '.money($option['total'])); ?></option><?php endforeach; ?></select><button class="btn btn-brand">Load PO</button></div></form><?php else: ?>
<form method="post" class="row g-4"><input type="hidden" name="po_id" value="<?php echo (int)$poId; ?>"><div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5">Invoice Header</h2><div class="row g-3"><div class="col-12"><label class="form-label">PO</label><input class="form-control" value="<?php echo esc($po['po_number'].' · '.$po['supplier_name']); ?>" disabled></div><div class="col-12"><label class="form-label">Supplier Invoice Number</label><input class="form-control" name="supplier_invoice_number" required></div><div class="col-12"><label class="form-label">Linked GRN</label><select class="form-select" name="goods_receipt_id"><option value="0">Auto / any GRN for PO</option><?php foreach($grns as $grn): ?><option value="<?php echo (int)$grn['id']; ?>"><?php echo esc($grn['grn_number'].' · '.$grn['receipt_date'].' · '.money($grn['total_value'])); ?></option><?php endforeach; ?></select></div><div class="col-6"><label class="form-label">Invoice Date</label><input class="form-control" type="date" name="invoice_date" value="<?php echo esc(date('Y-m-d')); ?>"></div><div class="col-6"><label class="form-label">Due Date</label><input class="form-control" type="date" name="due_date"></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" rows="3" name="notes"></textarea></div></div></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Invoice Lines</div><h2 class="h5 mb-0">PO-Based Supplier Lines</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>PO Qty</th><th>Received</th><th>Invoice Qty</th><th>Unit Cost</th><th>Tax %</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><strong><?php echo esc(($item['sku']?:'').' · '.($item['description']?:$item['product_name'])); ?></strong></td><td><?php echo number_format((float)$item['quantity'],2); ?></td><td><?php echo number_format((float)$item['received_quantity'],2); ?></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="quantity[<?php echo (int)$item['id']; ?>]" value="<?php echo esc((float)$item['received_quantity']>0?(float)$item['received_quantity']:(float)$item['quantity']); ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_cost[<?php echo (int)$item['id']; ?>]" value="<?php echo esc($item['unit_cost']); ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[<?php echo (int)$item['id']; ?>]" value="<?php echo esc($item['tax_rate']); ?>"></td></tr><?php endforeach; ?></tbody></table></div><div class="card-admin p-4 mt-4 d-flex justify-content-between align-items-center gap-3"><div><strong>Three-way match:</strong> quantity and value will be compared against PO and accepted GRN quantities.</div><button class="btn btn-brand">Create & Match Invoice</button></div></div></form>
<?php endif; ?>
<?php include dirname(__DIR__).'/footer.php'; ?>