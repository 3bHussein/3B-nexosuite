<?php
$pageTitle = 'Add Product';
require_once dirname(__DIR__) . '/includes/functions.php';
permissionGuard('online_products');
$pdo = getDB();
requireProductCreationAllowed($pdo);
$categories = $pdo->query('SELECT * FROM ' . table('categories') . ' ORDER BY sort_order ASC, name ASC')->fetchAll();
$productLimitReached = function_exists('licenseTrialLimitReached') ? licenseTrialLimitReached('products', $pdo) : false;
$productLimit = function_exists('licensePlanLimit') ? licensePlanLimit('products') : null;
$productCount = function_exists('currentLicenseEntityCount') ? currentLicenseEntityCount('products', $pdo) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireProductCreationAllowed($pdo);
    $name = trim((string)($_POST['name'] ?? ''));
    $slug = slugify(trim((string)($_POST['slug'] ?? $name)));
    $sku = trim((string)($_POST['sku'] ?? ''));
    $stock = max(0, (int)($_POST['stock'] ?? 0));
    $price = max(0, (float)($_POST['price'] ?? 0));
    $costPrice = ($_POST['cost_price'] ?? '') === '' ? round($price * max(0,min(1,(float)setting('inventory_default_cost_ratio','0.60'))),2) : max(0,(float)$_POST['cost_price']);
    if ($name === '' || $slug === '' || $sku === '') {
        flash('error', 'Name, slug, and SKU are required.');
        redirect(ADMIN_URL . '/add-product.php');
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
        $stmt = $pdo->prepare('INSERT INTO ' . table('products') . ' (category_id,name,name_ar,slug,description,description_ar,short_description,short_description_ar,price,compare_price,cost_price,average_cost,stock,sku,image,gallery,badge,warranty,warranty_ar,specifications,specifications_ar,featured,active,downloadable,download_file) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
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
            $costPrice,
            $stock,
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
        ]);
        $productId = (int)$pdo->lastInsertId();
        syncInventory($pdo, $productId, $sku, $stock, trim((string)($_POST['location'] ?? 'Main Store')) ?: 'Main Store');
        $pdo->commit();
        flash('success', 'Product created with uploaded media and linked ERP inventory.');
        redirect(ADMIN_URL . '/products.php');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Unable to create product: ' . $e->getMessage());
        redirect(ADMIN_URL . '/add-product.php');
    }
}

include __DIR__ . '/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><h1>Add Product</h1><p class="text-secondary mb-0">Publish a product, upload photos, and initialize its ERP inventory record.</p></div><a class="btn btn-outline-secondary" href="products.php">Back</a></div>
<form method="post" enctype="multipart/form-data" class="card p-4">
  <div class="row g-3">
    <div class="col-lg-6"><label class="form-label">Product name - English</label><input class="form-control" name="name" required></div>
    <div class="col-lg-6"><label class="form-label">Product name - Arabic</label><input class="form-control" name="name_ar" dir="rtl" placeholder="اسم المنتج بالعربية"></div>
    <div class="col-lg-6"><label class="form-label">Slug</label><input class="form-control" name="slug" placeholder="auto-from-name"></div>
    <div class="col-lg-4"><label class="form-label">SKU</label><input class="form-control" name="sku" required></div>
    <div class="col-lg-4"><label class="form-label">Category</label><select class="form-select" name="category_id"><option value="">None</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>"><?= esc($category['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-lg-4"><label class="form-label">Badge</label><input class="form-control" name="badge" placeholder="Featured / New / Best Seller"></div>
    <div class="col-lg-3"><label class="form-label">Price</label><input class="form-control" type="number" step="0.01" name="price" required></div>
    <div class="col-lg-3"><label class="form-label">Compare price</label><input class="form-control" type="number" step="0.01" name="compare_price"></div>
    <div class="col-lg-3"><label class="form-label">Cost price</label><input class="form-control" type="number" step="0.01" name="cost_price" placeholder="Defaults by valuation ratio"></div>
    <div class="col-lg-3"><label class="form-label">Initial stock</label><input class="form-control" type="number" name="stock" value="0"></div>

    <div class="col-12"><div class="admin-section-divider"><strong>Product media</strong><span>Upload JPG, PNG, WEBP, or GIF. Maximum 8 MB per image.</span></div></div>
    <div class="col-lg-6"><label class="form-label">Upload main product photo</label><input class="form-control" type="file" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif"></div>
    <div class="col-lg-6"><label class="form-label">Or main image filename / URL</label><input class="form-control" name="image" placeholder="optional image URL or existing filename"></div>
    <div class="col-lg-6"><label class="form-label">Upload gallery photos</label><input class="form-control" type="file" name="gallery_uploads[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple></div>
    <div class="col-lg-6"><label class="form-label">Inventory location</label><input class="form-control" name="location" value="Main Store"></div>
    <div class="col-12"><label class="form-label">Gallery image filenames / URLs</label><textarea class="form-control" name="gallery" rows="2" placeholder="One filename or URL per line. Uploaded gallery images are appended automatically."></textarea></div>

    <div class="col-lg-6"><label class="form-label">Warranty / commercial note - English</label><input class="form-control" name="warranty" placeholder="12-Month Warranty"></div>
    <div class="col-lg-6"><label class="form-label">Warranty / commercial note - Arabic</label><input class="form-control" name="warranty_ar" dir="rtl" placeholder="ضمان 12 شهر"></div>
    <div class="col-lg-6"><label class="form-label">Download file</label><input class="form-control" name="download_file"></div>
    <div class="col-12"><div class="admin-section-divider"><strong>English content</strong><span>Main product content used for English pages.</span></div></div>
    <div class="col-12"><label class="form-label">Short description - English</label><textarea class="form-control" name="short_description" rows="2"></textarea></div>
    <div class="col-12"><label class="form-label">Full description - English</label><textarea class="form-control" name="description" rows="7" data-rich-editor="product-description"></textarea></div>
    <div class="col-12"><label class="form-label">Specifications - English</label><textarea class="form-control" name="specifications" rows="5" placeholder="Mode: Bench programming&#10;Delivery: Physical product&#10;Support: Included"></textarea></div>
    <div class="col-12"><div class="admin-section-divider"><strong>Arabic content</strong><span>Used automatically on /ar product pages. Leave blank to fallback to English.</span></div></div>
    <div class="col-12"><label class="form-label">Short description - Arabic</label><textarea class="form-control" name="short_description_ar" rows="2" dir="rtl"></textarea></div>
    <div class="col-12"><label class="form-label">Full description - Arabic</label><textarea class="form-control" name="description_ar" rows="7" dir="rtl"></textarea></div>
    <div class="col-12"><label class="form-label">Specifications - Arabic</label><textarea class="form-control" name="specifications_ar" rows="5" dir="rtl" placeholder="الوضع: برمجة&#10;التسليم: منتج فعلي&#10;الدعم: متوفر"></textarea></div>
    <div class="col-12 d-flex flex-wrap gap-3">
      <label class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" checked> <span class="form-check-label">Visible on website</span></label>
      <label class="form-check"><input class="form-check-input" type="checkbox" name="featured" value="1"> <span class="form-check-label">Featured product</span></label>
      <label class="form-check"><input class="form-check-input" type="checkbox" name="downloadable" value="1"> <span class="form-check-label">Downloadable product</span></label>
    </div>
  </div>
  <div class="mt-4"><button class="btn btn-primary" type="submit">Create linked product</button></div>
</form>
<?php include __DIR__ . '/footer.php'; ?>