<?php
$pageTitle='Technician Dispatch';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('technician_portal');
$pdo=getDB();
$techs=$pdo->query('SELECT id,email,first_name,last_name FROM '.table('users').' WHERE status="active" AND can_login_erp=1 ORDER BY first_name,email')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$pdo->prepare('UPDATE '.table('job_cards').' SET technician_user_id=?,status=CASE WHEN status="draft" THEN "diagnosis" ELSE status END WHERE id=?')->execute([(int)$_POST['technician_user_id'],(int)$_POST['job_card_id']]);flash('success','Technician assigned.');}catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/technician-dispatch.php');
}
$jobs=$pdo->query('SELECT jc.*,u.email technician_email FROM '.table('job_cards').' jc LEFT JOIN '.table('users').' u ON u.id=jc.technician_user_id WHERE jc.status IN ("draft","diagnosis","in_progress","waiting_parts") ORDER BY jc.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Mobile Technician Dispatch</div><h2 class="h4 mb-1">Technician Dispatch</h2><p class="text-secondary mb-0">Assign technicians and monitor active workshop jobs for mobile execution.</p></div><a class="btn btn-outline-primary" target="_blank" href="<?php echo esc(SITE_URL); ?>/technician/dashboard.php">Technician Portal</a></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Job Card</th><th>Vehicle / Customer</th><th>Status</th><th>Technician</th><th>Assign</th></tr></thead><tbody><?php foreach($jobs as $j): ?><tr><td><strong><?php echo esc($j['job_card_number']); ?></strong><div class="small text-secondary"><?php echo esc($j['created_at']); ?></div></td><td><?php echo esc($j['customer_name']); ?><div class="small text-secondary"><?php echo esc($j['vehicle_make'].' '.$j['vehicle_model'].' · '.$j['plate_number']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($j['status'])); ?>"><?php echo esc($j['status']); ?></span></td><td><?php echo esc($j['technician_email']?:'Unassigned'); ?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="job_card_id" value="<?php echo (int)$j['id']; ?>"><select class="form-select form-select-sm" name="technician_user_id"><?php foreach($techs as $t): ?><option value="<?php echo (int)$t['id']; ?>" <?php echo (int)$j['technician_user_id']===(int)$t['id']?'selected':''; ?>><?php echo esc($t['email']); ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-outline-primary">Assign</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>