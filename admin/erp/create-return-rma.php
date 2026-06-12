<?php
$pageTitle='Create Return / RMA';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('returns_rma');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$scope=operationalScope($pdo);
$customers=$pdo->query('SELECT id,customer_code,company_name,contact_name FROM '.table('customers').' WHERE status="active" ORDER BY company_name,contact_name ASC LIMIT 400')->fetchAll();
$products=$pdo->query('SELECT id,sku,name,price FROM '.table('products').' WHERE active=1 ORDER BY name ASC LIMIT 500')->fetchAll();
$invoices=$pdo->query('SELECT id,invoice_number,customer_name,total FROM '.table('invoices').' WHERE status IN ("approved","partial","paid") ORDER BY created_at DESC LIMIT 300')->fetchAll();
$salesOrders=$pdo->query('SELECT id,sales_order_number,customer_name,total FROM '.table('sales_orders').' WHERE status IN ("converted","fulfilled","approved") ORDER BY created_at DESC LIMIT 300')->fetchAll();
$deliveryNotes=$pdo->query('SELECT id,delivery_number,customer_name FROM '.table('delivery_notes').' WHERE status="delivered" ORDER BY created_at DESC LIMIT 300')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $scope=[
    'company_id'=>(int)($_POST['company_id']??$scope['company_id']),
    'branch_id'=>(int)($_POST['branch_id']??$scope['branch_id']),
    'warehouse_id'=>(int)($_POST['warehouse_id']??$scope['warehouse_id']),
    'location_id'=>(int)setting('default_location_id','0'),
  ];
  $pdo->beginTransaction();
  try{
    enforceScopeAllowed($pdo,$scope['company_id'],$scope['branch_id'],$scope['warehouse_id'],true);
    $customerId=(int)($_POST['customer_id']??0)?:null;
    $items=[];$total=0.0;
    foreach(($_POST['item_product_id']??[]) as $idx=>$productIdRaw){
      $productId=(int)$productIdRaw;
      $description=trim((string)($_POST['item_description'][$idx]??''));
      $qty=max(0,(float)($_POST['item_quantity'][$idx]??0));
      $price=max(0,(float)($_POST['item_unit_price'][$idx]??0));
      $condition=trim((string)($_POST['item_condition'][$idx]??'uninspected'))?:'uninspected';
      $disposition=trim((string)($_POST['item_disposition'][$idx]??'restock'))?:'restock';
      if($qty<=0 || ($productId<=0 && $description==='')){continue;}
      if($description===''){
        $find=$pdo->prepare('SELECT name,price FROM '.table('products').' WHERE id=? LIMIT 1');$find->execute([$productId]);$product=$find->fetch();
        $description=(string)($product['name']??'Return item');if($price<=0){$price=(float)($product['price']??0);}
      }
      $line=round($qty*$price,2);$total+=$line;
      $items[]=['product_id'=>$productId?:null,'description'=>$description,'quantity'=>$qty,'unit_price'=>$price,'condition_status'=>$condition,'disposition'=>$disposition,'line_total'=>$line];
    }
    if(!$items){throw new RuntimeException('Add at least one return line.');}
    $total=round($total,2);$number=nextScopedDocumentNumber($pdo,'return_rma','RMA',$scope);
    $stmt=$pdo->prepare('INSERT INTO '.table('returns_rma').' (company_id,branch_id,warehouse_id,rma_number,customer_id,sales_order_id,invoice_id,delivery_note_id,return_date,reason,total_value,status,approval_status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,"draft","not_required",?)');
    $stmt->execute([$scope['company_id']?:null,$scope['branch_id']?:null,$scope['warehouse_id']?:null,$number,$customerId,(int)($_POST['sales_order_id']??0)?:null,(int)($_POST['invoice_id']??0)?:null,(int)($_POST['delivery_note_id']??0)?:null,trim((string)($_POST['return_date']??date('Y-m-d')))?:date('Y-m-d'),trim((string)($_POST['reason']??'')),$total,trim((string)($_POST['notes']??''))]);
    $rmaId=(int)$pdo->lastInsertId();
    $lineStmt=$pdo->prepare('INSERT INTO '.table('return_rma_items').' (return_rma_id,product_id,description,quantity,unit_price,condition_status,disposition,line_total) VALUES (?,?,?,?,?,?,?,?)');
    foreach($items as $item){$lineStmt->execute([$rmaId,$item['product_id'],$item['description'],$item['quantity'],$item['unit_price'],$item['condition_status'],$item['disposition'],$item['line_total']]);}
    logActivity($pdo,'Returns','rma_created','RMA '.$number.' created.','return_rma',$rmaId);
    $pdo->commit();flash('success','RMA created.');redirect(ADMIN_URL.'/erp/view-return-rma.php?id='.$rmaId);
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-return-rma.php');
  }
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Returns Intake</div><h2 class="h4 mb-1">Create Return / RMA</h2><p class="text-secondary mb-0">Capture a return request, link it to the commercial source document, and route larger values for approval.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/returns-rma.php">Back</a></div>
<form method="post" class="row g-4"><div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">RMA Header</h2><div class="row g-3"><div class="col-12"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$scope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$scope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$scope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Customer</label><select class="form-select" name="customer_id"><option value="0">Optional customer</option><?php foreach($customers as $customer): ?><option value="<?php echo (int)$customer['id']; ?>"><?php echo esc($customer['customer_code'].' · '.trim(($customer['company_name']?:'').' '.($customer['contact_name']?:''))); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Invoice</label><select class="form-select" name="invoice_id"><option value="0">Optional invoice</option><?php foreach($invoices as $invoice): ?><option value="<?php echo (int)$invoice['id']; ?>"><?php echo esc($invoice['invoice_number'].' · '.$invoice['customer_name'].' · '.money($invoice['total'])); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Sales Order</label><select class="form-select" name="sales_order_id"><option value="0">Optional sales order</option><?php foreach($salesOrders as $so): ?><option value="<?php echo (int)$so['id']; ?>"><?php echo esc($so['sales_order_number'].' · '.$so['customer_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Delivery Note</label><select class="form-select" name="delivery_note_id"><option value="0">Optional delivery note</option><?php foreach($deliveryNotes as $dn): ?><option value="<?php echo (int)$dn['id']; ?>"><?php echo esc($dn['delivery_number'].' · '.$dn['customer_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Return Date</label><input class="form-control" type="date" name="return_date" value="<?php echo esc(date('Y-m-d')); ?>"></div><div class="col-12"><label class="form-label">Reason</label><textarea class="form-control" rows="3" name="reason"></textarea></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" rows="3" name="notes"></textarea></div></div></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Return Lines</div><h2 class="h5 mb-0">Returned Items</h2></div></div><table class="table align-middle"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Unit Value</th><th>Condition</th><th>Disposition</th></tr></thead><tbody><?php for($i=0;$i<6;$i++): ?><tr><td><select class="form-select form-select-sm" name="item_product_id[]"><option value="0">Custom line</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc(($product['sku']?:'').' · '.$product['name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="item_description[]" placeholder="Returned item"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_quantity[]" value="<?php echo $i===0?'1':'0'; ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_unit_price[]" value="0"></td><td><select class="form-select form-select-sm" name="item_condition[]"><option value="uninspected">Uninspected</option><option value="good">Good</option><option value="damaged">Damaged</option></select></td><td><select class="form-select form-select-sm" name="item_disposition[]"><option value="restock">Restock</option><option value="scrap">Scrap</option><option value="supplier_return">Supplier Return</option></select></td></tr><?php endfor; ?></tbody></table></div><div class="card-admin p-4 mt-4 d-flex justify-content-between align-items-center gap-3"><div><strong>Approval routing:</strong> larger RMAs can enter the Approval Center before physical receipt.</div><button class="btn btn-brand">Create RMA</button></div></div></form>
<?php include dirname(__DIR__).'/footer.php'; ?>