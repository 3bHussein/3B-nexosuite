<?php
$pageTitle = 'Categories';
require_once dirname(__DIR__) . '/includes/functions.php';
permissionGuard('online_products');
$pdo = getDB();
$licenseState = licenseStatusSummary($pdo);
if (isset($_GET['delete'])) { $stmt=$pdo->prepare('DELETE FROM ' . table('categories') . ' WHERE id=?'); $stmt->execute([(int)$_GET['delete']]); flash('success','Category deleted.'); redirect(ADMIN_URL . '/categories.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!empty($_POST['id'])) { $stmt=$pdo->prepare('UPDATE ' . table('categories') . ' SET name=?,slug=?,description=?,sort_order=? WHERE id=?'); $stmt->execute([$_POST['name'],slugify($_POST['name']),$_POST['description'],(int)$_POST['sort_order'],(int)$_POST['id']]); flash('success','Category updated.'); }
  else { requireCategoryCreationAllowed($pdo); $stmt=$pdo->prepare('INSERT INTO ' . table('categories') . ' (name,slug,description,sort_order) VALUES (?,?,?,?)'); $stmt->execute([$_POST['name'],slugify($_POST['name']),$_POST['description'],(int)$_POST['sort_order']]); flash('success','Category created.'); }
  redirect(ADMIN_URL . '/categories.php');
}
$edit = null; if (isset($_GET['edit'])) { $stmt=$pdo->prepare('SELECT * FROM ' . table('categories') . ' WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch(); }
$categories = $pdo->query('SELECT * FROM ' . table('categories') . ' ORDER BY sort_order,name')->fetchAll();
include __DIR__ . '/header.php';
renderLicenseAdminNotice($pdo);
?>
<div class="row g-4"><div class="col-lg-4"><form method="post" class="card-admin p-4"><h2 class="h5"><?php echo $edit?'Edit':'Add'; ?> Category</h2><?php if($edit): ?><input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>"><?php endif; ?><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="<?php echo esc($edit['name'] ?? ''); ?>" required></div><div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?php echo esc($edit['description'] ?? ''); ?></textarea></div><div class="mb-3"><label class="form-label">Sort Order</label><input class="form-control" type="number" name="sort_order" value="<?php echo esc($edit['sort_order'] ?? 0); ?>"></div><?php if($edit || !licenseTrialLimitReached('categories',$pdo)): ?><button class="btn btn-brand"><?php echo $edit?'Update':'Create'; ?></button><?php else: ?><a class="btn btn-dark" href="<?php echo esc(SITE_URL); ?>/activation-loader.php">Activate to add more categories</a><?php endif; ?></form></div><div class="col-lg-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Name</th><th>Slug</th><th>Order</th><th></th></tr></thead><tbody><?php foreach($categories as $category): ?><tr><td><?php echo esc($category['name']); ?></td><td><?php echo esc($category['slug']); ?></td><td><?php echo (int)$category['sort_order']; ?></td><td class="text-end"><a class="btn btn-outline-primary btn-sm" href="?edit=<?php echo (int)$category['id']; ?>">Edit</a> <a class="btn btn-outline-danger btn-sm" data-confirm="Delete category?" href="?delete=<?php echo (int)$category['id']; ?>">Delete</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include __DIR__ . '/footer.php'; ?>