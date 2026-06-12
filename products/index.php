<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$pdo = getDB();

$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$q = trim((string)($_GET['q'] ?? ''));
$stockOnly = !empty($_GET['stock']);
$sort = (string)($_GET['sort'] ?? 'featured');

$where = ['p.active=1'];
$params = [];
if ($category > 0) {
    $where[] = 'p.category_id=?';
    $params[] = $category;
}
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ? OR p.description LIKE ?)';
    $term = '%' . $q . '%';
    array_push($params, $term, $term, $term, $term);
}
if ($stockOnly) {
    $where[] = 'p.stock > 0';
}

$order = match ($sort) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'newest' => 'p.created_at DESC',
    'name' => 'p.name ASC',
    default => 'p.featured DESC, p.created_at DESC',
};

$sql = 'SELECT p.*, c.name AS category_name FROM ' . table('products') . ' p LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM ' . table('categories') . ' c LEFT JOIN ' . table('products') . ' p ON p.category_id=c.id AND p.active=1 GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC')->fetchAll();
$selectedCategory = null;
foreach ($categories as $cat) {
    if ((int)$cat['id'] === $category) {
        $selectedCategory = $cat;
        break;
    }
}

siteHeader(setting('business_label', 'Product Catalogue') . ' Catalogue', 'products');
?>
<nav class="catalog-breadcrumb" aria-label="breadcrumb">
  <a href="<?= esc(SITE_URL) ?>/index.php">Home</a>
  <i class="bi bi-chevron-right"></i>
  <span>Products</span>
  <?php if ($selectedCategory): ?>
    <i class="bi bi-chevron-right"></i>
    <span><?= esc($selectedCategory['name']) ?></span>
  <?php endif; ?>
</nav>

<section class="catalog-hero-premium mb-4">
  <div>
    <span class="eyebrow-light">Product catalogue</span>
    <h1><?= esc(setting('catalog_title', 'Browse products with a marketplace-style shopping experience.')) ?></h1>
    <p><?= esc(setting('catalog_intro', 'Search, filter, compare stock, and request quotes when required.')) ?></p>
  </div>
  <div class="catalog-stat-panel">
    <strong><?= count($products) ?></strong>
    <span>matching items</span>
    <small><?= $selectedCategory ? esc($selectedCategory['name']) : 'All active categories' ?></small>
  </div>
</section>

<div class="catalog-layout-premium">
  <aside>
    <form class="catalog-filter-premium" method="get">
      <div class="filter-title">
        <h2>Filter Products</h2>
        <a href="<?= esc(SITE_URL) ?>/products/index.php">Reset</a>
      </div>
      <label>Search</label>
      <div class="filter-search">
        <i class="bi bi-search"></i>
        <input type="search" name="q" value="<?= esc($q) ?>" placeholder="Name, SKU, keyword">
      </div>

      <label>Category</label>
      <select name="category">
        <option value="0">All categories</option>
        <?php foreach ($categories as $item): ?>
          <option value="<?= (int)$item['id'] ?>" <?= $category === (int)$item['id'] ? 'selected' : '' ?>>
            <?= esc(productName($item)) ?> (<?= (int)$item['product_count'] ?>)
          </option>
        <?php endforeach; ?>
      </select>

      <label>Sort by</label>
      <select name="sort">
        <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured first</option>
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
      </select>

      <label class="filter-check">
        <input type="checkbox" name="stock" value="1" <?= $stockOnly ? 'checked' : '' ?>>
        <span>Only show in-stock items</span>
      </label>

      <button class="btn btn-brand w-100" type="submit">Apply Filters</button>
    </form>

    <div class="catalog-side-card">
      <span class="eyebrow">B2B buyers</span>
      <h3>Need a quotation instead of checkout?</h3>
      <p>Send company requirements for bulk orders, equipment projects, service bundles, or procurement requests.</p>
      <a class="btn btn-soft w-100" href="<?= esc(SITE_URL) ?>/contact.php">Request quote</a>
    </div>
  </aside>

  <section>
    <div class="catalog-chip-row">
      <a class="<?= $category === 0 ? 'active' : '' ?>" href="<?= esc(SITE_URL) ?>/products/index.php">All</a>
      <?php foreach (array_slice($categories, 0, 7) as $cat): ?>
        <a class="<?= $category === (int)$cat['id'] ? 'active' : '' ?>" href="<?= esc(SITE_URL) ?>/products/index.php?category=<?= (int)$cat['id'] ?>"><?= esc($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="catalog-toolbar">
      <div>
        <strong><?= count($products) ?> products</strong>
        <span><?= $q !== '' ? 'matching “' . esc($q) . '”' : 'available for discovery' ?></span>
      </div>
      <div class="toolbar-note"><i class="bi bi-shield-check"></i> ERP-connected inventory visibility</div>
    </div>

    <?php if (!$products): ?>
      <div class="empty-state-premium">
        <i class="bi bi-search"></i>
        <h2>No products matched your filters</h2>
        <p>Try a broader search phrase, remove stock-only filtering, or browse all categories.</p>
        <a class="btn btn-brand" href="<?= esc(SITE_URL) ?>/products/index.php">Clear Filters</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($products as $product): $stock = stockBadge((int)$product['stock']); ?>
          <div class="col-md-6 col-xl-4">
            <article class="market-product-card tall">
              <a class="market-product-image" href="<?= esc(productStoreUrl($product)) ?>">
                <img src="<?= esc(productImageUrl($product)) ?>" alt="<?= esc(productName($product)) ?>">
                <?php if (!empty($product['badge'])): ?><span class="market-badge"><?= esc($product['badge']) ?></span><?php endif; ?>
              </a>
              <div class="market-product-body">
                <div class="market-meta"><span><?= esc($product['category_name'] ?: 'Product') ?></span><span><?= esc($product['sku']) ?></span></div>
                <h2><a href="<?= esc(productStoreUrl($product)) ?>"><?= esc(productName($product)) ?></a></h2>
                <p><?= esc(productShortDescription($product)) ?></p>
                <div class="market-price">
                  <strong><?= money($product['price']) ?></strong>
                  <?php if ((float)$product['compare_price'] > (float)$product['price']): ?><span><?= money($product['compare_price']) ?></span><?php endif; ?>
                </div>
                <div class="market-product-actions">
                  <span class="badge bg-<?= esc($stock['class']) ?>"><?= esc($stock['text']) ?></span>
                  <a class="btn btn-brand btn-sm" href="<?= esc(productStoreUrl($product)) ?>">Details</a>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php siteFooter(); ?>