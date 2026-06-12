<?php
$pageTitle='Branches';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('org_structure');
$pdo=getDB();

$companies=$pdo->query('SELECT id,company_code,company_name FROM '.table('companies').' WHERE status="active" ORDER BY company_name')->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
  requireBranchCreationAllowed($pdo);

  $companyId=(int)($_POST['company_id']??0);
  $code=trim((string)($_POST['branch_code']??''));
  $name=trim((string)($_POST['branch_name']??''));

  if($companyId<=0||$code===''||$name===''){
    flash('error','Company, branch code, and branch name are required.');
    redirect(ADMIN_URL.'/erp/branches.php');
  }

  $stmt=$pdo->prepare('INSERT INTO '.table('branches').' (company_id,branch_code,branch_name,email,phone,address,status,is_head_office) VALUES (?,?,?,?,?,?,?,?)');
  $stmt->execute([
    $companyId,
    $code,
    $name,
    trim((string)($_POST['email']??'')),
    trim((string)($_POST['phone']??'')),
    trim((string)($_POST['address']??'')),
    trim((string)($_POST['status']??'active')),
    !empty($_POST['is_head_office'])?1:0
  ]);

  $id=(int)$pdo->lastInsertId();

  if(!empty($_POST['is_head_office'])){
    $pdo->prepare('UPDATE '.table('branches').' SET is_head_office=CASE WHEN id=? THEN 1 ELSE 0 END WHERE company_id=?')->execute([$id,$companyId]);
  }

  logActivity($pdo,'Organization','branch_created','Branch '.$code.' created.','branch',$id);
  flash('success','Branch created.');
  redirect(ADMIN_URL.'/erp/branches.php');
}

$rows=$pdo->query('SELECT b.*,c.company_code,c.company_name,(SELECT COUNT(*) FROM '.table('warehouses').' w WHERE w.branch_id=b.id) warehouse_count FROM '.table('branches').' b LEFT JOIN '.table('companies').' c ON c.id=b.company_id ORDER BY c.company_name,b.is_head_office DESC,b.branch_name')->fetchAll();

$branchLimitReached = function_exists('licenseTrialLimitReached') ? licenseTrialLimitReached('branches', $pdo) : false;
$branchLimit = function_exists('licensePlanLimit') ? licensePlanLimit('branches') : null;
$branchCount = function_exists('currentLicenseEntityCount') ? currentLicenseEntityCount('branches', $pdo) : count($rows);

include dirname(__DIR__).'/header.php';
?>

<?php if($branchLimitReached): ?>
<div class="alert alert-danger">
  <strong>Branch license limit reached.</strong>
  Current branches: <?php echo (int)$branchCount; ?> / <?php echo $branchLimit === null ? '∞' : (int)$branchLimit; ?>.
  Creating a new branch is blocked for this plan.
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4">
      <div class="erp-kicker">Operational Units</div>
      <h2 class="h5 mb-3">Create Branch</h2>

      <div class="mb-3">
        <label class="form-label">Company</label>
        <select class="form-select" name="company_id" <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
          <?php foreach($companies as $company): ?>
            <option value="<?php echo (int)$company['id']; ?>"><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row g-2">
        <div class="col-md-5">
          <label class="form-label">Code</label>
          <input class="form-control" name="branch_code" required <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
        </div>
        <div class="col-md-7">
          <label class="form-label">Name</label>
          <input class="form-control" name="branch_name" required <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
        </div>
      </div>

      <div class="row g-2 mt-1">
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input class="form-control" name="email" <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input class="form-control" name="phone" <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
        </div>
      </div>

      <div class="mt-2">
        <label class="form-label">Address</label>
        <textarea class="form-control" name="address" rows="2" <?php echo $branchLimitReached ? 'disabled' : ''; ?>></textarea>
      </div>

      <div class="row g-2 mt-1">
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select class="form-select" name="status" <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
          <label class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_head_office" value="1" <?php echo $branchLimitReached ? 'disabled' : ''; ?>>
            <span class="form-check-label">Head Office</span>
          </label>
        </div>
      </div>

      <button class="btn btn-brand mt-3" <?php echo $branchLimitReached ? 'disabled' : ''; ?>>Create Branch</button>
    </form>
  </div>

  <div class="col-xl-8">
    <div class="table-wrap table-responsive">
      <div class="table-toolbar">
        <div>
          <div class="erp-kicker">Branch Directory</div>
          <h2 class="h5 mb-0">Branches / Outlets</h2>
        </div>
        <a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/warehouses.php"><?php echo t('Warehouses', 'المخازن'); ?></a>
      </div>

      <table class="table align-middle">
        <thead>
          <tr>
            <th>Branch</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Warehouses</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $row): ?>
            <tr>
              <td>
                <strong><?php echo esc($row['branch_code'].' · '.$row['branch_name']); ?></strong>
                <?php if(!empty($row['is_head_office'])): ?><span class="badge bg-primary">Head Office</span><?php endif; ?>
              </td>
              <td><?php echo esc($row['company_code'].' · '.$row['company_name']); ?></td>
              <td><?php echo esc($row['email']); ?><div class="small text-secondary"><?php echo esc($row['phone']); ?></div></td>
              <td><?php echo (int)$row['warehouse_count']; ?></td>
              <td><span class="badge bg-<?php echo $row['status']==='active'?'success':'secondary'; ?>"><?php echo esc($row['status']); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include dirname(__DIR__).'/footer.php'; ?>