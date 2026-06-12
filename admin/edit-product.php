<?php
$pageTitle = 'Edit Product';
require_once dirname(__DIR__) . '/includes/functions.php';
permissionGuard('online_products');
$pdo = getDB();
$productId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM ' . table('products') . ' WHERE id=? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    flash('error', 'Product not found.');
    redirect(ADMIN_URL . '/products.php');
}
$categories = $pdo->query('SELECT * FROM ' . table('categories') . ' ORDER BY sort_order ASC, name ASC')->fetchAll();
$inventoryStmt = $pdo->prepare('SELECT * FROM ' . table('inventory') . ' WHERE product_id=? LIMIT 1');
$inventoryStmt->execute([$productId]);
$inventory = $inventoryStmt->fetch() ?: ['location' => 'Main Store', 'quantity' => $product['stock']];
$currentGallery = productGallery($product);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = slugify(trim((string)($_POST['slug'] ?? $name)));
    $sku = trim((string)($_POST['sku'] ?? ''));
    $newStock = max(0, (int)($_POST['stock'] ?? 0));
    $price = max(0, (float)($_POST['price'] ?? 0));
    $costPrice = ($_POST['cost_price'] ?? '') === '' ? max(0,(float)($product['cost_price'] ?? 0)) : max(0,(float)$_POST['cost_price']);
    if ($name === '' || $slug === '' || $sku === '') {
        flash('error', 'Name, slug, and SKU are required.');
        redirect(ADMIN_URL . '/edit-product.php?id=' . $productId);
    }

    try {
        $mainImage = trim((string)($_POST['image'] ?? ''));
        $uploadedMain = uploadAdminImage('image_upload', 'products');
        if ($uploadedMain) {
            $mainImage = $uploadedMain;
        }
        $galleryUploads = uploadAdminImages('gallery_uploads', 'products');
        $gallery = mergeGalleryValues((string)($_POST['gallery'] ?? ''), $galleryUploads);

        $pdo->beginTransaction();
        $update = $pdo->prepare('UPDATE ' . table('products') . ' SET category_id=?,name=?,name_ar=?,slug=?,description=?,description_ar=?,short_description=?,short_description_ar=?,price=?,compare_price=?,cost_price=?,stock=?,sku=?,image=?,gallery=?,badge=?,warranty=?,warranty_ar=?,specifications=?,specifications_ar=?,featured=?,active=?,downloadable=?,download_file=? WHERE id=?');
        $update->execute([
            (int)($_POST['category_id'] ?? 0) ?: null,
            $name,
            (string)($_POST['name_ar'] ?? ''),
            $slug,
            (string)($_POST['description'] ?? ''),
            (string)($_POST['description_ar'] ?? ''),
            (string)($_POST['short_description'] ?? ''),
            (string)($_POST['short_description_ar'] ?? ''),
            $price,
            ($_POST['compare_price'] ?? '') === '' ? null : (float)$_POST['compare_price'],
            $costPrice,
            $newStock,
            $sku,
            $mainImage,
            $gallery,
            (string)($_POST['badge'] ?? ''),
            (string)($_POST['warranty'] ?? ''),
            (string)($_POST['warranty_ar'] ?? ''),
            (string)($_POST['specifications'] ?? ''),
            (string)($_POST['specifications_ar'] ?? ''),
            !empty($_POST['featured']) ? 1 : 0,
            !empty($_POST['active']) ? 1 : 0,
            !empty($_POST['downloadable']) ? 1 : 0,
            (string)($_POST['download_file'] ?? ''),
            $productId,
        ]);
        syncInventory($pdo, $productId, $sku, $newStock, trim((string)($_POST['location'] ?? 'Main Store')) ?: 'Main Store');
        $pdo->commit();
        flash('success', 'Product updated. Photos, content, website listing, and ERP inventory are synchronized.');
        redirect(ADMIN_URL . '/products.php');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Unable to update product: ' . $e->getMessage());
        redirect(ADMIN_URL . '/edit-product.php?id=' . $productId);
    }
}

include __DIR__ . '/header.php';
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
  <div><h1>Edit Product</h1><p class="text-secondary mb-0">Changes affect website listing, product page, uploaded media, and ERP inventory stock.</p></div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="<?= esc(productStoreUrl($product)) ?>" target="_blank">Open website page</a>
    <a class="btn btn-outline-secondary" href="erp/inventory.php?product=<?= $productId ?>">ERP inventory</a>
  </div>
</div>
<form method="post" enctype="multipart/form-data" class="card p-4">
  <div class="row g-3">
    <div class="col-lg-6"><label class="form-label">Product name - English</label><input class="form-control" name="name" value="<?= esc($product['name']) ?>" required></div>
    <div class="col-lg-6"><label class="form-label">Product name - Arabic</label><input class="form-control" name="name_ar" value="<?= esc($product['name_ar'] ?? '') ?>" dir="rtl"></div>
    <div class="col-lg-6"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= esc($product['slug']) ?>" required></div>
    <div class="col-lg-4"><label class="form-label">SKU</label><input class="form-control" name="sku" value="<?= esc($product['sku']) ?>" required></div>
    <div class="col-lg-4"><label class="form-label">Category</label><select class="form-select" name="category_id"><option value="">None</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$product['category_id'] === (int)$category['id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-4"><label class="form-label">Badge</label><input class="form-control" name="badge" value="<?= esc($product['badge'] ?? '') ?>"></div>
    <div class="col-lg-3"><label class="form-label">Price</label><input class="form-control" type="number" step="0.01" name="price" value="<?= esc($product['price']) ?>" required></div>
    <div class="col-lg-3"><label class="form-label">Compare price</label><input class="form-control" type="number" step="0.01" name="compare_price" value="<?= esc($product['compare_price']) ?>"></div>
    <div class="col-lg-3"><label class="form-label">Cost price</label><input class="form-control" type="number" step="0.01" name="cost_price" value="<?= esc($product['cost_price'] ?? 0) ?>"><div class="small text-secondary">Moving avg: <?= money($product['average_cost'] ?? 0) ?></div></div>
    <div class="col-lg-3"><label class="form-label">Synced stock</label><input class="form-control" type="number" name="stock" value="<?= (int)$product['stock'] ?>"></div>

    <div class="col-12"><div class="admin-section-divider"><strong>Product media</strong><span>Upload a new photo to replace the current main image, or append gallery images.</span></div></div>
    <div class="col-lg-4">
      <label class="form-label">Current main photo</label>
      <div class="admin-image-preview"><img src="<?= esc(productImageUrl($product)) ?>" alt="<?= esc(productName($product)) ?>"></div>
    </div>
    <div class="col-lg-8">
      <div class="row g-3">
        <div class="col-lg-6"><label class="form-label">Upload replacement main photo</label><input class="form-control" type="file" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif"></div>
        <div class="col-lg-6"><label class="form-label">Main image filename / URL</label><input class="form-control" name="image" value="<?= esc($product['image']) ?>"></div>
        <div class="col-lg-6"><label class="form-label">Upload additional gallery photos</label><input class="form-control" type="file" name="gallery_uploads[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple></div>
        <div class="col-lg-6"><label class="form-label">Inventory location</label><input class="form-control" name="location" value="<?= esc($inventory['location'] ?? 'Main Store') ?>"></div>
      </div>
    </div>
    <div class="col-12">
      <label class="form-label">Gallery image filenames / URLs</label>
      <textarea class="form-control" name="gallery" rows="3"><?= esc($product['gallery'] ?? '') ?></textarea>
      <?php if ($currentGallery): ?>
        <div class="admin-gallery-preview mt-3"><?php foreach ($currentGallery as $image): ?><img src="<?= esc($image) ?>" alt="Gallery image"><?php endforeach; ?></div>
      <?php endif; ?>
    </div>

    <div class="col-lg-6"><label class="form-label">Warranty / commercial note - English</label><input class="form-control" name="warranty" value="<?= esc($product['warranty'] ?? '') ?>"></div>
    <div class="col-lg-6"><label class="form-label">Warranty / commercial note - Arabic</label><input class="form-control" name="warranty_ar" value="<?= esc($product['warranty_ar'] ?? '') ?>" dir="rtl"></div>
    <div class="col-lg-6"><label class="form-label">Download file</label><input class="form-control" name="download_file" value="<?= esc($product['download_file']) ?>"></div>
    <div class="col-12"><div class="admin-section-divider"><strong>English content</strong><span>Main product content used for English pages.</span></div></div>
    <div class="col-12"><label class="form-label">Short description - English</label><textarea class="form-control" name="short_description" rows="2"><?= esc($product['short_description']) ?></textarea></div>
    <div class="col-12"><label class="form-label">Full description - English</label><textarea class="form-control" name="description" rows="7" data-rich-editor="product-description"><?= esc($product['description']) ?></textarea></div>
    <div class="col-12"><label class="form-label">Specifications - English</label><textarea class="form-control" name="specifications" rows="5"><?= esc($product['specifications'] ?? '') ?></textarea></div>
    <div class="col-12"><div class="admin-section-divider"><strong>Arabic content</strong><span>Used automatically on /ar product pages. Leave blank to fallback to English.</span></div></div>
    <div class="col-12"><label class="form-label">Short description - Arabic</label><textarea class="form-control" name="short_description_ar" rows="2" dir="rtl"><?= esc($product['short_description_ar'] ?? '') ?></textarea></div>
    <div class="col-12"><label class="form-label">Full description - Arabic</label><textarea class="form-control" name="description_ar" rows="7" dir="rtl"><?= esc($product['description_ar'] ?? '') ?></textarea></div>
    <div class="col-12"><label class="form-label">Specifications - Arabic</label><textarea class="form-control" name="specifications_ar" rows="5" dir="rtl"><?= esc($product['specifications_ar'] ?? '') ?></textarea></div>
    <div class="col-12 d-flex flex-wrap gap-3">
      <label class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" <?= $product['active'] ? 'checked' : '' ?>> <span class="form-check-label">Visible on website</span></label>
      <label class="form-check"><input class="form-check-input" type="checkbox" name="featured" value="1" <?= $product['featured'] ? 'checked' : '' ?>> <span class="form-check-label">Featured product</span></label>
      <label class="form-check"><input class="form-check-input" type="checkbox" name="downloadable" value="1" <?= $product['downloadable'] ? 'checked' : '' ?>> <span class="form-check-label">Downloadable product</span></label>
    </div>
  </div>
  <div class="mt-4"><button class="btn btn-primary" type="submit">Save synchronized product</button></div>
</form>
<?php include __DIR__ . '/footer.php'; ?>