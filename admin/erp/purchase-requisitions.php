<?php
$pageTitle='Purchase Requisitions';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('purchase_requisitions');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$status=trim((string)($_GET['status']??''));

$params=[];$where='';
if($status!==''){$where=' WHERE pr.status=?';$params[]=$status;}
$condition=selectedScopeCondition('pr',$params,$filters,['company_id','branch_id','warehouse_id']);$condition.=scopeQueryCondition($pdo,'pr',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt=$pdo->prepare('SELECT pr.*,u.email requested_by_email,po.po_number FROM '.table('purchase_requisitions').' pr LEFT JOIN '.table('users').' u ON u.id=pr.requested_by_user_id LEFT JOIN '.table('purchase_orders').' po ON po.id=pr.converted_po_id'.$where.' ORDER BY pr.created_at DESC,pr.id DESC LIMIT 250');
$stmt->execute($params);$rows=$stmt->fetchAll();

$countParams=[];$countCondition=selectedScopeCondition('pr',$countParams,$filters,['company_id','branch_id','warehouse_id']);$countCondition.=scopeQueryCondition($pdo,'pr',$countParams,false);$countWhere=$countCondition!==''?' WHERE '.substr($countCondition,5):'';
$countStmt=$pdo->prepare('SELECT pr.status,COUNT(*) total,COALESCE(SUM(pr.total),0) value_total FROM '.table('purchase_requisitions').' pr'.$countWhere.' GROUP BY pr.status');$countStmt->execute($countParams);$counts=$countStmt->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Demand Before Procurement</div><h2 class="h4 mb-1">Purchase Requisitions</h2><p class="text-secondary mb-0">Create internal demand requests, route larger requisitions for approval, then convert approved demand into purchase orders.</p></div>
  <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-purchase-requisition.php">Create Requisition</a>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Draft</div><div class="metric-sm"><?php echo (int)($counts['draft']['total']??0); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Pending Approval</div><div class="metric-sm"><?php echo (int)($counts['pending_approval']['total']??0); ?></div><div class="small text-secondary"><?php echo money($counts['pending_approval']['value_total']??0); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Approved</div><div class="metric-sm money-positive"><?php echo (int)($counts['approved']['total']??0); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Converted</div><div class="metric-sm"><?php echo (int)($counts['converted']['total']??0); ?></div></div></div>
</div>
<div class="card-admin p-3 mb-4"><form class="d-flex flex-wrap gap-2 align-items-end">
  <div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['draft','pending_approval','approved','converted','rejected','cancelled'] as $filter): ?><option value="<?php echo esc($filter); ?>" <?php echo $status===$filter?'selected':''; ?>><?php echo esc(str_replace('_',' ',ucwords($filter,'_'))); ?></option><?php endforeach; ?></select></div>
  <div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
  <div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
  <button class="btn btn-brand">Apply</button>
</form></div>
<div class="table-wrap table-responsive">
  <div class="table-toolbar"><div><div class="erp-kicker">Requisition Register</div><h2 class="h5 mb-0">Internal Purchase Demand</h2></div></div>
  <table class="table align-middle"><thead><tr><th>Requisition</th><th>Department</th><th>Required</th><th>Total</th><th>Status</th><th>Requester</th><th>PO Link</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['requisition_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['created_at']); ?></div></td><td><?php echo esc($row['department']?:'—'); ?></td><td><?php echo esc($row['required_date']?:'—'); ?></td><td><?php echo money($row['total']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['status'],'_'))); ?></span></td><td><?php echo esc($row['requested_by_email']?:'System'); ?></td><td><?php echo esc($row['po_number']?:'—'); ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-purchase-requisition.php?id=<?php echo (int)$row['id']; ?>">Open</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="8" class="text-secondary">No purchase requisitions found.</td></tr><?php endif; ?></tbody></table>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>