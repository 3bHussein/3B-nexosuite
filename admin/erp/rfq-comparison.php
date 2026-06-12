<?php
$pageTitle='RFQ Comparison';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('rfq_comparison');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$poId=convertRfqQuoteToPurchaseOrder($pdo,(int)$_POST['quote_id'],trim((string)$_POST['decision_reason']));flash('success','RFQ awarded and PO created #'.$poId);}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'rfq-comparison']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/rfq-comparison.php?rfq_id='.(int)($_POST['rfq_id']??0));
}
$rfqs=$pdo->query('SELECT id,rfq_number,title,status FROM '.table('rfqs').' ORDER BY created_at DESC LIMIT 200')->fetchAll();
$rfqId=(int)($_GET['rfq_id']??($rfqs[0]['id']??0));
$quotes=[];
if($rfqId){$stmt=$pdo->prepare('SELECT q.*,s.company_name,s.supplier_code,(SELECT COUNT(*) FROM '.table('rfq_supplier_quote_items').' qi WHERE qi.rfq_supplier_quote_id=q.id) item_count FROM '.table('rfq_supplier_quotes').' q LEFT JOIN '.table('suppliers').' s ON s.id=q.supplier_id WHERE q.rfq_id=? ORDER BY q.total_amount ASC,q.delivery_days ASC');$stmt->execute([$rfqId]);$quotes=$stmt->fetchAll();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Award Decision</div><h2 class="h4 mb-1">RFQ Comparison</h2><p class="text-secondary mb-0">Compare supplier quotes by value, delivery, payment terms, rank score, and convert the selected quote into PO.</p></div></div>
<form class="card-admin p-3 mb-4"><label class="form-label">Select RFQ</label><select class="form-select" name="rfq_id" onchange="this.form.submit()"><?php foreach($rfqs as $r): ?><option value="<?php echo (int)$r['id']; ?>" <?php echo $rfqId===(int)$r['id']?'selected':''; ?>><?php echo esc($r['rfq_number'].' · '.$r['title'].' · '.$r['status']); ?></option><?php endforeach; ?></select></form>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Supplier</th><th>Quote</th><th>Total</th><th>Delivery</th><th>Payment</th><th>Items</th><th>Status</th><th>Award</th></tr></thead><tbody><?php foreach($quotes as $q): ?><tr><td><strong><?php echo esc($q['supplier_code']); ?></strong><div class="small text-secondary"><?php echo esc($q['company_name']); ?></div></td><td><?php echo esc($q['response_number']); ?><div class="small text-secondary">Valid: <?php echo esc($q['valid_until']); ?></div></td><td><?php echo money($q['total_amount']); ?></td><td><?php echo (int)$q['delivery_days']; ?> days</td><td><?php echo esc($q['payment_terms']); ?></td><td><?php echo (int)$q['item_count']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($q['status'])); ?>"><?php echo esc($q['status']); ?></span></td><td><?php if($q['status']!=='awarded'): ?><form method="post" class="d-flex gap-2"><input type="hidden" name="rfq_id" value="<?php echo (int)$rfqId; ?>"><input type="hidden" name="quote_id" value="<?php echo (int)$q['id']; ?>"><input class="form-control form-control-sm" name="decision_reason" placeholder="Reason"><button class="btn btn-sm btn-success">Award + PO</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$quotes): ?><tr><td colspan="8" class="text-secondary">No supplier quotes found for this RFQ.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>