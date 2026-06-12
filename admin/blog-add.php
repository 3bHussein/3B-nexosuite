<?php
$pageTitle='Add Blog Post';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=trim((string)($_POST['title']??''));
    $slug=slugify(trim((string)($_POST['slug']??$title)));
    if($title==='' || $slug===''){
        flash('error','Title and slug are required.');
        redirect(ADMIN_URL.'/blog-add.php');
    }
    $stmt=$pdo->prepare('INSERT INTO ' . table('blog_posts') . ' (title,slug,content,excerpt,status) VALUES (?,?,?,?,?)');
    $stmt->execute([
        $title,
        $slug,
        (string)($_POST['content']??''),
        (string)($_POST['excerpt']??''),
        trim((string)($_POST['status']??'published')) ?: 'published'
    ]);
    flash('success','Blog post created.');
    redirect(ADMIN_URL.'/blog.php');
}
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h3 mb-1">Add Blog Post</h1><p class="text-secondary mb-0">Use the visual editor to create readable, formatted articles.</p></div>
  <a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/blog.php">Back to Blog</a>
</div>
<form method="post" class="card-admin p-4 d-grid gap-3">
  <div class="row g-3">
    <div class="col-lg-8"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
    <div class="col-lg-4"><label class="form-label">Slug</label><input class="form-control" name="slug" placeholder="auto-from-title"></div>
    <div class="col-lg-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="published">Published</option><option value="draft">Draft</option></select></div>
    <div class="col-12"><label class="form-label">Excerpt / Summary</label><textarea class="form-control" name="excerpt" rows="4" data-rich-editor="blog-excerpt"></textarea></div>
    <div class="col-12"><label class="form-label">Article Content</label><textarea class="form-control" name="content" rows="16" data-rich-editor="blog-content"></textarea></div>
  </div>
  <div><button class="btn btn-brand btn-lg">Publish Blog Post</button></div>
</form>
<?php include __DIR__.'/footer.php'; ?>