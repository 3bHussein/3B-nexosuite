<?php
$pageTitle = 'Products & Commerce Links';
require_once dirname(__DIR__) . '/includes/functions.php';
permissionGuard('online_products');
$pdo = getDB();
$licenseState = licenseStatusSummary($pdo);

if (isset($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM ' . table('order_items') . ' WHERE product_id=?');
    $countStmt->execute([$productId]);
    if ((int)$countStmt->fetchColumn() > 0) {
        flash('error', 'This product has sales history. Keep it for reporting and mark it inactive instead.');
    } else {
        $pdo->prepare('DELETE FROM ' . table('inventory') . ' WHERE product_id=?')->execute([$productId]);
        $pdo->prepare('DELETE FROM ' . table('products') . ' WHERE id=?')->execute([$productId]);
        flash('success', 'Product deleted.');
    }
    redirect(ADMIN_URL . '/products.php');
}

$sql = 'SELECT p.*, c.name AS category_name, COALESCE(i.quantity,p.stock) AS erp_stock, (SELECT COALESCE(SUM(ws.stock_value),0) FROM ' . table('warehouse_stock') . ' ws WHERE ws.product_id=p.id) AS inventory_stock_value, COALESCE(SUM(oi.quantity),0) AS website_units_sold, COALESCE(SUM(oi.total),0) AS website_sales_value
FROM ' . table('products') . ' p
LEFT JOIN ' . table('categories') . ' c ON c.id=p.category_id
LEFT JOIN ' . table('inventory') . ' i ON i.product_id=p.id
LEFT JOIN ' . table('order_items') . ' oi ON oi.product_id=p.id
GROUP BY p.id,c.name,i.quantity
ORDER BY p.created_at DESC';
$products = $pdo->query($sql)->fetchAll();

include __DIR__ . '/header.php';
renderLicenseAdminNotice($pdo);
?>
<?php if (empty($licenseState['entities']['products']['can_create'])): ?>
<div class="alert alert-danger">
  <strong>Product license limit reached.</strong>
  Current products: <?php echo (int)($licenseState['entities']['products']['count'] ?? 0); ?> / <?php echo ($licenseState['entities']['products']['limit'] ?? null) === null ? '∞' : (int)$licenseState['entities']['products']['limit']; ?>.
  Creating a new product is blocked for this plan.
</div>
<?php endif; ?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
  <div>
    <h1 class="mb-1">Products</h1>
    <p class="text-secondary mb-0">One catalogue connects website presentation, ERP inventory, and website sales reporting.</p>
  </div>
  <?php $productCanCreate = !empty($licenseState['entities']['products']['can_create']); ?>
  <?php if ($productCanCreate): ?>
    <a class="btn btn-primary" href="add-product.php">Add product</a>
  <?php else: ?>
    <a class="btn btn-dark" href="<?php echo esc(SITE_URL); ?>/activation-loader.php">Product limit reached</a>
  <?php endif; ?>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Product</th><th>SKU</th><th>Price</th><th>Cost / Avg</th><th>Web Stock</th><th>ERP Stock</th><th>Inventory Value</th><th>Website Sold</th><th>Website Sales</th><th>Links</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($products as $product): ?>
        <tr>
          <td>
            <div class="fw-bold"><?= esc(productName($product)) ?></div>
            <div class="small text-secondary"><?= esc($product['category_name'] ?: 'Uncategorised') ?> · <?= $product['active'] ? 'Active' : 'Hidden' ?></div>
          </td>
          <td><?= esc($product['sku']) ?></td>
          <td><?= money($product['price']) ?></td>
          <td><?= money($product['cost_price'] ?? 0) ?><div class="small text-secondary">Avg <?= money($product['average_cost'] ?? 0) ?></div></td>
          <td><span class="badge bg-<?= (int)$product['stock'] > 0 ? 'success' : 'danger' ?>"><?= (int)$product['stock'] ?></span></td>
          <td><span class="badge bg-dark"><?= (int)$product['erp_stock'] ?></span></td>
          <td><?= money($product['inventory_stock_value'] ?? 0) ?></td>
          <td><?= (int)$product['website_units_sold'] ?></td>
          <td><?= money($product['website_sales_value']) ?></td>
          <td class="text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="<?= esc(productStoreUrl($product)) ?>" target="_blank">Website</a>
            <a class="btn btn-sm btn-outline-secondary" href="erp/inventory.php?product=<?= (int)$product['id'] ?>">ERP</a>
          </td>
          <td class="text-nowrap">
            <a class="btn btn-sm btn-warning" href="edit-product.php?id=<?= (int)$product['id'] ?>">Edit</a>
            <a class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')" href="products.php?delete=<?= (int)$product['id'] ?>">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>