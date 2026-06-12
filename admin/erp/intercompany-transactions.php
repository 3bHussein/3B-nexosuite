<?php
$pageTitle='Intercompany Transactions';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('intercompany');
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);
$status=trim((string)($_GET['status']??''));
$params=[];$parts=[];
if($status!==''){$parts[]='ict.status=?';$params[]=$status;}
$access=intercompanyAccessCondition($pdo,'ict',$params,false);
$where=$parts?' WHERE '.implode(' AND ',$parts):'';
if($where===''){$where=$access!==''?' WHERE '.substr($access,5):'';}else{$where.=$access;}

$stmt=$pdo->prepare('SELECT ict.*,fc.company_name from_company_name,tc.company_name to_company_name,fb.branch_name from_branch_name,tb.branch_name to_branch_name,st.transfer_number FROM '.table('intercompany_transactions').' ict LEFT JOIN '.table('companies').' fc ON fc.id=ict.from_company_id LEFT JOIN '.table('companies').' tc ON tc.id=ict.to_company_id LEFT JOIN '.table('branches').' fb ON fb.id=ict.from_branch_id LEFT JOIN '.table('branches').' tb ON tb.id=ict.to_branch_id LEFT JOIN '.table('stock_transfers').' st ON st.id=ict.stock_transfer_id'.$where.' ORDER BY ict.created_at DESC,ict.id DESC');
$stmt->execute($params);$rows=$stmt->fetchAll();

$inTransitValue=0.0;$recognizedValue=0.0;$settledValue=0.0;
foreach($rows as $row){
  if(($row['status']??'')==='in_transit'){$inTransitValue+=(float)$row['total_value'];}
  elseif(($row['status']??'')==='recognized'){$recognizedValue+=(float)$row['total_value'];}
  elseif(($row['status']??'')==='settled'){$settledValue+=(float)$row['total_value'];}
}
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Cross-Legal-Entity Control</div><h2 class="h4 mb-1">Intercompany Stock Value Transactions</h2><p class="text-secondary mb-0">Cross-company transfer value, recognition journals, and settlement readiness.</p></div><form class="d-flex gap-2 align-items-end"><div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach(['in_transit','recognized','settled','cancelled'] as $filter): ?><option value="<?php echo esc($filter); ?>" <?php echo $status===$filter?'selected':''; ?>><?php echo esc(str_replace('_',' ',ucwords($filter,'_'))); ?></option><?php endforeach; ?></select></div><button class="btn btn-brand">Filter</button></form></div>
<div class="row g-4 mb-4"><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Transactions</div><div class="metric"><?php echo count($rows); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">In Transit</div><div class="metric-sm"><?php echo money($inTransitValue); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Recognized</div><div class="metric-sm"><?php echo money($recognizedValue); ?></div></div></div><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Settled</div><div class="metric-sm money-positive"><?php echo money($settledValue); ?></div></div></div></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Intercompany Register</div><h2 class="h5 mb-0">Cross-Company Stock Value Flow</h2></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/branch-financial-consolidation.php"><?php echo t('Financial Consolidation', 'التجميع المالي'); ?></a></div><table class="table align-middle"><thead><tr><th>Transaction</th><th>Transfer</th><th>From → To</th><th>Value</th><th>Status</th><th>Recognition</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['transaction_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['created_at']); ?></div></td><td><?php if(!empty($row['stock_transfer_id'])): ?><a href="<?php echo esc(ADMIN_URL); ?>/erp/view-stock-transfer.php?id=<?php echo (int)$row['stock_transfer_id']; ?>"><?php echo esc($row['transfer_number']?:'Transfer #'.$row['stock_transfer_id']); ?></a><?php endif; ?></td><td><?php echo esc(($row['from_company_name']?:'—').' / '.($row['from_branch_name']?:'—')); ?><div class="small text-secondary">→ <?php echo esc(($row['to_company_name']?:'—').' / '.($row['to_branch_name']?:'—')); ?></div></td><td><?php echo money($row['total_value']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['status'],'_'))); ?></span></td><td><?php echo esc($row['recognized_at']?:'—'); ?><div class="small text-secondary">Source journal: <?php echo (int)($row['source_journal_id']??0); ?> · Destination: <?php echo (int)($row['destination_journal_id']??0); ?></div></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-intercompany-transaction.php?id=<?php echo (int)$row['id']; ?>">Open</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No intercompany transactions found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>