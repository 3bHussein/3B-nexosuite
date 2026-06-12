<?php
$pageTitle = 'Orders & Website Sales';
require_once dirname(__DIR__) . '/includes/functions.php';
permissionGuard('online_sales_orders');
$pdo = getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $action = (string)($_POST['action'] ?? 'update');
    $scopeStmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('orders').' WHERE id=? LIMIT 1');$scopeStmt->execute([$orderId]);$orderScope=$scopeStmt->fetch();
    if(!$orderScope){flash('error','Order not found.');redirect(ADMIN_URL . '/orders.php');}
    try {
        enforceScopeAllowed($pdo,(int)($orderScope['company_id']??0),(int)($orderScope['branch_id']??0),(int)($orderScope['warehouse_id']??0),true);
        $pdo->beginTransaction();
        if ($action === 'create_invoice') {
            $invoiceId = createErpInvoiceFromOrder($pdo, $orderId, false);
            $pdo->commit();
            flash('success', 'ERP invoice #' . $invoiceId . ' is linked to this website order.');
            redirect(ADMIN_URL . '/orders.php');
        }
        if ($action === 'update_status') {
            $orderStatus = trim((string)($_POST['order_status'] ?? 'pending'));
            $paymentStatus = trim((string)($_POST['payment_status'] ?? 'pending'));
            $pdo->prepare('UPDATE ' . table('orders') . ' SET order_status=?, payment_status=? WHERE id=?')->execute([$orderStatus, $paymentStatus, $orderId]);
            if ($orderStatus === 'cancelled') {
                releaseOrderStock($pdo, $orderId);
            }
            $pdo->commit();
            flash('success', 'Order status updated.');
            redirect(ADMIN_URL . '/orders.php');
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Order action failed: ' . $e->getMessage());
        redirect(ADMIN_URL . '/orders.php');
    }
}

$params=[];$condition=selectedScopeCondition('o',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'o',$params,false);$where=$condition!==''?' WHERE '.substr($condition,5):'';
$sql = 'SELECT o.*, i.invoice_number, i.status AS invoice_status, COUNT(oi.id) AS line_count, COALESCE(SUM(oi.quantity),0) AS total_units
FROM ' . table('orders') . ' o
LEFT JOIN ' . table('invoices') . ' i ON i.id=o.erp_invoice_id
LEFT JOIN ' . table('order_items') . ' oi ON oi.order_id=o.id'.$where.'
GROUP BY o.id,i.invoice_number,i.status
ORDER BY o.created_at DESC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$orders=$stmt->fetchAll();
$summaryParams=[];$summaryCondition=selectedScopeCondition('o',$summaryParams,$filters,['company_id','branch_id']);$summaryCondition.=scopeQueryCondition($pdo,'o',$summaryParams,false);$summaryWhere=$summaryCondition!==''?' WHERE '.substr($summaryCondition,5):'';
$ordersStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('orders').' o'.$summaryWhere);$ordersStmt->execute($summaryParams);
$totalStmt=$pdo->prepare('SELECT COALESCE(SUM(o.total),0) FROM '.table('orders').' o'.$summaryWhere);$totalStmt->execute($summaryParams);
$processingWhere=$summaryWhere!==''?$summaryWhere.' AND o.order_status IN ("new","processing")':' WHERE o.order_status IN ("new","processing")';$processingStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('orders').' o'.$processingWhere);$processingStmt->execute($summaryParams);
$invoiceWhere=$summaryWhere!==''?$summaryWhere.' AND o.erp_invoice_id IS NOT NULL':' WHERE o.erp_invoice_id IS NOT NULL';$invoiceStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('orders').' o'.$invoiceWhere);$invoiceStmt->execute($summaryParams);
$summary = [
    'orders' => (int)$ordersStmt->fetchColumn(),
    'website_total' => (float)$totalStmt->fetchColumn(),
    'processing' => (int)$processingStmt->fetchColumn(),
    'invoices' => (int)$invoiceStmt->fetchColumn(),
];

include __DIR__ . '/header.php';
renderLicenseAdminNotice($pdo);
?>
<div class="card p-3 mb-3"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><button class="btn btn-primary">Apply Scope</button></form></div>
<div class="mb-4">
  <h1>Website Orders & Sales Linkage</h1>
  <p class="text-secondary mb-0">Orders created on the storefront can be tracked, linked to ERP invoices, and kept aligned with inventory.</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card p-3"><small class="text-secondary">Orders</small><strong class="fs-3"><?= $summary['orders'] ?></strong></div></div>
  <div class="col-md-3"><div class="card p-3"><small class="text-secondary">Website order value</small><strong class="fs-3"><?= money($summary['website_total']) ?></strong></div></div>
  <div class="col-md-3"><div class="card p-3"><small class="text-secondary">Open fulfilment</small><strong class="fs-3"><?= $summary['processing'] ?></strong></div></div>
  <div class="col-md-3"><div class="card p-3"><small class="text-secondary">ERP-linked orders</small><strong class="fs-3"><?= $summary['invoices'] ?></strong></div></div>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Items</th><th>Payment</th><th>Status</th><th>ERP Invoice</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($orders as $order): ?>
        <tr>
          <td>
            <strong><?= esc($order['order_number']) ?></strong>
            <div class="small text-secondary"><?= esc($order['created_at']) ?></div>
          </td>
          <td>
            <div><?= esc($order['customer_name']) ?></div>
            <div class="small text-secondary"><?= esc($order['customer_email']) ?></div>
          </td>
          <td><?= money($order['total']) ?></td>
          <td><?= (int)$order['line_count'] ?> lines / <?= (int)$order['total_units'] ?> units</td>
          <td><span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'secondary' ?>"><?= esc($order['payment_status']) ?></span></td>
          <td><span class="badge bg-dark"><?= esc($order['order_status']) ?></span><?php if ((int)$order['stock_released']): ?><div class="small text-danger">stock returned</div><?php endif; ?></td>
          <td>
            <?php if (!empty($order['erp_invoice_id'])): ?>
              <a class="btn btn-sm btn-outline-primary" href="erp/view-invoice.php?id=<?= (int)$order['erp_invoice_id'] ?>"><?= esc($order['invoice_number'] ?: ('Invoice #' . $order['erp_invoice_id'])) ?></a>
              <div class="small text-secondary"><?= esc($order['invoice_status'] ?: '') ?></div>
            <?php else: ?>
              <form method="post">
                <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                <input type="hidden" name="action" value="create_invoice">
                <button class="btn btn-sm btn-outline-primary" type="submit">Create ERP invoice</button>
              </form>
            <?php endif; ?>
          </td>
          <td>
            <form method="post" class="d-flex flex-column gap-2" style="min-width:180px">
              <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
              <input type="hidden" name="action" value="update_status">
              <select class="form-select form-select-sm" name="order_status">
                <?php foreach (['new','processing','fulfilled','cancelled'] as $status): ?><option value="<?= esc($status) ?>" <?= $order['order_status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?>
              </select>
              <select class="form-select form-select-sm" name="payment_status">
                <?php foreach (['pending','paid','cancelled','refunded'] as $status): ?><option value="<?= esc($status) ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-warning" type="submit">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>