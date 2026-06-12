<?php
$pageTitle='Leave Requests';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('hr');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(($_POST['form_type']??'')==='create'){
    $stmt=$pdo->prepare('INSERT INTO ' . table('leave_requests') . ' (employee_id,leave_type,start_date,end_date,reason,status) VALUES (?,?,?,?,?,"pending")');
    $stmt->execute([(int)$_POST['employee_id'],$_POST['leave_type'],$_POST['start_date'],$_POST['end_date'],$_POST['reason']]);
    flash('success','Leave request created.');
  } else {
    $stmt=$pdo->prepare('UPDATE ' . table('leave_requests') . ' SET status=?,decision_notes=? WHERE id=?');
    $stmt->execute([$_POST['status'],$_POST['decision_notes']??'',(int)$_POST['id']]);
    $lr=$pdo->prepare('SELECT employee_id,leave_type,start_date FROM '.table('leave_requests').' WHERE id=?');$lr->execute([(int)$_POST['id']]);$leave=$lr->fetch();if($leave){refreshEmployeeLeaveBalance($pdo,(int)$leave['employee_id'],(string)$leave['leave_type'],(int)date('Y',strtotime($leave['start_date']?:date('Y-m-d'))));}
    flash('success','Leave request updated.');
  }
  redirect(ADMIN_URL.'/erp/leave-requests.php');
}
$employees=$pdo->query('SELECT id,CONCAT(first_name," ",last_name," (",employee_code,")") label FROM ' . table('employees') . ' WHERE status="active" ORDER BY first_name,last_name')->fetchAll();
$rows=$pdo->query('SELECT l.*, CONCAT(e.first_name," ",e.last_name) employee_name,e.employee_code FROM ' . table('leave_requests') . ' l LEFT JOIN ' . table('employees') . ' e ON e.id=l.employee_id ORDER BY l.created_at DESC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="form_type" value="create"><h2 class="h5">Create Leave Request</h2><div class="mb-3"><label class="form-label">Employee</label><select class="form-select" name="employee_id" required><?php foreach($employees as $employee): ?><option value="<?php echo (int)$employee['id']; ?>"><?php echo esc($employee['label']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Leave Type</label><select class="form-select" name="leave_type"><option>Annual Leave</option><option>Sick Leave</option><option>Emergency Leave</option><option>Unpaid Leave</option></select></div><div class="row g-2"><div class="col-md-6 mb-3"><label class="form-label">Start</label><input class="form-control" type="date" name="start_date" required></div><div class="col-md-6 mb-3"><label class="form-label">End</label><input class="form-control" type="date" name="end_date" required></div></div><div class="mb-3"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3"></textarea></div><button class="btn btn-brand">Create Request</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Reason</th><th>Decision</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo esc($row['employee_name']); ?><div class="small-muted"><?php echo esc($row['employee_code']); ?></div></td><td><?php echo esc($row['leave_type']); ?></td><td><?php echo esc($row['start_date'].' - '.$row['end_date']); ?></td><td><?php echo esc($row['reason']); ?></td><td><form method="post" class="d-grid gap-2"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><select class="form-select form-select-sm" name="status"><option <?php echo $row['status']==='pending'?'selected':''; ?>>pending</option><option <?php echo $row['status']==='approved'?'selected':''; ?>>approved</option><option <?php echo $row['status']==='rejected'?'selected':''; ?>>rejected</option></select><input class="form-control form-control-sm" name="decision_notes" placeholder="Decision notes" value="<?php echo esc($row['decision_notes']??''); ?>"><button class="btn btn-brand btn-sm">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>