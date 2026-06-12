<?php
$pageTitle='Create Supplier Quote';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('rfq_management');
$pdo=getDB();
$rfqId=(int)($_GET['rfq_id']??($_POST['rfq_id']??0));
$supplierId=(int)($_GET['supplier_id']??($_POST['supplier_id']??0));
$rfqStmt=$pdo->prepare('SELECT * FROM '.table('rfqs').' WHERE id=? LIMIT 1');$rfqStmt->execute([$rfqId]);$rfq=$rfqStmt->fetch();
if(!$rfq){flash('error','RFQ not found.');redirect(ADMIN_URL.'/erp/rfqs.php');}
enforceScopeAllowed($pdo,(int)($rfq['company_id']??0),(int)($rfq['branch_id']??0),(int)($rfq['warehouse_id']??0),false);
$suppliers=$pdo->prepare('SELECT s.id,s.supplier_code,s.company_name FROM '.table('suppliers').' s LEFT JOIN '.table('rfq_supplier_invitations').' i ON i.supplier_id=s.id AND i.rfq_id=? WHERE s.status="active" ORDER BY i.id DESC,s.company_name ASC');
$suppliers->execute([$rfqId]);$supplierRows=$suppliers->fetchAll();
$items=$pdo->prepare('SELECT ri.*,p.sku,p.name product_name FROM '.table('rfq_items').' ri LEFT JOIN '.table('products').' p ON p.id=ri.product_id WHERE ri.rfq_id=? ORDER BY ri.id ASC');$items->execute([$rfqId]);$lines=$items->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->beginTransaction();
  try{
    if($supplierId<=0){throw new RuntimeException('Select supplier.');}
    $scope=['company_id'=>(int)($rfq['company_id']??0),'branch_id'=>(int)($rfq['branch_id']??0),'warehouse_id'=>(int)($rfq['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $invStmt=$pdo->prepare('SELECT id FROM '.table('rfq_supplier_invitations').' WHERE rfq_id=? AND supplier_id=? LIMIT 1');$invStmt->execute([$rfqId,$supplierId]);$invitationId=(int)$invStmt->fetchColumn();
    if($invitationId<=0){$invitationId=inviteSupplierToRfq($pdo,$rfqId,$supplierId);}
    $subtotal=0.0;$tax=0.0;$prepared=[];
    foreach($lines as $line){
      $lineId=(int)$line['id'];$qty=max(0,(float)($_POST['quantity'][$lineId]??$line['quantity']));$price=max(0,(float)($_POST['unit_price'][$lineId]??0));$taxRate=max(0,(float)($_POST['tax_rate'][$lineId]??0));$delivery=max(0,(int)($_POST['delivery_days'][$lineId]??0));
      if($qty<=0){continue;}
      $lineTotal=round($qty*$price,2);$lineTax=round($lineTotal*$taxRate/100,2);
      $subtotal+=$lineTotal;$tax+=$lineTax;
      $prepared[]=['rfq_item_id'=>$lineId,'product_id'=>(int)($line['product_id']??0)?:null,'description'=>(string)($line['description']?:$line['product_name']),'quantity'=>$qty,'unit_price'=>$price,'tax_rate'=>$taxRate,'line_total'=>$lineTotal,'delivery_days'=>$delivery,'notes'=>trim((string)($_POST['line_notes'][$lineId]??''))];
    }
    if(!$prepared){throw new RuntimeException('Quote must include at least one line.');}
    $shipping=max(0,(float)($_POST['shipping']??0));$total=round($subtotal+$tax+$shipping,2);
    $number=nextScopedDocumentNumber($pdo,'supplier_quote',setting('supplier_quote_prefix','SQ'),$scope);
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('rfq_supplier_quotes').' (rfq_id,rfq_supplier_invitation_id,supplier_id,response_number,quote_date,due_date,subtotal,tax,shipping,total_amount,delivery_days,payment_terms,valid_until,status,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,"submitted",?,?)');
    $stmt->execute([$rfqId,$invitationId,$supplierId,$number,trim((string)($_POST['quote_date']??date('Y-m-d')))?:date('Y-m-d'),$rfq['due_date']?:null,round($subtotal,2),round($tax,2),$shipping,$total,max(0,(int)($_POST['delivery_days']??0)),trim((string)($_POST['payment_terms']??'')),trim((string)($_POST['valid_until']??''))?:null,trim((string)($_POST['notes']??'')),(int)($user['id']??0)?:null]);
    $quoteId=(int)$pdo->lastInsertId();
    $lineStmt=$pdo->prepare('INSERT INTO '.table('rfq_supplier_quote_items').' (rfq_supplier_quote_id,rfq_item_id,product_id,description,quantity,unit_price,tax_rate,line_total,delivery_days,compliance_status,notes) VALUES (?,?,?,?,?,?,?,?,?,"compliant",?)');
    foreach($prepared as $line){$lineStmt->execute([$quoteId,$line['rfq_item_id'],$line['product_id'],$line['description'],$line['quantity'],$line['unit_price'],$line['tax_rate'],$line['line_total'],$line['delivery_days'],$line['notes']]);}
    calculateRfqQuoteRank($pdo,$quoteId);
    $pdo->prepare('UPDATE '.table('rfq_supplier_invitations').' SET status="responded",responded_at=NOW() WHERE id=?')->execute([$invitationId]);
    logActivity($pdo,'RFQ','supplier_quote_created','Supplier quote '.$number.' submitted for RFQ '.$rfq['rfq_number'].'.','rfq_supplier_quote',$quoteId);
    $pdo->commit();flash('success','Supplier quote submitted.');redirect(ADMIN_URL.'/erp/view-rfq.php?id='.$rfqId);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-supplier-quote.php?rfq_id='.$rfqId.'&supplier_id='.$supplierId);}
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Supplier Response</div><h2 class="h4 mb-1">Create Supplier Quote</h2><p class="text-secondary mb-0"><?php echo esc($rfq['rfq_number'].' · '.$rfq['title']); ?></p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-rfq.php?id=<?php echo (int)$rfqId; ?>">Back to RFQ</a></div>
<form method="post" class="row g-4"><input type="hidden" name="rfq_id" value="<?php echo (int)$rfqId; ?>">
<div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5 mb-3">Quote Header</h2><label class="form-label">Supplier</label><select class="form-select mb-3" name="supplier_id" required><option value="0">Select supplier</option><?php foreach($supplierRows as $s): ?><option value="<?php echo (int)$s['id']; ?>" <?php echo $supplierId===(int)$s['id']?'selected':''; ?>><?php echo esc($s['supplier_code'].' · '.$s['company_name']); ?></option><?php endforeach; ?></select><label class="form-label">Quote Date</label><input class="form-control mb-3" type="date" name="quote_date" value="<?php echo esc(date('Y-m-d')); ?>"><label class="form-label">Valid Until</label><input class="form-control mb-3" type="date" name="valid_until"><label class="form-label">Overall Delivery Days</label><input class="form-control mb-3" type="number" min="0" name="delivery_days" value="0"><label class="form-label">Payment Terms</label><input class="form-control mb-3" name="payment_terms" placeholder="30 days, cash, advance..."><label class="form-label">Shipping</label><input class="form-control mb-3" type="number" step="0.01" min="0" name="shipping" value="0"><label class="form-label">Notes</label><textarea class="form-control" rows="3" name="notes"></textarea></div></div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Quote Lines</div><h2 class="h5 mb-0">Supplier Pricing</h2></div></div><table class="table align-middle"><thead><tr><th>Item</th><th>Qty</th><th>Unit Price</th><th>Tax %</th><th>Delivery Days</th><th>Notes</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc(($line['sku']?:'').' · '.($line['description']?:$line['product_name'])); ?></strong><div class="small text-secondary">Target <?php echo money($line['target_unit_cost']); ?></div></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="quantity[<?php echo (int)$line['id']; ?>]" value="<?php echo esc($line['quantity']); ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="unit_price[<?php echo (int)$line['id']; ?>]" value="<?php echo esc($line['target_unit_cost']); ?>"></td><td><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="tax_rate[<?php echo (int)$line['id']; ?>]" value="<?php echo esc(setting('tax_rate','5')); ?>"></td><td><input class="form-control form-control-sm" type="number" min="0" name="delivery_days[<?php echo (int)$line['id']; ?>]" value="0"></td><td><input class="form-control form-control-sm" name="line_notes[<?php echo (int)$line['id']; ?>]"></td></tr><?php endforeach; ?></tbody></table></div><div class="card-admin p-4 mt-4 text-end"><button class="btn btn-brand">Submit Supplier Quote</button></div></div>
</form>
<?php include dirname(__DIR__).'/footer.php'; ?>