<?php
$pageTitle='Create Quotation';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('quotations');
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
        $customerName=$customer?($customer['company_name']?:$customer['contact_name']):trim((string)($_POST['customer_name']??''));
        $customerEmail=$customer?($customer['email']??''):trim((string)($_POST['customer_email']??''));
        $customerType=$customer?($customer['customer_type']??'b2c'):($_POST['customer_type']??'b2c');
        $billing=$customer?($customer['billing_address']??''):($_POST['billing_address']??'');
        if($customerName===''){throw new RuntimeException('Customer name is required.');}

        $descriptions=$_POST['description']??[];$productIds=$_POST['product_id']??[];$qtys=$_POST['quantity']??[];$prices=$_POST['unit_price']??[];$taxRates=$_POST['tax_rate']??[];
        $items=[];$subtotal=0;$tax=0;
        foreach($descriptions as $idx=>$description){
            $description=trim((string)$description);$productId=(int)($productIds[$idx]??0);$qty=max(0,(float)($qtys[$idx]??0));$unit=max(0,(float)($prices[$idx]??0));$taxRate=max(0,(float)($taxRates[$idx]??0));
            if($description==='' && $productId===0){continue;}
            if($qty<=0){throw new RuntimeException('Every quotation line needs a quantity greater than zero.');}
            $line=$qty*$unit;$subtotal+=$line;$tax+=$line*($taxRate/100);
            $items[]=[$productId?:null,$description?:'Quotation item',$qty,$unit,$taxRate,$line];
        }
        if(!$items){throw new RuntimeException('Add at least one quotation item.');}

        $discount=max(0,(float)($_POST['discount']??0));$shipping=max(0,(float)($_POST['shipping']??0));$total=max(0,$subtotal-$discount+$tax+$shipping);
        $companyId=(int)($_POST['company_id']??$defaultScope['company_id']);$branchId=(int)($_POST['branch_id']??$defaultScope['branch_id']);$warehouseId=(int)($_POST['warehouse_id']??$defaultScope['warehouse_id']);
        enforceScopeAllowed($pdo,$companyId,$branchId,$warehouseId,true);
        $quoteScope=['company_id'=>$companyId,'branch_id'=>$branchId,'warehouse_id'=>$warehouseId,'location_id'=>(int)setting('default_location_id','0')];
        $quoteNumber=nextScopedDocumentNumber($pdo,'quotation',(string)setting('quotation_prefix','QTN'),$quoteScope);
        $stmt=$pdo->prepare('INSERT INTO ' . table('quotations') . ' (company_id,branch_id,warehouse_id,quotation_number,customer_id,customer_name,customer_email,customer_type,billing_address,subtotal,discount,tax,shipping,total,status,valid_until,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,"draft",?,?)');
        $stmt->execute([$companyId?:null,$branchId?:null,$warehouseId?:null,$quoteNumber,$customerId?:null,$customerName,$customerEmail,$customerType,$billing,$subtotal,$discount,$tax,$shipping,$total,$_POST['valid_until']?:null,$_POST['notes']??'']);
        $quoteId=(int)$pdo->lastInsertId();

        $stmt=$pdo->prepare('INSERT INTO ' . table('quotation_items') . ' (quotation_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
        foreach($items as $item){$stmt->execute([$quoteId,$item[0]?'product':'service',$item[0],$item[1],$item[2],$item[3],$item[4],$item[5]]);}
        logActivity($pdo,'Quotation','create','Quotation '.$quoteNumber.' created for '.$customerName.'.','quotation',$quoteId);
        $pdo->commit();
        flash('success','Draft quotation created. Review, send, or accept it before converting to invoice.');
        redirect(ADMIN_URL.'/erp/view-quotation.php?id='.$quoteId);
    }catch(Throwable $e){
        $pdo->rollBack();
        flash('error',$e->getMessage());
        redirect(ADMIN_URL.'/erp/create-quotation.php');
    }
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="row g-4" data-invoice-builder>
    <div class="col-12">
      <div class="card-admin p-4">
        <div class="erp-kicker">Quotation Scope</div>
        <div class="row g-3 mt-1">
          <div class="col-lg-4"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
          <div class="col-lg-4"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
          <div class="col-lg-4"><label class="form-label">Future Fulfilment Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$defaultScope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
    </div>
    <div class="col-xl-8">
        <div class="card-admin p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Customer & Commercial Terms</h2><span class="pill pill-soft">B2B / B2C sales proposal</span></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Existing Customer</label><select class="form-select" name="customer_id"><option value="0">Manual / prospect customer</option><?php foreach($customers as $customer): ?><option value="<?php echo (int)$customer['id']; ?>"><?php echo esc(($customer['company_name']?:$customer['contact_name']).' · '.strtoupper($customer['customer_type']).' · '.$customer['customer_code']); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Manual Type</label><select class="form-select" name="customer_type"><option value="b2b">B2B</option><option value="b2c">B2C</option></select></div>
                <div class="col-md-3"><label class="form-label">Valid Until</label><input class="form-control" type="date" name="valid_until" value="<?php echo esc(date('Y-m-d',strtotime('+14 days'))); ?>"></div>
                <div class="col-md-6"><label class="form-label">Manual Customer Name</label><input class="form-control" name="customer_name" placeholder="Required if no existing customer selected"></div>
                <div class="col-md-6"><label class="form-label">Manual Email</label><input class="form-control" type="email" name="customer_email"></div>
                <div class="col-12"><label class="form-label">Billing / Proposal Address</label><textarea class="form-control" name="billing_address" rows="2"></textarea></div>
                <div class="col-12"><label class="form-label">Commercial Notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Scope, delivery, validity, support, or sales remarks."></textarea></div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Quotation Items</h2><button class="btn btn-outline-secondary btn-sm" data-add-invoice-row>Add Row</button></div>
            <table class="table"><thead><tr><th style="min-width:180px">Product</th><th style="min-width:220px">Description</th><th>Qty</th><th>Unit Price</th><th>Tax %</th><th>Line</th><th></th></tr></thead>
            <tbody data-invoice-rows><tr class="line-item-row" data-invoice-row><td><select class="form-select form-select-sm" name="product_id[]" data-product-select><option value="0">Custom item</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>" data-name="<?php echo esc(productName($product)); ?>" data-price="<?php echo esc($product['price']); ?>"><?php echo esc($product['name'].' · '.$product['sku']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="quantity[]" value="1"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><span data-line-total>0.00</span></td><td><button class="btn btn-outline-danger btn-sm" data-remove-invoice-row>&times;</button></td></tr></tbody></table>
        </div>
    </div>
    <div class="col-xl-4"><div class="card-admin p-4 invoice-summary"><h2 class="h5">Quotation Summary</h2><div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong data-summary-subtotal>0.00</strong></div><div class="mb-3"><label class="form-label">Discount</label><input class="form-control" type="number" step="0.01" min="0" name="discount" value="0"></div><div class="d-flex justify-content-between mb-2"><span>Calculated Tax</span><strong data-summary-tax>0.00</strong></div><div class="mb-3"><label class="form-label">Shipping / Charges</label><input class="form-control" type="number" step="0.01" min="0" name="shipping" value="0"></div><hr><div class="d-flex justify-content-between h4"><span>Total</span><strong data-summary-total>0.00</strong></div><button class="btn btn-brand w-100 mt-3">Create Draft Quotation</button></div></div>
</form>
<template id="invoice-row-template"><tr class="line-item-row" data-invoice-row><td><select class="form-select form-select-sm" name="product_id[]" data-product-select><option value="0">Custom item</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>" data-name="<?php echo esc(productName($product)); ?>" data-price="<?php echo esc($product['price']); ?>"><?php echo esc($product['name'].' · '.$product['sku']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="quantity[]" value="1"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><span data-line-total>0.00</span></td><td><button class="btn btn-outline-danger btn-sm" data-remove-invoice-row>&times;</button></td></tr></template>
<?php include dirname(__DIR__).'/footer.php'; ?>