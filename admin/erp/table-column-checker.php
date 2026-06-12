<?php
$pageTitle='Table & Column Checker';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('table_column_checker');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$r=p34RunSchemaCheck($pdo);flash('success',$r['summary']);}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'table-column-checker']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/table-column-checker.php');
}
$latest=$pdo->query('SELECT * FROM '.table('production_schema_checks').' ORDER BY created_at DESC LIMIT 1')->fetch();
$items=[];if($latest){$s=$pdo->prepare('SELECT * FROM '.table('production_schema_check_items').' WHERE production_schema_check_id=? ORDER BY status DESC,item_type,table_name,column_name');$s->execute([(int)$latest['id']]);$items=$s->fetchAll();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Database Integrity</div><h2 class="h4 mb-1">Table & Column Checker</h2><p class="text-secondary mb-0">Check critical ERP tables and columns against the expected production schema.</p></div><form method="post"><button class="btn btn-brand">Run Table/Column Check</button></form></div>
<?php if($latest): ?><div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Tables</div><div class="metric-sm"><?php echo (int)$latest['tables_checked']; ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Columns</div><div class="metric-sm"><?php echo (int)$latest['columns_checked']; ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Missing Tables</div><div class="metric-sm"><?php echo (int)$latest['missing_tables']; ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Missing Columns</div><div class="metric-sm"><?php echo (int)$latest['missing_columns']; ?></div></div></div></div><?php endif; ?>
<div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Table</th><th>Column</th><th>Type</th><th>Status</th><th>Expected</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><strong><?php echo esc($i['table_name']); ?></strong></td><td><?php echo esc($i['column_name']?:'-'); ?></td><td><?php echo esc($i['item_type']); ?></td><td><span class="badge bg-<?php echo $i['exists_flag']?'success':'danger'; ?>"><?php echo esc($i['status']); ?></span></td><td><?php echo esc($i['expected_definition']); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>