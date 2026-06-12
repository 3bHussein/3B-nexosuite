<?php
$pageTitle='Mobile Checklists';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('technician_checklists');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='checklist'){
      $code=preg_replace('/[^A-Z0-9_\\-]/','',strtoupper((string)$_POST['checklist_code']));
      $pdo->prepare('INSERT INTO '.table('technician_checklists').' (checklist_code,checklist_name,job_type,status) VALUES (?,?,?,"active")')->execute([$code,trim((string)$_POST['checklist_name']),trim((string)$_POST['job_type'])]);
      flash('success','Checklist created.');
    }elseif($action==='item'){
      $pdo->prepare('INSERT INTO '.table('technician_checklist_items').' (technician_checklist_id,item_text,item_type,is_required,sort_order) VALUES (?,?,?,?,?)')->execute([(int)$_POST['checklist_id'],trim((string)$_POST['item_text']),trim((string)$_POST['item_type']),(int)($_POST['is_required']??1),(int)$_POST['sort_order']]);
      flash('success','Checklist item added.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'mobile-checklists']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/mobile-checklists.php');
}
$lists=$pdo->query('SELECT c.*,(SELECT COUNT(*) FROM '.table('technician_checklist_items').' i WHERE i.technician_checklist_id=c.id) item_count FROM '.table('technician_checklists').' c ORDER BY c.status DESC,c.checklist_name')->fetchAll();
$items=$pdo->query('SELECT i.*,c.checklist_code,c.checklist_name FROM '.table('technician_checklist_items').' i LEFT JOIN '.table('technician_checklists').' c ON c.id=i.technician_checklist_id ORDER BY c.checklist_name,i.sort_order LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Mobile SOP Control</div><h2 class="h4 mb-1">Mobile Checklists</h2><p class="text-secondary mb-0">Build required technician steps for installation, diagnosis, inspection, and field service workflows.</p></div></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4 mb-4"><input type="hidden" name="action" value="checklist"><h2 class="h5 mb-3">Create Checklist</h2><input class="form-control mb-2" name="checklist_code" placeholder="CHECKLIST-CODE"><input class="form-control mb-2" name="checklist_name" placeholder="Checklist name"><input class="form-control mb-3" name="job_type" placeholder="diagnostic / installation"><button class="btn btn-brand w-100">Create Checklist</button></form><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="item"><h2 class="h5 mb-3">Add Item</h2><select class="form-select mb-2" name="checklist_id"><?php foreach($lists as $list): ?><option value="<?php echo (int)$list['id']; ?>"><?php echo esc($list['checklist_code'].' · '.$list['checklist_name']); ?></option><?php endforeach; ?></select><input class="form-control mb-2" name="item_text" placeholder="Checklist item"><select class="form-select mb-2" name="item_type"><option value="checkbox">Checkbox</option><option value="text">Text</option><option value="photo">Photo reference</option><option value="signature">Signature</option></select><select class="form-select mb-2" name="is_required"><option value="1">Required</option><option value="0">Optional</option></select><input class="form-control mb-3" type="number" name="sort_order" value="10"><button class="btn btn-outline-primary w-100">Add Item</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive mb-4"><h2 class="h5 mb-3">Checklists</h2><table class="table"><thead><tr><th>Checklist</th><th>Type</th><th>Items</th><th>Status</th></tr></thead><tbody><?php foreach($lists as $list): ?><tr><td><strong><?php echo esc($list['checklist_code']); ?></strong><div class="small text-secondary"><?php echo esc($list['checklist_name']); ?></div></td><td><?php echo esc($list['job_type']); ?></td><td><?php echo (int)$list['item_count']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($list['status'])); ?>"><?php echo esc($list['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Checklist Items</h2><table class="table"><thead><tr><th>Checklist</th><th>Item</th><th>Type</th><th>Required</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><?php echo esc($item['checklist_code']); ?></td><td><?php echo esc($item['item_text']); ?></td><td><?php echo esc($item['item_type']); ?></td><td><?php echo (int)$item['is_required']?'Yes':'No'; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>