<?php
$pageTitle='Journal Entries';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('accounting');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$filters=requestScopeFilters();
$status=trim((string)($_GET['status']??''));
$params=[];$where='';
if($status!==''){$where=' WHERE je.status=?';$params[]=$status;}
$condition=selectedScopeCondition('je',$params,$filters,['company_id','branch_id']);
$condition.=scopeQueryCondition($pdo,'je',$params,false);
if($where===''){$where=$condition!==''?' WHERE '.substr($condition,5):'';}else{$where.=$condition;}
$stmt=$pdo->prepare('SELECT je.*,u.email created_by_email FROM ' . table('journal_entries') . ' je LEFT JOIN ' . table('users') . ' u ON u.id=je.created_by'.$where.' ORDER BY je.entry_date DESC,je.id DESC');
$stmt->execute($params);
$rows=$stmt->fetchAll();
$summaryParams=[];$summaryCondition=selectedScopeCondition('je',$summaryParams,$filters,['company_id','branch_id']);$summaryCondition.=scopeQueryCondition($pdo,'je',$summaryParams,false);$summaryWhere=$summaryCondition!==''?' WHERE '.substr($summaryCondition,5):'';
$summaryStmt=$pdo->prepare('SELECT je.status,COUNT(*) total,COALESCE(SUM(je.total_debit),0) amount FROM ' . table('journal_entries') . ' je'.$summaryWhere.' GROUP BY je.status');$summaryStmt->execute($summaryParams);$summary=$summaryStmt->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4">
  <?php foreach(['draft'=>'Draft','posted'=>'Posted','reversed'=>'Reversed'] as $key=>$label): $item=$summary[$key]??['total'=>0,'amount'=>0]; ?>
    <div class="col-md-4"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc($label); ?> Journals</div><div class="metric"><?php echo (int)$item['total']; ?></div><div class="small text-secondary"><?php echo money($item['amount']); ?></div></div></div>
  <?php endforeach; ?>
</div>
<div class="card-admin p-3 mb-3"><div class="small text-secondary">Reversed journals remain reportable; their reversal entry offsets the original in all ledger reports.</div></div>
<div class="card-admin p-3 mb-3"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Company</label><select class="form-select" name="company_id"><option value="0">All</option><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)$filters['company_id']===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="0">All</option><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)$filters['branch_id']===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div><input type="hidden" name="status" value="<?php echo esc($status); ?>"><button class="btn btn-brand">Apply Scope</button></form></div>
<div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div class="d-flex flex-wrap gap-2"><a class="btn btn-sm <?php echo $status===''?'btn-brand':'btn-outline-secondary'; ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/journal-entries.php">All</a><?php foreach(['draft','posted','reversed'] as $filter): ?><a class="btn btn-sm <?php echo $status===$filter?'btn-brand':'btn-outline-secondary'; ?>" href="?status=<?php echo esc($filter); ?>&company_id=<?php echo (int)$filters['company_id']; ?>&branch_id=<?php echo (int)$filters['branch_id']; ?>"><?php echo ucfirst(esc($filter)); ?></a><?php endforeach; ?></div><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-journal-entry.php">Create Journal Entry</a></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Journal</th><th>Date</th><th>Reference</th><th>Memo</th><th>Debit</th><th>Credit</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['journal_number']); ?></strong></td><td><?php echo esc($row['entry_date']); ?></td><td><?php echo esc(($row['reference_type']?:'manual') . (!empty($row['reference_id'])?' #'.$row['reference_id']:'')); ?></td><td><?php echo esc($row['memo']); ?><div class="small text-secondary"><?php echo esc($row['created_by_email']?:'System'); ?></div></td><td><?php echo money($row['total_debit']); ?></td><td><?php echo money($row['total_credit']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc($row['status']); ?></span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-journal-entry.php?id=<?php echo (int)$row['id']; ?>">View</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="8" class="text-secondary">No journal entries found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>