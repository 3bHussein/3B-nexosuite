<?php
$pageTitle='Campaign Automation 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('campaign_automation_2');
$pdo=getDB();
$campaigns=$pdo->query('SELECT * FROM '.table('marketing_campaigns').' ORDER BY created_at DESC LIMIT 200')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='create_actions'){$n=createCampaignActions($pdo,(int)$_POST['campaign_id'],trim((string)$_POST['channel']));flash('success','Created '.$n.' campaign actions.');}
    elseif($action==='execute'){$pdo->prepare('UPDATE '.table('crm_campaign_actions').' SET status="sent",executed_at=NOW() WHERE id=?')->execute([(int)$_POST['id']]);flash('success','Campaign action marked sent.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'campaign-automation']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/campaign-automation.php');
}
$actions=$pdo->query('SELECT a.*,c.campaign_name,m.email,m.phone FROM '.table('crm_campaign_actions').' a LEFT JOIN '.table('marketing_campaigns').' c ON c.id=a.marketing_campaign_id LEFT JOIN '.table('campaign_members').' m ON m.id=a.campaign_member_id ORDER BY FIELD(a.status,"scheduled","sent"),a.created_at DESC LIMIT 250')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Marketing Execution</div><h2 class="h4 mb-1">Campaign Automation 2.0</h2><p class="text-secondary mb-0">Create scheduled campaign actions for campaign members and mark execution status.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="create_actions"><h2 class="h5 mb-3">Create Campaign Actions</h2><select class="form-select mb-2" name="campaign_id"><?php foreach($campaigns as $c): ?><option value="<?php echo (int)$c['id']; ?>"><?php echo esc($c['campaign_code'].' · '.$c['campaign_name']); ?></option><?php endforeach; ?></select><select class="form-select mb-3" name="channel"><option value="email">Email</option><option value="whatsapp">WhatsApp</option><option value="phone">Phone</option><option value="sms">SMS</option></select><button class="btn btn-brand w-100">Create Actions</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Action</th><th>Campaign</th><th>Contact</th><th>Channel</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($actions as $a): ?><tr><td><strong><?php echo esc($a['action_number']); ?></strong><div class="small text-secondary"><?php echo esc($a['subject']); ?></div></td><td><?php echo esc($a['campaign_name']); ?></td><td><?php echo esc($a['email']?:$a['phone']); ?></td><td><?php echo esc($a['channel']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($a['status'])); ?>"><?php echo esc($a['status']); ?></span></td><td><?php if($a['status']!=='sent'): ?><form method="post"><input type="hidden" name="action" value="execute"><input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>"><button class="btn btn-sm btn-success">Mark Sent</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>