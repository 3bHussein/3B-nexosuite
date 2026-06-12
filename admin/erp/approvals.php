<?php
$pageTitle='Approval Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('approvals');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$status=trim((string)($_GET['status']??'pending'));

$params=[];$parts=[];
if($status!==''){$parts[]='ar.status=?';$params[]=$status;}
$condition=selectedScopeCondition('ar',$params,$filters,['company_id','branch_id']);
$condition.=scopeQueryCondition($pdo,'ar',$params,false);
$where=$parts?' WHERE '.implode(' AND ',$parts):'';
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}

$stmt=$pdo->prepare('SELECT ar.*,r.rule_name,r.rule_code,u.email maker_email,ars.step_label,ars.approver_role_slug,ars.status step_status FROM '.table('approval_requests').' ar LEFT JOIN '.table('approval_rules').' r ON r.id=ar.approval_rule_id LEFT JOIN '.table('users').' u ON u.id=ar.maker_user_id LEFT JOIN '.table('approval_request_steps').' ars ON ars.approval_request_id=ar.id AND ars.step_number=ar.current_step'.$where.' ORDER BY CASE WHEN ar.status="pending" THEN 0 ELSE 1 END,ar.submitted_at DESC,ar.id DESC LIMIT 250');
$stmt->execute($params);$rows=$stmt->fetchAll();

$countParams=[];$countCondition=selectedScopeCondition('ar',$countParams,$filters,['company_id','branch_id']);$countCondition.=scopeQueryCondition($pdo,'ar',$countParams,false);$countWhere=$countCondition!==''?' WHERE '.substr($countCondition,5):'';
$countStmt=$pdo->prepare('SELECT ar.status,COUNT(*) total,COALESCE(SUM(ar.request_amount),0) value_total FROM '.table('approval_requests').' ar'.$countWhere.' GROUP BY ar.status');
$countStmt->execute($countParams);$counts=$countStmt->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Internal Controls</div><h2 class="h4 mb-1">Approval Center</h2><p class="text-secondary mb-0">Review sequential approvals, maker-checker workflow, and high-value operational decisions.</p></div>
  <div class="d-flex flex-wrap gap-2">
    <?php if(hasPermission('approval_rules')): ?><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/approval-rules.php"><?php echo t('Approval Rules', 'قواعد الموافقات'); ?></a><?php endif; ?>
  </div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Pending</div><div class="metric-sm"><?php echo (int)($counts['pending']['total']??0); ?></div><div class="small text-secondary"><?php echo money($counts['pending']['value_total']??0); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Approved</div><div class="metric-sm money-positive"><?php echo (int)($counts['approved']['total']??0); ?></div><div class="small text-secondary"><?php echo money($counts['approved']['value_total']??0); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Rejected</div><div class="metric-sm money-negative"><?php echo (int)($counts['rejected']['total']??0); ?></div><div class="small text-secondary"><?php echo money($counts['rejected']['value_total']??0); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Visible Requests</div><div class="metric-sm"><?php echo count($rows); ?></div></div></div>
</div>
<div class="card-admin p-3 mb-4">
  <form class="d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['pending','approved','rejected','cancelled'] as $option): ?><option value="<?php echo esc($option); ?>" <?php echo $status===$option?'selected':''; ?>><?php echo esc(ucfirst($option)); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
    <div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
    <button class="btn btn-brand">Apply</button>
  </form>
</div>
<div class="table-wrap table-responsive">
  <div class="table-toolbar"><div><div class="erp-kicker">Approval Queue</div><h2 class="h5 mb-0">Requests Requiring Governance</h2></div></div>
  <table class="table align-middle"><thead><tr><th>Request</th><th>Document</th><th>Rule / Current Step</th><th>Amount</th><th>Discount</th><th>Maker</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['request_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['submitted_at']?:$row['created_at']); ?></div></td><td><strong><?php echo esc(approvalDocumentLabel($row['document_type']).' · '.$row['document_number']); ?></strong><div class="small text-secondary"><?php echo esc(approvalActionLabel($row['action_key'])); ?></div></td><td><?php echo esc(($row['rule_code']?:'').' · '.($row['rule_name']?:'')); ?><div class="small text-secondary">Step <?php echo (int)$row['current_step']; ?>: <?php echo esc(($row['step_label']?:'').' · '.($row['approver_role_slug']?:'')); ?></div></td><td><?php echo money($row['request_amount']); ?></td><td><?php echo money($row['request_discount']); ?></td><td><?php echo esc($row['maker_email']?:'System'); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(ucfirst($row['status'])); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$row['id']; ?>">Open</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="8" class="text-secondary">No approval requests found for this filter.</td></tr><?php endif; ?></tbody></table>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>