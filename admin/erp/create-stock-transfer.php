<?php
$pageTitle='Create Stock Transfer';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('stock_transfers');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);
$products=$pdo->query('SELECT id,sku,name,stock FROM '.table('products').' WHERE active=1 ORDER BY name ASC')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $pdo->beginTransaction();
    try{
        $fromCompany=(int)($_POST['from_company_id']??$defaultScope['company_id']);
        $fromBranch=(int)($_POST['from_branch_id']??$defaultScope['branch_id']);
        $fromWarehouse=(int)($_POST['from_warehouse_id']??$defaultScope['warehouse_id']);
        $fromLocation=(int)($_POST['from_location_id']??$defaultScope['location_id']);
        $toCompany=(int)($_POST['to_company_id']??$defaultScope['company_id']);
        $toBranch=(int)($_POST['to_branch_id']??$defaultScope['branch_id']);
        $toWarehouse=(int)($_POST['to_warehouse_id']??$defaultScope['warehouse_id']);
        $toLocation=(int)($_POST['to_location_id']??$defaultScope['location_id']);

        if($fromWarehouse<=0||$toWarehouse<=0){throw new RuntimeException('Source and destination warehouses are required.');}
        if($fromWarehouse===$toWarehouse && $fromLocation===$toLocation){throw new RuntimeException('Source and destination stock locations must be different.');}
        enforceScopeAllowed($pdo,$fromCompany,$fromBranch,$fromWarehouse,true);
        enforceScopeAllowed($pdo,$toCompany,$toBranch,$toWarehouse,true);

        if($fromLocation>0){$check=$pdo->prepare('SELECT COUNT(*) FROM '.table('warehouse_locations').' WHERE id=? AND warehouse_id=?');$check->execute([$fromLocation,$fromWarehouse]);if((int)$check->fetchColumn()===0){throw new RuntimeException('Selected source location does not belong to the source warehouse.');}}
        if($toLocation>0){$check=$pdo->prepare('SELECT COUNT(*) FROM '.table('warehouse_locations').' WHERE id=? AND warehouse_id=?');$check->execute([$toLocation,$toWarehouse]);if((int)$check->fetchColumn()===0){throw new RuntimeException('Selected destination location does not belong to the destination warehouse.');}}

        $productIds=$_POST['product_id']??[];$qtys=$_POST['quantity']??[];$notes=$_POST['item_notes']??[];
        $items=[];
        foreach($productIds as $i=>$productId){
            $productId=(int)$productId;$qty=max(0,(float)($qtys[$i]??0));
            if($productId<=0&&$qty<=0){continue;}
            if($productId<=0||$qty<=0){throw new RuntimeException('Every stock transfer line needs a valid product and quantity.');}
            $items[]=[$productId,$qty,trim((string)($notes[$i]??''))];
        }
        if(!$items){throw new RuntimeException('Add at least one stock transfer item.');}

        $transferScope=['company_id'=>$fromCompany,'branch_id'=>$fromBranch,'warehouse_id'=>$fromWarehouse,'location_id'=>$fromLocation];
        $transferNumber=nextScopedDocumentNumber($pdo,'stock_transfer','TRF',$transferScope);
        $user=currentUser();
        $stmt=$pdo->prepare('INSERT INTO '.table('stock_transfers').' (transfer_number,from_company_id,from_branch_id,from_warehouse_id,from_location_id,to_company_id,to_branch_id,to_warehouse_id,to_location_id,status,requested_by,notes) VALUES (?,?,?,?,?,?,?,?,?,"draft",?,?)');
        $stmt->execute([$transferNumber,$fromCompany?:null,$fromBranch?:null,$fromWarehouse?:null,$fromLocation?:null,$toCompany?:null,$toBranch?:null,$toWarehouse?:null,$toLocation?:null,(int)($user['id']??0)?:null,trim((string)($_POST['notes']??''))]);
        $transferId=(int)$pdo->lastInsertId();

        $itemStmt=$pdo->prepare('INSERT INTO '.table('stock_transfer_items').' (stock_transfer_id,product_id,quantity,received_quantity,notes) VALUES (?,?,?,0,?)');
        foreach($items as $item){$itemStmt->execute([$transferId,$item[0],$item[1],$item[2]]);}
        logActivity($pdo,'Inventory','stock_transfer_created','Stock transfer '.$transferNumber.' created as draft.','stock_transfer',$transferId);
        $pdo->commit();
        flash('success','Draft stock transfer created. Submit it for approval from the transfer view.');
        redirect(ADMIN_URL.'/erp/view-stock-transfer.php?id='.$transferId);
    }catch(Throwable $e){
        if($pdo->inTransaction()){$pdo->rollBack();}
        flash('error',$e->getMessage());
        redirect(ADMIN_URL.'/erp/create-stock-transfer.php');
    }
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="row g-4">
  <div class="col-xl-8">
    <div class="card-admin p-4 mb-4">
      <div class="erp-kicker">Transfer Route</div><h2 class="h5 mb-3">Source and Destination</h2>
      <div class="row g-3">
        <div class="col-lg-3"><label class="form-label">From Company</label><select class="form-select" name="from_company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">From Branch</label><select class="form-select" name="from_branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">From Warehouse</label><select class="form-select" name="from_warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$defaultScope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">From Location</label><select class="form-select" name="from_location_id"><?php foreach($scopeOptions['locations'] as $location): ?><option value="<?php echo (int)$location['id']; ?>" <?php echo (int)$location['id']===(int)$defaultScope['location_id']?'selected':''; ?>><?php echo esc($location['location_code'].' · '.$location['location_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">To Company</label><select class="form-select" name="to_company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>"><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">To Branch</label><select class="form-select" name="to_branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>"><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">To Warehouse</label><select class="form-select" name="to_warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>"><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-lg-3"><label class="form-label">To Location</label><select class="form-select" name="to_location_id"><?php foreach($scopeOptions['locations'] as $location): ?><option value="<?php echo (int)$location['id']; ?>"><?php echo esc($location['location_code'].' · '.$location['location_name']); ?></option><?php endforeach; ?></select></div>
      </div>
    </div>
    <div class="table-wrap table-responsive">
      <div class="table-toolbar"><div><div class="erp-kicker">Transfer Lines</div><h2 class="h5 mb-0">Products to move</h2></div></div>
      <table class="table align-middle"><thead><tr><th style="min-width:260px">Product</th><th style="width:140px">Quantity</th><th>Line Notes</th></tr></thead><tbody><?php for($i=0;$i<5;$i++): ?><tr><td><select class="form-select" name="product_id[]"><option value="0">Select product</option><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc($product['sku'].' · '.$product['name'].' · Total '.$product['stock']); ?></option><?php endforeach; ?></select></td><td><input class="form-control" type="number" step="0.01" min="0" name="quantity[]" value="0"></td><td><input class="form-control" name="item_notes[]" placeholder="Optional line instruction"></td></tr><?php endfor; ?></tbody></table>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card-admin p-4">
      <div class="erp-kicker">Control Note</div><h2 class="h5 mb-3">Transfer Notes</h2>
      <textarea class="form-control mb-3" name="notes" rows="8" placeholder="Reason for transfer, vehicle, branch need, workshop replenishment..."></textarea>
      <button class="btn btn-brand btn-lg w-100">Create Draft Transfer</button>
      <p class="small text-secondary mt-3 mb-0">Draft → Submit → Approve → Dispatch → Receive. Stock leaves the source only at dispatch and enters the destination only at receipt.</p>
    </div>
  </div>
</form>
<?php include dirname(__DIR__).'/footer.php'; ?>