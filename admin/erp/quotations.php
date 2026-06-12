<?php
$pageTitle='Quotations & Sales Proposals';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('quotations');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();

if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    $scopeStmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('quotations').' WHERE id=? LIMIT 1');$scopeStmt->execute([$id]);$quoteScope=$scopeStmt->fetch();
    if($quoteScope){enforceScopeAllowed($pdo,(int)($quoteScope['company_id']??0),(int)($quoteScope['branch_id']??0),(int)($quoteScope['warehouse_id']??0),true);}
    $stmt=$pdo->prepare('DELETE FROM ' . table('quotations') . ' WHERE id=? AND status="draft" AND converted_invoice_id IS NULL');
    $stmt->execute([$id]);
    flash('success','Draft quotation deleted when eligible.');
    redirect(ADMIN_URL.'/erp/quotations.php');
}
$status=trim((string)($_GET['status']??''));
$where='';$params=[];
if($status!==''){$where=' WHERE q.status=?';$params[]=$status;}
$condition=selectedScopeCondition('q',$params,$filters,['company_id','branch_id']);$condition.=scopeQueryCondition($pdo,'q',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt=$pdo->prepare('SELECT q.*,c.customer_code FROM ' . table('quotations') . ' q LEFT JOIN ' . table('customers') . ' c ON c.id=q.customer_id'.$where.' ORDER BY q.created_at DESC,q.id DESC');
$stmt->execute($params);
$rows=$stmt->fetchAll();

$totalParams=[];$totalCondition=selectedScopeCondition('q',$totalParams,$filters,['company_id','branch_id']);$totalCondition.=scopeQueryCondition($pdo,'q',$totalParams,false);$totalWhere=$totalCondition!==''?' WHERE '.substr($totalCondition,5):'';
$totalStmt=$pdo->prepare('SELECT COUNT(*) quotation_count,COALESCE(SUM(q.total),0) quoted_total,COALESCE(SUM(CASE WHEN q.status="accepted" AND q.converted_invoice_id IS NULL THEN q.total ELSE 0 END),0) accepted_total,COALESCE(SUM(CASE WHEN q.converted_invoice_id IS NOT NULL THEN q.total ELSE 0 END),0) converted_total FROM ' . table('quotations') . ' q'.$totalWhere);$totalStmt->execute($totalParams);$totals=$totalStmt->fetch();
$expiryParams=[];$expiryCondition=selectedScopeCondition('q',$expiryParams,$filters,['company_id','branch_id']);$expiryCondition.=scopeQueryCondition($pdo,'q',$expiryParams,false);$expiryWhere=' WHERE q.valid_until IS NOT NULL AND q.valid_until < CURDATE() AND q.status NOT IN ("converted","rejected")'.$expiryCondition;$expiryStmt=$pdo->prepare('SELECT COUNT(*) FROM ' . table('quotations') . ' q'.$expiryWhere);$expiryStmt->execute($expiryParams);$expiry=(int)$expiryStmt->fetchColumn();

include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Total Quotations</div><div class="metric"><?php echo (int)$totals['quotation_count']; ?></div></div><div class="stat-note">Sales proposals created.</div></div></div>
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Quoted Value</div><div class="metric-sm"><?php echo money($totals['quoted_total']); ?></div></div><div class="stat-note">Total commercial proposal value.</div></div></div>
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Accepted / Not Invoiced</div><div class="metric-sm money-positive"><?php echo money($totals['accepted_total']); ?></div></div><div class="stat-note">Ready for invoice conversion.</div></div></div>
    <div class="col-md-3"><div class="card-admin erp-stat p-4"><div><div class="erp-kicker">Expired Follow-up</div><div class="metric-sm <?php echo $expiry>0?'money-negative':'money-positive'; ?>"><?php echo $expiry; ?></div></div><div class="stat-note">Quotations beyond validity date.</div></div></div>
</div>

<div class="table-wrap">
    <div class="card-admin p-3 mb-3"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><input type="hidden" name="status" value="<?php echo esc($status); ?>"><button class="btn btn-brand">Apply Scope</button></form></div>
    <div class="table-toolbar">
        <div class="filter-bar">
            <input class="form-control search-input" placeholder="Search quotation, customer, value..." data-table-search="#quotationsTable">
            <a class="btn btn-sm <?php echo $status===''?'btn-brand':'btn-outline-secondary'; ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/quotations.php?company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>">All</a>
            <?php foreach(['draft','sent','pending_approval','accepted','rejected','converted'] as $filter): ?><a class="btn btn-sm <?php echo $status===$filter?'btn-brand':'btn-outline-secondary'; ?>" href="?status=<?php echo esc($filter); ?>&company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>"><?php echo esc(ucfirst($filter)); ?></a><?php endforeach; ?>
        </div>
        <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-quotation.php">Create Quotation</a>
    </div>
    <div class="table-responsive">
        <table class="table" id="quotationsTable">
            <thead><tr><th>Quotation</th><th>Customer</th><th>Total</th><th>Status</th><th>Validity</th><th>Invoice Link</th><th></th></tr></thead>
            <tbody>
            <?php foreach($rows as $row): ?>
                <tr>
                    <td><div class="fw-semibold"><?php echo esc($row['quotation_number']); ?></div><div class="small-muted"><?php echo esc($row['customer_code'] ?? 'Manual customer'); ?></div></td>
                    <td><?php echo esc($row['customer_name']); ?> <span class="badge <?php echo $row['customer_type']==='b2b'?'badge-b2b':'badge-b2c'; ?>"><?php echo strtoupper(esc($row['customer_type'])); ?></span></td>
                    <td><?php echo money($row['total']); ?></td>
                    <td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(ucfirst($row['status'])); ?></span></td>
                    <td><div><?php echo esc($row['valid_until'] ?: '—'); ?></div><div class="small-muted"><?php echo esc(dueLabel($row['valid_until'])); ?></div></td>
                    <td><?php echo !empty($row['converted_invoice_id']) ? '<a href="'.esc(ADMIN_URL).'/erp/view-invoice.php?id='.(int)$row['converted_invoice_id'].'">Invoice #'.(int)$row['converted_invoice_id'].'</a>' : '<span class="small-muted">Not converted</span>'; ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-quotation.php?id=<?php echo (int)$row['id']; ?>">View</a> <?php if($row['status']==='draft' && empty($row['converted_invoice_id'])): ?><a class="btn btn-sm btn-outline-danger" data-confirm="Delete this draft quotation?" href="?delete=<?php echo (int)$row['id']; ?>">Delete</a><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>