<?php
$pageTitle='Offline Job Cards';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('offline_job_cards');
$pdo=getDB();
$jobs=$pdo->query('SELECT id,job_card_number,customer_name,plate_number,status FROM '.table('job_cards').' ORDER BY created_at DESC LIMIT 300')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='draft'){
      if(setting('mobile_offline_enabled','1')!=='1'){throw new RuntimeException('Offline mode is disabled in settings.');}
      $payload=['complaint'=>trim((string)$_POST['complaint']),'diagnosis'=>trim((string)$_POST['diagnosis']),'parts'=>trim((string)$_POST['parts']),'mobile_notes'=>trim((string)$_POST['mobile_notes']),'created_at'=>date('c')];
      createOfflineJobDraft($pdo,(int)($_POST['job_card_id']??0)?:null,$payload,trim((string)$_POST['device_id']));
      flash('success','Offline draft saved.');
    }elseif($action==='sync'){
      syncOfflineJobDraft($pdo,(int)$_POST['id']);flash('success','Offline draft marked synced.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'offline-job-cards']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/offline-job-cards.php');
}
$drafts=$pdo->query('SELECT d.*,u.email user_email,jc.job_card_number FROM '.table('offline_job_card_drafts').' d LEFT JOIN '.table('users').' u ON u.id=d.user_id LEFT JOIN '.table('job_cards').' jc ON jc.id=d.job_card_id ORDER BY d.created_at DESC LIMIT 200')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Offline-first Field Work</div><h2 class="h4 mb-1">Offline Job Cards</h2><p class="text-secondary mb-0">Capture job-card notes while offline, then review and mark synced when data is safely transferred.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/technician-mobile.php"><?php echo t('Technician Mobile', 'موبايل الفني'); ?></a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="draft"><h2 class="h5 mb-3">Create Offline Draft</h2><label class="form-label">Job Card</label><select class="form-select mb-2" name="job_card_id"><option value="0">New / not linked</option><?php foreach($jobs as $job): ?><option value="<?php echo (int)$job['id']; ?>"><?php echo esc($job['job_card_number'].' · '.$job['customer_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="device_id" placeholder="Device ID / phone name"><textarea class="form-control mb-2" name="complaint" rows="2" placeholder="Complaint"></textarea><textarea class="form-control mb-2" name="diagnosis" rows="2" placeholder="Diagnosis"></textarea><textarea class="form-control mb-2" name="parts" rows="2" placeholder="Parts used"></textarea><textarea class="form-control mb-3" name="mobile_notes" rows="3" placeholder="Mobile notes"></textarea><button class="btn btn-brand w-100">Save Draft</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Draft</th><th>Job</th><th>Device</th><th>Status</th><th>Payload</th><th></th></tr></thead><tbody><?php foreach($drafts as $d): ?><tr><td><strong><?php echo esc($d['draft_number']); ?></strong><div class="small text-secondary"><?php echo esc($d['user_email'].' · '.$d['created_at']); ?></div></td><td><?php echo esc($d['job_card_number']?:'New job'); ?></td><td><?php echo esc($d['device_id']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($d['sync_status'])); ?>"><?php echo esc($d['sync_status']); ?></span></td><td><code><?php echo esc(substr((string)$d['draft_payload'],0,140)); ?></code></td><td><?php if($d['sync_status']!=='completed_sync'): ?><form method="post"><input type="hidden" name="action" value="sync"><input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>"><button class="btn btn-sm btn-success">Mark Synced</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$drafts): ?><tr><td colspan="6" class="text-secondary">No offline drafts yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>