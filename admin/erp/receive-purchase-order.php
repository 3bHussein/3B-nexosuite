<?php
$pageTitle='Create Goods Receipt Note';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('goods_receipts');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT * FROM '.table('purchase_orders').' WHERE id=? LIMIT 1');
$stmt->execute([$id]);$po=$stmt->fetch();
if(!$po){flash('error','Purchase order not found.');redirect(ADMIN_URL.'/erp/purchase-orders.php');}
enforceScopeAllowed($pdo,(int)($po['company_id']??0),(int)($po['branch_id']??0),(int)($po['warehouse_id']??0),true);
if(!in_array((string)$po['status'],['approved','partially_received'],true)){flash('error','Only approved or partially received purchase orders can create goods receipt notes.');redirect(ADMIN_URL.'/erp/view-purchase-order.php?id='.$id);}

$scope=operationalScope($pdo);$scope['company_id']=(int)($po['company_id']??$scope['company_id']);$scope['branch_id']=(int)($po['branch_id']??$scope['branch_id']);$scope['warehouse_id']=(int)($po['warehouse_id']??$scope['warehouse_id']);$scope['location_id']=(int)setting('default_location_id','0');
$itemsStmt=$pdo->prepare('SELECT poi.*,p.sku,p.name product_name FROM '.table('purchase_order_items').' poi LEFT JOIN '.table('products').' p ON p.id=poi.product_id WHERE poi.purchase_order_id=? ORDER BY poi.id ASC');
$itemsStmt->execute([$id]);$items=$itemsStmt->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('purchase_orders').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('Purchase order no longer exists.');}
    if(!in_array((string)$current['status'],['approved','partially_received'],true)){throw new RuntimeException('Purchase order is not open for receipt.');}
    $number=nextScopedDocumentNumber($pdo,'goods_receipt','GRN',$scope);
    $user=currentUser();$userId=(int)($user['id']??0)?:null;
    $grnStmt=$pdo->prepare('INSERT INTO '.table('goods_receipts').' (company_id,branch_id,warehouse_id,grn_number,purchase_order_id,supplier_id,supplier_name,receipt_date,received_by,status,total_value,notes) VALUES (?,?,?,?,?,?,?,?,?,"posted",0,?)');
    $grnStmt->execute([(int)($po['company_id']??0)?:null,(int)($po['branch_id']??0)?:null,(int)($po['warehouse_id']??0)?:null,$number,$id,(int)($po['supplier_id']??0)?:null,$po['supplier_name'],trim((string)($_POST['receipt_date']??date('Y-m-d')))?:date('Y-m-d'),$userId,trim((string)($_POST['notes']??''))]);
    $grnId=(int)$pdo->lastInsertId();
    $itemInsert=$pdo->prepare('INSERT INTO '.table('goods_receipt_items').' (goods_receipt_id,purchase_order_item_id,product_id,description,quantity_received,accepted_quantity,rejected_quantity,unit_cost,line_total,notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $updatePoi=$pdo->prepare('UPDATE '.table('purchase_order_items').' SET received_quantity=received_quantity+? WHERE id=? AND purchase_order_id=?');
    $receivedAny=false;$totalValue=0.0;
    foreach($items as $item){
      $lineId=(int)$item['id'];
      $accepted=max(0,(float)($_POST['accepted'][$lineId]??0));
      $rejected=max(0,(float)($_POST['rejected'][$lineId]??0));
      $received=$accepted+$rejected;
      $remaining=max(0,(float)$item['quantity']-(float)$item['received_quantity']);
      if($received<=0){continue;}
      if($accepted>$remaining){throw new RuntimeException('Accepted quantity exceeds PO remaining quantity for '.$item['description'].'.');}
      if($received>$remaining+max(0,$rejected)){/* receiving quality rejection may exceed PO accepted? do not block rejected evidence */ }
      $lineValue=round($accepted*(float)($item['unit_cost']??0),2);
      $itemInsert->execute([$grnId,$lineId,(int)($item['product_id']??0)?:null,$item['description'],$received,$accepted,$rejected,(float)($item['unit_cost']??0),$lineValue,trim((string)($_POST['line_notes'][$lineId]??''))]);
      if($accepted>0){
        $updatePoi->execute([$accepted,$lineId,$id]);
        $productId=(int)($item['product_id']??0);
        if($productId>0){adjustWarehouseStock($pdo,$productId,$accepted,$scope,'goods_receipt',$grnId,'Accepted stock received through GRN '.$number.'.',(float)($item['unit_cost']??0));}
        $totalValue+=$lineValue;
      }
      $receivedAny=true;
    }
    if(!$receivedAny){throw new RuntimeException('Enter at least one accepted or rejected quantity.');}
    $pdo->prepare('UPDATE '.table('goods_receipts').' SET total_value=? WHERE id=?')->execute([round($totalValue,2),$grnId]);
    $remainingStmt=$pdo->prepare('SELECT COALESCE(SUM(GREATEST(quantity-received_quantity,0)),0) FROM '.table('purchase_order_items').' WHERE purchase_order_id=?');
    $remainingStmt->execute([$id]);$remainingAfter=(float)$remainingStmt->fetchColumn();
    if($remainingAfter<=0){
      $pdo->prepare('UPDATE '.table('purchase_orders').' SET status="received",received_at=NOW() WHERE id=?')->execute([$id]);$newStatus='received';
    }else{
      $pdo->prepare('UPDATE '.table('purchase_orders').' SET status="partially_received" WHERE id=?')->execute([$id]);$newStatus='partially_received';
    }
    logActivity($pdo,'Procurement','goods_receipt_created','Goods receipt note '.$number.' posted against PO '.$po['po_number'].'; PO status '.$newStatus.'.','goods_receipt',$grnId);
    $pdo->commit();flash('success','Goods receipt note posted. Accepted stock was added to inventory.');redirect(ADMIN_URL.'/erp/view-goods-receipt.php?id='.$grnId);
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/receive-purchase-order.php?id='.$id);
  }
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Goods Receipt</div><h2 class="h4 mb-1">Create GRN for <?php echo esc($po['po_number']); ?></h2><p class="text-secondary mb-0">Accepted quantities update stock and moving-average valuation. Rejected quantities remain visible for supplier follow-up.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-purchase-order.php?id=<?php echo (int)$po['id']; ?>">Back to PO</a></div>
<form method="post" class="row g-4">
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Receipt Lines</div><h2 class="h5 mb-0">Accept / Reject Quantities</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Ordered</th><th>Received</th><th>Remaining</th><th>Accept</th><th>Reject</th><th>Note</th></tr></thead><tbody><?php foreach($items as $item): $remaining=max(0,(float)$item['quantity']-(float)$item['received_quantity']); ?><tr><td><strong><?php echo esc(($item['sku']?:'').' · '.($item['description']?:$item['product_name'])); ?></strong><div class="small text-secondary"><?php echo money($item['unit_cost']); ?></div></td><td><?php echo number_format((float)$item['quantity'],2); ?></td><td><?php echo number_format((float)$item['received_quantity'],2); ?></td><td><?php echo number_format($remaining,2); ?></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" max="<?php echo esc($remaining); ?>" name="accepted[<?php echo (int)$item['id']; ?>]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="rejected[<?php echo (int)$item['id']; ?>]" value="0"></td><td><input class="form-control form-control-sm" name="line_notes[<?php echo (int)$item['id']; ?>]" placeholder="Damage, shortage..."></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="col-xl-4"><div class="card-admin p-4"><div class="erp-kicker">GRN Header</div><div class="row g-3 mt-1"><div class="col-12"><label class="form-label">Receipt Date</label><input class="form-control" type="date" name="receipt_date" value="<?php echo esc(date('Y-m-d')); ?>"></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" rows="4" name="notes"></textarea></div></div><button class="btn btn-brand w-100 mt-3">Post Goods Receipt Note</button></div></div>
</form>
<?php include dirname(__DIR__).'/footer.php'; ?>