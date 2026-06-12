<?php
$pageTitle='Financial Close';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('financial_close');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='create_close'){
      createFinancialClosePeriod($pdo,trim((string)$_POST['period_name']),trim((string)$_POST['date_from']),trim((string)$_POST['date_to']),trim((string)($_POST['notes']??'')));
      flash('success','Financial close period created.');
    }elseif($action==='complete_task'){
      $user=currentUser();
      $pdo->prepare('UPDATE '.table('financial_close_tasks').' SET status="completed",completed_by=?,completed_at=NOW(),evidence_notes=? WHERE id=?')->execute([(int)($user['id']??0)?:null,trim((string)($_POST['evidence_notes']??'')),(int)$_POST['task_id']]);
      flash('success','Close task completed.');
    }elseif($action==='close_period'){
      $periodId=(int)$_POST['period_id'];
      $stmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('financial_close_tasks').' WHERE financial_close_period_id=? AND status<>"completed"');
      $stmt->execute([$periodId]);
      if((int)$stmt->fetchColumn()>0){throw new RuntimeException('All close tasks must be completed before closing period.');}
      $pdo->prepare('UPDATE '.table('financial_close_periods').' SET status="closed",closed_at=NOW() WHERE id=?')->execute([$periodId]);
      flash('success','Financial close period closed.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'financial-close']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/financial-close.php');
}
$periods=$pdo->query('SELECT p.*,(SELECT COUNT(*) FROM '.table('financial_close_tasks').' t WHERE t.financial_close_period_id=p.id) total_tasks,(SELECT COUNT(*) FROM '.table('financial_close_tasks').' t WHERE t.financial_close_period_id=p.id AND t.status="completed") done_tasks FROM '.table('financial_close_periods').' p ORDER BY p.created_at DESC LIMIT 100')->fetchAll();
$selectedId=(int)($_GET['id']??($periods[0]['id']??0));
$tasks=[];
if($selectedId){$stmt=$pdo->prepare('SELECT t.*,u.email completed_by_email FROM '.table('financial_close_tasks').' t LEFT JOIN '.table('users').' u ON u.id=t.completed_by WHERE t.financial_close_period_id=? ORDER BY FIELD(t.status,"open","completed"),t.category,t.id');$stmt->execute([$selectedId]);$tasks=$stmt->fetchAll();}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Period-End Governance</div><h2 class="h4 mb-1">Financial Close</h2><p class="text-secondary mb-0">Control period close tasks across AR, AP, bank, inventory, payroll, tax, reporting, and audit evidence.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/audit-controls.php"><?php echo t('Audit Controls', 'ضوابط التدقيق'); ?></a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><input type="hidden" name="action" value="create_close"><h2 class="h5 mb-3">Create Close Period</h2><label class="form-label">Period Name</label><input class="form-control mb-2" name="period_name" value="<?php echo esc(date('F Y')); ?>"><div class="row g-2"><div class="col-6"><label class="form-label">From</label><input class="form-control" type="date" name="date_from" value="<?php echo esc(date('Y-m-01')); ?>"></div><div class="col-6"><label class="form-label">To</label><input class="form-control" type="date" name="date_to" value="<?php echo esc(date('Y-m-t')); ?>"></div></div><label class="form-label mt-2">Notes</label><textarea class="form-control mb-3" name="notes" rows="3"></textarea><button class="btn btn-brand w-100">Create Close</button></form><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Close Periods</h2><table class="table"><tbody><?php foreach($periods as $p): $pct=(int)$p['total_tasks']>0?round((int)$p['done_tasks']/(int)$p['total_tasks']*100):0; ?><tr><td><a href="?id=<?php echo (int)$p['id']; ?>"><strong><?php echo esc($p['close_number']); ?></strong></a><div class="small text-secondary"><?php echo esc($p['period_name']); ?> · <?php echo $pct; ?>%</div></td><td><span class="badge bg-<?php echo esc(statusTone($p['status'])); ?>"><?php echo esc($p['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$periods): ?><tr><td class="text-secondary">No close periods yet.</td></tr><?php endif; ?></tbody></table></div></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Close Checklist</div><h2 class="h5 mb-0">Tasks</h2></div><?php if($selectedId): ?><form method="post"><input type="hidden" name="action" value="close_period"><input type="hidden" name="period_id" value="<?php echo (int)$selectedId; ?>"><button class="btn btn-sm btn-success">Close Period</button></form><?php endif; ?></div><table class="table align-middle"><thead><tr><th>Task</th><th>Category</th><th>Status</th><th>Evidence</th><th></th></tr></thead><tbody><?php foreach($tasks as $task): ?><tr><td><strong><?php echo esc($task['task_name']); ?></strong><div class="small text-secondary"><?php echo esc($task['task_key']); ?></div></td><td><?php echo esc($task['category']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($task['status'])); ?>"><?php echo esc($task['status']); ?></span><div class="small text-secondary"><?php echo esc($task['completed_by_email']?:''); ?></div></td><td><?php echo esc($task['evidence_notes']); ?></td><td class="text-end"><?php if($task['status']!=='completed'): ?><form method="post" class="d-flex gap-1"><input type="hidden" name="action" value="complete_task"><input type="hidden" name="task_id" value="<?php echo (int)$task['id']; ?>"><input class="form-control form-control-sm" name="evidence_notes" placeholder="Evidence note"><button class="btn btn-sm btn-outline-success">Done</button></form><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$tasks): ?><tr><td colspan="5" class="text-secondary">Select or create a close period.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>