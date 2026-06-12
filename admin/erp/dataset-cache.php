<?php
$pageTitle='Dataset Cache';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('dataset_cache');
$pdo=getDB();$types=reportTypeOptions();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$id=createDatasetCache($pdo,trim((string)$_POST['report_type']),['limit'=>(int)$_POST['limit']]);flash('success','Dataset cache created #'.$id);}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'dataset-cache']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/dataset-cache.php');
}
$rows=$pdo->query('SELECT * FROM '.table('report_dataset_cache').' ORDER BY created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Report Performance</div><h2 class="h4 mb-1">Dataset Cache</h2><p class="text-secondary mb-0">Cache large report datasets for faster BI dashboard and export workflows.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Create Cache</h2><select class="form-select mb-2" name="report_type"><?php foreach($types as $k=>$v): ?><option value="<?php echo esc($k); ?>"><?php echo esc($v); ?></option><?php endforeach; ?></select><input class="form-control mb-3" type="number" name="limit" value="300"><button class="btn btn-brand w-100">Cache Dataset</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Cache Key</th><th>Report</th><th>Rows</th><th>Expires</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['cache_key']); ?></strong><div class="small text-secondary"><?php echo esc($r['filter_hash']); ?></div></td><td><?php echo esc($r['report_type']); ?></td><td><?php echo (int)$r['row_count']; ?></td><td><?php echo esc($r['expires_at']); ?></td><td><span class="badge bg-<?php echo strtotime((string)$r['expires_at'])>time()?'success':'secondary'; ?>"><?php echo strtotime((string)$r['expires_at'])>time()?'cached':'expired'; ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>