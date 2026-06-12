<?php
$pageTitle='SaaS Dashboard 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('saas_dashboard_2');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='catalog'){$created=installSaasModuleCatalog($pdo);$matrix=installPlanModuleMatrix($pdo);flash('success','Module catalog created: '.$created.'. Plan matrix rows added: '.$matrix.'.');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'saas-dashboard-2']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/saas-dashboard-2.php');
}
$stats=[
 'companies'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('companies').' WHERE status="active"')->fetchColumn(),
 'active_subscriptions'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('tenant_subscriptions').' WHERE status IN ("active","trial")')->fetchColumn(),
 'open_invoices'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('saas_subscription_invoices').' WHERE status IN ("unpaid","partial")')->fetchColumn(),
 'open_enforcements'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('saas_usage_enforcement_logs').' WHERE status="open"')->fetchColumn(),
];
$subs=$pdo->query('SELECT ts.*,c.company_name,sp.plan_name FROM '.table('tenant_subscriptions').' ts LEFT JOIN '.table('companies').' c ON c.id=ts.company_id LEFT JOIN '.table('subscription_plans').' sp ON sp.id=ts.subscription_plan_id ORDER BY ts.created_at DESC LIMIT 10')->fetchAll();
$billing=$pdo->query('SELECT si.*,c.company_name FROM '.table('saas_subscription_invoices').' si LEFT JOIN '.table('companies').' c ON c.id=si.company_id ORDER BY si.created_at DESC LIMIT 10')->fetchAll();
$usage=$pdo->query('SELECT ul.*,c.company_name FROM '.table('saas_usage_enforcement_logs').' ul LEFT JOIN '.table('companies').' c ON c.id=ul.company_id ORDER BY ul.created_at DESC LIMIT 10')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Commercial SaaS Control</div><h2 class="h4 mb-1">SaaS Multi-Tenant Dashboard 2.0</h2><p class="text-secondary mb-0">Manage tenant plans, trials, billing, licenses, usage limits, modules and onboarding.</p></div><form method="post"><input type="hidden" name="action" value="catalog"><button class="btn btn-brand">Install Module Matrix</button></form></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Latest Subscriptions</h2><table class="table"><tbody><?php foreach($subs as $s): ?><tr><td><strong><?php echo esc($s['subscription_number']); ?></strong><div class="small text-secondary"><?php echo esc($s['company_name'].' · '.$s['plan_name'].' · '.$s['status']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Billing</h2><table class="table"><tbody><?php foreach($billing as $b): ?><tr><td><strong><?php echo esc($b['invoice_number']); ?></strong><div class="small text-secondary"><?php echo esc($b['company_name']); ?> · <?php echo money($b['balance_due']); ?> · <?php echo esc($b['status']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Usage Enforcement</h2><table class="table"><tbody><?php foreach($usage as $u): ?><tr><td><strong><?php echo esc($u['enforcement_number']); ?></strong><div class="small text-secondary"><?php echo esc($u['company_name'].' · '.$u['message']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>