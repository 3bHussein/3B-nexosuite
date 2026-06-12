<?php
$pageTitle='Sales CRM 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('sales_crm_dashboard');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='generate_lead_tasks'){$n=generateLeadFollowupTasks($pdo);flash('success','Generated '.$n.' lead follow-up tasks.');}
    if($action==='generate_quote_followups'){$n=generateQuoteFollowups($pdo);flash('success','Generated '.$n.' quotation follow-ups.');}
    if($action==='forecast'){createSalesForecast($pdo,date('Y-m'));flash('success','Sales forecast generated.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'sales-crm-dashboard']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/sales-crm-dashboard.php');
}
$stats=[
 'leads'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('crm_leads').' WHERE converted_customer_id IS NULL')->fetchColumn(),
 'hot_leads'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('crm_leads').' WHERE status="hot"')->fetchColumn(),
 'opportunities'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('sales_opportunities').' WHERE status="open"')->fetchColumn(),
 'tasks_due'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('crm_followup_tasks').' WHERE status="open" AND (due_date IS NULL OR due_date<=CURDATE())')->fetchColumn(),
];
$pipeline=$pdo->query('SELECT s.stage_name,COUNT(o.id) deal_count,COALESCE(SUM(o.value_amount),0) total_value,COALESCE(SUM(o.weighted_value),0) weighted_value FROM '.table('sales_pipeline_stages').' s LEFT JOIN '.table('sales_opportunities').' o ON o.stage_id=s.id AND o.status="open" WHERE s.status="active" GROUP BY s.id ORDER BY s.sort_order')->fetchAll();
$tasks=$pdo->query('SELECT t.*,l.name lead_name,q.quotation_number FROM '.table('crm_followup_tasks').' t LEFT JOIN '.table('crm_leads').' l ON l.id=t.lead_id LEFT JOIN '.table('quotations').' q ON q.id=t.quotation_id WHERE t.status="open" ORDER BY COALESCE(t.due_date,"9999-12-31") ASC,t.priority DESC LIMIT 15')->fetchAll();
$forecast=$pdo->query('SELECT * FROM '.table('crm_sales_forecasts').' ORDER BY created_at DESC LIMIT 1')->fetch();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Sales & CRM Automation</div><h2 class="h4 mb-1">Sales CRM 2.0 Dashboard</h2><p class="text-secondary mb-0">Pipeline value, due follow-ups, quote follow-up automation, campaign actions, and forecast control.</p></div><div class="d-flex flex-wrap gap-2"><form method="post"><input type="hidden" name="action" value="generate_lead_tasks"><button class="btn btn-outline-primary">Generate Lead Tasks</button></form><form method="post"><input type="hidden" name="action" value="generate_quote_followups"><button class="btn btn-outline-success">Generate Quote Follow-ups</button></form><form method="post"><input type="hidden" name="action" value="forecast"><button class="btn btn-brand">Forecast</button></form></div></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-7"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Pipeline by Stage</h2><table class="table"><thead><tr><th>Stage</th><th>Deals</th><th>Total</th><th>Weighted</th></tr></thead><tbody><?php foreach($pipeline as $p): ?><tr><td><strong><?php echo esc($p['stage_name']); ?></strong></td><td><?php echo (int)$p['deal_count']; ?></td><td><?php echo money($p['total_value']); ?></td><td><?php echo money($p['weighted_value']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-5"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Due Follow-ups</h2><table class="table"><tbody><?php foreach($tasks as $t): ?><tr><td><strong><?php echo esc($t['task_number']); ?></strong><div class="small text-secondary"><?php echo esc($t['subject']); ?></div><div><?php echo esc($t['due_date']); ?> · <?php echo esc($t['priority']); ?></div></td></tr><?php endforeach; ?><?php if(!$tasks): ?><tr><td class="text-secondary">No open follow-ups.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php if($forecast): ?><div class="card-admin p-4 mt-4"><h2 class="h5">Latest Forecast: <?php echo esc($forecast['forecast_number']); ?></h2><div class="row g-3"><div class="col-md-3"><strong>Pipeline</strong><br><?php echo money($forecast['pipeline_value']); ?></div><div class="col-md-3"><strong>Weighted</strong><br><?php echo money($forecast['weighted_value']); ?></div><div class="col-md-3"><strong>Expected</strong><br><?php echo money($forecast['expected_revenue']); ?></div><div class="col-md-3"><strong>Open Deals</strong><br><?php echo (int)$forecast['open_opportunities']; ?></div></div></div><?php endif; ?>
<?php include dirname(__DIR__).'/footer.php'; ?>