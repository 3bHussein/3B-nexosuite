<?php
$pageTitle='Document Expiry Alerts';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('document_expiry_alerts');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'scan');
    if($action==='scan'){$created=generateDocumentExpiryAlerts($pdo);flash('success','Expiry scan completed. New alerts: '.$created);}
    elseif($action==='resolve'){$pdo->prepare('UPDATE '.table('document_expiry_alerts').' SET status="resolved",resolved_by=?,resolved_at=NOW() WHERE id=?')->execute([(int)(currentUser()['id']??0)?:null,(int)$_POST['id']]);flash('success','Expiry alert resolved.');}
    else{createDocumentExpiryAlert($pdo,(int)$_POST['document_library_id'],(int)$_POST['days_before']);flash('success','Expiry alert created.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'document-expiry-alerts']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/document-expiry-alerts.php');
}
$docs=$pdo->query('SELECT id,document_number,title,expiry_date FROM '.table('document_library').' WHERE expiry_date IS NOT NULL ORDER BY expiry_date ASC LIMIT 200')->fetchAll();
$alerts=$pdo->query('SELECT a.*,d.document_number,d.title,d.expiry_date FROM '.table('document_expiry_alerts').' a LEFT JOIN '.table('document_library').' d ON d.id=a.document_library_id ORDER BY FIELD(a.status,"open","resolved"),a.alert_date ASC,a.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Renewal Control</div><h2 class="h4 mb-1">Document Expiry Alerts</h2><p class="text-secondary mb-0">Track expiring trade licenses, contracts, certificates, employee documents and compliance evidence.</p></div><form method="post"><input type="hidden" name="action" value="scan"><button class="btn btn-brand">Run Expiry Scan</button></form></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="create"><h2 class="h5 mb-3">Create Alert</h2><select class="form-select mb-2" name="document_library_id"><?php foreach($docs as $d): ?><option value="<?php echo (int)$d['id']; ?>"><?php echo esc($d['document_number'].' · '.$d['title'].' · '.$d['expiry_date']); ?></option><?php endforeach; ?></select><input class="form-control mb-3" type="number" name="days_before" value="<?php echo (int)setting('document_default_expiry_alert_days','30'); ?>"><button class="btn btn-outline-primary w-100">Create Alert</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Alert</th><th>Document</th><th>Expiry</th><th>Message</th><th>Status</th></tr></thead><tbody><?php foreach($alerts as $a): ?><tr><td><strong><?php echo esc($a['alert_number']); ?></strong><div class="small text-secondary"><?php echo esc($a['alert_date'].' · '.$a['days_before'].' days'); ?></div></td><td><?php echo esc($a['document_number']); ?><div class="small text-secondary"><?php echo esc($a['title']); ?></div></td><td><?php echo esc($a['expiry_date']); ?></td><td><?php echo esc($a['message']); ?></td><td><?php if($a['status']==='open'): ?><form method="post"><input type="hidden" name="action" value="resolve"><input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>"><button class="btn btn-sm btn-success">Resolve</button></form><?php else: ?><span class="badge bg-success">resolved</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>