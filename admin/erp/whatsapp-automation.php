<?php
$pageTitle='WhatsApp Automation';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('whatsapp_automation');
$pdo=getDB();
$templates=$pdo->query('SELECT * FROM '.table('whatsapp_templates').' ORDER BY status DESC,template_name')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='template'){
      $code=preg_replace('/[^a-z0-9_]/','',strtolower((string)$_POST['template_code']));
      $pdo->prepare('INSERT INTO '.table('whatsapp_templates').' (template_code,template_name,language_code,category,body,status) VALUES (?,?,?,?,?,"active")')->execute([$code,trim((string)$_POST['template_name']),trim((string)$_POST['language_code']),trim((string)$_POST['category']),trim((string)$_POST['body'])]);
      flash('success','WhatsApp template created.');
    }elseif($action==='queue'){
      queueWhatsAppMessage($pdo,(int)$_POST['template_id']?:null,trim((string)$_POST['recipient_phone']),trim((string)$_POST['recipient_name']),trim((string)$_POST['message_body']));
      flash('success','WhatsApp message queued.');
    }elseif($action==='sent'){
      markWhatsAppSent($pdo,(int)$_POST['id']);flash('success','WhatsApp message marked sent.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'whatsapp-automation']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/whatsapp-automation.php');
}
$queue=$pdo->query('SELECT q.*,t.template_code,t.template_name FROM '.table('whatsapp_queue').' q LEFT JOIN '.table('whatsapp_templates').' t ON t.id=q.template_id ORDER BY q.created_at DESC LIMIT 150')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Customer Messaging</div><h2 class="h4 mb-1">WhatsApp Automation</h2><p class="text-secondary mb-0">Create templates, queue customer WhatsApp messages, and track manual/provider sending status.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/communication-automation.php">Automation Rules</a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><input type="hidden" name="action" value="queue"><h2 class="h5 mb-3">Queue Message</h2><select class="form-select mb-2" name="template_id"><option value="0">No template</option><?php foreach($templates as $t): ?><option value="<?php echo (int)$t['id']; ?>"><?php echo esc($t['template_code'].' · '.$t['template_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="recipient_name" placeholder="Customer name"><input class="form-control mb-2" name="recipient_phone" value="<?php echo esc(setting('whatsapp_default_country_code','+971')); ?>" required><textarea class="form-control mb-3" name="message_body" rows="4" placeholder="Message body"></textarea><button class="btn btn-brand w-100">Queue Message</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="template"><h2 class="h5 mb-3">Create Template</h2><input class="form-control mb-2" name="template_code" placeholder="template_code"><input class="form-control mb-2" name="template_name" placeholder="Template name"><input class="form-control mb-2" name="language_code" value="en"><input class="form-control mb-2" name="category" placeholder="Order / Sales / Finance"><textarea class="form-control mb-3" name="body" rows="4" placeholder="Hello {{name}}..."></textarea><button class="btn btn-outline-primary w-100">Create Template</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Message Queue</h2><table class="table align-middle"><thead><tr><th>Message</th><th>Recipient</th><th>Template</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($queue as $q): ?><tr><td><strong><?php echo esc($q['queue_number']); ?></strong><div class="small text-secondary"><?php echo esc($q['message_body']); ?></div></td><td><?php echo esc($q['recipient_name']); ?><div class="small text-secondary"><?php echo esc($q['recipient_phone']); ?></div></td><td><?php echo esc($q['template_code']?:'manual'); ?></td><td><span class="badge bg-<?php echo esc(statusTone($q['status'])); ?>"><?php echo esc($q['status']); ?></span></td><td><?php if($q['status']==='queued'): ?><form method="post"><input type="hidden" name="action" value="sent"><input type="hidden" name="id" value="<?php echo (int)$q['id']; ?>"><button class="btn btn-sm btn-outline-success">Mark Sent</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Templates</h2><table class="table"><thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th></tr></thead><tbody><?php foreach($templates as $t): ?><tr><td><code><?php echo esc($t['template_code']); ?></code></td><td><?php echo esc($t['template_name']); ?></td><td><?php echo esc($t['category']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($t['status'])); ?>"><?php echo esc($t['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>