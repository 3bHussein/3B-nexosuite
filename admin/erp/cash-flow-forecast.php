<?php
$pageTitle='Cash Flow Forecast';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('cash_flow_forecast');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{generateCashFlowForecast($pdo,trim((string)$_POST['forecast_name']),trim((string)$_POST['date_from']),trim((string)$_POST['date_to']),max(0,(float)$_POST['opening_cash']));flash('success','Cash flow forecast generated.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'cash-flow-forecast']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/cash-flow-forecast.php');
}
$forecasts=$pdo->query('SELECT * FROM '.table('cash_flow_forecasts').' ORDER BY created_at DESC LIMIT 80')->fetchAll();
$selected=(int)($_GET['id']??($forecasts[0]['id']??0));$lines=[];
if($selected){$stmt=$pdo->prepare('SELECT * FROM '.table('cash_flow_forecast_lines').' WHERE cash_flow_forecast_id=? ORDER BY line_date');$stmt->execute([$selected]);$lines=$stmt->fetchAll();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Treasury Planning</div><h2 class="h4 mb-1">Cash Flow Forecast</h2><p class="text-secondary mb-0">Forecast inflows from invoices and outflows from payables.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Generate Forecast</h2><input class="form-control mb-2" name="forecast_name" value="Forecast <?php echo date('Y-m-d'); ?>"><div class="row g-2"><div class="col-6"><input class="form-control" type="date" name="date_from" value="<?php echo date('Y-m-d'); ?>"></div><div class="col-6"><input class="form-control" type="date" name="date_to" value="<?php echo date('Y-m-d',strtotime('+'.(int)setting('finance_forecast_days','30').' days')); ?>"></div></div><input class="form-control my-3" type="number" step="0.01" name="opening_cash" placeholder="Opening cash"><button class="btn btn-brand w-100">Generate</button></form><div class="table-wrap table-responsive mt-4"><table class="table"><tbody><?php foreach($forecasts as $f): ?><tr><td><a href="?id=<?php echo (int)$f['id']; ?>"><strong><?php echo esc($f['forecast_number']); ?></strong></a><div class="small text-secondary"><?php echo esc($f['forecast_name']); ?></div></td><td><?php echo money($f['closing_cash']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Forecast Lines</h2><table class="table"><thead><tr><th>Date</th><th>Source</th><th>Description</th><th>Inflow</th><th>Outflow</th></tr></thead><tbody><?php foreach($lines as $l): ?><tr><td><?php echo esc($l['line_date']); ?></td><td><?php echo esc($l['source_type']); ?></td><td><?php echo esc($l['description']); ?></td><td><?php echo money($l['inflow']); ?></td><td><?php echo money($l['outflow']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>