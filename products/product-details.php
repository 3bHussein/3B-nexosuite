<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$pdo = getDB();
$slug = trim((string)($_GET['slug'] ?? ''));

$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM ' . table('products') . ' p LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id WHERE p.slug=? AND p.active=1 LIMIT 1');
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Product not found.');
    redirect(SITE_URL . '/products/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = max(1, min((int)($_POST['quantity'] ?? 1), max(1, (int)$product['stock'])));
    if ((int)$product['stock'] <= 0) {
        flash('error', 'This product is currently out of stock.');
    } else {
        $_SESSION['cart'][(int)$product['id']] = [
            'id' => (int)$product['id'],
            'name' => productName($product),
            'slug' => (string)$product['slug'],
            'sku' => (string)$product['sku'],
            'image' => (string)$product['image'],
            'price' => (float)$product['price'],
            'quantity' => $qty,
        ];
        flash('success', 'Product added to cart.');
        redirect(SITE_URL . '/cart.php');
    }
}

$stock = stockBadge((int)$product['stock']);
$gallery = array_values(array_unique(array_merge([productImageUrl($product)], productGallery($product))));
$specs = specificationRows(productSpecifications($product));

$relatedStmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM ' . table('products') . ' p LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id WHERE p.active=1 AND p.category_id=? AND p.id<>? ORDER BY p.featured DESC, p.created_at DESC LIMIT 4');
$relatedStmt->execute([(int)$product['category_id'], (int)$product['id']]);
$related = $relatedStmt->fetchAll();

$serviceRows = $pdo->query('SELECT * FROM ' . table('services') . ' WHERE active=1 ORDER BY id ASC LIMIT 2')->fetchAll();

siteHeader(productName($product), 'product', ['description' => productShortDescription($product), 'keywords' => (string)($product['sku'] ?? '')]);
?>
<nav class="catalog-breadcrumb" aria-label="breadcrumb">
  <a href="<?= esc(SITE_URL) ?>/index.php">Home</a>
  <i class="bi bi-chevron-right"></i>
  <a href="<?= esc(SITE_URL) ?>/products/index.php"><?php echo t('Products', 'المنتجات'); ?></a>
  <?php if (!empty($product['category_name'])): ?>
    <i class="bi bi-chevron-right"></i>
    <a href="<?= esc(SITE_URL) ?>/products/index.php?category=<?= (int)$product['category_id'] ?>"><?= esc($product['category_name']) ?></a>
  <?php endif; ?>
  <i class="bi bi-chevron-right"></i>
  <span><?= esc(productName($product)) ?></span>
</nav>

<section class="product-view-premium">
  <div class="product-gallery-premium">
    <div class="main-product-image">
      <img data-gallery-main src="<?= esc($gallery[0]) ?>" alt="<?= esc(productName($product)) ?>">
      <?php if (!empty($product['badge'])): ?><span class="detail-badge"><?= esc($product['badge']) ?></span><?php endif; ?>
    </div>
    <?php if (count($gallery) > 1): ?>
      <div class="thumbnail-strip">
        <?php foreach ($gallery as $index => $image): ?>
          <button class="thumbnail-button <?= $index === 0 ? 'active' : '' ?>" type="button" data-gallery-image="<?= esc($image) ?>">
            <img src="<?= esc($image) ?>" alt="<?= esc(productName($product)) ?>">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="gallery-trust-row">
      <span><i class="bi bi-images"></i> Product gallery ready</span>
      <span><i class="bi bi-box-seam"></i> SKU: <?= esc($product['sku']) ?></span>
    </div>
  </div>

  <div class="product-core-premium">
    <div class="detail-status-row">
      <span class="badge bg-<?= esc($stock['class']) ?>"><?= esc($stock['text']) ?></span>
      <span class="detail-category"><?= esc($product['category_name'] ?: 'Product') ?></span>
    </div>
    <h1><?= esc(productName($product)) ?></h1>
    <p class="detail-lead"><?= esc(productShortDescription($product)) ?></p>

    <div class="detail-price-line">
      <strong><?= money($product['price']) ?></strong>
      <?php if ((float)$product['compare_price'] > (float)$product['price']): ?><span><?= money($product['compare_price']) ?></span><?php endif; ?>
    </div>

    <div class="detail-info-grid">
      <div><small>SKU</small><strong><?= esc($product['sku']) ?></strong></div>
      <div><small>Warranty</small><strong><?= esc(productWarranty($product) ?: 'Contact sales') ?></strong></div>
      <div><small>Delivery</small><strong><?= !empty($product['downloadable']) ? 'Digital delivery' : 'Physical / quote-assisted' ?></strong></div>
      <div><small>Commercial</small><strong><?= !empty($product['downloadable']) ? 'Instant fulfilment' : 'B2B/B2C ready' ?></strong></div>
    </div>


  </div>

  <aside class="purchase-panel-premium">
    <span class="eyebrow">Purchase or quote</span>
    <h2>Ready to proceed?</h2>
    <p>Use cart checkout for direct orders or request a business quotation for project, bulk, or procurement-led needs.</p>

    <form method="post">
      <label class="form-label fw-bold">Quantity</label>
      <div class="premium-qty">
        <button type="button" data-qty-minus="#productQty">−</button>
        <input id="productQty" type="number" name="quantity" min="1" max="<?= max(1, (int)$product['stock']) ?>" value="1">
        <button type="button" data-qty-plus="#productQty">+</button>
      </div>
      <button class="btn btn-brand btn-lg w-100" type="submit" name="add_to_cart" value="1" <?= (int)$product['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
    </form>

    <a class="btn btn-soft btn-lg w-100 mt-2" href="<?= esc(SITE_URL) ?>/contact.php"><?= esc(setting('quote_cta', 'Request B2B Quote')) ?></a>

    <?php if (setting('product_page_trust_enabled','0') === '1'): ?>
    <div class="purchase-trust">
      <span><i class="bi <?= esc(setting('product_page_trust_1_icon','bi-building')) ?>"></i> <?= esc(setting('product_page_trust_1_text','Company pricing path')) ?></span>
      <span><i class="bi <?= esc(setting('product_page_trust_2_icon','bi-receipt')) ?>"></i> <?= esc(setting('product_page_trust_2_text','ERP invoice workflow')) ?></span>
      <span><i class="bi <?= esc(setting('product_page_trust_3_icon','bi-headset')) ?>"></i> <?= esc(setting('product_page_trust_3_text','Sales support CTA')) ?></span>
    </div>
    <?php endif; ?>
  </aside>
</section>

<?php if (setting('product_page_slogan_enabled','1') === '1'): ?>
<section class="detail-support-band">
  <article style="grid-column:1/-1"><i class="bi <?= esc(setting('product_page_slogan_icon','bi-bag-check')) ?>"></i><div><strong><?= esc(setting('product_page_slogan_title','E-commerce made simple for every customer.')) ?></strong><span><?= esc(setting('product_page_slogan_text','Browse products, add to cart, pay online, and access your digital resources from one clean customer account.')) ?></span></div></article>
</section>
<?php endif; ?>

<section class="product-tabs-shell">
  <ul class="nav nav-tabs premium-tabs" id="productTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-pane" type="button" role="tab">Overview</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="spec-tab" data-bs-toggle="tab" data-bs-target="#spec-pane" type="button" role="tab">Specifications</button>
    </li>
    <?php if (setting('product_page_commercial_notes_enabled','0') === '1'): ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="support-tab" data-bs-toggle="tab" data-bs-target="#support-pane" type="button" role="tab"><?= esc(setting('product_page_commercial_notes_title','Commercial Notes')) ?></button>
    </li>
    <?php endif; ?>
  </ul>
  <div class="tab-content premium-tab-content" id="productTabsContent">
    <div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
      <h2>Product overview</h2>
      <div class="rich-product-copy"><?= renderTrustedRichText((string)productDescription($product)) ?></div>
    </div>
    <div class="tab-pane fade" id="spec-pane" role="tabpanel">
      <h2>Structured specifications</h2>
      <?php if (!$specs): ?>
        <p class="muted mb-0">Specifications can be added from Admin &gt; Products to make this section more technical and conversion-ready.</p>
      <?php else: ?>
        <div class="spec-table-premium">
          <?php foreach ($specs as $row): ?>
            <div><strong><?= esc($row['label']) ?></strong><span><?= esc($row['value']) ?></span></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php if (setting('product_page_commercial_notes_enabled','0') === '1'): ?>
    <div class="tab-pane fade" id="support-pane" role="tabpanel">
      <h2><?= esc(setting('product_page_commercial_notes_title','Commercial Notes')) ?></h2>
      <div class="commercial-notes-grid">
        <article><strong><?= esc(setting('product_page_commercial_note_1_title','Direct online purchase')) ?></strong><p><?= esc(setting('product_page_commercial_note_1_text','Customers can buy ready products through a clean cart and checkout journey.')) ?></p></article>
        <article><strong><?= esc(setting('product_page_commercial_note_2_title','Customer account access')) ?></strong><p><?= esc(setting('product_page_commercial_note_2_text','Digital files, order history, invoices, and resources can be accessed from the customer account.')) ?></p></article>
        <article><strong><?= esc(setting('product_page_commercial_note_3_title','Support-led selling')) ?></strong><p><?= esc(setting('product_page_commercial_note_3_text','High-value products can still connect to support, contact, and quotation workflows when needed.')) ?></p></article>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($serviceRows): ?>
<section class="section-block">
  <div class="section-heading-premium">
    <div>
      <span class="eyebrow">Support add-ons</span>
      <h2>Complement the product with service-led revenue</h2>
      <p>Promote onboarding, setup, remote support, or implementation alongside products.</p>
    </div>
  </div>
  <div class="row g-4">
    <?php foreach ($serviceRows as $service): ?>
      <div class="col-lg-6">
        <article class="service-inline-card">
          <div class="service-icon"><i class="bi bi-tools"></i></div>
          <div>
            <h3><?= esc($service['name']) ?></h3>
            <p><?= esc($service['description']) ?></p>
            <strong><?= money($service['price']) ?></strong>
          </div>
          <a class="btn btn-soft" href="<?= esc(SITE_URL) ?>/booking.php">Book</a>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="section-block">
  <div class="section-heading-premium">
    <div>
      <span class="eyebrow">People also viewed</span>
      <h2>Related products from this category</h2>
      <p>A stronger cross-sell row similar to commercial ecommerce product pages.</p>
    </div>
    <a class="section-link" href="<?= esc(SITE_URL) ?>/products/index.php?category=<?= (int)$product['category_id'] ?>">View category <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="row g-4">
    <?php foreach ($related as $item): $itemStock = stockBadge((int)$item['stock']); ?>
      <div class="col-md-6 col-xl-3">
        <article class="market-product-card">
          <a class="market-product-image" href="<?= esc(productStoreUrl($item)) ?>">
            <img src="<?= esc(productImageUrl($item)) ?>" alt="<?= esc(productName($item)) ?>">
            <?php if (!empty($item['badge'])): ?><span class="market-badge"><?= esc($item['badge']) ?></span><?php endif; ?>
          </a>
          <div class="market-product-body">
            <div class="market-meta"><span><?= esc($item['category_name'] ?: 'Product') ?></span><span><?= esc($item['sku']) ?></span></div>
            <h3><a href="<?= esc(productStoreUrl($item)) ?>"><?= esc(productName($item)) ?></a></h3>
            <div class="market-price"><strong><?= money($item['price']) ?></strong></div>
            <div class="market-product-actions">
              <span class="badge bg-<?= esc($itemStock['class']) ?>"><?= esc($itemStock['text']) ?></span>
              <a class="btn btn-brand btn-sm" href="<?= esc(productStoreUrl($item)) ?>">View</a>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php siteFooter(); ?>