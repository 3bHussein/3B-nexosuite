<?php
require_once dirname(__DIR__) . '/includes/functions.php';

$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM ' . table('blog_posts') . ' WHERE slug = ? AND status = "published" LIMIT 1');
$stmt->execute([trim((string)($_GET['slug'] ?? ''))]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    siteHeader('Post Not Found', 'blog');
    echo '<section class="content-empty-state"><div class="empty-icon"><i class="bi bi-journal-x"></i></div><h1>Post not found.</h1><p>The article may have been moved or is no longer published.</p><a class="btn btn-brand" href="' . esc(SITE_URL) . '/blog/index.php">Back to Blog</a></section>';
    siteFooter();
    exit;
}

$rawContent = (string)$post['content'];
$wordCount = str_word_count(strip_tags($rawContent));
$readingMinutes = max(1, (int)ceil($wordCount / 210));
$publishedDate = date('M d, Y', strtotime((string)$post['created_at']));

$toc = [];
$seenHeadingIds = [];
$content = preg_replace_callback('/<h([2-3])([^>]*)>(.*?)<\/h\1>/is', function ($matches) use (&$toc, &$seenHeadingIds) {
    $level = (int)$matches[1];
    $attrs = (string)$matches[2];
    $label = trim(strip_tags((string)$matches[3]));

    if ($label === '') {
        return $matches[0];
    }

    $baseId = slugify($label);
    if ($baseId === '') {
        $baseId = 'section';
    }
    $id = $baseId;
    $counter = 2;
    while (isset($seenHeadingIds[$id])) {
        $id = $baseId . '-' . $counter;
        $counter++;
    }
    $seenHeadingIds[$id] = true;

    $attrs = preg_replace('/\s+id=(["\']).*?\1/i', '', $attrs);
    $toc[] = ['level' => $level, 'id' => $id, 'label' => $label];

    return '<h' . $level . $attrs . ' id="' . esc($id) . '">' . $matches[3] . '</h' . $level . '>';
}, $rawContent);

if (!is_string($content)) {
    $content = $rawContent;
}

$relatedStmt = $pdo->prepare('SELECT title,slug,excerpt,created_at,content FROM ' . table('blog_posts') . ' WHERE status = "published" AND id <> ? ORDER BY created_at DESC LIMIT 3');
$relatedStmt->execute([(int)$post['id']]);
$relatedPosts = $relatedStmt->fetchAll();

siteHeader($post['title'], 'blog_post', ['description' => strip_tags((string)($post['excerpt'] ?? ''))]);
?>
<div class="reading-progress-shell" aria-hidden="true">
  <span data-reading-progress></span>
</div>

<nav class="page-breadcrumb article-breadcrumb" aria-label="breadcrumb">
  <a href="<?php echo esc(SITE_URL); ?>/index.php">Home</a>
  <i class="bi bi-chevron-right"></i>
  <a href="<?php echo esc(SITE_URL); ?>/blog/index.php">Blog</a>
  <i class="bi bi-chevron-right"></i>
  <span><?php echo esc($post['title']); ?></span>
</nav>

<header class="blog-article-hero">
  <div>
    <span class="eyebrow-light">Article</span>
    <h1><?php echo esc($post['title']); ?></h1>
    <p><?php echo esc(strip_tags((string)$post['excerpt'])); ?></p>
    <div class="article-meta-row">
      <span><i class="bi bi-calendar3"></i><?php echo esc($publishedDate); ?></span>
      <span><i class="bi bi-clock-history"></i><?php echo $readingMinutes; ?> min read</span>
      <span><i class="bi bi-building"></i><?php echo esc(setting('business_label', BUSINESS_LABEL)); ?></span>
    </div>
  </div>
  <aside class="article-hero-panel">
    <strong>Reading Guide</strong>
    <p>Clean typography, section navigation, and responsive spacing for comfortable long-form reading.</p>
    <a class="btn btn-glass" href="<?php echo esc(SITE_URL); ?>/blog/index.php">Back to Articles</a>
  </aside>
</header>

<section class="blog-article-layout">
  <article class="reading-card" data-reading-article>
    <div class="reading-content">
      <?php echo $content; ?>
    </div>
  </article>

  <aside class="article-side">
    <div class="article-side-card toc-card">
      <span class="blog-kicker">On this page</span>
      <?php if ($toc): ?>
        <nav class="toc-list" aria-label="Table of contents">
          <?php foreach ($toc as $tocItem): ?>
            <a class="toc-level-<?php echo (int)$tocItem['level']; ?>" href="#<?php echo esc($tocItem['id']); ?>">
              <?php echo esc($tocItem['label']); ?>
            </a>
          <?php endforeach; ?>
        </nav>
      <?php else: ?>
        <p>This article does not contain secondary headings yet. Add H2 or H3 headings in the admin blog editor to show a table of contents.</p>
      <?php endif; ?>
    </div>

    <div class="article-side-card">
      <span class="blog-kicker">Next step</span>
      <h2>Move from learning to action.</h2>
      <p>Browse the catalogue, request a quote, or explore resources connected to your selected business edition.</p>
      <div class="side-action-stack">
        <a class="btn btn-brand btn-sm" href="<?php echo esc(SITE_URL); ?>/products/index.php">Browse Products</a>
        <a class="btn btn-soft-outline btn-sm" href="<?php echo esc(SITE_URL); ?>/contact.php">Request Support</a>
      </div>
    </div>
  </aside>
</section>

<?php if ($relatedPosts): ?>
  <section class="related-reading-section">
    <div class="section-heading-premium">
      <div>
        <span class="eyebrow">Continue reading</span>
        <h2>Related guides and updates</h2>
        <p>Keep readers engaged with additional articles after the main post.</p>
      </div>
      <a class="section-link" href="<?php echo esc(SITE_URL); ?>/blog/index.php">View all articles <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="blog-card-grid related-grid">
      <?php foreach ($relatedPosts as $related): ?>
        <?php
          $relatedWords = str_word_count(strip_tags((string)$related['content']));
          $relatedMinutes = max(1, (int)ceil($relatedWords / 210));
        ?>
        <article class="blog-list-card compact-reading-card">
          <div class="blog-list-card-top">
            <span class="blog-kicker">Related</span>
            <span class="mini-reading-time"><?php echo $relatedMinutes; ?> min</span>
          </div>
          <h3><?php echo esc($related['title']); ?></h3>
          <p><?php echo esc($related['excerpt']); ?></p>
          <a class="blog-read-link" href="<?php echo esc(SITE_URL); ?>/blog/post.php?slug=<?php echo esc($related['slug']); ?>">
            Read Article <i class="bi bi-arrow-right"></i>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<?php siteFooter(); ?>