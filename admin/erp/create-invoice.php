<?php
$pageTitle='Create Itemized Invoice';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('invoices');
$pdo=getDB();
$customers=$pdo->query('SELECT * FROM ' . table('customers') . ' WHERE status="active" ORDER BY company_name,contact_name')->fetchAll();
$products=$pdo->query('SELECT id,name,sku,price FROM ' . table('products') . ' WHERE active=1 ORDER BY name')->fetchAll();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->beginTransaction();
  try{
    $customerId=(int)($_POST['customer_id']??0);$customer=null;
    if($customerId>0){$stmt=$pdo->prepare('SELECT * FROM ' . table('customers') . ' WHERE id=? LIMIT 1');$stmt->execute([$customerId]);$customer=$stmt->fetch();}
    $customerName=$customer?($customer['company_name']?:$customer['contact_name']):trim((string)$_POST['customer_name']);
    $customerEmail=$customer?($customer['email']??''):trim((string)($_POST['customer_email']??''));
    $customerType=$customer?($customer['customer_type']??'b2c'):($_POST['customer_type']??'b2c');
    $billing=$customer?($customer['billing_address']??''):($_POST['billing_address']??'');
    if($customerName===''){throw new RuntimeException('Customer name is required.');}
    $descriptions=$_POST['description']??[];$productIds=$_POST['product_id']??[];$qtys=$_POST['quantity']??[];$prices=$_POST['unit_price']??[];$taxRates=$_POST['tax_rate']??[];
    $items=[];$subtotal=0;$tax=0;
    foreach($descriptions as $idx=>$description){
      $description=trim((string)$description);$productId=(int)($productIds[$idx]??0);$qty=max(0,(float)($qtys[$idx]??0));$unit=max(0,(float)($prices[$idx]??0));$taxRate=max(0,(float)($taxRates[$idx]??0));
      if($description==='' && $productId===0){continue;}
      if($qty<=0){throw new RuntimeException('Every invoice line needs a quantity greater than zero.');}
      $line=$qty*$unit;$subtotal+=$line;$tax+=$line*($taxRate/100);
      $items[]=[$productId?:null,$description?:'Invoice item',$qty,$unit,$taxRate,$line];
    }
    if(!$items){throw new RuntimeException('Add at least one invoice item.');}
    $discount=max(0,(float)($_POST['discount']??0));$shipping=max(0,(float)($_POST['shipping']??0));$total=max(0,$subtotal-$discount+$tax+$shipping);
    $companyId=(int)($_POST['company_id']??$defaultScope['company_id']);$branchId=(int)($_POST['branch_id']??$defaultScope['branch_id']);$warehouseId=(int)($_POST['warehouse_id']??$defaultScope['warehouse_id']);
    enforceScopeAllowed($pdo,$companyId,$branchId,$warehouseId,true);
    $invoiceScope=['company_id'=>$companyId,'branch_id'=>$branchId,'warehouse_id'=>$warehouseId,'location_id'=>(int)setting('default_location_id','0')];
    $invoiceNumber=nextScopedDocumentNumber($pdo,'invoice',(string)setting('invoice_prefix','INV'),$invoiceScope);
        requireInvoiceCreationAllowed($pdo);
$stmt=$pdo->prepare('INSERT INTO ' . table('invoices') . ' (company_id,branch_id,warehouse_id,invoice_number,customer_id,customer_name,customer_email,customer_type,billing_address,subtotal,discount,tax,shipping,total,amount_paid,balance_due,status,due_date,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"draft",?,?)');
    $stmt->execute([$companyId?:null,$branchId?:null,$warehouseId?:null,$invoiceNumber,$customerId?:null,$customerName,$customerEmail,$customerType,$billing,$subtotal,$discount,$tax,$shipping,$total,0,$total,$_POST['due_date']?:null,$_POST['notes']??'']);
    $invoiceId=(int)$pdo->lastInsertId();
    $stmt=$pdo->prepare('INSERT INTO ' . table('invoice_items') . ' (invoice_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
    foreach($items as $item){$stmt->execute([$invoiceId,$item[0]?'product':'service',$item[0],$item[1],$item[2],$item[3],$item[4],$item[5]]);}
    logActivity($pdo,'Sales','invoice_create','Draft invoice '.$invoiceNumber.' created for '.$customerName.'.','invoice',$invoiceId);
    $pdo->commit();flash('success','Draft invoice created. Review and approve it to reserve stock.');redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$invoiceId);
  }catch(Throwable $e){$pdo->rollBack();flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-invoice.php');}
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="row g-4" data-invoice-builder>
    <div class="col-12">
      <div class="card-admin p-4">
        <div class="erp-kicker">Operational Scope</div>
        <div class="row g-3 mt-1">
          <div class="col-lg-4"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
          <div class="col-lg-4"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
          <div class="col-lg-4"><label class="form-label">Fulfilment Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$defaultScope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
    </div><div class="col-xl-8"><div class="card-admin p-4 mb-4"><h2 class="h5">Customer & Terms</h2><div class="row g-3"><div class="col-md-6"><label class="form-label">Existing Customer</label><select class="form-select" name="customer_id"><option value="0">Manual / walk-in customer</option><?php foreach($customers as $customer): ?><option value="<?php echo (int)$customer['id']; ?>"><?php echo esc(($customer['company_name']?:$customer['contact_name']).' · '.strtoupper($customer['customer_type']).' · '.$customer['customer_code']); ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label">Manual Type</label><select class="form-select" name="customer_type"><option value="b2b">B2B</option><option value="b2c">B2C</option></select></div><div class="col-md-3"><label class="form-label">Due Date</label><input class="form-control" type="date" name="due_date"></div><div class="col-md-6"><label class="form-label">Manual Customer Name</label><input class="form-control" name="customer_name" placeholder="Required if no existing customer selected"></div><div class="col-md-6"><label class="form-label">Manual Email</label><input class="form-control" type="email" name="customer_email"></div><div class="col-12"><label class="form-label">Billing Address</label><textarea class="form-control" name="billing_address" rows="2"></textarea></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div></div></div><div class="table-wrap table-responsive"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Invoice Items</h2><button class="btn btn-outline-secondary btn-sm" data-add-invoice-row>Add Row</button></div><table class="table"><thead><tr><th style="min-width:180px">Product</th><th style="min-width:220px">Description</th><th>Qty</th><th>Unit Price</th><th>Tax %</th><th>Line</th><th></th></tr></thead><tbody data-invoice-rows><tr class="line-item-row" data-invoice-row><td><select class="form-select form-select-sm" name="product_id[]" data-product-select><option value="0">Custom item</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>" data-name="<?php echo esc(productName($product)); ?>" data-price="<?php echo esc($product['price']); ?>"><?php echo esc($product['name'].' · '.$product['sku']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="quantity[]" value="1"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><span data-line-total>0.00</span></td><td><button class="btn btn-outline-danger btn-sm" data-remove-invoice-row>&times;</button></td></tr></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4 invoice-summary"><h2 class="h5">Totals</h2><div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong data-summary-subtotal>0.00</strong></div><div class="mb-3"><label class="form-label">Discount</label><input class="form-control" type="number" step="0.01" min="0" name="discount" value="0"></div><div class="d-flex justify-content-between mb-2"><span>Calculated Tax</span><strong data-summary-tax>0.00</strong></div><div class="mb-3"><label class="form-label">Shipping / Charges</label><input class="form-control" type="number" step="0.01" min="0" name="shipping" value="0"></div><hr><div class="d-flex justify-content-between h4"><span>Total</span><strong data-summary-total>0.00</strong></div><button class="btn btn-brand w-100 mt-3">Create Draft Invoice</button></div></div></form>
<template id="invoice-row-template"><tr class="line-item-row" data-invoice-row><td><select class="form-select form-select-sm" name="product_id[]" data-product-select><option value="0">Custom item</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>" data-name="<?php echo esc(productName($product)); ?>" data-price="<?php echo esc($product['price']); ?>"><?php echo esc($product['name'].' · '.$product['sku']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="quantity[]" value="1"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><span data-line-total>0.00</span></td><td><button class="btn btn-outline-danger btn-sm" data-remove-invoice-row>&times;</button></td></tr></template>
<?php include dirname(__DIR__).'/footer.php'; ?>