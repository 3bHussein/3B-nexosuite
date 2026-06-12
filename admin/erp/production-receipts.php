<?php
$pageTitle='Production Receipts';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('production_receipts');
$pdo=getDB();
$orders=$pdo->query('SELECT wo.*,p.name product_name FROM '.table('manufacturing_work_orders').' wo LEFT JOIN '.table('products').' p ON p.id=wo.finished_product_id WHERE wo.status IN ("released","in_progress","planned") ORDER BY wo.created_at DESC LIMIT 100')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{receiveProductionOutput($pdo,(int)$_POST['wo_id'],max(0.0001,(float)$_POST['quantity']),max(0,(float)$_POST['scrap_quantity']),trim((string)$_POST['notes']));flash('success','Production output received.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'production-receipts']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/production-receipts.php');
}
$receipts=$pdo->query('SELECT r.*,wo.work_order_number,p.name product_name FROM '.table('production_output_receipts').' r LEFT JOIN '.table('manufacturing_work_orders').' wo ON wo.id=r.manufacturing_work_order_id LEFT JOIN '.table('products').' p ON p.id=r.finished_product_id ORDER BY r.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Finished Goods</div><h2 class="h4 mb-1">Production Receipts</h2><p class="text-secondary mb-0">Receive finished goods from work orders and increase stock automatically if enabled.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Receive Output</h2><label class="form-label">Work Order</label><select class="form-select mb-2" name="wo_id"><?php foreach($orders as $wo): ?><option value="<?php echo (int)$wo['id']; ?>"><?php echo esc($wo['work_order_number'].' · '.$wo['product_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" type="number" step="0.0001" name="quantity" value="1"><input class="form-control mb-2" type="number" step="0.0001" name="scrap_quantity" placeholder="Scrap qty"><textarea class="form-control mb-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Receive Finished Goods</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Receipt History</h2><table class="table"><thead><tr><th>Receipt</th><th>WO</th><th>Product</th><th>Qty</th><th>Scrap</th><th>Total</th></tr></thead><tbody><?php foreach($receipts as $r): ?><tr><td><strong><?php echo esc($r['receipt_number']); ?></strong></td><td><?php echo esc($r['work_order_number']); ?></td><td><?php echo esc($r['product_name']); ?></td><td><?php echo number_format((float)$r['quantity'],4); ?></td><td><?php echo number_format((float)$r['scrap_quantity'],4); ?></td><td><?php echo money($r['total_cost']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>