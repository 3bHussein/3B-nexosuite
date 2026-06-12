<?php
$pageTitle='View Purchase Requisition';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('purchase_requisitions');
$pdo=getDB();
$id=(int)($_GET['id']??0);

$stmt=$pdo->prepare('SELECT pr.*,u.email requester_email,po.po_number FROM '.table('purchase_requisitions').' pr LEFT JOIN '.table('users').' u ON u.id=pr.requested_by_user_id LEFT JOIN '.table('purchase_orders').' po ON po.id=pr.converted_po_id WHERE pr.id=? LIMIT 1');
$stmt->execute([$id]);$req=$stmt->fetch();
if(!$req){flash('error','Purchase requisition not found.');redirect(ADMIN_URL.'/erp/purchase-requisitions.php');}
enforceScopeAllowed($pdo,(int)($req['company_id']??0),(int)($req['branch_id']??0),(int)($req['warehouse_id']??0),false);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('purchase_requisitions').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('Requisition not found during workflow update.');}
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    if($action==='submit'){
      if(($current['status']??'')!=='draft'){throw new RuntimeException('Only draft requisitions can be submitted.');}
      $request=createApprovalRequestForDocument($pdo,'purchase_requisition',$id,'approve','Purchase requisition approval request.');
      if($request){
        $pdo->prepare('UPDATE '.table('purchase_requisitions').' SET status="pending_approval" WHERE id=?')->execute([$id]);
        flash('success','Requisition submitted to approval center: '.$request['request_number'].'.');
      }else{
        $user=currentUser();$userId=(int)($user['id']??0)?:null;
        $pdo->prepare('UPDATE '.table('purchase_requisitions').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=?')->execute([$userId,$id]);
        flash('success','No approval rule matched. Requisition approved directly.');
      }
      logActivity($pdo,'Procurement','purchase_requisition_submitted','Purchase requisition '.$current['requisition_number'].' submitted / evaluated for approval.','purchase_requisition',$id);
    }elseif($action==='convert_po'){
      $poId=convertApprovedRequisitionToPurchaseOrder($pdo,$id,(int)($_POST['supplier_id']??0)?:null,trim((string)($_POST['supplier_name']??'')));
      $pdo->commit();
      flash('success','Purchase order created from requisition.');
      redirect(ADMIN_URL.'/erp/view-purchase-order.php?id='.$poId);
    }elseif($action==='cancel'){
      if(in_array((string)$current['status'],['converted','cancelled'],true)){throw new RuntimeException('This requisition can no longer be cancelled.');}
      $pdo->prepare('UPDATE '.table('purchase_requisitions').' SET status="cancelled" WHERE id=?')->execute([$id]);
      logActivity($pdo,'Procurement','purchase_requisition_cancelled','Purchase requisition '.$current['requisition_number'].' cancelled.','purchase_requisition',$id);
      flash('success','Requisition cancelled.');
    }
    if($pdo->inTransaction()){$pdo->commit();}
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/view-purchase-requisition.php?id='.$id);
}
$items=$pdo->prepare('SELECT pri.*,p.sku,p.name product_name FROM '.table('purchase_requisition_items').' pri LEFT JOIN '.table('products').' p ON p.id=pri.product_id WHERE pri.purchase_requisition_id=? ORDER BY pri.id ASC');$items->execute([$id]);$lines=$items->fetchAll();
$suppliers=$pdo->query('SELECT id,supplier_code,company_name FROM '.table('suppliers').' WHERE status="active" ORDER BY company_name ASC LIMIT 200')->fetchAll();
$approval=activeApprovalRequest($pdo,'purchase_requisition',$id,'approve');
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Purchase Requisition</div><h2 class="h4 mb-1"><?php echo esc($req['requisition_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc(($req['department']?:'General').' · Required '.($req['required_date']?:'not specified')); ?></p></div><div class="d-flex gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($req['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($req['status'],'_'))); ?></span><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/purchase-requisitions.php">Back</a></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="card-admin p-4 mb-4"><div class="row g-3"><div class="col-md-6"><strong>Requester</strong><div><?php echo esc($req['requester_email']?:'System'); ?></div></div><div class="col-md-6"><strong>Total</strong><div class="h5"><?php echo money($req['total']); ?></div></div><div class="col-12"><strong>Justification</strong><div class="text-secondary"><?php echo nl2br(esc($req['justification']?:'—')); ?></div></div></div></div><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Requisition Lines</div><h2 class="h5 mb-0">Requested Items</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Qty</th><th>Est. Cost</th><th>Tax</th><th>Line</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong></td><td><?php echo number_format((float)$line['quantity'],2); ?></td><td><?php echo money($line['estimated_unit_cost']); ?></td><td><?php echo number_format((float)$line['tax_rate'],2); ?>%</td><td><?php echo money($line['line_total']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4 mb-4"><div class="erp-kicker">Workflow</div><div class="d-grid gap-2 mt-3"><?php if($req['status']==='draft'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="submit">Submit / Approve Requisition</button></form><?php endif; ?><?php if($req['status']==='pending_approval' && $approval): ?><a class="btn btn-warning" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$approval['id']; ?>">Open Approval Request</a><?php endif; ?><?php if($req['status']==='approved'): ?><form method="post"><input type="hidden" name="action" value="convert_po"><select class="form-select mb-2" name="supplier_id"><option value="0">Optional supplier</option><?php foreach($suppliers as $supplier): ?><option value="<?php echo (int)$supplier['id']; ?>"><?php echo esc($supplier['supplier_code'].' · '.$supplier['company_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="supplier_name" placeholder="Supplier name fallback"><button class="btn btn-success w-100">Convert to Purchase Order</button></form><?php endif; ?><?php if(!in_array($req['status'],['converted','cancelled'],true)): ?><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel Requisition</button></form><?php endif; ?><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=purchase_requisition&id=<?php echo (int)$req['id']; ?>">Attachments</a></div></div><div class="card-admin p-4"><div class="erp-kicker">Converted PO</div><div class="h5 mb-0"><?php echo esc($req['po_number']?:'Not converted'); ?></div></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>