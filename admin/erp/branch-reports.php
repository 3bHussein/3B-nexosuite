<?php
$pageTitle='Branch Performance';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('reports');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);$branchId=(int)($_GET['branch_id']??0);
$params=[];$where='';if($branchId>0){$where=' WHERE b.id=? ';$params[]=$branchId;}
$accessCondition=branchTableAccessCondition($pdo,'b',$params,false);
if($where===''){$where=$accessCondition!==''?' WHERE '.substr($accessCondition,5):'';}else{$where.=$accessCondition;}
$sql='SELECT b.id,b.branch_code,b.branch_name,c.company_name,
COALESCE((SELECT SUM(total) FROM '.table('invoices').' i WHERE i.branch_id=b.id AND i.status<>"cancelled"),0) invoice_total,
COALESCE((SELECT SUM(total) FROM '.table('purchase_orders').' po WHERE po.branch_id=b.id AND po.status<>"cancelled"),0) purchase_total,
COALESCE((SELECT SUM(ws.quantity) FROM '.table('warehouse_stock').' ws WHERE ws.branch_id=b.id),0) stock_units,
COALESCE((SELECT COUNT(*) FROM '.table('orders').' o WHERE o.branch_id=b.id),0) order_count
FROM '.table('branches').' b LEFT JOIN '.table('companies').' c ON c.id=b.company_id'.$where.' ORDER BY c.company_name,b.branch_name';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Branch Reporting</div><h2 class="h4 mb-1">Operational Branch Summary</h2><p class="text-secondary mb-0">Sales, procurement, website orders, and warehouse stock summarized by branch.</p></div><form class="d-flex gap-2 align-items-end"><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All branches</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===$branchId?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Filter</button></form></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Branch</th><th>Company</th><th>Invoice Revenue</th><th>PO Value</th><th>Website Orders</th><th>Stock Units</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['branch_code'].' · '.$row['branch_name']); ?></strong></td><td><?php echo esc($row['company_name']); ?></td><td><?php echo money($row['invoice_total']); ?></td><td><?php echo money($row['purchase_total']); ?></td><td><?php echo (int)$row['order_count']; ?></td><td><?php echo number_format((float)$row['stock_units'],2); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>