<?php
$pageTitle='View RFQ';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('rfq_management');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT r.*,pr.requisition_number,po.po_number,s.company_name awarded_supplier FROM '.table('rfqs').' r LEFT JOIN '.table('purchase_requisitions').' pr ON pr.id=r.source_requisition_id LEFT JOIN '.table('purchase_orders').' po ON po.id=r.converted_po_id LEFT JOIN '.table('suppliers').' s ON s.id=r.awarded_supplier_id WHERE r.id=? LIMIT 1');
$stmt->execute([$id]);$rfq=$stmt->fetch();
if(!$rfq){flash('error','RFQ not found.');redirect(ADMIN_URL.'/erp/rfqs.php');}
enforceScopeAllowed($pdo,(int)($rfq['company_id']??0),(int)($rfq['branch_id']??0),(int)($rfq['warehouse_id']??0),false);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=(string)($_POST['action']??'');
  $pdo->beginTransaction();
  try{
    $lock=$pdo->prepare('SELECT * FROM '.table('rfqs').' WHERE id=? LIMIT 1 FOR UPDATE');$lock->execute([$id]);$current=$lock->fetch();
    if(!$current){throw new RuntimeException('RFQ not found during workflow update.');}
    enforceScopeAllowed($pdo,(int)($current['company_id']??0),(int)($current['branch_id']??0),(int)($current['warehouse_id']??0),true);
    if($action==='invite_supplier'){
      $supplierId=(int)($_POST['supplier_id']??0);if($supplierId<=0){throw new RuntimeException('Select a supplier.');}
      inviteSupplierToRfq($pdo,$id,$supplierId);flash('success','Supplier invited to RFQ.');
    }elseif($action==='close'){
      $pdo->prepare('UPDATE '.table('rfqs').' SET status="closed" WHERE id=? AND status IN ("open","draft")')->execute([$id]);flash('success','RFQ closed.');
    }elseif($action==='cancel'){
      $pdo->prepare('UPDATE '.table('rfqs').' SET status="cancelled" WHERE id=? AND status NOT IN ("awarded","cancelled")')->execute([$id]);flash('success','RFQ cancelled.');
    }
    $pdo->commit();
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/view-rfq.php?id='.$id);
}
$items=$pdo->prepare('SELECT ri.*,p.sku,p.name product_name FROM '.table('rfq_items').' ri LEFT JOIN '.table('products').' p ON p.id=ri.product_id WHERE ri.rfq_id=? ORDER BY ri.id ASC');$items->execute([$id]);$lines=$items->fetchAll();
$invitations=$pdo->prepare('SELECT i.*,s.supplier_code,s.company_name FROM '.table('rfq_supplier_invitations').' i LEFT JOIN '.table('suppliers').' s ON s.id=i.supplier_id WHERE i.rfq_id=? ORDER BY i.created_at ASC');$invitations->execute([$id]);$invites=$invitations->fetchAll();
$quotes=$pdo->prepare('SELECT q.*,s.supplier_code,s.company_name FROM '.table('rfq_supplier_quotes').' q LEFT JOIN '.table('suppliers').' s ON s.id=q.supplier_id WHERE q.rfq_id=? ORDER BY q.rank_score DESC,q.total_amount ASC');$quotes->execute([$id]);$quoteRows=$quotes->fetchAll();
$suppliers=$pdo->query('SELECT id,supplier_code,company_name FROM '.table('suppliers').' WHERE status="active" ORDER BY company_name ASC LIMIT 500')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div><div class="erp-kicker">RFQ Detail</div><h2 class="h4 mb-1"><?php echo esc($rfq['rfq_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc($rfq['title']); ?> · Due <?php echo esc($rfq['due_date']?:'not set'); ?></p></div>
  <div class="d-flex flex-wrap gap-2"><span class="badge fs-6 bg-<?php echo esc(statusTone($rfq['status'])); ?>"><?php echo esc($rfq['status']); ?></span><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/rfq-comparison.php?id=<?php echo (int)$rfq['id']; ?>">Compare Quotes</a><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/create-supplier-quote.php?rfq_id=<?php echo (int)$rfq['id']; ?>">Add Quote</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/rfqs.php">Back</a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Invited Suppliers</div><div class="metric-sm"><?php echo count($invites); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Quotes Received</div><div class="metric-sm"><?php echo count($quoteRows); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Awarded Supplier</div><div class="h6 mb-0"><?php echo esc($rfq['awarded_supplier']?:'Not awarded'); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Converted PO</div><div class="h6 mb-0"><?php echo esc($rfq['po_number']?:'Not converted'); ?></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-8">
    <div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">RFQ Lines</div><h2 class="h5 mb-0">Requested Items</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Qty</th><th>Target Cost</th><th>Required</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong><div class="small text-secondary"><?php echo esc($line['notes']); ?></div></td><td><?php echo number_format((float)$line['quantity'],2); ?></td><td><?php echo money($line['target_unit_cost']); ?></td><td><?php echo esc($line['required_date']?:'—'); ?></td></tr><?php endforeach; ?></tbody></table></div>
    <div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Supplier Quotes</div><h2 class="h5 mb-0">Commercial Responses</h2></div></div><table class="table align-middle"><thead><tr><th>Quote</th><th>Supplier</th><th>Total</th><th>Delivery</th><th>Score</th><th>Status</th></tr></thead><tbody><?php foreach($quoteRows as $q): ?><tr><td><strong><?php echo esc($q['response_number']); ?></strong><div class="small text-secondary"><?php echo esc($q['payment_terms']); ?></div></td><td><?php echo esc(($q['supplier_code']? $q['supplier_code'].' · ' : '').$q['company_name']); ?></td><td><?php echo money($q['total_amount']); ?></td><td><?php echo (int)$q['delivery_days']; ?> days</td><td><?php echo number_format((float)$q['rank_score'],2); ?></td><td><span class="badge bg-<?php echo esc(statusTone($q['status'])); ?>"><?php echo esc($q['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$quoteRows): ?><tr><td colspan="6" class="text-secondary">No supplier quotes submitted yet.</td></tr><?php endif; ?></tbody></table></div>
  </div>
  <div class="col-xl-4">
    <div class="card-admin p-4 mb-4"><div class="erp-kicker">Invite Supplier</div><form method="post" class="d-grid gap-2 mt-3"><input type="hidden" name="action" value="invite_supplier"><select class="form-select" name="supplier_id"><option value="0">Select supplier</option><?php foreach($suppliers as $supplier): ?><option value="<?php echo (int)$supplier['id']; ?>"><?php echo esc($supplier['supplier_code'].' · '.$supplier['company_name']); ?></option><?php endforeach; ?></select><button class="btn btn-brand">Invite Supplier</button></form></div>
    <div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Invitations</h2><table class="table"><tbody><?php foreach($invites as $invite): ?><tr><td><strong><?php echo esc($invite['invitation_number']); ?></strong><div class="small text-secondary"><?php echo esc(($invite['supplier_code']?:'').' '.$invite['company_name']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($invite['status'])); ?>"><?php echo esc($invite['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$invites): ?><tr><td class="text-secondary">No suppliers invited.</td></tr><?php endif; ?></tbody></table></div>
    <div class="card-admin p-4"><div class="erp-kicker">Workflow</div><div class="d-grid gap-2 mt-3"><?php if(in_array($rfq['status'],['draft','open'],true)): ?><form method="post"><button class="btn btn-outline-warning w-100" name="action" value="close">Close RFQ</button></form><form method="post"><button class="btn btn-outline-danger w-100" name="action" value="cancel">Cancel RFQ</button></form><?php endif; ?><?php if(!empty($rfq['converted_po_id'])): ?><a class="btn btn-success" href="<?php echo esc(ADMIN_URL); ?>/erp/view-purchase-order.php?id=<?php echo (int)$rfq['converted_po_id']; ?>">Open Purchase Order</a><?php endif; ?></div></div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>