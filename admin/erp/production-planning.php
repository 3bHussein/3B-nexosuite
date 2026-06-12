<?php
$pageTitle='Production Planning';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('production_planning');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $number=nextScopedDocumentNumber($pdo,'production_plan',setting('production_plan_prefix','PLAN'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('production_plans').' (plan_number,plan_name,date_from,date_to,status,notes) VALUES (?,?,?,?, "draft", ?)')->execute([$number,trim((string)$_POST['plan_name']),trim((string)$_POST['date_from']),trim((string)$_POST['date_to']),trim((string)$_POST['notes'])]);
    flash('success','Production plan created.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'production-planning']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/production-planning.php');
}
$plans=$pdo->query('SELECT * FROM '.table('production_plans').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
$orders=$pdo->query('SELECT wo.*,p.name product_name FROM '.table('manufacturing_work_orders').' wo LEFT JOIN '.table('products').' p ON p.id=wo.finished_product_id ORDER BY wo.planned_start DESC LIMIT 100')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Production Calendar</div><h2 class="h4 mb-1">Production Planning</h2><p class="text-secondary mb-0">Create production periods and review scheduled work orders.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Create Production Plan</h2><input class="form-control mb-2" name="plan_name" placeholder="Plan name"><div class="row g-2"><div class="col-6"><input class="form-control" type="date" name="date_from"></div><div class="col-6"><input class="form-control" type="date" name="date_to"></div></div><textarea class="form-control my-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Create Plan</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Production Plans</h2><table class="table"><thead><tr><th>Plan</th><th>Range</th><th>Status</th></tr></thead><tbody><?php foreach($plans as $p): ?><tr><td><strong><?php echo esc($p['plan_number']); ?></strong><div class="small text-secondary"><?php echo esc($p['plan_name']); ?></div></td><td><?php echo esc($p['date_from'].' → '.$p['date_to']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($p['status'])); ?>"><?php echo esc($p['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Scheduled Work Orders</h2><table class="table"><thead><tr><th>WO</th><th>Product</th><th>Start</th><th>Finish</th><th>Status</th></tr></thead><tbody><?php foreach($orders as $o): ?><tr><td><?php echo esc($o['work_order_number']); ?></td><td><?php echo esc($o['product_name']); ?></td><td><?php echo esc($o['planned_start']); ?></td><td><?php echo esc($o['planned_finish']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($o['status'])); ?>"><?php echo esc($o['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>