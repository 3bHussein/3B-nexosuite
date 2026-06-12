<?php
$pageTitle='Material Issue';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('material_issue');
$pdo=getDB();
$orders=[];$products=[];$pageWarning='';
try{$orders=$pdo->query('SELECT * FROM '.table('manufacturing_work_orders').' WHERE status IN ("released","in_progress","planned") ORDER BY created_at DESC LIMIT 100')->fetchAll();}catch(Throwable $e){$pageWarning=$e->getMessage();}
try{$products=$pdo->query('SELECT id,sku,name,COALESCE(NULLIF(average_cost,0), NULLIF(cost_price,0), 0) AS cost,stock FROM '.table('products').' ORDER BY name LIMIT 500')->fetchAll();}catch(Throwable $e){$pageWarning=$pageWarning?:$e->getMessage();}
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{issueProductionMaterial($pdo,(int)$_POST['wo_id'],(int)$_POST['product_id'],max(0.0001,(float)$_POST['quantity']),trim((string)$_POST['notes']));flash('success','Material issued.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'material-issue']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/material-issue.php');
}
$issues=[];
try{$issues=$pdo->query('SELECT i.*,wo.work_order_number,p.sku,p.name FROM '.table('production_material_issues').' i LEFT JOIN '.table('manufacturing_work_orders').' wo ON wo.id=i.manufacturing_work_order_id LEFT JOIN '.table('products').' p ON p.id=i.component_product_id ORDER BY i.created_at DESC LIMIT 150')->fetchAll();}catch(Throwable $e){$pageWarning=$pageWarning?:$e->getMessage();}
include dirname(__DIR__).'/header.php';
?>
<?php if(!empty($pageWarning)): ?><div class="alert alert-warning">Material Issue module warning: <?php echo esc($pageWarning); ?>. Run the latest installer/update if manufacturing tables are missing.</div><?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Component Consumption</div><h2 class="h4 mb-1">Material Issue</h2><p class="text-secondary mb-0">Issue raw materials/components to work orders and reduce stock automatically if enabled.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Issue Material</h2><label class="form-label">Work Order</label><select class="form-select mb-2" name="wo_id"><?php foreach($orders as $wo): ?><option value="<?php echo (int)$wo['id']; ?>"><?php echo esc($wo['work_order_number']); ?></option><?php endforeach; ?></select><label class="form-label">Component</label><select class="form-select mb-2" name="product_id"><?php foreach($products as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo esc($p['sku'].' · '.$p['name'].' · Stock '.$p['stock']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" type="number" step="0.0001" name="quantity" value="1"><textarea class="form-control mb-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Issue Material</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Issue History</h2><table class="table"><thead><tr><th>Issue</th><th>WO</th><th>Component</th><th>Qty</th><th>Total</th></tr></thead><tbody><?php foreach($issues as $i): ?><tr><td><strong><?php echo esc($i['issue_number']); ?></strong></td><td><?php echo esc($i['work_order_number']); ?></td><td><?php echo esc($i['sku'].' · '.$i['name']); ?></td><td><?php echo number_format((float)$i['quantity'],4); ?></td><td><?php echo money($i['total_cost']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>