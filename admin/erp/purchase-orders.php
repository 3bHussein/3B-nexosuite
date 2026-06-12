<?php
$pageTitle='Purchase Orders & Replenishment';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('purchase_orders');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$status=trim((string)($_GET['status']??''));
$where='';$params=[];
if($status!==''){$where=' WHERE po.status=?';$params[]=$status;}
$condition=selectedScopeCondition('po',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'po',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt=$pdo->prepare('SELECT po.*,s.supplier_code FROM ' . table('purchase_orders') . ' po LEFT JOIN ' . table('suppliers') . ' s ON s.id=po.supplier_id'.$where.' ORDER BY po.created_at DESC,po.id DESC');
$stmt->execute($params);$rows=$stmt->fetchAll();
$totalParams=[];$totalCondition=selectedScopeCondition('po',$totalParams,$filters,['company_id','branch_id']);$totalCondition.=scopeQueryCondition($pdo,'po',$totalParams,false);$totalWhere=$totalCondition!==''?' WHERE '.substr($totalCondition,5):'';
$totalStmt=$pdo->prepare('SELECT COUNT(*) po_count,COALESCE(SUM(po.total),0) po_total,COALESCE(SUM(CASE WHEN po.status IN ("approved","partially_received") THEN po.total ELSE 0 END),0) committed_total,COALESCE(SUM(CASE WHEN po.status="received" THEN po.total ELSE 0 END),0) received_total FROM ' . table('purchase_orders') . ' po'.$totalWhere);$totalStmt->execute($totalParams);$totals=$totalStmt->fetch();
$dueParams=[];$dueCondition=selectedScopeCondition('po',$dueParams,$filters,['company_id','branch_id']);$dueCondition.=scopeQueryCondition($pdo,'po',$dueParams,false);$dueStmt=$pdo->prepare('SELECT COUNT(*) FROM ' . table('purchase_orders') . ' po WHERE po.expected_date IS NOT NULL AND po.expected_date < CURDATE() AND po.status IN ("approved","partially_received")'.$dueCondition);$dueStmt->execute($dueParams);$due=(int)$dueStmt->fetchColumn();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker"><?php echo t('Purchase Orders', 'أوامر الشراء'); ?></div><div class="metric"><?php echo (int)$totals['po_count']; ?></div></div><div class="stat-note">Procurement documents created.</div></div></div>
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Total PO Value</div><div class="metric-sm"><?php echo money($totals['po_total']); ?></div></div><div class="stat-note">All purchase orders.</div></div></div>
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Committed Procurement</div><div class="metric-sm"><?php echo money($totals['committed_total']); ?></div></div><div class="stat-note">Approved or partially received.</div></div></div>
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Late Deliveries</div><div class="metric-sm <?php echo $due>0?'money-negative':'money-positive'; ?>"><?php echo $due; ?></div></div><div class="stat-note">Open orders past expected date.</div></div></div>
</div>
<div class="table-wrap">
    <div class="card-admin p-3 mb-3"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><input type="hidden" name="status" value="<?php echo esc($status); ?>"><button class="btn btn-brand">Apply Scope</button></form></div>
    <div class="table-toolbar">
        <div class="filter-bar">
            <input class="form-control search-input" placeholder="Search PO, supplier, amount..." data-table-search="#poTable">
            <a class="btn btn-sm <?php echo $status===''?'btn-brand':'btn-outline-secondary'; ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/purchase-orders.php?company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>">All</a>
            <?php foreach(['draft','pending_approval','approved','partially_received','received','cancelled'] as $filter): ?><a class="btn btn-sm <?php echo $status===$filter?'btn-brand':'btn-outline-secondary'; ?>" href="?status=<?php echo esc($filter); ?>&company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>"><?php echo esc(str_replace('_',' ',ucwords($filter,'_'))); ?></a><?php endforeach; ?>
        </div>
        <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-purchase-order.php">Create Purchase Order</a>
    </div>
    <div class="table-responsive"><table class="table" id="poTable"><thead><tr><th>PO</th><th>Supplier</th><th>Total</th><th>Status</th><th>Order Date</th><th>Expected</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><div class="fw-semibold"><?php echo esc($row['po_number']); ?></div><div class="small-muted"><?php echo esc($row['supplier_code'] ?? 'Supplier'); ?></div></td><td><?php echo esc($row['supplier_name']); ?></td><td><?php echo money($row['total']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['status'],'_'))); ?></span></td><td><?php echo esc($row['order_date'] ?: '—'); ?></td><td><div><?php echo esc($row['expected_date'] ?: '—'); ?></div><div class="small-muted"><?php echo esc(dueLabel($row['expected_date'])); ?></div></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-purchase-order.php?id=<?php echo (int)$row['id']; ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>