<?php
$pageTitle='View Delivery Note';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('delivery_notes');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT dn.*,so.sales_order_number,i.invoice_number,u1.email dispatched_email,u2.email delivered_email FROM '.table('delivery_notes').' dn LEFT JOIN '.table('sales_orders').' so ON so.id=dn.sales_order_id LEFT JOIN '.table('invoices').' i ON i.id=dn.invoice_id LEFT JOIN '.table('users').' u1 ON u1.id=dn.dispatched_by LEFT JOIN '.table('users').' u2 ON u2.id=dn.delivered_by WHERE dn.id=? LIMIT 1');
$stmt->execute([$id]);$delivery=$stmt->fetch();
if(!$delivery){flash('error','Delivery note not found.');redirect(ADMIN_URL.'/erp/delivery-notes.php');}
enforceScopeAllowed($pdo,(int)($delivery['company_id']??0),(int)($delivery['branch_id']??0),(int)($delivery['warehouse_id']??0),false);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('delivery_notes').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('Delivery note not found during workflow update.');}
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    $user=currentUser();$userId=(int)($user['id']??0)?:null;
    if($action==='dispatch'){
      if(($current['status']??'')!=='draft'){throw new RuntimeException('Only draft delivery notes can be dispatched.');}
      $pdo->prepare('UPDATE '.table('delivery_notes').' SET status="dispatched",dispatched_by=?,dispatched_at=NOW() WHERE id=?')->execute([$userId,$id]);
      logActivity($pdo,'Fulfillment','delivery_note_dispatched','Delivery note '.$current['delivery_number'].' dispatched.','delivery_note',$id);
      flash('success','Delivery note marked dispatched.');
    }elseif($action==='deliver'){
      if(($current['status']??'')!=='dispatched'){throw new RuntimeException('Only dispatched delivery notes can be completed.');}
      $pdo->prepare('UPDATE '.table('delivery_notes').' SET status="delivered",delivered_by=?,delivered_at=NOW() WHERE id=?')->execute([$userId,$id]);
      if(!empty($current['sales_order_id'])){$pdo->prepare('UPDATE '.table('sales_orders').' SET status="fulfilled" WHERE id=? AND status IN ("approved","converted")')->execute([(int)$current['sales_order_id']]);}
      logActivity($pdo,'Fulfillment','delivery_note_delivered','Delivery note '.$current['delivery_number'].' delivered.','delivery_note',$id);
      flash('success','Delivery note marked delivered.');
    }elseif($action==='cancel'){
      if(in_array((string)$current['status'],['delivered','cancelled'],true)){throw new RuntimeException('This delivery note can no longer be cancelled.');}
      $pdo->prepare('UPDATE '.table('delivery_notes').' SET status="cancelled" WHERE id=?')->execute([$id]);
      logActivity($pdo,'Fulfillment','delivery_note_cancelled','Delivery note '.$current['delivery_number'].' cancelled.','delivery_note',$id);
      flash('success','Delivery note cancelled.');
    }
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-delivery-note.php?id='.$id);
}
$items=$pdo->prepare('SELECT dni.*,p.sku,p.name product_name FROM '.table('delivery_note_items').' dni LEFT JOIN '.table('products').' p ON p.id=dni.product_id WHERE dni.delivery_note_id=? ORDER BY dni.id ASC');$items->execute([$id]);$lines=$items->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Delivery Note</div><h2 class="h4 mb-1"><?php echo esc($delivery['delivery_number']); ?></h2><p class="text-secondary mb-0">Sales order <?php echo esc($delivery['sales_order_number']?:'—'); ?> · Invoice <?php echo esc($delivery['invoice_number']?:'—'); ?></p></div><div class="d-flex flex-wrap gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($delivery['status'])); ?>"><?php echo esc(ucfirst($delivery['status'])); ?></span><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=delivery_note&id=<?php echo (int)$delivery['id']; ?>">Attachments</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/delivery-notes.php">Back</a></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="card-admin p-4 mb-4"><div class="row g-3"><div class="col-md-6"><strong>Customer</strong><div><?php echo esc($delivery['customer_name']?:'—'); ?></div></div><div class="col-md-6"><strong>Delivery Date</strong><div><?php echo esc($delivery['delivery_date']?:'—'); ?></div></div><div class="col-12"><strong>Shipping Address</strong><div class="text-secondary"><?php echo nl2br(esc($delivery['shipping_address']?:'—')); ?></div></div></div></div><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Delivery Lines</div><h2 class="h5 mb-0">Quantities for Fulfillment</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Qty Delivered</th><th>Notes</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong></td><td><?php echo number_format((float)$line['quantity_delivered'],2); ?></td><td><?php echo esc($line['notes']?:'—'); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4"><div class="erp-kicker">Workflow</div><div class="d-grid gap-2 mt-3"><?php if($delivery['status']==='draft'): ?><form method="post"><button class="btn btn-warning w-100" name="action" value="dispatch">Mark Dispatched</button></form><?php endif; ?><?php if($delivery['status']==='dispatched'): ?><form method="post"><button class="btn btn-success w-100" name="action" value="deliver">Mark Delivered</button></form><?php endif; ?><?php if(!in_array($delivery['status'],['delivered','cancelled'],true)): ?><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel Delivery</button></form><?php endif; ?></div><hr><div class="small"><div><strong>Dispatched:</strong> <?php echo esc($delivery['dispatched_at']?:'—'); ?></div><div><?php echo esc($delivery['dispatched_email']?:''); ?></div><div class="mt-2"><strong>Delivered:</strong> <?php echo esc($delivery['delivered_at']?:'—'); ?></div><div><?php echo esc($delivery['delivered_email']?:''); ?></div></div></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>