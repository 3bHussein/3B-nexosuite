<?php
$pageTitle='Backup & Restore';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('backup_restore');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create_backup'){
  try{
    $backup=createDatabaseBackup($pdo,trim((string)($_POST['notes']??'')));
    createNotification($pdo,['role_slug'=>'erp-manager','title'=>'Database backup created','message'=>'Backup '.$backup['backup_number'].' was created successfully.','severity'=>'success','link_url'=>ADMIN_URL.'/erp/backup-restore.php']);
    flash('success','Backup created: '.$backup['backup_number']);
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'backup-restore']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/backup-restore.php');
}
$rows=$pdo->query('SELECT bj.*,u.email created_by_email FROM '.table('backup_jobs').' bj LEFT JOIN '.table('users').' u ON u.id=bj.created_by ORDER BY bj.created_at DESC,bj.id DESC LIMIT 100')->fetchAll();
$totalSize=0;foreach($rows as $r){$totalSize+=(int)$r['file_size'];}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Operational Recovery</div><h2 class="h4 mb-1">Backup & Restore</h2><p class="text-secondary mb-0">Create SQL database backups and track backup history. Restore should be performed carefully on staging before production.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/deployment-checklist.php"><?php echo t('Deployment Checklist', 'قائمة النشر'); ?></a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="create_backup"><div class="erp-kicker">Create Backup</div><h2 class="h5 mb-3">Database SQL Backup</h2><div class="small text-secondary mb-3">Backup directory: <code>uploads/<?php echo esc(setting('backup_directory','backups')); ?></code></div><label class="form-label">Notes</label><textarea class="form-control mb-3" name="notes" rows="4" placeholder="Before update, before migration, weekly backup..."></textarea><button class="btn btn-brand w-100">Create Backup Now</button></form><div class="card-admin p-4 mt-4"><div class="erp-kicker">Storage</div><div class="metric-sm"><?php echo number_format($totalSize/1048576,2); ?> MB</div><div class="small text-secondary">Total tracked backup file size</div></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Backup Register</div><h2 class="h5 mb-0">Created Backups</h2></div></div><table class="table align-middle"><thead><tr><th>Backup</th><th>File</th><th>Size</th><th>Status</th><th>Created By</th><th>Date</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['backup_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['notes']); ?></div></td><td><?php echo esc($row['file_name']); ?></td><td><?php echo number_format((int)$row['file_size']/1048576,2); ?> MB</td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td><?php echo esc($row['created_by_email']?:'System'); ?></td><td><?php echo esc($row['created_at']); ?></td><td class="text-end"><?php if(!empty($row['file_path'])): ?><a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo esc(UPLOADS_URL.'/'.ltrim($row['file_path'],'/')); ?>">Download</a><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No backups created yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>