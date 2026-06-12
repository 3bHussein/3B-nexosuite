<?php
$pageTitle='View Warranty Claim';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('warranty_claims');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT wc.*,c.customer_code,jc.job_card_number,p.sku product_sku,p.name product_name,s.supplier_code,s.company_name supplier_name FROM '.table('warranty_claims').' wc LEFT JOIN '.table('customers').' c ON c.id=wc.customer_id LEFT JOIN '.table('job_cards').' jc ON jc.id=wc.job_card_id LEFT JOIN '.table('products').' p ON p.id=wc.product_id LEFT JOIN '.table('suppliers').' s ON s.id=wc.supplier_id WHERE wc.id=? LIMIT 1');
$stmt->execute([$id]);$claim=$stmt->fetch();
if(!$claim){flash('error','Warranty claim not found.');redirect(ADMIN_URL.'/erp/warranty-claims.php');}
enforceScopeAllowed($pdo,(int)($claim['company_id']??0),(int)($claim['branch_id']??0),(int)($claim['warehouse_id']??0),false);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim((string)($_POST['action']??''));
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('warranty_claims').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    if($action==='submit'){
      $request=createApprovalRequestForDocument($pdo,'warranty_claim',$id,'approve','Warranty claim approval request.');
      if($request){$pdo->prepare('UPDATE '.table('warranty_claims').' SET status="pending_approval",approval_status="pending_approval",submitted_at=NOW() WHERE id=?')->execute([$id]);flash('success','Warranty claim submitted to approval center: '.$request['request_number'].'.');}
      else{$pdo->prepare('UPDATE '.table('warranty_claims').' SET status="approved",approval_status="not_required",approved_at=NOW() WHERE id=?')->execute([$id]);flash('success','No approval rule matched. Warranty claim approved.');}
    }elseif($action==='close'){
      $pdo->prepare('UPDATE '.table('warranty_claims').' SET status="closed",resolution=?,approved_amount=?,supplier_claim_reference=?,closed_at=NOW() WHERE id=?')->execute([trim((string)($_POST['resolution']??'')),max(0,(float)($_POST['approved_amount']??$current['claim_value'])),trim((string)($_POST['supplier_claim_reference']??'')),$id]);
      flash('success','Warranty claim closed.');
    }elseif($action==='cancel'){
      $pdo->prepare('UPDATE '.table('warranty_claims').' SET status="cancelled" WHERE id=? AND status NOT IN ("closed","cancelled")')->execute([$id]);flash('success','Warranty claim cancelled.');
    }
    logActivity($pdo,'Warranty','warranty_claim_'.$action,'Warranty claim '.$current['claim_number'].' action: '.$action.'.','warranty_claim',$id);
    $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/view-warranty-claim.php?id='.$id);
}
$approval=activeApprovalRequest($pdo,'warranty_claim',$id,'approve');
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Warranty Claim</div><h2 class="h4 mb-1"><?php echo esc($claim['claim_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc(($claim['customer_code']?:'Customer').' · '.($claim['product_sku']?:'Product').' · '.$claim['serial_number']); ?></p></div><div class="d-flex flex-wrap gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($claim['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($claim['status'],'_'))); ?></span><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/document-attachments.php?type=warranty_claim&id=<?php echo (int)$claim['id']; ?>">Attachments</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/warranty-claims.php">Back</a></div></div>
<div class="row g-4"><div class="col-xl-8"><div class="card-admin p-4 mb-4"><div class="row g-3"><div class="col-md-4"><strong>Claim Value</strong><div class="h5"><?php echo money($claim['claim_value']); ?></div></div><div class="col-md-4"><strong>Approved Amount</strong><div class="h5"><?php echo money($claim['approved_amount']); ?></div></div><div class="col-md-4"><strong>Supplier Ref</strong><div><?php echo esc($claim['supplier_claim_reference']?:'—'); ?></div></div><div class="col-12"><strong>Failure Description</strong><div class="text-secondary"><?php echo nl2br(esc($claim['failure_description']?:'—')); ?></div></div><div class="col-12"><strong>Resolution</strong><div class="text-secondary"><?php echo nl2br(esc($claim['resolution']?:'—')); ?></div></div></div></div></div><div class="col-xl-4"><div class="card-admin p-4"><div class="erp-kicker">Workflow</div><div class="d-grid gap-2 mt-3"><?php if($claim['status']==='draft'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="submit">Submit / Approve Claim</button></form><?php endif; ?><?php if($approval): ?><a class="btn btn-warning" href="<?php echo esc(ADMIN_URL); ?>/erp/view-approval.php?id=<?php echo (int)$approval['id']; ?>">Open Approval Request</a><?php endif; ?><?php if($claim['status']==='approved'): ?><form method="post"><textarea class="form-control mb-2" rows="3" name="resolution" placeholder="Resolution"></textarea><input class="form-control mb-2" type="number" step="0.01" name="approved_amount" value="<?php echo esc($claim['claim_value']); ?>"><input class="form-control mb-2" name="supplier_claim_reference" placeholder="Supplier claim reference"><button class="btn btn-success w-100" name="action" value="close">Close Claim</button></form><?php endif; ?><?php if(!in_array($claim['status'],['closed','cancelled'],true)): ?><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel</button></form><?php endif; ?></div></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>