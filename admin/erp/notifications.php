<?php
$pageTitle='Notifications';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('notifications');
$pdo=getDB();
$user=currentUser();

if(isset($_GET['read'])){
  $id=(int)$_GET['read'];
  $pdo->prepare('UPDATE '.table('notifications').' SET is_read=1,read_at=NOW() WHERE id=?')->execute([$id]);
  redirect(ADMIN_URL.'/erp/notifications.php');
}
if(isset($_GET['mark_all'])){
  if(($user['role']??'')==='admin'){$pdo->query('UPDATE '.table('notifications').' SET is_read=1,read_at=NOW() WHERE is_read=0');}
  else{$stmt=$pdo->prepare('UPDATE '.table('notifications').' SET is_read=1,read_at=NOW() WHERE is_read=0 AND (user_id=? OR user_id IS NULL)');$stmt->execute([(int)$user['id']]);}
  redirect(ADMIN_URL.'/erp/notifications.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  createNotification($pdo,[
    'user_id'=>(int)($_POST['user_id']??0)?:null,
    'role_slug'=>trim((string)($_POST['role_slug']??'')),
    'title'=>trim((string)($_POST['title']??'Manual notification')),
    'message'=>trim((string)($_POST['message']??'')),
    'severity'=>trim((string)($_POST['severity']??'info')),
    'link_url'=>trim((string)($_POST['link_url']??'')),
  ]);
  flash('success','Notification created.');
  redirect(ADMIN_URL.'/erp/notifications.php');
}
if(($user['role']??'')==='admin'){
  $rows=$pdo->query('SELECT n.*,u.email user_email FROM '.table('notifications').' n LEFT JOIN '.table('users').' u ON u.id=n.user_id ORDER BY n.created_at DESC,n.id DESC LIMIT 200')->fetchAll();
}else{
  $roleSlug=currentErpRoleSlug($pdo,$user);
  $stmt=$pdo->prepare('SELECT n.*,u.email user_email FROM '.table('notifications').' n LEFT JOIN '.table('users').' u ON u.id=n.user_id WHERE n.user_id=? OR (n.user_id IS NULL AND (n.role_slug=? OR n.role_slug IS NULL)) ORDER BY n.created_at DESC,n.id DESC LIMIT 200');
  $stmt->execute([(int)$user['id'],$roleSlug]);$rows=$stmt->fetchAll();
}
$users=$pdo->query('SELECT id,email FROM '.table('users').' WHERE status="active" ORDER BY email ASC LIMIT 300')->fetchAll();
$roles=$pdo->query('SELECT slug,name FROM '.table('erp_roles').' WHERE active=1 ORDER BY name ASC')->fetchAll();
$unread=0;foreach($rows as $r){if(empty($r['is_read'])){$unread++;}}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Operational Alert Center</div><h2 class="h4 mb-1">Notifications</h2><p class="text-secondary mb-0">Role and user-level alerts for approvals, stock risk, budget warnings, and governance events.</p></div><a class="btn btn-outline-primary" href="?mark_all=1">Mark All Read</a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><div class="erp-kicker">Create Alert</div><h2 class="h5 mb-3">Manual Notification</h2><label class="form-label">Specific User</label><select class="form-select mb-3" name="user_id"><option value="0">No specific user</option><?php foreach($users as $u): ?><option value="<?php echo (int)$u['id']; ?>"><?php echo esc($u['email']); ?></option><?php endforeach; ?></select><label class="form-label">Role Target</label><select class="form-select mb-3" name="role_slug"><option value="">All / no role</option><?php foreach($roles as $role): ?><option value="<?php echo esc($role['slug']); ?>"><?php echo esc($role['name'].' · '.$role['slug']); ?></option><?php endforeach; ?></select><label class="form-label">Title</label><input class="form-control mb-3" name="title" required><label class="form-label">Message</label><textarea class="form-control mb-3" name="message" rows="4"></textarea><label class="form-label">Severity</label><select class="form-select mb-3" name="severity"><option>info</option><option>success</option><option>warning</option><option>danger</option></select><label class="form-label">Link URL</label><input class="form-control mb-3" name="link_url" placeholder="/admin/erp/approvals.php"><button class="btn btn-brand w-100">Create Notification</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Inbox</div><h2 class="h5 mb-0">Unread <?php echo (int)$unread; ?></h2></div></div><table class="table align-middle"><thead><tr><th>Alert</th><th>Target</th><th>Severity</th><th>Date</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr class="<?php echo empty($row['is_read'])?'table-warning':''; ?>"><td><strong><?php echo esc($row['title']); ?></strong><div class="small text-secondary"><?php echo esc($row['message']); ?></div></td><td><?php echo esc($row['user_email']?:($row['role_slug']?:'All')); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['severity'])); ?>"><?php echo esc($row['severity']); ?></span></td><td><?php echo esc($row['created_at']); ?></td><td class="text-end"><?php if($row['link_url']): ?><a class="btn btn-sm btn-outline-primary" href="<?php echo esc($row['link_url']); ?>">Open</a><?php endif; ?><?php if(empty($row['is_read'])): ?> <a class="btn btn-sm btn-outline-secondary" href="?read=<?php echo (int)$row['id']; ?>">Read</a><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-secondary">No notifications.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>