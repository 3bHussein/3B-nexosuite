<?php
$pageTitle='Developer Docs';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('developer_docs');
$pdo=getDB();
$apiApps=$pdo->query('SELECT * FROM '.table('api_marketplace_apps').' WHERE status="available" ORDER BY category,app_name')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Developer Documentation</div><h2 class="h4 mb-1">API & Webhook Docs</h2><p class="text-secondary mb-0">Use this page as the internal/public developer handoff for API keys, scopes, endpoints, and webhook payloads.</p></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/api-keys.php">Generate API Key</a></div>
<div class="row g-4"><div class="col-xl-8"><div class="card-admin p-4 mb-4"><h2 class="h5">Authentication</h2><p>Use a bearer token generated from <strong>API Keys</strong>.</p><pre class="bg-dark text-white p-3 rounded-4"><code>Authorization: Bearer ec_xxxxxxxxxxxxxxxxx</code></pre><h2 class="h5 mt-4">Example Request</h2><pre class="bg-dark text-white p-3 rounded-4"><code>curl -H "Authorization: Bearer YOUR_TOKEN" "<?php echo esc(SITE_URL); ?>/api/index.php?resource=products"</code></pre><h2 class="h5 mt-4">Webhook Payload</h2><pre class="bg-dark text-white p-3 rounded-4"><code>{
  "event": "order.created",
  "created_at": "2026-01-01T10:00:00+04:00",
  "data": {
    "id": 1001,
    "status": "created"
  }
}</code></pre></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">API Products</h2><table class="table"><thead><tr><th>API</th><th>Category</th><th>Scopes</th></tr></thead><tbody><?php foreach($apiApps as $app): ?><tr><td><strong><?php echo esc($app['app_code']); ?></strong><div class="small text-secondary"><?php echo esc($app['app_name'].' · '.$app['description']); ?></div></td><td><?php echo esc($app['category']); ?></td><td><code><?php echo esc($app['default_scopes']); ?></code></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-4"><div class="card-admin p-4"><h2 class="h5">Available Resources</h2><ul class="mb-0"><li><code>products</code></li><li><code>orders</code></li><li><code>customers</code></li><li><code>inventory</code></li><li><code>reports</code></li><li><code>kpis</code></li></ul><hr><p class="text-secondary mb-0">For public docs, link this content from a frontend page only after reviewing security and token policy.</p></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>