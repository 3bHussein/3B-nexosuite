<?php
$pageTitle='Invoices & Receivables';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('invoices');
$pdo=getDB();

$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();

if(isset($_GET['delete'])){
    $deleteId=(int)$_GET['delete'];
    $scopeStmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('invoices').' WHERE id=? LIMIT 1');
    $scopeStmt->execute([$deleteId]);
    $invoiceScope=$scopeStmt->fetch();

    if($invoiceScope){
        enforceScopeAllowed(
            $pdo,
            (int)($invoiceScope['company_id']??0),
            (int)($invoiceScope['branch_id']??0),
            (int)($invoiceScope['warehouse_id']??0),
            true
        );
    }

    $stmt=$pdo->prepare('DELETE FROM ' . table('invoices') . ' WHERE id=? AND status="draft"');
    $stmt->execute([$deleteId]);

    flash('success','Draft invoice deleted when eligible.');
    redirect(ADMIN_URL.'/erp/invoices.php');
}

$status=$_GET['status']??'';
$where='';
$params=[];

if($status!==''){
    $where=' WHERE i.status=?';
    $params[]=$status;
}

$condition=selectedScopeCondition('i',$params,$filters,['company_id','branch_id']);
$condition.=scopeQueryCondition($pdo,'i',$params,false);

if($where===''){
    $where=$condition!==''?' WHERE '.substr($condition,5):'';
}else{
    $where.=$condition;
}

$stmt=$pdo->prepare('SELECT i.*,c.customer_code FROM ' . table('invoices') . ' i LEFT JOIN ' . table('customers') . ' c ON c.id=i.customer_id'.$where.' ORDER BY i.created_at DESC');
$stmt->execute($params);
$rows=$stmt->fetchAll();

$totalParams=[];
$totalCondition=selectedScopeCondition('i',$totalParams,$filters,['company_id','branch_id']);
$totalCondition.=scopeQueryCondition($pdo,'i',$totalParams,false);
$totalWhere=$totalCondition!==''?' WHERE '.substr($totalCondition,5):'';

$totalStmt=$pdo->prepare('SELECT COUNT(*) invoice_count,COALESCE(SUM(i.total),0) issued_total,COALESCE(SUM(i.amount_paid),0) paid_total,COALESCE(SUM(i.balance_due),0) balance_total FROM ' . table('invoices') . ' i'.$totalWhere);
$totalStmt->execute($totalParams);
$totals=$totalStmt->fetch();

$invoiceLimitReached = function_exists('licenseTrialLimitReached') ? licenseTrialLimitReached('invoices', $pdo) : false;
$invoiceLimit = function_exists('licensePlanLimit') ? licensePlanLimit('invoices') : null;
$invoiceCount = function_exists('currentLicenseEntityCount') ? currentLicenseEntityCount('invoices', $pdo) : (int)($totals['invoice_count'] ?? 0);
$invoiceCreateUrl = ADMIN_URL . '/erp/create-invoice.php';

include dirname(__DIR__).'/header.php';
?>

<?php if($invoiceLimitReached): ?>
<div class="alert alert-danger">
  <strong>ERP invoice license limit reached.</strong>
  Current invoices: <?php echo (int)$invoiceCount; ?> / <?php echo $invoiceLimit === null ? '∞' : (int)$invoiceLimit; ?>.
  Creating a new invoice is blocked for this plan.
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Invoices</div><div class="metric"><?php echo (int)$totals['invoice_count']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Issued</div><div class="metric-sm"><?php echo money($totals['issued_total']); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Paid</div><div class="metric-sm money-positive"><?php echo money($totals['paid_total']); ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Receivable</div><div class="metric-sm money-negative"><?php echo money($totals['balance_total']); ?></div></div></div>
</div>

<div class="card-admin p-3 mb-3">
  <form class="d-flex flex-wrap gap-2 align-items-end">
    <div>
      <label class="form-label">Company</label>
      <select class="form-select" name="company_id">
        <option value="0">All</option>
        <?php foreach($scopeOptions['companies'] as $company): ?>
          <option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>>
            <?php echo esc($company['company_code'].' · '.$company['company_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="form-label">Branch</label>
      <select class="form-select" name="branch_id">
        <option value="0">All</option>
        <?php foreach($scopeOptions['branches'] as $branch): ?>
          <option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>>
            <?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <input type="hidden" name="status" value="<?php echo esc($status); ?>">
    <button class="btn btn-brand">Apply Scope</button>
  </form>
</div>

<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-sm <?php echo $status===''?'btn-brand':'btn-outline-secondary'; ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/invoices.php?company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>">All</a>
    <?php foreach(['draft','pending_approval','approved','partial','paid','cancelled'] as $filter): ?>
      <a class="btn btn-sm <?php echo $status===$filter?'btn-brand':'btn-outline-secondary'; ?>" href="?status=<?php echo esc($filter); ?>&company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>"><?php echo ucfirst(esc($filter)); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if($invoiceLimitReached): ?>
    <button class="btn btn-secondary" disabled title="ERP invoice license limit reached">
      Create Itemized Invoice
    </button>
  <?php else: ?>
    <a class="btn btn-brand" href="<?php echo esc($invoiceCreateUrl); ?>">Create Itemized Invoice</a>
  <?php endif; ?>
</div>

<div class="table-wrap table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>Invoice</th>
        <th>Customer</th>
        <th>Total</th>
        <th>Paid</th>
        <th>Balance</th>
        <th>Status</th>
        <th>Due</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $row): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?php echo esc($row['invoice_number']); ?></div>
            <div class="small-muted"><?php echo esc($row['customer_code']); ?></div>
          </td>
          <td><?php echo esc($row['customer_name']); ?> <span class="badge <?php echo $row['customer_type']==='b2b'?'badge-b2b':'badge-b2c'; ?>"><?php echo strtoupper(esc($row['customer_type'])); ?></span></td>
          <td><?php echo money($row['total']); ?></td>
          <td><?php echo money($row['amount_paid']); ?></td>
          <td><?php echo money($row['balance_due']); ?></td>
          <td><?php echo esc($row['status']); ?></td>
          <td><?php echo esc($row['due_date']); ?></td>
          <td class="text-end">
            <a class="btn btn-outline-primary btn-sm" href="<?php echo esc(ADMIN_URL); ?>/erp/view-invoice.php?id=<?php echo (int)$row['id']; ?>">View</a>
            <?php if($row['status']==='draft'): ?>
              <a class="btn btn-outline-success btn-sm" href="<?php echo esc(ADMIN_URL); ?>/erp/approve-invoice.php?id=<?php echo (int)$row['id']; ?>">Approve</a>
            <?php endif; ?>
            <a class="btn btn-outline-dark btn-sm" href="<?php echo esc(ADMIN_URL); ?>/erp/print-invoice.php?id=<?php echo (int)$row['id']; ?>" target="_blank">Print</a>
            <?php if($row['status']==='draft'): ?>
              <a data-confirm="Delete draft invoice?" class="btn btn-outline-danger btn-sm" href="?delete=<?php echo (int)$row['id']; ?>">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include dirname(__DIR__).'/footer.php'; ?>