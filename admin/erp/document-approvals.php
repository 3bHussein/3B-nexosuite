<?php
$pageTitle='Document Approvals';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('document_approvals');
$pdo=getDB();
$docs=$pdo->query('SELECT id,document_number,title,approval_status FROM '.table('document_library').' ORDER BY created_at DESC LIMIT 300')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'request');
    if($action==='decision'){decideDocumentApproval($pdo,(int)$_POST['approval_id'],trim((string)$_POST['decision']),trim((string)$_POST['decision_notes']));flash('success','Document approval updated.');}
    else{createDocumentApproval($pdo,(int)$_POST['document_library_id'],trim((string)$_POST['approval_type']),trim((string)$_POST['notes']));flash('success','Document approval requested.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'document-approvals']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/document-approvals.php');
}
$approvals=$pdo->query('SELECT a.*,d.document_number,d.title,u.email requester_email FROM '.table('document_approvals').' a LEFT JOIN '.table('document_library').' d ON d.id=a.document_library_id LEFT JOIN '.table('users').' u ON u.id=a.requested_by ORDER BY FIELD(a.status,"pending","approved","rejected"),a.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Controlled Review</div><h2 class="h4 mb-1">Document Approvals</h2><p class="text-secondary mb-0">Maker-checker review process for controlled documents.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Request Approval</h2><select class="form-select mb-2" name="document_library_id"><?php foreach($docs as $d): ?><option value="<?php echo (int)$d['id']; ?>"><?php echo esc($d['document_number'].' · '.$d['title'].' · '.$d['approval_status']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="approval_type" value="document_review"><textarea class="form-control mb-3" name="notes" rows="4" placeholder="Review notes"></textarea><button class="btn btn-brand w-100">Request Approval</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Approval</th><th>Document</th><th>Requester</th><th>Status</th><th>Decision</th></tr></thead><tbody><?php foreach($approvals as $a): ?><tr><td><strong><?php echo esc($a['approval_number']); ?></strong><div class="small text-secondary"><?php echo esc($a['approval_type'].' · '.$a['requested_at']); ?></div></td><td><?php echo esc($a['document_number']); ?><div class="small text-secondary"><?php echo esc($a['title']); ?></div></td><td><?php echo esc($a['requester_email']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($a['status'])); ?>"><?php echo esc($a['status']); ?></span></td><td><?php if($a['status']==='pending'): ?><form method="post" class="d-flex gap-1"><input type="hidden" name="action" value="decision"><input type="hidden" name="approval_id" value="<?php echo (int)$a['id']; ?>"><select class="form-select form-select-sm" name="decision"><option value="approved">Approve</option><option value="rejected">Reject</option></select><input class="form-control form-control-sm" name="decision_notes" placeholder="Notes"><button class="btn btn-sm btn-outline-primary">Save</button></form><?php else: ?><?php echo esc($a['resolved_at']); ?><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>