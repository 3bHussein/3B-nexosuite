<?php
$pageTitle='Error Log Viewer';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('production_error_log_viewer');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$ids=array_map('intval',(array)($_POST['ids']??[]));if($ids){$in=implode(',',array_fill(0,count($ids),'?'));$pdo->prepare('UPDATE '.table('system_error_logs').' SET status="resolved",resolved_at=NOW(),resolved_by=? WHERE id IN ('.$in.')')->execute(array_merge([(int)(currentUser()['id']??0)?:null],$ids));}flash('success','Selected errors resolved.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'error-log-viewer']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/error-log-viewer.php');
}
$filter=(string)($_GET['status']??'open');$where=$filter==='all'?'1=1':'status='.$pdo->quote($filter);
$errors=$pdo->query('SELECT * FROM '.table('system_error_logs').' WHERE '.$where.' ORDER BY created_at DESC LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Runtime Stability</div><h2 class="h4 mb-1">Production Error Log Viewer</h2><p class="text-secondary mb-0">Review PHP/runtime errors captured by the ERP and close resolved issues.</p></div><form class="d-flex gap-2" method="get"><select class="form-select" name="status"><option value="open" <?php echo $filter==='open'?'selected':''; ?>>Open</option><option value="resolved" <?php echo $filter==='resolved'?'selected':''; ?>>Resolved</option><option value="all" <?php echo $filter==='all'?'selected':''; ?>>All</option></select><button class="btn btn-outline-primary">Filter</button></form></div>
<form method="post"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th></th><th>Severity</th><th>Message</th><th>File</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($errors as $e): ?><tr><td><input type="checkbox" name="ids[]" value="<?php echo (int)$e['id']; ?>"></td><td><span class="badge bg-<?php echo esc(statusTone($e['severity'])); ?>"><?php echo esc($e['severity']); ?></span></td><td><strong><?php echo esc($e['message']); ?></strong><div class="small text-secondary"><?php echo esc($e['context_json']); ?></div></td><td><?php echo esc($e['file_path']); ?>:<?php echo (int)$e['line_number']; ?></td><td><?php echo esc($e['status']); ?></td><td><?php echo esc($e['created_at']); ?></td></tr><?php endforeach; ?></tbody></table></div><button class="btn btn-success mt-3">Resolve Selected</button></form>
<?php include dirname(__DIR__).'/footer.php'; ?>