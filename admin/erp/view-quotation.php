<?php
$pageTitle='Quotation Detail';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('quotations');
$pdo=getDB();
$id=(int)($_GET['id']??0);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $scopeStmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('quotations').' WHERE id=? LIMIT 1');$scopeStmt->execute([$id]);$postScope=$scopeStmt->fetch();
    if(!$postScope){flash('error','Quotation not found.');redirect(ADMIN_URL.'/erp/quotations.php');}
    enforceScopeAllowed($pdo,(int)($postScope['company_id']??0),(int)($postScope['branch_id']??0),(int)($postScope['warehouse_id']??0),true);
    $status=(string)($_POST['status']??'');
    if(in_array($status,['draft','sent','accepted','rejected'],true)){
        if($status==='accepted'){
            $active=activeApprovalRequest($pdo,'quotation',$id,'accept');
            if($active){throw new RuntimeException('Quotation acceptance is already waiting in the approval center: '.$active['request_number'].'.');}
            $request=createApprovalRequestForDocument($pdo,'quotation',$id,'accept','Quotation discount / acceptance approval request.');
            if($request){
                $stmt=$pdo->prepare('UPDATE ' . table('quotations') . ' SET status="pending_approval" WHERE id=? AND converted_invoice_id IS NULL');
                $stmt->execute([$id]);
                logActivity($pdo,'Quotation','quotation_submitted_for_approval','Quotation #'.$id.' acceptance submitted to approval workflow.','quotation',$id);
                flash('success','Quotation sent to approval center: '.$request['request_number'].'.');
            }else{
                $stmt=$pdo->prepare('UPDATE ' . table('quotations') . ' SET status=? WHERE id=? AND converted_invoice_id IS NULL');
                $stmt->execute([$status,$id]);
                logActivity($pdo,'Quotation','status_change','Quotation #'.$id.' status changed to '.$status.'.','quotation',$id);
                flash('success','Quotation status updated.');
            }
        }else{
            $stmt=$pdo->prepare('UPDATE ' . table('quotations') . ' SET status=? WHERE id=? AND converted_invoice_id IS NULL');
            $stmt->execute([$status,$id]);
            logActivity($pdo,'Quotation','status_change','Quotation #'.$id.' status changed to '.$status.'.','quotation',$id);
            flash('success','Quotation status updated.');
        }
    }
    redirect(ADMIN_URL.'/erp/view-quotation.php?id='.$id);
}

$stmt=$pdo->prepare('SELECT q.*,c.customer_code FROM ' . table('quotations') . ' q LEFT JOIN ' . table('customers') . ' c ON c.id=q.customer_id WHERE q.id=? LIMIT 1');
$stmt->execute([$id]);
$quote=$stmt->fetch();
if(!$quote){flash('error','Quotation not found.');redirect(ADMIN_URL.'/erp/quotations.php');}
try{enforceScopeAllowed($pdo,(int)($quote['company_id']??0),(int)($quote['branch_id']??0),(int)($quote['warehouse_id']??0),false);}catch(Throwable $e){flash('error',$e->getMessage());redirect(ADMIN_URL.'/erp/quotations.php');}
$itemsStmt=$pdo->prepare('SELECT qi.*,p.sku FROM ' . table('quotation_items') . ' qi LEFT JOIN ' . table('products') . ' p ON p.id=qi.product_id WHERE qi.quotation_id=? ORDER BY qi.id ASC');
$itemsStmt->execute([$id]);
$items=$itemsStmt->fetchAll();
$pageTitle='Quotation '.$quote['quotation_number'];
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card-admin p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <div class="erp-kicker">Sales quotation</div>
                    <h2 class="h3 mb-1"><?php echo esc($quote['quotation_number']); ?></h2>
                    <div class="small-muted"><?php echo esc($quote['customer_name']); ?> · <?php echo strtoupper(esc($quote['customer_type'])); ?> · <?php echo esc($quote['customer_code'] ?? 'Manual customer'); ?></div>
                </div>
                <div class="text-end"><span class="badge fs-6 bg-<?php echo esc(statusTone($quote['status'])); ?>"><?php echo esc(ucfirst($quote['status'])); ?></span><div class="small-muted mt-2">Valid until: <?php echo esc($quote['valid_until'] ?: '—'); ?></div></div>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-md-6"><div class="data-card"><div class="erp-kicker">Customer email</div><div class="fw-semibold"><?php echo esc($quote['customer_email'] ?: '—'); ?></div></div></div>
                <div class="col-md-6"><div class="data-card"><div class="erp-kicker">Validity</div><div class="fw-semibold"><?php echo esc(dueLabel($quote['valid_until'])); ?></div></div></div>
                <div class="col-12"><div class="data-card"><div class="erp-kicker">Billing / proposal address</div><div><?php echo nl2br(esc($quote['billing_address'] ?: '—')); ?></div></div></div>
                <div class="col-12"><div class="data-card"><div class="erp-kicker">Commercial notes</div><div><?php echo nl2br(esc($quote['notes'] ?: '—')); ?></div></div></div>
            </div>
        </div>

        <div class="table-wrap table-responsive">
            <h2 class="h5 mb-3">Quoted Items</h2>
            <table class="table">
                <thead><tr><th>Item</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th>Line Total</th></tr></thead>
                <tbody><?php foreach($items as $item): ?><tr><td><?php echo esc($item['description']); ?></td><td><?php echo esc($item['sku'] ?? 'Custom'); ?></td><td><?php echo esc($item['quantity']); ?></td><td><?php echo money($item['unit_price']); ?></td><td><?php echo esc($item['tax_rate']); ?>%</td><td><?php echo money($item['line_total']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card-admin p-4 invoice-summary mb-4">
            <h2 class="h5">Commercial Summary</h2>
            <div class="split-stat"><span>Subtotal</span><strong><?php echo money($quote['subtotal']); ?></strong></div>
            <div class="split-stat"><span>Discount</span><strong><?php echo money($quote['discount']); ?></strong></div>
            <div class="split-stat"><span>Tax</span><strong><?php echo money($quote['tax']); ?></strong></div>
            <div class="split-stat"><span>Shipping / charges</span><strong><?php echo money($quote['shipping']); ?></strong></div>
            <div class="split-stat h5"><span>Total</span><strong><?php echo money($quote['total']); ?></strong></div>
        </div>

        <div class="card-admin p-4 mb-4">
            <h2 class="h5">Workflow Controls</h2>
            <?php if(empty($quote['converted_invoice_id'])): ?>
                <form method="post" class="d-grid gap-2">
                    <button class="btn btn-outline-secondary" name="status" value="draft">Mark Draft</button>
                    <button class="btn btn-outline-primary" name="status" value="sent">Mark Sent</button>
                    <button class="btn btn-success" name="status" value="accepted">Mark Accepted</button>
                    <button class="btn btn-outline-danger" name="status" value="rejected">Mark Rejected</button>
                </form>
                <?php if($quote['status']==='accepted'): ?><a class="btn btn-brand w-100 mt-3" href="<?php echo esc(ADMIN_URL); ?>/erp/convert-quotation.php?id=<?php echo (int)$quote['id']; ?>">Convert to Invoice</a><?php endif; ?>
            <?php else: ?>
                <div class="alert alert-success mb-0">Converted to invoice. <a href="<?php echo esc(ADMIN_URL); ?>/erp/view-invoice.php?id=<?php echo (int)$quote['converted_invoice_id']; ?>">Open invoice</a>.</div>
            <?php endif; ?>
        </div>

        <div class="card-admin p-4">
            <h2 class="h5">Navigation</h2>
            <div class="d-grid gap-2">
                <a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/quotations.php">Back to Quotations</a>
                <a class="btn btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-quotation.php">Create Another</a>
            </div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>