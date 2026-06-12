<?php
$pageTitle='ERP Customers';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
erpGuard('customers');
$pdo = getDB();
$scopeOptions=scopeSelectOptions($pdo);
$defaultScope=operationalScope($pdo);
$filters=requestScopeFilters();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_customer') {
    requireCustomerCreationAllowed($pdo);
    $customerType = in_array(($_POST['customer_type'] ?? 'b2c'), ['b2b','b2c'], true) ? $_POST['customer_type'] : 'b2c';
    $companyId=(int)($_POST['company_id']??$defaultScope['company_id']);
    $branchId=(int)($_POST['branch_id']??$defaultScope['branch_id']);
    enforceScopeAllowed($pdo,$companyId,$branchId,0,true);
    $customerCode = nextDocumentNumber($pdo, 'customers', setting('customer_prefix','CUS'));
    $stmt = $pdo->prepare('INSERT INTO ' . table('customers') . ' (company_id,branch_id,customer_code,customer_type,company_name,contact_name,email,phone,tax_number,billing_address,shipping_address,credit_limit,payment_terms_days,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $companyId?:null,
        $branchId?:null,
        $customerCode,
        $customerType,
        trim((string)($_POST['company_name'] ?? '')),
        trim((string)($_POST['contact_name'] ?? 'New Customer')),
        trim((string)($_POST['email'] ?? '')),
        trim((string)($_POST['phone'] ?? '')),
        trim((string)($_POST['tax_number'] ?? '')),
        trim((string)($_POST['billing_address'] ?? '')),
        trim((string)($_POST['shipping_address'] ?? '')),
        (float)($_POST['credit_limit'] ?? 0),
        (int)($_POST['payment_terms_days'] ?? 0),
        trim((string)($_POST['status'] ?? 'active')) ?: 'active'
    ]);
    logActivity($pdo,'Customers','created','Customer ' . $customerCode . ' created.','customer',(int)$pdo->lastInsertId());
    flash('success','ERP customer created.');
    redirect(ADMIN_URL . '/erp/customers.php');
}

$search = trim((string)($_GET['q'] ?? ''));
$params = [];
$where = '';
if ($search !== '') {
    $where = ' WHERE (c.customer_code LIKE ? OR c.company_name LIKE ? OR c.contact_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)';
    $like = '%' . $search . '%';
    $params = [$like,$like,$like,$like,$like];
}
$condition=selectedScopeCondition('c',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'c',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt = $pdo->prepare('SELECT c.* FROM ' . table('customers') . ' c' . $where . ' ORDER BY c.created_at DESC LIMIT 200');
$stmt->execute($params);
$customers = $stmt->fetchAll();
$countParams=[];$countCondition=selectedScopeCondition('c',$countParams,$filters,['company_id','branch_id']);$countCondition.=scopeQueryCondition($pdo,'c',$countParams,false);$countWhere=$countCondition!==''?' WHERE '.substr($countCondition,5):'';
$allStmt=$pdo->prepare('SELECT COUNT(*) FROM ' . table('customers') . ' c'.$countWhere);$allStmt->execute($countParams);
$b2bParams=$countParams;$b2bWhere=$countWhere!==''?$countWhere.' AND c.customer_type="b2b"':' WHERE c.customer_type="b2b"';$b2bStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('customers').' c'.$b2bWhere);$b2bStmt->execute($b2bParams);
$b2cParams=$countParams;$b2cWhere=$countWhere!==''?$countWhere.' AND c.customer_type="b2c"':' WHERE c.customer_type="b2c"';$b2cStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('customers').' c'.$b2cWhere);$b2cStmt->execute($b2cParams);
$counts = [
    'all' => (int)$allStmt->fetchColumn(),
    'b2b' => (int)$b2bStmt->fetchColumn(),
    'b2c' => (int)$b2cStmt->fetchColumn(),
];
include dirname(__DIR__) . '/header.php';
renderLicenseAdminNotice($pdo);
?>
<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <input type="hidden" name="action" value="create_customer">
      <h2 class="h4 mb-1">Create ERP Customer</h2>
      <p class="text-secondary">Add B2B or B2C accounts for quotations, invoices, and receivables.</p>
      <div class="row g-3">
        <div class="col-12"><label class="form-label">Customer Type</label><select class="form-select" name="customer_type"><option value="b2b">B2B Company</option><option value="b2c">B2C Customer</option></select></div>
        <div class="col-12"><label class="form-label">Company</label><select class="form-select" name="company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$company['id']===(int)$defaultScope['company_id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$branch['id']===(int)$defaultScope['branch_id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Company Name</label><input class="form-control" name="company_name"></div>
        <div class="col-12"><label class="form-label">Contact Name</label><input class="form-control" name="contact_name" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
        <div class="col-12"><label class="form-label">Tax / TRN</label><input class="form-control" name="tax_number"></div>
        <div class="col-12"><label class="form-label">Billing Address</label><textarea class="form-control" name="billing_address" rows="2"></textarea></div>
        <div class="col-12"><label class="form-label">Shipping Address</label><textarea class="form-control" name="shipping_address" rows="2"></textarea></div>
        <div class="col-md-6"><label class="form-label">Credit Limit</label><input class="form-control" type="number" step="0.01" name="credit_limit" value="0"></div>
        <div class="col-md-6"><label class="form-label">Payment Terms Days</label><input class="form-control" type="number" name="payment_terms_days" value="0"></div>
        <div class="col-12"><button class="btn btn-brand w-100">Create Customer</button></div>
      </div>
    </form>
  </div>
  <div class="col-xl-8">
    <div class="card-admin p-3 mb-3"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Search</label><input class="form-control" name="q" value="<?php echo esc($search); ?>" placeholder="Customer..."></div><button class="btn btn-brand">Apply</button></form></div>
    <div class="card-admin p-4 mb-4">
      <div class="row g-3">
        <div class="col-md-4"><div class="metric-card"><span>Total Customers</span><strong><?php echo number_format($counts['all']); ?></strong></div></div>
        <div class="col-md-4"><div class="metric-card"><span>B2B Accounts</span><strong><?php echo number_format($counts['b2b']); ?></strong></div></div>
        <div class="col-md-4"><div class="metric-card"><span>B2C Accounts</span><strong><?php echo number_format($counts['b2c']); ?></strong></div></div>
      </div>
    </div>
    <div class="card-admin p-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h2 class="h4 mb-1">Customer Master</h2><p class="text-secondary mb-0">Searchable ERP customer directory.</p></div>
        <form method="get" class="d-flex gap-2"><input class="form-control" name="q" value="<?php echo esc($search); ?>" placeholder="Search customers"><button class="btn btn-outline-dark">Search</button></form>
      </div>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Customer</th><th>Type</th><th>Contact</th><th>Credit / Terms</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($customers as $customer): ?>
            <tr>
              <td><strong><?php echo esc($customer['company_name'] ?: $customer['contact_name']); ?></strong><br><small><?php echo esc($customer['customer_code']); ?></small></td>
              <td><span class="badge bg-<?php echo $customer['customer_type']==='b2b'?'primary':'secondary'; ?>"><?php echo strtoupper(esc($customer['customer_type'])); ?></span></td>
              <td><?php echo esc($customer['contact_name']); ?><br><small><?php echo esc($customer['email']); ?> <?php echo esc($customer['phone']); ?></small></td>
              <td><?php echo money($customer['credit_limit']); ?><br><small><?php echo (int)$customer['payment_terms_days']; ?> day terms</small></td>
              <td><span class="badge bg-<?php echo esc(statusTone($customer['status'])); ?>"><?php echo esc($customer['status']); ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$customers): ?><tr><td colspan="5" class="text-secondary">No customers found.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include dirname(__DIR__) . '/footer.php'; ?>