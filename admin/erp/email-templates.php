<?php
$pageTitle='Email Templates';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('email_templates');
$pdo=getDB();
$edit=null;
if(isset($_GET['edit'])){$stmt=$pdo->prepare('SELECT * FROM '.table('email_templates').' WHERE id=? LIMIT 1');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch()?:null;}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $key=preg_replace('/[^a-z0-9_]/','',strtolower((string)($_POST['template_key']??'')));
  $name=trim((string)($_POST['template_name']??''));
  $subject=trim((string)($_POST['subject']??''));
  $body=(string)($_POST['body']??'');
  $status=in_array((string)($_POST['status']??'active'),['active','inactive'],true)?(string)$_POST['status']:'active';
  if($key===''||$name===''||$subject===''){flash('error','Template key, name and subject are required.');redirect(ADMIN_URL.'/erp/email-templates.php'.($id?'?edit='.$id:''));}
  try{
    if($id>0){
      $stmt=$pdo->prepare('UPDATE '.table('email_templates').' SET template_key=?,template_name=?,subject=?,body=?,status=?,updated_at=NOW() WHERE id=?');
      $stmt->execute([$key,$name,$subject,$body,$status,$id]);
      logActivity($pdo,'Email','email_template_updated','Email template '.$key.' updated.','email_template',$id);
    }else{
      $stmt=$pdo->prepare('INSERT INTO '.table('email_templates').' (template_key,template_name,subject,body,status) VALUES (?,?,?,?,?)');
      $stmt->execute([$key,$name,$subject,$body,$status]);$id=(int)$pdo->lastInsertId();
      logActivity($pdo,'Email','email_template_created','Email template '.$key.' created.','email_template',$id);
    }
    flash('success','Email template saved.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'email-templates']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/email-templates.php');
}
$templates=$pdo->query('SELECT * FROM '.table('email_templates').' ORDER BY status DESC,template_name ASC')->fetchAll();
$queue=$pdo->query('SELECT * FROM '.table('email_queue').' ORDER BY created_at DESC,id DESC LIMIT 30')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Communication Templates</div><h2 class="h4 mb-1">Email Templates</h2><p class="text-secondary mb-0">Manage transactional templates for approvals, orders, low stock, backups, and credit alerts.</p></div><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/notifications.php">Notifications</a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>"><div class="erp-kicker">Template Editor</div><h2 class="h5 mb-3"><?php echo $edit?'Edit Template':'Create Template'; ?></h2><label class="form-label">Template Key</label><input class="form-control mb-3" name="template_key" value="<?php echo esc($edit['template_key']??''); ?>" placeholder="order_confirmation" required><label class="form-label">Template Name</label><input class="form-control mb-3" name="template_name" value="<?php echo esc($edit['template_name']??''); ?>" required><label class="form-label">Subject</label><input class="form-control mb-3" name="subject" value="<?php echo esc($edit['subject']??''); ?>" required><label class="form-label">Body</label><textarea class="form-control mb-3" name="body" rows="8" placeholder="Use variables like {{customer_name}}, {{order_number}}, {{total}}"><?php echo esc($edit['body']??''); ?></textarea><label class="form-label">Status</label><select class="form-select mb-3" name="status"><option value="active" <?php echo (($edit['status']??'active')==='active')?'selected':''; ?>>Active</option><option value="inactive" <?php echo (($edit['status']??'active')==='inactive')?'selected':''; ?>>Inactive</option></select><button class="btn btn-brand w-100">Save Template</button><?php if($edit): ?><a class="btn btn-outline-secondary w-100 mt-2" href="<?php echo esc(ADMIN_URL); ?>/erp/email-templates.php">Cancel Edit</a><?php endif; ?></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">Template Library</div><h2 class="h5 mb-0">Active Templates</h2></div></div><table class="table align-middle"><thead><tr><th>Template</th><th>Subject</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody><?php foreach($templates as $tpl): ?><tr><td><strong><?php echo esc($tpl['template_name']); ?></strong><div class="small text-secondary"><?php echo esc($tpl['template_key']); ?></div></td><td><?php echo esc($tpl['subject']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($tpl['status'])); ?>"><?php echo esc($tpl['status']); ?></span></td><td><?php echo esc($tpl['updated_at']?:$tpl['created_at']); ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int)$tpl['id']; ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Recent Email Queue</h2><table class="table"><thead><tr><th>Recipient</th><th>Subject</th><th>Status</th><th>Attempts</th></tr></thead><tbody><?php foreach($queue as $item): ?><tr><td><?php echo esc($item['recipient_email']); ?></td><td><?php echo esc($item['subject']); ?><div class="small text-secondary"><?php echo esc($item['last_error']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($item['status'])); ?>"><?php echo esc($item['status']); ?></span></td><td><?php echo (int)$item['attempts']; ?></td></tr><?php endforeach; ?><?php if(!$queue): ?><tr><td colspan="4" class="text-secondary">No queued emails yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>