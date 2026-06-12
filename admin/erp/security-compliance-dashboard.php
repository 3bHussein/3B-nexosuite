<?php
$pageTitle='Audit, Compliance & Security 2.0';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('security_compliance_dashboard');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='scan'){
      $failed=(int)$pdo->query('SELECT COUNT(*) FROM '.table('security_events').' WHERE severity IN ("warning","critical") AND created_at>=DATE_SUB(NOW(),INTERVAL 24 HOUR)')->fetchColumn();
      if($failed>0){createSuspiciousActivityEvent($pdo,'security_scan','Security scan detected '.$failed.' warning/critical event(s) in the last 24 hours.',min(100,50+$failed*5),'warning');}
      flash('success','Security scan completed.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'security-compliance-dashboard']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/security-compliance-dashboard.php');
}
$stats=[
 'security_score'=>securityScore($pdo),
 'open_suspicious'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('suspicious_activity_events').' WHERE status="open"')->fetchColumn(),
 'pending_sensitive'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('sensitive_action_approvals').' WHERE status="pending"')->fetchColumn(),
 'exports_today'=>(int)$pdo->query('SELECT COUNT(*) FROM '.table('data_export_logs').' WHERE DATE(created_at)=CURDATE()')->fetchColumn(),
];
$events=$pdo->query('SELECT * FROM '.table('suspicious_activity_events').' ORDER BY created_at DESC LIMIT 10')->fetchAll();
$approvals=$pdo->query('SELECT sa.*,u.email requester FROM '.table('sensitive_action_approvals').' sa LEFT JOIN '.table('users').' u ON u.id=sa.requested_by ORDER BY sa.created_at DESC LIMIT 10')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Security Governance</div><h2 class="h4 mb-1">Audit, Compliance & Security 2.0</h2><p class="text-secondary mb-0">Central view for access posture, suspicious activity, sensitive approvals, data exports, and compliance evidence.</p></div><form method="post"><input type="hidden" name="action" value="scan"><button class="btn btn-brand">Run Security Scan</button></form></div>
<div class="row g-4 mb-4"><?php foreach($stats as $k=>$v): ?><div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker"><?php echo esc(ucwords(str_replace('_',' ',$k))); ?></div><div class="metric-sm"><?php echo (int)$v; ?><?php echo $k==='security_score'?'%':''; ?></div></div></div><?php endforeach; ?></div>
<div class="row g-4"><div class="col-xl-6"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Suspicious Activity</h2><table class="table"><thead><tr><th>Event</th><th>Risk</th><th>Status</th></tr></thead><tbody><?php foreach($events as $e): ?><tr><td><strong><?php echo esc($e['event_number']); ?></strong><div class="small text-secondary"><?php echo esc($e['event_type'].' · '.$e['description']); ?></div></td><td><?php echo number_format((float)$e['risk_score'],2); ?><br><span class="badge bg-<?php echo esc(statusTone($e['severity'])); ?>"><?php echo esc($e['severity']); ?></span></td><td><span class="badge bg-<?php echo esc(statusTone($e['status'])); ?>"><?php echo esc($e['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div><div class="col-xl-6"><div class="table-wrap table-responsive"><h2 class="h5 mb-3">Sensitive Approvals</h2><table class="table"><thead><tr><th>Approval</th><th>Action</th><th>Status</th></tr></thead><tbody><?php foreach($approvals as $a): ?><tr><td><strong><?php echo esc($a['approval_number']); ?></strong><div class="small text-secondary"><?php echo esc($a['requester']); ?></div></td><td><?php echo esc($a['module'].' · '.$a['action_key']); ?><div class="small text-secondary"><?php echo esc($a['reason']); ?></div></td><td><span class="badge bg-<?php echo esc(statusTone($a['status'])); ?>"><?php echo esc($a['status']); ?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include dirname(__DIR__).'/footer.php'; ?>