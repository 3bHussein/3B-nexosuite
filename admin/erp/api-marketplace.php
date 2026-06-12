<?php
$pageTitle='API Marketplace';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('api_marketplace');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $code=preg_replace('/[^A-Z0-9_\\-]/','',strtoupper((string)($_POST['app_code']??'')));
    $stmt=$pdo->prepare('INSERT INTO '.table('api_marketplace_apps').' (app_code,app_name,category,description,default_scopes,status,doc_url) VALUES (?,?,?,?,?,"available",?)');
    $stmt->execute([$code,trim((string)$_POST['app_name']),trim((string)$_POST['category']),trim((string)$_POST['description']),trim((string)$_POST['default_scopes']),trim((string)$_POST['doc_url'])]);
    flash('success','API marketplace app created.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'api-marketplace']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/api-marketplace.php');
}
$apps=$pdo->query('SELECT * FROM '.table('api_marketplace_apps').' ORDER BY category,app_name')->fetchAll();
$keys=(int)$pdo->query('SELECT COUNT(*) FROM '.table('api_keys').' WHERE status="active"')->fetchColumn();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Developer Products</div><h2 class="h4 mb-1">API Marketplace</h2><p class="text-secondary mb-0">Publish API products, default scopes, and developer documentation links for external integrations.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/api-keys.php">Create API Key</a></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">API Products</div><div class="metric-sm"><?php echo count($apps); ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Active API Keys</div><div class="metric-sm"><?php echo $keys; ?></div></div></div><div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker">Marketplace</div><div class="metric-sm"><?php echo setting('api_marketplace_enabled','1')==='1'?'ON':'OFF'; ?></div></div></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Create API Product</h2><input class="form-control mb-2" name="app_code" placeholder="APP-CODE" required><input class="form-control mb-2" name="app_name" placeholder="App name" required><input class="form-control mb-2" name="category" placeholder="Commerce / CRM / Reports"><textarea class="form-control mb-2" name="description" rows="3" placeholder="Description"></textarea><textarea class="form-control mb-2" name="default_scopes" rows="2" placeholder="read:products,write:orders"></textarea><input class="form-control mb-3" name="doc_url" placeholder="/developer-docs.php"><button class="btn btn-brand w-100">Create Product</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Product</th><th>Category</th><th>Scopes</th><th>Status</th><th>Docs</th></tr></thead><tbody><?php foreach($apps as $app): ?><tr><td><strong><?php echo esc($app['app_code']); ?></strong><div class="small text-secondary"><?php echo esc($app['app_name']); ?><br><?php echo esc($app['description']); ?></div></td><td><?php echo esc($app['category']); ?></td><td><code><?php echo esc($app['default_scopes']); ?></code></td><td><span class="badge bg-<?php echo esc(statusTone($app['status'])); ?>"><?php echo esc($app['status']); ?></span></td><td><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/developer-docs.php">Open</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>