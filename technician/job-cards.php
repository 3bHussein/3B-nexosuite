<?php
require_once dirname(__DIR__) . '/includes/functions.php';
employeePortalGuard();$user=currentUser();$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $jobId=(int)$_POST['job_card_id'];
    $note=trim((string)($_POST['note']??''));$status=trim((string)($_POST['status']??''));
    if($note!==''){$pdo->prepare('INSERT INTO '.table('technician_portal_notes').' (job_card_id,technician_user_id,note_type,note) VALUES (?,?,?,?)')->execute([$jobId,$user['id'],trim((string)($_POST['note_type']??'progress')),$note]);}
    if($status!==''){$pdo->prepare('UPDATE '.table('job_cards').' SET status=? WHERE id=? AND technician_user_id=?')->execute([$status,$jobId,$user['id']]);}
    flash('success','Job updated.');
  }catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(SITE_URL.'/technician/job-cards.php');
}
$stmt=$pdo->prepare('SELECT * FROM '.table('job_cards').' WHERE technician_user_id=? ORDER BY FIELD(status,"in_progress","diagnosis","waiting_parts","completed","invoiced"),created_at DESC LIMIT 100');$stmt->execute([$user['id']]);$jobs=$stmt->fetchAll();
siteHeader('My Job Cards','login');
?>
<h1 class="mb-4">My Job Cards</h1><div class="row g-4"><?php foreach($jobs as $j): ?><div class="col-lg-6"><div class="form-card h-100"><div class="d-flex justify-content-between"><h2 class="h5"><?php echo esc($j['job_card_number']); ?></h2><span class="badge bg-<?php echo esc(statusTone($j['status'])); ?>"><?php echo esc($j['status']); ?></span></div><p class="text-secondary mb-2"><?php echo esc($j['customer_name']); ?> · <?php echo esc($j['vehicle_make'].' '.$j['vehicle_model'].' '.$j['plate_number']); ?></p><p><strong>Complaint:</strong> <?php echo esc($j['complaint']); ?></p><form method="post"><input type="hidden" name="job_card_id" value="<?php echo (int)$j['id']; ?>"><label class="form-label">Status</label><select class="form-select mb-2" name="status"><option value="">No change</option><option value="diagnosis">Diagnosis</option><option value="in_progress">In Progress</option><option value="waiting_parts">Waiting Parts</option><option value="completed">Completed</option></select><label class="form-label">Progress Note</label><textarea class="form-control mb-2" name="note" rows="3"></textarea><button class="btn btn-brand">Update</button></form></div></div><?php endforeach; ?><?php if(!$jobs): ?><div class="col-12"><div class="form-card text-secondary">No assigned job cards.</div></div><?php endif; ?></div>
<?php siteFooter(); ?>