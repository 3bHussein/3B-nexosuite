<?php
$pageTitle='E-commerce Website Permissions';
require_once dirname(__DIR__,2) . '/includes/functions.php';
adminGuard();

$pdo=getDB();
$catalog=websitePermissionCatalog();
$userTypes=websiteUserTypesCatalog();

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $action=(string)($_POST['action']??'save');
        if($action==='reset'){
            saveSetting('website_permission_config_json','');
            saveSetting('website_permission_enforcement_enabled','1');
            flash('success','Website permissions reset to default.');
            redirect(ADMIN_URL.'/erp/ecommerce-permission-manager.php');
        }

        $config=[];
        foreach($catalog as $permissionKey=>$permission){
            $selected=(array)($_POST['permissions'][$permissionKey]??[]);
            $selected=array_values(array_intersect(array_map('strval',$selected), array_keys($userTypes)));
            $config[$permissionKey]=$selected;
        }
        saveSetting('website_permission_config_json',json_encode($config,JSON_UNESCAPED_SLASHES));
        saveSetting('website_permission_enforcement_enabled',($_POST['website_permission_enforcement_enabled']??'1')==='1'?'1':'0');
        saveSetting('website_permission_require_active_module',($_POST['website_permission_require_active_module']??'0')==='1'?'1':'0');
        flash('success','E-commerce website permissions saved. Website, portal, and user-type access now follow this configuration.');
    }catch(Throwable $e){
        recordSystemError($pdo,$e,['page'=>'ecommerce-permission-manager']);
        flash('error',$e->getMessage());
    }
    redirect(ADMIN_URL.'/erp/ecommerce-permission-manager.php');
}

$config=websitePermissionConfig();
$groups=[];
foreach($catalog as $key=>$permission){$groups[$permission['group'] ?? 'Other'][$key]=$permission;}
include dirname(__DIR__).'/header.php';
?>
<style>
.ecom-permission-grid{display:grid;gap:18px}
.permission-group-card{background:#fff;border:1px solid #e6eaf2;border-radius:24px;box-shadow:0 14px 32px rgba(15,23,42,.06);overflow:hidden}
.permission-group-head{padding:18px 20px;background:linear-gradient(135deg,#f8fafc,#eef2ff);border-bottom:1px solid #e6eaf2}
.permission-table{margin:0}
.permission-table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b}
.permission-check{display:inline-flex;gap:6px;align-items:center;border:1px solid #e6eaf2;border-radius:999px;padding:5px 9px;margin:2px;background:#fff;font-size:.82rem}
.permission-check input{margin:0}
.module-pill{display:inline-flex;border-radius:999px;background:#eef2ff;color:#1e3a8a;font-size:.72rem;font-weight:800;padding:3px 8px}
.denied-pill{display:inline-flex;border-radius:999px;background:#fef2f2;color:#991b1b;font-size:.72rem;font-weight:800;padding:3px 8px}
.allowed-pill{display:inline-flex;border-radius:999px;background:#ecfdf3;color:#027a48;font-size:.72rem;font-weight:800;padding:3px 8px}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <div class="erp-kicker">Website + User Type Access</div>
    <h2 class="h4 mb-1">E-commerce Website Permissions</h2>
    <p class="text-secondary mb-0">Control permissions that belong to the e-commerce website and customer/vendor/technician portal, not only ERP sidebar modules.</p>
  </div>
  <form method="post" onsubmit="return confirm('Reset website permissions to default?')">
    <input type="hidden" name="action" value="reset">
    <button class="btn btn-outline-danger">Reset Defaults</button>
  </form>
</div>

<div class="alert alert-info border-0 shadow-sm">
  <strong>Real effect:</strong>
  These permissions affect website pages and user type access. Example: disable <strong>Bulk Order Request</strong> for Guest, and guests cannot open the bulk order page. Customer account access is kept flexible by default, and module-package dependency checks are optional.
</div>

<form method="post" class="ecom-permission-grid">
  <div class="card-admin p-4">
    <div class="row g-3 align-items-end">
      <div class="col-lg-4">
        <label class="form-label">Website Permission Enforcement</label>
        <select class="form-select" name="website_permission_enforcement_enabled">
          <option value="1" <?php echo setting('website_permission_enforcement_enabled','1')==='1'?'selected':''; ?>>Enforce user-type permissions</option>
          <option value="0" <?php echo setting('website_permission_enforcement_enabled','1')==='0'?'selected':''; ?>>Preview only / allow all</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Require Active Module Package</label>
        <select class="form-select" name="website_permission_require_active_module">
          <option value="0" <?php echo setting('website_permission_require_active_module','0')==='0'?'selected':''; ?>>Flexible / do not block by module</option>
          <option value="1" <?php echo setting('website_permission_require_active_module','0')==='1'?'selected':''; ?>>Strict / block if module disabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <div class="small text-secondary">Use <strong>Flexible</strong> so customers can still access account, booking, downloads, and portal pages even when ERP module selection is narrow.</div>
      </div>
    </div>
  </div>

  <?php foreach($groups as $groupName=>$permissions): ?>
    <section class="permission-group-card">
      <div class="permission-group-head">
        <h3 class="h5 mb-1"><?php echo esc($groupName); ?></h3>
        <p class="text-secondary mb-0">Assign access by user type.</p>
      </div>
      <div class="table-responsive">
        <table class="table permission-table align-middle">
          <thead>
            <tr>
              <th>Permission</th>
              <th>Required Module</th>
              <th>User Types Allowed</th>
              <th>Module Effect</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($permissions as $permissionKey=>$permission): ?>
              <?php
                $requiredModule=(string)($permission['module']??'');
                $moduleEnabled=$requiredModule==='' || appModuleEnabled($requiredModule);
                $selectedTypes=(array)($config[$permissionKey]??[]);
              ?>
              <tr>
                <td style="min-width:260px">
                  <strong><?php echo esc($permission['label']); ?></strong>
                  <div class="small text-secondary"><?php echo esc($permission['description']); ?></div>
                  <code class="small"><?php echo esc($permissionKey); ?></code>
                </td>
                <td><span class="module-pill"><?php echo esc($requiredModule ?: 'none'); ?></span></td>
                <td style="min-width:420px">
                  <?php foreach($userTypes as $typeKey=>$typeLabel): ?>
                    <label class="permission-check">
                      <input type="checkbox" name="permissions[<?php echo esc($permissionKey); ?>][]" value="<?php echo esc($typeKey); ?>" <?php echo in_array($typeKey,$selectedTypes,true)?'checked':''; ?>>
                      <?php echo esc($typeLabel); ?>
                    </label>
                  <?php endforeach; ?>
                </td>
                <td>
                  <?php if($moduleEnabled): ?>
                    <span class="allowed-pill">Module Enabled</span>
                  <?php else: ?>
                    <span class="denied-pill">Module Disabled</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endforeach; ?>

  <div class="card-admin p-4 d-flex flex-wrap justify-content-between gap-3">
    <div>
      <h3 class="h5 mb-1">Save Website Permission Rules</h3>
      <p class="text-secondary mb-0">Changes affect user types immediately on website and portal pages.</p>
    </div>
    <button class="btn btn-brand btn-lg">Save Permissions</button>
  </div>
</form>

<?php include dirname(__DIR__).'/footer.php'; ?>