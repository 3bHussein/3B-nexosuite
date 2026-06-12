<?php
$pageTitle='Lead Scoring';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('lead_scoring');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='score_all'){
      $ids=$pdo->query('SELECT id FROM '.table('crm_leads').' WHERE converted_customer_id IS NULL ORDER BY created_at DESC LIMIT 500')->fetchAll(PDO::FETCH_COLUMN);
      $count=0;foreach($ids as $id){calculateLeadScore($pdo,(int)$id);$count++;}
      flash('success','Scored '.$count.' active leads.');
    }elseif(($_POST['action']??'')==='create_opportunity'){
      $oppId=createOpportunityFromLead($pdo,(int)$_POST['lead_id']);flash('success','Opportunity created/confirmed #'.$oppId.'.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'lead-scoring']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/lead-scoring.php');
}
$rules=$pdo->query('SELECT * FROM '.table('lead_score_rules').' ORDER BY id ASC')->fetchAll();
$leads=$pdo->query('SELECT l.*,MAX(e.total_score) score FROM '.table('crm_leads').' l LEFT JOIN '.table('lead_score_events').' e ON e.lead_id=l.id GROUP BY l.id ORDER BY score DESC,l.estimated_value DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Lead Quality Engine</div><h2 class="h4 mb-1">Lead Scoring</h2><p class="text-secondary mb-0">Score leads based on contact completeness, value, probability, B2B status, and follow-up urgency.</p></div><form method="post"><input type="hidden" name="action" value="score_all"><button class="btn btn-brand">Score All Leads</button></form></div>
<div class="row g-4"><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Scoring Rules</h2><table class="table"><thead><tr><th>Rule</th><th>Score</th></tr></thead><tbody><?php foreach($rules as $rule): ?><tr><td><strong><?php echo esc($rule['rule_code']); ?></strong><div class="small text-secondary"><?php echo esc($rule['rule_name']); ?></div></td><td><?php echo (int)$rule['score_value']; ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Lead Scores</div><h2 class="h5 mb-0">Ranked Leads</h2></div></div><table class="table align-middle"><thead><tr><th>Lead</th><th>Source</th><th>Value</th><th>Probability</th><th>Score</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($leads as $lead): ?><tr><td><strong><?php echo esc($lead['name']); ?></strong><div class="small text-secondary"><?php echo esc($lead['company'].' · '.$lead['email'].' · '.$lead['phone']); ?></div></td><td><?php echo esc($lead['source']); ?></td><td><?php echo money($lead['estimated_value']); ?></td><td><?php echo (int)$lead['probability']; ?>%</td><td><strong><?php echo (int)($lead['score']??0); ?></strong></td><td><span class="badge bg-<?php echo esc(statusTone($lead['status'])); ?>"><?php echo esc($lead['status']); ?></span></td><td><form method="post"><input type="hidden" name="action" value="create_opportunity"><input type="hidden" name="lead_id" value="<?php echo (int)$lead['id']; ?>"><button class="btn btn-sm btn-outline-success">Create Opp</button></form></td></tr><?php endforeach; ?><?php if(!$leads): ?><tr><td colspan="7" class="text-secondary">No leads found.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>