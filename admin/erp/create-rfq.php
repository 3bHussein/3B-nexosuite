<?php
$pageTitle='Create RFQ';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('rfq_management');
$pdo=getDB();
$scope=operationalScope($pdo);$scopeOptions=scopeSelectOptions($pdo);
$products=$pdo->query('SELECT id,sku,name,cost_price,average_cost FROM '.table('products').' WHERE active=1 ORDER BY name ASC LIMIT 500')->fetchAll();
$suppliers=$pdo->query('SELECT id,supplier_code,company_name FROM '.table('suppliers').' WHERE status="active" ORDER BY company_name ASC LIMIT 500')->fetchAll();
$requisitions=$pdo->query('SELECT id,requisition_number,total,status FROM '.table('purchase_requisitions').' WHERE status="approved" AND converted_po_id IS NULL ORDER BY created_at DESC LIMIT 100')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->beginTransaction();
  try{
    $sourceReq=(int)($_POST['source_requisition_id']??0);
    $supplierIds=array_map('intval',$_POST['supplier_ids']??[]);
    if($sourceReq>0 && ($_POST['mode']??'')==='from_requisition'){
      $rfqId=createRfqFromRequisition($pdo,$sourceReq,$supplierIds);
      $pdo->commit();flash('success','RFQ created from requisition.');redirect(ADMIN_URL.'/erp/view-rfq.php?id='.$rfqId);
    }
    $scope=[
      'company_id'=>(int)($_POST['company_id']??$scope['company_id']),
      'branch_id'=>(int)($_POST['branch_id']??$scope['branch_id']),
      'warehouse_id'=>(int)($_POST['warehouse_id']??$scope['warehouse_id']),
      'location_id'=>(int)setting('default_location_id','0'),
    ];
    enforceScopeAllowed($pdo,$scope['company_id'],$scope['branch_id'],$scope['warehouse_id'],true);
    $items=[];
    foreach(($_POST['item_product_id']??[]) as $idx=>$productIdRaw){
      $productId=(int)$productIdRaw;$description=trim((string)($_POST['item_description'][$idx]??''));$qty=max(0,(float)($_POST['item_quantity'][$idx]??0));$target=max(0,(float)($_POST['item_target_cost'][$idx]??0));
      if($qty<=0 || ($productId<=0 && $description==='')){continue;}
      if($description===''){$find=$pdo->prepare('SELECT name FROM '.table('products').' WHERE id=? LIMIT 1');$find->execute([$productId]);$description=(string)$find->fetchColumn();}
      $items[]=['product_id'=>$productId?:null,'description'=>$description,'quantity'=>$qty,'target_unit_cost'=>$target];
    }
    if(!$items){throw new RuntimeException('Add at least one RFQ line.');}
    $number=nextScopedDocumentNumber($pdo,'rfq',setting('rfq_prefix','RFQ'),$scope);
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('rfqs').' (company_id,branch_id,warehouse_id,rfq_number,title,description,request_date,due_date,status,created_by,notes) VALUES (?,?,?,?,?,?,?,?,"draft",?,?)');
    $stmt->execute([$scope['company_id']?:null,$scope['branch_id']?:null,$scope['warehouse_id']?:null,$number,trim((string)($_POST['title']??'RFQ')),trim((string)($_POST['description']??'')),trim((string)($_POST['request_date']??date('Y-m-d')))?:date('Y-m-d'),trim((string)($_POST['due_date']??''))?:null,(int)($user['id']??0)?:null,trim((string)($_POST['notes']??''))]);
    $rfqId=(int)$pdo->lastInsertId();
    $lineStmt=$pdo->prepare('INSERT INTO '.table('rfq_items').' (rfq_id,product_id,description,quantity,target_unit_cost,required_date,notes) VALUES (?,?,?,?,?,?,?)');
    foreach($items as $item){$lineStmt->execute([$rfqId,$item['product_id'],$item['description'],$item['quantity'],$item['target_unit_cost'],trim((string)($_POST['due_date']??''))?:null,'']);}
    foreach($supplierIds as $supplierId){if($supplierId>0){inviteSupplierToRfq($pdo,$rfqId,$supplierId);}}
    logActivity($pdo,'RFQ','rfq_created','RFQ '.$number.' created.','rfq',$rfqId);
    $pdo->commit();flash('success','RFQ created.');redirect(ADMIN_URL.'/erp/view-rfq.php?id='.$rfqId);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-rfq.php');}
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Create Sourcing Event</div><h2 class="h4 mb-1">Create RFQ</h2><p class="text-secondary mb-0">Create a direct RFQ or generate one from an approved purchase requisition.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/rfqs.php">Back</a></div>
<form method="post" class="row g-4">
<div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">RFQ Header</h2><div class="row g-3">
<div class="col-12"><label class="form-label">Mode</label><select class="form-select" name="mode"><option value="direct">Direct RFQ</option><option value="from_requisition">From Approved Requisition</option></select></div>
<div class="col-12"><label class="form-label">Approved Requisition</label><select class="form-select" name="source_requisition_id"><option value="0">None</option><?php foreach($requisitions as $req): ?><option value="<?php echo (int)$req['id']; ?>"><?php echo esc($req['requisition_number'].' · '.money($req['total'])); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$scope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$scope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$scope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Title</label><input class="form-control" name="title" value="Supplier RFQ"></div>
<div class="col-6"><label class="form-label">Request Date</label><input class="form-control" type="date" name="request_date" value="<?php echo esc(date('Y-m-d')); ?>"></div>
<div class="col-6"><label class="form-label">Due Date</label><input class="form-control" type="date" name="due_date"></div>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
<div class="col-12"><label class="form-label">Invite Suppliers</label><select class="form-select" name="supplier_ids[]" multiple size="7"><?php foreach($suppliers as $supplier): ?><option value="<?php echo (int)$supplier['id']; ?>"><?php echo esc($supplier['supplier_code'].' · '.$supplier['company_name']); ?></option><?php endforeach; ?></select><div class="small text-secondary">Hold Ctrl to select multiple suppliers.</div></div>
</div></div></div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">RFQ Lines</div><h2 class="h5 mb-0">Requested Products / Services</h2></div></div><table class="table align-middle"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Target Cost</th></tr></thead><tbody><?php for($i=0;$i<6;$i++): ?><tr><td><select class="form-select form-select-sm" name="item_product_id[]"><option value="0">Custom</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc(($product['sku']?:'').' · '.$product['name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="item_description[]" placeholder="Item description"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_quantity[]" value="<?php echo $i===0?'1':'0'; ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_target_cost[]" value="0"></td></tr><?php endfor; ?></tbody></table></div><div class="card-admin p-4 mt-4 d-flex justify-content-between align-items-center"><div><strong>RFQ rule:</strong> recommended minimum supplier count is <?php echo esc(setting('rfq_min_supplier_count','3')); ?>.</div><button class="btn btn-brand">Create RFQ</button></div></div>
</form>
<?php include dirname(__DIR__).'/footer.php'; ?>