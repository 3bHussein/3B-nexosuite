<?php
$pageTitle='Deployment Checklist';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('deployment_checklist');
$pdo=getDB();

/*
  Priority 7 self-repair guard:
  If this page is opened on an existing Priority 6 database, the new Priority 7
  table may not exist yet. This creates the missing table and default checklist
  instead of throwing HTTP 500.
*/
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS '.table('deployment_checklist_items').' (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_key VARCHAR(160) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(120),
        severity VARCHAR(40) DEFAULT "medium",
        status VARCHAR(40) DEFAULT "open",
        recommendation TEXT,
        completed_by INT NULL,
        completed_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB');
    $existingChecklist=(int)$pdo->query('SELECT COUNT(*) FROM '.table('deployment_checklist_items'))->fetchColumn();
    if($existingChecklist===0){
        $checklistStmt=$pdo->prepare('INSERT INTO '.table('deployment_checklist_items').' (item_key,title,category,severity,status,recommendation) VALUES (?,?,?,?,?,?)');
        $defaultChecklist=[
            ['https_ssl','Enable HTTPS / SSL','Security','high','open','Point the production domain to HTTPS and update SHOP_URL to https://.'],
            ['disable_display_errors','Disable PHP display_errors','Security','high','open','Keep display_errors disabled and log errors server-side.'],
            ['backup_schedule','Configure backup schedule','Operations','high','open','Schedule database backups and verify restore procedure.'],
            ['cron_job','Configure cron runner','Automation','medium','open','Add a server cron hitting /admin/erp/cron-endpoint.php?token=YOUR_SECRET or run from UI.'],
            ['mail_delivery','Verify email delivery','Communication','medium','open','Configure SMTP/server email sending and test notification templates.'],
            ['file_permissions','Check file permissions','Security','high','open','Uploads/backups should be writable; config and PHP source should not be publicly editable.'],
            ['api_key_review','Review API keys','Integration','medium','open','Create scoped API keys only for approved integrations and rotate regularly.'],
            ['role_audit','Review employee roles','Access Control','medium','open','Confirm least-privilege roles and branch scopes for all employees.'],
        ];
        foreach($defaultChecklist as $item){$checklistStmt->execute($item);}
    }
} catch(Throwable $e) {
    if(function_exists('recordSystemError')){recordSystemError($pdo,$e,['page'=>'deployment-checklist-self-repair']);}
    flash('error','Deployment checklist table repair failed: '.$e->getMessage());
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $id=(int)($_POST['id']??0);
    $status=in_array((string)($_POST['status']??'open'),['open','completed'],true)?(string)$_POST['status']:'open';
    $user=currentUser();
    if($status==='completed'){
        $pdo->prepare('UPDATE '.table('deployment_checklist_items').' SET status="completed",completed_by=?,completed_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,$id]);
    }else{
        $pdo->prepare('UPDATE '.table('deployment_checklist_items').' SET status="open",completed_by=NULL,completed_at=NULL WHERE id=?')->execute([$id]);
    }
    logActivity($pdo,'Deployment','deployment_checklist_updated','Deployment checklist item #'.$id.' set to '.$status.'.','deployment_checklist',$id);
    flash('success','Checklist item updated.');
  }catch(Throwable $e){
    if(function_exists('recordSystemError')){recordSystemError($pdo,$e,['page'=>'deployment-checklist-post']);}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/deployment-checklist.php');
}

try{
    $rows=$pdo->query('SELECT dci.*,u.email completed_by_email FROM '.table('deployment_checklist_items').' dci LEFT JOIN '.table('users').' u ON u.id=dci.completed_by ORDER BY FIELD(dci.status,"open","completed"),FIELD(dci.severity,"high","medium","low"),dci.category,dci.title')->fetchAll();
}catch(Throwable $e){
    if(function_exists('recordSystemError')){recordSystemError($pdo,$e,['page'=>'deployment-checklist-query']);}
    $rows=[];
    flash('error','Unable to load deployment checklist: '.$e->getMessage());
}
$done=0;
foreach($rows as $row){if(($row['status']??'')==='completed'){$done++;}}
$percent=count($rows)>0?round($done/count($rows)*100):0;
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <div class="erp-kicker">Production Readiness</div>
    <h2 class="h4 mb-1">Deployment Checklist</h2>
    <p class="text-secondary mb-0">Track core launch controls before moving the ERP-commerce system into production.</p>
  </div>
  <a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/system-health.php"><?php echo t('System Health', 'صحة النظام'); ?></a>
</div>
<div class="card-admin p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <strong>Readiness Progress</strong>
    <span><?php echo $done; ?> / <?php echo count($rows); ?> complete</span>
  </div>
  <div class="progress" style="height:12px"><div class="progress-bar" style="width:<?php echo (int)$percent; ?>%"></div></div>
</div>
<div class="table-wrap table-responsive">
  <div class="table-toolbar">
    <div><div class="erp-kicker">Checklist</div><h2 class="h5 mb-0">Launch Items</h2></div>
  </div>
  <table class="table align-middle">
    <thead><tr><th>Item</th><th>Category</th><th>Severity</th><th>Status</th><th>Completed By</th><th></th></tr></thead>
    <tbody>
      <?php foreach($rows as $row): ?>
      <tr>
        <td><strong><?php echo esc($row['title']??''); ?></strong><div class="small text-secondary"><?php echo esc($row['recommendation']??''); ?></div></td>
        <td><?php echo esc($row['category']??''); ?></td>
        <td><span class="badge bg-<?php echo esc(statusTone(($row['severity']??'medium')==='high'?'fail':(($row['severity']??'medium')==='medium'?'warning':'ok'))); ?>"><?php echo esc($row['severity']??'medium'); ?></span></td>
        <td><span class="badge bg-<?php echo esc(statusTone($row['status']??'open')); ?>"><?php echo esc($row['status']??'open'); ?></span></td>
        <td><?php echo esc($row['completed_by_email']?:'—'); ?><div class="small text-secondary"><?php echo esc($row['completed_at']?:''); ?></div></td>
        <td class="text-end">
          <form method="post">
            <input type="hidden" name="id" value="<?php echo (int)($row['id']??0); ?>">
            <input type="hidden" name="status" value="<?php echo ($row['status']??'open')==='completed'?'open':'completed'; ?>">
            <button class="btn btn-sm btn-outline-primary"><?php echo ($row['status']??'open')==='completed'?'Reopen':'Mark Complete'; ?></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No checklist items found. Refresh this page once; the self-repair guard will recreate defaults if the table is empty.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>