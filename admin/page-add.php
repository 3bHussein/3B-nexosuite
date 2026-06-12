<?php
$pageTitle='Create HTML Page';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
ensureCustomPagesTable($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $title=trim((string)($_POST['title']??''));
        $slug=slugify(trim((string)($_POST['slug']??$title)));
        if($title==='' || $slug===''){
            throw new RuntimeException('Title and slug are required.');
        }
        $stmt=$pdo->prepare('INSERT INTO ' . table('custom_pages') . ' (title,slug,content_html,meta_title,meta_description,meta_keywords,header_label,show_in_header,sort_order,active) VALUES (?,?,?,?,?,?,?,?,?,?)');
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
        ]);
        flash('success','HTML page created.');
        redirect(ADMIN_URL.'/pages.php');
    }catch(Throwable $e){
        flash('error',$e->getMessage());
    }
}
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h3 mb-1">Create HTML Page</h1><p class="text-secondary mb-0">Paste HTML, add SEO fields, and show it in the public header if needed.</p></div>
  <a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/pages.php">Back to HTML Pages</a>
</div>

<form method="post" class="card-admin p-4 d-grid gap-3">
  <div class="row g-3">
    <div class="col-lg-8"><label class="form-label">Page Title</label><input class="form-control" name="title" required></div>
    <div class="col-lg-4"><label class="form-label">Slug</label><input class="form-control" name="slug" placeholder="auto-from-title"></div>
    <div class="col-lg-4"><label class="form-label">Header Label</label><input class="form-control" name="header_label" placeholder="Optional menu label"></div>
    <div class="col-lg-2"><label class="form-label">Sort Order</label><input class="form-control" type="number" name="sort_order" value="0"></div>
    <div class="col-lg-3"><label class="form-label">Show in Header</label><select class="form-select" name="show_in_header"><option value="1">Show</option><option value="0" selected>Hide</option></select></div>
    <div class="col-lg-3"><label class="form-label">Status</label><select class="form-select" name="active"><option value="1" selected>Active</option><option value="0">Draft</option></select></div>
    <div class="col-lg-6"><label class="form-label">SEO Title</label><input class="form-control" name="meta_title"></div>
    <div class="col-lg-6"><label class="form-label">SEO Keywords</label><input class="form-control" name="meta_keywords"></div>
    <div class="col-12"><label class="form-label">SEO Description</label><textarea class="form-control" name="meta_description" rows="2"></textarea></div>
    <div class="col-12">
      <label class="form-label">HTML Content</label>
      <textarea class="form-control font-monospace" name="content_html" rows="20" data-rich-editor="custom-page-content" placeholder="<section><h1>Your page</h1><p>Write or paste HTML here...</p></section>"></textarea>
      <div class="form-text">Admin HTML is trusted and rendered on the frontend. Avoid scripts unless you fully trust the code.</div>
    </div>
  </div>
  <div><button class="btn btn-brand btn-lg">Create Page</button></div>
</form>
<?php include __DIR__.'/footer.php'; ?>