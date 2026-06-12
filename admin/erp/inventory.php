<?php
$pageTitle='Inventory & Warehouse Stock Controls';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('inventory');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $pdo->beginTransaction();
        if(($_POST['form_type']??'')==='stock_movement'){
            $productId=(int)($_POST['product_id']??0);
            $movement=(string)($_POST['movement_type']??'in');
            $qty=max(0,(float)($_POST['quantity']??0));
            if($productId<=0||$qty<=0){throw new RuntimeException('Product and quantity are required.');}
            $scope=[
              'company_id'=>(int)($_POST['company_id']??$defaultScope['company_id']),
              'branch_id'=>(int)($_POST['branch_id']??$defaultScope['branch_id']),
              'warehouse_id'=>(int)($_POST['warehouse_id']??$defaultScope['warehouse_id']),
              'location_id'=>(int)($_POST['location_id']??$defaultScope['location_id']),
            ];
            enforceScopeAllowed($pdo,(int)$scope['company_id'],(int)$scope['branch_id'],(int)$scope['warehouse_id'],true);
            $delta=$movement==='out' ? -$qty : $qty;
            $unitCost=$movement==='in' ? max(0,(float)($_POST['unit_cost']??0)) : null;
            if($delta<0 && warehouseAvailableQuantity($pdo,$productId,$scope)<abs($delta)){throw new RuntimeException('Stock out quantity exceeds warehouse availability.');}
            adjustWarehouseStock($pdo,$productId,$delta,$scope,'manual_inventory',0,(string)($_POST['notes']??''),$unitCost);
            logActivity($pdo,'Inventory','manual_stock_movement','Manual stock movement posted for product #'.$productId.' with delta '.$delta.'.','product',$productId);
            $pdo->commit();
            flash('success','Warehouse stock movement posted and consolidated product stock refreshed.');
        } else {
            $stockId=(int)($_POST['stock_id']??0);
            $scopeStmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('warehouse_stock').' WHERE id=? LIMIT 1 FOR UPDATE');$scopeStmt->execute([$stockId]);$stockScope=$scopeStmt->fetch();
            if(!$stockScope){throw new RuntimeException('Warehouse stock row not found.');}
            enforceScopeAllowed($pdo,(int)($stockScope['company_id']??0),(int)($stockScope['branch_id']??0),(int)($stockScope['warehouse_id']??0),true);
            $reorder=max(0,(float)($_POST['reorder_level']??5));
            $pdo->prepare('UPDATE '.table('warehouse_stock').' SET reorder_level=? WHERE id=?')->execute([$reorder,$stockId]);
            logActivity($pdo,'Inventory','warehouse_reorder_update','Warehouse stock reorder level updated for stock row #'.$stockId.'.','warehouse_stock',$stockId);
            $pdo->commit();
            flash('success','Warehouse reorder level updated.');
        }
    } catch(Throwable $e){
        if($pdo->inTransaction()){$pdo->rollBack();}
        flash('error',$e->getMessage());
    }
    redirect(ADMIN_URL.'/erp/inventory.php');
}

$products=$pdo->query('SELECT id,name,sku,stock FROM '.table('products').' WHERE active=1 ORDER BY name')->fetchAll();
$stockParams=[];$stockWhere=scopeWhereClause($pdo,'ws',$stockParams,false);
$stockStmt=$pdo->prepare('SELECT ws.*,p.name,p.sku,p.price,c.company_name,b.branch_name,w.warehouse_name,l.location_name FROM '.table('warehouse_stock').' ws LEFT JOIN '.table('products').' p ON p.id=ws.product_id LEFT JOIN '.table('companies').' c ON c.id=ws.company_id LEFT JOIN '.table('branches').' b ON b.id=ws.branch_id LEFT JOIN '.table('warehouses').' w ON w.id=ws.warehouse_id LEFT JOIN '.table('warehouse_locations').' l ON l.id=ws.location_id'.$stockWhere.' ORDER BY p.name,w.warehouse_name');$stockStmt->execute($stockParams);$rows=$stockStmt->fetchAll();
$movementParams=[];$movementWhere=scopeWhereClause($pdo,'m',$movementParams,false);$movementStmt=$pdo->prepare('SELECT m.*,p.name,w.warehouse_name,b.branch_name FROM '.table('inventory_movements').' m LEFT JOIN '.table('products').' p ON p.id=m.product_id LEFT JOIN '.table('warehouses').' w ON w.id=m.warehouse_id LEFT JOIN '.table('branches').' b ON b.id=m.branch_id'.$movementWhere.' ORDER BY m.created_at DESC,m.id DESC LIMIT 40');$movementStmt->execute($movementParams);$movements=$movementStmt->fetchAll();
$metricParams=[];$metricWhere=scopeWhereClause($pdo,'ws',$metricParams,false);$lowStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('warehouse_stock').' ws'.($metricWhere!==''?$metricWhere.' AND ws.quantity<=ws.reorder_level':' WHERE ws.quantity<=ws.reorder_level'));$lowStmt->execute($metricParams);$lowStock=(int)$lowStmt->fetchColumn();
$totalStmt=$pdo->prepare('SELECT COALESCE(SUM(ws.quantity),0) FROM '.table('warehouse_stock').' ws'.$metricWhere);$totalStmt->execute($metricParams);$totalUnits=(float)$totalStmt->fetchColumn();
$reservedStmt=$pdo->prepare('SELECT COALESCE(SUM(ws.reserved_quantity),0) FROM '.table('warehouse_stock').' ws'.$metricWhere);$reservedStmt->execute($metricParams);$reservedUnits=(float)$reservedStmt->fetchColumn();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Warehouse Units</div><div class="metric-sm"><?php echo number_format($totalUnits,2); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Reserved Units</div><div class="metric-sm"><?php echo number_format($reservedUnits,2); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Low Stock Lines</div><div class="metric-sm <?php echo $lowStock>0?'money-negative':'money-positive'; ?>"><?php echo $lowStock; ?></div></div></div></div>
<div class="row g-4">
<div class="col-xl-4">
<form method="post" class="card-admin p-4">
<input type="hidden" name="form_type" value="stock_movement">
<div class="erp-kicker">Warehouse Movement</div><h2 class="h5 mb-3">Post Stock Movement</h2>
<div class="mb-3"><label class="form-label">Product</label><select class="form-select" name="product_id"><?php foreach($products as $product): ?><option value="<?php echo (int)$product['id']; ?>"><?php echo esc($product['sku'].' · '.$product['name'].' · Total '.$product['stock']); ?></option><?php endforeach; ?></select></div>
<div class="row g-2"><div class="col-md-4 mb-3"><label class="form-label">Movement</label><select class="form-select" name="movement_type"><option value="in">Stock In</option><option value="out">Stock Out</option></select></div><div class="col-md-4 mb-3"><label class="form-label">Quantity</label><input class="form-control" type="number" step="0.01" min="0" name="quantity" required></div><div class="col-md-4 mb-3"><label class="form-label">Unit Cost for Stock In</label><input class="form-control" type="number" step="0.01" min="0" name="unit_cost" placeholder="Uses average cost if blank"></div></div>
<div class="mb-3"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Warehouse</label><select class="form-select" name="warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$warehouse['id']===(int)$defaultScope['warehouse_id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Location</label><select class="form-select" name="location_id"><?php foreach($scopeOptions['locations'] as $location): ?><option value="<?php echo (int)$location['id']; ?>" <?php echo (int)$location['id']===(int)$defaultScope['location_id']?'selected':''; ?>><?php echo esc($location['location_code'].' · '.$location['location_name']); ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div><button class="btn btn-brand">Post Movement</button>
</form>
</div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Warehouse Stock Lines</div><h2 class="h5 mb-0">Stock by Scope</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/warehouse-stock.php">Detailed Warehouse Stock</a></div><table class="table align-middle"><thead><tr><th>Product</th><th>Scope</th><th>Qty</th><th>Reserved</th><th>Reorder</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['sku'].' · '.$row['name']); ?></strong></td><td><?php echo esc($row['company_name'].' / '.$row['branch_name']); ?><div class="small text-secondary"><?php echo esc($row['warehouse_name'].' / '.$row['location_name']); ?></div></td><td><?php echo number_format((float)$row['quantity'],2); ?></td><td><?php echo number_format((float)$row['reserved_quantity'],2); ?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="stock_id" value="<?php echo (int)$row['id']; ?>"><input class="form-control form-control-sm" style="max-width:100px" type="number" step="0.01" name="reorder_level" value="<?php echo esc($row['reorder_level']); ?>"><button class="btn btn-sm btn-outline-primary">Save</button></form></td><td><span class="badge bg-<?php echo (float)$row['quantity']<=(float)$row['reorder_level']?'danger':'success'; ?>"><?php echo (float)$row['quantity']<=(float)$row['reorder_level']?'Low':'OK'; ?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<div class="table-wrap table-responsive mt-4"><div class="table-toolbar"><div><div class="erp-kicker">Stock Ledger</div><h2 class="h5 mb-0">Recent Movements</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Product</th><th>Movement</th><th>Qty</th><th>Scope</th><th>Reference</th></tr></thead><tbody><?php foreach($movements as $move): ?><tr><td><?php echo esc($move['created_at']); ?></td><td><?php echo esc($move['name']); ?></td><td><?php echo esc($move['movement_type']); ?></td><td><?php echo esc($move['quantity']); ?></td><td><?php echo esc(($move['branch_name']?:''). ' / '.($move['warehouse_name']?:'')); ?></td><td><?php echo esc($move['reference_type'].' #'.$move['reference_id']); ?><div class="small text-secondary"><?php echo esc($move['notes']); ?></div></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>