<?php
$pageTitle='View Project';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('projects');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT p.*,cc.cost_center_code,cc.cost_center_name,c.customer_code,u.email manager_email FROM '.table('projects').' p LEFT JOIN '.table('cost_centers').' cc ON cc.id=p.cost_center_id LEFT JOIN '.table('customers').' c ON c.id=p.customer_id LEFT JOIN '.table('users').' u ON u.id=p.project_manager_user_id WHERE p.id=? LIMIT 1');
$stmt->execute([$id]);$project=$stmt->fetch();
if(!$project){flash('error','Project not found.');redirect(ADMIN_URL.'/erp/projects.php');}
enforceScopeAllowed($pdo,(int)($project['company_id']??0),(int)($project['branch_id']??0),0,false);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('projects').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if($action==='add_cost'){
      $amount=max(0,(float)($_POST['amount']??0));if($amount<=0){throw new RuntimeException('Cost amount must be greater than zero.');}
      $cc=(int)($_POST['cost_center_id']??$current['cost_center_id'])?:null;$date=trim((string)($_POST['entry_date']??date('Y-m-d')))?:date('Y-m-d');$user=currentUser();
      $pdo->prepare('INSERT INTO '.table('project_cost_entries').' (project_id,cost_center_id,reference_type,reference_id,cost_category,description,amount,entry_date,created_by) VALUES (?,?,?,?,?,?,?,?,?)')->execute([$id,$cc,'manual',null,trim((string)($_POST['cost_category']??'General')),trim((string)($_POST['description']??'')),$amount,$date,(int)($user['id']??0)?:null]);
      projectRecalculate($pdo,$id);if($cc){updateBudgetActuals($pdo,$cc,(int)substr($date,0,4),(int)substr($date,5,2));}
      flash('success','Project cost added.');
    }elseif($action==='activate'){
      $pdo->prepare('UPDATE '.table('projects').' SET status="active" WHERE id=? AND status="planning"')->execute([$id]);flash('success','Project activated.');
    }elseif($action==='complete'){
      projectRecalculate($pdo,$id);$pdo->prepare('UPDATE '.table('projects').' SET status="completed" WHERE id=?')->execute([$id]);flash('success','Project completed.');
    }elseif($action==='budget_override'){
      $request=createApprovalRequestForDocument($pdo,'project',$id,'budget_override','Project budget override review.');
      if($request){$pdo->prepare('UPDATE '.table('projects').' SET status="pending_approval" WHERE id=?')->execute([$id]);flash('success','Budget override submitted: '.$request['request_number'].'.');}
      else{flash('error','No budget override approval rule matched.');}
    }
    logActivity($pdo,'Projects','project_'.$action,'Project '.$current['project_number'].' action: '.$action.'.','project',$id);
    $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/view-project.php?id='.$id);
}
projectRecalculate($pdo,$id);$stmt->execute([$id]);$project=$stmt->fetch();
$costs=$pdo->prepare('SELECT pce.*,cc.cost_center_code FROM '.table('project_cost_entries').' pce LEFT JOIN '.table('cost_centers').' cc ON cc.id=pce.cost_center_id WHERE pce.project_id=? ORDER BY pce.entry_date DESC,pce.id DESC');$costs->execute([$id]);$costRows=$costs->fetchAll();
$costCenters=$pdo->query('SELECT id,cost_center_code,cost_center_name FROM '.table('cost_centers').' WHERE status="active" ORDER BY cost_center_code')->fetchAll();
$approval=activeApprovalRequest($pdo,'project',$id,'budget_override');
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Project</div><h2 class="h4 mb-1"><?php echo esc($project['project_number'].' · '.$project['project_name']); ?></h2><p class="text-secondary mb-0"><?php echo esc(($project['customer_code']?:$project['customer_name']?:'Customer').' · '.($project['cost_center_code']?:'No cost center')); ?></p></div><div class="d-flex flex-wrap gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($project['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($project['status'],'_'))); ?></span><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=project&id=<?php echo (int)$project['id']; ?>">Attachments</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/projects.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Budget</div><div class="metric-sm"><?php echo money($project['budget_amount']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Revenue</div><div class="metric-sm"><?php echo money($project['revenue_amount']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Cost</div><div class="metric-sm"><?php echo money($project['cost_amount']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Margin</div><div class="metric-sm <?php echo (float)$project['margin_amount']>=0?'money-positive':'money-negative'; ?>"><?php echo money($project['margin_amount']); ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Cost Ledger</div><h2 class="h5 mb-0">Project Cost Entries</h2></div></div><table class="table"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Cost Center</th><th>Amount</th></tr></thead><tbody><?php foreach($costRows as $row): ?><tr><td><?php echo esc($row['entry_date']); ?></td><td><?php echo esc($row['cost_category']); ?></td><td><?php echo esc($row['description']); ?></td><td><?php echo esc($row['cost_center_code']?:'—'); ?></td><td><?php echo money($row['amount']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4 mb-4"><div class="erp-kicker">Actions</div><div class="d-grid gap-2 mt-3"><?php if($project['status']==='planning'): ?><form method="post"><button class="btn btn-success w-100" name="action" value="activate">Activate Project</button></form><?php endif; ?><?php if(in_array($project['status'],['planning','active'],true)): ?><form method="post"><button class="btn btn-outline-warning w-100" name="action" value="budget_override">Request Budget Override</button></form><?php endif; ?><?php if($approval): ?><a class="btn btn-warning" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$approval['id']; ?>">Open Approval Request</a><?php endif; ?><?php if($project['status']==='active'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="complete">Complete Project</button></form><?php endif; ?></div></div><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="add_cost"><div class="erp-kicker">Add Cost</div><select class="form-select mb-2" name="cost_center_id"><option value="0">Project default</option><?php foreach($costCenters as $cc): ?><option value="<?php echo (int)$cc['id']; ?>" <?php echo (int)$project['cost_center_id']===(int)$cc['id']?'selected':''; ?>><?php echo esc($cc['cost_center_code'].' · '.$cc['cost_center_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="cost_category" placeholder="Labor, Materials, Travel..." value="General"><input class="form-control mb-2" name="description" placeholder="Description"><div class="row g-2"><div class="col-6"><input class="form-control" type="date" name="entry_date" value="<?php echo esc(date('Y-m-d')); ?>"></div><div class="col-6"><input class="form-control" type="number" step="0.01" name="amount" placeholder="Amount"></div></div><button class="btn btn-outline-primary w-100 mt-2">Add Cost</button></form></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>