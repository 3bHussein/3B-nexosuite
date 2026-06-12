<?php
$pageTitle='Opening Balances';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$accounts=$pdo->query('SELECT id,account_code,account_name,account_type FROM ' . table('accounts') . ' WHERE active=1 ORDER BY account_code ASC')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $lines=[];
  $accountIds=$_POST['account_id']??[];
  $descriptions=$_POST['line_description']??[];
  $debits=$_POST['debit']??[];
  $credits=$_POST['credit']??[];
  foreach($accountIds as $i=>$accountId){
    $lines[]=[
      'account_id'=>(int)$accountId,
      'description'=>(string)($descriptions[$i]??'Opening balance'),
      'debit'=>(float)($debits[$i]??0),
      'credit'=>(float)($credits[$i]??0),
    ];
  }
  try{
    $pdo->beginTransaction();
    $journalId=createAccountingJournal($pdo,[
      'entry_date'=>trim((string)($_POST['entry_date']??date('Y-m-d'))),
      'reference_type'=>'opening_balance',
      'reference_id'=>null,
      'memo'=>trim((string)($_POST['memo']??'Opening balances')),
    ],$lines,!empty($_POST['post_now']));
    $pdo->commit();
    flash('success','Opening balance journal saved.');
    redirect(ADMIN_URL.'/erp/view-journal-entry.php?id='.$journalId);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/opening-balances.php');}
}
$openingJournals=$pdo->query('SELECT * FROM ' . table('journal_entries') . ' WHERE reference_type="opening_balance" ORDER BY entry_date DESC,id DESC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
  <div class="col-xl-8">
    <form method="post" class="card-admin p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4"><div><div class="erp-kicker">Accounting Setup</div><h2 class="h4 mb-1">Create Opening Balance Journal</h2><p class="text-secondary mb-0">Enter initial balances as a balanced journal. Debits must equal credits.</p></div><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/trial-balance.php"><?php echo t('Trial Balance', 'ميزان المراجعة'); ?></a></div>
      <div class="row g-3 mb-4"><div class="col-md-3"><label class="form-label">Entry date</label><input class="form-control" type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required></div><div class="col-md-9"><label class="form-label">Memo</label><input class="form-control" name="memo" value="Opening balances"></div></div>
      <div class="table-responsive"><table class="table align-middle" id="openingLines"><thead><tr><th style="min-width:240px">Account</th><th>Description</th><th style="width:150px">Debit</th><th style="width:150px">Credit</th><th style="width:70px"></th></tr></thead><tbody><?php for($i=0;$i<5;$i++): ?><tr class="opening-line"><td><select class="form-select" name="account_id[]"><option value="0">Select account</option><?php foreach($accounts as $account): ?><option value="<?php echo (int)$account['id']; ?>"><?php echo esc($account['account_code'].' · '.$account['account_name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control" name="line_description[]" value="Opening balance"></td><td><input class="form-control opening-debit" type="number" step="0.01" min="0" name="debit[]" value="0"></td><td><input class="form-control opening-credit" type="number" step="0.01" min="0" name="credit[]" value="0"></td><td><button class="btn btn-sm btn-outline-danger remove-opening-line" type="button">×</button></td></tr><?php endfor; ?></tbody></table></div>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3"><div class="d-flex flex-wrap gap-3"><div class="journal-total-pill">Debits: <strong id="openingDebitTotal">0.00</strong></div><div class="journal-total-pill">Credits: <strong id="openingCreditTotal">0.00</strong></div><div class="journal-total-pill">Difference: <strong id="openingDifference">0.00</strong></div></div><button class="btn btn-outline-primary" id="addOpeningLine" type="button">Add Line</button></div>
      <div class="d-flex flex-wrap gap-3 align-items-center mt-4"><label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="post_now" value="1" checked><span class="form-check-label">Post immediately</span></label><button class="btn btn-brand btn-lg">Save Opening Balance</button></div>
    </form>
  </div>
  <div class="col-xl-4">
    <div class="card-admin p-4"><div class="erp-kicker">Existing Setup Entries</div><h3 class="h5">Opening Balance Journals</h3><?php foreach($openingJournals as $journal): ?><div class="border-bottom py-2"><a href="<?php echo esc(ADMIN_URL); ?>/erp/view-journal-entry.php?id=<?php echo (int)$journal['id']; ?>"><strong><?php echo esc($journal['journal_number']); ?></strong></a><div class="small text-secondary"><?php echo esc($journal['entry_date'].' · '.$journal['status']); ?> · <?php echo money($journal['total_debit']); ?></div></div><?php endforeach; ?><?php if(!$openingJournals): ?><p class="text-secondary mb-0">No opening balance journals created yet.</p><?php endif; ?></div>
  </div>
</div>
<template id="openingLineTemplate"><tr class="opening-line"><td><select class="form-select" name="account_id[]"><option value="0">Select account</option><?php foreach($accounts as $account): ?><option value="<?php echo (int)$account['id']; ?>"><?php echo esc($account['account_code'].' · '.$account['account_name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control" name="line_description[]" value="Opening balance"></td><td><input class="form-control opening-debit" type="number" step="0.01" min="0" name="debit[]" value="0"></td><td><input class="form-control opening-credit" type="number" step="0.01" min="0" name="credit[]" value="0"></td><td><button class="btn btn-sm btn-outline-danger remove-opening-line" type="button">×</button></td></tr></template>
<script>
const openingBody=document.querySelector('#openingLines tbody');
const openingTemplate=document.getElementById('openingLineTemplate');
function recalcOpening(){let debit=0,credit=0;document.querySelectorAll('.opening-debit').forEach(el=>debit+=parseFloat(el.value||0));document.querySelectorAll('.opening-credit').forEach(el=>credit+=parseFloat(el.value||0));document.getElementById('openingDebitTotal').textContent=debit.toFixed(2);document.getElementById('openingCreditTotal').textContent=credit.toFixed(2);document.getElementById('openingDifference').textContent=Math.abs(debit-credit).toFixed(2);}
document.getElementById('addOpeningLine').addEventListener('click',()=>{openingBody.appendChild(openingTemplate.content.cloneNode(true));recalcOpening();});
document.addEventListener('input',(e)=>{if(e.target.matches('.opening-debit,.opening-credit'))recalcOpening();});
document.addEventListener('click',(e)=>{if(e.target.matches('.remove-opening-line')){const rows=document.querySelectorAll('.opening-line');if(rows.length>2){e.target.closest('tr').remove();recalcOpening();}}});
recalcOpening();
</script>
<?php include dirname(__DIR__).'/footer.php'; ?>