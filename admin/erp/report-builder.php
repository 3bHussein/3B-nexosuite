<?php
$pageTitle='Report Builder';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('report_builder');
$pdo=getDB();
$reportTypes=reportTypeOptions();
$user=currentUser();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $code=trim((string)($_POST['report_code']??''));
  $name=trim((string)($_POST['report_name']??''));
  $type=trim((string)($_POST['report_type']??''));
  $visibility=in_array((string)($_POST['visibility']??'private'),['private','public'],true)?(string)$_POST['visibility']:'private';
  if($code===''||$name===''||!isset($reportTypes[$type])){flash('error','Report code, name, and valid type are required.');redirect(ADMIN_URL.'/erp/report-builder.php');}
  if($id>0){
    $stmt=$pdo->prepare('UPDATE '.table('saved_reports').' SET report_code=?,report_name=?,report_type=?,config_json=?,visibility=?,active=? WHERE id=?');
    $stmt->execute([$code,$name,$type,trim((string)($_POST['config_json']??'{}')),$visibility,!empty($_POST['active'])?1:0,$id]);
    flash('success','Saved report updated.');
  }else{
    $stmt=$pdo->prepare('INSERT INTO '.table('saved_reports').' (report_code,report_name,report_type,config_json,visibility,owner_user_id,active) VALUES (?,?,?,?,?,?,1)');
    $stmt->execute([$code,$name,$type,trim((string)($_POST['config_json']??'{}'))?:'{}',$visibility,(int)($user['id']??0)?:null]);
    flash('success','Saved report created.');
  }
  redirect(ADMIN_URL.'/erp/report-builder.php');
}
if(isset($_GET['toggle'])){
  $pdo->prepare('UPDATE '.table('saved_reports').' SET active=CASE WHEN active=1 THEN 0 ELSE 1 END WHERE id=?')->execute([(int)$_GET['toggle']]);
  flash('success','Report status changed.');redirect(ADMIN_URL.'/erp/report-builder.php');
}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM '.table('saved_reports').' WHERE id=? LIMIT 1');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:null;}
$rows=$pdo->query('SELECT sr.*,u.email owner_email FROM '.table('saved_reports').' sr LEFT JOIN '.table('users').' u ON u.id=sr.owner_user_id ORDER BY sr.active DESC,sr.report_name ASC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Analytics Factory</div><h2 class="h4 mb-1">Report Builder</h2><p class="text-secondary mb-0">Create saved operational reports and export live report output as CSV.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/executive-dashboard.php"><?php echo t('Executive BI', 'ذكاء الأعمال التنفيذي'); ?></a></div>
<div class="row g-4">
  <div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>"><div class="erp-kicker">Saved Report</div><h2 class="h5 mb-3"><?php echo $edit?'Edit Report':'Create Report'; ?></h2><label class="form-label">Report Code</label><input class="form-control mb-3" name="report_code" value="<?php echo esc($edit['report_code']??''); ?>" placeholder="RPT-CUSTOM" required><label class="form-label">Report Name</label><input class="form-control mb-3" name="report_name" value="<?php echo esc($edit['report_name']??''); ?>" required><label class="form-label">Report Type</label><select class="form-select mb-3" name="report_type"><?php foreach($reportTypes as $key=>$label): ?><option value="<?php echo esc($key); ?>" <?php echo (($edit['report_type']??'')===$key)?'selected':''; ?>><?php echo esc($label); ?></option><?php endforeach; ?></select><label class="form-label">Config JSON</label><textarea class="form-control mb-3" name="config_json" rows="4"><?php echo esc($edit['config_json']??'{}'); ?></textarea><label class="form-label">Visibility</label><select class="form-select mb-3" name="visibility"><option value="private" <?php echo (($edit['visibility']??'private')==='private')?'selected':''; ?>>Private</option><option value="public" <?php echo (($edit['visibility']??'')==='public')?'selected':''; ?>>Public</option></select><label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="active" value="1" <?php echo !isset($edit['active'])||!empty($edit['active'])?'checked':''; ?>> Active</label><button class="btn btn-brand w-100"><?php echo $edit?'Save Report':'Create Report'; ?></button></form></div>
  <div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Saved Reports</div><h2 class="h5 mb-0">Report Library</h2></div></div><table class="table align-middle"><thead><tr><th>Report</th><th>Type</th><th>Owner</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['report_code']); ?></strong><div class="small text-secondary"><?php echo esc($row['report_name']); ?></div></td><td><?php echo esc($reportTypes[$row['report_type']]??$row['report_type']); ?></td><td><?php echo esc($row['owner_email']?:'System'); ?></td><td><span class="badge bg-<?php echo !empty($row['active'])?'success':'secondary'; ?>"><?php echo !empty($row['active'])?'Active':'Inactive'; ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/run-report.php?id=<?php echo (int)$row['id']; ?>">Run</a> <a class="btn btn-sm btn-outline-dark" href="<?php echo esc(ADMIN_URL); ?>/erp/run-report.php?id=<?php echo (int)$row['id']; ?>&format=csv">CSV</a> <a class="btn btn-sm btn-outline-secondary" href="?edit=<?php echo (int)$row['id']; ?>">Edit</a> <a class="btn btn-sm btn-outline-secondary" href="?toggle=<?php echo (int)$row['id']; ?>"><?php echo !empty($row['active'])?'Disable':'Enable'; ?></a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-secondary">No saved reports configured.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>