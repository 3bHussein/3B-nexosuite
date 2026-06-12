<?php
$pageTitle='Warranty Claims';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('warranty_claims');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$status=trim((string)($_GET['status']??''));
$params=[];$where='';
if($status!==''){$where=' WHERE wc.status=?';$params[]=$status;}
$condition=scopeQueryCondition($pdo,'wc',$params,false);if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt=$pdo->prepare('SELECT wc.*,c.customer_code,jc.job_card_number,p.sku product_sku,s.supplier_code FROM '.table('warranty_claims').' wc LEFT JOIN '.table('customers').' c ON c.id=wc.customer_id LEFT JOIN '.table('job_cards').' jc ON jc.id=wc.job_card_id LEFT JOIN '.table('products').' p ON p.id=wc.product_id LEFT JOIN '.table('suppliers').' s ON s.id=wc.supplier_id'.$where.' ORDER BY wc.created_at DESC LIMIT 250');
$stmt->execute($params);$rows=$stmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">After-Sales Control</div><h2 class="h4 mb-1">Warranty Claims</h2><p class="text-secondary mb-0">Manage customer warranty claims, supplier follow-up, approval, replacement value, and closure.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-warranty-claim.php">Create Claim</a></div>
<div class="card-admin p-3 mb-4"><form class="d-flex gap-2 align-items-end"><div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['draft','pending_approval','approved','submitted','closed','rejected','cancelled'] as $filter): ?><option value="<?php echo esc($filter); ?>" <?php echo $status===$filter?'selected':''; ?>><?php echo esc(str_replace('_',' ',ucwords($filter,'_'))); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Apply</button></form></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Claim Register</div><h2 class="h5 mb-0">Warranty Follow-Up</h2></div></div><table class="table align-middle"><thead><tr><th>Claim</th><th>Customer / Job</th><th>Product</th><th>Value</th><th>Status</th><th>Approval</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['claim_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['serial_number']?:''); ?></div></td><td><?php echo esc(($row['customer_code']?:'Customer').' · JC '.($row['job_card_number']?:'—')); ?></td><td><?php echo esc($row['product_sku']?:'—'); ?><div class="small text-secondary"><?php echo esc($row['supplier_code']?:''); ?></div></td><td><?php echo money($row['claim_value']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['status'],'_'))); ?></span></td><td><span class="badge bg-<?php echo esc(statusTone($row['approval_status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['approval_status'],'_'))); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-warranty-claim.php?id=<?php echo (int)$row['id']; ?>">Open</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>