<?php
$pageTitle='Fiscal Years & Periods';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='fiscal_year'){
  $name=trim((string)($_POST['name']??''));
  $start=trim((string)($_POST['start_date']??''));
  $end=trim((string)($_POST['end_date']??''));
  if($name==='' || $start==='' || $end===''){flash('error','Fiscal year name, start date, and end date are required.');redirect(ADMIN_URL.'/erp/accounting-periods.php');}
  $pdo->beginTransaction();
  try{
    $stmt=$pdo->prepare('INSERT INTO ' . table('fiscal_years') . ' (name,start_date,end_date,status) VALUES (?,?,?,"open")');
    $stmt->execute([$name,$start,$end]);
    $yearId=(int)$pdo->lastInsertId();
    $cursor=new DateTimeImmutable($start);
    $limit=new DateTimeImmutable($end);
    $periodStmt=$pdo->prepare('INSERT INTO ' . table('accounting_periods') . ' (fiscal_year_id,period_name,start_date,end_date,status) VALUES (?,?,?,?,?)');
    while($cursor<=$limit){
      $monthStart=$cursor->modify('first day of this month');
      if($monthStart<new DateTimeImmutable($start)){$monthStart=new DateTimeImmutable($start);}
      $monthEnd=$cursor->modify('last day of this month');
      if($monthEnd>$limit){$monthEnd=$limit;}
      $periodStmt->execute([$yearId,$cursor->format('F Y'),$monthStart->format('Y-m-d'),$monthEnd->format('Y-m-d'),'open']);
      $cursor=$cursor->modify('first day of next month');
    }
    logActivity($pdo,'Accounting','fiscal_year_created','Fiscal year '.$name.' created with accounting periods.','fiscal_year',$yearId);
    $pdo->commit();
    flash('success','Fiscal year and monthly periods created.');
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/accounting-periods.php');
}
if(isset($_GET['close_period'])){
  $id=(int)$_GET['close_period'];
  $pdo->beginTransaction();
  try{closeAccountingPeriod($pdo,$id);$pdo->commit();flash('success','Accounting period closed.');}
  catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/accounting-periods.php');
}
if(isset($_GET['reopen_period'])){
  $id=(int)$_GET['reopen_period'];
  $pdo->beginTransaction();
  try{reopenAccountingPeriod($pdo,$id);$pdo->commit();flash('success','Accounting period reopened.');}
  catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/accounting-periods.php');
}
$years=$pdo->query('SELECT fy.*,COUNT(ap.id) periods FROM ' . table('fiscal_years') . ' fy LEFT JOIN ' . table('accounting_periods') . ' ap ON ap.fiscal_year_id=fy.id GROUP BY fy.id ORDER BY fy.start_date DESC')->fetchAll();
$periods=$pdo->query('SELECT ap.*,fy.name fiscal_year_name,
  (SELECT COUNT(*) FROM ' . table('journal_entries') . ' je WHERE je.status="draft" AND je.entry_date BETWEEN ap.start_date AND ap.end_date) draft_journals,
  (SELECT COUNT(*) FROM ' . table('journal_entries') . ' je WHERE je.status IN ("posted","reversed") AND je.entry_date BETWEEN ap.start_date AND ap.end_date) posted_journals
  FROM ' . table('accounting_periods') . ' ap LEFT JOIN ' . table('fiscal_years') . ' fy ON fy.id=ap.fiscal_year_id ORDER BY ap.start_date DESC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <input type="hidden" name="form_type" value="fiscal_year">
      <h2 class="h5 mb-1">Create Fiscal Year</h2>
      <p class="text-secondary">Monthly periods are generated automatically and are open by default.</p>
      <div class="mb-3"><label class="form-label">Fiscal year name</label><input class="form-control" name="name" placeholder="FY 2027" required></div>
      <div class="row g-2"><div class="col-md-6"><label class="form-label">Start</label><input class="form-control" type="date" name="start_date" required></div><div class="col-md-6"><label class="form-label">End</label><input class="form-control" type="date" name="end_date" required></div></div>
      <button class="btn btn-brand mt-3">Create Fiscal Year</button>
    </form>
    <div class="card-admin p-4 mt-4">
      <div class="erp-kicker">Period Control Rules</div>
      <h3 class="h6">Close discipline</h3>
      <ul class="small text-secondary mb-0">
        <li>A period cannot close while draft journals still exist inside it.</li>
        <li>Posted and reversed journals remain financially reportable.</li>
        <li>New postings are blocked when the entry date falls in a closed period.</li>
      </ul>
    </div>
    <div class="card-admin p-4 mt-4">
      <div class="erp-kicker">Fiscal Years</div>
      <?php foreach($years as $year): ?><div class="border-bottom py-2"><strong><?php echo esc($year['name']); ?></strong><div class="small text-secondary"><?php echo esc($year['start_date'].' → '.$year['end_date']); ?> · <?php echo (int)$year['periods']; ?> periods</div></div><?php endforeach; ?>
    </div>
  </div>
  <div class="col-xl-8">
    <div class="table-wrap table-responsive">
      <div class="table-toolbar"><div><div class="erp-kicker">Accounting Calendar</div><h2 class="h5 mb-0">Periods</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-journal-entry.php">New Journal</a></div>
      <table class="table align-middle"><thead><tr><th>Period</th><th>Fiscal Year</th><th>Start</th><th>End</th><th>Draft</th><th>Posted/Reversed</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($periods as $period): ?><tr><td><strong><?php echo esc($period['period_name']); ?></strong></td><td><?php echo esc($period['fiscal_year_name']); ?></td><td><?php echo esc($period['start_date']); ?></td><td><?php echo esc($period['end_date']); ?></td><td><?php echo (int)$period['draft_journals']; ?></td><td><?php echo (int)$period['posted_journals']; ?></td><td><span class="badge bg-<?php echo $period['status']==='open'?'success':'secondary'; ?>"><?php echo esc(ucfirst($period['status'])); ?></span></td><td class="text-end"><?php if($period['status']==='open'): ?><a class="btn btn-sm btn-outline-danger" href="?close_period=<?php echo (int)$period['id']; ?>">Close</a><?php else: ?><a class="btn btn-sm btn-outline-dark" href="?reopen_period=<?php echo (int)$period['id']; ?>">Reopen</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table>
    </div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>