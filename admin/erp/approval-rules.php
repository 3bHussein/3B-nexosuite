<?php
$pageTitle='Approval Rules';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('approval_rules');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$roles=$pdo->query('SELECT slug,name FROM '.table('erp_roles').' WHERE active=1 ORDER BY name ASC')->fetchAll();
$documentTypes=['purchase_order'=>'Purchase Order','purchase_requisition'=>'Purchase Requisition','stock_transfer'=>'Stock Transfer','expense'=>'Expense','invoice'=>'Invoice','quotation'=>'Quotation','sales_order'=>'Sales Order','supplier_invoice'=>'Supplier Invoice','return_rma'=>'Return / RMA','warranty_claim'=>'Warranty Claim','project'=>'Project / Budget'];
$actionMap=['approve'=>'Approve','accept'=>'Accept','credit_override'=>'Credit Override','budget_override'=>'Budget Override'];

if(isset($_GET['toggle'])){
  $id=(int)$_GET['toggle'];
  $pdo->prepare('UPDATE '.table('approval_rules').' SET active=CASE WHEN active=1 THEN 0 ELSE 1 END WHERE id=?')->execute([$id]);
  flash('success','Approval rule status updated.');
  redirect(ADMIN_URL.'/erp/approval-rules.php');
}
$edit=null;
if(isset($_GET['edit'])){$stmt=$pdo->prepare('SELECT * FROM '.table('approval_rules').' WHERE id=? LIMIT 1');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch()?:null;}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $documentType=trim((string)($_POST['document_type']??''));
  $actionKey=trim((string)($_POST['action_key']??'approve'));
  $ruleCode=trim((string)($_POST['rule_code']??''));
  $ruleName=trim((string)($_POST['rule_name']??''));
  if(!isset($documentTypes[$documentType])||!isset($actionMap[$actionKey])||$ruleCode===''||$ruleName===''){flash('error','Rule code, rule name, document type, and action are required.');redirect(ADMIN_URL.'/erp/approval-rules.php'.($id?'?edit='.$id:''));}
  $companyId=(int)($_POST['company_id']??0)?:null;
  $branchId=(int)($_POST['branch_id']??0)?:null;
  $minAmount=max(0,(float)($_POST['min_amount']??0));
  $maxAmount=($_POST['max_amount']??'')===''?null:max(0,(float)$_POST['max_amount']);
  $minDiscount=max(0,(float)($_POST['min_discount']??0));
  $maxDiscount=($_POST['max_discount']??'')===''?null:max(0,(float)$_POST['max_discount']);
  $makerChecker=!empty($_POST['maker_checker'])?1:0;
  $active=!empty($_POST['active'])?1:0;
  $stepRoles=$_POST['step_role_slug']??[];$stepLabels=$_POST['step_label']??[];
  $steps=[];
  foreach($stepRoles as $i=>$slug){$slug=trim((string)$slug);if($slug===''){continue;}$steps[]=['label'=>trim((string)($stepLabels[$i]??('Approval Step '.($i+1)))),'slug'=>$slug];}
  if(!$steps){flash('error','Configure at least one approver role step.');redirect(ADMIN_URL.'/erp/approval-rules.php'.($id?'?edit='.$id:''));}
  $pdo->beginTransaction();
  try{
    if($id>0){
      $stmt=$pdo->prepare('UPDATE '.table('approval_rules').' SET rule_code=?,rule_name=?,document_type=?,action_key=?,company_id=?,branch_id=?,min_amount=?,max_amount=?,min_discount=?,max_discount=?,maker_checker=?,active=? WHERE id=?');
      $stmt->execute([$ruleCode,$ruleName,$documentType,$actionKey,$companyId,$branchId,$minAmount,$maxAmount,$minDiscount,$maxDiscount,$makerChecker,$active,$id]);
      $pdo->prepare('DELETE FROM '.table('approval_rule_steps').' WHERE approval_rule_id=?')->execute([$id]);
      $ruleId=$id;
      logActivity($pdo,'Approvals','approval_rule_updated','Approval rule '.$ruleCode.' updated.','approval_rule',$id);
    }else{
      $stmt=$pdo->prepare('INSERT INTO '.table('approval_rules').' (rule_code,rule_name,document_type,action_key,company_id,branch_id,min_amount,max_amount,min_discount,max_discount,maker_checker,active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
      $stmt->execute([$ruleCode,$ruleName,$documentType,$actionKey,$companyId,$branchId,$minAmount,$maxAmount,$minDiscount,$maxDiscount,$makerChecker,$active]);
      $ruleId=(int)$pdo->lastInsertId();
      logActivity($pdo,'Approvals','approval_rule_created','Approval rule '.$ruleCode.' created.','approval_rule',$ruleId);
    }
    $stepStmt=$pdo->prepare('INSERT INTO '.table('approval_rule_steps').' (approval_rule_id,step_number,step_label,approver_role_slug) VALUES (?,?,?,?)');
    foreach($steps as $i=>$step){$stepStmt->execute([$ruleId,$i+1,$step['label'],$step['slug']]);}
    $pdo->commit();flash('success','Approval rule saved.');
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/approval-rules.php');
}

$rules=$pdo->query('SELECT r.*,c.company_name,b.branch_name FROM '.table('approval_rules').' r LEFT JOIN '.table('companies').' c ON c.id=r.company_id LEFT JOIN '.table('branches').' b ON b.id=r.branch_id ORDER BY r.active DESC,r.document_type,r.min_amount DESC,r.min_discount DESC,r.id DESC')->fetchAll();
$stepMap=[];$steps=$pdo->query('SELECT * FROM '.table('approval_rule_steps').' ORDER BY approval_rule_id,step_number')->fetchAll();foreach($steps as $step){$stepMap[(int)$step['approval_rule_id']][]=$step;}
$editSteps=$edit?$stepMap[(int)$edit['id']]??[]:[];
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Policy Configuration</div><h2 class="h4 mb-1">Approval Rules & Maker-Checker Controls</h2><p class="text-secondary mb-0">Define thresholds, discount governance, sequential approver roles, and separation of duties.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/approvals.php">Approval Center</a></div>
<div class="row g-4">
<div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="id" value="<?php echo (int)($edit['id']??0); ?>"><div class="erp-kicker">Rule Editor</div><h2 class="h5 mb-3"><?php echo $edit?'Edit Rule':'Create Rule'; ?></h2><div class="row g-3">
<div class="col-md-5"><label class="form-label">Rule Code</label><input class="form-control" name="rule_code" value="<?php echo esc($edit['rule_code']??''); ?>" required></div>
<div class="col-md-7"><label class="form-label">Rule Name</label><input class="form-control" name="rule_name" value="<?php echo esc($edit['rule_name']??''); ?>" required></div>
<div class="col-md-6"><label class="form-label">Document</label><select class="form-select" name="document_type"><?php foreach($documentTypes as $key=>$label): ?><option value="<?php echo esc($key); ?>" <?php echo (($edit['document_type']??'')===$key)?'selected':''; ?>><?php echo esc($label); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Action</label><select class="form-select" name="action_key"><?php foreach($actionMap as $key=>$label): ?><option value="<?php echo esc($key); ?>" <?php echo (($edit['action_key']??'approve')===$key)?'selected':''; ?>><?php echo esc($label); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All companies</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)($edit['company_id']??0)===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All branches</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)($edit['branch_id']??0)===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Min Amount</label><input class="form-control" type="number" step="0.01" name="min_amount" value="<?php echo esc($edit['min_amount']??0); ?>"></div>
<div class="col-md-6"><label class="form-label">Max Amount</label><input class="form-control" type="number" step="0.01" name="max_amount" value="<?php echo esc($edit['max_amount']??''); ?>"></div>
<div class="col-md-6"><label class="form-label">Min Discount</label><input class="form-control" type="number" step="0.01" name="min_discount" value="<?php echo esc($edit['min_discount']??0); ?>"></div>
<div class="col-md-6"><label class="form-label">Max Discount</label><input class="form-control" type="number" step="0.01" name="max_discount" value="<?php echo esc($edit['max_discount']??''); ?>"></div>
<div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="maker_checker" value="1" <?php echo !isset($edit['maker_checker'])||!empty($edit['maker_checker'])?'checked':''; ?>><span class="form-check-label">Enable maker-checker separation</span></label></div>
<div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" value="1" <?php echo !isset($edit['active'])||!empty($edit['active'])?'checked':''; ?>><span class="form-check-label">Rule active</span></label></div>
</div><hr><div class="erp-kicker mb-2">Approval Steps</div><?php for($i=0;$i<3;$i++): $step=$editSteps[$i]??[]; ?><div class="border rounded-4 p-3 mb-2"><div class="small fw-bold mb-2">Step <?php echo $i+1; ?></div><input class="form-control mb-2" name="step_label[]" placeholder="Step label" value="<?php echo esc($step['step_label']??''); ?>"><select class="form-select" name="step_role_slug[]"><option value="">No step</option><?php foreach($roles as $role): ?><option value="<?php echo esc($role['slug']); ?>" <?php echo (($step['approver_role_slug']??'')===$role['slug'])?'selected':''; ?>><?php echo esc($role['name'].' · '.$role['slug']); ?></option><?php endforeach; ?></select></div><?php endfor; ?><div class="mt-3 d-flex gap-2"><button class="btn btn-brand"><?php echo $edit?'Save Rule':'Create Rule'; ?></button><?php if($edit): ?><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/approval-rules.php">Cancel</a><?php endif; ?></div></form></div>
<div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Configured Policies</div><h2 class="h5 mb-0">Approval Matrix</h2></div></div><table class="table align-middle"><thead><tr><th>Rule</th><th>Document / Action</th><th>Threshold</th><th>Steps</th><th>Scope</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rules as $rule): ?><tr><td><strong><?php echo esc($rule['rule_code']); ?></strong><div class="small text-secondary"><?php echo esc($rule['rule_name']); ?></div></td><td><?php echo esc(approvalDocumentLabel($rule['document_type']).' · '.approvalActionLabel($rule['action_key'])); ?></td><td>Amount ≥ <?php echo money($rule['min_amount']); ?><div class="small text-secondary">Discount ≥ <?php echo money($rule['min_discount']); ?></div></td><td><?php foreach($stepMap[(int)$rule['id']]??[] as $step): ?><div class="small"><?php echo (int)$step['step_number']; ?>. <?php echo esc(($step['step_label']?:'Approval').' · '.$step['approver_role_slug']); ?></div><?php endforeach; ?></td><td><?php echo esc(($rule['company_name']?:'All companies').' / '.($rule['branch_name']?:'All branches')); ?><div class="small text-secondary"><?php echo !empty($rule['maker_checker'])?'Maker-checker enabled':'Maker-checker disabled'; ?></div></td><td><span class="badge bg-<?php echo !empty($rule['active'])?'success':'secondary'; ?>"><?php echo !empty($rule['active'])?'Active':'Inactive'; ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int)$rule['id']; ?>">Edit</a> <a class="btn btn-sm btn-outline-secondary" href="?toggle=<?php echo (int)$rule['id']; ?>"><?php echo !empty($rule['active'])?'Disable':'Enable'; ?></a></td></tr><?php endforeach; ?><?php if(!$rules): ?><tr><td colspan="7" class="text-secondary">No approval rules configured.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>