<?php
$pageTitle='Create Purchase Order';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('purchase_orders');
$pdo=getDB();
$suppliers=$pdo->query('SELECT * FROM ' . table('suppliers') . ' WHERE status="active" ORDER BY company_name')->fetchAll();
$products=$pdo->query('SELECT id,name,sku FROM ' . table('products') . ' WHERE active=1 ORDER BY name')->fetchAll();
$scopeOptions=scopeSelectOptions($pdo);$defaultScope=operationalScope($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $pdo->beginTransaction();
    try{
        $supplierId=(int)($_POST['supplier_id']??0);
        $stmt=$pdo->prepare('SELECT * FROM ' . table('suppliers') . ' WHERE id=? AND status="active" LIMIT 1');
        $stmt->execute([$supplierId]);
        $supplier=$stmt->fetch();
        if(!$supplier){throw new RuntimeException('Select an active supplier.');}
        $descriptions=$_POST['description']??[];$productIds=$_POST['product_id']??[];$qtys=$_POST['quantity']??[];$costs=$_POST['unit_price']??[];$taxRates=$_POST['tax_rate']??[];
        $items=[];$subtotal=0;$tax=0;
        foreach($descriptions as $idx=>$description){
            $description=trim((string)$description);$productId=(int)($productIds[$idx]??0);$qty=max(0,(float)($qtys[$idx]??0));$unit=max(0,(float)($costs[$idx]??0));$taxRate=max(0,(float)($taxRates[$idx]??0));
            if($description==='' && $productId===0){continue;}
            if($qty<=0){throw new RuntimeException('Every purchase line needs quantity greater than zero.');}
            $line=$qty*$unit;$subtotal+=$line;$tax+=$line*($taxRate/100);
            $items[]=[$productId?:null,$description?:'Purchase item',$qty,$unit,$taxRate,$line];
        }
        if(!$items){throw new RuntimeException('Add at least one purchase order item.');}
        $shipping=max(0,(float)($_POST['shipping']??0));$total=max(0,$subtotal+$tax+$shipping);
        $companyId=(int)($_POST['company_id']??$defaultScope['company_id']);$branchId=(int)($_POST['branch_id']??$defaultScope['branch_id']);$warehouseId=(int)($_POST['warehouse_id']??$defaultScope['warehouse_id']);
        enforceScopeAllowed($pdo,$companyId,$branchId,$warehouseId,true);
        $poScope=['company_id'=>$companyId,'branch_id'=>$branchId,'warehouse_id'=>$warehouseId,'location_id'=>(int)setting('default_location_id','0')];
        $poNumber=nextScopedDocumentNumber($pdo,'purchase_order',(string)setting('purchase_order_prefix','PO'),$poScope);
        $stmt=$pdo->prepare('INSERT INTO ' . table('purchase_orders') . ' (company_id,branch_id,warehouse_id,po_number,supplier_id,supplier_name,order_date,expected_date,subtotal,tax,shipping,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"draft",?)');
        $stmt->execute([$companyId?:null,$branchId?:null,$warehouseId?:null,$poNumber,$supplierId,$supplier['company_name'],$_POST['order_date']?:date('Y-m-d'),$_POST['expected_date']?:null,$subtotal,$tax,$shipping,$total,$_POST['notes']??'']);
        $poId=(int)$pdo->lastInsertId();
        $stmt=$pdo->prepare('INSERT INTO ' . table('purchase_order_items') . ' (purchase_order_id,product_id,description,quantity,received_quantity,unit_cost,tax_rate,line_total) VALUES (?,?,?,?,0,?,?,?)');
        foreach($items as $item){$stmt->execute([$poId,$item[0],$item[1],$item[2],$item[3],$item[4],$item[5]]);}
        logActivity($pdo,'Procurement','purchase_order_create','Purchase order '.$poNumber.' created for '.$supplier['company_name'].'.','purchase_order',$poId);
        $pdo->commit();
        flash('success','Draft purchase order created. Review and approve it before receiving stock.');
        redirect(ADMIN_URL.'/erp/view-purchase-order.php?id='.$poId);
    }catch(Throwable $e){
        $pdo->rollBack();
        flash('error',$e->getMessage());
        redirect(ADMIN_URL.'/erp/create-purchase-order.php');
    }
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="row g-4" data-invoice-builder>
    <div class="col-12">
      <div class="card-admin p-4">
        <div class="erp-kicker">Procurement Scope</div>
        <div class="row g-3 mt-1">
          <div class="col-lg-4"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
          <div class="col-lg-4"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
          <div class="col-lg-4"><label class="form-label">Receiving Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$defaultScope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
    </div>
    <div class="col-xl-8">
        <div class="card-admin p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Supplier & Delivery Terms</h2><span class="pill pill-soft">Procurement workflow</span></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Supplier</label><select class="form-select" name="supplier_id" required><option value="">Select supplier</option><?php foreach($suppliers as $supplier): ?><option value="<?php echo (int)$supplier['id']; ?>"><?php echo esc($supplier['company_name'].' · '.$supplier['supplier_code']); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Order Date</label><input class="form-control" type="date" name="order_date" value="<?php echo esc(date('Y-m-d')); ?>"></div>
                <div class="col-md-3"><label class="form-label">Expected Date</label><input class="form-control" type="date" name="expected_date" value="<?php echo esc(date('Y-m-d',strtotime('+10 days'))); ?>"></div>
                <div class="col-12"><label class="form-label">Procurement Notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Delivery commitments, payment terms, supplier remarks."></textarea></div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Purchase Items</h2><button class="btn btn-outline-secondary btn-sm" data-add-invoice-row>Add Row</button></div>
            <table class="table"><thead><tr><th style="min-width:180px">Product</th><th style="min-width:220px">Description</th><th>Qty</th><th>Unit Cost</th><th>Tax %</th><th>Line</th><th></th></tr></thead>
            <tbody data-invoice-rows><tr class="line-item-row" data-invoice-row><td><select class="form-select form-select-sm" name="product_id[]" data-product-select><option value="0">Custom item</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>" data-name="<?php echo esc(productName($product)); ?>"><?php echo esc($product['name'].' · '.$product['sku']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="quantity[]" value="1"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><span data-line-total>0.00</span></td><td><button class="btn btn-outline-danger btn-sm" data-remove-invoice-row>&times;</button></td></tr></tbody></table>
        </div>
    </div>
    <div class="col-xl-4"><div class="card-admin p-4 invoice-summary"><h2 class="h5">Purchase Summary</h2><div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong data-summary-subtotal>0.00</strong></div><div class="d-flex justify-content-between mb-2"><span>Calculated Tax</span><strong data-summary-tax>0.00</strong></div><div class="mb-3"><label class="form-label">Freight / Charges</label><input class="form-control" type="number" step="0.01" min="0" name="shipping" value="0"></div><hr><div class="d-flex justify-content-between h4"><span>Total</span><strong data-summary-total>0.00</strong></div><button class="btn btn-brand w-100 mt-3">Create Draft Purchase Order</button></div></div>
</form>
<template id="invoice-row-template"><tr class="line-item-row" data-invoice-row><td><select class="form-select form-select-sm" name="product_id[]" data-product-select><option value="0">Custom item</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>" data-name="<?php echo esc(productName($product)); ?>"><?php echo esc($product['name'].' · '.$product['sku']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="description[]" placeholder="Description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0.01" name="quantity[]" value="1"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[]" value="5"></td><td><span data-line-total>0.00</span></td><td><button class="btn btn-outline-danger btn-sm" data-remove-invoice-row>&times;</button></td></tr></template>
<?php include dirname(__DIR__).'/footer.php'; ?>