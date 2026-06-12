<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();

$featuredLimit = max(1, min(24, (int)setting('homepage_featured_limit', '8')));
$newLimit = max(1, min(24, (int)setting('homepage_new_limit', '4')));
$serviceLimit = max(1, min(12, (int)setting('homepage_services_limit', '3')));

$featuredProducts = $pdo->query('SELECT p.*, c.name AS category_name FROM ' . table('products') . ' p LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id WHERE p.active=1 ORDER BY p.featured DESC, p.created_at DESC LIMIT ' . $featuredLimit)->fetchAll();
$newArrivals = $pdo->query('SELECT p.*, c.name AS category_name FROM ' . table('products') . ' p LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id WHERE p.active=1 ORDER BY p.created_at DESC LIMIT ' . $newLimit)->fetchAll();
$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM ' . table('categories') . ' c LEFT JOIN ' . table('products') . ' p ON p.category_id=c.id AND p.active=1 GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC LIMIT 8')->fetchAll();
$services = $pdo->query('SELECT * FROM ' . table('services') . ' WHERE active=1 ORDER BY id ASC LIMIT ' . $serviceLimit)->fetchAll();

$stats = [
    'products' => (int)$pdo->query('SELECT COUNT(*) FROM ' . table('products') . ' WHERE active=1')->fetchColumn(),
    'categories' => (int)$pdo->query('SELECT COUNT(*) FROM ' . table('categories'))->fetchColumn(),
    'orders' => (int)$pdo->query('SELECT COUNT(*) FROM ' . table('orders'))->fetchColumn(),
    'customers' => (int)$pdo->query('SELECT COUNT(*) FROM ' . table('customers'))->fetchColumn(),
];

$heroImage = uploadAssetUrl(setting('homepage_hero_image', ''), 'homepage');
siteHeader(setting('business_label', BUSINESS_LABEL), 'home');
?>
<?php if (setting('homepage_hero_enabled','1') === '1'): ?>
<section class="market-hero mb-4">
  <div class="hero-main">
    <div class="hero-copy">
      <span class="hero-kicker"><?= esc(setting('homepage_kicker', 'Commerce + ERP workflow')) ?></span>
      <h1><?= esc(setting('homepage_title', 'Build a storefront that sells and operates like a business system.')) ?></h1>
      <p><?= esc(setting('homepage_intro', 'Present products with a premium catalogue experience while keeping sales connected to stock, quotations, invoices, and ERP reporting.')) ?></p>
      <div class="hero-actions">
        <a class="btn btn-brand btn-lg" href="<?= esc(storeRelativeUrl(setting('homepage_hero_primary_url','/products/index.php'), '/products/index.php')) ?>"><?= esc(setting('homepage_hero_primary_label','Shop Products')) ?></a>
        <a class="btn btn-glass btn-lg" href="<?= esc(storeRelativeUrl(setting('homepage_hero_secondary_url','/contact.php'), '/contact.php')) ?>"><?= esc(setting('homepage_hero_secondary_label', setting('quote_cta', 'Request B2B Quote'))) ?></a>
      </div>
      <div class="hero-trust">
        <span><i class="bi bi-check2-circle"></i> <?= esc(setting('homepage_hero_trust_1','Product catalogue')) ?></span>
        <span><i class="bi bi-check2-circle"></i> <?= esc(setting('homepage_hero_trust_2','ERP-linked sales')) ?></span>
        <span><i class="bi bi-check2-circle"></i> <?= esc(setting('homepage_hero_trust_3','Procurement ready')) ?></span>
      </div>
    </div>
    <div class="hero-showcase">
      <?php if ($heroImage !== ''): ?>
        <div class="homepage-hero-media">
          <img src="<?= esc($heroImage) ?>" alt="<?= esc(setting('homepage_title', SHOP_NAME)) ?>">
        </div>
      <?php endif; ?>
      <div class="showcase-card large">
        <span><?= esc(setting('homepage_hero_showcase_label','Featured Workflow')) ?></span>
        <strong><?= esc(setting('homepage_hero_showcase_title','Storefront → Cart → ERP Invoice')) ?></strong>
        <p><?= esc(setting('homepage_hero_showcase_text','Commerce actions become operational records.')) ?></p>
      </div>
      <div class="showcase-grid">
        <div class="showcase-card">
          <i class="bi bi-box-seam"></i>
          <strong><?= $stats['products'] ?>+</strong>
          <span>Active products</span>
        </div>
        <div class="showcase-card">
          <i class="bi bi-grid"></i>
          <strong><?= $stats['categories'] ?>+</strong>
          <span>Departments</span>
        </div>
        <div class="showcase-card">
          <i class="bi bi-receipt"></i>
          <strong><?= $stats['orders'] ?>+</strong>
          <span>Orders recorded</span>
        </div>
        <div class="showcase-card">
          <i class="bi bi-buildings"></i>
          <strong><?= $stats['customers'] ?>+</strong>
          <span>ERP customers</span>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (setting('homepage_promo_ribbon_enabled','1') === '1'): ?>
<section class="promo-ribbon mb-4">
  <?php for ($promoIndex=1; $promoIndex<=4; $promoIndex++): ?>
    <div>
      <i class="bi <?= esc(setting('homepage_promo_'.$promoIndex.'_icon','bi-stars')) ?>"></i>
      <strong><?= esc(setting('homepage_promo_'.$promoIndex.'_title','Business Feature')) ?></strong>
      <span><?= esc(setting('homepage_promo_'.$promoIndex.'_text','Customize this feature from the homepage builder.')) ?></span>
    </div>
  <?php endfor; ?>
</section>
<?php endif; ?>

<?php if (setting('homepage_categories_enabled','1') === '1'): ?>
<section class="section-block">
  <div class="section-heading-premium">
    <div>
      <span class="eyebrow"><?= esc(setting('homepage_categories_eyebrow','Shop by department')) ?></span>
      <h2><?= esc(setting('homepage_categories_title','Browse high-intent product categories')) ?></h2>
      <p><?= esc(setting('homepage_categories_description', setting('category_section_intro', 'Browse categories with clearer merchandising and ERP relevance.'))) ?></p>
    </div>
    <a href="<?= esc(storeRelativeUrl(setting('homepage_categories_cta_url','/products/index.php'), '/products/index.php')) ?>" class="section-link"><?= esc(setting('homepage_categories_cta_label','View catalogue')) ?> <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="category-market-grid">
    <?php foreach ($categories as $cat): ?>
      <a class="category-market-card" href="<?= esc(SITE_URL) ?>/products/index.php?category=<?= (int)$cat['id'] ?>">
        <div class="category-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div>
          <strong><?= esc($cat['name']) ?></strong>
          <span><?= (int)$cat['product_count'] ?> products</span>
        </div>
        <i class="bi bi-arrow-up-right"></i>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (setting('homepage_featured_products_enabled','1') === '1'): ?>
<section class="section-block">
  <div class="section-heading-premium">
    <div>
      <span class="eyebrow"><?= esc(setting('homepage_featured_eyebrow','Featured catalogue')) ?></span>
      <h2><?= esc(setting('homepage_featured_title','Products designed to look more sellable')) ?></h2>
      <p><?= esc(setting('homepage_featured_description','Sharper product hierarchy: category, SKU, stock, price, detail link, and a premium card layout.')) ?></p>
    </div>
    <a href="<?= esc(storeRelativeUrl(setting('homepage_featured_cta_url','/products/index.php'), '/products/index.php')) ?>" class="section-link"><?= esc(setting('homepage_featured_cta_label','Shop all')) ?> <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="row g-4">
    <?php foreach ($featuredProducts as $product): $stock = stockBadge((int)$product['stock']); ?>
      <div class="col-md-6 col-xl-3">
        <article class="market-product-card">
          <a class="market-product-image" href="<?= esc(productStoreUrl($product)) ?>">
            <img src="<?= esc(productImageUrl($product)) ?>" alt="<?= esc(productName($product)) ?>">
            <?php if (!empty($product['badge'])): ?><span class="market-badge"><?= esc($product['badge']) ?></span><?php endif; ?>
          </a>
          <div class="market-product-body">
            <div class="market-meta"><span><?= esc($product['category_name'] ?: 'Product') ?></span><span><?= esc($product['sku']) ?></span></div>
            <h3><a href="<?= esc(productStoreUrl($product)) ?>"><?= esc(productName($product)) ?></a></h3>
            <p><?= esc(productShortDescription($product)) ?></p>
            <div class="market-price">
              <strong><?= money($product['price']) ?></strong>
              <?php if ((float)$product['compare_price'] > (float)$product['price']): ?><span><?= money($product['compare_price']) ?></span><?php endif; ?>
            </div>
            <div class="market-product-actions">
              <span class="badge bg-<?= esc($stock['class']) ?>"><?= esc($stock['text']) ?></span>
              <a class="btn btn-brand btn-sm" href="<?= esc(productStoreUrl($product)) ?>">View</a>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (setting('homepage_commercial_split_enabled','1') === '1'): ?>
<section class="split-commercial section-block">
  <div class="commercial-panel dark">
    <span class="eyebrow-light"><?= esc(setting('homepage_b2b_eyebrow','B2B path')) ?></span>
    <h2><?= esc(setting('homepage_b2b_title','Turn larger enquiries into quotes, invoices, and account records.')) ?></h2>
    <p><?= esc(setting('homepage_b2b_text','The frontend should not only sell. It should send qualified business buyers into ERP quotations and sales operations.')) ?></p>
    <div class="button-row">
      <a class="btn btn-light btn-lg" href="<?= esc(storeRelativeUrl(setting('homepage_b2b_primary_url','/contact.php'), '/contact.php')) ?>"><?= esc(setting('homepage_b2b_primary_label','Request Commercial Quote')) ?></a>
      <a class="btn btn-outline-light btn-lg" href="<?= esc(storeRelativeUrl(setting('homepage_b2b_secondary_url','/services/index.php'), '/services/index.php')) ?>"><?= esc(setting('homepage_b2b_secondary_label','View Services')) ?></a>
    </div>
  </div>
  <div class="commercial-panel light">
    <span class="eyebrow"><?= esc(setting('homepage_b2c_eyebrow','B2C path')) ?></span>
    <h2><?= esc(setting('homepage_b2c_title','Make quick retail buying easier.')) ?></h2>
    <ul>
      <?php for ($bulletIndex=1; $bulletIndex<=4; $bulletIndex++): ?>
        <?php $bullet = trim((string)setting('homepage_b2c_bullet_'.$bulletIndex,'')); ?>
        <?php if ($bullet !== ''): ?><li><i class="bi bi-check-circle-fill"></i> <?= esc($bullet) ?></li><?php endif; ?>
      <?php endfor; ?>
    </ul>
    <a class="btn btn-brand" href="<?= esc(storeRelativeUrl(setting('homepage_b2c_cta_url','/products/index.php'), '/products/index.php')) ?>"><?= esc(setting('homepage_b2c_cta_label','Browse Catalogue')) ?></a>
  </div>
</section>
<?php endif; ?>

<?php if (setting('homepage_new_arrivals_enabled','1') === '1'): ?>
<section class="section-block">
  <div class="section-heading-premium">
    <div>
      <span class="eyebrow"><?= esc(setting('homepage_new_eyebrow','New arrivals')) ?></span>
      <h2><?= esc(setting('homepage_new_title','Freshly added products')) ?></h2>
      <p><?= esc(setting('homepage_new_description','A marketplace-style discovery row that keeps the homepage dynamic.')) ?></p>
    </div>
  </div>
  <div class="row g-4">
    <?php foreach ($newArrivals as $product): $stock = stockBadge((int)$product['stock']); ?>
      <div class="col-md-6 col-xl-3">
        <article class="compact-product">
          <a href="<?= esc(productStoreUrl($product)) ?>"><img src="<?= esc(productImageUrl($product)) ?>" alt="<?= esc(productName($product)) ?>"></a>
          <div>
            <span><?= esc($product['category_name'] ?: 'Product') ?></span>
            <h3><a href="<?= esc(productStoreUrl($product)) ?>"><?= esc(productName($product)) ?></a></h3>
            <strong><?= money($product['price']) ?></strong>
            <small class="badge bg-<?= esc($stock['class']) ?>"><?= esc($stock['text']) ?></small>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (setting('homepage_services_enabled','1') === '1' && $services): ?>
<section class="section-block">
  <div class="section-heading-premium">
    <div>
      <span class="eyebrow"><?= esc(setting('homepage_services_eyebrow','Service commerce')) ?></span>
      <h2><?= esc(setting('homepage_services_title','Sell products and services from the same business storefront')) ?></h2>
      <p><?= esc(setting('homepage_services_description','Useful for onboarding, remote support, installations, setup, and B2B implementation services.')) ?></p>
    </div>
    <a href="<?= esc(storeRelativeUrl(setting('homepage_services_cta_url','/services/index.php'), '/services/index.php')) ?>" class="section-link"><?= esc(setting('homepage_services_cta_label','All services')) ?> <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="row g-4">
    <?php foreach ($services as $service): ?>
      <div class="col-lg-4">
        <article class="service-card-premium">
          <div class="service-icon"><i class="bi bi-tools"></i></div>
          <h3><?= esc($service['name']) ?></h3>
          <p><?= esc($service['description']) ?></p>
          <div><strong><?= money($service['price']) ?></strong></div>
          <a href="<?= esc(SITE_URL) ?>/booking.php" class="btn btn-soft mt-3">Book service</a>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (setting('homepage_trust_grid_enabled','1') === '1'): ?>
<section class="trust-grid section-block">
  <?php for ($trustIndex=1; $trustIndex<=4; $trustIndex++): ?>
    <article>
      <i class="bi <?= esc(setting('homepage_trust_'.$trustIndex.'_icon','bi-stars')) ?>"></i>
      <h3><?= esc(setting('homepage_trust_'.$trustIndex.'_title','Trust Card')) ?></h3>
      <p><?= esc(setting('homepage_trust_'.$trustIndex.'_text','Customize this card from the homepage builder.')) ?></p>
    </article>
  <?php endfor; ?>
</section>
<?php endif; ?>
<?php siteFooter(); ?>