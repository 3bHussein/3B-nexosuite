<?php
$pageTitle='Technician Timesheets';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('technician_timesheets');
$pdo=getDB();
$scope=operationalScope($pdo);
$users=$pdo->query('SELECT id,email,first_name,last_name FROM '.table('users').' WHERE status="active" AND (can_login_erp=1 OR role="admin") ORDER BY first_name,email ASC LIMIT 300')->fetchAll();
$jobs=$pdo->query('SELECT id,job_card_number,customer_name,status FROM '.table('job_cards').' WHERE status IN ("draft","diagnosis","in_progress","waiting_parts","completed") ORDER BY created_at DESC LIMIT 300')->fetchAll();
$projects=$pdo->query('SELECT id,project_number,project_name FROM '.table('projects').' WHERE status IN ("planning","active") ORDER BY created_at DESC LIMIT 300')->fetchAll();
$costCenters=$pdo->query('SELECT id,cost_center_code,cost_center_name FROM '.table('cost_centers').' WHERE status="active" ORDER BY cost_center_code ASC')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??'create'));
  try{
    if($action==='approve'){
      $id=(int)($_POST['id']??0);$user=currentUser();$userId=(int)($user['id']??0)?:null;
      $pdo->prepare('UPDATE '.table('technician_timesheets').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=?')->execute([$userId,$id]);
      $row=$pdo->prepare('SELECT job_card_id,project_id,cost_center_id,work_date FROM '.table('technician_timesheets').' WHERE id=?');$row->execute([$id]);$r=$row->fetch();
      if($r && !empty($r['job_card_id'])){jobCardRecalculate($pdo,(int)$r['job_card_id']);}
      if($r && !empty($r['project_id'])){projectRecalculate($pdo,(int)$r['project_id']);}
      if($r && !empty($r['cost_center_id'])){updateBudgetActuals($pdo,(int)$r['cost_center_id'],(int)substr((string)$r['work_date'],0,4),(int)substr((string)$r['work_date'],5,2));}
      flash('success','Timesheet approved.');
    }else{
      $hours=max(0,(float)($_POST['hours']??0));$cost=max(0,(float)($_POST['hourly_cost']??setting('default_technician_hourly_cost','45')));
      if($hours<=0){throw new RuntimeException('Hours must be greater than zero.');}
      $projectId=(int)($_POST['project_id']??0)?:null;$jobId=(int)($_POST['job_card_id']??0)?:null;$cc=(int)($_POST['cost_center_id']??0)?:null;
      $date=trim((string)($_POST['work_date']??date('Y-m-d')))?:date('Y-m-d');
      $stmt=$pdo->prepare('INSERT INTO '.table('technician_timesheets').' (company_id,branch_id,cost_center_id,technician_user_id,job_card_id,project_id,work_date,hours,billable,hourly_cost,cost_amount,status,description) VALUES (?,?,?,?,?,?,?,?,?,?,?,"draft",?)');
      $stmt->execute([(int)$scope['company_id']?:null,(int)$scope['branch_id']?:null,$cc,(int)$_POST['technician_user_id'],$jobId,$projectId,$date,$hours,!empty($_POST['billable'])?1:0,$cost,round($hours*$cost,2),trim((string)($_POST['description']??''))]);
      $id=(int)$pdo->lastInsertId();
      logActivity($pdo,'Timesheets','timesheet_created','Technician timesheet #'.$id.' recorded.','timesheet',$id);
      flash('success','Timesheet recorded.');
    }
  }catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/technician-timesheets.php');
}
$rows=$pdo->query('SELECT ts.*,u.email technician_email,jc.job_card_number,p.project_number,cc.cost_center_code FROM '.table('technician_timesheets').' ts LEFT JOIN '.table('users').' u ON u.id=ts.technician_user_id LEFT JOIN '.table('job_cards').' jc ON jc.id=ts.job_card_id LEFT JOIN '.table('projects').' p ON p.id=ts.project_id LEFT JOIN '.table('cost_centers').' cc ON cc.id=ts.cost_center_id ORDER BY ts.work_date DESC,ts.id DESC LIMIT 300')->fetchAll();
$totalHours=0;$totalCost=0;foreach($rows as $r){$totalHours+=(float)$r['hours'];$totalCost+=(float)$r['cost_amount'];}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Labor Cost Control</div><h2 class="h4 mb-1">Technician Timesheets</h2><p class="text-secondary mb-0">Capture labor cost against job cards, projects, and cost centers.</p></div></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Hours</div><div class="metric-sm"><?php echo number_format($totalHours,2); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Labor Cost</div><div class="metric-sm"><?php echo money($totalCost); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Entries</div><div class="metric-sm"><?php echo count($rows); ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5">Record Time</h2><label class="form-label">Technician</label><select class="form-select mb-2" name="technician_user_id" required><?php foreach($users as $u): ?><option value="<?php echo (int)$u['id']; ?>"><?php echo esc($u['email']); ?></option><?php endforeach; ?></select><label class="form-label">Job Card</label><select class="form-select mb-2" name="job_card_id"><option value="0">No job card</option><?php foreach($jobs as $j): ?><option value="<?php echo (int)$j['id']; ?>"><?php echo esc($j['job_card_number'].' · '.$j['customer_name']); ?></option><?php endforeach; ?></select><label class="form-label">Project</label><select class="form-select mb-2" name="project_id"><option value="0">No project</option><?php foreach($projects as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo esc($p['project_number'].' · '.$p['project_name']); ?></option><?php endforeach; ?></select><label class="form-label">Cost Center</label><select class="form-select mb-2" name="cost_center_id"><option value="0">No cost center</option><?php foreach($costCenters as $cc): ?><option value="<?php echo (int)$cc['id']; ?>"><?php echo esc($cc['cost_center_code'].' · '.$cc['cost_center_name']); ?></option><?php endforeach; ?></select><div class="row g-2"><div class="col-6"><label class="form-label">Date</label><input class="form-control" type="date" name="work_date" value="<?php echo esc(date('Y-m-d')); ?>"></div><div class="col-6"><label class="form-label">Hours</label><input class="form-control" type="number" step="0.01" name="hours" required></div><div class="col-6"><label class="form-label">Hourly Cost</label><input class="form-control" type="number" step="0.01" name="hourly_cost" value="<?php echo esc(setting('default_technician_hourly_cost','45')); ?>"></div><div class="col-6"><label class="form-label">Billable</label><select class="form-select" name="billable"><option value="1">Yes</option><option value="0">No</option></select></div></div><textarea class="form-control my-2" name="description" rows="3" placeholder="Work performed"></textarea><button class="btn btn-brand w-100">Save Timesheet</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Time Register</div><h2 class="h5 mb-0">Technician Labor Entries</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Technician</th><th>Document</th><th>Hours</th><th>Cost</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo esc($row['work_date']); ?></td><td><?php echo esc($row['technician_email']); ?></td><td><?php echo esc(($row['job_card_number']?:$row['project_number']?:'General').' · '.($row['cost_center_code']?:'')); ?></td><td><?php echo number_format((float)$row['hours'],2); ?></td><td><?php echo money($row['cost_amount']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td><?php if($row['status']==='draft'): ?><form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="btn btn-sm btn-outline-success">Approve</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>