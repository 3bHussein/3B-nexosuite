<?php
$pageTitle='Documentation Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('documentation_center');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'install');
    if($action==='install'){p35InstallDefaults($pdo);flash('success','Documentation, training and commercial defaults installed.');}
    else{
      $number=nextScopedDocumentNumber($pdo,'documentation_article',setting('documentation_article_prefix','DOCART'),operationalScope($pdo));
      $slug=preg_replace('/[^a-z0-9]+/','-',strtolower(trim((string)$_POST['title'])));$slug=trim($slug,'-').'-'.substr(md5((string)microtime(true)),0,4);
      $pdo->prepare('INSERT INTO '.table('documentation_articles').' (article_number,title,doc_type,audience,module_key,slug,content,status,sort_order,created_by,updated_at) VALUES (?,?,?,?,?,?,?,"published",?,?,NOW())')->execute([$number,trim((string)$_POST['title']),trim((string)$_POST['doc_type']),trim((string)$_POST['audience']),trim((string)$_POST['module_key']),$slug,trim((string)$_POST['content']),(int)$_POST['sort_order'],(int)(currentUser()['id']??0)?:null]);
      flash('success','Documentation article created.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'documentation-center']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/documentation-center.php');
}
$counts=p35ReadinessCounts($pdo);
$articles=$pdo->query('SELECT * FROM '.table('documentation_articles').' ORDER BY sort_order,title LIMIT 200')->fetchAll();
$selected=null;if(!empty($_GET['id'])){$s=$pdo->prepare('SELECT * FROM '.table('documentation_articles').' WHERE id=? LIMIT 1');$s->execute([(int)$_GET['id']]);$selected=$s->fetch();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Priority 35</div><h2 class="h4 mb-1">Documentation Center</h2><p class="text-secondary mb-0">README, installation guide, admin manual, user manual, developer handover and commercial documentation.</p></div><form method="post"><input type="hidden" name="action" value="install"><button class="btn btn-brand">Install Defaults</button></form></div>
<div class="row g-4 mb-4"><?php foreach($counts as $k=>$v): ?><div class="col-md-2"><div class="card-admin p-3"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><h2 class="h5 mb-3">Create Article</h2><input class="form-control mb-2" name="title" placeholder="Guide title" required><select class="form-select mb-2" name="doc_type"><option>readme</option><option>installation</option><option>admin_manual</option><option>user_manual</option><option>developer_handover</option><option>commercial</option></select><input class="form-control mb-2" name="audience" value="admin"><input class="form-control mb-2" name="module_key" value="general"><input class="form-control mb-2" type="number" name="sort_order" value="10"><textarea class="form-control mb-3" name="content" rows="8"># Guide Title&#10;&#10;Write the guide content here. Use Print to save as PDF.</textarea><button class="btn btn-outline-primary w-100">Create Article</button></form><div class="table-wrap table-responsive"><h2 class="h6 mb-3">Articles</h2><table class="table table-sm"><tbody><?php foreach($articles as $a): ?><tr><td><a href="<?php echo esc(ADMIN_URL); ?>/erp/documentation-center.php?id=<?php echo (int)$a['id']; ?>"><strong><?php echo esc($a['title']); ?></strong></a><div class="small text-secondary"><?php echo esc($a['doc_type'].' · '.$a['audience']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="card-admin p-4"><div class="d-flex justify-content-between gap-3"><h2 class="h5 mb-3"><?php echo esc($selected['title'] ?? 'Documentation Preview'); ?></h2><button class="btn btn-sm btn-outline-primary" onclick="window.print()">Print / Save PDF</button></div><?php if($selected): ?><div class="text-secondary small mb-3"><?php echo esc($selected['article_number'].' · '.$selected['doc_type'].' · '.$selected['audience']); ?></div><div style="white-space:pre-wrap;line-height:1.7"><?php echo esc($selected['content']); ?></div><?php else: ?><p class="text-secondary mb-0">Select an article from the left or install defaults.</p><?php endif; ?></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>