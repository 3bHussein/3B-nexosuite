<?php
$pageTitle='Create Purchase Requisition';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('purchase_requisitions');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$scope=operationalScope($pdo);
$products=$pdo->query('SELECT id,sku,name,cost_price,average_cost FROM '.table('products').' WHERE active=1 ORDER BY name ASC LIMIT 400')->fetchAll();

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
    $items=[];$subtotal=0.0;$tax=0.0;
    foreach(($_POST['item_product_id']??[]) as $idx=>$productIdRaw){
      $productId=(int)$productIdRaw;
      $description=trim((string)(($_POST['item_description'][$idx]??'')));
      $qty=max(0,(float)(($_POST['item_quantity'][$idx]??0)));
      $cost=max(0,(float)(($_POST['item_cost'][$idx]??0)));
      $taxRate=max(0,(float)(($_POST['item_tax_rate'][$idx]??0)));
      if($qty<=0 || ($productId<=0 && $description==='')){continue;}
      if($description===''){
        $find=$pdo->prepare('SELECT name FROM '.table('products').' WHERE id=? LIMIT 1');$find->execute([$productId]);$description=(string)$find->fetchColumn();
      }
      $line=round($qty*$cost,2);$lineTax=round($line*$taxRate/100,2);
      $subtotal+=$line;$tax+=$lineTax;
      $items[]=['product_id'=>$productId?:null,'description'=>$description,'quantity'=>$qty,'estimated_unit_cost'=>$cost,'tax_rate'=>$taxRate,'line_total'=>$line];
    }
    if(!$items){throw new RuntimeException('Add at least one requisition line.');}
    $subtotal=round($subtotal,2);$tax=round($tax,2);$total=round($subtotal+$tax,2);
    $number=nextScopedDocumentNumber($pdo,'purchase_requisition','REQ',$scope);
    $user=currentUser();$userId=(int)($user['id']??0)?:null;
    $stmt=$pdo->prepare('INSERT INTO '.table('purchase_requisitions').' (company_id,branch_id,warehouse_id,requisition_number,requested_by_user_id,department,required_date,justification,subtotal,tax,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,"draft",?)');
    $stmt->execute([$scope['company_id']?:null,$scope['branch_id']?:null,$scope['warehouse_id']?:null,$number,$userId,trim((string)($_POST['department']??'')),trim((string)($_POST['required_date']??''))?:null,trim((string)($_POST['justification']??'')),$subtotal,$tax,$total,trim((string)($_POST['notes']??''))]);
    $id=(int)$pdo->lastInsertId();
    $lineStmt=$pdo->prepare('INSERT INTO '.table('purchase_requisition_items').' (purchase_requisition_id,product_id,description,quantity,estimated_unit_cost,tax_rate,line_total) VALUES (?,?,?,?,?,?,?)');
    foreach($items as $item){$lineStmt->execute([$id,$item['product_id'],$item['description'],$item['quantity'],$item['estimated_unit_cost'],$item['tax_rate'],$item['line_total']]);}
    logActivity($pdo,'Procurement','purchase_requisition_created','Purchase requisition '.$number.' created.','purchase_requisition',$id);
    $pdo->commit();flash('success','Purchase requisition created.');redirect(ADMIN_URL.'/erp/view-purchase-requisition.php?id='.$id);
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
    redirect(ADMIN_URL.'/erp/create-purchase-requisition.php');
  }
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Procurement Intake</div><h2 class="h4 mb-1">Create Purchase Requisition</h2><p class="text-secondary mb-0">Capture operational demand before vendor sourcing and purchase order creation.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/purchase-requisitions.php">Back</a></div>
<form method="post" class="row g-4">
<div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">Request Scope</h2><div class="row g-3">
<div class="col-12"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$scope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$scope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$scope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Department</label><input class="form-control" name="department" placeholder="Workshop, IT, Operations..."></div>
<div class="col-12"><label class="form-label">Required Date</label><input class="form-control" type="date" name="required_date"></div>
<div class="col-12"><label class="form-label">Justification</label><textarea class="form-control" rows="3" name="justification"></textarea></div>
<div class="col-12"><label class="form-label">Internal Notes</label><textarea class="form-control" rows="3" name="notes"></textarea></div>
</div></div></div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Requested Lines</div><h2 class="h5 mb-0">Items & Estimated Cost</h2></div></div><table class="table align-middle"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Est. Cost</th><th>Tax %</th></tr></thead><tbody><?php for($i=0;$i<6;$i++): ?><tr><td><select class="form-select form-select-sm" name="item_product_id[]"><option value="0">Custom line</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc(($product['sku']?:'').' · '.$product['name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="item_description[]" placeholder="Item / service"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_quantity[]" value="<?php echo $i===0?'1':'0'; ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_cost[]" value="0"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_tax_rate[]" value="<?php echo esc(setting('tax_rate','5')); ?>"></td></tr><?php endfor; ?></tbody></table></div><div class="card-admin p-4 mt-4 d-flex flex-wrap justify-content-between align-items-center gap-3"><div><strong>Approval routing:</strong> requisitions above configured thresholds will enter the Approval Center before PO conversion.</div><button class="btn btn-brand">Create Requisition</button></div></div>
</form>
<?php include dirname(__DIR__).'/footer.php'; ?>