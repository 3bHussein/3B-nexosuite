<?php
$pageTitle='View Journal Entry';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$id=(int)($_GET['id']??0);

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='post_journal'){
  $pdo->beginTransaction();
  try{postDraftJournal($pdo,$id);$pdo->commit();flash('success','Journal posted.');}
  catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/view-journal-entry.php?id='.$id);
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='reverse_journal'){
  $pdo->beginTransaction();
  try{
    $reversalId=reverseJournal($pdo,$id,trim((string)($_POST['reversal_date']??date('Y-m-d'))),trim((string)($_POST['memo']??'')));
    $pdo->commit();
    flash('success','Journal reversed successfully.');
    redirect(ADMIN_URL.'/erp/view-journal-entry.php?id='.$reversalId);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/view-journal-entry.php?id='.$id);}
}

$stmt=$pdo->prepare('SELECT je.*,u.email created_by_email,r.journal_number reversed_journal_number FROM ' . table('journal_entries') . ' je LEFT JOIN ' . table('users') . ' u ON u.id=je.created_by LEFT JOIN ' . table('journal_entries') . ' r ON r.id=je.reversed_entry_id WHERE je.id=? LIMIT 1');$stmt->execute([$id]);$entry=$stmt->fetch();
if(!$entry){flash('error','Journal entry not found.');redirect(ADMIN_URL.'/erp/journal-entries.php');}
$stmt=$pdo->prepare('SELECT jl.*,a.account_code,a.account_name,a.account_type FROM ' . table('journal_lines') . ' jl LEFT JOIN ' . table('accounts') . ' a ON a.id=jl.account_id WHERE jl.journal_entry_id=? ORDER BY jl.id ASC');$stmt->execute([$id]);$lines=$stmt->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4"><div><div class="erp-kicker">Journal Entry</div><h2 class="mb-1"><?php echo esc($entry['journal_number']); ?></h2><p class="text-secondary mb-0"><?php echo esc($entry['memo']); ?></p></div><div class="d-flex gap-2 align-items-start flex-wrap"><?php if($entry['status']==='draft'): ?><form method="post"><input type="hidden" name="form_type" value="post_journal"><button class="btn btn-success">Post Journal</button></form><?php endif; ?><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/journal-entries.php">Back</a></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Date</div><div class="metric-sm"><?php echo esc($entry['entry_date']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Debit</div><div class="metric-sm"><?php echo money($entry['total_debit']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Credit</div><div class="metric-sm"><?php echo money($entry['total_credit']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Status</div><div><span class="badge bg-<?php echo esc(statusTone($entry['status'])); ?>"><?php echo esc($entry['status']); ?></span></div><?php if(!empty($entry['reversed_journal_number'])): ?><div class="small text-secondary mt-2">Reversal: <?php echo esc($entry['reversed_journal_number']); ?></div><?php endif; ?></div></div></div>
<?php if($entry['status']==='posted' && empty($entry['reversed_entry_id'])): ?>
<form method="post" class="card-admin p-4 mb-4">
  <input type="hidden" name="form_type" value="reverse_journal">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div><div class="erp-kicker">Controlled Correction</div><h3 class="h5 mb-1">Reverse this posted journal</h3><p class="text-secondary mb-0">A reversing journal with swapped debit and credit values will be created. The original remains auditable.</p></div>
    <div class="row g-2 align-items-end">
      <div class="col-md-auto"><label class="form-label">Reversal date</label><input class="form-control" type="date" name="reversal_date" value="<?php echo date('Y-m-d'); ?>" required></div>
      <div class="col-md-auto"><label class="form-label">Memo</label><input class="form-control" name="memo" placeholder="Reason for reversal"></div>
      <div class="col-md-auto"><button class="btn btn-outline-danger">Create Reversal</button></div>
    </div>
  </div>
</form>
<?php endif; ?>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Account</th><th>Description</th><th>Debit</th><th>Credit</th></tr></thead><tbody><?php foreach($lines as $line): ?><tr><td><strong><?php echo esc($line['account_code']); ?></strong> · <a href="<?php echo esc(ADMIN_URL); ?>/erp/account-ledger.php?account_id=<?php echo (int)$line['account_id']; ?>"><?php echo esc($line['account_name']); ?></a><div class="small text-secondary"><?php echo esc(ucfirst($line['account_type'])); ?></div></td><td><?php echo esc($line['description']); ?></td><td><?php echo money($line['debit']); ?></td><td><?php echo money($line['credit']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>