<?php
$pageTitle='View Stock Transfer';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('stock_transfers');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT t.*,fc.company_name from_company_name,tc.company_name to_company_name,fb.branch_name from_branch_name,tb.branch_name to_branch_name,fw.warehouse_name from_warehouse_name,tw.warehouse_name to_warehouse_name,fl.location_name from_location_name,tl.location_name to_location_name,ru.email requested_by_email,au.email approved_by_email,du.email dispatched_by_email,vu.email received_by_email FROM '.table('stock_transfers').' t LEFT JOIN '.table('companies').' fc ON fc.id=t.from_company_id LEFT JOIN '.table('companies').' tc ON tc.id=t.to_company_id LEFT JOIN '.table('branches').' fb ON fb.id=t.from_branch_id LEFT JOIN '.table('branches').' tb ON tb.id=t.to_branch_id LEFT JOIN '.table('warehouses').' fw ON fw.id=t.from_warehouse_id LEFT JOIN '.table('warehouses').' tw ON tw.id=t.to_warehouse_id LEFT JOIN '.table('warehouse_locations').' fl ON fl.id=t.from_location_id LEFT JOIN '.table('warehouse_locations').' tl ON tl.id=t.to_location_id LEFT JOIN '.table('users').' ru ON ru.id=t.requested_by LEFT JOIN '.table('users').' au ON au.id=t.approved_by LEFT JOIN '.table('users').' du ON du.id=t.dispatched_by LEFT JOIN '.table('users').' vu ON vu.id=t.received_by WHERE t.id=? LIMIT 1');
$stmt->execute([$id]);$transfer=$stmt->fetch();
if(!$transfer){flash('error','Stock transfer not found.');redirect(ADMIN_URL.'/erp/stock-transfers.php');}
enforceTransferScopeAllowed($pdo,$transfer,false);
$itemsStmt=$pdo->prepare('SELECT sti.*,p.sku,p.name FROM '.table('stock_transfer_items').' sti LEFT JOIN '.table('products').' p ON p.id=sti.product_id WHERE sti.stock_transfer_id=? ORDER BY sti.id ASC');
$itemsStmt->execute([$id]);$items=$itemsStmt->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('stock_transfers').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('Transfer not found during workflow update.');}
    enforceTransferScopeAllowed($pdo,$current,true);
    $user=currentUser();$userId=(int)($user['id']??0)?:null;

    if($action==='submit'){
      if(($current['status']??'')!=='draft'){throw new RuntimeException('Only draft transfers can be submitted.');}
      $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="pending_approval",requested_by=?,requested_at=NOW() WHERE id=?')->execute([$userId,$id]);
      $request=createApprovalRequestForDocument($pdo,'stock_transfer',$id,'approve','Stock transfer approval request.');
      logActivity($pdo,'Inventory','stock_transfer_submitted','Stock transfer '.$current['transfer_number'].' submitted for approval workflow.','stock_transfer',$id);
      flash('success',$request ? 'Transfer submitted to approval center: '.$request['request_number'].'.' : 'Transfer submitted for approval. No approval rule matched, so direct approval remains available.');
    }elseif($action==='approve'){
      if(($current['status']??'')!=='pending_approval'){throw new RuntimeException('Only pending transfers can be approved.');}
      $active=activeApprovalRequest($pdo,'stock_transfer',$id,'approve');
      if($active){throw new RuntimeException('This transfer is controlled by approval workflow. Open request '.$active['request_number'].' in the approval center.');}
      $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="approved",approved_by=?,approved_at=NOW(),rejection_reason=NULL,rejected_at=NULL WHERE id=?')->execute([$userId,$id]);
      logActivity($pdo,'Inventory','stock_transfer_approved','Stock transfer '.$current['transfer_number'].' approved without matched approval rule.','stock_transfer',$id);
      flash('success','Transfer approved.');
    }elseif($action==='reject'){
      if(($current['status']??'')!=='pending_approval'){throw new RuntimeException('Only pending transfers can be rejected.');}
      $active=activeApprovalRequest($pdo,'stock_transfer',$id,'approve');
      if($active){throw new RuntimeException('This transfer is controlled by approval workflow. Reject request '.$active['request_number'].' in the approval center.');}
      $reason=trim((string)($_POST['rejection_reason']??''));
      if($reason===''){throw new RuntimeException('A rejection reason is required.');}
      $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="rejected",approved_by=?,rejected_at=NOW(),rejection_reason=? WHERE id=?')->execute([$userId,$reason,$id]);
      logActivity($pdo,'Inventory','stock_transfer_rejected','Stock transfer '.$current['transfer_number'].' rejected without matched approval rule.','stock_transfer',$id);
      flash('success','Transfer rejected.');
    }elseif($action==='dispatch'){
      if(($current['status']??'')!=='approved'){throw new RuntimeException('Only approved transfers can be dispatched.');}
      enforceScopeAllowed($pdo,(int)($current['from_company_id']??0),(int)($current['from_branch_id']??0),(int)($current['from_warehouse_id']??0),true);
      $lineStmt=$pdo->prepare('SELECT * FROM '.table('stock_transfer_items').' WHERE stock_transfer_id=? ORDER BY id ASC FOR UPDATE');$lineStmt->execute([$id]);$lockedItems=$lineStmt->fetchAll();
      $sourceScope=['company_id'=>(int)$current['from_company_id'],'branch_id'=>(int)$current['from_branch_id'],'warehouse_id'=>(int)$current['from_warehouse_id'],'location_id'=>(int)$current['from_location_id']];
      foreach($lockedItems as $line){
        if(warehouseAvailableQuantity($pdo,(int)$line['product_id'],$sourceScope)<(float)$line['quantity']){throw new RuntimeException('Insufficient available source stock for product #'.$line['product_id'].'.');}
      }
      $transitInsert=$pdo->prepare('INSERT INTO '.table('in_transit_stock').' (stock_transfer_id,stock_transfer_item_id,product_id,from_company_id,from_branch_id,from_warehouse_id,from_location_id,to_company_id,to_branch_id,to_warehouse_id,to_location_id,quantity,received_quantity,unit_cost,total_value,status,dispatched_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,"in_transit",NOW())');
      $intercompanyItems=[];$transferValue=0.0;
      foreach($lockedItems as $line){
        $unitCost=warehouseStockUnitCost($pdo,(int)$line['product_id'],$sourceScope);
        adjustWarehouseStock($pdo,(int)$line['product_id'],-(float)$line['quantity'],$sourceScope,'stock_transfer_dispatch',$id,'Dispatched through transfer '.$current['transfer_number'].'.');
        $lineValue=round((float)$line['quantity']*$unitCost,2);
        $transitInsert->execute([
          $id,(int)$line['id'],(int)$line['product_id'],
          (int)($current['from_company_id']??0)?:null,(int)($current['from_branch_id']??0)?:null,(int)($current['from_warehouse_id']??0)?:null,(int)($current['from_location_id']??0)?:null,
          (int)($current['to_company_id']??0)?:null,(int)($current['to_branch_id']??0)?:null,(int)($current['to_warehouse_id']??0)?:null,(int)($current['to_location_id']??0)?:null,
          (float)$line['quantity'],$unitCost,$lineValue
        ]);
        $intercompanyItems[]=['product_id'=>(int)$line['product_id'],'quantity'=>(float)$line['quantity'],'unit_cost'=>$unitCost,'total_value'=>$lineValue,'notes'=>(string)($line['notes']??'')];
        $transferValue+=$lineValue;
      }
      createIntercompanyTransactionFromTransfer($pdo,$current,$id,$intercompanyItems,$transferValue);
      $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="dispatched",dispatched_by=?,dispatched_at=NOW() WHERE id=?')->execute([$userId,$id]);
      logActivity($pdo,'Inventory','stock_transfer_dispatched','Stock transfer '.$current['transfer_number'].' dispatched from source warehouse and moved to in-transit control.','stock_transfer',$id);
      flash('success','Transfer dispatched. Source stock has been reduced and moved into in-transit inventory control.');
    }elseif($action==='receive'){
      if(($current['status']??'')!=='dispatched'){throw new RuntimeException('Only dispatched transfers can be received.');}
      enforceScopeAllowed($pdo,(int)($current['to_company_id']??0),(int)($current['to_branch_id']??0),(int)($current['to_warehouse_id']??0),true);
      $transitStmt=$pdo->prepare('SELECT * FROM '.table('in_transit_stock').' WHERE stock_transfer_id=? AND status="in_transit" ORDER BY id ASC FOR UPDATE');$transitStmt->execute([$id]);$transitRows=$transitStmt->fetchAll();
      if(!$transitRows){throw new RuntimeException('No in-transit stock rows were found for this dispatched transfer.');}
      $destinationScope=['company_id'=>(int)$current['to_company_id'],'branch_id'=>(int)$current['to_branch_id'],'warehouse_id'=>(int)$current['to_warehouse_id'],'location_id'=>(int)$current['to_location_id']];
      foreach($transitRows as $transit){
        $receiveQty=max(0,(float)$transit['quantity']-(float)$transit['received_quantity']);
        if($receiveQty<=0){continue;}
        adjustWarehouseStock($pdo,(int)$transit['product_id'],$receiveQty,$destinationScope,'stock_transfer_receipt',$id,'Received through transfer '.$current['transfer_number'].'.',(float)$transit['unit_cost']);
        $pdo->prepare('UPDATE '.table('stock_transfer_items').' SET received_quantity=received_quantity+? WHERE id=?')->execute([$receiveQty,(int)$transit['stock_transfer_item_id']]);
        $pdo->prepare('UPDATE '.table('in_transit_stock').' SET received_quantity=quantity,status="received",received_at=NOW() WHERE id=?')->execute([(int)$transit['id']]);
      }
      recognizeIntercompanyTransaction($pdo,$id);
      $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="received",received_by=?,received_at=NOW() WHERE id=?')->execute([$userId,$id]);
      logActivity($pdo,'Inventory','stock_transfer_received','Stock transfer '.$current['transfer_number'].' received into destination warehouse and cleared from in-transit control.','stock_transfer',$id);
      flash('success','Transfer received. Destination stock has been increased, in-transit stock cleared, and intercompany value recognized when applicable.');
    }elseif($action==='cancel'){
      if(!in_array(($current['status']??''),['draft','pending_approval'],true)){throw new RuntimeException('Only draft or pending transfers can be cancelled.');}
      $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="cancelled" WHERE id=?')->execute([$id]);
      logActivity($pdo,'Inventory','stock_transfer_cancelled','Stock transfer '.$current['transfer_number'].' cancelled.','stock_transfer',$id);
      flash('success','Transfer cancelled.');
    }else{
      throw new RuntimeException('Unknown transfer workflow action.');
    }
    $pdo->commit();
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-stock-transfer.php?id='.$id);
}
$activity=$pdo->prepare('SELECT al.*,u.email actor_email FROM '.table('activity_log').' al LEFT JOIN '.table('users').' u ON u.id=al.actor_user_id WHERE al.reference_type="stock_transfer" AND al.reference_id=? ORDER BY al.created_at DESC,al.id DESC LIMIT 30');
$activity->execute([$id]);$activityRows=$activity->fetchAll();
$transitSummaryStmt=$pdo->prepare('SELECT COUNT(*) transit_lines,COALESCE(SUM(CASE WHEN status="in_transit" THEN total_value ELSE 0 END),0) open_value,COALESCE(SUM(total_value),0) total_value FROM '.table('in_transit_stock').' WHERE stock_transfer_id=?');
$transitSummaryStmt->execute([$id]);$transitSummary=$transitSummaryStmt->fetch()?:['transit_lines'=>0,'open_value'=>0,'total_value'=>0];
$intercompanyStmt=$pdo->prepare('SELECT id,transaction_number,status,total_value FROM '.table('intercompany_transactions').' WHERE stock_transfer_id=? LIMIT 1');
$intercompanyStmt->execute([$id]);$intercompany=$intercompanyStmt->fetch()?:null;
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Inter-Branch Stock Workflow</div><h2 class="h4 mb-1"><?php echo esc($transfer['transfer_number']); ?></h2><p class="text-secondary mb-0">Draft → Submit → Approve → Dispatch → Receive with scope and audit history.</p></div><div><span class="badge fs-6 bg-<?php echo esc(statusTone($transfer['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($transfer['status'],'_'))); ?></span></div></div>
<div class="row g-4 mb-4"><div class="col-xl-6"><div class="card-admin p-4"><div class="erp-kicker">From</div><h3 class="h5 mb-2"><?php echo esc(($transfer['from_company_name']?:'—').' / '.($transfer['from_branch_name']?:'—')); ?></h3><div><?php echo esc(($transfer['from_warehouse_name']?:'—').' / '.($transfer['from_location_name']?:'Default location')); ?></div></div></div><div class="col-xl-6"><div class="card-admin p-4"><div class="erp-kicker">To</div><h3 class="h5 mb-2"><?php echo esc(($transfer['to_company_name']?:'—').' / '.($transfer['to_branch_name']?:'—')); ?></h3><div><?php echo esc(($transfer['to_warehouse_name']?:'—').' / '.($transfer['to_location_name']?:'Default location')); ?></div></div></div></div>
<div class="row g-4">
<div class="col-xl-8">
  <div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">Items</div><h2 class="h5 mb-0">Transfer Lines</h2></div></div><table class="table align-middle"><thead><tr><th>Product</th><th>Requested Qty</th><th>Received Qty</th><th>Notes</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><strong><?php echo esc($item['sku'].' · '.$item['name']); ?></strong></td><td><?php echo number_format((float)$item['quantity'],2); ?></td><td><?php echo number_format((float)$item['received_quantity'],2); ?></td><td><?php echo esc($item['notes']); ?></td></tr><?php endforeach; ?></tbody></table></div>
  <div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker"><?php echo t('Audit Trail', 'سجل التدقيق'); ?></div><h2 class="h5 mb-0">Transfer Activity</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Description</th></tr></thead><tbody><?php foreach($activityRows as $row): ?><tr><td><?php echo esc($row['created_at']); ?></td><td><?php echo esc($row['actor_email']?:'System'); ?></td><td><?php echo esc($row['action']); ?></td><td><?php echo esc($row['description']); ?></td></tr><?php endforeach; ?><?php if(!$activityRows): ?><tr><td colspan="4" class="text-secondary">No transfer activity recorded yet.</td></tr><?php endif; ?></tbody></table></div>
</div>
<div class="col-xl-4">
  <div class="card-admin p-4 mb-4"><div class="erp-kicker">Workflow Controls</div><h2 class="h5 mb-3">Actions</h2><div class="d-grid gap-2">
    <?php if($transfer['status']==='draft'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="submit">Submit for Approval</button></form><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel Transfer</button></form><?php endif; ?>
    <?php if($transfer['status']==='pending_approval'): ?><?php $transferApproval=activeApprovalRequest($pdo,'stock_transfer',(int)$transfer['id'],'approve'); ?><?php if($transferApproval): ?><a class="btn btn-warning w-100" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$transferApproval['id']; ?>">Open Approval Request</a><?php else: ?><form method="post"><button class="btn btn-success w-100" name="action" value="approve">Approve Transfer</button></form><form method="post"><textarea class="form-control mb-2" name="rejection_reason" rows="2" placeholder="Rejection reason"></textarea><button class="btn btn-outline-danger w-100" name="action" value="reject">Reject Transfer</button></form><?php endif; ?><form method="post"><button class="btn btn-outline-secondary w-100" name="action" value="cancel">Cancel Transfer</button></form><?php endif; ?>
    <?php if($transfer['status']==='approved'): ?><form method="post"><button class="btn btn-warning w-100" name="action" value="dispatch">Dispatch Stock</button></form><?php endif; ?>
    <?php if($transfer['status']==='dispatched'): ?><form method="post"><button class="btn btn-success w-100" name="action" value="receive">Receive Stock</button></form><?php endif; ?>
    <a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/stock-transfers.php">Back to Transfers</a>
  </div></div>
  <div class="card-admin p-4 mb-4"><div class="erp-kicker">Status Timeline</div><div class="small d-grid gap-2"><div><strong>Requested:</strong> <?php echo esc($transfer['requested_at']?:'—'); ?><br><span class="text-secondary"><?php echo esc($transfer['requested_by_email']?:''); ?></span></div><div><strong>Approved:</strong> <?php echo esc($transfer['approved_at']?:'—'); ?><br><span class="text-secondary"><?php echo esc($transfer['approved_by_email']?:''); ?></span></div><div><strong>Dispatched:</strong> <?php echo esc($transfer['dispatched_at']?:'—'); ?><br><span class="text-secondary"><?php echo esc($transfer['dispatched_by_email']?:''); ?></span></div><div><strong>Received:</strong> <?php echo esc($transfer['received_at']?:'—'); ?><br><span class="text-secondary"><?php echo esc($transfer['received_by_email']?:''); ?></span></div><?php if(!empty($transfer['rejection_reason'])): ?><div class="alert alert-danger mb-0"><strong>Rejected:</strong> <?php echo esc($transfer['rejection_reason']); ?></div><?php endif; ?></div></div>
  <div class="card-admin p-4"><div class="erp-kicker">Phase 3 Value Control</div><h3 class="h5 mb-3">In-Transit & Intercompany</h3><div class="small d-grid gap-2"><div><strong>Transit lines:</strong> <?php echo (int)($transitSummary['transit_lines']??0); ?></div><div><strong>Open in-transit value:</strong> <?php echo money($transitSummary['open_value']??0); ?></div><div><strong>Total transfer value:</strong> <?php echo money($transitSummary['total_value']??0); ?></div><?php if($intercompany): ?><div class="alert alert-info mb-0"><strong><?php echo esc($intercompany['transaction_number']); ?></strong><br><?php echo money($intercompany['total_value']); ?> · <?php echo esc(str_replace('_',' ',ucwords($intercompany['status'],'_'))); ?><div class="mt-2"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-intercompany-transaction.php?id=<?php echo (int)$intercompany['id']; ?>">Open Intercompany Record</a></div></div><?php else: ?><div class="text-secondary">No intercompany record is required for same-company transfers.</div><?php endif; ?></div></div>
</div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>