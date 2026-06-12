<?php
$pageTitle='Document Sequences';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('org_structure');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$documentTypes=[
  'invoice'=>'Invoices',
  'quotation'=>'Quotations',
  'purchase_order'=>'Purchase Orders',
  'order'=>'Website Orders',
  'payment'=>'Payments',
  'journal'=>'Journal Entries',
  'stock_transfer'=>'Stock Transfers',
  'intercompany_transaction'=>'Intercompany Transactions',
  'approval_request'=>'Approval Requests',
  'purchase_requisition'=>'Purchase Requisitions',
  'goods_receipt'=>'Goods Receipt Notes',
  'supplier_invoice'=>'Supplier Invoices',
  'sales_order'=>'Sales Orders',
  'delivery_note'=>'Delivery Notes',
  'return_rma'=>'Returns / RMA',
  'job_card'=>'Job Cards',
  'service_contract'=>'Service Contracts / AMC',
  'warranty_claim'=>'Warranty Claims',
  'project'=>'Projects',
];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $companyId=(int)($_POST['company_id']??0);
  $branchId=(int)($_POST['branch_id']??0);
  $type=trim((string)($_POST['document_type']??''));
  $prefix=trim((string)($_POST['prefix']??''));
  $next=max(1,(int)($_POST['next_number']??1));
  $padding=max(3,min(12,(int)($_POST['padding']??5)));
  $status=trim((string)($_POST['status']??'active')) ?: 'active';
  if(!isset($documentTypes[$type])||$prefix===''){flash('error','Document type and prefix are required.');redirect(ADMIN_URL.'/erp/document-sequences.php');}
  enforceScopeAllowed($pdo,$companyId,$branchId,0,true);
  if($id>0){
    $stmt=$pdo->prepare('UPDATE '.table('document_sequences').' SET company_id=?,branch_id=?,document_type=?,prefix=?,next_number=?,padding=?,status=? WHERE id=?');
    $stmt->execute([$companyId?:null,$branchId?:null,$type,$prefix,$next,$padding,$status,$id]);
    logActivity($pdo,'Organization','document_sequence_updated','Document sequence '.$prefix.' updated.','document_sequence',$id);
    flash('success','Document sequence updated.');
  }else{
    $stmt=$pdo->prepare('INSERT INTO '.table('document_sequences').' (company_id,branch_id,document_type,prefix,next_number,padding,status) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$companyId?:null,$branchId?:null,$type,$prefix,$next,$padding,$status]);
    logActivity($pdo,'Organization','document_sequence_created','Document sequence '.$prefix.' created.','document_sequence',(int)$pdo->lastInsertId());
    flash('success','Document sequence created.');
  }
  redirect(ADMIN_URL.'/erp/document-sequences.php');
}
$edit=null;if(isset($_GET['edit'])){$stmt=$pdo->prepare('SELECT * FROM '.table('document_sequences').' WHERE id=? LIMIT 1');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch()?:null;}
$rows=$pdo->query('SELECT ds.*,c.company_name,b.branch_name FROM '.table('document_sequences').' ds LEFT JOIN '.table('companies').' c ON c.id=ds.company_id LEFT JOIN '.table('branches').' b ON b.id=ds.branch_id ORDER BY c.company_name,b.branch_name,ds.document_type')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
<div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>"><div class="erp-kicker">Branch-Specific Numbering</div><h2 class="h5 mb-3"><?php echo $edit?'Edit Sequence':'Create Sequence'; ?></h2><div class="mb-3"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)($edit['company_id']??0)===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)($edit['branch_id']??0)===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Document Type</label><select class="form-select" name="document_type"><?php foreach($documentTypes as $key=>$label): ?><option value="<?php echo esc($key); ?>" <?php echo (($edit['document_type']??'')===$key)?'selected':''; ?>><?php echo esc($label); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Prefix</label><input class="form-control" name="prefix" value="<?php echo esc($edit['prefix']??''); ?>" placeholder="INV-DXB"></div><div class="row g-2"><div class="col-md-6"><label class="form-label">Next Number</label><input class="form-control" type="number" min="1" name="next_number" value="<?php echo esc($edit['next_number']??1); ?>"></div><div class="col-md-6"><label class="form-label">Padding</label><input class="form-control" type="number" min="3" max="12" name="padding" value="<?php echo esc($edit['padding']??5); ?>"></div></div><div class="mt-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo (($edit['status']??'active')==='active')?'selected':''; ?>>Active</option><option value="inactive" <?php echo (($edit['status']??'active')==='inactive')?'selected':''; ?>>Inactive</option></select></div><div class="mt-3 d-flex gap-2"><button class="btn btn-brand"><?php echo $edit?'Save':'Create'; ?></button><?php if($edit): ?><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/document-sequences.php">Cancel</a><?php endif; ?></div></form></div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Sequence Matrix</div><h2 class="h5 mb-0">Configured Branch Sequences</h2></div></div><table class="table align-middle"><thead><tr><th>Document</th><th>Company / Branch</th><th>Prefix</th><th>Next</th><th>Padding</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($documentTypes[$row['document_type']]??$row['document_type']); ?></strong></td><td><?php echo esc(($row['company_name']?:'—').' / '.($row['branch_name']?:'—')); ?></td><td><?php echo esc($row['prefix']); ?></td><td><?php echo (int)$row['next_number']; ?></td><td><?php echo (int)$row['padding']; ?></td><td><span class="badge bg-<?php echo $row['status']==='active'?'success':'secondary'; ?>"><?php echo esc($row['status']); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int)$row['id']; ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>