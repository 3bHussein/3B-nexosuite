<?php
$pageTitle='Technician Mobile';
require_once dirname(__DIR__,2) . '/includes/functions.php';

function techMobileSafeExec(PDO $pdo, string $sql): void
{
    try { $pdo->exec($sql); } catch (Throwable $e) { /* keep page alive; visible warning is shown later if needed */ }
}

function techMobileEnsureRuntime(PDO $pdo): void
{
    techMobileSafeExec($pdo,'CREATE TABLE IF NOT EXISTS '.table('field_service_dispatches').' (id INT AUTO_INCREMENT PRIMARY KEY,dispatch_number VARCHAR(120) NOT NULL UNIQUE,job_card_id INT NULL,technician_user_id INT NULL,dispatch_date DATE NULL,start_time TIME NULL,end_time TIME NULL,service_address TEXT,customer_contact VARCHAR(255),gps_lat DECIMAL(12,8) NULL,gps_lng DECIMAL(12,8) NULL,priority VARCHAR(40) DEFAULT "normal",dispatch_status VARCHAR(40) DEFAULT "scheduled",created_by INT NULL,accepted_at DATETIME NULL,enroute_at DATETIME NULL,arrived_at DATETIME NULL,completed_at DATETIME NULL,notes TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    techMobileSafeExec($pdo,'CREATE TABLE IF NOT EXISTS '.table('technician_portal_notes').' (id INT AUTO_INCREMENT PRIMARY KEY,job_card_id INT NULL,technician_user_id INT NULL,note_type VARCHAR(80) DEFAULT "progress",note TEXT,status VARCHAR(40) DEFAULT "active",created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    techMobileSafeExec($pdo,'CREATE TABLE IF NOT EXISTS '.table('mobile_parts_usage').' (id INT AUTO_INCREMENT PRIMARY KEY,job_card_id INT NOT NULL,product_id INT NULL,technician_user_id INT NULL,barcode_scan_log_id INT NULL,quantity DECIMAL(12,2) DEFAULT 1,unit_cost DECIMAL(12,2) DEFAULT 0,status VARCHAR(40) DEFAULT "used",notes TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    techMobileSafeExec($pdo,'CREATE TABLE IF NOT EXISTS '.table('mobile_time_entries').' (id INT AUTO_INCREMENT PRIMARY KEY,job_card_id INT NULL,technician_user_id INT NULL,timer_start DATETIME NULL,timer_end DATETIME NULL,duration_minutes INT DEFAULT 0,status VARCHAR(40) DEFAULT "running",notes TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    techMobileSafeExec($pdo,'CREATE TABLE IF NOT EXISTS '.table('barcode_scan_logs').' (id INT AUTO_INCREMENT PRIMARY KEY,scan_value VARCHAR(255) NOT NULL,scan_type VARCHAR(80) DEFAULT "qr",source_module VARCHAR(120),reference_type VARCHAR(120),reference_id INT NULL,scanned_by INT NULL,scan_result VARCHAR(120) DEFAULT "captured",scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP,notes TEXT) ENGINE=InnoDB');

    $alter = [
        table('job_cards').' ADD COLUMN technician_user_id INT NULL',
        table('job_cards').' ADD COLUMN job_card_number VARCHAR(120) NULL',
        table('job_cards').' ADD COLUMN customer_name VARCHAR(255) NULL',
        table('job_cards').' ADD COLUMN plate_number VARCHAR(80) NULL',
        table('job_cards').' ADD COLUMN status VARCHAR(50) DEFAULT "draft"',
        table('field_service_dispatches').' ADD COLUMN dispatch_status VARCHAR(40) DEFAULT "scheduled"',
        table('field_service_dispatches').' ADD COLUMN dispatch_number VARCHAR(120) NULL',
        table('field_service_dispatches').' ADD COLUMN technician_user_id INT NULL',
        table('field_service_dispatches').' ADD COLUMN job_card_id INT NULL',
        table('mobile_time_entries').' ADD COLUMN duration_minutes INT DEFAULT 0',
    ];
    foreach($alter as $ddl){ techMobileSafeExec($pdo,'ALTER TABLE '.$ddl); }
}

function techMobileTableOk(PDO $pdo, string $table): bool
{
    try {
        $stmt=$pdo->query('SHOW TABLES LIKE '.$pdo->quote(DB_PREFIX.$table));
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) { return false; }
}

function techMobileLogScan(PDO $pdo, string $scanValue, string $referenceType, int $referenceId, int $userId): ?int
{
    $scanValue=trim($scanValue);
    if($scanValue===''){ return null; }
    if(function_exists('logBarcodeScan')){
        try { return logBarcodeScan($pdo,$scanValue,'barcode','technician-mobile',$referenceType,$referenceId,'part_used'); } catch(Throwable $e) {}
    }
    try {
        $stmt=$pdo->prepare('INSERT INTO '.table('barcode_scan_logs').' (scan_value,scan_type,source_module,reference_type,reference_id,scanned_by,scan_result,notes) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$scanValue,'barcode','technician-mobile',$referenceType,$referenceId,$userId,'captured','Part usage scan']);
        return (int)$pdo->lastInsertId();
    } catch(Throwable $e) { return null; }
}

erpGuard('technician_portal');
$pdo=getDB();
techMobileEnsureRuntime($pdo);
$user=currentUser();
$uid=(int)($user['id']??0);
$pageWarning='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='note'){
      $pdo->prepare('INSERT INTO '.table('technician_portal_notes').' (job_card_id,technician_user_id,note_type,note,status) VALUES (?,?,?,?, "active")')->execute([(int)$_POST['job_card_id'],$uid,trim((string)$_POST['note_type']),trim((string)$_POST['note'])]);
      flash('success','Mobile note saved.');
    }elseif($action==='part'){
      $scanId=techMobileLogScan($pdo,(string)($_POST['scan_value']??''),'job_card',(int)$_POST['job_card_id'],$uid);
      $cost=0.0;
      try{$pstmt=$pdo->prepare('SELECT COALESCE(NULLIF(average_cost,0), NULLIF(cost_price,0), 0) FROM '.table('products').' WHERE id=? LIMIT 1');$pstmt->execute([(int)$_POST['product_id']]);$cost=(float)$pstmt->fetchColumn();}catch(Throwable $e){}
      $pdo->prepare('INSERT INTO '.table('mobile_parts_usage').' (job_card_id,product_id,technician_user_id,barcode_scan_log_id,quantity,unit_cost,status,notes) VALUES (?,?,?,?,?,?,"used",?)')->execute([(int)$_POST['job_card_id'],(int)$_POST['product_id'],$uid,$scanId,max(0,(float)$_POST['quantity']),$cost,trim((string)$_POST['notes'])]);
      flash('success','Part usage recorded.');
    }elseif($action==='start_timer'){
      $pdo->prepare('INSERT INTO '.table('mobile_time_entries').' (job_card_id,technician_user_id,timer_start,status,notes) VALUES (?,?,NOW(),"running",?)')->execute([(int)$_POST['job_card_id'],$uid,trim((string)($_POST['notes']??''))]);
      flash('success','Timer started.');
    }elseif($action==='stop_timer'){
      $id=(int)$_POST['time_entry_id'];
      $pdo->prepare('UPDATE '.table('mobile_time_entries').' SET timer_end=NOW(),duration_minutes=TIMESTAMPDIFF(MINUTE,timer_start,NOW()),status="stopped" WHERE id=? AND technician_user_id=?')->execute([$id,$uid]);
      flash('success','Timer stopped.');
    }elseif($action==='dispatch_status'){
      $status=trim((string)$_POST['dispatch_status']);
      $allowed=['accepted','enroute','arrived','completed'];
      if(!in_array($status,$allowed,true)){throw new RuntimeException('Invalid dispatch status.');}
      $fields=['accepted'=>'accepted_at','enroute'=>'enroute_at','arrived'=>'arrived_at','completed'=>'completed_at'];
      $sql='UPDATE '.table('field_service_dispatches').' SET dispatch_status=?';
      if(isset($fields[$status])){$sql.=','.$fields[$status].'=NOW()';}
      $sql.=' WHERE id=? AND technician_user_id=?';
      $pdo->prepare($sql)->execute([$status,(int)$_POST['dispatch_id'],$uid]);
      if($status==='completed'){
        $jobId=(int)($_POST['job_card_id']??0);
        if($jobId>0){techMobileSafeExec($pdo,'UPDATE '.table('job_cards').' SET status="completed",completed_at=NOW() WHERE id='.(int)$jobId);}
      }
      flash('success','Dispatch status updated.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'technician-mobile']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/technician-mobile.php');
}

$assignedJobs=[];
$products=[];
$times=[];

try{
    if(techMobileTableOk($pdo,'job_cards')){
        $jobs=$pdo->prepare('SELECT jc.*,d.id dispatch_id,d.dispatch_number,d.dispatch_status FROM '.table('job_cards').' jc LEFT JOIN '.table('field_service_dispatches').' d ON d.job_card_id=jc.id AND d.technician_user_id=? WHERE jc.technician_user_id=? OR d.technician_user_id=? ORDER BY jc.created_at DESC LIMIT 100');
        $jobs->execute([$uid,$uid,$uid]);
        $assignedJobs=$jobs->fetchAll();
    } else {
        $pageWarning='Job card table is missing. Please rerun the latest installer or open the Job Cards module once.';
    }
}catch(Throwable $e){
    recordSystemError($pdo,$e,['page'=>'technician-mobile','section'=>'assigned_jobs']);
    $pageWarning='Assigned jobs could not be loaded. The page has been kept online; check System Error Logs for details.';
}

try{
    if(techMobileTableOk($pdo,'products')){
        $products=$pdo->query('SELECT id,sku,name,COALESCE(NULLIF(average_cost,0), NULLIF(cost_price,0), 0) AS cost,price,stock FROM '.table('products').' WHERE active=1 ORDER BY name LIMIT 300')->fetchAll();
    }
}catch(Throwable $e){
    recordSystemError($pdo,$e,['page'=>'technician-mobile','section'=>'products']);
}

try{
    $timeEntries=$pdo->prepare('SELECT t.*,jc.job_card_number FROM '.table('mobile_time_entries').' t LEFT JOIN '.table('job_cards').' jc ON jc.id=t.job_card_id WHERE t.technician_user_id=? ORDER BY t.created_at DESC LIMIT 50');
    $timeEntries->execute([$uid]);
    $times=$timeEntries->fetchAll();
}catch(Throwable $e){
    recordSystemError($pdo,$e,['page'=>'technician-mobile','section'=>'time_entries']);
}

include dirname(__DIR__).'/header.php';
?>
<div class="mobile-workspace">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
      <div class="erp-kicker">Mobile Technician Workspace</div>
      <h2 class="h4 mb-1">Technician Mobile</h2>
      <p class="text-secondary mb-0">Fast mobile actions for assigned jobs, dispatch status, notes, timers, and parts usage.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/offline-job-cards.php">Offline Drafts</a>
  </div>

  <?php if($pageWarning): ?>
    <div class="alert alert-warning"><?php echo esc($pageWarning); ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-xl-7">
      <div class="table-wrap table-responsive">
        <h2 class="h5 mb-3">Assigned Jobs</h2>
        <table class="table align-middle">
          <thead><tr><th>Job</th><th>Status</th><th>Dispatch</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($assignedJobs as $job): ?>
            <tr>
              <td><strong><?php echo esc($job['job_card_number'] ?: ('JOB-'.$job['id'])); ?></strong><div class="small text-secondary"><?php echo esc(($job['customer_name']??'').' · '.($job['plate_number']??'')); ?></div></td>
              <td><span class="badge bg-<?php echo esc(statusTone($job['status']??'draft')); ?>"><?php echo esc($job['status']??'draft'); ?></span></td>
              <td><?php echo esc($job['dispatch_number']?:'—'); ?><div class="small text-secondary"><?php echo esc($job['dispatch_status']?:''); ?></div></td>
              <td>
                <?php if(!empty($job['dispatch_id'])): ?>
                  <form method="post" class="d-flex gap-1 mb-1">
                    <input type="hidden" name="action" value="dispatch_status">
                    <input type="hidden" name="dispatch_id" value="<?php echo (int)$job['dispatch_id']; ?>">
                    <input type="hidden" name="job_card_id" value="<?php echo (int)$job['id']; ?>">
                    <select class="form-select form-select-sm" name="dispatch_status"><option>accepted</option><option>enroute</option><option>arrived</option><option>completed</option></select>
                    <button class="btn btn-sm btn-success">Update</button>
                  </form>
                <?php endif; ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="action" value="start_timer">
                  <input type="hidden" name="job_card_id" value="<?php echo (int)$job['id']; ?>">
                  <button class="btn btn-sm btn-brand">Start Timer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$assignedJobs): ?><tr><td colspan="4" class="text-secondary">No assigned jobs found for your user. Assign a technician from Field Dispatch or Job Cards.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-xl-5">
      <form method="post" class="card-admin p-4 mb-4">
        <input type="hidden" name="action" value="note">
        <h2 class="h5 mb-3">Add Mobile Note</h2>
        <select class="form-select mb-2" name="job_card_id" required>
          <?php foreach($assignedJobs as $job): ?><option value="<?php echo (int)$job['id']; ?>"><?php echo esc($job['job_card_number'] ?: ('JOB-'.$job['id'])); ?></option><?php endforeach; ?>
        </select>
        <select class="form-select mb-2" name="note_type"><option value="progress">Progress</option><option value="diagnosis">Diagnosis</option><option value="customer">Customer update</option></select>
        <textarea class="form-control mb-3" name="note" rows="3"></textarea>
        <button class="btn btn-brand w-100" <?php echo !$assignedJobs?'disabled':''; ?>>Save Note</button>
      </form>

      <form method="post" class="card-admin p-4">
        <input type="hidden" name="action" value="part">
        <h2 class="h5 mb-3">Record Part Used</h2>
        <select class="form-select mb-2" name="job_card_id" required>
          <?php foreach($assignedJobs as $job): ?><option value="<?php echo (int)$job['id']; ?>"><?php echo esc($job['job_card_number'] ?: ('JOB-'.$job['id'])); ?></option><?php endforeach; ?>
        </select>
        <input class="form-control mb-2" name="scan_value" placeholder="Barcode / SKU scan value">
        <select class="form-select mb-2" name="product_id" required>
          <?php foreach($products as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo esc(($p['sku']??'').' · '.($p['name']??'').' · stock '.($p['stock']??0)); ?></option><?php endforeach; ?>
        </select>
        <input class="form-control mb-2" type="number" step="0.01" name="quantity" value="1">
        <textarea class="form-control mb-3" name="notes" rows="2"></textarea>
        <button class="btn btn-outline-primary w-100" <?php echo (!$assignedJobs || !$products)?'disabled':''; ?>>Add Part Usage</button>
      </form>
    </div>
  </div>

  <div class="table-wrap table-responsive mt-4">
    <h2 class="h5 mb-3">Mobile Time Entries</h2>
    <table class="table">
      <thead><tr><th>Job</th><th>Start</th><th>End</th><th>Minutes</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach($times as $t): ?>
        <tr>
          <td><?php echo esc($t['job_card_number'] ?: ('JOB-'.$t['job_card_id'])); ?></td>
          <td><?php echo esc($t['timer_start']); ?></td>
          <td><?php echo esc($t['timer_end']); ?></td>
          <td><?php echo (int)$t['duration_minutes']; ?></td>
          <td><span class="badge bg-<?php echo esc(statusTone($t['status'])); ?>"><?php echo esc($t['status']); ?></span></td>
          <td><?php if($t['status']==='running'): ?><form method="post"><input type="hidden" name="action" value="stop_timer"><input type="hidden" name="time_entry_id" value="<?php echo (int)$t['id']; ?>"><button class="btn btn-sm btn-outline-danger">Stop</button></form><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$times): ?><tr><td colspan="6" class="text-secondary">No mobile time entries yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>