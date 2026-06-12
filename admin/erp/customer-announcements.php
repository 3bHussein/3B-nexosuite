<?php
$pageTitle='Customer Announcements';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('customer_announcements');
$pdo=getDB();$user=currentUser();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $pdo->prepare('INSERT INTO '.table('customer_portal_announcements').' (title,message,audience,status,publish_from,publish_to,created_by) VALUES (?,?,?,"published",?,?,?)')->execute([trim((string)$_POST['title']),trim((string)$_POST['message']),trim((string)$_POST['audience']),trim((string)$_POST['publish_from'])?:null,trim((string)$_POST['publish_to'])?:null,(int)($user['id']??0)?:null]);
    flash('success','Announcement published.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'customer-announcements']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/customer-announcements.php');
}
$rows=$pdo->query('SELECT * FROM '.table('customer_portal_announcements').' ORDER BY created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Customer Notice Board</div><h2 class="h4 mb-1">Customer Announcements</h2><p class="text-secondary mb-0">Publish notices visible on customer portal dashboards.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><h2 class="h5 mb-3">Publish Announcement</h2><input class="form-control mb-2" name="title" placeholder="Title" required><textarea class="form-control mb-2" name="message" rows="5" placeholder="Message" required></textarea><select class="form-select mb-2" name="audience"><option value="all">All Customers</option><option value="b2b">B2B</option><option value="b2c">B2C</option></select><input class="form-control mb-2" type="datetime-local" name="publish_from"><input class="form-control mb-3" type="datetime-local" name="publish_to"><button class="btn btn-brand w-100">Publish</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><table class="table"><thead><tr><th>Title</th><th>Audience</th><th>Status</th><th>Window</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['title']); ?></strong><div class="small text-secondary"><?php echo esc($r['message']); ?></div></td><td><?php echo esc($r['audience']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td><td><?php echo esc($r['publish_from'].' → '.$r['publish_to']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>