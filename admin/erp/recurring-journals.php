<?php
$pageTitle='Recurring Journals';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('recurring_journals');
$pdo=getDB();
$accounts=$pdo->query('SELECT id,account_code,account_name FROM '.table('accounts').' WHERE active=1 ORDER BY account_code')->fetchAll();
$templates=$pdo->query('SELECT * FROM '.table('recurring_journal_templates').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'template');
    if($action==='line'){
      $pdo->prepare('INSERT INTO '.table('recurring_journal_template_lines').' (recurring_journal_template_id,account_id,description,debit,credit) VALUES (?,?,?,?,?)')->execute([(int)$_POST['template_id'],(int)$_POST['account_id'],trim((string)$_POST['description']),max(0,(float)$_POST['debit']),max(0,(float)$_POST['credit'])]);
      flash('success','Template line added.');
    }else{
      $num=nextScopedDocumentNumber($pdo,'recurring_journal',setting('recurring_journal_prefix','RJ'),operationalScope($pdo));
      $pdo->prepare('INSERT INTO '.table('recurring_journal_templates').' (template_number,template_name,frequency,next_run_date,status,memo) VALUES (?,?,?,?, "active", ?)')->execute([$num,trim((string)$_POST['template_name']),trim((string)$_POST['frequency']),trim((string)$_POST['next_run_date'])?:null,trim((string)$_POST['memo'])]);
      flash('success','Recurring journal template created.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'recurring-journals']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/recurring-journals.php');
}
$lines=$pdo->query('SELECT l.*,t.template_number,a.account_code,a.account_name FROM '.table('recurring_journal_template_lines').' l LEFT JOIN '.table('recurring_journal_templates').' t ON t.id=l.recurring_journal_template_id LEFT JOIN '.table('accounts').' a ON a.id=l.account_id ORDER BY l.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Auto Posting</div><h2 class="h4 mb-1">Recurring Journals</h2><p class="text-secondary mb-0">Create journal templates and run scheduled recurring journal postings.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Create Template</h2><input class="form-control mb-2" name="template_name" placeholder="Monthly rent accrual"><select class="form-select mb-2" name="frequency"><option>monthly</option><option>weekly</option><option>quarterly</option><option>yearly</option></select><input class="form-control mb-2" type="date" name="next_run_date"><textarea class="form-control mb-3" name="memo" rows="3" placeholder="Memo"></textarea><button class="btn btn-brand w-100">Create Template</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="line"><h2 class="h5 mb-3">Add Line</h2><select class="form-select mb-2" name="template_id"><?php foreach($templates as $t): ?><option value="<?php echo (int)$t['id']; ?>"><?php echo esc($t['template_number'].' · '.$t['template_name']); ?></option><?php endforeach; ?></select><select class="form-select mb-2" name="account_id"><?php foreach($accounts as $a): ?><option value="<?php echo (int)$a['id']; ?>"><?php echo esc($a['account_code'].' · '.$a['account_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="description" placeholder="Description"><div class="row g-2"><div class="col-6"><input class="form-control" type="number" step="0.01" name="debit" placeholder="Debit"></div><div class="col-6"><input class="form-control" type="number" step="0.01" name="credit" placeholder="Credit"></div></div><button class="btn btn-outline-primary w-100 mt-3">Add Line</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Templates</h2><table class="table"><thead><tr><th>Template</th><th>Frequency</th><th>Next Run</th><th>Status</th></tr></thead><tbody><?php foreach($templates as $t): ?><tr><td><strong><?php echo esc($t['template_number']); ?></strong><div class="small text-secondary"><?php echo esc($t['template_name']); ?></div></td><td><?php echo esc($t['frequency']); ?></td><td><?php echo esc($t['next_run_date']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($t['status'])); ?>"><?php echo esc($t['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Template Lines</h2><table class="table"><thead><tr><th>Template</th><th>Account</th><th>Debit</th><th>Credit</th></tr></thead><tbody><?php foreach($lines as $l): ?><tr><td><?php echo esc($l['template_number']); ?></td><td><?php echo esc($l['account_code'].' · '.$l['account_name']); ?></td><td><?php echo money($l['debit']); ?></td><td><?php echo money($l['credit']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>