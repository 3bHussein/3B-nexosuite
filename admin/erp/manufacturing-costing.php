<?php
$pageTitle='Manufacturing Costing';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('manufacturing_costing');
$pdo=getDB();
$boms=$pdo->query('SELECT b.*,p.name product_name FROM '.table('bill_of_materials').' b LEFT JOIN '.table('products').' p ON p.id=b.finished_product_id ORDER BY b.created_at DESC LIMIT 100')->fetchAll();
$orders=$pdo->query('SELECT * FROM '.table('manufacturing_work_orders').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{createProductionCostRollup($pdo,(int)($_POST['bom_id']??0)?:null,(int)($_POST['wo_id']??0)?:null);flash('success','Cost rollup calculated.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'manufacturing-costing']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/manufacturing-costing.php');
}
$rollups=$pdo->query('SELECT c.*,b.bom_number,wo.work_order_number FROM '.table('production_cost_rollups').' c LEFT JOIN '.table('bill_of_materials').' b ON b.id=c.bill_of_material_id LEFT JOIN '.table('manufacturing_work_orders').' wo ON wo.id=c.manufacturing_work_order_id ORDER BY c.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Cost Rollup</div><h2 class="h4 mb-1">Manufacturing Costing</h2><p class="text-secondary mb-0">Calculate material, labor, overhead, total cost, and unit cost by BOM or work order.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Create Cost Rollup</h2><label class="form-label">BOM</label><select class="form-select mb-2" name="bom_id"><option value="0">No BOM</option><?php foreach($boms as $b): ?><option value="<?php echo (int)$b['id']; ?>"><?php echo esc($b['bom_number'].' · '.$b['product_name']); ?></option><?php endforeach; ?></select><label class="form-label">Work Order</label><select class="form-select mb-3" name="wo_id"><option value="0">No Work Order</option><?php foreach($orders as $o): ?><option value="<?php echo (int)$o['id']; ?>"><?php echo esc($o['work_order_number']); ?></option><?php endforeach; ?></select><button class="btn btn-brand w-100">Calculate</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Rollup</th><th>BOM / WO</th><th>Material</th><th>Labor</th><th>Overhead</th><th>Total</th><th>Unit</th></tr></thead><tbody><?php foreach($rollups as $r): ?><tr><td><strong><?php echo esc($r['rollup_number']); ?></strong></td><td><?php echo esc(($r['bom_number']?:'').' '.$r['work_order_number']); ?></td><td><?php echo money($r['material_cost']); ?></td><td><?php echo money($r['labor_cost']); ?></td><td><?php echo money($r['overhead_cost']); ?></td><td><?php echo money($r['total_cost']); ?></td><td><?php echo money($r['unit_cost']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>