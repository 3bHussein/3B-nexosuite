<?php
$pageTitle='Bank Accounts';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$cashAccounts=$pdo->query('SELECT id,account_code,account_name FROM ' . table('accounts') . ' WHERE account_type="asset" ORDER BY account_code ASC')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $stmt=$pdo->prepare('INSERT INTO ' . table('bank_accounts') . ' (account_name,bank_name,account_number,currency,cash_account_id,opening_balance,active) VALUES (?,?,?,?,?,?,?)');
  $stmt->execute([trim((string)($_POST['account_name']??'')),trim((string)($_POST['bank_name']??'')),trim((string)($_POST['account_number']??'')),trim((string)($_POST['currency']??'AED')),(int)($_POST['cash_account_id']??0) ?: null,(float)($_POST['opening_balance']??0),!empty($_POST['active'])?1:0]);
  logActivity($pdo,'Accounting','bank_account_created','Bank account created for reconciliation.','bank_account',(int)$pdo->lastInsertId());
  flash('success','Bank account created.');
  redirect(ADMIN_URL.'/erp/bank-accounts.php');
}
$rows=$pdo->query('SELECT b.*,a.account_code,a.account_name cash_gl_account FROM ' . table('bank_accounts') . ' b LEFT JOIN ' . table('accounts') . ' a ON a.id=b.cash_account_id ORDER BY b.created_at DESC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
  <div class="col-xl-4"><form method="post" class="card-admin p-4"><div class="erp-kicker">Bank Master</div><h2 class="h5 mb-3">Add Bank Account</h2><div class="mb-3"><label class="form-label">Account label</label><input class="form-control" name="account_name" required></div><div class="row g-2"><div class="col-md-6 mb-3"><label class="form-label">Bank</label><input class="form-control" name="bank_name"></div><div class="col-md-6 mb-3"><label class="form-label">Currency</label><input class="form-control" name="currency" value="<?php echo esc(CURRENCY); ?>"></div></div><div class="mb-3"><label class="form-label">Account number</label><input class="form-control" name="account_number"></div><div class="mb-3"><label class="form-label">Cash GL account</label><select class="form-select" name="cash_account_id"><option value="0">Select GL account</option><?php foreach($cashAccounts as $account): ?><option value="<?php echo (int)$account['id']; ?>"><?php echo esc($account['account_code'].' · '.$account['account_name']); ?></option><?php endforeach; ?></select></div><div class="mb-3"><label class="form-label">Opening bank balance</label><input class="form-control" type="number" step="0.01" name="opening_balance" value="0"></div><label class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="active" value="1" checked><span class="form-check-label">Active</span></label><button class="btn btn-brand">Create Bank Account</button></form></div>
  <div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Reconciliation Foundation</div><h2 class="h5 mb-0">Bank Accounts</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/bank-reconciliation.php">Open Reconciliation</a></div><table class="table align-middle"><thead><tr><th>Bank Account</th><th>GL Link</th><th>Currency</th><th>Opening Balance</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['account_name']); ?></strong><div class="small text-secondary"><?php echo esc($row['bank_name'].' · '.$row['account_number']); ?></div></td><td><?php echo esc(($row['account_code']?$row['account_code'].' · ':'').$row['cash_gl_account']); ?></td><td><?php echo esc($row['currency']); ?></td><td><?php echo money($row['opening_balance']); ?></td><td><span class="badge bg-<?php echo !empty($row['active'])?'success':'secondary'; ?>"><?php echo !empty($row['active'])?'Active':'Inactive'; ?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>