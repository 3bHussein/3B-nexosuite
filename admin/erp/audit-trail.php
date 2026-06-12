<?php
$pageTitle='Advanced Audit Trail';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('audit_trail');
$pdo=getDB();
$module=trim((string)($_GET['module']??''));
$actor=trim((string)($_GET['actor']??''));
$from=trim((string)($_GET['from']??''));
$to=trim((string)($_GET['to']??''));
$params=[];$where=[];
if($module!==''){$where[]='al.module=?';$params[]=$module;}
if($actor!==''){$where[]='u.email LIKE ?';$params[]='%'.$actor.'%';}
if($from!==''){$where[]='DATE(al.created_at)>=?';$params[]=$from;}
if($to!==''){$where[]='DATE(al.created_at)<=?';$params[]=$to;}
$sql='SELECT al.*,u.email actor_email FROM '.table('activity_log').' al LEFT JOIN '.table('users').' u ON u.id=al.actor_user_id'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY al.created_at DESC,al.id DESC LIMIT 500';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$modules=$pdo->query('SELECT DISTINCT module FROM '.table('activity_log').' ORDER BY module ASC')->fetchAll(PDO::FETCH_COLUMN);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Compliance & Traceability</div><h2 class="h4 mb-1">Advanced Audit Trail</h2><p class="text-secondary mb-0">Filter operational activity by module, actor, date, document type, and reference.</p></div></div>
<div class="card-admin p-3 mb-4"><form class="d-flex flex-wrap gap-2 align-items-end"><div><label class="form-label">Module</label><select class="form-select" name="module"><option value="">All</option><?php foreach($modules as $m): ?><option value="<?php echo esc($m); ?>" <?php echo $module===$m?'selected':''; ?>><?php echo esc($m); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Actor Email</label><input class="form-control" name="actor" value="<?php echo esc($actor); ?>"></div><div><label class="form-label">From</label><input class="form-control" type="date" name="from" value="<?php echo esc($from); ?>"></div><div><label class="form-label">To</label><input class="form-control" type="date" name="to" value="<?php echo esc($to); ?>"></div><button class="btn btn-brand">Filter</button></form></div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Audit Log</div><h2 class="h5 mb-0"><?php echo count($rows); ?> Events</h2></div></div><table class="table align-middle"><thead><tr><th>Date</th><th>Module</th><th>Action</th><th>Actor</th><th>Reference</th><th>Description</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo esc($row['created_at']); ?></td><td><?php echo esc($row['module']); ?></td><td><code><?php echo esc($row['action']); ?></code></td><td><?php echo esc($row['actor_email']?:'System'); ?></td><td><?php echo esc(($row['reference_type']?:'').' #'.($row['reference_id']?:'')); ?></td><td><?php echo esc($row['description']); ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No audit events match the filter.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>