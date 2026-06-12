<?php
$pageTitle='Customer Portal Requests';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('customer_portal_admin');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $id=(int)$_POST['id'];$status=trim((string)($_POST['status']??'open'));$jobCard=(int)($_POST['job_card_id']??0)?:null;
    $pdo->prepare('UPDATE '.table('customer_service_requests').' SET status=?,job_card_id=? WHERE id=?')->execute([$status,$jobCard,$id]);
    logActivity($pdo,'Customer Portal','service_request_updated','Customer service request #'.$id.' updated.','customer_service_request',$id);
    flash('success','Service request updated.');
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'customer-portal-requests']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/customer-portal-requests.php');
}
$status=trim((string)($_GET['status']??''));
$params=[];$where='';
if($status!==''){$where=' WHERE csr.status=?';$params[]=$status;}
$stmt=$pdo->prepare('SELECT csr.*,u.email user_email,ca.asset_name,jc.job_card_number FROM '.table('customer_service_requests').' csr LEFT JOIN '.table('users').' u ON u.id=csr.user_id LEFT JOIN '.table('customer_assets').' ca ON ca.id=csr.customer_asset_id LEFT JOIN '.table('job_cards').' jc ON jc.id=csr.job_card_id'.$where.' ORDER BY csr.created_at DESC LIMIT 250');$stmt->execute($params);$rows=$stmt->fetchAll();
$jobs=$pdo->query('SELECT id,job_card_number,customer_name FROM '.table('job_cards').' WHERE status IN ("draft","diagnosis","in_progress","waiting_parts") ORDER BY created_at DESC LIMIT 300')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Customer Portal Admin</div><h2 class="h4 mb-1">Service Requests</h2><p class="text-secondary mb-0">Review customer-submitted service requests and link them to workshop job cards.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/create-job-card.php">Create Job Card</a></div>
<div class="card-admin p-3 mb-4"><form class="d-flex gap-2"><select class="form-select" name="status"><option value="">All</option><?php foreach(['open','reviewing','scheduled','in_progress','completed','cancelled'] as $s): ?><option value="<?php echo esc($s); ?>" <?php echo $status===$s?'selected':''; ?>><?php echo esc(ucwords(str_replace('_',' ',$s))); ?></option><?php endforeach; ?></select><button class="btn btn-brand">Filter</button></form></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Request</th><th>Customer</th><th>Asset</th><th>Preferred</th><th>Status</th><th>Link Job Card</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['request_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['subject']); ?></div><div class="small"><?php echo esc($r['description']); ?></div></td><td><?php echo esc($r['user_email']?:'Customer'); ?></td><td><?php echo esc($r['asset_name']?:'General'); ?></td><td><?php echo esc(($r['preferred_date']?:'').' '.($r['preferred_time']?:'')); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td><td><form method="post" class="d-flex flex-column gap-2"><input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>"><select class="form-select form-select-sm" name="status"><?php foreach(['open','reviewing','scheduled','in_progress','completed','cancelled'] as $s): ?><option value="<?php echo esc($s); ?>" <?php echo $r['status']===$s?'selected':''; ?>><?php echo esc($s); ?></option><?php endforeach; ?></select><select class="form-select form-select-sm" name="job_card_id"><option value="0">No job card</option><?php foreach($jobs as $j): ?><option value="<?php echo (int)$j['id']; ?>" <?php echo (int)$r['job_card_id']===(int)$j['id']?'selected':''; ?>><?php echo esc($j['job_card_number'].' · '.$j['customer_name']); ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-outline-primary">Save</button></form></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No customer service requests found.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>