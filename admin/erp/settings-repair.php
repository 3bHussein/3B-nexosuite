<?php
$pageTitle='Settings Repair';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('settings_repair');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$n=p34RepairMissingSettings($pdo);flash('success','Settings repair completed. Created '.$n.' missing setting(s).');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'settings-repair']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/settings-repair.php');
}
$settings=p34ExpectedSettings();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Configuration Auto-Repair</div><h2 class="h4 mb-1">Settings Repair</h2><p class="text-secondary mb-0">Check and restore missing production settings without affecting existing values.</p></div><form method="post"><button class="btn btn-brand">Repair Missing Settings</button></form></div>
<div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Setting</th><th>Current Value</th><th>Default</th><th>Status</th></tr></thead><tbody><?php foreach($settings as $k=>$v): $cur=setting($k,null); ?><tr><td><code><?php echo esc($k); ?></code></td><td><?php echo esc((string)($cur??'')); ?></td><td><?php echo esc((string)$v); ?></td><td><span class="badge bg-<?php echo $cur===null?'danger':'success'; ?>"><?php echo $cur===null?'Missing':'Present'; ?></span></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>