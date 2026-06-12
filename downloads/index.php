<?php
require_once dirname(__DIR__) . '/includes/functions.php';
websitePermissionGuard('website_public_downloads');

$pdo = getDB();
$q = trim((string)($_GET['q'] ?? ''));
$params = [];

$sql = 'SELECT d.*,
               p.id AS product_id,
               p.name AS product_name,
               p.slug AS product_slug,
               p.sku AS product_sku,
               p.price AS product_price,
               p.image AS product_image,
               p.short_description AS product_short_description,
               p.downloadable AS product_downloadable,
               c.name AS category_name
        FROM ' . table('downloads') . ' d
        LEFT JOIN ' . table('products') . ' p
          ON p.active = 1
         AND p.download_file <> ""
         AND p.download_file = d.file_name
        LEFT JOIN ' . table('categories') . ' c ON c.id = p.category_id
        WHERE d.active = 1';

if ($q !== '') {
    $sql .= ' AND (
        d.title LIKE ?
        OR d.description LIKE ?
        OR d.file_name LIKE ?
        OR p.name LIKE ?
        OR p.sku LIKE ?
        OR c.name LIKE ?
    )';
    $term = '%' . $q . '%';
    $params = [$term, $term, $term, $term, $term, $term];
}

$sql .= ' ORDER BY d.created_at DESC, d.title ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$downloads = $stmt->fetchAll();

$totalResources = (int)$pdo->query('SELECT COUNT(*) FROM ' . table('downloads') . ' WHERE active = 1')->fetchColumn();
$linkedResources = (int)$pdo->query('SELECT COUNT(*) FROM ' . table('downloads') . ' d INNER JOIN ' . table('products') . ' p ON p.active=1 AND p.download_file<>"" AND p.download_file=d.file_name WHERE d.active=1')->fetchColumn();
$downloadableProducts = $pdo->query('SELECT p.*, c.name AS category_name FROM ' . table('products') . ' p LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id WHERE p.active=1 AND (p.downloadable=1 OR p.download_file<>"") ORDER BY p.featured DESC, p.created_at DESC LIMIT 8')->fetchAll();

$accountUrl = isLoggedIn() ? SITE_URL . '/user/downloads.php' : SITE_URL . '/user/login.php';
$productsUrl = SITE_URL . '/products/index.php';

siteHeader('Downloads', 'downloads');
?>
<style>
.downloads-product-page{display:grid;gap:28px}
.downloads-product-hero{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.65fr);gap:24px;align-items:stretch;background:linear-gradient(135deg,#07111f,#122b4a 55%,#e6005c);border-radius:32px;padding:34px;color:#fff;overflow:hidden;position:relative}
.downloads-product-hero:after{content:"";position:absolute;inset:auto -90px -120px auto;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.14)}
.downloads-product-hero h1{font-size:clamp(2rem,4vw,4rem);font-weight:900;line-height:.95;margin:8px 0 16px;letter-spacing:-.05em}
.downloads-product-hero p{max-width:760px;color:rgba(255,255,255,.82);font-size:1.08rem;line-height:1.65}
.hero-pill{display:inline-flex;gap:8px;align-items:center;border-radius:999px;background:rgba(255,255,255,.14);padding:8px 12px;font-weight:800;font-size:.82rem;text-transform:uppercase;letter-spacing:.04em}
.downloads-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}
.downloads-stats{display:grid;gap:12px;position:relative;z-index:1}
.downloads-stat{background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);border-radius:22px;padding:18px;backdrop-filter:blur(10px)}
.downloads-stat strong{display:block;font-size:2rem;line-height:1;font-weight:900}
.downloads-stat span{display:block;color:rgba(255,255,255,.78);margin-top:7px}
.downloads-toolbar{background:#fff;border:1px solid #e6eaf2;border-radius:24px;padding:16px;box-shadow:0 14px 35px rgba(15,23,42,.06)}
.downloads-search{display:flex;gap:10px;align-items:center}
.downloads-search .search-box{flex:1;display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:0 14px}
.downloads-search input{border:0;background:transparent;outline:0;width:100%;padding:14px 0}
.linked-download-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.linked-download-card{background:#fff;border:1px solid #e6eaf2;border-radius:26px;padding:18px;box-shadow:0 16px 36px rgba(15,23,42,.06);display:flex;flex-direction:column;gap:14px}
.download-icon-row{display:flex;align-items:center;justify-content:space-between}
.download-icon{width:52px;height:52px;border-radius:18px;background:#fff1f7;color:#e6005c;display:grid;place-items:center;font-size:1.35rem}
.download-ext{font-size:.72rem;font-weight:900;color:#1e3a8a;background:#eef2ff;border-radius:999px;padding:5px 10px}
.linked-download-card h2{font-size:1.16rem;margin:0;font-weight:900;letter-spacing:-.02em}
.linked-download-card p{color:#667085;line-height:1.55;margin:0}
.product-link-box{border:1px solid #e6eaf2;border-radius:18px;padding:12px;background:linear-gradient(180deg,#f8fafc,#fff)}
.product-link-box small{display:block;color:#667085;font-size:.76rem;text-transform:uppercase;font-weight:800;letter-spacing:.05em}
.product-link-box strong{display:block;margin-top:2px;color:#0f172a}
.download-meta-list{display:grid;gap:7px;color:#667085;font-size:.86rem}
.download-meta-list span{display:flex;gap:8px;align-items:center}
.download-card-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:auto}
.download-product-section{display:grid;gap:16px}
.download-product-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.download-product-card{background:#fff;border:1px solid #e6eaf2;border-radius:24px;padding:14px;text-decoration:none;color:#0f172a;box-shadow:0 12px 30px rgba(15,23,42,.05);transition:.18s ease;display:grid;gap:10px}
.download-product-card:hover{transform:translateY(-3px);box-shadow:0 18px 38px rgba(15,23,42,.09)}
.download-product-card img{width:100%;height:150px;object-fit:contain;background:#f8fafc;border-radius:18px;padding:10px}
.download-product-card strong{font-size:.98rem;line-height:1.25}
.download-product-card small{color:#667085}
.empty-download-state{background:#fff;border:1px dashed #cbd5e1;border-radius:26px;padding:40px;text-align:center}
@media(max-width:991px){.downloads-product-hero{grid-template-columns:1fr}.linked-download-grid,.download-product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.downloads-product-hero{padding:24px;border-radius:24px}.downloads-search{flex-direction:column;align-items:stretch}.linked-download-grid,.download-product-grid{grid-template-columns:1fr}.download-product-card img{height:180px}}
</style>

<div class="downloads-product-page">
  <nav class="page-breadcrumb" aria-label="breadcrumb">
    <a href="<?php echo esc(SITE_URL); ?>/index.php">Home</a>
    <i class="bi bi-chevron-right"></i>
    <span>Downloads</span>
  </nav>

  <section class="downloads-product-hero">
    <div>
      <span class="hero-pill"><i class="bi bi-cloud-arrow-down"></i> Product-linked resource library</span>
      <h1>Downloads connected directly to your products.</h1>
      <p>Use this page for manuals, activation files, warranty guides, training packs, installation checklists, and digital product resources. Each download can automatically show its related product when the file name matches the product download file.</p>
      <div class="downloads-actions">
        <a class="btn btn-light" href="<?php echo esc($productsUrl); ?>"><i class="bi bi-box-seam"></i> Browse Products</a>
        <a class="btn btn-outline-light" href="<?php echo esc($accountUrl); ?>"><i class="bi bi-person-circle"></i> <?php echo isLoggedIn() ? 'My Downloads' : 'Customer Login'; ?></a>
      </div>
    </div>
    <div class="downloads-stats">
      <div class="downloads-stat"><strong><?php echo $totalResources; ?></strong><span>Total published downloads</span></div>
      <div class="downloads-stat"><strong><?php echo $linkedResources; ?></strong><span>Downloads linked to products</span></div>
      <div class="downloads-stat"><strong><?php echo count($downloadableProducts); ?></strong><span>Downloadable products shown</span></div>
    </div>
  </section>

  <section class="downloads-toolbar">
    <form method="get" class="downloads-search">
      <label class="visually-hidden" for="download-search">Search downloads</label>
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input id="download-search" type="search" name="q" value="<?php echo esc($q); ?>" placeholder="Search by file, manual, product name, SKU or category...">
      </div>
      <button class="btn btn-brand">Search</button>
      <?php if ($q !== ''): ?>
        <a class="btn btn-soft-outline" href="<?php echo esc(SITE_URL); ?>/downloads/index.php">Clear</a>
      <?php endif; ?>
    </form>
  </section>

  <?php if (!$downloads): ?>
    <section class="empty-download-state">
      <div class="empty-icon"><i class="bi bi-file-earmark-x"></i></div>
      <h2>No downloads matched your search.</h2>
      <p>Try another product name, SKU, manual name, or file name.</p>
      <a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/downloads/index.php">View All Downloads</a>
    </section>
  <?php else: ?>
    <section>
      <div class="section-heading-clean">
        <span class="eyebrow">Downloads</span>
        <h2>Resource files with product links</h2>
        <p>Open the related product page first, then login to access gated customer resources when needed.</p>
      </div>
      <div class="linked-download-grid">
        <?php foreach ($downloads as $download): ?>
          <?php
            $fileName = trim((string)($download['file_name'] ?? ''));
            $extension = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION) ?: 'FILE');
            $size = trim((string)($download['file_size'] ?? ''));
            $hasProduct = !empty($download['product_slug']);
            $productUrl = $hasProduct
                ? SITE_URL . '/products/product-details.php?slug=' . urlencode((string)$download['product_slug'])
                : SITE_URL . '/products/index.php?q=' . urlencode((string)$download['title']);
          ?>
          <article class="linked-download-card">
            <div class="download-icon-row">
              <div class="download-icon"><i class="bi bi-file-earmark-arrow-down"></i></div>
              <div class="download-ext"><?php echo esc($extension); ?></div>
            </div>

            <div>
              <h2><?php echo esc($download['title']); ?></h2>
              <p><?php echo esc($download['description']); ?></p>
            </div>

            <div class="product-link-box">
              <small><?php echo $hasProduct ? 'Linked Product' : 'Product Link'; ?></small>
              <strong><?php echo esc($hasProduct ? $download['product_name'] : 'Find matching product'); ?></strong>
              <?php if ($hasProduct): ?>
                <div class="small text-secondary"><?php echo esc(($download['category_name'] ?: 'Product') . ' · SKU: ' . ($download['product_sku'] ?: 'N/A')); ?></div>
              <?php else: ?>
                <div class="small text-secondary">No direct file match yet. Link by setting the product download file to: <?php echo esc($fileName); ?></div>
              <?php endif; ?>
            </div>

            <div class="download-meta-list">
              <span><i class="bi bi-paperclip"></i><?php echo esc($fileName ?: 'File name pending'); ?></span>
              <span><i class="bi bi-hdd"></i><?php echo esc($size ?: 'Size not listed'); ?></span>
              <span><i class="bi bi-calendar3"></i><?php echo esc(date('M d, Y', strtotime((string)$download['created_at']))); ?></span>
            </div>

            <div class="download-card-actions">
              <a class="btn btn-brand btn-sm" href="<?php echo esc($productUrl); ?>">
                <i class="bi bi-box-arrow-up-right"></i>
                <?php echo $hasProduct ? 'View Product' : 'Browse Product'; ?>
              </a>
              <a class="btn btn-soft-outline btn-sm" href="<?php echo esc($accountUrl); ?>">
                <?php echo isLoggedIn() ? 'Access File' : 'Login to Access'; ?>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($downloadableProducts): ?>
    <section class="download-product-section">
      <div class="section-heading-clean">
        <span class="eyebrow">Products with digital resources</span>
        <h2>Open the product connected to a download</h2>
        <p>These products are marked as downloadable or include a download file. Keep file names consistent to link cards automatically.</p>
      </div>
      <div class="download-product-grid">
        <?php foreach ($downloadableProducts as $product): ?>
          <a class="download-product-card" href="<?php echo esc(SITE_URL); ?>/products/product-details.php?slug=<?php echo urlencode((string)$product['slug']); ?>">
            <img src="<?php echo esc(productImageUrl($product)); ?>" alt="<?php echo esc(productName($product)); ?>">
            <span class="download-ext"><?php echo esc($product['category_name'] ?: 'Digital Resource'); ?></span>
            <strong><?php echo esc(productName($product)); ?></strong>
            <small>SKU: <?php echo esc($product['sku'] ?: 'N/A'); ?></small>
            <small><?php echo esc(!empty($product['download_file']) ? 'File: ' . $product['download_file'] : 'Downloadable product'); ?></small>
            <strong><?php echo money($product['price']); ?></strong>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="content-support-strip">
    <div>
      <span class="eyebrow">How to link a download to product?</span>
      <h2>Use the same file name in the download record and product download file.</h2>
      <p>Example: if the download file is <strong>manual.pdf</strong>, set the product download file as <strong>manual.pdf</strong>. The product button will appear automatically on this page.</p>
    </div>
    <a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/contact.php">Contact Support</a>
  </section>
</div>

<?php siteFooter(); ?>