<?php
$pageTitle='Edit Blog Post';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
$stmt=$pdo->prepare('SELECT * FROM ' . table('blog_posts') . ' WHERE id=?');
$stmt->execute([(int)($_GET['id']??0)]);
$post=$stmt->fetch();
if(!$post){flash('error','Post not found.');redirect(ADMIN_URL.'/blog.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=trim((string)($_POST['title']??''));
    $slug=slugify(trim((string)($_POST['slug']??$title)));
    if($title==='' || $slug===''){
        flash('error','Title and slug are required.');
        redirect(ADMIN_URL.'/blog-edit.php?id='.(int)$post['id']);
    }
    $stmt=$pdo->prepare('UPDATE ' . table('blog_posts') . ' SET title=?,slug=?,content=?,excerpt=?,status=? WHERE id=?');
    $stmt->execute([
        $title,
        $slug,
        (string)($_POST['content']??''),
        (string)($_POST['excerpt']??''),
        trim((string)($_POST['status']??'published')) ?: 'published',
        (int)$post['id']
    ]);
    flash('success','Blog post updated.');
    redirect(ADMIN_URL.'/blog.php');
}
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h3 mb-1">Edit Blog Post</h1><p class="text-secondary mb-0">Adjust the article visually, including headings, lists, links, and formatted copy.</p></div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-primary" target="_blank" href="<?php echo esc(SITE_URL); ?>/blog/post.php?slug=<?php echo esc($post['slug']); ?>">Preview Article</a>
    <a class="btn btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/blog.php">Back to Blog</a>
  </div>
</div>
<form method="post" class="card-admin p-4 d-grid gap-3">
  <div class="row g-3">
    <div class="col-lg-8"><label class="form-label">Title</label><input class="form-control" name="title" value="<?php echo esc($post['title']); ?>" required></div>
    <div class="col-lg-4"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?php echo esc($post['slug']); ?>" required></div>
    <div class="col-lg-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="published" <?php echo $post['status']==='published'?'selected':''; ?>>Published</option><option value="draft" <?php echo $post['status']==='draft'?'selected':''; ?>>Draft</option></select></div>
    <div class="col-12"><label class="form-label">Excerpt / Summary</label><textarea class="form-control" name="excerpt" rows="4" data-rich-editor="blog-excerpt"><?php echo esc($post['excerpt']); ?></textarea></div>
    <div class="col-12"><label class="form-label">Article Content</label><textarea class="form-control" name="content" rows="16" data-rich-editor="blog-content"><?php echo esc($post['content']); ?></textarea></div>
  </div>
  <div><button class="btn btn-brand btn-lg">Save Blog Post</button></div>
</form>
<?php include __DIR__.'/footer.php'; ?>