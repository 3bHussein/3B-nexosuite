<?php
$pageTitle='Document Versions';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('document_versions');
$pdo=getDB();
$docs=$pdo->query('SELECT id,document_number,title,version_number FROM '.table('document_library').' ORDER BY created_at DESC LIMIT 300')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $file=dmsUploadDocumentFile('document_file');
    if(!$file){throw new RuntimeException('Please upload a version file.');}
    createDocumentVersion($pdo,(int)$_POST['document_library_id'],trim((string)$_POST['version_number']),$file,trim((string)$_POST['change_summary']));
    flash('success','Document version uploaded.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'document-versions']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/document-versions.php');
}
$versions=$pdo->query('SELECT v.*,d.document_number,d.title,u.email uploaded_email FROM '.table('document_versions').' v LEFT JOIN '.table('document_library').' d ON d.id=v.document_library_id LEFT JOIN '.table('users').' u ON u.id=v.uploaded_by ORDER BY v.created_at DESC LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Version Control</div><h2 class="h4 mb-1">Document Versions</h2><p class="text-secondary mb-0">Upload document revisions and preserve change summaries.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" enctype="multipart/form-data" class="card-admin p-4"><h2 class="h5 mb-3">Upload New Version</h2><select class="form-select mb-2" name="document_library_id"><?php foreach($docs as $d): ?><option value="<?php echo (int)$d['id']; ?>"><?php echo esc($d['document_number'].' · '.$d['title'].' · v'.$d['version_number']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="version_number" placeholder="2.0" required><textarea class="form-control mb-2" name="change_summary" rows="4" placeholder="What changed?"></textarea><input class="form-control mb-3" type="file" name="document_file" required><button class="btn btn-brand w-100">Upload Version</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Document</th><th>Version</th><th>File</th><th>Change</th><th>Status</th></tr></thead><tbody><?php foreach($versions as $v): ?><tr><td><strong><?php echo esc($v['document_number']); ?></strong><div class="small text-secondary"><?php echo esc($v['title']); ?></div></td><td><?php echo esc($v['version_number']); ?></td><td><?php echo esc($v['file_name']); ?><div class="small text-secondary"><?php echo esc($v['uploaded_email'].' · '.$v['created_at']); ?></div></td><td><?php echo esc($v['change_summary']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($v['status'])); ?>"><?php echo esc($v['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>