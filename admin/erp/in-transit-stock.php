<?php
$pageTitle='In-Transit Stock';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('inventory_valuation');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$status=trim((string)($_GET['status']??'in_transit'));

$params=[];$parts=[];
if($status!==''){$parts[]='its.status=?';$params[]=$status;}
if((int)$filters['company_id']>0){$parts[]='(its.from_company_id=? OR its.to_company_id=?)';$params[]=(int)$filters['company_id'];$params[]=(int)$filters['company_id'];}
if((int)$filters['branch_id']>0){$parts[]='(its.from_branch_id=? OR its.to_branch_id=?)';$params[]=(int)$filters['branch_id'];$params[]=(int)$filters['branch_id'];}
$access=inTransitAccessCondition($pdo,'its',$params,false);
$where=$parts?' WHERE '.implode(' AND ',$parts):'';
if($where===''){$where=$access!==''?' WHERE '.substr($access,5):'';}else{$where.=$access;}

$stmt=$pdo->prepare('SELECT its.*,st.transfer_number,p.sku,p.name,fb.branch_name from_branch_name,tb.branch_name to_branch_name,fw.warehouse_name from_warehouse_name,tw.warehouse_name to_warehouse_name FROM '.table('in_transit_stock').' its LEFT JOIN '.table('stock_transfers').' st ON st.id=its.stock_transfer_id LEFT JOIN '.table('products').' p ON p.id=its.product_id LEFT JOIN '.table('branches').' fb ON fb.id=its.from_branch_id LEFT JOIN '.table('branches').' tb ON tb.id=its.to_branch_id LEFT JOIN '.table('warehouses').' fw ON fw.id=its.from_warehouse_id LEFT JOIN '.table('warehouses').' tw ON tw.id=its.to_warehouse_id'.$where.' ORDER BY its.dispatched_at DESC,its.id DESC');
$stmt->execute($params);
$rows=$stmt->fetchAll();

$openQty=0.0;$openValue=0.0;$receivedValue=0.0;$receivedQty=0.0;
foreach($rows as $row){
  if(($row['status']??'')==='received'){$receivedQty+=(float)$row['received_quantity'];$receivedValue+=(float)$row['total_value'];}
  else{$openQty+=max(0,(float)$row['quantity']-(float)$row['received_quantity']);$openValue+=(float)$row['total_value'];}
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Transfer In-Transit Control</div><h2 class="h4 mb-1">In-Transit Stock Register</h2><p class="text-secondary mb-0">Stock dispatched but not yet received remains visible with quantity and value traceability.</p></div><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['in_transit','received'] as $filter): ?><option value="<?php echo esc($filter); ?>" <?php echo $status===$filter?'selected':''; ?>><?php echo esc(str_replace('_',' ',ucwords($filter,'_'))); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Apply</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Visible Lines</div><div class="metric"><?php echo count($rows); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open In-Transit Qty</div><div class="metric-sm"><?php echo number_format($openQty,2); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open In-Transit Value</div><div class="metric-sm"><?php echo money($openValue); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Received Value In View</div><div class="metric-sm money-positive"><?php echo money($receivedValue); ?></div></div></div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">In-Transit Ledger</div><h2 class="h5 mb-0">Dispatched Stock Rows</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/stock-transfers.php"><?php echo t('Stock Transfers', 'تحويلات المخزون'); ?></a></div><table class="table align-middle"><thead><tr><th>Transfer / Product</th><th>Route</th><th>Qty</th><th>Received</th><th>Unit Cost</th><th>Value</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/view-stock-transfer.php?id=<?php echo (int)$row['stock_transfer_id']; ?>"><strong><?php echo esc($row['transfer_number']); ?></strong></a><div class="small text-secondary"><?php echo esc(($row['sku']?:''). ' · '.($row['name']?:'')); ?></div></td><td><?php echo esc(($row['from_branch_name']?:'—').' / '.($row['from_warehouse_name']?:'—')); ?><div class="small text-secondary">→ <?php echo esc(($row['to_branch_name']?:'—').' / '.($row['to_warehouse_name']?:'—')); ?></div></td><td><?php echo number_format((float)$row['quantity'],2); ?></td><td><?php echo number_format((float)$row['received_quantity'],2); ?></td><td><?php echo money($row['unit_cost']); ?></td><td><?php echo money($row['total_value']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['status'],'_'))); ?></span><div class="small text-secondary"><?php echo esc($row['dispatched_at']?:''); ?></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No in-transit stock found for this filter.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>