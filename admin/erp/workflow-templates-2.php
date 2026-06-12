<?php
$pageTitle='Workflow Templates 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('workflow_templates_2');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$created=installWorkflowBuilderTemplates($pdo);flash('success','Workflow templates installed: '.$created.' new template(s).');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'workflow-templates-2']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/workflow-templates-2.php');
}
$rules=$pdo->query('SELECT r.*,t.event_key,a.action_type FROM '.table('workflow_builder_rules').' r LEFT JOIN '.table('workflow_builder_triggers').' t ON t.workflow_builder_rule_id=r.id LEFT JOIN '.table('workflow_builder_actions').' a ON a.workflow_builder_rule_id=r.id ORDER BY r.created_at DESC LIMIT 200')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Business Rule Templates</div><h2 class="h4 mb-1">Workflow Templates 2.0</h2><p class="text-secondary mb-0">Install standard B2B/B2C ERP automation templates.</p></div><form method="post"><button class="btn btn-brand">Install Default Templates</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><h3 class="h6">Finance</h3><p class="small text-secondary mb-0">Overdue invoice → collection task.</p></div></div><div class="col-md-4"><div class="card-admin p-4"><h3 class="h6">Inventory</h3><p class="small text-secondary mb-0">Low stock → purchase requisition.</p></div></div><div class="col-md-4"><div class="card-admin p-4"><h3 class="h6">Approvals</h3><p class="small text-secondary mb-0">Pending too long → escalation.</p></div></div></div>
<div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Rule</th><th>Module</th><th>Trigger</th><th>Action</th><th>Status</th></tr></thead><tbody><?php foreach($rules as $r): ?><tr><td><strong><?php echo esc($r['rule_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['rule_name']); ?></div></td><td><?php echo esc($r['module']); ?></td><td><?php echo esc($r['event_key']); ?></td><td><?php echo esc($r['action_type']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>