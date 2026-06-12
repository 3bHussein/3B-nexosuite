<?php
$pageTitle='ERP Activity Log';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('activity_log');
$pdo=getDB();
$module=trim((string)($_GET['module']??''));
$where='';$params=[];
if($module!==''){$where=' WHERE a.module=?';$params[]=$module;}
$stmt=$pdo->prepare('SELECT a.*,u.first_name,u.last_name,u.email FROM ' . table('activity_log') . ' a LEFT JOIN ' . table('users') . ' u ON u.id=a.actor_user_id'.$where.' ORDER BY a.created_at DESC,a.id DESC LIMIT 250');
$stmt->execute($params);$rows=$stmt->fetchAll();
$modules=$pdo->query('SELECT DISTINCT module FROM ' . table('activity_log') . ' WHERE module IS NOT NULL AND module<>"" ORDER BY module')->fetchAll(PDO::FETCH_COLUMN);
include dirname(__DIR__).'/header.php';
?>
<div class="table-wrap">
    <div class="table-toolbar">
        <div><div class="erp-kicker">Control trail</div><h2 class="h5 mb-0">Operational Activity</h2></div>
        <div class="filter-bar">
            <input class="form-control search-input" placeholder="Search activity..." data-table-search="#activityTable">
            <a class="btn btn-sm <?php echo $module===''?'btn-brand':'btn-outline-secondary'; ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/activity-log.php">All</a>
            <?php foreach($modules as $item): ?><a class="btn btn-sm <?php echo $module===$item?'btn-brand':'btn-outline-secondary'; ?>" href="?module=<?php echo rawurlencode((string)$item); ?>"><?php echo esc($item); ?></a><?php endforeach; ?>
        </div>
    </div>
    <div class="table-responsive"><table class="table" id="activityTable"><thead><tr><th>Date</th><th>Module</th><th>Action</th><th>Description</th><th>Actor</th><th>Reference</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?php echo esc(substr((string)$row['created_at'],0,16)); ?></td><td><span class="pill pill-soft"><?php echo esc($row['module']); ?></span></td><td><?php echo esc($row['action']); ?></td><td><?php echo esc($row['description']); ?></td><td><?php echo esc(trim(($row['first_name']??'').' '.($row['last_name']??'')) ?: ($row['email']??'System')); ?></td><td><?php echo esc(($row['reference_type'] ?: '—') . (!empty($row['reference_id']) ? ' #' . $row['reference_id'] : '')); ?></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>