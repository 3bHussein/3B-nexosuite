<?php
$pageTitle='API Docs 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('api_docs_2');
$pdo=getDB();
$endpoints=$pdo->query('SELECT * FROM '.table('api_endpoint_catalog').' WHERE status="active" ORDER BY module,http_method,route_path')->fetchAll();
$scopes=$pdo->query('SELECT * FROM '.table('api_scope_policies').' WHERE status="active" ORDER BY module,scope_key')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Developer Documentation</div><h2 class="h4 mb-1">API Documentation 2.0</h2><p class="text-secondary mb-0">REST examples for connecting websites, mobile apps, marketplaces, payment gateways and accounting systems.</p></div></div>
<div class="card-admin p-4 mb-4"><h2 class="h5">Authentication</h2><p>Use a Bearer token generated from API Keys. Keep the token secret.</p><pre class="bg-dark text-white p-3 rounded-4"><code>Authorization: Bearer ec_xxxxxxxxxxxxxxxxx</code></pre><h2 class="h5 mt-4">Example Request</h2><pre class="bg-dark text-white p-3 rounded-4"><code>curl -H "Authorization: Bearer YOUR_TOKEN" <?php echo esc(SITE_URL); ?>/api/v1/products.php</code></pre></div>
<div class="row g-4"><div class="col-xl-8"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Endpoints</h2><table class="table"><thead><tr><th>Method</th><th>Route</th><th>Scope</th><th>Description</th></tr></thead><tbody><?php foreach($endpoints as $e): ?><tr><td><span class="badge bg-primary"><?php echo esc($e['http_method']); ?></span></td><td><code><?php echo esc($e['route_path']); ?></code><div class="small text-secondary"><?php echo esc($e['endpoint_name'].' · '.$e['module']); ?></div></td><td><code><?php echo esc($e['required_scope']); ?></code></td><td><?php echo esc($e['description']); ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Scopes</h2><table class="table"><tbody><?php foreach($scopes as $s): ?><tr><td><strong><code><?php echo esc($s['scope_key']); ?></code></strong><div class="small text-secondary"><?php echo esc($s['scope_name'].' · '.$s['access_level']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>