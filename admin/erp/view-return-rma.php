<?php
$pageTitle='View Return / RMA';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('returns_rma');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT r.*,c.customer_code,c.company_name,c.contact_name,i.invoice_number,so.sales_order_number,dn.delivery_number FROM '.table('returns_rma').' r LEFT JOIN '.table('customers').' c ON c.id=r.customer_id LEFT JOIN '.table('invoices').' i ON i.id=r.invoice_id LEFT JOIN '.table('sales_orders').' so ON so.id=r.sales_order_id LEFT JOIN '.table('delivery_notes').' dn ON dn.id=r.delivery_note_id WHERE r.id=? LIMIT 1');
$stmt->execute([$id]);$rma=$stmt->fetch();
if(!$rma){flash('error','RMA not found.');redirect(ADMIN_URL.'/erp/returns-rma.php');}
enforceScopeAllowed($pdo,(int)($rma['company_id']??0),(int)($rma['branch_id']??0),(int)($rma['warehouse_id']??0),false);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('returns_rma').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('RMA not found during workflow update.');}
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    $user=currentUser();$userId=(int)($user['id']??0)?:null;
    if($action==='submit'){
      if(($current['status']??'')!=='draft'){throw new RuntimeException('Only draft RMAs can be submitted.');}
      $request=createApprovalRequestForDocument($pdo,'return_rma',$id,'approve','RMA value approval request.');
      if($request){
        $pdo->prepare('UPDATE '.table('returns_rma').' SET status="pending_approval",approval_status="pending_approval" WHERE id=?')->execute([$id]);
        flash('success','RMA submitted to approval center: '.$request['request_number'].'.');
      }else{
        $pdo->prepare('UPDATE '.table('returns_rma').' SET status="approved",approval_status="not_required",approved_by=?,approved_at=NOW() WHERE id=?')->execute([$userId,$id]);
        flash('success','No approval rule matched. RMA approved directly.');
      }
      logActivity($pdo,'Returns','rma_submitted','RMA '.$current['rma_number'].' submitted / evaluated for approval.','return_rma',$id);
    }elseif($action==='receive'){
      if(($current['status']??'')!=='approved'){throw new RuntimeException('Only approved RMAs can be received.');}
      $scope=['company_id'=>(int)($current['company_id']??0),'branch_id'=>(int)($current['branch_id']??0),'warehouse_id'=>(int)($current['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
      $items=$pdo->prepare('SELECT * FROM '.table('return_rma_items').' WHERE return_rma_id=? ORDER BY id ASC FOR UPDATE');$items->execute([$id]);$lines=$items->fetchAll();
      foreach($lines as $line){
        if(($line['disposition']??'restock')==='restock' && (int)($line['product_id']??0)>0 && (float)($line['quantity']??0)>0){
          adjustWarehouseStock($pdo,(int)$line['product_id'],(float)$line['quantity'],$scope,'return_rma',$id,'Restocked from RMA '.$current['rma_number'].'.');
        }
      }
      $pdo->prepare('UPDATE '.table('returns_rma').' SET status="received",received_by=?,received_at=NOW() WHERE id=?')->execute([$userId,$id]);
      logActivity($pdo,'Returns','rma_received','RMA '.$current['rma_number'].' physically received.','return_rma',$id);
      flash('success','RMA received. Restock-designated items were returned to inventory.');
    }elseif($action==='cancel'){
      if(in_array((string)$current['status'],['received','credited','cancelled'],true)){throw new RuntimeException('This RMA can no longer be cancelled.');}
      $pdo->prepare('UPDATE '.table('returns_rma').' SET status="cancelled" WHERE id=?')->execute([$id]);
      logActivity($pdo,'Returns','rma_cancelled','RMA '.$current['rma_number'].' cancelled.','return_rma',$id);
      flash('success','RMA cancelled.');
    }
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-return-rma.php?id='.$id);
}
$items=$pdo->prepare('SELECT ri.*,p.sku,p.name product_name FROM '.table('return_rma_items').' ri LEFT JOIN '.table('products').' p ON p.id=ri.product_id WHERE ri.return_rma_id=? ORDER BY ri.id ASC');$items->execute([$id]);$lines=$items->fetchAll();
$approval=activeApprovalRequest($pdo,'return_rma',$id,'approve');
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Return / RMA</div><h2 class="h4 mb-1"><?php echo esc($rma['rma_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc(($rma['customer_code']?:'Customer').' · Invoice '.($rma['invoice_number']?:'—')); ?></p></div><div class="d-flex flex-wrap gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($rma['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($rma['status'],'_'))); ?></span><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=return_rma&id=<?php echo (int)$rma['id']; ?>">Attachments</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/returns-rma.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Return Value</div><div class="metric-sm"><?php echo money($rma['total_value']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Approval</div><div><span class="badge fs-6 bg-<?php echo esc(statusTone($rma['approval_status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($rma['approval_status'],'_'))); ?></span></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Source SO</div><div class="h6 mb-0"><?php echo esc($rma['sales_order_number']?:'—'); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Delivery</div><div class="h6 mb-0"><?php echo esc($rma['delivery_number']?:'—'); ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="card-admin p-4 mb-4"><strong>Reason</strong><div class="text-secondary"><?php echo nl2br(esc($rma['reason']?:'—')); ?></div></div><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Returned Items</div><h2 class="h5 mb-0">Disposition & Value</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Qty</th><th>Unit Value</th><th>Condition</th><th>Disposition</th><th>Total</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong></td><td><?php echo number_format((float)$line['quantity'],2); ?></td><td><?php echo money($line['unit_price']); ?></td><td><?php echo esc($line['condition_status']); ?></td><td><?php echo esc($line['disposition']); ?></td><td><?php echo money($line['line_total']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4"><div class="erp-kicker">Workflow</div><div class="d-grid gap-2 mt-3"><?php if($rma['status']==='draft'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="submit">Submit / Approve RMA</button></form><?php endif; ?><?php if($approval): ?><a class="btn btn-warning" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$approval['id']; ?>">Open Approval Request</a><?php endif; ?><?php if($rma['status']==='approved'): ?><form method="post"><button class="btn btn-success w-100" name="action" value="receive">Receive Returned Items</button></form><?php endif; ?><?php if(!in_array($rma['status'],['received','credited','cancelled'],true)): ?><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel RMA</button></form><?php endif; ?><?php if($rma['status']==='received'): ?><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-credit-note.php">Prepare Credit Note</a><?php endif; ?></div></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>