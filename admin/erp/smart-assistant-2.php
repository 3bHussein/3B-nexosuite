<?php
$pageTitle='Smart Assistant 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('ai_assistant_2');
$pdo=getDB();$user=currentUser();$sessionId=(int)($_GET['session_id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $message=trim((string)($_POST['message']??''));if($message===''){throw new RuntimeException('Please type a message.');}
    $sessionId=(int)($_POST['session_id']??0);
    if($sessionId<=0){$number=nextScopedDocumentNumber($pdo,'ai_assistant_session','AIS',operationalScope($pdo));$pdo->prepare('INSERT INTO '.table('ai_assistant_sessions').' (session_number,session_title,user_id,module_context,status) VALUES (?,?,?,?, "open")')->execute([$number,substr($message,0,120),(int)($user['id']??0)?:null,trim((string)($_POST['module_context']??'Smart Assistant 2.0'))]);$sessionId=(int)$pdo->lastInsertId();}
    $pdo->prepare('INSERT INTO '.table('ai_assistant_messages').' (ai_assistant_session_id,sender_type,message_text,intent_key,confidence_score) VALUES (?, "user", ?, "smart_prompt", 100)')->execute([$sessionId,$message]);
    $reply=smartAssistant2Response($pdo,$message);
    $pdo->prepare('INSERT INTO '.table('ai_assistant_messages').' (ai_assistant_session_id,sender_type,message_text,intent_key,confidence_score,action_json) VALUES (?, "assistant", ?, "smart_assistant_2", 90, ?)')->execute([$sessionId,$reply,json_encode(['mode'=>'rules_based_ai_2','context'=>'ERP'])]);
    flash('success','Smart response generated.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'smart-assistant-2']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/smart-assistant-2.php?session_id='.$sessionId);
}
$sessions=$pdo->query('SELECT * FROM '.table('ai_assistant_sessions').' ORDER BY created_at DESC LIMIT 50')->fetchAll();
$messages=[];
if($sessionId>0){$stmt=$pdo->prepare('SELECT * FROM '.table('ai_assistant_messages').' WHERE ai_assistant_session_id=? ORDER BY created_at ASC,id ASC');$stmt->execute([$sessionId]);$messages=$stmt->fetchAll();}
$suggestions=$pdo->query('SELECT * FROM '.table('ai_assistant_action_suggestions').' WHERE status="open" ORDER BY FIELD(priority,"critical","high","medium","low"),created_at DESC LIMIT 8')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Context-Aware Assistant</div><h2 class="h4 mb-1">Smart Assistant 2.0</h2><p class="text-secondary mb-0">Ask about risk, invoices, stock, sales, suppliers, technicians, or operations. This is rules-based ERP intelligence, not external AI API.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/ai-automation-dashboard.php">Run AI Engine</a></div>
<div class="row g-4"><div class="col-xl-3"><div class="table-wrap table-responsive"><h2 class="h6 mb-3">Sessions</h2><table class="table table-sm"><tbody><?php foreach($sessions as $s): ?><tr><td><a href="?session_id=<?php echo (int)$s['id']; ?>"><strong><?php echo esc($s['session_number']); ?></strong></a><div class="small text-secondary"><?php echo esc($s['session_title']); ?></div></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-6"><div class="card-admin p-3 mb-3" style="min-height:420px"><?php foreach($messages as $m): ?><div class="mb-3 <?php echo $m['sender_type']==='assistant'?'text-primary':''; ?>"><strong><?php echo esc(ucfirst($m['sender_type'])); ?>:</strong><div class="border rounded-4 p-3 mt-1 bg-white"><?php echo nl2br(esc($m['message_text'])); ?></div></div><?php endforeach; ?><?php if(!$messages): ?><div class="text-secondary">Start by asking: "what is my biggest risk today?", "show invoice collection risk", "low stock risk", "hot leads", or "supplier risk".</div><?php endif; ?></div><form method="post" class="card-admin p-3"><input type="hidden" name="session_id" value="<?php echo (int)$sessionId; ?>"><div class="row g-2"><div class="col-md-4"><select class="form-select" name="module_context"><option>General</option><option>Finance</option><option>Inventory</option><option>Sales</option><option>Procurement</option><option>Service</option></select></div><div class="col-md-6"><input class="form-control" name="message" placeholder="Ask Smart Assistant 2.0..."></div><div class="col-md-2"><button class="btn btn-brand w-100">Ask</button></div></div></form></div><div class="col-xl-3"><div class="table-wrap table-responsive"><h2 class="h6 mb-3">Open Actions</h2><table class="table table-sm"><tbody><?php foreach($suggestions as $s): ?><tr><td><strong><?php echo esc($s['suggestion_title']); ?></strong><div class="small text-secondary"><?php echo esc($s['module'].' · '.$s['priority']); ?></div><a class="btn btn-sm btn-outline-primary mt-1" href="<?php echo esc($s['action_url'] ?: '#'); ?>"><?php echo esc($s['action_label'] ?: 'Open'); ?></a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>