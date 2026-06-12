<?php
$pageTitle='Create Journal Entry';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$accounts=$pdo->query('SELECT id,account_code,account_name,account_type FROM ' . table('accounts') . ' WHERE active=1 ORDER BY account_code ASC')->fetchAll();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $lines=[];
  $accountIds=$_POST['account_id']??[];
  $descriptions=$_POST['line_description']??[];
  $debits=$_POST['debit']??[];
  $credits=$_POST['credit']??[];
  foreach($accountIds as $i=>$accountId){
    $lines[]=[
      'account_id'=>(int)$accountId,
      'description'=>(string)($descriptions[$i]??''),
      'debit'=>(float)($debits[$i]??0),
      'credit'=>(float)($credits[$i]??0),
    ];
  }
  try{
    $pdo->beginTransaction();
    $companyId=(int)($_POST['company_id']??$defaultScope['company_id']);
    $branchId=(int)($_POST['branch_id']??$defaultScope['branch_id']);
    enforceScopeAllowed($pdo,$companyId,$branchId,0,true);
    $journalId=createAccountingJournal($pdo,[
      'company_id'=>$companyId,
      'branch_id'=>$branchId,
      'entry_date'=>trim((string)($_POST['entry_date']??date('Y-m-d'))),
      'reference_type'=>'manual',
      'reference_id'=>null,
      'memo'=>trim((string)($_POST['memo']??'')),
    ],$lines,!empty($_POST['post_now']));
    $pdo->commit();
    flash('success','Journal entry saved.');
    redirect(ADMIN_URL.'/erp/view-journal-entry.php?id='.$journalId);
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/create-journal-entry.php');}
}
include dirname(__DIR__).'/header.php';
?>
<form method="post" class="card-admin p-4">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4"><div><div class="erp-kicker">Manual Accounting</div><h2 class="h4 mb-1">New Journal Entry</h2><p class="text-secondary mb-0">Debit and credit totals must balance before a journal can be saved.</p></div><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/journal-entries.php">Back to Journals</a></div>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><label class="form-label">Entry date</label><input class="form-control" type="date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required></div>
    <div class="col-md-3"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Memo</label><input class="form-control" name="memo" placeholder="Opening balance, correction..."></div>
  </div>
  <div class="table-responsive"><table class="table align-middle" id="journalLines"><thead><tr><th style="min-width:240px">Account</th><th>Description</th><th style="width:150px">Debit</th><th style="width:150px">Credit</th><th style="width:70px"></th></tr></thead><tbody><?php for($i=0;$i<4;$i++): ?><tr class="journal-line"><td><select class="form-select" name="account_id[]"><option value="0">Select account</option><?php foreach($accounts as $account): ?><option value="<?php echo (int)$account['id']; ?>"><?php echo esc($account['account_code'].' · '.$account['account_name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control" name="line_description[]" placeholder="Line description"></td><td><input class="form-control debit-field" type="number" step="0.01" min="0" name="debit[]" value="0"></td><td><input class="form-control credit-field" type="number" step="0.01" min="0" name="credit[]" value="0"></td><td><button class="btn btn-sm btn-outline-danger remove-line" type="button">×</button></td></tr><?php endfor; ?></tbody></table></div>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3"><div class="d-flex flex-wrap gap-3"><div class="journal-total-pill">Debits: <strong id="debitTotal">0.00</strong></div><div class="journal-total-pill">Credits: <strong id="creditTotal">0.00</strong></div><div class="journal-total-pill">Difference: <strong id="journalDifference">0.00</strong></div></div><button class="btn btn-outline-primary" id="addJournalLine" type="button">Add Line</button></div>
  <div class="d-flex flex-wrap gap-3 align-items-center mt-4"><label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="post_now" value="1" checked><span class="form-check-label">Post immediately</span></label><button class="btn btn-brand btn-lg">Save Journal Entry</button></div>
</form>
<template id="journalLineTemplate"><tr class="journal-line"><td><select class="form-select" name="account_id[]"><option value="0">Select account</option><?php foreach($accounts as $account): ?><option value="<?php echo (int)$account['id']; ?>"><?php echo esc($account['account_code'].' · '.$account['account_name']); ?></option><?php endforeach; ?></select></td><td><input class="form-control" name="line_description[]" placeholder="Line description"></td><td><input class="form-control debit-field" type="number" step="0.01" min="0" name="debit[]" value="0"></td><td><input class="form-control credit-field" type="number" step="0.01" min="0" name="credit[]" value="0"></td><td><button class="btn btn-sm btn-outline-danger remove-line" type="button">×</button></td></tr></template>
<script>
const linesBody=document.querySelector('#journalLines tbody');
const template=document.getElementById('journalLineTemplate');
function recalcJournal(){
 let debit=0,credit=0;
 document.querySelectorAll('.debit-field').forEach(el=>debit+=parseFloat(el.value||0));
 document.querySelectorAll('.credit-field').forEach(el=>credit+=parseFloat(el.value||0));
 document.getElementById('debitTotal').textContent=debit.toFixed(2);
 document.getElementById('creditTotal').textContent=credit.toFixed(2);
 document.getElementById('journalDifference').textContent=Math.abs(debit-credit).toFixed(2);
}
document.getElementById('addJournalLine').addEventListener('click',()=>{linesBody.appendChild(template.content.cloneNode(true));recalcJournal();});
document.addEventListener('input',(e)=>{if(e.target.matches('.debit-field,.credit-field'))recalcJournal();});
document.addEventListener('click',(e)=>{if(e.target.matches('.remove-line')){const rows=document.querySelectorAll('.journal-line');if(rows.length>2){e.target.closest('tr').remove();recalcJournal();}}});
recalcJournal();
</script>
<?php include dirname(__DIR__).'/footer.php'; ?>