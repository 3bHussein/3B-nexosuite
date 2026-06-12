<?php
$pageTitle='Cash Flow Summary';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$from=trim((string)($_GET['from']??''));
$to=trim((string)($_GET['to']??''));
$params=[];$dateSql='';
if($from!==''){$dateSql.=' AND je.entry_date>=?';$params[]=$from;}
if($to!==''){$dateSql.=' AND je.entry_date<=?';$params[]=$to;}
$sql='SELECT je.id journal_id,je.journal_number,je.entry_date,je.reference_type,je.reference_id,je.memo,jl.description,jl.debit,jl.credit FROM ' . table('journal_lines') . ' jl INNER JOIN ' . table('accounts') . ' a ON a.id=jl.account_id AND a.account_code="1000" INNER JOIN ' . table('journal_entries') . ' je ON je.id=jl.journal_entry_id AND je.status IN ("posted","reversed") '.$dateSql.' ORDER BY je.entry_date ASC,je.id ASC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$inflow=0;$outflow=0;
foreach($rows as &$row){$inflow+=(float)$row['debit'];$outflow+=(float)$row['credit'];$row['net']=(float)$row['debit']-(float)$row['credit'];}unset($row);
$net=$inflow-$outflow;
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Cash Movement</div><h2 class="h4 mb-1">Cash Flow Summary</h2><p class="text-secondary mb-0">Phase 2 cash-flow view based on Cash & Bank ledger movements.</p></div><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">From</label><input class="form-control" type="date" name="from" value="<?php echo esc($from); ?>"></div><div><label class="form-label">To</label><input class="form-control" type="date" name="to" value="<?php echo esc($to); ?>"></div><button class="btn btn-brand">Filter</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Cash Inflow</div><div class="metric-sm money-positive"><?php echo money($inflow); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Cash Outflow</div><div class="metric-sm money-negative"><?php echo money($outflow); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Net Movement</div><div class="metric-sm <?php echo $net>=0?'money-positive':'money-negative'; ?>"><?php echo money($net); ?></div></div></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Journal</th><th>Reference</th><th>Description</th><th>Cash In</th><th>Cash Out</th><th>Net</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo esc($row['entry_date']); ?></td><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/view-journal-entry.php?id=<?php echo (int)$row['journal_id']; ?>"><?php echo esc($row['journal_number']); ?></a></td><td><?php echo esc(($row['reference_type']?:'manual').(!empty($row['reference_id'])?' #'.$row['reference_id']:'')); ?></td><td><?php echo esc($row['description'] ?: $row['memo']); ?></td><td><?php echo money($row['debit']); ?></td><td><?php echo money($row['credit']); ?></td><td><?php echo money($row['net']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No cash movements found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>