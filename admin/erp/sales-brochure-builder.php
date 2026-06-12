<?php
$pageTitle='Sales Brochure Builder';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('sales_brochure_builder');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{p35CreateSalesBrochureSection($pdo,trim((string)$_POST['section_title']),trim((string)$_POST['section_type']),trim((string)$_POST['headline']),trim((string)$_POST['body_text']),trim((string)$_POST['cta_text']),(int)$_POST['sort_order']);flash('success','Brochure section created.');}
  catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'sales-brochure-builder']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/sales-brochure-builder.php');
}
$sections=$pdo->query('SELECT * FROM '.table('sales_brochure_sections').' ORDER BY sort_order,section_title')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Commercial Asset</div><h2 class="h4 mb-1">Sales Brochure Builder</h2><p class="text-secondary mb-0">Build print-ready brochure sections for demos, proposals and customer presentations.</p></div><button class="btn btn-outline-primary" onclick="window.print()">Print / Save PDF</button></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Add Section</h2><input class="form-control mb-2" name="section_title" placeholder="Hero"><input class="form-control mb-2" name="section_type" value="brochure"><input class="form-control mb-2" name="headline" placeholder="Headline"><textarea class="form-control mb-2" name="body_text" rows="5"></textarea><input class="form-control mb-2" name="cta_text" placeholder="Book a demo"><input class="form-control mb-3" type="number" name="sort_order" value="10"><button class="btn btn-brand w-100">Add Section</button></form></div><div class="col-xl-8"><div class="card-admin p-4"><?php foreach($sections as $s): ?><section class="mb-4 pb-3 border-bottom"><div class="erp-kicker"><?php echo esc($s['section_title']); ?></div><h2 class="h4"><?php echo esc($s['headline']); ?></h2><p class="text-secondary" style="line-height:1.7"><?php echo nl2br(esc($s['body_text'])); ?></p><strong><?php echo esc($s['cta_text']); ?></strong></section><?php endforeach; ?></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>