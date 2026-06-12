<?php
$pageTitle='Feature Comparison Sheet';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('feature_comparison_sheet');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{p35CreateFeatureComparisonItem($pdo,trim((string)$_POST['feature_area']),trim((string)$_POST['feature_name']),trim((string)$_POST['our_system']),trim((string)$_POST['sap_oracle_comparison']),trim((string)$_POST['business_value']),(int)$_POST['sort_order']);flash('success','Feature comparison item created.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'feature-comparison-sheet']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/feature-comparison-sheet.php');
}
$rows=$pdo->query('SELECT * FROM '.table('feature_comparison_items').' ORDER BY sort_order,feature_area,feature_name')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Sales Comparison</div><h2 class="h4 mb-1">Feature Comparison Sheet</h2><p class="text-secondary mb-0">Position the system against enterprise ERP platforms using practical implementation value.</p></div><button class="btn btn-outline-primary" onclick="window.print()">Print / Save PDF</button></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Add Comparison</h2><input class="form-control mb-2" name="feature_area" placeholder="Finance"><input class="form-control mb-2" name="feature_name" placeholder="Accounting"><input class="form-control mb-2" name="our_system" placeholder="Included"><input class="form-control mb-2" name="sap_oracle_comparison" placeholder="Requires implementation"><textarea class="form-control mb-2" name="business_value" rows="3"></textarea><input class="form-control mb-3" type="number" name="sort_order" value="10"><button class="btn btn-brand w-100">Add</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Area</th><th>Feature</th><th>Our System</th><th>SAP/Oracle Position</th><th>Business Value</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?php echo esc($r['feature_area']); ?></td><td><strong><?php echo esc($r['feature_name']); ?></strong></td><td><?php echo esc($r['our_system']); ?></td><td><?php echo esc($r['sap_oracle_comparison']); ?></td><td><?php echo esc($r['business_value']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>