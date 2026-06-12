<?php
$pageTitle='Update Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('update_center');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='add_update'){
      $stmt=$pdo->prepare('INSERT INTO '.table('system_updates').' (update_code,channel_id,version_label,release_title,release_notes,package_url,checksum,status,is_required) VALUES (?,?,?,?,?,?,?,"available",?)');
      $stmt->execute([trim((string)$_POST['update_code']),(int)$_POST['channel_id'],trim((string)$_POST['version_label']),trim((string)$_POST['release_title']),trim((string)$_POST['release_notes']),trim((string)$_POST['package_url']),trim((string)$_POST['checksum']),!empty($_POST['is_required'])?1:0]);
      flash('success','Update release added.');
    }elseif($action==='mark_installed'){
      $id=(int)$_POST['id'];$user=currentUser();
      $pdo->prepare('UPDATE '.table('system_updates').' SET status="installed",installed_by=?,installed_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,$id]);
      recordUpgradeEvent($pdo,'update_installed','completed','current','selected','Update marked installed from Update Center.');
      flash('success','Update marked installed.');
    }elseif($action==='toggle_upgrade'){
      $value=setting('upgrade_mode_enabled','0')==='1'?'0':'1';
      $pdo->prepare('UPDATE '.table('settings').' SET value=? WHERE key_name="upgrade_mode_enabled"')->execute([$value]);
      recordUpgradeEvent($pdo,'upgrade_mode',$value==='1'?'open':'completed','current','pending','Upgrade mode toggled to '.$value.'.');
      flash('success','Upgrade mode updated.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'update-center']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/update-center.php');
}
$channels=$pdo->query('SELECT * FROM '.table('update_channels').' ORDER BY channel_code')->fetchAll();
$updates=$pdo->query('SELECT su.*,uc.channel_code,u.email installed_by_email FROM '.table('system_updates').' su LEFT JOIN '.table('update_channels').' uc ON uc.id=su.channel_id LEFT JOIN '.table('users').' u ON u.id=su.installed_by ORDER BY su.created_at DESC,su.id DESC')->fetchAll();
$events=$pdo->query('SELECT ue.*,u.email created_by_email FROM '.table('upgrade_mode_events').' ue LEFT JOIN '.table('users').' u ON u.id=ue.created_by ORDER BY ue.created_at DESC LIMIT 60')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Version Governance</div><h2 class="h4 mb-1">Update Center</h2><p class="text-secondary mb-0">Track release channels, update packages, installed versions, and upgrade mode events.</p></div><form method="post"><button class="btn btn-<?php echo setting('upgrade_mode_enabled','0')==='1'?'danger':'outline-warning'; ?>" name="action" value="toggle_upgrade">Upgrade Mode: <?php echo setting('upgrade_mode_enabled','0')==='1'?'ON':'OFF'; ?></button></form></div>
<div class="row g-4">
  <div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="add_update"><div class="erp-kicker">Release Register</div><h2 class="h5 mb-3">Add Update Package</h2><label class="form-label">Update Code</label><input class="form-control mb-2" name="update_code" placeholder="P8-30-0-1" required><label class="form-label">Channel</label><select class="form-select mb-2" name="channel_id"><?php foreach($channels as $ch): ?><option value="<?php echo (int)$ch['id']; ?>"><?php echo esc($ch['channel_code'].' · '.$ch['channel_name']); ?></option><?php endforeach; ?></select><label class="form-label">Version</label><input class="form-control mb-2" name="version_label" placeholder="30.0.1"><label class="form-label">Release Title</label><input class="form-control mb-2" name="release_title"><label class="form-label">Package URL</label><input class="form-control mb-2" name="package_url"><label class="form-label">Checksum</label><input class="form-control mb-2" name="checksum"><label class="form-label">Notes</label><textarea class="form-control mb-2" rows="4" name="release_notes"></textarea><label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_required" value="1"> Required update</label><button class="btn btn-brand w-100">Add Update</button></form></div>
  <div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><div class="table-toolbar"><div><div class="erp-kicker">Update Register</div><h2 class="h5 mb-0">Available / Installed Releases</h2></div></div><table class="table align-middle"><thead><tr><th>Release</th><th>Channel</th><th>Status</th><th>Required</th><th>Installed</th><th></th></tr></thead><tbody><?php foreach($updates as $u): ?><tr><td><strong><?php echo esc($u['version_label'].' · '.$u['release_title']); ?></strong><div class="small text-secondary"><?php echo esc($u['update_code']); ?></div></td><td><?php echo esc($u['channel_code']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($u['status'])); ?>"><?php echo esc($u['status']); ?></span></td><td><?php echo !empty($u['is_required'])?'Yes':'No'; ?></td><td><?php echo esc($u['installed_at']?:'—'); ?><div class="small text-secondary"><?php echo esc($u['installed_by_email']?:''); ?></div></td><td class="text-end"><?php if($u['status']!=='installed'): ?><form method="post"><input type="hidden" name="action" value="mark_installed"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"><button class="btn btn-sm btn-outline-success">Mark Installed</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Upgrade Mode Events</h2><table class="table"><thead><tr><th>Date</th><th>Event</th><th>Status</th><th>Version</th><th>Description</th></tr></thead><tbody><?php foreach($events as $e): ?><tr><td><?php echo esc($e['created_at']); ?></td><td><?php echo esc($e['event_type']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($e['status'])); ?>"><?php echo esc($e['status']); ?></span></td><td><?php echo esc($e['from_version'].' → '.$e['to_version']); ?></td><td><?php echo esc($e['description']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>