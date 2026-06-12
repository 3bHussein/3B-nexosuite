<?php
$pageTitle='Procurement Tenders';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('tender_management');
$pdo=getDB();
$scope=operationalScope($pdo);$scopeOptions=scopeSelectOptions($pdo);
$products=$pdo->query('SELECT id,sku,name,cost_price FROM '.table('products').' WHERE active=1 ORDER BY name ASC LIMIT 400')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->beginTransaction();
  try{
    $scope=[
      'company_id'=>(int)($_POST['company_id']??$scope['company_id']),
      'branch_id'=>(int)($_POST['branch_id']??$scope['branch_id']),
      'warehouse_id'=>(int)setting('default_warehouse_id','0'),
      'location_id'=>(int)setting('default_location_id','0'),
    ];
    enforceScopeAllowed($pdo,$scope['company_id'],$scope['branch_id'],0,true);
    $items=[];$estimated=0.0;
    foreach(($_POST['item_product_id']??[]) as $idx=>$productIdRaw){
      $productId=(int)$productIdRaw;$description=trim((string)($_POST['item_description'][$idx]??''));$qty=max(0,(float)($_POST['item_quantity'][$idx]??0));$target=max(0,(float)($_POST['item_target_cost'][$idx]??0));
      if($qty<=0 || ($productId<=0 && $description==='')){continue;}
      if($description===''){$find=$pdo->prepare('SELECT name FROM '.table('products').' WHERE id=? LIMIT 1');$find->execute([$productId]);$description=(string)$find->fetchColumn();}
      $estimated += $qty*$target;
      $items[]=['product_id'=>$productId?:null,'description'=>$description,'quantity'=>$qty,'target_unit_cost'=>$target];
    }
    if(!$items){throw new RuntimeException('Add at least one tender line.');}
    $number=nextScopedDocumentNumber($pdo,'procurement_tender',setting('tender_prefix','TND'),$scope);
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('procurement_tenders').' (company_id,branch_id,tender_number,title,description,publish_date,due_date,status,estimated_value,notes,created_by) VALUES (?,?,?,?,?,?,?,"draft",?,?,?)');
    $stmt->execute([$scope['company_id']?:null,$scope['branch_id']?:null,$number,trim((string)($_POST['title']??'Procurement Tender')),trim((string)($_POST['description']??'')),trim((string)($_POST['publish_date']??date('Y-m-d')))?:date('Y-m-d'),trim((string)($_POST['due_date']??''))?:null,round($estimated,2),trim((string)($_POST['notes']??'')),(int)($user['id']??0)?:null]);
    $tenderId=(int)$pdo->lastInsertId();
    $lineStmt=$pdo->prepare('INSERT INTO '.table('procurement_tender_items').' (procurement_tender_id,product_id,description,quantity,target_unit_cost) VALUES (?,?,?,?,?)');
    foreach($items as $item){$lineStmt->execute([$tenderId,$item['product_id'],$item['description'],$item['quantity'],$item['target_unit_cost']]);}
    logActivity($pdo,'Tender','tender_created','Procurement tender '.$number.' created.','procurement_tender',$tenderId);
    $pdo->commit();flash('success','Tender created.');
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/procurement-tenders.php');
}
$rows=$pdo->query('SELECT t.*,s.company_name awarded_supplier FROM '.table('procurement_tenders').' t LEFT JOIN '.table('suppliers').' s ON s.id=t.awarded_supplier_id ORDER BY t.created_at DESC,t.id DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Tender Management</div><h2 class="h4 mb-1">Procurement Tenders</h2><p class="text-secondary mb-0">Create formal tender records for larger procurement events and future RFQ conversion.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/rfqs.php">RFQ Center</a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Create Tender</h2><label class="form-label">Company</label><select class="form-select mb-2" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$scope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select><label class="form-label">Branch</label><select class="form-select mb-2" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$scope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select><label class="form-label">Title</label><input class="form-control mb-2" name="title" value="Procurement Tender"><label class="form-label">Publish Date</label><input class="form-control mb-2" type="date" name="publish_date" value="<?php echo esc(date('Y-m-d')); ?>"><label class="form-label">Due Date</label><input class="form-control mb-2" type="date" name="due_date"><label class="form-label">Description</label><textarea class="form-control mb-3" name="description" rows="3"></textarea><button class="btn btn-brand w-100">Create Tender</button></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Tender Lines</h2><table class="table align-middle"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Target Cost</th></tr></thead><tbody><?php for($i=0;$i<4;$i++): ?><tr><td><select class="form-select form-select-sm" name="item_product_id[]"><option value="0">Custom</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc(($product['sku']?:'').' · '.$product['name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control form-control-sm" name="item_description[]"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_quantity[]" value="<?php echo $i===0?'1':'0'; ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="item_target_cost[]" value="0"></td></tr><?php endfor; ?></tbody></table></div></form><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Tender Register</div><h2 class="h5 mb-0">Tender Events</h2></div></div><table class="table align-middle"><thead><tr><th>Tender</th><th>Due</th><th>Estimated</th><th>Status</th><th>Awarded</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['tender_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['title']); ?></div></td><td><?php echo esc($row['due_date']?:'—'); ?></td><td><?php echo money($row['estimated_value']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td><?php echo esc($row['awarded_supplier']?:'—'); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-secondary">No tenders created yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>