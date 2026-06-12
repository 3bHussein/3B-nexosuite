<?php
$pageTitle='Workflow Approval Automation';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('workflow_approval_automation');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='scan'){
      $days=(int)setting('workflow_approval_escalation_days','2');$rows=safeAiRows($pdo,'SELECT * FROM '.table('approval_requests').' WHERE status="pending" AND DATEDIFF(CURDATE(),submitted_at)>='.$days.' ORDER BY submitted_at ASC LIMIT 100');$created=0;
      foreach($rows as $r){$exists=$pdo->prepare('SELECT id FROM '.table('workflow_approval_escalations').' WHERE approval_request_id=? AND status="open" LIMIT 1');$exists->execute([(int)$r['id']]);if($exists->fetchColumn()){continue;}$number=nextScopedDocumentNumber($pdo,'workflow_escalation',setting('workflow_escalation_prefix','WFESC'),operationalScope($pdo));$pdo->prepare('INSERT INTO '.table('workflow_approval_escalations').' (escalation_number,approval_request_id,document_type,document_number,current_step,days_pending,escalated_to_role,status,message) VALUES (?,?,?,?,?,?,?,"open",?)')->execute([$number,(int)$r['id'],$r['document_type'],$r['document_number'],(int)$r['current_step'],max(0,(int)((time()-strtotime($r['submitted_at']))/86400)),'manager','Approval pending too long.']);$created++;}
      flash('success','Approval escalation scan completed: '.$created.' created.');
    }else{$pdo->prepare('UPDATE '.table('workflow_approval_escalations').' SET status="resolved",resolved_at=NOW() WHERE id=?')->execute([(int)$_POST['id']]);flash('success','Escalation resolved.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'workflow-approval-automation']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/workflow-approval-automation.php');
}
$rows=$pdo->query('SELECT e.*,ar.request_number FROM '.table('workflow_approval_escalations').' e LEFT JOIN '.table('approval_requests').' ar ON ar.id=e.approval_request_id ORDER BY FIELD(e.status,"open","resolved"),e.created_at DESC LIMIT 200')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Approval SLA Control</div><h2 class="h4 mb-1">Workflow Approval Automation</h2><p class="text-secondary mb-0">Escalate approval requests that remain pending beyond the configured SLA.</p></div><form method="post"><input type="hidden" name="action" value="scan"><button class="btn btn-brand">Scan Pending Approvals</button></form></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Escalation</th><th>Approval</th><th>Document</th><th>Days</th><th>Escalated To</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['escalation_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['message']); ?></div></td><td><?php echo esc($r['request_number']); ?></td><td><?php echo esc($r['document_type'].' · '.$r['document_number']); ?></td><td><?php echo (int)$r['days_pending']; ?></td><td><?php echo esc($r['escalated_to_role']); ?></td><td><?php if($r['status']!=='resolved'): ?><form method="post"><input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>"><button class="btn btn-sm btn-success">Resolve</button></form><?php else: ?><span class="badge bg-success">resolved</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>