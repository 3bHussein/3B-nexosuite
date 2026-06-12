<?php
$pageTitle='Stock Transfers';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('stock_transfers');
$pdo=getDB();
$status=trim((string)($_GET['status']??''));
$params=[];$where='';
if($status!==''){$where=' WHERE t.status=?';$params[]=$status;}
$accessCondition=transferAccessCondition($pdo,'t',$params,false);
if($where===''){$where=$accessCondition!==''?' WHERE '.substr($accessCondition,5):'';}else{$where.=$accessCondition;}
$stmt=$pdo->prepare('SELECT t.*,fb.branch_name from_branch_name,tb.branch_name to_branch_name,fw.warehouse_name from_warehouse_name,tw.warehouse_name to_warehouse_name,fu.email requested_by_email,au.email approved_by_email FROM '.table('stock_transfers').' t LEFT JOIN '.table('branches').' fb ON fb.id=t.from_branch_id LEFT JOIN '.table('branches').' tb ON tb.id=t.to_branch_id LEFT JOIN '.table('warehouses').' fw ON fw.id=t.from_warehouse_id LEFT JOIN '.table('warehouses').' tw ON tw.id=t.to_warehouse_id LEFT JOIN '.table('users').' fu ON fu.id=t.requested_by LEFT JOIN '.table('users').' au ON au.id=t.approved_by'.$where.' ORDER BY t.created_at DESC,t.id DESC');
$stmt->execute($params);$rows=$stmt->fetchAll();
$summary=['all'=>count($rows),'pending'=>0,'in_transit'=>0,'received'=>0];
foreach($rows as $row){
  if(($row['status']??'')==='pending_approval'){$summary['pending']++;}
  if(($row['status']??'')==='dispatched'){$summary['in_transit']++;}
  if(($row['status']??'')==='received'){$summary['received']++;}
}
include dirname(__DIR__).'/header.php';
?>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Visible Transfers</div><div class="metric"><?php echo (int)$summary['all']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Pending Approval</div><div class="metric-sm"><?php echo (int)$summary['pending']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">In Transit</div><div class="metric-sm"><?php echo (int)$summary['in_transit']; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Received</div><div class="metric-sm money-positive"><?php echo (int)$summary['received']; ?></div></div></div>
</div>
<div class="table-wrap">
  <div class="table-toolbar">
    <div class="filter-bar">
      <a class="btn btn-sm <?php echo $status===''?'btn-brand':'btn-outline-secondary'; ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/stock-transfers.php">All</a>
      <?php foreach(['draft','pending_approval','approved','dispatched','received','rejected','cancelled'] as $filter): ?><a class="btn btn-sm <?php echo $status===$filter?'btn-brand':'btn-outline-secondary'; ?>" href="?status=<?php echo esc($filter); ?>"><?php echo esc(str_replace('_',' ',ucwords($filter,'_'))); ?></a><?php endforeach; ?>
    </div>
    <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-stock-transfer.php">Create Stock Transfer</a>
  </div>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Transfer</th><th>From</th><th>To</th><th>Status</th><th>Requested</th><th>Approved</th><th></th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?php echo esc($row['transfer_number']); ?></strong><div class="small text-secondary"><?php echo esc($row['created_at']); ?></div></td><td><?php echo esc(($row['from_branch_name']?:'—').' / '.($row['from_warehouse_name']?:'—')); ?></td><td><?php echo esc(($row['to_branch_name']?:'—').' / '.($row['to_warehouse_name']?:'—')); ?></td><td><span class="badge bg-<?php echo esc(statusTone($row['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($row['status'],'_'))); ?></span></td><td><?php echo esc($row['requested_at']?:'—'); ?><div class="small text-secondary"><?php echo esc($row['requested_by_email']?:''); ?></div></td><td><?php echo esc($row['approved_at']?:'—'); ?><div class="small text-secondary"><?php echo esc($row['approved_by_email']?:''); ?></div></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/view-stock-transfer.php?id=<?php echo (int)$row['id']; ?>">Open</a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No stock transfers found for this filter.</td></tr><?php endif; ?></tbody></table></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>