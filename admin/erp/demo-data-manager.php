<?php
$pageTitle='Demo Data Manager';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('demo_data_manager');
$pdo=getDB();

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'install');
    if($action==='install'){
        $n=p34InstallDemoData($pdo);
        flash('success','Demo data installed. Records created: '.$n);
    } elseif($action==='defaults'){
        installDocumentDefaults($pdo);
        ecom33InstallDefaults($pdo);
        flash('success','Module defaults installed for DMS and advanced ecommerce.');
    } elseif($action==='website_content'){
        $n=p56InstallWhiteLabelWebsiteContent($pdo);
        flash('success','Website product, blog and downloads content installed/checked. New records created: '.$n);
    } elseif($action==='remove_batch'){
        $batchId=(int)($_POST['batch_id']??0);
        if($batchId<=0){throw new RuntimeException('Select a demo batch to remove.');}
        $result=p34RemoveDemoData($pdo,$batchId,true);
        flash('success','Demo batch removed. Deleted: '.$result['deleted'].', skipped: '.$result['skipped'].', errors: '.$result['errors'].'.');
    } elseif($action==='remove_all'){
        $confirm=trim((string)($_POST['confirm_remove_demo']??''));
        if($confirm!=='REMOVE DEMO'){
            throw new RuntimeException('Type REMOVE DEMO to confirm removing all demo data.');
        }
        $result=p34RemoveDemoData($pdo,0,true);
        flash('success','All demo data removal completed. Deleted: '.$result['deleted'].', tracked items: '.$result['tracked_items'].', skipped: '.$result['skipped'].', errors: '.$result['errors'].'.');
    }
  }catch(Throwable $e){
    recordSystemError($pdo,$e,['page'=>'demo-data-manager']);
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/demo-data-manager.php');
}

$batches=$pdo->query('SELECT * FROM '.table('production_demo_data_batches').' ORDER BY created_at DESC LIMIT 100')->fetchAll();
$items=$pdo->query('SELECT i.*,b.batch_number FROM '.table('production_demo_data_items').' i LEFT JOIN '.table('production_demo_data_batches').' b ON b.id=i.production_demo_data_batch_id ORDER BY i.created_at DESC LIMIT 300')->fetchAll();

$activeItems=0;$removedItems=0;$errorItems=0;
foreach($items as $item){
    if(($item['status']??'')==='removed'){$removedItems++;}
    elseif(($item['status']??'')==='error'){$errorItems++;}
    else{$activeItems++;}
}
$activeBatches=0;
foreach($batches as $batch){
    if(($batch['status']??'')!=='removed'){$activeBatches++;}
}

include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <div class="erp-kicker">Demo Environment</div>
    <h2 class="h4 mb-1">Demo Data Manager</h2>
    <p class="text-secondary mb-0">Install starter demo data during testing, then remove it after installation before handing over the system.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <form method="post"><button name="action" value="install" class="btn btn-brand">Install Starter Demo</button></form>
    <form method="post"><button name="action" value="defaults" class="btn btn-outline-primary">Install Module Defaults</button></form><form method="post"><button name="action" value="website_content" class="btn btn-outline-success">Install Website Content</button></form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card-admin p-3"><div class="erp-kicker">Active Batches</div><strong class="h4 mb-0"><?php echo (int)$activeBatches; ?></strong></div></div>
  <div class="col-md-3"><div class="card-admin p-3"><div class="erp-kicker">Active Demo Items</div><strong class="h4 mb-0"><?php echo (int)$activeItems; ?></strong></div></div>
  <div class="col-md-3"><div class="card-admin p-3"><div class="erp-kicker">Removed Items</div><strong class="h4 mb-0"><?php echo (int)$removedItems; ?></strong></div></div>
  <div class="col-md-3"><div class="card-admin p-3"><div class="erp-kicker">Errors</div><strong class="h4 mb-0"><?php echo (int)$errorItems; ?></strong></div></div>
</div>

<div class="alert alert-warning border-0 shadow-sm">
  <strong>After install cleanup:</strong>
  Use <strong>Remove All Demo Data</strong> before giving the system to a real customer. This removes tracked starter demo records and known untracked demo records such as demo products, demo categories and demo customers.
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card-admin p-4 mb-4">
      <h2 class="h5 mb-3">Remove All Demo Data</h2>
      <p class="text-secondary">This is the post-install cleanup option. It removes demo records created for testing while keeping real settings, admin users, module bundle settings and system configuration.</p>
      <form method="post" onsubmit="return confirm('Remove all demo data? This cannot be undone.');">
        <input type="hidden" name="action" value="remove_all">
        <label class="form-label">Type <code>REMOVE DEMO</code> to confirm</label>
        <input class="form-control mb-3" name="confirm_remove_demo" placeholder="REMOVE DEMO" autocomplete="off">
        <button class="btn btn-outline-danger w-100">Remove All Demo Data</button>
      </form>
    </div>

    <div class="card-admin p-4">
      <h2 class="h5 mb-3">What is removed?</h2>
      <ul class="small text-secondary mb-0">
        <li>Tracked demo categories, products, customers and future tracked entities.</li>
        <li>Known starter demo records even if tracking was missing.</li>
        <li>Demo batch status is changed to removed for audit history.</li>
        <li>Real customers, orders, settings, users and developer access are not touched.</li>
      </ul>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="table-wrap table-responsive mb-4">
      <h2 class="h5 mb-3">Demo Batches</h2>
      <table class="table align-middle">
        <thead><tr><th>Batch</th><th>Records</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach($batches as $b): ?>
            <tr>
              <td><strong><?php echo esc($b['batch_number']); ?></strong><div class="small text-secondary"><?php echo esc($b['batch_name'].' · '.$b['created_at']); ?></div></td>
              <td><?php echo (int)$b['records_created']; ?></td>
              <td><span class="badge bg-<?php echo esc(statusTone($b['status'])); ?>"><?php echo esc($b['status']); ?></span></td>
              <td class="text-end">
                <?php if(($b['status']??'')!=='removed'): ?>
                  <form method="post" class="d-inline" onsubmit="return confirm('Remove this demo batch?');">
                    <input type="hidden" name="action" value="remove_batch">
                    <input type="hidden" name="batch_id" value="<?php echo (int)$b['id']; ?>">
                    <button class="btn btn-sm btn-outline-danger">Remove Batch</button>
                  </form>
                <?php else: ?>
                  <span class="text-secondary small">Removed</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$batches): ?><tr><td colspan="4" class="text-secondary">No demo batches found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Demo Items</h2>
      <table class="table align-middle">
        <thead><tr><th>Batch</th><th>Entity</th><th>Label</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach($items as $i): ?>
            <tr>
              <td><?php echo esc($i['batch_number']); ?></td>
              <td><?php echo esc($i['entity_type'].' #'.$i['entity_id']); ?></td>
              <td><?php echo esc($i['entity_label']); ?></td>
              <td><span class="badge bg-<?php echo esc(statusTone($i['status'])); ?>"><?php echo esc($i['status']); ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$items): ?><tr><td colspan="4" class="text-secondary">No demo items found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>