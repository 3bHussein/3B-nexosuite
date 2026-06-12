<?php
require_once dirname(__DIR__) . '/includes/functions.php';

$pdo = getDB();
$q = trim((string)($_GET['q'] ?? ''));
$params = [];
$sql = 'SELECT * FROM ' . table('blog_posts') . ' WHERE status = "published"';

if ($q !== '') {
    $sql .= ' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
    $term = '%' . $q . '%';
    $params = [$term, $term, $term];
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$totalPosts = (int)$pdo->query('SELECT COUNT(*) FROM ' . table('blog_posts') . ' WHERE status = "published"')->fetchColumn();
$featuredPost = $posts[0] ?? null;
$gridPosts = $featuredPost ? array_slice($posts, 1) : [];

siteHeader('Blog', 'blog');
?>
<nav class="page-breadcrumb" aria-label="breadcrumb">
  <a href="<?php echo esc(SITE_URL); ?>/index.php">Home</a>
  <i class="bi bi-chevron-right"></i>
  <span>Blog</span>
</nav>

<section class="blog-index-hero">
  <div>
    <span class="eyebrow-light">Insights & Guides</span>
    <h1>Readable articles for buyers, teams, and business operators.</h1>
    <p>Publish product education, implementation guides, B2B explainers, and ERP-related resources in a clean blog layout built for mobile, tablet, and desktop reading.</p>
  </div>
  <div class="blog-index-summary">
    <div><strong><?php echo $totalPosts; ?></strong><span>Published articles</span></div>
    <div><strong><?php echo count($posts); ?></strong><span>Current matches</span></div>
  </div>
</section>

<section class="content-toolbar-panel">
  <form method="get" class="content-search-form">
    <label class="visually-hidden" for="blog-search">Search blog posts</label>
    <div class="content-search-input">
      <i class="bi bi-search"></i>
      <input id="blog-search" type="search" name="q" value="<?php echo esc($q); ?>" placeholder="Search articles, guides, topics...">
    </div>
    <button class="btn btn-brand">Search Articles</button>
    <?php if ($q !== ''): ?>
      <a class="btn btn-soft-outline" href="<?php echo esc(SITE_URL); ?>/blog/index.php">Clear</a>
    <?php endif; ?>
  </form>
</section>

<?php if (!$posts): ?>
  <section class="content-empty-state">
    <div class="empty-icon"><i class="bi bi-journal-x"></i></div>
    <h2>No blog posts matched your search.</h2>
    <p>Try a broader phrase or clear the search to view all published content.</p>
    <a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/blog/index.php">View All Articles</a>
  </section>
<?php else: ?>
  <?php if ($featuredPost): ?>
    <?php
      $featuredWordCount = str_word_count(strip_tags((string)$featuredPost['content']));
      $featuredMinutes = max(1, (int)ceil($featuredWordCount / 210));
    ?>
    <section class="blog-featured-card">
      <div class="blog-featured-copy">
        <span class="blog-kicker">Featured Article</span>
        <h2><?php echo esc($featuredPost['title']); ?></h2>
        <p><?php echo esc(strip_tags((string)$featuredPost['excerpt'])); ?></p>
        <div class="blog-card-meta">
          <span><i class="bi bi-calendar3"></i><?php echo esc(date('M d, Y', strtotime((string)$featuredPost['created_at']))); ?></span>
          <span><i class="bi bi-clock"></i><?php echo $featuredMinutes; ?> min read</span>
        </div>
        <a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/blog/post.php?slug=<?php echo esc($featuredPost['slug']); ?>">Read Featured Article</a>
      </div>
      <div class="blog-featured-visual">
        <div class="article-orb"></div>
        <strong><?php echo esc(setting('business_label', BUSINESS_LABEL)); ?></strong>
        <span>Knowledge center</span>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($gridPosts): ?>
    <section class="blog-card-grid">
      <?php foreach ($gridPosts as $post): ?>
        <?php
          $wordCount = str_word_count(strip_tags((string)$post['content']));
          $minutes = max(1, (int)ceil($wordCount / 210));
        ?>
        <article class="blog-list-card">
          <div class="blog-list-card-top">
            <span class="blog-kicker">Article</span>
            <span class="mini-reading-time"><?php echo $minutes; ?> min</span>
          </div>
          <h2><?php echo esc($post['title']); ?></h2>
          <p><?php echo esc(strip_tags((string)$post['excerpt'])); ?></p>
          <div class="blog-card-meta">
            <span><i class="bi bi-calendar3"></i><?php echo esc(date('M d, Y', strtotime((string)$post['created_at']))); ?></span>
            <span><i class="bi bi-book"></i>Readable guide</span>
          </div>
          <a class="blog-read-link" href="<?php echo esc(SITE_URL); ?>/blog/post.php?slug=<?php echo esc($post['slug']); ?>">
            Read More <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
<?php endif; ?>

<section class="content-support-strip blog-strip">
  <div>
    <span class="eyebrow">Content-led commerce</span>
    <h2>Use the blog to educate, rank, and convert.</h2>
    <p>Long-form product education and business guidance help the frontend feel richer while supporting search visibility and buyer confidence.</p>
  </div>
  <a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/products/index.php">Browse Products</a>
</section>

<?php siteFooter(); ?>