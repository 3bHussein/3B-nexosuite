<?php
$pageTitle='ERP Command Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('dashboard');
$pdo=getDB();

$customerCount=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('customers'))->fetchColumn();
$b2bCount=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('customers') . ' WHERE customer_type="b2b"')->fetchColumn();
$b2cCount=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('customers') . ' WHERE customer_type="b2c"')->fetchColumn();
$pipeline=(float)$pdo->query('SELECT COALESCE(SUM(estimated_value * probability / 100),0) FROM ' . table('crm_leads') . ' WHERE status NOT IN ("won","lost")')->fetchColumn();
$receivables=(float)$pdo->query('SELECT COALESCE(SUM(balance_due),0) FROM ' . table('invoices') . ' WHERE status IN ("approved","sent","partial")')->fetchColumn();
$collected=(float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM ' . table('payments') . ' WHERE status="received"')->fetchColumn();
$openInvoices=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('invoices') . ' WHERE balance_due>0 AND status<>"cancelled"')->fetchColumn();
$openQuotes=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('quotations') . ' WHERE status IN ("draft","sent","accepted") AND converted_invoice_id IS NULL')->fetchColumn();
$acceptedQuotes=(float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM ' . table('quotations') . ' WHERE status="accepted" AND converted_invoice_id IS NULL')->fetchColumn();
$pendingPO=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('purchase_orders') . ' WHERE status IN ("draft","pending_approval","approved","partially_received")')->fetchColumn();
$poValue=(float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM ' . table('purchase_orders') . ' WHERE status IN ("approved","partially_received")')->fetchColumn();
$supplierCount=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('suppliers') . ' WHERE status="active"')->fetchColumn();
$lowStock=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('inventory') . ' WHERE quantity<=reorder_level')->fetchColumn();
$pendingLeave=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('leave_requests') . ' WHERE status="pending"')->fetchColumn();
$websiteOrders=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('orders'))->fetchColumn();
$websiteSales=(float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM ' . table('orders') . ' WHERE payment_status IN ("paid","pending")')->fetchColumn();
$pendingApprovals=(int)$pdo->query('SELECT COUNT(*) FROM ' . table('approval_requests') . ' WHERE status="pending"')->fetchColumn();
$pendingApprovalValue=(float)$pdo->query('SELECT COALESCE(SUM(request_amount),0) FROM ' . table('approval_requests') . ' WHERE status="pending"')->fetchColumn();

$followups=$pdo->query('SELECT name,company,status,estimated_value,next_follow_up FROM ' . table('crm_leads') . ' WHERE next_follow_up IS NOT NULL AND status NOT IN ("won","lost") ORDER BY next_follow_up ASC LIMIT 5')->fetchAll();
$quotes=$pdo->query('SELECT id,quotation_number,customer_name,total,status,valid_until FROM ' . table('quotations') . ' WHERE status IN ("draft","sent","accepted") AND converted_invoice_id IS NULL ORDER BY valid_until ASC,created_at DESC LIMIT 5')->fetchAll();
$orders=$pdo->query('SELECT id,po_number,supplier_name,total,status,expected_date FROM ' . table('purchase_orders') . ' WHERE status IN ("draft","approved","partially_received") ORDER BY expected_date ASC,created_at DESC LIMIT 5')->fetchAll();
$stockRows=$pdo->query('SELECT p.name,i.sku,i.quantity,i.reorder_level FROM ' . table('inventory') . ' i LEFT JOIN ' . table('products') . ' p ON p.id=i.product_id WHERE i.quantity<=i.reorder_level ORDER BY i.quantity ASC LIMIT 5')->fetchAll();
$activity=$pdo->query('SELECT a.*,u.first_name,u.last_name FROM ' . table('activity_log') . ' a LEFT JOIN ' . table('users') . ' u ON u.id=a.actor_user_id ORDER BY a.created_at DESC,a.id DESC LIMIT 8')->fetchAll();
$approvalQueue=$pdo->query('SELECT ar.id,ar.request_number,ar.document_type,ar.document_number,ar.request_amount,ar.request_discount,ars.step_label,ars.approver_role_slug FROM ' . table('approval_requests') . ' ar LEFT JOIN ' . table('approval_request_steps') . ' ars ON ars.approval_request_id=ar.id AND ars.step_number=ar.current_step WHERE ar.status="pending" ORDER BY ar.submitted_at DESC,ar.id DESC LIMIT 5')->fetchAll();
$monthly=$pdo->query('SELECT DATE_FORMAT(COALESCE(paid_at,created_at),"%Y-%m") month_label,COALESCE(SUM(amount),0) total FROM ' . table('payments') . ' WHERE status="received" GROUP BY DATE_FORMAT(COALESCE(paid_at,created_at),"%Y-%m") ORDER BY month_label ASC LIMIT 12')->fetchAll();
$quoteStatus=$pdo->query('SELECT status,COUNT(*) total FROM ' . table('quotations') . ' GROUP BY status ORDER BY total DESC')->fetchAll();
$invoiceStatus=$pdo->query('SELECT status,COUNT(*) total FROM ' . table('invoices') . ' GROUP BY status ORDER BY total DESC')->fetchAll();
$leadStatus=$pdo->query('SELECT status,COUNT(*) total,COALESCE(SUM(estimated_value),0) estimated_total FROM ' . table('crm_leads') . ' GROUP BY status ORDER BY total DESC')->fetchAll();

$months=[];$revenue=[];foreach($monthly as $row){$months[]=$row['month_label'];$revenue[]=(float)$row['total'];}
$quoteLabels=[];$quoteTotals=[];foreach($quoteStatus as $row){$quoteLabels[]=$row['status'];$quoteTotals[]=(int)$row['total'];}
$invoiceLabels=[];$invoiceTotals=[];foreach($invoiceStatus as $row){$invoiceLabels[]=$row['status'];$invoiceTotals[]=(int)$row['total'];}

include dirname(__DIR__).'/header.php';
?>
<div class="erp-hero mb-4">
    <div class="hero-eyebrow">Executive operations view</div>
    <div class="hero-title">Sales, finance, procurement, and customer operations in one ERP workspace.</div>
    <p class="hero-copy">Use this command center to track B2B quotes, B2C store sales, unpaid invoices, supplier procurement, low stock, and follow-ups that need action today.</p>
    <div class="hero-action-row">
        <a class="btn btn-light" href="<?php echo esc(ADMIN_URL); ?>/erp/create-invoice.php">Create Invoice</a>
        <a class="btn btn-warning" href="<?php echo esc(ADMIN_URL); ?>/erp/create-quotation.php">Create Quotation</a>
        <a class="btn btn-outline-light" href="<?php echo esc(ADMIN_URL); ?>/erp/create-purchase-order.php">Create Purchase Order</a>
        <a class="btn btn-outline-light" href="<?php echo esc(ADMIN_URL); ?>/erp/reports.php">Open Reports</a>
    </div>
</div>

<div class="kpi-grid mb-4">
    <div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Cash Collected</div><div class="metric money-positive"><?php echo money($collected); ?></div></div><div class="stat-note">Payments recorded across ERP invoices.</div></div>
    <div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Receivables</div><div class="metric money-negative"><?php echo money($receivables); ?></div></div><div class="stat-note"><?php echo $openInvoices; ?> invoice(s) still have balance.</div></div>
    <div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Weighted Pipeline</div><div class="metric"><?php echo money($pipeline); ?></div></div><div class="stat-note">Probability-adjusted CRM opportunity value.</div></div>
    <div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Accepted Quotes</div><div class="metric"><?php echo money($acceptedQuotes); ?></div></div><div class="stat-note"><?php echo $openQuotes; ?> active quotation(s) remain open.</div></div>
    <div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Procurement Value</div><div class="metric"><?php echo money($poValue); ?></div></div><div class="stat-note"><?php echo $pendingPO; ?> purchase order(s) need monitoring.</div></div>
    <div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Low Stock Alerts</div><div class="metric <?php echo $lowStock>0?'money-negative':'money-positive'; ?>"><?php echo $lowStock; ?></div></div><div class="stat-note"><?php echo $supplierCount; ?> active supplier(s) available.</div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8"><div class="card-admin p-4 chart-card"><div class="d-flex flex-wrap justify-content-between gap-2 align-items-center mb-3"><div><div class="erp-kicker">Cash performance</div><h2 class="h5 mb-0">Monthly Collections</h2></div><span class="pill pill-soft">Website Sales: <?php echo money($websiteSales); ?></span></div><canvas id="revenueChart"></canvas></div></div>
    <div class="col-xl-4"><div class="card-admin p-4 chart-card"><div class="erp-kicker">Document control</div><h2 class="h5 mb-3">Quotation Status</h2><canvas id="quoteStatusChart"></canvas></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-7">
        <div class="table-wrap">
            <div class="table-toolbar">
                <div><div class="erp-kicker">Critical queues</div><h2 class="h5 mb-0">Actions Requiring Attention</h2></div>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/reports.php">Full reports</a>
            </div>
            <div class="row g-3">
                <div class="col-lg-6"><div class="queue-card"><div class="queue-title"><?php echo t('CRM Follow-ups', 'متابعات CRM'); ?></div><?php if(!$followups): ?><div class="small-muted">No scheduled follow-ups.</div><?php else: ?><?php foreach($followups as $lead): ?><div class="queue-row"><div><strong><?php echo esc($lead['name']); ?></strong><div class="small-muted"><?php echo esc($lead['company']); ?> · <?php echo money($lead['estimated_value']); ?></div></div><span class="pill pill-warning"><?php echo esc(dueLabel($lead['next_follow_up'])); ?></span></div><?php endforeach; ?><?php endif; ?></div></div>
                <div class="col-lg-6"><div class="queue-card"><div class="queue-title">Open Quotations</div><?php if(!$quotes): ?><div class="small-muted">No open quotations.</div><?php else: ?><?php foreach($quotes as $quote): ?><div class="queue-row"><div><a class="link-muted fw-semibold" href="<?php echo esc(ADMIN_URL); ?>/erp/view-quotation.php?id=<?php echo (int)$quote['id']; ?>"><?php echo esc($quote['quotation_number']); ?></a><div class="small-muted"><?php echo esc($quote['customer_name']); ?> · <?php echo money($quote['total']); ?></div></div><span class="pill pill-<?php echo str_contains(statusTone($quote['status']),'success')?'success':'warning'; ?>"><?php echo esc($quote['status']); ?></span></div><?php endforeach; ?><?php endif; ?></div></div>
                <div class="col-lg-6"><div class="queue-card"><div class="queue-title">Purchase Order Deliveries</div><?php if(!$orders): ?><div class="small-muted">No open purchase orders.</div><?php else: ?><?php foreach($orders as $po): ?><div class="queue-row"><div><a class="link-muted fw-semibold" href="<?php echo esc(ADMIN_URL); ?>/erp/view-purchase-order.php?id=<?php echo (int)$po['id']; ?>"><?php echo esc($po['po_number']); ?></a><div class="small-muted"><?php echo esc($po['supplier_name']); ?> · <?php echo money($po['total']); ?></div></div><span class="pill pill-warning"><?php echo esc(dueLabel($po['expected_date'])); ?></span></div><?php endforeach; ?><?php endif; ?></div></div>
                <div class="col-lg-6"><div class="queue-card"><div class="queue-title">Stock Alerts</div><?php if(!$stockRows): ?><div class="small-muted">All monitored stock levels are healthy.</div><?php else: ?><?php foreach($stockRows as $stock): ?><div class="queue-row"><div><strong><?php echo esc($stock['name']); ?></strong><div class="small-muted"><?php echo esc($stock['sku']); ?></div></div><span class="pill pill-danger"><?php echo (int)$stock['quantity']; ?> / <?php echo (int)$stock['reorder_level']; ?></span></div><?php endforeach; ?><?php endif; ?></div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="table-wrap h-100">
            <div class="table-toolbar">
                <div><div class="erp-kicker">Audit-style feed</div><h2 class="h5 mb-0">Recent ERP Activity</h2></div>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/activity-log.php">See all</a>
            </div>
            <?php if(!$activity): ?><div class="empty-state">No activity has been recorded yet.</div><?php else: ?><?php foreach($activity as $item): ?><div class="activity-item"><div class="activity-dot"></div><div><div class="activity-copy"><strong><?php echo esc($item['module']); ?></strong> — <?php echo esc($item['description']); ?></div><div class="activity-meta"><?php echo esc($item['action']); ?> · <?php echo esc(substr((string)$item['created_at'],0,16)); ?> · <?php echo esc(trim(($item['first_name']??'').' '.($item['last_name']??'')) ?: 'System'); ?></div></div></div><?php endforeach; ?><?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8"><div class="card-admin p-4 chart-card"><div class="erp-kicker">Sales operations</div><h2 class="h5 mb-3">Invoice Status Distribution</h2><canvas id="invoiceStatusChart"></canvas></div></div>
    <div class="col-xl-4"><div class="card-admin p-4"><div class="erp-kicker">Company Mix</div><h2 class="h5">B2B / B2C Footprint</h2><div class="split-stat"><span>Active Customers</span><strong><?php echo $customerCount; ?></strong></div><div class="split-stat"><span>B2B Accounts</span><strong><?php echo $b2bCount; ?></strong></div><div class="split-stat"><span>B2C Accounts</span><strong><?php echo $b2cCount; ?></strong></div><div class="split-stat"><span>Website Orders</span><strong><?php echo $websiteOrders; ?></strong></div><div class="split-stat"><span>Pending Leave</span><strong><?php echo $pendingLeave; ?></strong></div></div></div>
</div>

<div class="row g-4">
    <div class="col-xl-12"><div class="table-wrap"><div class="table-toolbar"><div><div class="erp-kicker">Pipeline control</div><h2 class="h5 mb-0">Lead Stages Snapshot</h2></div><a class="btn btn-sm btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/crm.php">Manage CRM</a></div><div class="kanban-mini"><?php foreach($leadStatus as $stage): ?><div class="kanban-lane"><div class="small-muted"><?php echo esc(ucfirst($stage['status'])); ?></div><strong><?php echo (int)$stage['total']; ?></strong><div class="small-muted"><?php echo money($stage['estimated_total']); ?></div></div><?php endforeach; ?></div></div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('revenueChart'),{type:'line',data:{labels:<?php echo json_encode($months); ?>,datasets:[{label:'Collected Revenue',data:<?php echo json_encode($revenue); ?>,tension:.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
new Chart(document.getElementById('quoteStatusChart'),{type:'doughnut',data:{labels:<?php echo json_encode($quoteLabels); ?>,datasets:[{data:<?php echo json_encode($quoteTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false}});
new Chart(document.getElementById('invoiceStatusChart'),{type:'bar',data:{labels:<?php echo json_encode($invoiceLabels); ?>,datasets:[{label:'Invoices',data:<?php echo json_encode($invoiceTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
</script>
<?php include dirname(__DIR__).'/footer.php'; ?>