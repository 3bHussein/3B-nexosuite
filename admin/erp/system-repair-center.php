<?php
$pageTitle='System Repair Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('system_repair_center');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'scan');
    if($action==='scan'){$result=p34RunRepairScan($pdo,false);flash('success',$result['summary']);}
    elseif($action==='repair'){$result=p34RunRepairScan($pdo,true);flash('success',$result['summary']);}
    elseif($action==='settings'){$n=p34RepairMissingSettings($pdo);flash('success','Settings repaired: '.$n);}
    elseif($action==='permissions'){$n=p34RepairPermissions($pdo);flash('success','Permissions repaired: '.$n);}
    elseif($action==='schema'){$r=p34RunSchemaCheck($pdo);flash('success',$r['summary']);}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'system-repair-center']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/system-repair-center.php');
}
$runs=$pdo->query('SELECT * FROM '.table('production_repair_runs').' ORDER BY created_at DESC LIMIT 50')->fetchAll();
$items=$pdo->query('SELECT i.*,r.run_number FROM '.table('production_repair_items').' i LEFT JOIN '.table('production_repair_runs').' r ON r.id=i.production_repair_run_id ORDER BY i.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Safe Repair</div><h2 class="h4 mb-1">System Repair Center</h2><p class="text-secondary mb-0">Run non-destructive scans and repairs for missing settings, permissions and schema checks.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">Repair Actions</h2><form method="post" class="d-grid gap-2"><button name="action" value="scan" class="btn btn-outline-primary">Scan Only</button><button name="action" value="repair" class="btn btn-brand">Run Safe Repair</button><button name="action" value="settings" class="btn btn-outline-secondary">Repair Settings</button><button name="action" value="permissions" class="btn btn-outline-secondary">Repair Admin Permissions</button><button name="action" value="schema" class="btn btn-outline-secondary">Run Schema Check</button></form></div><div class="table-wrap table-responsive mt-4"><h2 class="h6 mb-3">Recent Runs</h2><table class="table table-sm"><tbody><?php foreach($runs as $r): ?><tr><td><strong><?php echo esc($r['run_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['summary']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Latest Repair Items</h2><table class="table"><thead><tr><th>Run</th><th>Item</th><th>Severity</th><th>Status</th><th>Recommendation</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?php echo esc($i['run_number']); ?></td><td><strong><?php echo esc($i['item_key']); ?></strong><div class="small text-secondary"><?php echo esc($i['description']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($i['severity'])); ?>"><?php echo esc($i['severity']); ?></span></td><td><?php echo esc($i['status']); ?></td><td><?php echo esc($i['recommendation']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>