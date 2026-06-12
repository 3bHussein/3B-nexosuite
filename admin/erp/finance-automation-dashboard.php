<?php
$pageTitle='Finance Automation 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('finance_automation_dashboard');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)$_POST['action'];
    if($action==='aging'){generateAgingSnapshots($pdo,trim((string)$_POST['run_date']));flash('success','AR/AP aging generated.');}
    if($action==='collections'){$c=generateCollectionTasks($pdo,trim((string)$_POST['run_date']));flash('success','Generated '.$c.' collection tasks.');}
    if($action==='recurring'){$r=runRecurringJournals($pdo,trim((string)$_POST['run_date']));flash('success','Posted '.$r['created'].' recurring journal(s).');}
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'finance-automation-dashboard']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/finance-automation-dashboard.php');
}
$stats=[
 'open_ar'=>(float)$pdo->query('SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE balance_due>0')->fetchColumn(),
 'open_ap'=>(float)$pdo->query('SELECT COALESCE(SUM(balance_due),0) FROM '.table('expenses').' WHERE balance_due>0')->fetchColumn(),
 'collection_tasks'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('collection_tasks').' WHERE status="open"')->fetchColumn(),
 'draft_journals'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('journal_entries').' WHERE status="draft"')->fetchColumn(),
];
$runs=$pdo->query('SELECT * FROM '.table('finance_automation_runs').' ORDER BY created_at DESC LIMIT 12')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Accounting & Finance Automation</div><h2 class="h4 mb-1">Finance Automation 2.0</h2><p class="text-secondary mb-0">Automate recurring journals, aging, collections, payment runs, budgets, forecasts and tax preparation.</p></div></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open AR</div><div class="metric-sm"><?php echo money($stats['open_ar']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Open AP</div><div class="metric-sm"><?php echo money($stats['open_ap']); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Collection Tasks</div><div class="metric-sm"><?php echo (int)$stats['collection_tasks']; ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Draft Journals</div><div class="metric-sm"><?php echo (int)$stats['draft_journals']; ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Run Automation</h2><input class="form-control mb-2" type="date" name="run_date" value="<?php echo date('Y-m-d'); ?>"><button class="btn btn-brand w-100 mb-2" name="action" value="aging">Generate Aging</button><button class="btn btn-outline-primary w-100 mb-2" name="action" value="collections">Generate Collections</button><button class="btn btn-outline-success w-100" name="action" value="recurring">Run Recurring Journals</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Automation Runs</h2><table class="table"><thead><tr><th>Run</th><th>Type</th><th>Items</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach($runs as $r): ?><tr><td><strong><?php echo esc($r['run_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['created_at']); ?></div></td><td><?php echo esc($r['run_type']); ?></td><td><?php echo (int)$r['items_processed']; ?></td><td><?php echo money($r['total_amount']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>