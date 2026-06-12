<?php
$pageTitle='Webhooks';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('webhooks');
$pdo=getDB();
$eventTypes=['order.created','order.updated','invoice.paid','quotation.sent','lead.created','inventory.low','approval.completed','all'];
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='subscribe'){
      $code=nextScopedDocumentNumber($pdo,'webhook_subscription',setting('webhook_subscription_prefix','WHS'),operationalScope($pdo));$user=currentUser();
      $pdo->prepare('INSERT INTO '.table('webhook_subscriptions').' (subscription_code,event_type,target_url,secret_token,status,created_by) VALUES (?,?,?,?,"active",?)')->execute([$code,trim((string)$_POST['event_type']),trim((string)$_POST['target_url']),trim((string)$_POST['secret_token']),(int)($user['id']??0)?:null]);
      flash('success','Webhook subscription created.');
    }elseif($action==='queue_event'){
      queueWebhookEvent($pdo,trim((string)$_POST['event_type']),['demo'=>true,'created_at'=>date('c'),'message'=>'Manual test webhook event'],'manual',0);flash('success','Webhook event queued.');
    }elseif($action==='deliver'){
      $result=deliverWebhookEvent($pdo,(int)$_POST['event_id']);flash('success','Webhook delivery simulated: '.$result['delivered'].' delivered, '.$result['failed'].' failed.');
    }elseif($action==='toggle'){
      $pdo->prepare('UPDATE '.table('webhook_subscriptions').' SET status=CASE WHEN status="active" THEN "inactive" ELSE "active" END WHERE id=?')->execute([(int)$_POST['id']]);flash('success','Webhook status changed.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'webhooks']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/webhooks.php');
}
$subs=$pdo->query('SELECT * FROM '.table('webhook_subscriptions').' ORDER BY created_at DESC')->fetchAll();
$events=$pdo->query('SELECT * FROM '.table('webhook_events').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
$attempts=$pdo->query('SELECT a.*,e.event_number,s.subscription_code FROM '.table('webhook_delivery_attempts').' a LEFT JOIN '.table('webhook_events').' e ON e.id=a.webhook_event_id LEFT JOIN '.table('webhook_subscriptions').' s ON s.id=a.webhook_subscription_id ORDER BY a.created_at DESC LIMIT 80')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Event Delivery</div><h2 class="h4 mb-1">Webhooks</h2><p class="text-secondary mb-0">Subscribe external systems to ERP events and inspect delivery attempts.</p></div><form method="post"><input type="hidden" name="action" value="queue_event"><input type="hidden" name="event_type" value="order.created"><button class="btn btn-brand">Queue Test Event</button></form></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="subscribe"><h2 class="h5 mb-3">New Subscription</h2><select class="form-select mb-2" name="event_type"><?php foreach($eventTypes as $type): ?><option value="<?php echo esc($type); ?>"><?php echo esc($type); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="target_url" placeholder="https://example.com/webhook" required><input class="form-control mb-3" name="secret_token" placeholder="Signing secret"><button class="btn btn-brand w-100">Create Webhook</button></form><div class="table-wrap table-responsive mt-4"><h2 class="h5 mb-3">Subscriptions</h2><table class="table"><tbody><?php foreach($subs as $sub): ?><tr><td><strong><?php echo esc($sub['subscription_code']); ?></strong><div class="small text-secondary"><?php echo esc($sub['event_type']); ?><br><?php echo esc($sub['target_url']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($sub['status'])); ?>"><?php echo esc($sub['status']); ?></span><form method="post" class="mt-1"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?php echo (int)$sub['id']; ?>"><button class="btn btn-sm btn-outline-secondary">Toggle</button></form></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Events</h2><table class="table align-middle"><thead><tr><th>Event</th><th>Type</th><th>Attempts</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($events as $event): ?><tr><td><strong><?php echo esc($event['event_number']); ?></strong><div class="small text-secondary"><?php echo esc($event['created_at']); ?></div></td><td><?php echo esc($event['event_type']); ?></td><td><?php echo (int)$event['attempt_count']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($event['status'])); ?>"><?php echo esc($event['status']); ?></span></td><td><form method="post"><input type="hidden" name="action" value="deliver"><input type="hidden" name="event_id" value="<?php echo (int)$event['id']; ?>"><button class="btn btn-sm btn-outline-success">Deliver</button></form></td></tr><?php endforeach; ?><?php if(!$events): ?><tr><td colspan="5" class="text-secondary">No webhook events yet.</td></tr><?php endif; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Delivery Attempts</h2><table class="table"><thead><tr><th>Event</th><th>Subscription</th><th>Target</th><th>HTTP</th><th>Status</th></tr></thead><tbody><?php foreach($attempts as $a): ?><tr><td><?php echo esc($a['event_number']); ?></td><td><?php echo esc($a['subscription_code']); ?></td><td><?php echo esc($a['target_url']); ?></td><td><?php echo (int)$a['http_status']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($a['status'])); ?>"><?php echo esc($a['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>