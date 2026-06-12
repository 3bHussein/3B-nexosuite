<?php
$pageTitle='Inventory Valuation';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('inventory_valuation');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();

$params=[];
$condition=selectedScopeCondition('ws',$params,$filters,['company_id','branch_id','warehouse_id']);
$condition.=scopeQueryCondition($pdo,'ws',$params,false);
$where=$condition!==''?' WHERE '.substr($condition,5):'';

$stmt=$pdo->prepare('SELECT ws.*,p.sku,p.name,p.cost_price,p.average_cost,c.company_name,b.branch_name,w.warehouse_name,l.location_name FROM '.table('warehouse_stock').' ws LEFT JOIN '.table('products').' p ON p.id=ws.product_id LEFT JOIN '.table('companies').' c ON c.id=ws.company_id LEFT JOIN '.table('branches').' b ON b.id=ws.branch_id LEFT JOIN '.table('warehouses').' w ON w.id=ws.warehouse_id LEFT JOIN '.table('warehouse_locations').' l ON l.id=ws.location_id'.$where.' ORDER BY c.company_name,b.branch_name,w.warehouse_name,p.name');
$stmt->execute($params);
$rows=$stmt->fetchAll();

$units=0.0;$stockValue=0.0;$lines=count($rows);$lowValueLines=0;
foreach($rows as $row){
  $units+=(float)$row['quantity'];
  $stockValue+=(float)$row['stock_value'];
  if((float)$row['stock_value']<=0 && (float)$row['quantity']>0){$lowValueLines++;}
}
$avgValue=$units>0?$stockValue/$units:0;

$entryParams=[];
$entryCondition=selectedScopeCondition('ive',$entryParams,$filters,['company_id','branch_id','warehouse_id']);
$entryCondition.=scopeQueryCondition($pdo,'ive',$entryParams,false);
$entryWhere=$entryCondition!==''?' WHERE '.substr($entryCondition,5):'';
$entryStmt=$pdo->prepare('SELECT ive.*,p.sku,p.name,w.warehouse_name,b.branch_name FROM '.table('inventory_valuation_entries').' ive LEFT JOIN '.table('products').' p ON p.id=ive.product_id LEFT JOIN '.table('warehouses').' w ON w.id=ive.warehouse_id LEFT JOIN '.table('branches').' b ON b.id=ive.branch_id'.$entryWhere.' ORDER BY ive.created_at DESC,ive.id DESC LIMIT 80');
$entryStmt->execute($entryParams);
$entries=$entryStmt->fetchAll();

include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Moving-Average Stock Valuation</div><h2 class="h4 mb-1">Inventory Valuation Control</h2><p class="text-secondary mb-0">Warehouse-level stock value, moving-average unit cost, and valuation audit history.</p></div>
  <form class="d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">Warehouse</label><select class="form-select" name="warehouse_id"><option value="0">All</option><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)$filters['warehouse_id']===(int)$warehouse['id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
    <button class="btn btn-brand">Apply</button>
  </form>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Valued Units</div><div class="metric-sm"><?php echo number_format($units,2); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Inventory Value</div><div class="metric-sm"><?php echo money($stockValue); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Average Cost / Unit</div><div class="metric-sm"><?php echo money($avgValue); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Unvalued Lines</div><div class="metric-sm <?php echo $lowValueLines>0?'money-negative':'money-positive'; ?>"><?php echo (int)$lowValueLines; ?></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-8">
    <div class="table-wrap table-responsive">
      <div class="table-toolbar"><div><div class="erp-kicker">Warehouse Valuation</div><h2 class="h5 mb-0">Stock Value by Scope</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/in-transit-stock.php"><?php echo t('In-Transit Stock', 'مخزون بالطريق'); ?></a></div>
      <table class="table align-middle"><thead><tr><th>Product</th><th>Scope</th><th>Qty</th><th>Avg Unit Cost</th><th>Stock Value</th><th>Product Cost</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc(($row['sku']?:''). ' · '.($row['name']?:'')); ?></strong><div class="small text-secondary"><?php echo esc($row['location_name']?:'Default location'); ?></div></td><td><?php echo esc(($row['company_name']?:'—').' / '.($row['branch_name']?:'—')); ?><div class="small text-secondary"><?php echo esc($row['warehouse_name']?:'—'); ?></div></td><td><?php echo number_format((float)$row['quantity'],2); ?></td><td><?php echo money($row['average_unit_cost']); ?></td><td><?php echo money($row['stock_value']); ?></td><td><?php echo money($row['cost_price']); ?><div class="small text-secondary">Global avg: <?php echo money($row['average_cost']); ?></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No valuation rows found for this scope.</td></tr><?php endif; ?></tbody></table>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Valuation Audit</div><h2 class="h5 mb-0">Latest Value Movements</h2></div></div><table class="table align-middle"><thead><tr><th>Entry</th><th class="text-end">Value</th></tr></thead><tbody><?php foreach($entries as $entry): ?><tr><td><strong><?php echo esc(($entry['sku']?:''). ' · '.($entry['name']?:'')); ?></strong><div class="small text-secondary"><?php echo esc($entry['movement_type'].' · '.$entry['reference_type'].' #'.$entry['reference_id']); ?></div><div class="small text-secondary"><?php echo esc(($entry['branch_name']?:'').' / '.($entry['warehouse_name']?:'')); ?></div></td><td class="text-end"><?php echo money($entry['value_delta']); ?><div class="small text-secondary"><?php echo esc($entry['created_at']); ?></div></td></tr><?php endforeach; ?><?php if(!$entries): ?><tr><td colspan="2" class="text-secondary">No valuation history yet.</td></tr><?php endif; ?></tbody></table></div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>