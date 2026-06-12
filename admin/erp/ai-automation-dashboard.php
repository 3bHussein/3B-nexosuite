<?php
$pageTitle='AI Automation 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('ai_automation_dashboard');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'run');
    if($action==='run'){$result=runAiDecisionEngine($pdo,'full');flash('success','AI run completed: '.$result['items'].' items scored, '.$result['alerts'].' risk alerts, '.$result['recommendations'].' recommendations.');}
    elseif($action==='playbooks'){$created=createDefaultAiPlaybooks($pdo);flash('success','Default playbooks created: '.$created);}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'ai-automation-dashboard']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/ai-automation-dashboard.php');
}
$stats=[
 'runs'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('ai_automation_runs'))->fetchColumn(),
 'open_risks'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('ai_risk_scores').' WHERE status="open"')->fetchColumn(),
 'recommendations'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('ai_decision_recommendations').' WHERE status="open"')->fetchColumn(),
 'actions'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('ai_assistant_action_suggestions').' WHERE status="open"')->fetchColumn(),
];
$runs=$pdo->query('SELECT * FROM '.table('ai_automation_runs').' ORDER BY created_at DESC LIMIT 10')->fetchAll();
$risks=$pdo->query('SELECT * FROM '.table('ai_risk_scores').' WHERE status="open" ORDER BY risk_score DESC,created_at DESC LIMIT 10')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">AI Control Center</div><h2 class="h4 mb-1">AI Automation, Decision Engine & Smart Assistant 2.0</h2><p class="text-secondary mb-0">Run rules-based AI scoring across invoices, stock, leads, suppliers, and service operations.</p></div><div class="d-flex gap-2"><form method="post"><input type="hidden" name="action" value="run"><button class="btn btn-brand">Run AI Engine</button></form><form method="post"><input type="hidden" name="action" value="playbooks"><button class="btn btn-outline-primary">Create Playbooks</button></form></div></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-6"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Latest AI Runs</h2><table class="table"><thead><tr><th>Run</th><th>Items</th><th>Alerts</th><th>Recommendations</th><th>Status</th></tr></thead><tbody><?php foreach($runs as $r): ?><tr><td><strong><?php echo esc($r['run_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['run_type'].' · '.$r['created_at']); ?></div></td><td><?php echo (int)$r['items_scored']; ?></td><td><?php echo (int)$r['alerts_created']; ?></td><td><?php echo (int)$r['recommendations_created']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-6"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Highest Open Risks</h2><table class="table"><thead><tr><th>Risk</th><th>Module</th><th>Score</th><th>Level</th></tr></thead><tbody><?php foreach($risks as $r): ?><tr><td><strong><?php echo esc($r['score_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['entity_label']); ?></div></td><td><?php echo esc($r['module']); ?></td><td><?php echo number_format((float)$r['risk_score'],2); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['risk_level'])); ?>"><?php echo esc($r['risk_level']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>