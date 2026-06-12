<?php
$pageTitle='Chart of Accounts';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();

$editId=(int)($_GET['edit']??0);
$editAccount=null;
if($editId>0){
  $stmt=$pdo->prepare('SELECT * FROM ' . table('accounts') . ' WHERE id=? LIMIT 1');
  $stmt->execute([$editId]);
  $editAccount=$stmt->fetch() ?: null;
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='account'){
  $id=(int)($_POST['id']??0);
  $code=trim((string)($_POST['account_code']??''));
  $name=trim((string)($_POST['account_name']??''));
  $type=trim((string)($_POST['account_type']??'asset'));
  $normal=trim((string)($_POST['normal_balance']??'debit'));
  if($code==='' || $name===''){flash('error','Account code and name are required.');redirect(ADMIN_URL.'/erp/chart-of-accounts.php'.($id?'?edit='.$id:''));}
  if(!in_array($type,['asset','liability','equity','revenue','expense'],true)){$type='asset';}
  if(!in_array($normal,['debit','credit'],true)){$normal='debit';}
  if($id>0){
    $stmt=$pdo->prepare('UPDATE ' . table('accounts') . ' SET account_code=?,account_name=?,account_type=?,normal_balance=?,parent_id=?,description=?,active=? WHERE id=?');
    $stmt->execute([$code,$name,$type,$normal,(int)($_POST['parent_id']??0) ?: null,trim((string)($_POST['description']??'')),!empty($_POST['active'])?1:0,$id]);
    logActivity($pdo,'Accounting','account_updated','Account '.$code.' updated.','account',$id);
    flash('success','Account updated.');
  }else{
    $stmt=$pdo->prepare('INSERT INTO ' . table('accounts') . ' (account_code,account_name,account_type,normal_balance,parent_id,description,active) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$code,$name,$type,$normal,(int)($_POST['parent_id']??0) ?: null,trim((string)($_POST['description']??'')),!empty($_POST['active'])?1:0]);
    $newId=(int)$pdo->lastInsertId();
    logActivity($pdo,'Accounting','account_created','Account '.$code.' created.','account',$newId);
    flash('success','Account created.');
  }
  redirect(ADMIN_URL.'/erp/chart-of-accounts.php');
}
if(isset($_GET['toggle'])){
  $id=(int)$_GET['toggle'];
  $stmt=$pdo->prepare('UPDATE ' . table('accounts') . ' SET active=CASE WHEN active=1 THEN 0 ELSE 1 END WHERE id=?');
  $stmt->execute([$id]);
  logActivity($pdo,'Accounting','account_status_toggled','Account active status toggled.','account',$id);
  flash('success','Account status updated.');
  redirect(ADMIN_URL.'/erp/chart-of-accounts.php');
}
$accounts=$pdo->query('SELECT a.*,p.account_code AS parent_code,p.account_name AS parent_name FROM ' . table('accounts') . ' a LEFT JOIN ' . table('accounts') . ' p ON p.id=a.parent_id ORDER BY a.account_code ASC')->fetchAll();
$accountOptions=$pdo->query('SELECT id,account_code,account_name FROM ' . table('accounts') . ' ORDER BY account_code ASC')->fetchAll();
$summary=$pdo->query('SELECT account_type,COUNT(*) total FROM ' . table('accounts') . ' GROUP BY account_type')->fetchAll(PDO::FETCH_KEY_PAIR);
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4">
  <?php foreach(['asset'=>'Assets','liability'=>'Liabilities','equity'=>'Equity','revenue'=>'Revenue','expense'=>'Expenses'] as $key=>$label): ?>
    <div class="col-md-4 col-xl"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc($label); ?></div><div class="metric"><?php echo (int)($summary[$key]??0); ?></div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <input type="hidden" name="form_type" value="account">
      <input type="hidden" name="id" value="<?php echo (int)($editAccount['id']??0); ?>">
      <h2 class="h5 mb-1"><?php echo $editAccount?'Edit Account':'Create Account'; ?></h2>
      <p class="text-secondary">Maintain the accounting chart used by journals and financial statements.</p>
      <div class="row g-3">
        <div class="col-md-5"><label class="form-label">Code</label><input class="form-control" name="account_code" value="<?php echo esc($editAccount['account_code']??''); ?>" required></div>
        <div class="col-md-7"><label class="form-label">Name</label><input class="form-control" name="account_name" value="<?php echo esc($editAccount['account_name']??''); ?>" required></div>
        <div class="col-md-6"><label class="form-label">Type</label><select class="form-select" name="account_type"><?php foreach(['asset','liability','equity','revenue','expense'] as $type): ?><option value="<?php echo esc($type); ?>" <?php echo (($editAccount['account_type']??'asset')===$type)?'selected':''; ?>><?php echo ucfirst(esc($type)); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Normal Balance</label><select class="form-select" name="normal_balance"><?php foreach(['debit','credit'] as $normal): ?><option value="<?php echo esc($normal); ?>" <?php echo (($editAccount['normal_balance']??'debit')===$normal)?'selected':''; ?>><?php echo ucfirst(esc($normal)); ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Parent account</label><select class="form-select" name="parent_id"><option value="0">No parent</option><?php foreach($accountOptions as $option): ?><option value="<?php echo (int)$option['id']; ?>" <?php echo (int)($editAccount['parent_id']??0)===(int)$option['id']?'selected':''; ?>><?php echo esc($option['account_code'].' · '.$option['account_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?php echo esc($editAccount['description']??''); ?></textarea></div>
        <div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="active" value="1" <?php echo !isset($editAccount['active']) || !empty($editAccount['active'])?'checked':''; ?>><span class="form-check-label">Account active</span></label></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-brand"><?php echo $editAccount?'Save Account':'Create Account'; ?></button><?php if($editAccount): ?><a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/chart-of-accounts.php">Cancel</a><?php endif; ?></div>
      </div>
    </form>
  </div>
  <div class="col-xl-8">
    <div class="table-wrap table-responsive">
      <div class="table-toolbar"><div><div class="erp-kicker">Accounting Master Data</div><h2 class="h5 mb-0">Chart of Accounts</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-journal-entry.php">New Journal Entry</a></div>
      <table class="table align-middle">
        <thead><tr><th>Code</th><th>Account</th><th>Type</th><th>Normal</th><th>Parent</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach($accounts as $account): ?>
          <tr>
            <td><strong><?php echo esc($account['account_code']); ?></strong></td>
            <td><?php echo esc($account['account_name']); ?><div class="small text-secondary"><?php echo esc($account['description']); ?></div></td>
            <td><span class="badge bg-secondary"><?php echo esc(ucfirst($account['account_type'])); ?></span></td>
            <td><?php echo esc(ucfirst($account['normal_balance'])); ?></td>
            <td><?php echo esc(trim(($account['parent_code']??'').' '.($account['parent_name']??'')) ?: '—'); ?></td>
            <td><span class="badge bg-<?php echo !empty($account['active'])?'success':'danger'; ?>"><?php echo !empty($account['active'])?'Active':'Inactive'; ?></span></td>
            <td class="text-end"><div class="d-flex gap-1 justify-content-end flex-wrap"><a class="btn btn-sm btn-outline-info" href="<?php echo esc(ADMIN_URL); ?>/erp/account-ledger.php?account_id=<?php echo (int)$account['id']; ?>">Ledger</a><a class="btn btn-sm btn-outline-primary" href="?edit=<?php echo (int)$account['id']; ?>">Edit</a><a class="btn btn-sm btn-outline-dark" href="?toggle=<?php echo (int)$account['id']; ?>">Toggle</a></div></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$accounts): ?><tr><td colspan="7" class="text-secondary">No accounts created.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>