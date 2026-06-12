<?php
$pageTitle='RFQs';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('rfq_management');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$status=trim((string)($_GET['status']??''));
$params=[];$where='';
if($status!==''){$where=' WHERE r.status=?';$params[]=$status;}
$condition=selectedScopeCondition('r',$params,$filters,['company_id','branch_id','warehouse_id']);$condition.=scopeQueryCondition($pdo,'r',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt=$pdo->prepare('SELECT r.*,pr.requisition_number,s.company_name awarded_supplier,po.po_number,(SELECT COUNT(*) FROM '.table('rfq_supplier_invitations').' i WHERE i.rfq_id=r.id) supplier_count,(SELECT COUNT(*) FROM '.table('rfq_supplier_quotes').' q WHERE q.rfq_id=r.id) quote_count FROM '.table('rfqs').' r LEFT JOIN '.table('purchase_requisitions').' pr ON pr.id=r.source_requisition_id LEFT JOIN '.table('suppliers').' s ON s.id=r.awarded_supplier_id LEFT JOIN '.table('purchase_orders').' po ON po.id=r.converted_po_id'.$where.' ORDER BY r.created_at DESC,r.id DESC LIMIT 250');
$stmt->execute($params);$rows=$stmt->fetchAll();
$summary=['draft'=>0,'open'=>0,'awarded'=>0,'closed'=>0,'cancelled'=>0];foreach($rows as $r){$k=(string)($r['status']??'');if(isset($summary[$k])){$summary[$k]++;}}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Procurement Sourcing</div><h2 class="h4 mb-1">RFQs</h2><p class="text-secondary mb-0">Invite suppliers, collect quotes, compare commercial offers, and award into purchase orders.</p></div>
  <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-rfq.php">Create RFQ</a>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open</div><div class="metric-sm"><?php echo (int)$summary['open']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Draft</div><div class="metric-sm"><?php echo (int)$summary['draft']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Awarded</div><div class="metric-sm money-positive"><?php echo (int)$summary['awarded']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Closed</div><div class="metric-sm"><?php echo (int)$summary['closed']; ?></div></div></div>
</div>
<div class="card-admin p-3 mb-4"><form class="d-flex flex-wrap gap-2 align-items-end">
  <div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['draft','open','awarded','closed','cancelled'] as $filter): ?><option value="<?php echo esc($filter); ?>" <?php echo $status===$filter?'selected':''; ?>><?php echo esc(ucfirst($filter)); ?></option><?php endforeach; ?></select></div>
  <div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
  <div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
  <button class="btn btn-brand">Apply</button>
</form></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">RFQ Register</div><h2 class="h5 mb-0">Supplier Sourcing Pipeline</h2></div></div><table class="table align-middle"><thead><tr><th>RFQ</th><th>Source</th><th>Due</th><th>Suppliers</th><th>Quotes</th><th>Award</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['rfq_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['title']); ?></div></td><td><?php echo esc($row['requisition_number']?:'Direct RFQ'); ?></td><td><?php echo esc($row['due_date']?:'—'); ?></td><td><?php echo (int)$row['supplier_count']; ?></td><td><?php echo (int)$row['quote_count']; ?></td><td><?php echo esc($row['awarded_supplier']?:'—'); ?><div class="small text-secondary"><?php echo esc($row['po_number']?:''); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-rfq.php?id=<?php echo (int)$row['id']; ?>">Open</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="8" class="text-secondary">No RFQs found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>