<?php
require_once __DIR__ . '/includes/functions.php';
$pdo=getDB();
ensureCustomPagesTable($pdo);

$slug=slugify((string)($_GET['slug']??''));
$stmt=$pdo->prepare('SELECT * FROM ' . table('custom_pages') . ' WHERE slug=? AND active=1 LIMIT 1');
$stmt->execute([$slug]);
$page=$stmt->fetch();

if(!$page){
    http_response_code(404);
    siteHeader('Page Not Found','custom_page_not_found');
    echo '<section class="py-5 text-center"><h1>Page not found</h1><p class="text-secondary">The requested page is unavailable or not published.</p><a class="btn btn-brand" href="'.esc(SITE_URL).'/index.php">Back to Home</a></section>';
    siteFooter();
    exit;
}

siteHeader(
    (string)$page['title'],
    'custom_page_' . $page['slug'],
    [
        'title' => trim((string)$page['meta_title']) ?: seoFallbackTitle((string)$page['title']),
        'description' => trim((string)$page['meta_description']),
        'keywords' => trim((string)$page['meta_keywords']),
        'canonical' => customPageUrl((string)$page['slug']),
    ]
);
?>
<style>
.custom-html-page{background:#fff;border:1px solid #e6eaf2;border-radius:28px;padding:clamp(22px,4vw,46px);box-shadow:0 18px 42px rgba(15,23,42,.06)}
.custom-html-page h1,.custom-html-page h2,.custom-html-page h3{letter-spacing:-.03em}
.custom-html-page img{max-width:100%;height:auto;border-radius:18px}
.custom-html-page table{width:100%;border-collapse:collapse;margin:18px 0}.custom-html-page th,.custom-html-page td{border:1px solid #e6eaf2;padding:10px;text-align:left}
</style>
<article class="custom-html-page">
  <?php echo renderTrustedRichText((string)$page['content_html']); ?>
</article>
<?php siteFooter(); ?>