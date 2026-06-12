<?php
$pageTitle='Dashboard Widgets';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('dashboard_widgets');
$pdo=getDB();
$roles=$pdo->query('SELECT slug,name FROM '.table('erp_roles').' WHERE active=1 ORDER BY name ASC')->fetchAll();
$defaults=dashboardWidgetDefaults();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $role=trim((string)($_POST['role_slug']??'erp-manager'));
  $pdo->beginTransaction();
  try{
    foreach($defaults as $key=>$title){
      $enabled=!empty($_POST['enabled'][$key])?1:0;
      $sort=(int)($_POST['sort_order'][$key]??0);
      $customTitle=trim((string)($_POST['title'][$key]??$title))?:$title;
      $stmt=$pdo->prepare('INSERT INTO '.table('dashboard_widget_preferences').' (role_slug,widget_key,title,sort_order,is_enabled,config_json) VALUES (?,?,?,?,?,"{}") ON DUPLICATE KEY UPDATE title=VALUES(title),sort_order=VALUES(sort_order),is_enabled=VALUES(is_enabled)');
      $stmt->execute([$role,$key,$customTitle,$sort,$enabled]);
    }
    $pdo->commit();logActivity($pdo,'Dashboard','dashboard_widgets_saved','Dashboard widget preferences saved for role '.$role.'.','dashboard_widgets',0);flash('success','Dashboard widget preferences saved.');
  }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}recordSystemError($pdo,$e,['page'=>'dashboard-widgets']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/dashboard-widgets.php?role_slug='.urlencode($role));
}
$role=trim((string)($_GET['role_slug']??'erp-manager'));
$stmt=$pdo->prepare('SELECT * FROM '.table('dashboard_widget_preferences').' WHERE role_slug=? ORDER BY sort_order ASC,widget_key ASC');$stmt->execute([$role]);$prefs=[];foreach($stmt->fetchAll() as $row){$prefs[$row['widget_key']]=$row;}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Role-Based UI</div><h2 class="h4 mb-1">Dashboard Widgets</h2><p class="text-secondary mb-0">Control which dashboard blocks appear for each role and in what order.</p></div><form class="d-flex gap-2"><select class="form-select" name="role_slug"><?php foreach($roles as $r): ?><option value="<?php echo esc($r['slug']); ?>" <?php echo $role===$r['slug']?'selected':''; ?>><?php echo esc($r['name']); ?></option><?php endforeach; ?></select><button class="btn btn-brand">Load</button></form></div>
<form method="post" class="table-wrap table-responsive"><input type="hidden" name="role_slug" value="<?php echo esc($role); ?>"><div class="table-toolbar"><div><div class="erp-kicker">Widget Matrix</div><h2 class="h5 mb-0"><?php echo esc($role); ?></h2></div><button class="btn btn-brand">Save Widget Preferences</button></div><table class="table align-middle"><thead><tr><th>Enabled</th><th>Widget</th><th>Title</th><th>Sort Order</th></tr></thead><tbody><?php $i=10; foreach($defaults as $key=>$defaultTitle): $pref=$prefs[$key]??[]; ?><tr><td><input class="form-check-input" type="checkbox" name="enabled[<?php echo esc($key); ?>]" value="1" <?php echo !isset($pref['is_enabled']) || (int)$pref['is_enabled']===1?'checked':''; ?>></td><td><strong><?php echo esc($key); ?></strong><div class="small text-secondary"><?php echo esc($defaultTitle); ?></div></td><td><input class="form-control" name="title[<?php echo esc($key); ?>]" value="<?php echo esc($pref['title']??$defaultTitle); ?>"></td><td><input class="form-control" type="number" name="sort_order[<?php echo esc($key); ?>]" value="<?php echo esc($pref['sort_order']??$i); ?>"></td></tr><?php $i+=10; endforeach; ?></tbody></table></form>
<?php include dirname(__DIR__).'/footer.php'; ?>