<?php
$pageTitle='Client Onboarding Checklist';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('client_onboarding_checklist');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'create');
    if($action==='complete'){$pdo->prepare('UPDATE '.table('client_onboarding_checklist_items').' SET status="completed",completed_by=?,completed_at=NOW() WHERE id=?')->execute([(int)(currentUser()['id']??0)?:null,(int)$_POST['id']]);flash('success','Onboarding item completed.');}
    else{p35CreateOnboardingChecklist($pdo,trim((string)$_POST['client_name']),trim((string)$_POST['package_name']),trim((string)$_POST['go_live_date']));flash('success','Onboarding checklist created.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'client-onboarding-checklist']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/client-onboarding-checklist.php');
}
$lists=$pdo->query('SELECT * FROM '.table('client_onboarding_checklists').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
$items=$pdo->query('SELECT i.*,c.onboarding_number,c.client_name FROM '.table('client_onboarding_checklist_items').' i LEFT JOIN '.table('client_onboarding_checklists').' c ON c.id=i.client_onboarding_checklist_id ORDER BY c.created_at DESC,i.sort_order LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Implementation Workflow</div><h2 class="h4 mb-1">Client Onboarding Checklist</h2><p class="text-secondary mb-0">Discovery, setup, data import, training and go-live checklist for each client.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Create Checklist</h2><input class="form-control mb-2" name="client_name" placeholder="Client name"><input class="form-control mb-2" name="package_name" placeholder="Package"><input class="form-control mb-3" type="date" name="go_live_date"><button class="btn btn-brand w-100">Create</button></form><div class="table-wrap table-responsive mt-4"><h2 class="h6 mb-3">Checklists</h2><table class="table table-sm"><tbody><?php foreach($lists as $l): ?><tr><td><strong><?php echo esc($l['onboarding_number']); ?></strong><div class="small text-secondary"><?php echo esc($l['client_name'].' · '.$l['package_name']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Client</th><th>Phase</th><th>Task</th><th>Owner</th><th>Status</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?php echo esc($i['client_name']); ?><div class="small text-secondary"><?php echo esc($i['onboarding_number']); ?></div></td><td><?php echo esc($i['phase']); ?></td><td><?php echo esc($i['item_title']); ?></td><td><?php echo esc($i['owner_role']); ?></td><td><?php if($i['status']==='open'): ?><form method="post"><input type="hidden" name="action" value="complete"><input type="hidden" name="id" value="<?php echo (int)$i['id']; ?>"><button class="btn btn-sm btn-success">Complete</button></form><?php else: ?><span class="badge bg-success">completed</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>