<?php
$pageTitle='Purchase Order Detail';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('purchase_orders');
$pdo=getDB();
$id=(int)($_GET['id']??0);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $scopeStmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('purchase_orders').' WHERE id=? LIMIT 1');$scopeStmt->execute([$id]);$postScope=$scopeStmt->fetch();
    if(!$postScope){flash('error','Purchase order not found.');redirect(ADMIN_URL.'/erp/purchase-orders.php');}
    enforceScopeAllowed($pdo,(int)($postScope['company_id']??0),(int)($postScope['branch_id']??0),(int)($postScope['warehouse_id']??0),true);
    $action=(string)($_POST['action']??'');
    if($action==='approve'){
        $active=activeApprovalRequest($pdo,'purchase_order',$id,'approve');
        if($active){throw new RuntimeException('Purchase order approval is already pending in the approval center: '.$active['request_number'].'.');}
        $request=createApprovalRequestForDocument($pdo,'purchase_order',$id,'approve','Purchase order approval request.');
        if($request){
            $stmt=$pdo->prepare('UPDATE ' . table('purchase_orders') . ' SET status="pending_approval" WHERE id=? AND status="draft"');
            $stmt->execute([$id]);
            logActivity($pdo,'Procurement','purchase_order_submitted_for_approval','Purchase order #'.$id.' submitted to approval workflow.','purchase_order',$id);
            flash('success','Purchase order submitted to approval center: '.$request['request_number'].'.');
        }else{
            $stmt=$pdo->prepare('UPDATE ' . table('purchase_orders') . ' SET status="approved",approved_at=NOW() WHERE id=? AND status="draft"');
            $stmt->execute([$id]);
            logActivity($pdo,'Procurement','purchase_order_approve','Purchase order #'.$id.' approved without workflow rule.','purchase_order',$id);
            flash('success','Purchase order approved.');
        }
    }elseif($action==='cancel'){
        $stmt=$pdo->prepare('UPDATE ' . table('purchase_orders') . ' SET status="cancelled" WHERE id=? AND status NOT IN ("received","cancelled")');
        $stmt->execute([$id]);
        logActivity($pdo,'Procurement','purchase_order_cancel','Purchase order #'.$id.' cancelled.','purchase_order',$id);
        flash('success','Purchase order cancelled when eligible.');
    }
    redirect(ADMIN_URL.'/erp/view-purchase-order.php?id='.$id);
}

$stmt=$pdo->prepare('SELECT po.*,s.supplier_code,s.contact_name,s.email,s.phone,s.address FROM ' . table('purchase_orders') . ' po LEFT JOIN ' . table('suppliers') . ' s ON s.id=po.supplier_id WHERE po.id=? LIMIT 1');
$stmt->execute([$id]);
$po=$stmt->fetch();
if(!$po){flash('error','Purchase order not found.');redirect(ADMIN_URL.'/erp/purchase-orders.php');}
try{enforceScopeAllowed($pdo,(int)($po['company_id']??0),(int)($po['branch_id']??0),(int)($po['warehouse_id']??0),false);}catch(Throwable $e){flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/purchase-orders.php');}
$itemsStmt=$pdo->prepare('SELECT poi.*,p.sku,p.name product_name FROM ' . table('purchase_order_items') . ' poi LEFT JOIN ' . table('products') . ' p ON p.id=poi.product_id WHERE poi.purchase_order_id=? ORDER BY poi.id ASC');
$itemsStmt->execute([$id]);
$items=$itemsStmt->fetchAll();
$totalQty=0;$receivedQty=0;
foreach($items as $item){$totalQty+=(float)$item['quantity'];$receivedQty+=(float)$item['received_quantity'];}
$receiptRate=$totalQty>0?min(100,round(($receivedQty/$totalQty)*100,1)):0;
$pageTitle='Purchase Order '.$po['po_number'];
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card-admin p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <div class="erp-kicker">Procurement document</div>
                    <h2 class="h3 mb-1"><?php echo esc($po['po_number']); ?></h2>
                    <div class="small-muted"><?php echo esc($po['supplier_name']); ?> · <?php echo esc($po['supplier_code'] ?? 'Supplier'); ?></div>
                </div>
                <div class="text-end"><span class="badge fs-6 bg-<?php echo esc(statusTone($po['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($po['status'],'_'))); ?></span><div class="small-muted mt-2">Expected: <?php echo esc($po['expected_date'] ?: '—'); ?> · <?php echo esc(dueLabel($po['expected_date'])); ?></div></div>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-md-4"><div class="data-card"><div class="erp-kicker">Order Date</div><div class="fw-semibold"><?php echo esc($po['order_date'] ?: '—'); ?></div></div></div>
                <div class="col-md-4"><div class="data-card"><div class="erp-kicker">Supplier Contact</div><div class="fw-semibold"><?php echo esc($po['contact_name'] ?: '—'); ?></div><div class="small-muted"><?php echo esc($po['email'] ?: ''); ?></div></div></div>
                <div class="col-md-4"><div class="data-card"><div class="erp-kicker">Receipt Progress</div><div class="fw-semibold"><?php echo esc($receiptRate); ?>%</div><div class="lead-progress mt-2"><span style="width:<?php echo esc($receiptRate); ?>%"></span></div></div></div>
                <div class="col-12"><div class="data-card"><div class="erp-kicker">Procurement Notes</div><div><?php echo nl2br(esc($po['notes'] ?: '—')); ?></div></div></div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            <h2 class="h5 mb-3">Purchase Order Items</h2>
            <table class="table"><thead><tr><th>Item</th><th>SKU</th><th>Ordered</th><th>Received</th><th>Remaining</th><th>Unit Cost</th><th>Line</th></tr></thead><tbody><?php foreach($items as $item): $remaining=max(0,(float)$item['quantity']-(float)$item['received_quantity']); ?><tr><td><?php echo esc($item['description']); ?></td><td><?php echo esc($item['sku'] ?? 'Custom'); ?></td><td><?php echo esc($item['quantity']); ?></td><td><?php echo esc($item['received_quantity']); ?></td><td><?php echo esc($remaining); ?></td><td><?php echo money($item['unit_cost']); ?></td><td><?php echo money($item['line_total']); ?></td></tr><?php endforeach; ?></tbody></table>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card-admin p-4 invoice-summary mb-4">
            <h2 class="h5">Procurement Summary</h2>
            <div class="split-stat"><span>Subtotal</span><strong><?php echo money($po['subtotal']); ?></strong></div>
            <div class="split-stat"><span>Tax</span><strong><?php echo money($po['tax']); ?></strong></div>
            <div class="split-stat"><span>Shipping / freight</span><strong><?php echo money($po['shipping']); ?></strong></div>
            <div class="split-stat h5"><span>Total</span><strong><?php echo money($po['total']); ?></strong></div>
        </div>
        <div class="card-admin p-4 mb-4">
            <h2 class="h5">Workflow Controls</h2>
            <div class="d-grid gap-2">
                <?php if($po['status']==='draft'): ?><form method="post"><button class="btn btn-brand w-100" name="action" value="approve">Submit / Approve Purchase Order</button></form><?php endif; ?>
                <?php if($po['status']==='pending_approval'): ?><?php $poApproval=activeApprovalRequest($pdo,'purchase_order',(int)$po['id'],'approve'); ?><a class="btn btn-warning w-100" href="<?php echo esc($poApproval ? ADMIN_URL.'/erp/view-approval.php?id='.(int)$poApproval['id'] : ADMIN_URL.'/erp/approvals.php'); ?>">Open Pending Approval</a><?php endif; ?>
                <?php if(in_array($po['status'],['approved','partially_received'],true)): ?><a class="btn btn-success" href="<?php echo esc(ADMIN_URL); ?>/erp/receive-purchase-order.php?id=<?php echo (int)$po['id']; ?>">Create Goods Receipt Note</a><?php endif; ?>
                <?php if(in_array($po['status'],['approved','partially_received','received'],true)): ?><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-supplier-invoice.php?po_id=<?php echo (int)$po['id']; ?>">Create Supplier Invoice</a><?php endif; ?>
                <?php if(!in_array($po['status'],['received','cancelled'],true)): ?><form method="post"><button class="btn btn-outline-danger w-100" data-confirm="Cancel this purchase order?" name="action" value="cancel">Cancel Purchase Order</button></form><?php endif; ?>
            </div>
        </div>
        <div class="card-admin p-4">
            <h2 class="h5">Navigation</h2>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/purchase-orders.php">Back to Purchase Orders</a>
                <a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-purchase-order.php">Create Another</a>
            </div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>