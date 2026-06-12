<?php
$pageTitle='Subscription Plans';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('subscription_plans');
$pdo=getDB();

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $id=(int)($_POST['id']??0);
    $code=preg_replace('/[^A-Z0-9_]/','',strtoupper((string)($_POST['plan_code']??'')));
    $name=trim((string)($_POST['plan_name']??''));
    if($code===''||$name===''){throw new RuntimeException('Plan code and plan name are required.');}
    $data=[
      $code,$name,trim((string)($_POST['billing_cycle']??'monthly')),
      max(0,(float)($_POST['monthly_price']??0)),max(0,(float)($_POST['yearly_price']??0)),
      max(0,(int)($_POST['user_limit']??0)),max(0,(int)($_POST['branch_limit']??0)),max(0,(int)($_POST['warehouse_limit']??0)),
      max(0,(int)($_POST['product_limit']??0)),max(0,(int)($_POST['storage_limit_mb']??0)),max(0,(int)($_POST['api_call_limit_monthly']??0)),
      in_array((string)($_POST['status']??'active'),['active','inactive'],true)?(string)$_POST['status']:'active',
      trim((string)($_POST['description']??''))
    ];
    if($id>0){
      $stmt=$pdo->prepare('UPDATE '.table('subscription_plans').' SET plan_code=?,plan_name=?,billing_cycle=?,monthly_price=?,yearly_price=?,user_limit=?,branch_limit=?,warehouse_limit=?,product_limit=?,storage_limit_mb=?,api_call_limit_monthly=?,status=?,description=? WHERE id=?');
      $stmt->execute([...$data,$id]);
      logActivity($pdo,'SaaS','subscription_plan_updated','Subscription plan '.$code.' updated.','subscription_plan',$id);
    }else{
      $stmt=$pdo->prepare('INSERT INTO '.table('subscription_plans').' (plan_code,plan_name,billing_cycle,monthly_price,yearly_price,user_limit,branch_limit,warehouse_limit,product_limit,storage_limit_mb,api_call_limit_monthly,status,description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
      $stmt->execute($data);$id=(int)$pdo->lastInsertId();
      logActivity($pdo,'SaaS','subscription_plan_created','Subscription plan '.$code.' created.','subscription_plan',$id);
    }
    flash('success','Subscription plan saved.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'subscription-plans']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/subscription-plans.php');
}
$edit=null;
if(isset($_GET['edit'])){$stmt=$pdo->prepare('SELECT * FROM '.table('subscription_plans').' WHERE id=? LIMIT 1');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch()?:null;}
$plans=$pdo->query('SELECT * FROM '.table('subscription_plans').' ORDER BY monthly_price ASC,id ASC')->fetchAll();
$features=$pdo->query('SELECT pf.*,sp.plan_code FROM '.table('plan_features').' pf LEFT JOIN '.table('subscription_plans').' sp ON sp.id=pf.subscription_plan_id ORDER BY sp.monthly_price ASC,pf.feature_key ASC')->fetchAll();
$featureMap=[];foreach($features as $f){$featureMap[$f['plan_code']][]=$f;}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><div class="erp-kicker">SaaS Commercial Control</div><h2 class="h4 mb-1">Subscription Plans</h2><p class="text-secondary mb-0">Define package limits, pricing, and plan features for SaaS or licensed ERP deployments.</p></div>
  <a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/tenant-usage.php"><?php echo t('Tenant Usage', 'استخدام العملاء'); ?></a>
</div>
<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>">
      <div class="erp-kicker">Plan Editor</div><h2 class="h5 mb-3"><?php echo $edit?'Edit Plan':'Create Plan'; ?></h2>
      <div class="row g-3">
        <div class="col-md-5"><label class="form-label">Code</label><input class="form-control" name="plan_code" value="<?php echo esc($edit['plan_code']??''); ?>" placeholder="GROWTH" required></div>
        <div class="col-md-7"><label class="form-label">Name</label><input class="form-control" name="plan_name" value="<?php echo esc($edit['plan_name']??''); ?>" required></div>
        <div class="col-md-6"><label class="form-label">Monthly Price</label><input class="form-control" type="number" step="0.01" name="monthly_price" value="<?php echo esc($edit['monthly_price']??0); ?>"></div>
        <div class="col-md-6"><label class="form-label">Yearly Price</label><input class="form-control" type="number" step="0.01" name="yearly_price" value="<?php echo esc($edit['yearly_price']??0); ?>"></div>
        <div class="col-md-6"><label class="form-label">User Limit</label><input class="form-control" type="number" name="user_limit" value="<?php echo esc($edit['user_limit']??5); ?>"></div>
        <div class="col-md-6"><label class="form-label">Branch Limit</label><input class="form-control" type="number" name="branch_limit" value="<?php echo esc($edit['branch_limit']??1); ?>"></div>
        <div class="col-md-6"><label class="form-label">Warehouse Limit</label><input class="form-control" type="number" name="warehouse_limit" value="<?php echo esc($edit['warehouse_limit']??1); ?>"></div>
        <div class="col-md-6"><label class="form-label">Product Limit</label><input class="form-control" type="number" name="product_limit" value="<?php echo esc($edit['product_limit']??1000); ?>"></div>
        <div class="col-md-6"><label class="form-label">Storage MB</label><input class="form-control" type="number" name="storage_limit_mb" value="<?php echo esc($edit['storage_limit_mb']??1024); ?>"></div>
        <div class="col-md-6"><label class="form-label">API Calls / Month</label><input class="form-control" type="number" name="api_call_limit_monthly" value="<?php echo esc($edit['api_call_limit_monthly']??10000); ?>"></div>
        <div class="col-md-6"><label class="form-label">Billing Cycle</label><select class="form-select" name="billing_cycle"><option value="monthly" <?php echo (($edit['billing_cycle']??'monthly')==='monthly')?'selected':''; ?>>Monthly</option><option value="yearly" <?php echo (($edit['billing_cycle']??'monthly')==='yearly')?'selected':''; ?>>Yearly</option></select></div>
        <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo (($edit['status']??'active')==='active')?'selected':''; ?>>Active</option><option value="inactive" <?php echo (($edit['status']??'active')==='inactive')?'selected':''; ?>>Inactive</option></select></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?php echo esc($edit['description']??''); ?></textarea></div>
      </div>
      <button class="btn btn-brand w-100 mt-3">Save Plan</button>
      <?php if($edit): ?><a class="btn btn-outline-secondary w-100 mt-2" href="<?php echo esc(ADMIN_URL); ?>/erp/subscription-plans.php">Cancel Edit</a><?php endif; ?>
    </form>
  </div>
  <div class="col-xl-8">
    <div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">Plan Register</div><h2 class="h5 mb-0">Commercial Packages</h2></div></div>
      <table class="table align-middle"><thead><tr><th>Plan</th><th>Price</th><th>Limits</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($plans as $plan): ?><tr><td><strong><?php echo esc($plan['plan_code']); ?> · <?php echo esc($plan['plan_name']); ?></strong><div class="small text-secondary"><?php echo esc($plan['description']); ?></div></td><td><?php echo money($plan['monthly_price']); ?>/mo<div class="small text-secondary"><?php echo money($plan['yearly_price']); ?>/yr</div></td><td><?php echo (int)$plan['user_limit']; ?> users · <?php echo (int)$plan['branch_limit']; ?> branches<div class="small text-secondary"><?php echo (int)$plan['product_limit']; ?> products · <?php echo (int)$plan['storage_limit_mb']; ?> MB</div></td><td><span class="badge bg-<?php echo esc(statusTone($plan['status'])); ?>"><?php echo esc($plan['status']); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int)$plan['id']; ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table>
    </div>
    <div class="table-wrap table-responsive"><h2 class="h5 mb-3">Feature Matrix</h2><table class="table"><tbody><?php foreach($featureMap as $planCode=>$rows): ?><tr><td><strong><?php echo esc($planCode); ?></strong></td><td><?php foreach($rows as $f): ?><span class="badge me-1 mb-1 bg-<?php echo (int)$f['is_enabled']===1?'success':'secondary'; ?>"><?php echo esc($f['feature_key']); ?></span><?php endforeach; ?></td></tr><?php endforeach; ?></tbody></table></div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>