<?php
$pageTitle='Edit HTML Page';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
ensureCustomPagesTable($pdo);

$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT * FROM ' . table('custom_pages') . ' WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$page=$stmt->fetch();
if(!$page){
    flash('error','Custom page not found.');
    redirect(ADMIN_URL.'/pages.php');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $title=trim((string)($_POST['title']??''));
        $slug=slugify(trim((string)($_POST['slug']??$title)));
        if($title==='' || $slug===''){
            throw new RuntimeException('Title and slug are required.');
        }
        $stmt=$pdo->prepare('UPDATE ' . table('custom_pages') . ' SET title=?,slug=?,content_html=?,meta_title=?,meta_description=?,meta_keywords=?,header_label=?,show_in_header=?,sort_order=?,active=? WHERE id=?');
        $stmt->execute([
            $title,
            $slug,
            (string)($_POST['content_html']??''),
            trim((string)($_POST['meta_title']??'')),
            trim((string)($_POST['meta_description']??'')),
            trim((string)($_POST['meta_keywords']??'')),
            trim((string)($_POST['header_label']??'')),
            !empty($_POST['show_in_header']) ? 1 : 0,
            (int)($_POST['sort_order']??0),
            !empty($_POST['active']) ? 1 : 0,
            $id,
        ]);
        flash('success','HTML page updated.');
        redirect(ADMIN_URL.'/pages.php');
    }catch(Throwable $e){
        flash('error',$e->getMessage());
    }
}
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h3 mb-1">Edit HTML Page</h1><p class="text-secondary mb-0">Update the standalone HTML page and header visibility.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-dark" target="_blank" href="<?php echo esc(customPageUrl($page['slug'])); ?>">View Page</a><a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/pages.php">Back</a></div>
</div>

<form method="post" class="card-admin p-4 d-grid gap-3">
  <div class="row g-3">
    <div class="col-lg-8"><label class="form-label">Page Title</label><input class="form-control" name="title" value="<?php echo esc($page['title']); ?>" required></div>
    <div class="col-lg-4"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?php echo esc($page['slug']); ?>"></div>
    <div class="col-lg-4"><label class="form-label">Header Label</label><input class="form-control" name="header_label" value="<?php echo esc($page['header_label']); ?>"></div>
    <div class="col-lg-2"><label class="form-label">Sort Order</label><input class="form-control" type="number" name="sort_order" value="<?php echo (int)$page['sort_order']; ?>"></div>
    <div class="col-lg-3"><label class="form-label">Show in Header</label><select class="form-select" name="show_in_header"><option value="1" <?php echo !empty($page['show_in_header'])?'selected':''; ?>>Show</option><option value="0" <?php echo empty($page['show_in_header'])?'selected':''; ?>>Hide</option></select></div>
    <div class="col-lg-3"><label class="form-label">Status</label><select class="form-select" name="active"><option value="1" <?php echo !empty($page['active'])?'selected':''; ?>>Active</option><option value="0" <?php echo empty($page['active'])?'selected':''; ?>>Draft</option></select></div>
    <div class="col-lg-6"><label class="form-label">SEO Title</label><input class="form-control" name="meta_title" value="<?php echo esc($page['meta_title']); ?>"></div>
    <div class="col-lg-6"><label class="form-label">SEO Keywords</label><input class="form-control" name="meta_keywords" value="<?php echo esc($page['meta_keywords']); ?>"></div>
    <div class="col-12"><label class="form-label">SEO Description</label><textarea class="form-control" name="meta_description" rows="2"><?php echo esc($page['meta_description']); ?></textarea></div>
    <div class="col-12">
      <label class="form-label">HTML Content</label>
      <textarea class="form-control font-monospace" name="content_html" rows="22" data-rich-editor="custom-page-content"><?php echo esc($page['content_html']); ?></textarea>
      <div class="form-text">This content is rendered as HTML on the frontend.</div>
    </div>
  </div>
  <div><button class="btn btn-brand btn-lg">Save Page</button></div>
</form>
<?php include __DIR__.'/footer.php'; ?>