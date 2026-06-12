<?php
$pageTitle='HTML Pages';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
ensureCustomPagesTable($pdo);

if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    $stmt=$pdo->prepare('DELETE FROM ' . table('custom_pages') . ' WHERE id=?');
    $stmt->execute([$id]);
    flash('success','Custom page deleted.');
    redirect(ADMIN_URL.'/pages.php');
}

$pages=$pdo->query('SELECT * FROM ' . table('custom_pages') . ' ORDER BY sort_order ASC, created_at DESC')->fetchAll();
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
  <div>
    <h1 class="h3 mb-1">HTML Pages</h1>
    <p class="text-secondary mb-0">Create standalone website pages using HTML and choose whether they appear in the public header menu.</p>
  </div>
  <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/page-add.php">Create New Page</a>
</div>

<div class="alert alert-info border-0 shadow-sm">
  <strong>Header control:</strong> Enable <em>Show in Header</em> inside a page to automatically show it in the website header and mobile menu.
</div>

<div class="table-wrap table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Page</th><th>Slug</th><th>Header</th><th>Status</th><th>Sort</th><th></th></tr></thead>
    <tbody>
      <?php foreach($pages as $page): ?>
        <tr>
          <td>
            <strong><?php echo esc($page['title']); ?></strong>
            <div class="small text-secondary">Label: <?php echo esc($page['header_label'] ?: $page['title']); ?></div>
          </td>
          <td><code><?php echo esc($page['slug']); ?></code></td>
          <td><?php echo !empty($page['show_in_header']) ? '<span class="badge bg-success">Shown</span>' : '<span class="badge bg-light text-dark border">Hidden</span>'; ?></td>
          <td><?php echo !empty($page['active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Draft</span>'; ?></td>
          <td><?php echo (int)$page['sort_order']; ?></td>
          <td class="text-end">
            <a class="btn btn-outline-dark btn-sm" target="_blank" href="<?php echo esc(customPageUrl($page['slug'])); ?>">View</a>
            <a class="btn btn-outline-primary btn-sm" href="<?php echo esc(ADMIN_URL); ?>/page-edit.php?id=<?php echo (int)$page['id']; ?>">Edit</a>
            <a data-confirm="Delete this custom page?" class="btn btn-outline-danger btn-sm" href="?delete=<?php echo (int)$page['id']; ?>">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$pages): ?><tr><td colspan="6" class="text-secondary">No custom HTML pages yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__.'/footer.php'; ?>