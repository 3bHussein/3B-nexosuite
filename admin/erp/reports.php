<?php
$pageTitle='ERP Intelligence & Reports';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('reports');
$pdo=getDB();

$lowStock=$pdo->query('SELECT p.name,i.sku,i.quantity,i.reorder_level FROM ' . table('inventory') . ' i LEFT JOIN ' . table('products') . ' p ON p.id=i.product_id WHERE i.quantity<=i.reorder_level ORDER BY i.quantity ASC LIMIT 20')->fetchAll();
$leadStatus=$pdo->query('SELECT status,COUNT(*) total,COALESCE(SUM(estimated_value),0) value_total FROM ' . table('crm_leads') . ' GROUP BY status ORDER BY total DESC')->fetchAll();
$customerType=$pdo->query('SELECT customer_type,COUNT(*) total FROM ' . table('customers') . ' GROUP BY customer_type')->fetchAll();
$quoteStatus=$pdo->query('SELECT status,COUNT(*) total,COALESCE(SUM(total),0) value_total FROM ' . table('quotations') . ' GROUP BY status ORDER BY total DESC')->fetchAll();
$poStatus=$pdo->query('SELECT status,COUNT(*) total,COALESCE(SUM(total),0) value_total FROM ' . table('purchase_orders') . ' GROUP BY status ORDER BY total DESC')->fetchAll();
$topCustomers=$pdo->query('SELECT customer_name,customer_type,COALESCE(SUM(total),0) invoice_total,COALESCE(SUM(amount_paid),0) paid_total,COALESCE(SUM(balance_due),0) balance_total FROM ' . table('invoices') . ' GROUP BY customer_name,customer_type ORDER BY invoice_total DESC LIMIT 10')->fetchAll();
$monthly=$pdo->query('SELECT DATE_FORMAT(COALESCE(paid_at,created_at),"%Y-%m") month_label,COALESCE(SUM(amount),0) total FROM ' . table('payments') . ' WHERE status="received" GROUP BY DATE_FORMAT(COALESCE(paid_at,created_at),"%Y-%m") ORDER BY month_label ASC LIMIT 12')->fetchAll();
$receivables=$pdo->query('SELECT invoice_number,customer_name,balance_due,due_date,status FROM ' . table('invoices') . ' WHERE balance_due>0 AND status<>"cancelled" ORDER BY due_date ASC LIMIT 20')->fetchAll();

$aging=['Current'=>0,'1-30 Days'=>0,'31-60 Days'=>0,'61+ Days'=>0];
foreach($receivables as $row){
    $days=daysUntil($row['due_date']);
    $amount=(float)$row['balance_due'];
    if($days===null || $days>=0){$aging['Current']+=$amount;}
    elseif($days>=-30){$aging['1-30 Days']+=$amount;}
    elseif($days>=-60){$aging['31-60 Days']+=$amount;}
    else{$aging['61+ Days']+=$amount;}
}

$months=[];$revenue=[];foreach($monthly as $row){$months[]=$row['month_label'];$revenue[]=(float)$row['total'];}
$leadLabels=[];$leadTotals=[];foreach($leadStatus as $row){$leadLabels[]=$row['status'];$leadTotals[]=(int)$row['total'];}
$typeLabels=[];$typeTotals=[];foreach($customerType as $row){$typeLabels[]=strtoupper($row['customer_type']);$typeTotals[]=(int)$row['total'];}
$quoteLabels=[];$quoteTotals=[];foreach($quoteStatus as $row){$quoteLabels[]=$row['status'];$quoteTotals[]=(float)$row['value_total'];}
$poLabels=[];$poTotals=[];foreach($poStatus as $row){$poLabels[]=str_replace('_',' ',$row['status']);$poTotals[]=(float)$row['value_total'];}
$agingLabels=array_keys($aging);$agingTotals=array_values($aging);

include dirname(__DIR__).'/header.php';
?>
<div class="erp-hero mb-4">
    <div class="hero-eyebrow">Management reporting</div>
    <div class="hero-title">Commercial intelligence across sales, receivables, quotes, procurement, and stock.</div>
    <p class="hero-copy">This report center gives a practical management view for ERP demos, internal operations, and customer-facing B2B deployments.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8"><div class="card-admin p-4 chart-card"><div class="erp-kicker"><?php echo t('Finance', 'المالية'); ?></div><h2 class="h5 mb-3">Monthly Cash Collection</h2><canvas id="monthlyCash"></canvas></div></div>
    <div class="col-xl-4"><div class="card-admin p-4 chart-card"><div class="erp-kicker">Receivables</div><h2 class="h5 mb-3">Ageing Exposure</h2><canvas id="agingChart"></canvas></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4"><div class="card-admin p-4 chart-card"><div class="erp-kicker"><?php echo t('Customers', 'العملاء'); ?></div><h2 class="h5 mb-3">B2B / B2C Mix</h2><canvas id="customerMix"></canvas></div></div>
    <div class="col-xl-4"><div class="card-admin p-4 chart-card"><div class="erp-kicker"><?php echo t('Sales Pipeline', 'مسار المبيعات'); ?></div><h2 class="h5 mb-3">Lead Stage Count</h2><canvas id="leadStatus"></canvas></div></div>
    <div class="col-xl-4"><div class="card-admin p-4 chart-card"><div class="erp-kicker">Quotation Value</div><h2 class="h5 mb-3">Quote Value by Status</h2><canvas id="quoteStatus"></canvas></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-5"><div class="card-admin p-4 chart-card"><div class="erp-kicker"><?php echo t('Procurement', 'المشتريات'); ?></div><h2 class="h5 mb-3">PO Value by Status</h2><canvas id="poStatus"></canvas></div></div>
    <div class="col-xl-7"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Revenue quality</div><h2 class="h5 mb-0">Top Customers by Invoice Value</h2></div><input class="form-control search-input" placeholder="Search customers..." data-table-search="#topCustomersTable"></div><table class="table" id="topCustomersTable"><thead><tr><th>Customer</th><th>Type</th><th>Invoices</th><th>Paid</th><th>Balance</th></tr></thead><tbody><?php foreach($topCustomers as $row): ?><tr><td><?php echo esc($row['customer_name']); ?></td><td><?php echo strtoupper(esc($row['customer_type'])); ?></td><td><?php echo money($row['invoice_total']); ?></td><td><?php echo money($row['paid_total']); ?></td><td><?php echo money($row['balance_total']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>

<div class="row g-4">
    <div class="col-xl-6"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Working capital</div><h2 class="h5 mb-0">Open Receivables</h2></div><input class="form-control search-input" placeholder="Search receivables..." data-table-search="#receivablesTable"></div><table class="table" id="receivablesTable"><thead><tr><th>Invoice</th><th>Customer</th><th>Balance</th><th>Due</th><th>Condition</th></tr></thead><tbody><?php foreach($receivables as $row): ?><tr><td><?php echo esc($row['invoice_number']); ?></td><td><?php echo esc($row['customer_name']); ?></td><td><?php echo money($row['balance_due']); ?></td><td><?php echo esc($row['due_date'] ?: '—'); ?></td><td><?php echo esc(dueLabel($row['due_date'])); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
    <div class="col-xl-6"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Warehouse health</div><h2 class="h5 mb-0">Low Stock Report</h2></div><input class="form-control search-input" placeholder="Search low stock..." data-table-search="#lowStockTable"></div><table class="table" id="lowStockTable"><thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Reorder</th></tr></thead><tbody><?php foreach($lowStock as $row): ?><tr><td><?php echo esc($row['name']); ?></td><td><?php echo esc($row['sku']); ?></td><td><?php echo (int)$row['quantity']; ?></td><td><?php echo (int)$row['reorder_level']; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('monthlyCash'),{type:'line',data:{labels:<?php echo json_encode($months); ?>,datasets:[{label:'Cash Collected',data:<?php echo json_encode($revenue); ?>,tension:.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
new Chart(document.getElementById('agingChart'),{type:'doughnut',data:{labels:<?php echo json_encode($agingLabels); ?>,datasets:[{data:<?php echo json_encode($agingTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false}});
new Chart(document.getElementById('customerMix'),{type:'doughnut',data:{labels:<?php echo json_encode($typeLabels); ?>,datasets:[{data:<?php echo json_encode($typeTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false}});
new Chart(document.getElementById('leadStatus'),{type:'bar',data:{labels:<?php echo json_encode($leadLabels); ?>,datasets:[{label:'Lead Count',data:<?php echo json_encode($leadTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
new Chart(document.getElementById('quoteStatus'),{type:'bar',data:{labels:<?php echo json_encode($quoteLabels); ?>,datasets:[{label:'Quotation Value',data:<?php echo json_encode($quoteTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
new Chart(document.getElementById('poStatus'),{type:'bar',data:{labels:<?php echo json_encode($poLabels); ?>,datasets:[{label:'PO Value',data:<?php echo json_encode($poTotals); ?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
</script>
<?php include dirname(__DIR__).'/footer.php'; ?>