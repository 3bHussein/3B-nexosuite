<?php
$pageTitle='Create Sales Order';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('sales_orders');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$scope=operationalScope($pdo);
$customers=$pdo->query('SELECT id,customer_code,company_name,contact_name,email,customer_type,billing_address,shipping_address,credit_limit FROM '.table('customers').' WHERE status="active" ORDER BY company_name,contact_name ASC LIMIT 400')->fetchAll();
$products=$pdo->query('SELECT id,sku,name,price,stock FROM '.table('products').' WHERE active=1 ORDER BY name ASC LIMIT 500')->fetchAll();

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
    $customerId=(int)($_POST['customer_id']??0);
    $customerStmt=$pdo->prepare('SELECT * FROM '.table('customers').' WHERE id=? LIMIT 1');$customerStmt->execute([$customerId]);$customer=$customerStmt->fetch();
    if(!$customer){throw new RuntimeException('Select a valid customer.');}
    $items=[];$subtotal=0.0;$tax=0.0;
    foreach(($_POST['item_product_id']??[]) as $idx=>$productIdRaw){
      $productId=(int)$productIdRaw;
      $description=trim((string)($_POST['item_description'][$idx]??''));
      $qty=max(0,(float)($_POST['item_quantity'][$idx]??0));
      $price=max(0,(float)($_POST['item_price'][$idx]??0));
      $taxRate=max(0,(float)($_POST['item_tax_rate'][$idx]??0));
      if($qty<=0 || ($productId<=0 && $description==='')){continue;}
      if($description===''){
        $prodStmt=$pdo->prepare('SELECT name,price FROM '.table('products').' WHERE id=? LIMIT 1');$prodStmt->execute([$productId]);$product=$prodStmt->fetch();
        $description=(string)($product['name']??'Product');
        if($price<=0){$price=(float)($product['price']??0);}
      }
      $line=round($qty*$price,2);$lineTax=round($line*$taxRate/100,2);
      $subtotal+=$line;$tax+=$lineTax;
      $items[]=['item_type'=>$productId>0?'product':'service','product_id'=>$productId?:null,'description'=>$description,'quantity'=>$qty,'unit_price'=>$price,'tax_rate'=>$taxRate,'line_total'=>$line];
    }
    if(!$items){throw new RuntimeException('Add at least one sales order line.');}
    $discount=max(0,(float)($_POST['discount']??0));$shipping=max(0,(float)($_POST['shipping']??0));
    $subtotal=round($subtotal,2);$tax=round($tax,2);$total=round(max(0,$subtotal-$discount)+$tax+$shipping,2);
    $number=nextScopedDocumentNumber($pdo,'sales_order','SO',$scope);
    $stmt=$pdo->prepare('INSERT INTO '.table('sales_orders').' (company_id,branch_id,warehouse_id,sales_order_number,customer_id,customer_name,customer_email,customer_type,billing_address,shipping_address,order_date,due_date,subtotal,discount,tax,shipping,total,status,credit_check_status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"draft","not_required",?)');
    $stmt->execute([$scope['company_id']?:null,$scope['branch_id']?:null,$scope['warehouse_id']?:null,$number,$customerId,trim((string)(($customer['company_name']?:'').' '.($customer['contact_name']?:''))),$customer['email'],$customer['customer_type'],$customer['billing_address'],$customer['shipping_address'],trim((string)($_POST['order_date']??date('Y-m-d')))?:date('Y-m-d'),trim((string)($_POST['due_date']??''))?:null,$subtotal,$discount,$tax,$shipping,$total,trim((string)($_POST['notes']??''))]);
    $salesOrderId=(int)$pdo->lastInsertId();
    $lineStmt=$pdo->prepare('INSERT INTO '.table('sales_order_items').' (sales_order_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
    foreach($items as $item){$lineStmt->execute([$salesOrderId,$item['item_type'],$item['product_id'],$item['description'],$item['quantity'],$item['unit_price'],$item['tax_rate'],$item['line_total']]);}
    $credit=salesOrderCreditDecision($pdo,['id'=>$salesOrderId,'customer_id'=>$customerId,'total'=>$total]);
    $creditStatus=match($credit['status']??'not_required'){ 'passed'=>'passed','not_required'=>'not_required', default=>'blocked' };
    $pdo->prepare('UPDATE '.table('sales_orders').' SET credit_check_status=? WHERE id=?')->execute([$creditStatus,$salesOrderId]);
    logActivity($pdo,'Sales','sales_order_created','Sales order '.$number.' created.','sales_order',$salesOrderId);
    $pdo->commit();flash('success','Sales order created.');redirect(ADMIN_URL.'/erp/view-sales-order.php?id='.$salesOrderId);
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-sales-order.php');
  }
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Sales Order Entry</div><h2 class="h4 mb-1">Create Sales Order</h2><p class="text-secondary mb-0">Capture customer demand, evaluate credit exposure, and prepare order-to-cash execution.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-orders.php">Back</a></div>
<form method="post" class="row g-4"><div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">Header</h2><div class="row g-3"><div class="col-12"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$scope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$scope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$scope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Customer</label><select class="form-select" name="customer_id" required><option value="">Select customer</option><?php foreach($customers as $customer): ?><option value="<?php echo (int)$customer['id']; ?>"><?php echo esc($customer['customer_code'].' · '.trim(($customer['company_name']?:'').' '.($customer['contact_name']?:''))); ?></option><?php endforeach; ?></select></div><div class="col-6"><label class="form-label">Order Date</label><input class="form-control" type="date" name="order_date" value="<?php echo esc(date('Y-m-d')); ?>"></div><div class="col-6"><label class="form-label">Due Date</label><input class="form-control" type="date" name="due_date"></div><div class="col-6"><label class="form-label">Discount</label><input class="form-control" type="number" step="0.01" min="0" name="discount" value="0"></div><div class="col-6"><label class="form-label">Shipping</label><input class="form-control" type="number" step="0.01" min="0" name="shipping" value="0"></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" rows="3" name="notes"></textarea></div></div></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Order Lines</div><h2 class="h5 mb-0">Products / Services</h2></div></div><table class="table align-middle"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Tax %</th></tr></thead><tbody><?php for($i=0;$i<6;$i++): ?><tr><td><select class="form-select form-select-sm" name="item_product_id[]"><option value="0">Custom service</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc(($product['sku']?:'').' · '.$product['name'].' · '.money($product['price'])); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="item_description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_quantity[]" value="<?php echo $i===0?'1':'0'; ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_tax_rate[]" value="<?php echo esc(setting('tax_rate','5')); ?>"></td></tr><?php endfor; ?></tbody></table></div><div class="card-admin p-4 mt-4 d-flex flex-wrap justify-content-between align-items-center gap-3"><div><strong>Credit control:</strong> B2B orders are checked against customer credit limit and open exposure before approval.</div><button class="btn btn-brand">Create Sales Order</button></div></div></form>
<?php include dirname(__DIR__).'/footer.php'; ?>