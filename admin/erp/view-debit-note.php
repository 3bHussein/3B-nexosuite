<?php
$pageTitle='View Supplier Debit Note';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='approve'){
  $pdo->beginTransaction();
  try{
    $stmt=$pdo->prepare('SELECT * FROM ' . table('debit_notes') . ' WHERE id=? LIMIT 1 FOR UPDATE');$stmt->execute([$id]);$note=$stmt->fetch();
    if(!$note || $note['status']!=='draft'){throw new RuntimeException('Only draft debit notes can be approved.');}
    if(!empty($note['expense_id'])){$exp=$pdo->prepare('SELECT balance_due FROM ' . table('expenses') . ' WHERE id=? LIMIT 1');$exp->execute([(int)$note['expense_id']]);$balance=(float)$exp->fetchColumn();if((float)$note['total']>$balance+0.01){throw new RuntimeException('Linked debit note total cannot exceed the current expense payable balance.');}}
    $pdo->prepare('UPDATE ' . table('debit_notes') . ' SET status="approved",approved_at=NOW() WHERE id=?')->execute([$id]);
    postDebitNoteAccounting($pdo,$id);
    if(!empty($note['expense_id'])){recalculateExpenseBalance($pdo,(int)$note['expense_id']);}
    logActivity($pdo,'Accounting','debit_note_approved','Debit note '.$note['debit_note_number'].' approved.','debit_note',$id);
    $pdo->commit();flash('success','Debit note approved and posted to accounting.');
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/view-debit-note.php?id='.$id);
}
$stmt=$pdo->prepare('SELECT n.*,s.supplier_code,e.expense_number FROM ' . table('debit_notes') . ' n LEFT JOIN ' . table('suppliers') . ' s ON s.id=n.supplier_id LEFT JOIN ' . table('expenses') . ' e ON e.id=n.expense_id WHERE n.id=? LIMIT 1');$stmt->execute([$id]);$note=$stmt->fetch();
if(!$note){flash('error','Debit note not found.');redirect(ADMIN_URL.'/erp/debit-notes.php');}
$stmt=$pdo->prepare('SELECT * FROM ' . table('debit_note_items') . ' WHERE debit_note_id=? ORDER BY id ASC');$stmt->execute([$id]);$items=$stmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><div><div class="erp-kicker">Supplier Debit Note</div><h2 class="h4 mb-1"><?php echo esc($note['debit_note_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc(($note['supplier_code']?$note['supplier_code'].' · ':'').$note['supplier_name']); ?><?php if($note['expense_number']): ?> · Expense <?php echo esc($note['expense_number']); ?><?php endif; ?></p></div><div class="d-flex gap-2 flex-wrap"><?php if($note['status']==='draft'): ?><form method="post"><input type="hidden" name="action" value="approve"><button class="btn btn-success">Approve & Post</button></form><?php endif; ?><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/debit-notes.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Subtotal</div><div class="metric-sm"><?php echo money($note['subtotal']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">VAT</div><div class="metric-sm"><?php echo money($note['tax']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Total</div><div class="metric-sm"><?php echo money($note['total']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Status</div><span class="badge bg-<?php echo esc(statusTone($note['status'])); ?>"><?php echo esc($note['status']); ?></span></div></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Description</th><th>Net</th><th>VAT %</th><th>Total</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><?php echo esc($item['description']); ?></td><td><?php echo money($item['amount']); ?></td><td><?php echo esc($item['tax_rate']); ?>%</td><td><?php echo money($item['line_total']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>