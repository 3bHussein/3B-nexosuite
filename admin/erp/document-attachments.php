<?php
$pageTitle='Document Attachments';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('document_attachments');
$pdo=getDB();
$type=preg_replace('/[^a-z0-9_]/','',strtolower((string)($_GET['type']??($_POST['type']??''))));
$id=(int)($_GET['id']??($_POST['id']??0));
if($type===''||$id<=0){flash('error','Document type and ID are required.');redirect(ADMIN_URL.'/erp/dashboard.php');}
$docUrl=match($type){
  'purchase_requisition'=>ADMIN_URL.'/erp/view-purchase-requisition.php?id='.$id,
  'purchase_order'=>ADMIN_URL.'/erp/view-purchase-order.php?id='.$id,
  'goods_receipt'=>ADMIN_URL.'/erp/view-goods-receipt.php?id='.$id,
  'supplier_invoice'=>ADMIN_URL.'/erp/view-supplier-invoice.php?id='.$id,
  'sales_order'=>ADMIN_URL.'/erp/view-sales-order.php?id='.$id,
  'delivery_note'=>ADMIN_URL.'/erp/view-delivery-note.php?id='.$id,
  'return_rma'=>ADMIN_URL.'/erp/view-return-rma.php?id='.$id,
  'job_card'=>ADMIN_URL.'/erp/view-job-card.php?id='.$id,
  'warranty_claim'=>ADMIN_URL.'/erp/view-warranty-claim.php?id='.$id,
  'project'=>ADMIN_URL.'/erp/view-project.php?id='.$id,
  'invoice'=>ADMIN_URL.'/erp/view-invoice.php?id='.$id,
  'quotation'=>ADMIN_URL.'/erp/view-quotation.php?id='.$id,
  default=>ADMIN_URL.'/erp/dashboard.php',
};
$scope=operationalScope($pdo);
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $upload=uploadAdminDocument('attachment_file','documents');
    if(!$upload){throw new RuntimeException('Choose a document to upload.');}
    $user=currentUser();$userId=(int)($user['id']??0)?:null;
    $stmt=$pdo->prepare('INSERT INTO '.table('document_attachments').' (company_id,branch_id,document_type,document_id,file_name,stored_path,mime_type,file_size,uploaded_by,notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,$type,$id,$upload['file_name'],$upload['stored_path'],$upload['mime_type'],$upload['file_size'],$userId,trim((string)($_POST['notes']??''))]);
    logActivity($pdo,'Documents','attachment_uploaded','Supporting document uploaded for '.$type.' #'.$id.'.','document_attachment',(int)$pdo->lastInsertId());
    flash('success','Supporting document uploaded.');
  }catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/document-attachments.php?type='.$type.'&id='.$id);
}
$stmt=$pdo->prepare('SELECT da.*,u.email uploader_email FROM '.table('document_attachments').' da LEFT JOIN '.table('users').' u ON u.id=da.uploaded_by WHERE da.document_type=? AND da.document_id=? ORDER BY da.created_at DESC,da.id DESC');
$stmt->execute([$type,$id]);$rows=$stmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Supporting Evidence</div><h2 class="h4 mb-1">Document Attachments</h2><p class="text-secondary mb-0"><?php echo esc(ucwords(str_replace('_',' ',$type)).' #'.$id); ?></p></div><a class="btn btn-outline-secondary" href="<?php echo esc($docUrl); ?>">Back to Document</a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" enctype="multipart/form-data" class="card-admin p-4"><input type="hidden" name="type" value="<?php echo esc($type); ?>"><input type="hidden" name="id" value="<?php echo (int)$id; ?>"><div class="erp-kicker">Upload Evidence</div><h2 class="h5 mb-3">Add Attachment</h2><label class="form-label">File</label><input class="form-control mb-3" type="file" name="attachment_file" required><div class="small text-secondary mb-3">Allowed: PDF, JPG, PNG, WEBP, DOCX, XLSX, TXT. Maximum 15 MB.</div><label class="form-label">Notes</label><textarea class="form-control mb-3" rows="4" name="notes" placeholder="Invoice scan, delivery proof, inspection note..."></textarea><button class="btn btn-brand w-100">Upload Document</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Evidence Register</div><h2 class="h5 mb-0">Attached Files</h2></div></div><table class="table align-middle"><thead><tr><th>File</th><th>Type</th><th>Size</th><th>Uploaded By</th><th>Date</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['file_name']); ?></strong><div class="small text-secondary"><?php echo esc($row['notes']?:''); ?></div></td><td><?php echo esc($row['mime_type']); ?></td><td><?php echo number_format((int)$row['file_size']/1024,1); ?> KB</td><td><?php echo esc($row['uploader_email']?:'System'); ?></td><td><?php echo esc($row['created_at']); ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo esc(documentAttachmentUrl($row['stored_path'])); ?>">Open</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No attachments uploaded yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>