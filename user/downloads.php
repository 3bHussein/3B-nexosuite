<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
websitePermissionGuard('customer_downloads');

$pdo = getDB();
$user = currentUser();
$userId = (int)($user['id'] ?? 0);
$userEmail = trim((string)($user['email'] ?? ''));

$q = trim((string)($_GET['q'] ?? ''));

$productParams = [$userId, $userEmail];
$productSql = 'SELECT p.*,
                      c.name AS category_name,
                      po.id AS order_id,
                      po.order_number,
                      po.payment_status,
                      po.order_status,
                      po.created_at AS purchased_at,
                      d.id AS download_id,
                      d.title AS download_title,
                      d.description AS download_description,
                      d.file_name AS linked_download_file,
                      d.file_size AS linked_download_size,
                      d.download_count AS linked_download_count
               FROM ' . table('products') . ' p
               INNER JOIN (
                    SELECT oi.product_id, MAX(o.id) AS order_id
                    FROM ' . table('order_items') . ' oi
                    INNER JOIN ' . table('orders') . ' o ON o.id = oi.order_id
                    WHERE oi.product_id IS NOT NULL
                      AND (o.user_id = ? OR o.customer_email = ?)
                      AND COALESCE(o.order_status, "") NOT IN ("cancelled","refunded","void")
                    GROUP BY oi.product_id
               ) purchased ON purchased.product_id = p.id
               INNER JOIN ' . table('orders') . ' po ON po.id = purchased.order_id
               LEFT JOIN ' . table('categories') . ' c ON c.id = p.category_id
               LEFT JOIN ' . table('downloads') . ' d
                 ON d.active = 1
                AND p.download_file <> ""
                AND (
                    d.file_name = p.download_file
                    OR SUBSTRING_INDEX(d.file_name, "/", -1) = SUBSTRING_INDEX(p.download_file, "/", -1)
                )
               WHERE p.active = 1
                 AND (p.downloadable = 1 OR p.download_file <> "" OR d.id IS NOT NULL)';

if ($q !== '') {
    $productSql .= ' AND (
        p.name LIKE ?
        OR p.sku LIKE ?
        OR p.download_file LIKE ?
        OR d.title LIKE ?
        OR d.file_name LIKE ?
        OR c.name LIKE ?
        OR po.order_number LIKE ?
    )';
    $term = '%' . $q . '%';
    array_push($productParams, $term, $term, $term, $term, $term, $term, $term);
}
$productSql .= ' ORDER BY po.created_at DESC, p.name ASC';

$productStmt = $pdo->prepare($productSql);
$productStmt->execute($productParams);
$purchasedResources = $productStmt->fetchAll();

$generalParams = [];
$generalSql = 'SELECT d.*,
                      p.id AS product_id,
                      p.name AS product_name,
                      p.slug AS product_slug,
                      p.sku AS product_sku,
                      p.image AS product_image,
                      p.price AS product_price,
                      p.download_file AS product_download_file,
                      c.name AS category_name
               FROM ' . table('downloads') . ' d
               LEFT JOIN ' . table('products') . ' p
                 ON p.active = 1
                AND p.download_file <> ""
                AND (
                    p.download_file = d.file_name
                    OR SUBSTRING_INDEX(p.download_file, "/", -1) = SUBSTRING_INDEX(d.file_name, "/", -1)
                )
               LEFT JOIN ' . table('categories') . ' c ON c.id = p.category_id
               WHERE d.active = 1';

if ($q !== '') {
    $generalSql .= ' AND (
        d.title LIKE ?
        OR d.description LIKE ?
        OR d.file_name LIKE ?
        OR p.name LIKE ?
        OR p.sku LIKE ?
        OR c.name LIKE ?
    )';
    $term = '%' . $q . '%';
    $generalParams = [$term, $term, $term, $term, $term, $term];
}
$generalSql .= ' ORDER BY d.created_at DESC, d.title ASC LIMIT 100';
$generalStmt = $pdo->prepare($generalSql);
$generalStmt->execute($generalParams);
$generalDownloads = $generalStmt->fetchAll();

$totalPurchased = count($purchasedResources);
$directProductFiles = 0;
$linkedDownloadFiles = 0;
foreach ($purchasedResources as $resource) {
    if (!empty($resource['download_file'])) {
        $directProductFiles++;
    }
    if (!empty($resource['download_id'])) {
        $linkedDownloadFiles++;
    }
}

siteHeader('My Downloads', 'login');
?>
<style>
.customer-downloads-page{display:grid;gap:26px}
.customer-downloads-hero{background:linear-gradient(135deg,#0f172a,#1e3a8a 62%,#e6005c);color:#fff;border-radius:30px;padding:30px;display:grid;grid-template-columns:minmax(0,1.35fr) minmax(240px,.65fr);gap:22px;align-items:center;overflow:hidden;position:relative}
.customer-downloads-hero:after{content:"";position:absolute;right:-90px;bottom:-130px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.12)}
.customer-downloads-hero h1{font-size:clamp(2rem,4vw,3.5rem);font-weight:900;letter-spacing:-.04em;line-height:1;margin:8px 0 14px}
.customer-downloads-hero p{color:rgba(255,255,255,.82);line-height:1.65;margin:0;max-width:760px}
.customer-pill{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18);border-radius:999px;padding:7px 12px;font-weight:800;font-size:.82rem;text-transform:uppercase;letter-spacing:.04em}
.customer-stats{display:grid;gap:12px;position:relative;z-index:1}
.customer-stat{background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);border-radius:20px;padding:16px;backdrop-filter:blur(10px)}
.customer-stat strong{display:block;font-size:1.8rem;font-weight:900;line-height:1}
.customer-stat span{display:block;color:rgba(255,255,255,.78);margin-top:6px}
.download-toolbar{background:#fff;border:1px solid #e6eaf2;border-radius:22px;padding:15px;box-shadow:0 12px 32px rgba(15,23,42,.06)}
.download-search{display:flex;gap:10px}
.download-search .search-box{flex:1;display:flex;align-items:center;gap:10px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:16px;padding:0 14px}
.download-search input{width:100%;border:0;background:transparent;outline:0;padding:14px 0}
.purchased-download-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.purchased-download-card{background:#fff;border:1px solid #e6eaf2;border-radius:26px;padding:18px;box-shadow:0 16px 36px rgba(15,23,42,.06);display:grid;gap:14px}
.product-main{display:grid;grid-template-columns:112px minmax(0,1fr);gap:14px;align-items:center}
.product-main img{width:112px;height:112px;object-fit:contain;background:#f8fafc;border:1px solid #edf2f7;border-radius:20px;padding:10px}
.product-main h2{font-size:1.2rem;font-weight:900;letter-spacing:-.02em;margin:0 0 6px}
.product-main p{margin:0;color:#667085;line-height:1.5}
.tag-row{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px}
.tag-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;background:#eef2ff;color:#1e3a8a;font-size:.72rem;font-weight:900;padding:5px 9px}
.tag-pill.success{background:#ecfdf3;color:#027a48}
.tag-pill.warn{background:#fff7ed;color:#c2410c}
.file-box{border:1px solid #e6eaf2;border-radius:18px;background:linear-gradient(180deg,#f8fafc,#fff);padding:12px;display:grid;gap:7px}
.file-box strong{font-size:.95rem;color:#0f172a}
.file-box small{color:#667085}
.download-actions{display:flex;flex-wrap:wrap;gap:8px}
.general-download-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.general-download-card{background:#fff;border:1px solid #e6eaf2;border-radius:22px;padding:16px;box-shadow:0 12px 30px rgba(15,23,42,.05);display:grid;gap:10px}
.general-download-card h3{font-size:1.02rem;font-weight:900;margin:0}
.general-download-card p{color:#667085;line-height:1.5;margin:0}
.empty-state-box{background:#fff;border:1px dashed #cbd5e1;border-radius:24px;padding:36px;text-align:center;color:#667085}
.help-box{border:1px solid #fde68a;background:#fffbeb;color:#92400e;border-radius:22px;padding:18px}
@media(max-width:991px){.customer-downloads-hero{grid-template-columns:1fr}.general-download-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:720px){.customer-downloads-hero{padding:22px;border-radius:22px}.download-search{flex-direction:column}.purchased-download-grid,.general-download-grid{grid-template-columns:1fr}.product-main{grid-template-columns:1fr}.product-main img{width:100%;height:180px}}
</style>

<div class="customer-downloads-page">
  <section class="customer-downloads-hero">
    <div>
      <span class="customer-pill"><i class="bi bi-bag-check"></i> Purchased product downloads</span>
      <h1>My Downloads</h1>
      <p>This page shows downloadable products that this customer bought. It checks the logged-in customer orders, then shows products marked as downloadable or products with a download file configured.</p>
    </div>
    <div class="customer-stats">
      <div class="customer-stat"><strong><?php echo $totalPurchased; ?></strong><span>Purchased downloadable products</span></div>
      <div class="customer-stat"><strong><?php echo $directProductFiles; ?></strong><span>Products with download file</span></div>
      <div class="customer-stat"><strong><?php echo $linkedDownloadFiles; ?></strong><span>Matched download records</span></div>
    </div>
  </section>

  <section class="download-toolbar">
    <form method="get" class="download-search">
      <label class="visually-hidden" for="download-search">Search downloads</label>
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input id="download-search" type="search" name="q" value="<?php echo esc($q); ?>" placeholder="Search bought product, SKU, order number, file name...">
      </div>
      <button class="btn btn-brand">Search</button>
      <?php if ($q !== ''): ?><a class="btn btn-soft-outline" href="<?php echo esc(SITE_URL); ?>/user/downloads.php">Clear</a><?php endif; ?>
    </form>
  </section>

  <?php if (!$purchasedResources): ?>
    <section class="empty-state-box">
      <i class="bi bi-cloud-arrow-down fs-1 d-block mb-2"></i>
      <h2 class="h4">No purchased downloadable products found</h2>
      <p class="mb-3">This means the logged-in customer has no order items connected to products marked as <strong>Downloadable</strong> or products with a <strong>Download File</strong>.</p>
      <div class="help-box text-start">
        <strong>Checklist:</strong>
        <ol class="mb-0 mt-2">
          <li>Customer must be logged in with the same email/user used in the order.</li>
          <li>The order must contain this product in <code>order_items.product_id</code>.</li>
          <li>The product must have <code>downloadable = 1</code> or <code>download_file</code> filled.</li>
          <li>Optional download record can be linked when <code>downloads.file_name</code> matches <code>products.download_file</code>.</li>
        </ol>
      </div>
    </section>
  <?php else: ?>
    <section>
      <div class="section-heading-clean">
        <span class="eyebrow">Bought products</span>
        <h2>Download files for products you purchased</h2>
        <p>The download button is protected and checks customer purchase history before allowing the file.</p>
      </div>

      <div class="purchased-download-grid">
        <?php foreach ($purchasedResources as $resource): ?>
          <?php
            $hasProductFile = trim((string)($resource['download_file'] ?? '')) !== '';
            $hasDownloadRecord = !empty($resource['download_id']);
            $fileName = $hasDownloadRecord ? (string)$resource['linked_download_file'] : (string)$resource['download_file'];
            $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');
          ?>
          <article class="purchased-download-card">
            <div class="product-main">
              <img src="<?php echo esc(productImageUrl($resource)); ?>" alt="<?php echo esc($resource['name']); ?>">
              <div>
                <h2><?php echo esc($resource['name']); ?></h2>
                <p><?php echo esc($resource['short_description'] ?: 'Purchased product resource.'); ?></p>
                <div class="tag-row">
                  <span class="tag-pill"><?php echo esc($resource['category_name'] ?: 'Product'); ?></span>
                  <span class="tag-pill">SKU: <?php echo esc($resource['sku'] ?: 'N/A'); ?></span>
                  <span class="tag-pill success">Order: <?php echo esc($resource['order_number']); ?></span>
                  <span class="tag-pill <?php echo $hasProductFile ? 'success' : 'warn'; ?>"><?php echo $hasProductFile ? 'Product file set' : 'No file set'; ?></span>
                </div>
              </div>
            </div>

            <div class="file-box">
              <strong><i class="bi bi-file-earmark-arrow-down"></i> <?php echo esc($hasDownloadRecord ? ($resource['download_title'] ?: $resource['name']) : $resource['name']); ?></strong>
              <small>File: <?php echo esc($fileName ?: 'No file configured'); ?></small>
              <small>Type: <?php echo esc($extension); ?> · <?php echo $hasDownloadRecord ? 'Matched download library record' : 'Direct product download file'; ?></small>
              <small>Purchased: <?php echo esc(date('M d, Y', strtotime((string)$resource['purchased_at']))); ?> · Payment: <?php echo esc($resource['payment_status']); ?> · Order: <?php echo esc($resource['order_status']); ?></small>
            </div>

            <div class="download-actions">
              <?php if ($hasProductFile): ?>
                <a class="btn btn-brand btn-sm" href="<?php echo esc(SITE_URL); ?>/user/product-download-file.php?id=<?php echo (int)$resource['id']; ?>">
                  <i class="bi bi-download"></i> Download Product File
                </a>
              <?php endif; ?>
              <?php if ($hasDownloadRecord): ?>
                <a class="btn btn-soft-outline btn-sm" href="<?php echo esc(SITE_URL); ?>/user/download-file.php?id=<?php echo (int)$resource['download_id']; ?>">
                  <i class="bi bi-cloud-arrow-down"></i> Download Library File
                </a>
              <?php endif; ?>
              <a class="btn btn-soft-outline btn-sm" href="<?php echo esc(SITE_URL); ?>/products/product-details.php?slug=<?php echo urlencode((string)$resource['slug']); ?>">
                <i class="bi bi-box-arrow-up-right"></i> View Product
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($generalDownloads): ?>
    <section>
      <div class="section-heading-clean">
        <span class="eyebrow">General library</span>
        <h2>Other published downloads</h2>
        <p>These are active library downloads. Purchased product downloads are shown above.</p>
      </div>
      <div class="general-download-grid">
        <?php foreach ($generalDownloads as $download): ?>
          <?php $hasLinkedProduct = !empty($download['product_slug']); ?>
          <article class="general-download-card">
            <span class="tag-pill"><?php echo esc(strtoupper(pathinfo((string)$download['file_name'], PATHINFO_EXTENSION) ?: 'FILE')); ?></span>
            <h3><?php echo esc($download['title']); ?></h3>
            <p><?php echo esc($download['description']); ?></p>
            <?php if ($hasLinkedProduct): ?>
              <small class="text-secondary">Linked product: <?php echo esc($download['product_name']); ?> · SKU: <?php echo esc($download['product_sku'] ?: 'N/A'); ?></small>
            <?php else: ?>
              <small class="text-secondary">No linked product record.</small>
            <?php endif; ?>
            <div class="download-actions">
              <a class="btn btn-soft-outline btn-sm" href="<?php echo esc(SITE_URL); ?>/user/download-file.php?id=<?php echo (int)$download['id']; ?>">Download</a>
              <?php if ($hasLinkedProduct): ?>
                <a class="btn btn-soft-outline btn-sm" href="<?php echo esc(SITE_URL); ?>/products/product-details.php?slug=<?php echo urlencode((string)$download['product_slug']); ?>">View Product</a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php siteFooter(); ?>