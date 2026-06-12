<?php
$pageTitle='Customer Segments';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('customer_segments');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='create'){
      $code=preg_replace('/[^A-Z0-9_\\-]/','',strtoupper((string)$_POST['segment_code']));
      $criteria=trim((string)$_POST['criteria_json']);json_decode($criteria,true);if(json_last_error()!==JSON_ERROR_NONE){throw new RuntimeException('Criteria JSON is invalid.');}
      $stmt=$pdo->prepare('INSERT INTO '.table('customer_segments').' (segment_code,segment_name,segment_type,criteria_json,status) VALUES (?,?,?,?,?)');
      $stmt->execute([$code,trim((string)$_POST['segment_name']),trim((string)$_POST['segment_type']),$criteria,'active']);
      flash('success','Segment created.');
    }elseif($action==='refresh'){
      $count=refreshCustomerSegment($pdo,(int)$_POST['segment_id']);flash('success','Segment refreshed: '.$count.' members.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'customer-segments']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/customer-segments.php');
}
$segments=$pdo->query('SELECT * FROM '.table('customer_segments').' ORDER BY created_at DESC')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Audience Intelligence</div><h2 class="h4 mb-1">Customer Segments</h2><p class="text-secondary mb-0">Build reusable customer and lead lists for campaigns, sales focus, and account targeting.</p></div><a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/campaigns.php"><?php echo t('Campaigns', 'الحملات'); ?></a></div>
<div class="row g-4"><div class="col-xl-4"><form method="post" class="card-admin p-4"><input type="hidden" name="action" value="create"><h2 class="h5 mb-3">Create Segment</h2><label class="form-label">Code</label><input class="form-control mb-2" name="segment_code" placeholder="SEG-HOT-LEADS" required><label class="form-label">Name</label><input class="form-control mb-2" name="segment_name" required><label class="form-label">Type</label><select class="form-select mb-2" name="segment_type"><option value="dynamic">Dynamic</option><option value="static">Static</option></select><label class="form-label">Criteria JSON</label><textarea class="form-control mb-2" name="criteria_json" rows="5">{&quot;customer_type&quot;:&quot;b2b&quot;}</textarea><div class="small text-secondary mb-3">Supported examples: {"customer_type":"b2b"}, {"credit_status":"hold"}, {"lead_score_gte":70}</div><button class="btn btn-brand w-100">Create Segment</button></form></div><div class="col-xl-8"><div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Segment Register</div><h2 class="h5 mb-0">Segments</h2></div></div><table class="table align-middle"><thead><tr><th>Segment</th><th>Type</th><th>Criteria</th><th>Members</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($segments as $segment): ?><tr><td><strong><?php echo esc($segment['segment_code']); ?></strong><div class="small text-secondary"><?php echo esc($segment['segment_name']); ?></div></td><td><?php echo esc($segment['segment_type']); ?></td><td><code><?php echo esc($segment['criteria_json']); ?></code></td><td><?php echo (int)$segment['member_count']; ?></td><td><span class="badge bg-<?php echo esc(statusTone($segment['status'])); ?>"><?php echo esc($segment['status']); ?></span></td><td class="text-end"><form method="post"><input type="hidden" name="action" value="refresh"><input type="hidden" name="segment_id" value="<?php echo (int)$segment['id']; ?>"><button class="btn btn-sm btn-outline-primary">Refresh</button></form></td></tr><?php endforeach; ?><?php if(!$segments): ?><tr><td colspan="6" class="text-secondary">No segments yet.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>