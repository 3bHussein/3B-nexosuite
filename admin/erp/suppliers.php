<?php
$pageTitle='Suppliers & Vendor Master';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('suppliers');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);
$filters=requestScopeFilters();

if(isset($_GET['toggle'])){
    $id=(int)$_GET['toggle'];
    $scopeStmt=$pdo->prepare('SELECT company_id,branch_id FROM '.table('suppliers').' WHERE id=? LIMIT 1');$scopeStmt->execute([$id]);$supplierScope=$scopeStmt->fetch();
    if($supplierScope){enforceScopeAllowed($pdo,(int)($supplierScope['company_id']??0),(int)($supplierScope['branch_id']??0),0,true);}
    $stmt=$pdo->prepare('UPDATE ' . table('suppliers') . ' SET status=CASE WHEN status="active" THEN "inactive" ELSE "active" END WHERE id=?');
    $stmt->execute([$id]);
    logActivity($pdo,'Procurement','supplier_status','Supplier #'.$id.' status toggled.','supplier',$id);
    flash('success','Supplier status updated.');
    redirect(ADMIN_URL.'/erp/suppliers.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $id=(int)($_POST['id']??0);
    $companyId=(int)($_POST['company_id']??$defaultScope['company_id']);
    $branchId=(int)($_POST['branch_id']??$defaultScope['branch_id']);
    enforceScopeAllowed($pdo,$companyId,$branchId,0,true);
    $code=trim((string)($_POST['supplier_code']??''));
    if($code===''){$code=nextDocumentNumber($pdo,'suppliers',(string)setting('supplier_prefix','SUP'));}
    $payload=[
        $companyId?:null,
        $branchId?:null,
        $code,
        trim((string)($_POST['company_name']??'')),
        trim((string)($_POST['contact_name']??'')),
        trim((string)($_POST['email']??'')),
        trim((string)($_POST['phone']??'')),
        trim((string)($_POST['tax_number']??'')),
        trim((string)($_POST['address']??'')),
        max(0,(int)($_POST['payment_terms_days']??0)),
        (string)($_POST['status']??'active'),
    ];
    if($payload[3]===''){flash('error','Supplier company name is required.');redirect(ADMIN_URL.'/erp/suppliers.php');}
    if($id>0){
        $stmt=$pdo->prepare('UPDATE ' . table('suppliers') . ' SET company_id=?,branch_id=?,supplier_code=?,company_name=?,contact_name=?,email=?,phone=?,tax_number=?,address=?,payment_terms_days=?,status=? WHERE id=?');
        $stmt->execute([...$payload,$id]);
        logActivity($pdo,'Procurement','supplier_update','Supplier '.$payload[3].' updated.','supplier',$id);
        flash('success','Supplier updated.');
    }else{
        $stmt=$pdo->prepare('INSERT INTO ' . table('suppliers') . ' (company_id,branch_id,supplier_code,company_name,contact_name,email,phone,tax_number,address,payment_terms_days,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute($payload);
        $newId=(int)$pdo->lastInsertId();
        logActivity($pdo,'Procurement','supplier_create','Supplier '.$payload[3].' created.','supplier',$newId);
        flash('success','Supplier created.');
    }
    redirect(ADMIN_URL.'/erp/suppliers.php');
}
$edit=null;
if(isset($_GET['edit'])){$stmt=$pdo->prepare('SELECT * FROM ' . table('suppliers') . ' WHERE id=? LIMIT 1');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch();}
$rowParams=[];$rowCondition=selectedScopeCondition('s',$rowParams,$filters,['company_id','branch_id']);$rowCondition.=scopeQueryCondition($pdo,'s',$rowParams,false);$rowWhere=$rowCondition!==''?' WHERE '.substr($rowCondition,5):'';
$rowStmt=$pdo->prepare('SELECT s.*,(SELECT COUNT(*) FROM ' . table('purchase_orders') . ' po WHERE po.supplier_id=s.id) po_count,(SELECT COALESCE(SUM(total),0) FROM ' . table('purchase_orders') . ' po WHERE po.supplier_id=s.id) po_value FROM ' . table('suppliers') . ' s'.$rowWhere.' ORDER BY s.created_at DESC,s.id DESC');$rowStmt->execute($rowParams);$rows=$rowStmt->fetchAll();
$activeParams=[];$activeCondition=selectedScopeCondition('s',$activeParams,$filters,['company_id','branch_id']);$activeCondition.=scopeQueryCondition($pdo,'s',$activeParams,false);$activeWhere=' WHERE s.status="active"'.$activeCondition;$activeStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('suppliers').' s'.$activeWhere);$activeStmt->execute($activeParams);$active=(int)$activeStmt->fetchColumn();
$vendorParams=[];$vendorCondition=selectedScopeCondition('po',$vendorParams,$filters,['company_id','branch_id']);$vendorCondition.=scopeQueryCondition($pdo,'po',$vendorParams,false);$vendorStmt=$pdo->prepare('SELECT COALESCE(SUM(po.total),0) FROM '.table('purchase_orders').' po WHERE po.status IN ("approved","partially_received","received")'.$vendorCondition);$vendorStmt->execute($vendorParams);$vendorSpend=(float)$vendorStmt->fetchColumn();
$pendingPO=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('purchase_orders') . ' WHERE status IN ("draft","approved","partially_received")')->fetchColumn();

include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Active Suppliers</div><div class="metric"><?php echo $active; ?></div><div class="stat-note">Current purchasing partners.</div></div></div>
    <div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Tracked Procurement</div><div class="metric-sm"><?php echo money($vendorSpend); ?></div><div class="stat-note">Approved/received purchase value.</div></div></div>
    <div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Open Purchase Orders</div><div class="metric"><?php echo $pendingPO; ?></div><div class="stat-note">Orders needing procurement follow-up.</div></div></div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <form method="post" class="card-admin p-4">
            <input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>">
            <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0"><?php echo $edit?'Edit Supplier':'Add Supplier'; ?></h2><?php if($edit): ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/suppliers.php">Cancel</a><?php endif; ?></div>
            <div class="mb-3"><label class="form-label">Supplier Code</label><input class="form-control" name="supplier_code" value="<?php echo esc($edit['supplier_code']??''); ?>" placeholder="Auto if blank"></div>
            <div class="mb-3"><label class="form-label">Company Name</label><input class="form-control" name="company_name" required value="<?php echo esc($edit['company_name']??''); ?>"></div>
            <div class="mb-3"><label class="form-label">Contact Name</label><input class="form-control" name="contact_name" value="<?php echo esc($edit['contact_name']??''); ?>"></div>
            <div class="row g-3"><div class="col-12"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)($edit['company_id']??$defaultScope['company_id'])===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)($edit['branch_id']??$defaultScope['branch_id'])===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?php echo esc($edit['email']??''); ?>"></div><div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo esc($edit['phone']??''); ?>"></div></div>
            <div class="mt-3"><label class="form-label">Tax / TRN</label><input class="form-control" name="tax_number" value="<?php echo esc($edit['tax_number']??''); ?>"></div>
            <div class="mt-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"><?php echo esc($edit['address']??''); ?></textarea></div>
            <div class="row g-3 mt-1"><div class="col-md-6"><label class="form-label">Payment Terms Days</label><input class="form-control" type="number" min="0" name="payment_terms_days" value="<?php echo esc($edit['payment_terms_days']??0); ?>"></div><div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo ($edit['status']??'active')==='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo ($edit['status']??'')==='inactive'?'selected':''; ?>>Inactive</option></select></div></div>
            <button class="btn btn-brand w-100 mt-4"><?php echo $edit?'Update Supplier':'Create Supplier'; ?></button>
        </form>
    </div>
    <div class="col-xl-8">
        <div class="card-admin p-3 mb-3"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Apply Scope</button></form></div>
<div class="table-wrap">
            <div class="table-toolbar"><div><div class="erp-kicker">Vendor directory</div><h2 class="h5 mb-0">Supplier Master</h2></div><input class="form-control search-input" placeholder="Search suppliers..." data-table-search="#suppliersTable"></div>
            <div class="table-responsive"><table class="table" id="suppliersTable"><thead><tr><th>Supplier</th><th>Contact</th><th>Terms</th><th>POs</th><th>PO Value</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><div class="fw-semibold"><?php echo esc($row['company_name']); ?></div><div class="small-muted"><?php echo esc($row['supplier_code']); ?></div></td><td><?php echo esc($row['contact_name']); ?><div class="small-muted"><?php echo esc($row['email']); ?></div></td><td><?php echo (int)$row['payment_terms_days']; ?> days</td><td><?php echo (int)$row['po_count']; ?></td><td><?php echo money($row['po_value']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(ucfirst($row['status'])); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?edit=<?php echo (int)$row['id']; ?>">Edit</a> <a class="btn btn-sm btn-outline-dark" href="?toggle=<?php echo (int)$row['id']; ?>">Toggle</a></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>