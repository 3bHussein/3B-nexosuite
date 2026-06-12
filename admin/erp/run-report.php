<?php
$pageTitle='Run Report';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('report_builder');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT * FROM '.table('saved_reports').' WHERE id=? LIMIT 1');$stmt->execute([$id]);$report=$stmt->fetch();
if(!$report){flash('error','Saved report not found.');redirect(ADMIN_URL.'/erp/report-builder.php');}
$rows=reportRows($pdo,(string)$report['report_type'],['limit'=>(int)setting('report_export_max_rows','5000')]);
$user=currentUser();$run=$pdo->prepare('INSERT INTO '.table('report_runs').' (saved_report_id,run_by,run_type,status,row_count,started_at,finished_at,notes) VALUES (?,?,"manual","completed",?,NOW(),NOW(),?)');
$run->execute([$id,(int)($user['id']??0)?:null,count($rows),'Report executed from ERP UI.']);
if(($_GET['format']??'')==='csv'){writeCsvResponse(slugify($report['report_code']).'-'.date('Ymd-His').'.csv',$rows);}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Report Output</div><h2 class="h4 mb-1"><?php echo esc($report['report_name']); ?></h2><p class="text-secondary mb-0"><?php echo esc($report['report_code'].' · '.($report['report_type'])); ?> · <?php echo count($rows); ?> rows</p></div><div class="d-flex gap-2"><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/run-report.php?id=<?php echo (int)$id; ?>&format=csv">Download CSV</a><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/report-builder.php">Back</a></div></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><?php if($rows): ?><?php foreach(array_keys($rows[0]) as $col): ?><th><?php echo esc(str_replace('_',' ',ucwords((string)$col,'_'))); ?></th><?php endforeach; ?><?php else: ?><th>Result</th><?php endif; ?></tr></thead><tbody><?php foreach($rows as $row): ?><tr><?php foreach($row as $value): ?><td><?php echo esc((string)$value); ?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td class="text-secondary">No rows returned for this report.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>