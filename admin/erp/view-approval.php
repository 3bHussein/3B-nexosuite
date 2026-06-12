<?php
$pageTitle='Approval Request Detail';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('approvals');
$pdo=getDB();
$id=(int)($_GET['id']??0);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $decision=trim((string)($_POST['decision']??''));
  $notes=trim((string)($_POST['notes']??''));
  $pdo->beginTransaction();
  try{
    decideApprovalRequest($pdo,$id,$decision,$notes);
    $pdo->commit();
    flash('success',$decision==='reject'?'Approval request rejected.':'Approval decision recorded.');
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-approval.php?id='.$id);
}

$stmt=$pdo->prepare('SELECT ar.*,r.rule_name,r.rule_code,r.maker_checker,r.approval_mode,u.email maker_email,u.first_name maker_first_name,u.last_name maker_last_name,c.company_name,b.branch_name FROM '.table('approval_requests').' ar LEFT JOIN '.table('approval_rules').' r ON r.id=ar.approval_rule_id LEFT JOIN '.table('users').' u ON u.id=ar.maker_user_id LEFT JOIN '.table('companies').' c ON c.id=ar.company_id LEFT JOIN '.table('branches').' b ON b.id=ar.branch_id WHERE ar.id=? LIMIT 1');
$stmt->execute([$id]);$request=$stmt->fetch();
if(!$request){flash('error','Approval request not found.');redirect(ADMIN_URL.'/erp/approvals.php');}
try{enforceScopeAllowed($pdo,(int)($request['company_id']??0),(int)($request['branch_id']??0),0,false);}catch(Throwable $e){flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/approvals.php');}
$current=currentApprovalStep($pdo,$id);
$canDecide=$request['status']==='pending' && $current && userCanApproveStep($pdo,$current);
$stepsStmt=$pdo->prepare('SELECT ars.*,u.email decided_by_email FROM '.table('approval_request_steps').' ars LEFT JOIN '.table('users').' u ON u.id=ars.decided_by WHERE ars.approval_request_id=? ORDER BY ars.step_number ASC');
$stepsStmt->execute([$id]);$steps=$stepsStmt->fetchAll();
$logStmt=$pdo->prepare('SELECT al.*,u.email actor_email FROM '.table('approval_logs').' al LEFT JOIN '.table('users').' u ON u.id=al.actor_user_id WHERE al.approval_request_id=? ORDER BY al.created_at DESC,al.id DESC');
$logStmt->execute([$id]);$logs=$logStmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div><div class="erp-kicker">Approval Request Detail</div><h2 class="h4 mb-1"><?php echo esc($request['request_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc(approvalDocumentLabel($request['document_type']).' '.$request['document_number'].' · '.approvalActionLabel($request['action_key'])); ?></p></div>
  <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-dark" href="<?php echo esc(approvalDocumentUrl($request['document_type'],(int)$request['document_id'])); ?>">Open Source Document</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/approvals.php">Back to Approval Center</a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Status</div><div class="metric-sm"><span class="badge fs-6 bg-<?php echo esc(statusTone($request['status'])); ?>"><?php echo esc(ucfirst($request['status'])); ?></span></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Request Amount</div><div class="metric-sm"><?php echo money($request['request_amount']); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Discount Trigger</div><div class="metric-sm"><?php echo money($request['request_discount']); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Current Step</div><div class="metric-sm"><?php echo (int)$request['current_step']; ?></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-8">
    <div class="card-admin p-4 mb-4"><div class="erp-kicker">Policy Context</div><div class="row g-3"><div class="col-md-6"><strong>Rule:</strong><div><?php echo esc(($request['rule_code']?:'').' · '.($request['rule_name']?:'')); ?></div></div><div class="col-md-6"><strong>Maker:</strong><div><?php echo esc(trim(($request['maker_first_name']?:'').' '.($request['maker_last_name']?:'')).' · '.($request['maker_email']?:'System')); ?></div></div><div class="col-md-6"><strong>Scope:</strong><div><?php echo esc(($request['company_name']?:'—').' / '.($request['branch_name']?:'—')); ?></div></div><div class="col-md-6"><strong>Maker-checker:</strong><div><?php echo !empty($request['maker_checker'])?'Enabled':'Disabled'; ?></div></div><div class="col-12"><strong>Notes:</strong><div class="text-secondary"><?php echo nl2br(esc($request['notes']?:'—')); ?></div></div><?php if(!empty($request['rejection_reason'])): ?><div class="col-12"><div class="alert alert-danger mb-0"><strong>Rejection reason:</strong> <?php echo esc($request['rejection_reason']); ?></div></div><?php endif; ?></div></div>
    <div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">Sequential Controls</div><h2 class="h5 mb-0">Approval Steps</h2></div></div><table class="table align-middle"><thead><tr><th>Step</th><th>Required Role</th><th>Status</th><th>Decision</th></tr></thead><tbody><?php foreach($steps as $step): ?><tr><td><strong><?php echo (int)$step['step_number']; ?> · <?php echo esc($step['step_label']); ?></strong></td><td><?php echo esc($step['approver_role_slug']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($step['status'])); ?>"><?php echo esc(ucfirst($step['status'])); ?></span></td><td><?php echo esc($step['decided_by_email']?:'—'); ?><div class="small text-secondary"><?php echo esc($step['decided_at']?:''); ?></div><div class="small text-secondary"><?php echo esc($step['decision_notes']?:''); ?></div></td></tr><?php endforeach; ?></tbody></table></div>
    <div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker"><?php echo t('Audit Trail', 'سجل التدقيق'); ?></div><h2 class="h5 mb-0">Approval Logs</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Action</th><th>Actor</th><th>Notes</th></tr></thead><tbody><?php foreach($logs as $log): ?><tr><td><?php echo esc($log['created_at']); ?></td><td><?php echo esc($log['action']); ?></td><td><?php echo esc($log['actor_email']?:'System'); ?></td><td><?php echo esc($log['notes']); ?></td></tr><?php endforeach; ?><?php if(!$logs): ?><tr><td colspan="4" class="text-secondary">No approval audit logs yet.</td></tr><?php endif; ?></tbody></table></div>
  </div>
  <div class="col-xl-4">
    <div class="card-admin p-4">
      <div class="erp-kicker">Decision Panel</div><h2 class="h5 mb-3">Current Approval Step</h2>
      <?php if($canDecide): ?>
      <div class="alert alert-info">You are eligible to decide step <?php echo (int)$current['step_number']; ?>: <?php echo esc($current['step_label']); ?>.</div>
      <form method="post" class="d-grid gap-3">
        <textarea class="form-control" name="notes" rows="4" placeholder="Approval note or mandatory rejection reason"></textarea>
        <button class="btn btn-success" name="decision" value="approve">Approve Current Step</button>
        <button class="btn btn-outline-danger" name="decision" value="reject">Reject Request</button>
      </form>
      <?php elseif($request['status']!=='pending'): ?>
      <div class="alert alert-secondary mb-0">This approval request is already <?php echo esc($request['status']); ?>.</div>
      <?php else: ?>
      <div class="alert alert-warning mb-0">This step is assigned to role <strong><?php echo esc($current['approver_role_slug']??''); ?></strong>. Your account cannot decide it.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>