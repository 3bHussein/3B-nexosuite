<?php
$pageTitle='Module Bundle Manager';
require_once dirname(__DIR__,2) . '/includes/functions.php';
moduleBundleDeveloperGuard();
$pdo=getDB();

$moduleCatalog=[
 'website_sales'=>['label'=>'Website Sales Only','priority'=>'Website Sales','priority_label'=>'Only public website sales tools: products, categories, online orders, bookings, homepage, content and settings','group'=>'Website Sales','customer_detail'=>'Customer uses only the website sales back office. ERP sidebar modules stay hidden. Best for a customer who needs product catalog, orders, content and settings only.'],
 'website_storefront'=>['label'=>'Website + Storefront','priority'=>'Core','priority_label'=>'Core website, products, cart and checkout','group'=>'Commerce Core','customer_detail'=>'Customer uses the public ecommerce store, product pages, cart, checkout, contact, downloads and product browsing.'],
 'homepage_cms'=>['label'=>'Homepage Builder + CMS','priority'=>'Frontend','priority_label'=>'Homepage, header/footer, blog and downloads management','group'=>'Commerce Core','customer_detail'=>'Customer can manage homepage sections, promotional blocks, blog/content, downloads and frontend presentation.'],
 'accounting_finance'=>['label'=>'Accounting & Finance','priority'=>'P01/P13/P24','priority_label'=>'Accounting core, finance automation, tax, bank and fixed assets','group'=>'Finance','customer_detail'=>'Customer uses invoices, finance dashboard, journals, budgets, cash flow, tax, bank reconciliation, fixed assets and financial close.'],
 'multicompany_inventory'=>['label'=>'Multi-company, Branch & Warehouse','priority'=>'P02/P19','priority_label'=>'Companies, branches, warehouses, transfers and advanced inventory','group'=>'Operations','customer_detail'=>'Customer uses company/branch structure, warehouses, stock transfers, stock counts, bin locations, serial/lot tracking and inventory valuation.'],
 'approval_workflow'=>['label'=>'Approvals & Internal Controls','priority'=>'P03/P27','priority_label'=>'Maker-checker, approval rules and workflow automation','group'=>'Controls','customer_detail'=>'Customer uses approval rules, internal controls, workflow builder and approval automation for safer operations.'],
 'sales_operations'=>['label'=>'Sales Orders, Fulfillment & RMA','priority'=>'P04','priority_label'=>'Sales orders, delivery notes, returns and credit control','group'=>'Sales','customer_detail'=>'Customer uses quotations, sales orders, delivery notes, returns/RMA, credit control and sales fulfillment.'],
 'service_projects'=>['label'=>'Service, Projects, Warranty & AMC','priority'=>'P05/P16','priority_label'=>'Job cards, service operations, warranty, technician and field workflows','group'=>'Service','customer_detail'=>'Customer uses job cards, service contracts, warranty claims, technician checklists, field dispatch, sign-off and project/service execution.'],
 'security_deployment'=>['label'=>'Security, Backup & Deployment','priority'=>'P07/P28/P34','priority_label'=>'Security, audit, repair center, production readiness and hardening','group'=>'Security','customer_detail'=>'Customer/admin uses audit, backups, system health, production hardening, repair center, settings repair and error log viewer.'],
 'seo_frontend_settings'=>['label'=>'SEO, Frontend Settings & Robots','priority'=>'P08/P09','priority_label'=>'SEO controls, robots.txt, page settings and mobile-friendly storefront','group'=>'Marketing','customer_detail'=>'Customer manages SEO metadata, robots.txt, frontend visibility settings, header/footer sections and website performance basics.'],
 'rfq_tender'=>['label'=>'RFQ, Tender & Quote Workflow','priority'=>'P10','priority_label'=>'RFQ, tender, supplier quote comparison and award controls','group'=>'Procurement','customer_detail'=>'Customer uses RFQ management, tender workflow, supplier comparison and quote awarding.'],
 'crm_pipeline'=>['label'=>'CRM, Pipeline & Campaigns','priority'=>'P11/P22','priority_label'=>'Leads, opportunities, campaigns, follow-ups and sales automation','group'=>'Sales','customer_detail'=>'Customer uses leads, opportunities, sales pipeline, campaign automation, customer segments, follow-ups and sales forecasting.'],
 'hr_payroll'=>['label'=>'HR, Payroll & Employee Self-Service','priority'=>'P12/P23','priority_label'=>'Employees, attendance, leave, payroll, commissions and ESS portal','group'=>'HR','customer_detail'=>'Customer uses employee records, attendance, leave, payroll, commissions, expenses, contracts, loans and employee self-service.'],
 'reporting_bi'=>['label'=>'Reporting, BI & KPI Dashboards','priority'=>'P14/P25','priority_label'=>'Management reports, KPI builder, executive dashboards and scheduled exports','group'=>'Analytics','customer_detail'=>'Customer uses dashboards, reports, KPI builder, drilldowns, scheduled exports, executive BI and metrics library.'],
 'api_integrations'=>['label'=>'API, Webhooks & Integrations','priority'=>'P15/P30','priority_label'=>'API catalog, webhooks, connector templates, marketplace and accounting sync','group'=>'Integrations','customer_detail'=>'Customer/developer uses API keys, endpoint catalog, webhooks, marketplace sync, integration logs, field mappings and connector templates.'],
 'ai_decision'=>['label'=>'AI Assistant & Decision Engine','priority'=>'P17/P26','priority_label'=>'Smart search, alerts, recommendations, decision support and assistant workflows','group'=>'AI','customer_detail'=>'Customer uses smart search, predictive alerts, recommendations, decision engine, risk scoring and anomaly detection.'],
 'manufacturing_bom'=>['label'=>'Manufacturing, BOM & Work Orders','priority'=>'P18','priority_label'=>'BOM, work orders, material issue, production receipt and costing','group'=>'Manufacturing','customer_detail'=>'Customer uses BOM, work orders, production planning, material issue, production receipts, quality checks and work centers.'],
 'procurement_supplier_portal'=>['label'=>'Procurement + Supplier Portal','priority'=>'P20','priority_label'=>'Supplier onboarding, scorecards, contracts, procurement and vendor portal','group'=>'Procurement','customer_detail'=>'Customer uses suppliers, purchase orders, contracts, supplier onboarding, scorecards, vendor portal and procurement dashboard.'],
 'customer_portal'=>['label'=>'Customer Portal 2.0','priority'=>'P21','priority_label'=>'Customer dashboard, assets, service requests, invoices, documents and payments','group'=>'Portal','customer_detail'=>'Customer’s clients use a portal for orders, assets, service requests, invoices, documents, downloads, notifications, feedback and support.'],
 'saas_subscription'=>['label'=>'SaaS, Multi-Tenant & Subscription','priority'=>'P29','priority_label'=>'Tenant plans, subscriptions, billing, domains and usage enforcement','group'=>'SaaS','customer_detail'=>'Business owner uses tenant onboarding, subscription plans, license enforcement, usage limits, SaaS billing and trial accounts.'],
 'mobile_pwa'=>['label'=>'Mobile App / PWA Readiness','priority'=>'P31','priority_label'=>'Mobile shell, PWA settings, offline sync and push notification foundation','group'=>'Mobile','customer_detail'=>'Customer uses mobile dashboard, PWA setup, offline sync, push notifications, quick actions and field-friendly mobile pages.'],
 'document_management'=>['label'=>'Document Management System','priority'=>'P32','priority_label'=>'Document library, folders, versions, approvals, expiry and access logs','group'=>'Documents','customer_detail'=>'Customer uses document library, folders, versions, approvals, expiry alerts, linked records and document access logs.'],
 'advanced_ecommerce'=>['label'=>'Advanced E-commerce 2.0','priority'=>'P33','priority_label'=>'B2B pricing, bundles, digital licenses, wishlists, compare, quote and bulk order','group'=>'Commerce Advanced','customer_detail'=>'Customer uses B2B price lists, customer price rules, bundles, digital licenses, wishlists, product comparison, quote and bulk order requests.'],
 'documentation_training'=>['label'=>'Documentation, Training & Commercial Packaging','priority'=>'P35','priority_label'=>'Documentation center, training, demo credentials, packages and handover','group'=>'Commercial','customer_detail'=>'Customer/admin uses manuals, training courses, demo credentials, commercial packages, onboarding checklists, feature comparison and handover center.'],
];

$bundlePresets=[
 'website_sales_only'=>['label'=>'Website Sales Only','business'=>'general','modules'=>['website_sales']],
 'starter_commerce'=>['label'=>'Starter Commerce Bundle','business'=>'general','modules'=>['website_storefront','homepage_cms','seo_frontend_settings','sales_operations','crm_pipeline','customer_portal','security_deployment','documentation_training']],
 'finance_inventory'=>['label'=>'Finance + Inventory Bundle','business'=>'general','modules'=>['accounting_finance','multicompany_inventory','security_deployment','reporting_bi','documentation_training']],
 'automotive_workshop'=>['label'=>'Automotive Workshop ERP Bundle','business'=>'automotive','modules'=>['website_storefront','homepage_cms','seo_frontend_settings','accounting_finance','multicompany_inventory','approval_workflow','sales_operations','service_projects','rfq_tender','crm_pipeline','reporting_bi','api_integrations','procurement_supplier_portal','customer_portal','mobile_pwa','document_management','advanced_ecommerce','security_deployment','documentation_training']],
 'electronics_retail'=>['label'=>'Electronics Retail + Warranty Bundle','business'=>'electronics','modules'=>['website_storefront','homepage_cms','seo_frontend_settings','accounting_finance','multicompany_inventory','sales_operations','crm_pipeline','reporting_bi','api_integrations','procurement_supplier_portal','customer_portal','mobile_pwa','document_management','advanced_ecommerce','security_deployment','documentation_training']],
 'food_industry'=>['label'=>'Food Industry Operations Bundle','business'=>'food','modules'=>['website_storefront','homepage_cms','seo_frontend_settings','accounting_finance','multicompany_inventory','approval_workflow','sales_operations','rfq_tender','crm_pipeline','hr_payroll','reporting_bi','api_integrations','manufacturing_bom','procurement_supplier_portal','customer_portal','mobile_pwa','document_management','security_deployment','documentation_training']],
 'general_trading'=>['label'=>'General Trading ERP Bundle','business'=>'general','modules'=>['website_storefront','homepage_cms','seo_frontend_settings','accounting_finance','multicompany_inventory','approval_workflow','sales_operations','rfq_tender','crm_pipeline','hr_payroll','reporting_bi','api_integrations','procurement_supplier_portal','customer_portal','mobile_pwa','document_management','advanced_ecommerce','security_deployment','documentation_training']],
 'enterprise_full'=>['label'=>'Full Enterprise ERP Bundle','business'=>'general','modules'=>array_keys($moduleCatalog)],
];

$settingsStmt=$pdo->prepare('INSERT INTO '.table('settings').' (key_name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
$saveSetting=function(string $key,string $value) use ($settingsStmt): void {$settingsStmt->execute([$key,$value]);};

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $action=(string)($_POST['action']??'save');
        if($action==='apply_preset'){
            $preset=(string)($_POST['preset_key']??'');
            if(!isset($bundlePresets[$preset])){throw new RuntimeException('Invalid preset bundle.');}
            $selected=$bundlePresets[$preset]['modules'];
            $bundleKey=$preset;
            $bundleLabel=$bundlePresets[$preset]['label'];
            $businessType=$bundlePresets[$preset]['business'];
        }else{
            $selected=array_values(array_unique(array_filter(array_map('strval',(array)($_POST['enabled_modules']??[])),fn($m)=>isset($moduleCatalog[$m]))));
            $bundleKey=trim((string)($_POST['bundle_key']??'custom'));
            $bundleLabel=trim((string)($_POST['bundle_label']??'Custom Module Selection'));
            $businessType=trim((string)($_POST['business_type']??setting('selected_business_type','automotive')));
        }
        if(!$selected){throw new RuntimeException('Please select at least one module.');}
        $priorities=[];
        foreach($selected as $m){$priorities[]=$moduleCatalog[$m]['priority'];}
        $priorities=array_values(array_unique($priorities));

        $isPureWebsiteSalesOnly = count($selected) === 1 && in_array('website_sales', $selected, true);
        if (!$isPureWebsiteSalesOnly && $bundleKey === 'website_sales_only') {
            $bundleKey = 'custom';
            $bundleLabel = 'Custom Module Selection';
        }
        $websiteSalesOnlyMode = ($isPureWebsiteSalesOnly && $bundleKey === 'website_sales_only') ? '1' : '0';

        $enforcement=($_POST['module_bundle_enforcement_enabled']??setting('module_bundle_enforcement_enabled','1'))==='1'?'1':'0';
        $hideSidebar=($_POST['module_bundle_hide_disabled_sidebar']??setting('module_bundle_hide_disabled_sidebar','1'))==='1'?'1':'0';
        $locked=($_POST['module_bundle_selection_locked']??setting('module_bundle_selection_locked','0'))==='1'?'1':'0';
        $customerNotes=trim((string)($_POST['module_bundle_customer_notes']??setting('module_bundle_customer_notes','Selected modules control the sidebar and direct ERP access.')));

        foreach([
            'selected_module_bundle'=>$bundleKey,
            'selected_module_bundle_label'=>$bundleLabel,
            'selected_business_type'=>$businessType,
            'enabled_modules_json'=>json_encode($selected,JSON_UNESCAPED_SLASHES),
            'enabled_priorities_json'=>json_encode($priorities,JSON_UNESCAPED_SLASHES),
            'website_sales_only_mode'=>$websiteSalesOnlyMode,
            'module_bundle_enforcement_enabled'=>$enforcement,
            'module_bundle_hide_disabled_sidebar'=>$hideSidebar,
            'module_bundle_selection_locked'=>$locked,
            'module_bundle_customer_notes'=>$customerNotes,
        ] as $k=>$v){$saveSetting($k,(string)$v);}

        $number='MBND-'.date('YmdHis');
        $pdo->prepare('INSERT INTO '.table('module_bundle_selections').' (bundle_number,business_type,bundle_key,bundle_label,selected_modules_json,selected_priorities_json,status,created_by) VALUES (?,?,?,?,?,?,"active",?)')->execute([$number,$businessType,$bundleKey,$bundleLabel,json_encode($selected,JSON_UNESCAPED_SLASHES),json_encode($priorities,JSON_UNESCAPED_SLASHES),(int)(currentUser()['id']??0)?:null]);
        $selectionId=(int)$pdo->lastInsertId();
        $item=$pdo->prepare('INSERT INTO '.table('module_bundle_selection_items').' (module_bundle_selection_id,module_code,module_label,priority_code,priority_label,module_group,status) VALUES (?,?,?,?,?,?,"enabled")');
        foreach($selected as $code){$info=$moduleCatalog[$code];$item->execute([$selectionId,$code,$info['label'],$info['priority'],$info['priority_label'],$info['group']]);}

        flash('success','Module bundle applied. Sidebar and ERP direct access now follow this module plan.');
    }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'module-bundle-manager']);flash('error',$e->getMessage());}
    redirect(ADMIN_URL.'/erp/module-bundle-manager.php');
}

$currentModules=json_decode((string)setting('enabled_modules_json','[]'),true);
if(!is_array($currentModules)){$currentModules=[];}
$currentPriorities=json_decode((string)setting('enabled_priorities_json','[]'),true);
if(!is_array($currentPriorities)){$currentPriorities=[];}
$enabledCount=count($currentModules);
$totalCount=count($moduleCatalog);
$disabledCount=max(0,$totalCount-$enabledCount);
$currentGroups=[];
foreach($currentModules as $code){if(isset($moduleCatalog[$code])){$currentGroups[$moduleCatalog[$code]['group']][]=$code;}}
$history=$pdo->query('SELECT * FROM '.table('module_bundle_selections').' ORDER BY created_at DESC LIMIT 50')->fetchAll();
$items=$pdo->query('SELECT i.*,s.bundle_number FROM '.table('module_bundle_selection_items').' i LEFT JOIN '.table('module_bundle_selections').' s ON s.id=i.module_bundle_selection_id ORDER BY i.created_at DESC LIMIT 200')->fetchAll();

include dirname(__DIR__).'/header.php';
?>
<style>
.module-control-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;max-height:700px;overflow:auto;padding:4px}
.module-control-card{display:block;border:1px solid #e6eaf2;border-radius:18px;padding:14px;background:#fff;transition:.18s ease}
.module-control-card:hover{border-color:#cbd5e1;box-shadow:0 12px 28px rgba(15,23,42,.06)}
.module-control-card input{margin-right:8px}
.module-control-card.is-enabled{border-color:#e6005c;background:#fff4f8}
.module-priority{display:inline-flex;border-radius:99px;background:#eef2ff;color:#1e3a8a;font-size:.72rem;font-weight:800;padding:3px 8px;margin-bottom:6px}
.module-group-pill{display:inline-flex;border-radius:99px;background:#f1f5f9;color:#334155;font-size:.72rem;font-weight:800;padding:3px 8px}
.preset-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.preset-card{border:1px solid #e6eaf2;border-radius:18px;padding:14px;background:#fff;height:100%}
.customer-explain-box{border:1px solid #dbeafe;background:#eff6ff;border-radius:20px;padding:18px;color:#1e3a8a}
.effect-box{border:1px solid #bbf7d0;background:#f0fdf4;border-radius:20px;padding:18px;color:#166534}
.disabled-box{border:1px solid #fecaca;background:#fef2f2;border-radius:20px;padding:18px;color:#991b1b}
@media(max-width:991px){.module-control-grid,.preset-grid{grid-template-columns:1fr;max-height:none}}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <div class="erp-kicker">Business Bundle Control</div>
    <h2 class="h4 mb-1">Module Bundle Manager</h2>
    <p class="text-secondary mb-0">Control exactly which ERP priorities/modules the customer can see and open.</p>
  </div>
  <span class="badge bg-danger">Developer-only control</span>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Active Bundle</div><strong><?php echo esc(setting('selected_module_bundle_label','Custom Module Selection')); ?></strong></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Enabled Modules</div><div class="metric-sm"><?php echo (int)$enabledCount; ?>/<?php echo (int)$totalCount; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Hidden Modules</div><div class="metric-sm"><?php echo (int)$disabledCount; ?></div></div></div>
  <div class="col-md-3"><div class="card-admin p-4"><div class="erp-kicker">Real Effect</div><span class="badge bg-<?php echo setting('module_bundle_enforcement_enabled','1')==='1'?'success':'warning'; ?>"><?php echo setting('module_bundle_enforcement_enabled','1')==='1'?'Enforced':'Preview only'; ?></span></div></div>
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card-admin p-4 mb-4">
      <h2 class="h5 mb-3">Quick Apply Bundle</h2>
      <div class="preset-grid">
        <?php foreach($bundlePresets as $key=>$preset): ?>
          <form method="post" class="preset-card">
            <input type="hidden" name="action" value="apply_preset">
            <input type="hidden" name="preset_key" value="<?php echo esc($key); ?>">
            <input type="hidden" name="module_bundle_enforcement_enabled" value="<?php echo esc(setting('module_bundle_enforcement_enabled','1')); ?>">
            <input type="hidden" name="module_bundle_hide_disabled_sidebar" value="<?php echo esc(setting('module_bundle_hide_disabled_sidebar','1')); ?>">
            <input type="hidden" name="module_bundle_selection_locked" value="<?php echo esc(setting('module_bundle_selection_locked','0')); ?>">
            <strong><?php echo esc($preset['label']); ?></strong>
            <div class="small text-secondary mb-2"><?php echo count($preset['modules']); ?> modules · <?php echo esc($preset['business']); ?></div>
            <button class="btn btn-sm btn-outline-primary w-100">Apply</button>
          </form>
        <?php endforeach; ?>
      </div>
    </div>

    <form method="post" class="card-admin p-4">
      <input type="hidden" name="action" value="save">
      <h2 class="h5 mb-3">Control Settings</h2>
      <label class="form-label">Business Type</label>
      <input class="form-control mb-2" name="business_type" value="<?php echo esc(setting('selected_business_type','automotive')); ?>">
      <label class="form-label">Bundle Key</label>
      <input class="form-control mb-2" name="bundle_key" value="<?php echo esc(setting('selected_module_bundle','custom')); ?>">
      <label class="form-label">Bundle Label</label>
      <input class="form-control mb-3" name="bundle_label" value="<?php echo esc(setting('selected_module_bundle_label','Custom Module Selection')); ?>">

      <label class="form-label">Real Effect / Enforcement</label>
      <select class="form-select mb-2" name="module_bundle_enforcement_enabled">
        <option value="1" <?php echo setting('module_bundle_enforcement_enabled','1')==='1'?'selected':''; ?>>Enforce selected modules</option>
        <option value="0" <?php echo setting('module_bundle_enforcement_enabled','1')==='0'?'selected':''; ?>>Preview only / allow all</option>
      </select>

      <label class="form-label">Sidebar Behavior</label>
      <select class="form-select mb-2" name="module_bundle_hide_disabled_sidebar">
        <option value="1" <?php echo setting('module_bundle_hide_disabled_sidebar','1')==='1'?'selected':''; ?>>Hide disabled modules</option>
        <option value="0" <?php echo setting('module_bundle_hide_disabled_sidebar','1')==='0'?'selected':''; ?>>Do not hide sidebar items</option>
      </select>

      <label class="form-label">Lock Plan</label>
      <select class="form-select mb-3" name="module_bundle_selection_locked">
        <option value="0" <?php echo setting('module_bundle_selection_locked','0')==='0'?'selected':''; ?>>Unlocked</option>
        <option value="1" <?php echo setting('module_bundle_selection_locked','0')==='1'?'selected':''; ?>>Locked</option>
      </select>

      <label class="form-label">Customer Notes</label>
      <textarea class="form-control mb-3" name="module_bundle_customer_notes" rows="4"><?php echo esc(setting('module_bundle_customer_notes','Selected modules control the sidebar and direct ERP access.')); ?></textarea>

      <div class="customer-explain-box small">
        <strong>Customer explanation:</strong>
        <div class="mt-2">Use this page to decide what the customer will use later. The selected modules become the customer's actual ERP scope. Disabled modules are removed from the navigation and blocked from direct ERP access when enforcement is enabled. If you add modules to a Website Sales Only installation, the system automatically switches it to a custom bundle so admin can see the selected ERP modules.</div>
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <form method="post" class="card-admin p-4 mb-4" id="moduleControlForm">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="business_type" value="<?php echo esc(setting('selected_business_type','automotive')); ?>">
      <input type="hidden" name="bundle_key" value="<?php echo esc(setting('selected_module_bundle','custom')); ?>">
      <input type="hidden" name="bundle_label" value="<?php echo esc(setting('selected_module_bundle_label','Custom Module Selection')); ?>">
      <input type="hidden" name="module_bundle_enforcement_enabled" value="<?php echo esc(setting('module_bundle_enforcement_enabled','1')); ?>">
      <input type="hidden" name="module_bundle_hide_disabled_sidebar" value="<?php echo esc(setting('module_bundle_hide_disabled_sidebar','1')); ?>">
      <input type="hidden" name="module_bundle_selection_locked" value="<?php echo esc(setting('module_bundle_selection_locked','0')); ?>">
      <input type="hidden" name="module_bundle_customer_notes" value="<?php echo esc(setting('module_bundle_customer_notes','Selected modules control the sidebar and direct ERP access.')); ?>">

      <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <div>
          <h2 class="h5 mb-1">Enable / Disable Modules</h2>
          <p class="text-secondary mb-0">Save to apply real sidebar and direct-access effect.</p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('#moduleControlForm input[type=checkbox]').forEach(x=>x.checked=true)">Select All</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('#moduleControlForm input[type=checkbox]').forEach(x=>x.checked=false)">Clear</button>
          <button class="btn btn-sm btn-brand">Save Active Modules</button>
        </div>
      </div>

      <div class="module-control-grid">
        <?php foreach($moduleCatalog as $code=>$info): $enabled=in_array($code,$currentModules,true); ?>
          <label class="module-control-card <?php echo $enabled?'is-enabled':''; ?>">
            <input type="checkbox" name="enabled_modules[]" value="<?php echo esc($code); ?>" <?php echo $enabled?'checked':''; ?>>
            <span class="module-priority"><?php echo esc($info['priority']); ?></span>
            <span class="module-group-pill"><?php echo esc($info['group']); ?></span>
            <h3 class="h6 mt-2 mb-1"><?php echo esc($info['label']); ?></h3>
            <div class="small text-secondary mb-2"><?php echo esc($info['priority_label']); ?></div>
            <div class="small"><strong>Customer later uses:</strong> <?php echo esc($info['customer_detail']); ?></div>
          </label>
        <?php endforeach; ?>
      </div>
    </form>

    <div class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="effect-box">
          <strong>Enabled module groups</strong>
          <ul class="mb-0 mt-2">
            <?php foreach($currentGroups as $group=>$codes): ?>
              <li><?php echo esc($group); ?> — <?php echo count($codes); ?> module(s)</li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="disabled-box">
          <strong>Real effect rule</strong>
          <div class="mt-2">When enforcement is active, pages connected to disabled modules fail permission checks. The admin sidebar also follows the selected module plan.</div>
          <div class="mt-2"><strong>Current priorities:</strong> <?php echo esc(implode(', ', $currentPriorities) ?: 'None'); ?></div>
        </div>
      </div>
    </div>

    <div class="table-wrap table-responsive mb-4">
      <h2 class="h5 mb-3">Bundle History</h2>
      <table class="table"><thead><tr><th>Bundle</th><th>Business</th><th>Modules</th><th>Status</th></tr></thead><tbody>
      <?php foreach($history as $h): $mods=json_decode((string)$h['selected_modules_json'],true); ?>
        <tr><td><strong><?php echo esc($h['bundle_number']); ?></strong><div class="small text-secondary"><?php echo esc($h['bundle_label']); ?></div></td><td><?php echo esc($h['business_type']); ?></td><td><?php echo is_array($mods)?count($mods):0; ?></td><td><?php echo esc($h['status']); ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div>

    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Latest Enabled Module Items</h2>
      <table class="table"><thead><tr><th>Bundle</th><th>Module</th><th>Priority</th><th>Group</th></tr></thead><tbody>
      <?php foreach($items as $i): ?>
        <tr><td><?php echo esc($i['bundle_number']); ?></td><td><strong><?php echo esc($i['module_label']); ?></strong><div class="small text-secondary"><?php echo esc($i['module_code']); ?></div></td><td><?php echo esc($i['priority_code']); ?></td><td><?php echo esc($i['module_group']); ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    </div>
  </div>
</div>

<?php include dirname(__DIR__).'/footer.php'; ?>