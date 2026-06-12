<?php
$pageTitle='Shift Scheduling';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('shift_scheduling');
$pdo=getDB();$employees=$pdo->query('SELECT * FROM '.table('employees').' WHERE status="active" ORDER BY first_name,last_name')->fetchAll();$shifts=$pdo->query('SELECT * FROM '.table('employee_shift_templates').' ORDER BY shift_code')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'template');
    if($action==='assign'){
      $pdo->prepare('INSERT INTO '.table('employee_shift_assignments').' (employee_id,employee_shift_template_id,work_date,status,notes) VALUES (?,?,?,"scheduled",?) ON DUPLICATE KEY UPDATE employee_shift_template_id=VALUES(employee_shift_template_id),status="scheduled",notes=VALUES(notes)')->execute([(int)$_POST['employee_id'],(int)$_POST['shift_id'],trim((string)$_POST['work_date']),trim((string)$_POST['notes'])]);
      flash('success','Shift assigned.');
    }else{
      $code=trim((string)$_POST['shift_code']);if($code===''){$code=nextScopedDocumentNumber($pdo,'shift_template',setting('shift_template_prefix','SHIFT'),operationalScope($pdo));}
      $pdo->prepare('INSERT INTO '.table('employee_shift_templates').' (shift_code,shift_name,start_time,end_time,break_minutes,standard_hours,status,notes) VALUES (?,?,?,?,?,?,"active",?)')->execute([$code,trim((string)$_POST['shift_name']),trim((string)$_POST['start_time'])?:null,trim((string)$_POST['end_time'])?:null,max(0,(int)$_POST['break_minutes']),max(0,(float)$_POST['standard_hours']),trim((string)$_POST['notes'])]);
      flash('success','Shift template created.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'shift-scheduling']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/shift-scheduling.php');
}
$assignments=$pdo->query('SELECT a.*,CONCAT(e.first_name," ",e.last_name) employee_name,e.employee_code,s.shift_code,s.shift_name FROM '.table('employee_shift_assignments').' a LEFT JOIN '.table('employees').' e ON e.id=a.employee_id LEFT JOIN '.table('employee_shift_templates').' s ON s.id=a.employee_shift_template_id ORDER BY a.work_date DESC,a.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Workforce Planning</div><h2 class="h4 mb-1">Shift Scheduling</h2><p class="text-secondary mb-0">Create shift templates and assign employees by work date.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Create Shift Template</h2><input class="form-control mb-2" name="shift_code" placeholder="Auto or SHIFT-A"><input class="form-control mb-2" name="shift_name" placeholder="Morning Shift"><div class="row g-2"><div class="col-6"><input class="form-control" type="time" name="start_time"></div><div class="col-6"><input class="form-control" type="time" name="end_time"></div></div><input class="form-control my-2" type="number" name="break_minutes" value="60"><input class="form-control mb-2" type="number" step="0.01" name="standard_hours" value="8"><textarea class="form-control mb-3" name="notes" rows="2"></textarea><button class="btn btn-brand w-100">Create Shift</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="assign"><h2 class="h5 mb-3">Assign Shift</h2><select class="form-select mb-2" name="employee_id"><?php foreach($employees as $e): ?><option value="<?php echo (int)$e['id']; ?>"><?php echo esc($e['employee_code'].' · '.$e['first_name'].' '.$e['last_name']); ?></option><?php endforeach; ?></select><select class="form-select mb-2" name="shift_id"><?php foreach($shifts as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo esc($s['shift_code'].' · '.$s['shift_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" type="date" name="work_date" required><textarea class="form-control mb-3" name="notes" rows="2"></textarea><button class="btn btn-outline-primary w-100">Assign</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Assignments</h2><table class="table"><thead><tr><th>Date</th><th>Employee</th><th>Shift</th><th>Status</th></tr></thead><tbody><?php foreach($assignments as $a): ?><tr><td><?php echo esc($a['work_date']); ?></td><td><?php echo esc($a['employee_code'].' · '.$a['employee_name']); ?></td><td><?php echo esc($a['shift_code'].' · '.$a['shift_name']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($a['status'])); ?>"><?php echo esc($a['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>