<?php
$pageTitle='Advanced CRM';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('crm_advanced');
$pdo=getDB();
$leadCounts=$pdo->query('SELECT status,COUNT(*) total FROM '.table('crm_leads').' GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$pipeline=$pdo->query('SELECT s.stage_name,COUNT(o.id) deals,COALESCE(SUM(o.value_amount),0) value_sum,COALESCE(SUM(o.weighted_value),0) weighted_sum FROM '.table('sales_pipeline_stages').' s LEFT JOIN '.table('sales_opportunities').' o ON o.stage_id=s.id AND o.status="open" GROUP BY s.id ORDER BY s.sort_order')->fetchAll();
$campaigns=$pdo->query('SELECT * FROM '.table('marketing_campaigns').' ORDER BY created_at DESC LIMIT 6')->fetchAll();
$hotLeads=$pdo->query('SELECT l.*,MAX(e.total_score) score FROM '.table('crm_leads').' l LEFT JOIN '.table('lead_score_events').' e ON e.lead_id=l.id GROUP BY l.id ORDER BY score DESC,l.estimated_value DESC LIMIT 8')->fetchAll();
$kpi=[
  'leads'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('crm_leads'))->fetchColumn(),
  'opps'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('sales_opportunities').' WHERE status="open"')->fetchColumn(),
  'pipeline'=>(float)$pdo->query('SELECT COALESCE(SUM(value_amount),0) FROM '.table('sales_opportunities').' WHERE status="open"')->fetchColumn(),
  'weighted'=>(float)$pdo->query('SELECT COALESCE(SUM(weighted_value),0) FROM '.table('sales_opportunities').' WHERE status="open"')->fetchColumn(),
];
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">Revenue Intelligence</div><h2 class="h4 mb-1">Advanced CRM</h2><p class="text-secondary mb-0">Sales pipeline, campaign performance, lead quality, and customer segmentation control center.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-pipeline.php">Pipeline</a><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/lead-scoring.php">Run Scoring</a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Total Leads</div><div class="metric-sm"><?php echo $kpi['leads']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open Opportunities</div><div class="metric-sm"><?php echo $kpi['opps']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Pipeline Value</div><div class="metric-sm"><?php echo money($kpi['pipeline']); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Weighted Forecast</div><div class="metric-sm money-positive"><?php echo money($kpi['weighted']); ?></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-7"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Pipeline</div><h2 class="h5 mb-0">Stage Forecast</h2></div></div><table class="table align-middle"><thead><tr><th>Stage</th><th>Deals</th><th>Value</th><th>Weighted</th></tr></thead><tbody><?php foreach($pipeline as $row): ?><tr><td><strong><?php echo esc($row['stage_name']); ?></strong></td><td><?php echo (int)$row['deals']; ?></td><td><?php echo money($row['value_sum']); ?></td><td><?php echo money($row['weighted_sum']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
  <div class="col-xl-5"><div class="card-admin p-4 mb-4"><div class="erp-kicker">Lead Status</div><div class="d-flex flex-wrap gap-2 mt-3"><?php foreach($leadCounts as $status=>$count): ?><span class="badge bg-<?php echo esc(statusTone($status)); ?>"><?php echo esc($status); ?> · <?php echo (int)$count; ?></span><?php endforeach; ?></div></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Top Scored Leads</h2><table class="table"><tbody><?php foreach($hotLeads as $lead): ?><tr><td><strong><?php echo esc($lead['name']); ?></strong><div class="small text-secondary"><?php echo esc($lead['company'].' · '.$lead['email']); ?></div></td><td><?php echo money($lead['estimated_value']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($lead['status'])); ?>"><?php echo esc((string)($lead['score']??0)); ?></span></td></tr><?php endforeach; ?><?php if(!$hotLeads): ?><tr><td class="text-secondary">No scored leads yet.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<div class="table-wrap table-responsive mt-4"><div class="table-toolbar"><div><div class="erp-kicker">Campaign Snapshot</div><h2 class="h5 mb-0">Recent Campaigns</h2></div><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/campaigns.php">Open Campaigns</a></div><table class="table"><thead><tr><th>Campaign</th><th>Type</th><th>Budget</th><th>Leads</th><th>Status</th></tr></thead><tbody><?php foreach($campaigns as $campaign): ?><tr><td><strong><?php echo esc($campaign['campaign_code']); ?></strong><div class="small text-secondary"><?php echo esc($campaign['campaign_name']); ?></div></td><td><?php echo esc($campaign['campaign_type']); ?></td><td><?php echo money($campaign['budget_amount']); ?></td><td><?php echo (int)$campaign['generated_leads']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($campaign['status'])); ?>"><?php echo esc($campaign['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$campaigns): ?><tr><td colspan="5" class="text-secondary">No campaigns yet.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>