<?php
$pageTitle='Customer Credit Control';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('credit_control');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $customerId=(int)($_POST['customer_id']??0);
  $creditStatus=in_array((string)($_POST['credit_status']??'open'),['open','hold'],true)?(string)$_POST['credit_status']:'open';
  $reason=trim((string)($_POST['credit_hold_reason']??''));
  $stmt=$pdo->prepare('SELECT company_id,branch_id,customer_code FROM '.table('customers').' WHERE id=? LIMIT 1');$stmt->execute([$customerId]);$customer=$stmt->fetch();
  if(!$customer){flash('error','Customer not found.');redirect(ADMIN_URL.'/erp/credit-control.php');}
  enforceScopeAllowed($pdo,(int)($customer['company_id']??0),(int)($customer['branch_id']??0),0,true);
  $pdo->prepare('UPDATE '.table('customers').' SET credit_status=?,credit_hold_reason=? WHERE id=?')->execute([$creditStatus,$creditStatus==='hold'?$reason:null,$customerId]);
  logActivity($pdo,'Credit Control','customer_credit_status_updated','Customer '.$customer['customer_code'].' credit status updated to '.$creditStatus.'.','customer',$customerId);
  flash('success','Customer credit status updated.');
  redirect(ADMIN_URL.'/erp/credit-control.php');
}

$params=[];$condition=selectedScopeCondition('c',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'c',$params,false);$where=' WHERE c.customer_type="b2b"'.$condition;
$stmt=$pdo->prepare('SELECT c.* FROM '.table('customers').' c'.$where.' ORDER BY c.credit_status DESC,c.company_name ASC,c.contact_name ASC LIMIT 300');
$stmt->execute($params);$customers=$stmt->fetchAll();
$rows=[];$holdCount=0;$totalLimit=0.0;$totalExposure=0.0;$totalAvailable=0.0;
foreach($customers as $customer){
  $snap=customerCreditSnapshot($pdo,(int)$customer['id'],0);
  $rows[]=['customer'=>$customer,'snapshot'=>$snap];
  if(($customer['credit_status']??'open')==='hold'){$holdCount++;}
  $totalLimit+=(float)($snap['limit']??0);$totalExposure+=(float)($snap['exposure']??0);$totalAvailable+=(float)($snap['available']??0);
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Receivables Risk Control</div><h2 class="h4 mb-1">Customer Credit Control</h2><p class="text-secondary mb-0">Monitor credit limits, open invoice exposure, sales order exposure, and customer credit holds.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-orders.php"><?php echo t('Sales Orders', 'أوامر البيع'); ?></a></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Credit Limit</div><div class="metric-sm"><?php echo money($totalLimit); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Exposure</div><div class="metric-sm"><?php echo money($totalExposure); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Available</div><div class="metric-sm <?php echo $totalAvailable>=0?'money-positive':'money-negative'; ?>"><?php echo money($totalAvailable); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Credit Holds</div><div class="metric-sm money-negative"><?php echo (int)$holdCount; ?></div></div></div></div>
<div class="card-admin p-3 mb-4"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Apply</button></form></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Credit Register</div><h2 class="h5 mb-0">B2B Customer Exposure</h2></div></div><table class="table align-middle"><thead><tr><th>Customer</th><th>Limit</th><th>Exposure</th><th>Available</th><th>Status</th><th>Control</th></tr></thead><tbody><?php foreach($rows as $row): $customer=$row['customer'];$snap=$row['snapshot']; ?><tr><td><strong><?php echo esc($customer['customer_code'].' · '.trim(($customer['company_name']?:'').' '.($customer['contact_name']?:''))); ?></strong><div class="small text-secondary"><?php echo esc($customer['credit_hold_reason']?:''); ?></div></td><td><?php echo money($snap['limit']??0); ?></td><td><?php echo money($snap['exposure']??0); ?></td><td class="<?php echo (float)($snap['available']??0)>=0?'money-positive':'money-negative'; ?>"><?php echo money($snap['available']??0); ?></td><td><span class="badge bg-<?php echo esc(($customer['credit_status']??'open')==='hold'?'danger':'success'); ?>"><?php echo esc(ucfirst($customer['credit_status']??'open')); ?></span></td><td><form method="post" class="d-flex flex-column gap-2"><input type="hidden" name="customer_id" value="<?php echo (int)$customer['id']; ?>"><select class="form-select form-select-sm" name="credit_status"><option value="open" <?php echo ($customer['credit_status']??'open')==='open'?'selected':''; ?>>Open</option><option value="hold" <?php echo ($customer['credit_status']??'open')==='hold'?'selected':''; ?>>Hold</option></select><input class="form-control form-control-sm" name="credit_hold_reason" value="<?php echo esc($customer['credit_hold_reason']??''); ?>" placeholder="Hold reason"><button class="btn btn-sm btn-outline-primary">Save</button></form></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No B2B credit-control customers found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>