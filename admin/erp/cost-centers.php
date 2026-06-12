<?php
$pageTitle='Cost Centers';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('cost_centers');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $companyId=(int)($_POST['company_id']??0)?:null;
  $branchId=(int)($_POST['branch_id']??0)?:null;
  $code=trim((string)($_POST['cost_center_code']??''));
  $name=trim((string)($_POST['cost_center_name']??''));
  $status=in_array((string)($_POST['status']??'active'),['active','inactive'],true)?(string)$_POST['status']:'active';
  if($code===''||$name===''){flash('error','Code and name are required.');redirect(ADMIN_URL.'/erp/cost-centers.php');}
  try{
    enforceScopeAllowed($pdo,(int)$companyId,(int)$branchId,0,true);
    if($id>0){
      $stmt=$pdo->prepare('UPDATE '.table('cost_centers').' SET company_id=?,branch_id=?,cost_center_code=?,cost_center_name=?,status=?,notes=? WHERE id=?');
      $stmt->execute([$companyId,$branchId,$code,$name,$status,trim((string)($_POST['notes']??'')),$id]);
      logActivity($pdo,'Cost Centers','cost_center_updated','Cost center '.$code.' updated.','cost_center',$id);
    }else{
      $stmt=$pdo->prepare('INSERT INTO '.table('cost_centers').' (company_id,branch_id,cost_center_code,cost_center_name,status,notes) VALUES (?,?,?,?,?,?)');
      $stmt->execute([$companyId,$branchId,$code,$name,$status,trim((string)($_POST['notes']??''))]);
      $id=(int)$pdo->lastInsertId();
      logActivity($pdo,'Cost Centers','cost_center_created','Cost center '.$code.' created.','cost_center',$id);
    }
    flash('success','Cost center saved.');
  }catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/cost-centers.php');
}
$rows=$pdo->query('SELECT cc.*,c.company_name,b.branch_name FROM '.table('cost_centers').' cc LEFT JOIN '.table('companies').' c ON c.id=cc.company_id LEFT JOIN '.table('branches').' b ON b.id=cc.branch_id ORDER BY cc.status DESC,cc.cost_center_code ASC')->fetchAll();
$edit=null;if(isset($_GET['edit'])){$stmt=$pdo->prepare('SELECT * FROM '.table('cost_centers').' WHERE id=? LIMIT 1');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch()?:null;}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Responsibility Accounting</div><h2 class="h4 mb-1">Cost Centers</h2><p class="text-secondary mb-0">Control budgets, service costs, project costs, and operational responsibility by department or business unit.</p></div></div>
<div class="row g-4">
<div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>"><div class="erp-kicker">Editor</div><h2 class="h5 mb-3"><?php echo $edit?'Edit Cost Center':'Create Cost Center'; ?></h2><div class="row g-3"><div class="col-md-6"><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">Global</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)($edit['company_id']??0)===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All branches</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)($edit['branch_id']??0)===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div class="col-md-5"><label class="form-label">Code</label><input class="form-control" name="cost_center_code" value="<?php echo esc($edit['cost_center_code']??''); ?>" required></div><div class="col-md-7"><label class="form-label">Name</label><input class="form-control" name="cost_center_name" value="<?php echo esc($edit['cost_center_name']??''); ?>" required></div><div class="col-12"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo ($edit['status']??'active')==='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo ($edit['status']??'')==='inactive'?'selected':''; ?>>Inactive</option></select></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" rows="3" name="notes"><?php echo esc($edit['notes']??''); ?></textarea></div></div><button class="btn btn-brand mt-3"><?php echo $edit?'Save Cost Center':'Create Cost Center'; ?></button><?php if($edit): ?><a class="btn btn-outline-secondary mt-3" href="<?php echo esc(ADMIN_URL); ?>/erp/cost-centers.php">Cancel</a><?php endif; ?></form></div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Register</div><h2 class="h5 mb-0">Cost Center Master</h2></div></div><table class="table align-middle"><thead><tr><th>Code</th><th>Name</th><th>Scope</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['cost_center_code']); ?></strong></td><td><?php echo esc($row['cost_center_name']); ?><div class="small text-secondary"><?php echo esc($row['notes']?:''); ?></div></td><td><?php echo esc(($row['company_name']?:'Global').' / '.($row['branch_name']?:'All')); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(ucfirst($row['status'])); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int)$row['id']; ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>