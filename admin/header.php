<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$user = currentUser();
if (!$user) {
    redirect(SITE_URL . '/employee/login.php');
}
$pageTitle = $pageTitle ?? 'Admin';
$currentPath = $_SERVER['PHP_SELF'] ?? '';
$active = static fn(string $needle): string => str_contains($currentPath, $needle) ? 'active' : '';
$isAdmin = (($user['role'] ?? '') === 'admin');
$can = static fn(string $permission): bool => hasPermission($permission);
$selectedModulesForHeader = selectedAppModules();
$selectedRealModulesForHeader = array_values(array_filter($selectedModulesForHeader, static fn($m) => $m !== '__all__'));
$websiteSalesOnly = setting('website_sales_only_mode','0') === '1'
    && count($selectedRealModulesForHeader) === 1
    && in_array('website_sales', $selectedRealModulesForHeader, true);
$hideDisabledSidebar = setting('module_bundle_hide_disabled_sidebar','1') === '1';
$showWebsiteSalesModule = !$hideDisabledSidebar || appAnyModuleEnabled(['website_sales','website_storefront','homepage_cms','seo_frontend_settings','advanced_ecommerce']);
$isModuleBundleDeveloper = isModuleBundleDeveloper();
?>
<!doctype html>
<html lang="<?php echo esc(siteLanguage()); ?>" dir="<?php echo esc(siteDirection()); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc($pageTitle); ?> | <?php echo esc(SHOP_NAME); ?> Back Office</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo esc(ADMIN_URL); ?>/assets/css/admin.css">
<style>



/* Clean responsive sidebar: click section titles to open/close */
.admin-mobile-toggle {
    display: none;
    position: fixed;
    top: 12px;
    left: 12px;
    z-index: 1301;
    border: 0;
    border-radius: 12px;
    padding: 10px 12px;
    background: #111827;
    color: #fff;
    box-shadow: 0 8px 22px rgba(0,0,0,.22);
    font-weight: 800;
}
.admin-sidebar-overlay {
    display: none;
}
.admin-sidebar-section-toggle {
    cursor: pointer !important;
    user-select: none;
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding-right: 10px !important;
}
.admin-sidebar-section-toggle::after {
    content: "⌄";
    font-size: 13px;
    opacity: .85;
    transition: transform .18s ease;
}
.admin-sidebar-section-toggle.is-collapsed::after {
    transform: rotate(-90deg);
}
.admin-sidebar-section-collapsed {
    display: none !important;
}
@media (max-width: 991.98px) {
    body {
        overflow-x: hidden;
    }
    .admin-mobile-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .admin-sidebar,
    .sidebar,
    aside[class*="sidebar"],
    .admin-aside {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: min(86vw, 320px) !important;
        max-width: 320px !important;
        height: 100vh !important;
        overflow-y: auto !important;
        z-index: 1300 !important;
        transform: translateX(-105%) !important;
        transition: transform .25s ease !important;
        box-shadow: 20px 0 45px rgba(15,23,42,.35) !important;
    }
    body.admin-sidebar-open .admin-sidebar,
    body.admin-sidebar-open .sidebar,
    body.admin-sidebar-open aside[class*="sidebar"],
    body.admin-sidebar-open .admin-aside {
        transform: translateX(0) !important;
    }
    .admin-sidebar-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.55);
        z-index: 1299;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }
    body.admin-sidebar-open .admin-sidebar-overlay {
        opacity: 1;
        pointer-events: auto;
    }
    .admin-main,
    .main-content,
    main,
    .content-wrapper,
    .admin-content {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 14px !important;
        padding-right: 14px !important;
        padding-top: 64px !important;
    }
    .container-fluid {
        max-width: 100% !important;
    }
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
}


/* Sidebar nested section click fix */
.admin-sidebar-section-toggle {
    cursor: pointer !important;
    user-select: none;
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.admin-sidebar-section-toggle::after {
    content: "⌄";
    font-size: 13px;
    opacity: .85;
    transition: transform .18s ease;
}
.admin-sidebar-section-toggle.is-collapsed::after {
    transform: rotate(-90deg);
}
.admin-sidebar-section-collapsed {
    display: none !important;
}

</style>
</head>
<body>

<button type="button" class="admin-mobile-toggle" id="adminSidebarToggle" aria-label="Open admin menu" aria-expanded="false">
  ☰ <span>Menu</span>
</button>
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>







<div class="container-fluid admin-shell">
<?php renderLicenseAdminNotice(); ?>
<div class="row g-0">
<aside class="col-xl-2 col-lg-3 admin-sidebar">
    <a class="brand" href="<?php echo esc($websiteSalesOnly ? ADMIN_URL . '/dashboard.php' : ($can('dashboard') ? ADMIN_URL . '/erp/dashboard.php' : SITE_URL . '/employee/dashboard.php')); ?>">
        <span class="brand-mark">EW</span>
        <span><strong><?php echo t('E-commerce','التجارة الإلكترونية'); ?></strong><small><?php echo t('ERP Commerce Suite','نظام ERP تجاري متكامل'); ?></small></span>
    </a>
    <div class="sidebar-chip"><?php echo $websiteSalesOnly ? t('Website Sales Mode','وضع مبيعات الموقع') : ($isAdmin ? t('Administrator Access','دخول المدير') : t('Role-Based Employee Access','دخول الموظف حسب الدور')); ?> · <?php echo appModuleEnabled('__all__') ? t('All Modules','كل الوحدات') : count(selectedAppModules()) . ' ' . t('Module(s)','وحدة'); ?></div>
    <nav class="nav flex-column sidebar-nav">
        <?php if ($showWebsiteSalesModule && ($isAdmin || $can('online_products') || $can('online_sales_orders'))): ?>
        <div class="nav-section"><?php echo t('Website Sales', 'مبيعات الموقع'); ?></div>
        <?php if ($isAdmin): ?><a class="nav-link <?php echo $active('/admin/dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/dashboard.php"><?php echo t('Overview', 'نظرة عامة'); ?></a><?php endif; ?>
        <?php if ($isAdmin || $can('online_products')): ?>
        <a class="nav-link <?php echo $active('/admin/products.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/products.php"><?php echo t('Products', 'المنتجات'); ?></a>
        <a class="nav-link <?php echo $active('/admin/categories.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/categories.php"><?php echo t('Categories', 'الأقسام'); ?></a>
        <?php endif; ?>
        <?php if ($isAdmin || $can('online_sales_orders')): ?>
        <a class="nav-link <?php echo $active('/admin/orders.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/orders.php"><?php echo t('Online Orders', 'طلبات الموقع'); ?></a>
        <a class="nav-link <?php echo $active('/admin/bookings.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/bookings.php"><?php echo t('Bookings', 'الحجوزات'); ?></a>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
        <a class="nav-link <?php echo $active('/admin/users.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/users.php"><?php echo t('Users', 'المستخدمون'); ?></a>
        <a class="nav-link <?php echo $active('/admin/homepage.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/homepage.php"><?php echo t('Homepage Builder', 'منشئ الصفحة الرئيسية'); ?></a>
        <a class="nav-link <?php echo $active('/admin/blog'); ?>" href="<?php echo esc(ADMIN_URL); ?>/blog.php"><?php echo t('Content', 'المحتوى'); ?></a>
        <a class="nav-link <?php echo $active('/admin/pages'); ?>" href="<?php echo esc(ADMIN_URL); ?>/pages.php"><?php echo t('HTML Pages', 'صفحات HTML'); ?></a>
        <a class="nav-link <?php echo $active('/activation-loader.php'); ?>" href="<?php echo esc(SITE_URL); ?>/activation-loader.php"><?php echo t('Activation', 'التفعيل'); ?></a>
        <a class="nav-link <?php echo $active('/admin/settings.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/settings.php"><?php echo t('Settings', 'الإعدادات'); ?></a>
        <a class="nav-link <?php echo $active('/admin/translations.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/translations.php"><?php echo t('Translation', 'الترجمة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/encrypted-database-tools.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/encrypted-database-tools.php"><?php echo t('Encrypted DB Tools', 'أدوات قاعدة البيانات المشفرة'); ?></a>
        <?php if ($isModuleBundleDeveloper): ?><a class="nav-link <?php echo $active('/erp/module-bundle-manager.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/module-bundle-manager.php"><?php echo t('Module Bundle', 'حزمة الوحدات'); ?></a><?php endif; ?>
        <a class="nav-link <?php echo $active('/erp/ecommerce-permission-manager.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ecommerce-permission-manager.php"><?php echo t('E-commerce Permissions', 'صلاحيات المتجر الإلكتروني'); ?></a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (!$websiteSalesOnly && $can('access_erp')): ?>
        <div class="nav-section mt-3"><?php echo t('ERP Command', 'لوحة تحكم ERP'); ?></div>
        <?php if ($can('dashboard')): ?><a class="nav-link <?php echo $active('/erp/dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/dashboard.php"><?php echo t('Command Center', 'مركز القيادة'); ?></a><?php endif; ?>
        <?php if ($can('crm')): ?><a class="nav-link <?php echo $active('/erp/crm.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/crm.php"><?php echo t('CRM', 'إدارة العملاء CRM'); ?></a><?php endif; ?>
        <?php if ($can('crm_advanced')): ?><a class="nav-link <?php echo $active('/erp/advanced-crm.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/advanced-crm.php"><?php echo t('Advanced CRM', 'CRM متقدم'); ?></a><?php endif; ?>
        <?php if ($can('sales_crm_dashboard')): ?><a class="nav-link <?php echo $active('/erp/sales-crm-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-crm-dashboard.php"><?php echo t('Sales CRM 2.0', 'CRM المبيعات 2.0'); ?></a><?php endif; ?>
        <?php if ($can('sales_opportunities_2')): ?><a class="nav-link <?php echo $active('/erp/sales-opportunities.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-opportunities.php"><?php echo t('Opportunities 2.0', 'الفرص 2.0'); ?></a><?php endif; ?>
        <?php if ($can('crm_followups')): ?><a class="nav-link <?php echo $active('/erp/crm-followups.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/crm-followups.php"><?php echo t('CRM Follow-ups', 'متابعات CRM'); ?></a><?php endif; ?>
        <?php if ($can('quote_followups')): ?><a class="nav-link <?php echo $active('/erp/quote-followups.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/quote-followups.php"><?php echo t('Quote Follow-ups', 'متابعات عروض الأسعار'); ?></a><?php endif; ?>
        <?php if ($can('sales_forecast')): ?><a class="nav-link <?php echo $active('/erp/sales-forecast.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-forecast.php"><?php echo t('Sales Forecast', 'توقعات المبيعات'); ?></a><?php endif; ?>
        <?php if ($can('campaign_automation_2')): ?><a class="nav-link <?php echo $active('/erp/campaign-automation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/campaign-automation.php"><?php echo t('Campaign Automation 2.0', 'أتمتة الحملات 2.0'); ?></a><?php endif; ?>

        <?php if ($can('sales_pipeline')): ?><a class="nav-link <?php echo $active('/erp/sales-pipeline.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-pipeline.php"><?php echo t('Sales Pipeline', 'مسار المبيعات'); ?></a><?php endif; ?>
        <?php if ($can('marketing_campaigns')): ?><a class="nav-link <?php echo $active('/erp/campaigns.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/campaigns.php"><?php echo t('Campaigns', 'الحملات'); ?></a><?php endif; ?>
        <?php if ($can('lead_scoring')): ?><a class="nav-link <?php echo $active('/erp/lead-scoring.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/lead-scoring.php"><?php echo t('Lead Scoring', 'تقييم العملاء المحتملين'); ?></a><?php endif; ?>
        <?php if ($can('customer_segments')): ?><a class="nav-link <?php echo $active('/erp/customer-segments.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-segments.php"><?php echo t('Segments', 'الشرائح'); ?></a><?php endif; ?>
        <?php if ($can('crm_automation')): ?><a class="nav-link <?php echo $active('/erp/crm-automation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/crm-automation.php"><?php echo t('CRM Automation', 'أتمتة CRM'); ?></a><?php endif; ?>
        <?php if ($can('customers')): ?><a class="nav-link <?php echo $active('/erp/customers.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customers.php"><?php echo t('Customers', 'العملاء'); ?></a><?php endif; ?>
        <?php if ($can('quotations')): ?><a class="nav-link <?php echo $active('/erp/quotations'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/quotations.php"><?php echo t('Quotations', 'عروض الأسعار'); ?></a><?php endif; ?>
        <?php if ($can('sales_orders')): ?><a class="nav-link <?php echo $active('/erp/sales-orders.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-orders.php"><?php echo t('Sales Orders', 'أوامر البيع'); ?></a><?php endif; ?>
        <?php if ($can('delivery_notes')): ?><a class="nav-link <?php echo $active('/erp/delivery-notes.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/delivery-notes.php"><?php echo t('Delivery Notes', 'أذون التسليم'); ?></a><?php endif; ?>
        <?php if ($can('returns_rma')): ?><a class="nav-link <?php echo $active('/erp/returns-rma.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/returns-rma.php"><?php echo t('Returns & RMA', 'المرتجعات و RMA'); ?></a><?php endif; ?>
        <?php if ($can('invoices')): ?><a class="nav-link <?php echo $active('/erp/invoices'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/invoices.php"><?php echo t('Invoices & Receivables', 'الفواتير والتحصيلات'); ?></a><?php endif; ?>
        <?php if ($can('credit_control')): ?><a class="nav-link <?php echo $active('/erp/credit-control.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/credit-control.php"><?php echo t('Credit Control', 'التحكم الائتماني'); ?></a><?php endif; ?>
        <?php if ($can('job_cards') || $can('technician_timesheets') || $can('service_contracts') || $can('warranty_claims')): ?>
        <div class="nav-section mt-3"><?php echo t('Service Operations', 'عمليات الخدمة'); ?></div>
        <?php if ($can('job_cards')): ?><a class="nav-link <?php echo $active('/erp/job-cards.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/job-cards.php"><?php echo t('Job Cards', 'كروت العمل'); ?></a><?php endif; ?>
        <?php if ($can('technician_timesheets')): ?><a class="nav-link <?php echo $active('/erp/technician-timesheets.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/technician-timesheets.php"><?php echo t('Technician Timesheets', 'ساعات الفنيين'); ?></a><?php endif; ?>
        <?php if ($can('service_contracts')): ?><a class="nav-link <?php echo $active('/erp/service-contracts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/service-contracts.php"><?php echo t('Service Contracts', 'عقود الخدمة'); ?></a><?php endif; ?>
        <?php if ($can('warranty_claims')): ?><a class="nav-link <?php echo $active('/erp/warranty-claims.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/warranty-claims.php"><?php echo t('Warranty Claims', 'مطالبات الضمان'); ?></a><?php endif; ?>
        <?php endif; ?>
        <?php if ($can('finance_automation_dashboard')): ?><a class="nav-link <?php echo $active('/erp/finance-automation-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/finance-automation-dashboard.php"><?php echo t('Finance 2.0', 'المالية 2.0'); ?></a><?php endif; ?>
        <?php if ($can('recurring_journals')): ?><a class="nav-link <?php echo $active('/erp/recurring-journals.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/recurring-journals.php"><?php echo t('Recurring Journals', 'قيود متكررة'); ?></a><?php endif; ?>
        <?php if ($can('budgeting')): ?><a class="nav-link <?php echo $active('/erp/budgeting.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/budgeting.php"><?php echo t('Budgeting', 'الموازنات'); ?></a><?php endif; ?>
        <?php if ($can('cash_flow_forecast')): ?><a class="nav-link <?php echo $active('/erp/cash-flow-forecast.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/cash-flow-forecast.php"><?php echo t('Cash Flow', 'التدفق النقدي'); ?></a><?php endif; ?>
        <?php if ($can('ar_ap_aging')): ?><a class="nav-link <?php echo $active('/erp/ar-ap-aging.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ar-ap-aging.php"><?php echo t('AR/AP Aging', 'أعمار العملاء والموردين'); ?></a><?php endif; ?>
        <?php if ($can('supplier_payment_runs')): ?><a class="nav-link <?php echo $active('/erp/supplier-payment-runs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-payment-runs.php"><?php echo t('Payment Runs', 'دفعات السداد'); ?></a><?php endif; ?>
        <?php if ($can('tax_automation_2')): ?><a class="nav-link <?php echo $active('/erp/tax-automation-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/tax-automation-2.php"><?php echo t('Tax Auto 2.0', 'الضرائب الآلية 2.0'); ?></a><?php endif; ?>
                <?php if ($can('finance')): ?><a class="nav-link <?php echo $active('/erp/finance.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/finance.php"><?php echo t('Finance', 'المالية'); ?></a><?php endif; ?>
        <?php if ($can('approvals')): ?><a class="nav-link <?php echo $active('/erp/approvals.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/approvals.php"><?php echo t('Approvals', 'الموافقات'); ?></a><?php endif; ?>
        <?php if ($can('approval_rules')): ?><a class="nav-link <?php echo $active('/erp/approval-rules.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/approval-rules.php"><?php echo t('Approval Rules', 'قواعد الموافقات'); ?></a><?php endif; ?>
        <?php if ($can('intercompany')): ?><a class="nav-link <?php echo $active('/erp/intercompany-transactions.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/intercompany-transactions.php"><?php echo t('Intercompany', 'بين الشركات'); ?></a><?php endif; ?>
        <?php if ($can('reports')): ?><a class="nav-link <?php echo $active('/erp/company-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/company-dashboard.php"><?php echo t('Company Dashboard', 'لوحة الشركة'); ?></a><?php endif; ?>
        <?php if ($can('consolidation')): ?><a class="nav-link <?php echo $active('/erp/branch-financial-consolidation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/branch-financial-consolidation.php"><?php echo t('Financial Consolidation', 'التجميع المالي'); ?></a><?php endif; ?>
        <?php if ($isAdmin || $can('executive_bi') || $can('report_builder') || $can('data_import_export') || $can('notifications') || $can('api_keys') || $can('audit_trail')): ?>
        <div class="nav-section mt-3"><?php echo t('BI & Automation', 'ذكاء الأعمال والأتمتة'); ?></div>
        <?php if ($can('executive_bi')): ?><a class="nav-link <?php echo $active('/erp/executive-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/executive-dashboard.php"><?php echo t('Executive BI', 'ذكاء الأعمال التنفيذي'); ?></a><?php endif; ?>
        <?php if ($can('report_builder')): ?><a class="nav-link <?php echo $active('/erp/report-builder.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/report-builder.php"><?php echo t('Report Builder', 'منشئ التقارير'); ?></a><?php endif; ?>
        <?php if ($can('data_import_export')): ?><a class="nav-link <?php echo $active('/erp/data-import-export.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/data-import-export.php"><?php echo t('Import / Export', 'استيراد / تصدير'); ?></a><?php endif; ?>
        <?php if ($isAdmin || $can('data_import_export')): ?><a class="nav-link <?php echo $active('/erp/encrypted-database-tools.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/encrypted-database-tools.php"><?php echo t('Encrypted DB Tools', 'أدوات قاعدة البيانات المشفرة'); ?></a><?php endif; ?>
        <?php if ($can('notifications')): ?><a class="nav-link <?php echo $active('/erp/notifications.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/notifications.php">Notifications <?php $notifCount=unreadNotificationCount(getDB(),$user); if($notifCount>0): ?><span class="badge bg-danger"><?php echo (int)$notifCount; ?></span><?php endif; ?></a><?php endif; ?>
        <?php if ($can('warehouse_dashboard')): ?><a class="nav-link <?php echo $active('/erp/warehouse-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/warehouse-dashboard.php"><?php echo t('Warehouse', 'المخزن'); ?></a><?php endif; ?>
        <?php if ($can('bin_locations')): ?><a class="nav-link <?php echo $active('/erp/bin-locations.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/bin-locations.php"><?php echo t('Bins', 'المواقع التخزينية'); ?></a><?php endif; ?>
        <?php if ($can('lot_serial_tracking')): ?><a class="nav-link <?php echo $active('/erp/lot-serial-tracking.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/lot-serial-tracking.php"><?php echo t('Lots / Serials', 'الدفعات / الأرقام التسلسلية'); ?></a><?php endif; ?>
        <?php if ($can('stock_counts')): ?><a class="nav-link <?php echo $active('/erp/stock-counts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/stock-counts.php"><?php echo t('Stock Counts', 'جرد المخزون'); ?></a><?php endif; ?>
        <?php if ($can('inventory_adjustments')): ?><a class="nav-link <?php echo $active('/erp/inventory-adjustments.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/inventory-adjustments.php"><?php echo t('Adjustments', 'تسويات المخزون'); ?></a><?php endif; ?>
        <?php if ($can('picking_packing')): ?><a class="nav-link <?php echo $active('/erp/picking-packing.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/picking-packing.php"><?php echo t('Picking / Packing', 'التجهيز والتعبئة'); ?></a><?php endif; ?>
        <?php if ($can('warehouse_dispatch')): ?><a class="nav-link <?php echo $active('/erp/warehouse-dispatch.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/warehouse-dispatch.php"><?php echo t('Dispatch', 'الشحن والتوزيع'); ?></a><?php endif; ?>
        <?php if ($can('replenishment')): ?><a class="nav-link <?php echo $active('/erp/replenishment.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/replenishment.php"><?php echo t('Replenishment', 'إعادة التوريد'); ?></a><?php endif; ?>
        <?php if ($can('manufacturing_dashboard')): ?><a class="nav-link <?php echo $active('/erp/manufacturing-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/manufacturing-dashboard.php"><?php echo t('Manufacturing', 'التصنيع'); ?></a><?php endif; ?>
        <?php if ($can('bom_management')): ?><a class="nav-link <?php echo $active('/erp/bom.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/bom.php"><?php echo t('BOM', 'قائمة المواد'); ?></a><?php endif; ?>
        <?php if ($can('work_orders')): ?><a class="nav-link <?php echo $active('/erp/manufacturing-work-orders.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/manufacturing-work-orders.php"><?php echo t('Work Orders', 'أوامر التشغيل'); ?></a><?php endif; ?>
        <?php if ($can('production_planning')): ?><a class="nav-link <?php echo $active('/erp/production-planning.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/production-planning.php"><?php echo t('Production Planning', 'تخطيط الإنتاج'); ?></a><?php endif; ?>
        <?php if ($can('material_issue')): ?><a class="nav-link <?php echo $active('/erp/material-issue.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/material-issue.php"><?php echo t('Material Issue', 'صرف المواد'); ?></a><?php endif; ?>
        <?php if ($can('production_receipts')): ?><a class="nav-link <?php echo $active('/erp/production-receipts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/production-receipts.php"><?php echo t('Production Receipts', 'استلام الإنتاج'); ?></a><?php endif; ?>
        <?php if ($can('manufacturing_costing')): ?><a class="nav-link <?php echo $active('/erp/manufacturing-costing.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/manufacturing-costing.php"><?php echo t('Costing', 'التكلفة'); ?></a><?php endif; ?>
        <?php if ($can('quality_checks')): ?><a class="nav-link <?php echo $active('/erp/quality-checks.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/quality-checks.php"><?php echo t('Quality', 'الجودة'); ?></a><?php endif; ?>
        <?php if ($can('work_centers')): ?><a class="nav-link <?php echo $active('/erp/work-centers.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/work-centers.php"><?php echo t('Work Centers', 'مراكز العمل'); ?></a><?php endif; ?>
        <?php if ($can('ai_automation_dashboard')): ?><a class="nav-link <?php echo $active('/erp/ai-automation-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ai-automation-dashboard.php"><?php echo t('AI Automation 2.0', 'أتمتة الذكاء الاصطناعي 2.0'); ?></a><?php endif; ?>
        <?php if ($can('decision_engine_2')): ?><a class="nav-link <?php echo $active('/erp/decision-engine-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/decision-engine-2.php"><?php echo t('Decision Engine', 'محرك القرارات'); ?></a><?php endif; ?>
        <?php if ($can('ai_risk_scoring')): ?><a class="nav-link <?php echo $active('/erp/ai-risk-scoring.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ai-risk-scoring.php"><?php echo t('Risk Scoring', 'تقييم المخاطر'); ?></a><?php endif; ?>
        <?php if ($can('smart_action_suggestions')): ?><a class="nav-link <?php echo $active('/erp/action-suggestions.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/action-suggestions.php"><?php echo t('Action Suggestions', 'اقتراحات الإجراءات'); ?></a><?php endif; ?>
        <?php if ($can('ai_assistant_2')): ?><a class="nav-link <?php echo $active('/erp/smart-assistant-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/smart-assistant-2.php"><?php echo t('Smart Assistant 2.0', 'المساعد الذكي 2.0'); ?></a><?php endif; ?>
        <?php if ($can('ai_assistant')): ?><a class="nav-link <?php echo $active('/erp/ai-assistant.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ai-assistant.php"><?php echo t('AI Assistant', 'مساعد الذكاء الاصطناعي'); ?></a><?php endif; ?>
        <?php if ($can('smart_search')): ?><a class="nav-link <?php echo $active('/erp/smart-search.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/smart-search.php"><?php echo t('Smart Search', 'البحث الذكي'); ?></a><?php endif; ?>
        <?php if ($can('predictive_alerts')): ?><a class="nav-link <?php echo $active('/erp/predictive-alerts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/predictive-alerts.php"><?php echo t('Predictive Alerts', 'تنبيهات تنبؤية'); ?></a><?php endif; ?>
        <?php if ($can('recommendations')): ?><a class="nav-link <?php echo $active('/erp/recommendations.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/recommendations.php"><?php echo t('Recommendations', 'التوصيات'); ?></a><?php endif; ?>
        <?php if ($can('decision_support')): ?><a class="nav-link <?php echo $active('/erp/decision-support.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/decision-support.php"><?php echo t('Decision Support', 'دعم القرار'); ?></a><?php endif; ?>
        <?php if ($can('anomaly_detection')): ?><a class="nav-link <?php echo $active('/erp/anomaly-detection.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/anomaly-detection.php"><?php echo t('Anomaly Detection', 'كشف الحالات الشاذة'); ?></a><?php endif; ?>
        <?php if ($can('api_dashboard_2')): ?><a class="nav-link <?php echo $active('/erp/api-dashboard-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/api-dashboard-2.php"><?php echo t('API Dashboard 2.0', 'لوحة API 2.0'); ?></a><?php endif; ?>
        <?php if ($can('api_endpoint_catalog')): ?><a class="nav-link <?php echo $active('/erp/api-endpoint-catalog.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/api-endpoint-catalog.php"><?php echo t('API Endpoints', 'نقاط API'); ?></a><?php endif; ?>
        <?php if ($can('api_usage_limits')): ?><a class="nav-link <?php echo $active('/erp/api-usage-limits.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/api-usage-limits.php"><?php echo t('API Usage Limits', 'حدود استخدام API'); ?></a><?php endif; ?>
        <?php if ($can('webhook_builder_2')): ?><a class="nav-link <?php echo $active('/erp/webhook-builder-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/webhook-builder-2.php"><?php echo t('Webhook Builder 2.0', 'منشئ Webhook 2.0'); ?></a><?php endif; ?>
        <?php if ($can('integration_connectors_2')): ?><a class="nav-link <?php echo $active('/erp/integration-connectors-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/integration-connectors-2.php"><?php echo t('Connectors 2.0', 'الموصلات 2.0'); ?></a><?php endif; ?>
        <?php if ($can('integration_field_mappings')): ?><a class="nav-link <?php echo $active('/erp/integration-field-mappings.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/integration-field-mappings.php"><?php echo t('Field Mappings', 'ربط الحقول'); ?></a><?php endif; ?>
        <?php if ($can('integration_error_logs')): ?><a class="nav-link <?php echo $active('/erp/integration-error-logs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/integration-error-logs.php"><?php echo t('Integration Errors', 'أخطاء التكامل'); ?></a><?php endif; ?>
        <?php if ($can('api_docs_2')): ?><a class="nav-link <?php echo $active('/erp/api-docs-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/api-docs-2.php"><?php echo t('API Docs 2.0', 'توثيق API 2.0'); ?></a><?php endif; ?>
        <?php if ($can('marketplace_sync')): ?><a class="nav-link <?php echo $active('/erp/marketplace-sync.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/marketplace-sync.php"><?php echo t('Marketplace Sync', 'مزامنة السوق'); ?></a><?php endif; ?>

        <?php if ($can('api_keys')): ?><a class="nav-link <?php echo $active('/erp/api-keys.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/api-keys.php"><?php echo t('API Keys', 'مفاتيح API'); ?></a><?php endif; ?>
        <?php if ($can('api_marketplace')): ?><a class="nav-link <?php echo $active('/erp/api-marketplace.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/api-marketplace.php"><?php echo t('API Marketplace', 'سوق API'); ?></a><?php endif; ?>
        <?php if ($can('integrations')): ?><a class="nav-link <?php echo $active('/erp/integrations.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/integrations.php"><?php echo t('Integrations', 'التكاملات'); ?></a><?php endif; ?>
        <?php if ($can('webhooks')): ?><a class="nav-link <?php echo $active('/erp/webhooks.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/webhooks.php"><?php echo t('Webhooks', 'Webhooks'); ?></a><?php endif; ?>
        <?php if ($can('whatsapp_automation')): ?><a class="nav-link <?php echo $active('/erp/whatsapp-automation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/whatsapp-automation.php"><?php echo t('WhatsApp', 'واتساب'); ?></a><?php endif; ?>
        <?php if ($can('communication_automation')): ?><a class="nav-link <?php echo $active('/erp/communication-automation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/communication-automation.php"><?php echo t('Comms Automation', 'أتمتة التواصل'); ?></a><?php endif; ?>
        <?php if ($can('developer_docs')): ?><a class="nav-link <?php echo $active('/erp/developer-docs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/developer-docs.php"><?php echo t('Developer Docs', 'توثيق المطورين'); ?></a><?php endif; ?>
        <?php if ($can('audit_trail')): ?><a class="nav-link <?php echo $active('/erp/audit-trail.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/audit-trail.php"><?php echo t('Audit Trail', 'سجل التدقيق'); ?></a><?php endif; ?>
        <?php endif; ?>
        <?php if ($can('security_center') || $can('backup_restore') || $can('system_health') || $can('error_logs') || $can('cron_runner') || $can('email_templates') || $can('dashboard_widgets') || $can('deployment_checklist') || $can('migration_center')): ?>
        <div class="nav-section mt-3"><?php echo t('System Hardening', 'تقوية النظام'); ?></div>
        <?php if ($can('security_compliance_dashboard')): ?><a class="nav-link <?php echo $active('/erp/security-compliance-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/security-compliance-dashboard.php"><?php echo t('Security 2.0', 'الأمان 2.0'); ?></a><?php endif; ?>
        <?php if ($can('login_session_monitor')): ?><a class="nav-link <?php echo $active('/erp/login-session-monitor.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/login-session-monitor.php"><?php echo t('Login Sessions', 'جلسات الدخول'); ?></a><?php endif; ?>
        <?php if ($can('permission_change_history')): ?><a class="nav-link <?php echo $active('/erp/permission-change-history.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/permission-change-history.php"><?php echo t('Permission History', 'سجل الصلاحيات'); ?></a><?php endif; ?>
        <?php if ($can('data_export_tracking')): ?><a class="nav-link <?php echo $active('/erp/data-export-tracking.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/data-export-tracking.php"><?php echo t('Export Tracking', 'تتبع التصدير'); ?></a><?php endif; ?>
        <?php if ($can('sensitive_action_approvals')): ?><a class="nav-link <?php echo $active('/erp/sensitive-action-approvals.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sensitive-action-approvals.php"><?php echo t('Sensitive Approvals', 'موافقات حساسة'); ?></a><?php endif; ?>
        <?php if ($can('security_policy_center')): ?><a class="nav-link <?php echo $active('/erp/security-policies.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/security-policies.php"><?php echo t('Security Policies', 'سياسات الأمان'); ?></a><?php endif; ?>
        <?php if ($can('b2b_price_lists')): ?><a class="nav-link <?php echo $active('/erp/b2b-price-lists.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/b2b-price-lists.php"><?php echo t('B2B Price Lists', 'قوائم أسعار B2B'); ?></a><?php endif; ?>
        <?php if ($can('customer_price_rules')): ?><a class="nav-link <?php echo $active('/erp/customer-price-rules.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-price-rules.php"><?php echo t('Customer Price Rules', 'قواعد أسعار العملاء'); ?></a><?php endif; ?>
        <?php if ($can('product_bundles')): ?><a class="nav-link <?php echo $active('/erp/product-bundles.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/product-bundles.php"><?php echo t('Product Bundles', 'باقات المنتجات'); ?></a><?php endif; ?>
        <?php if ($can('digital_license_control')): ?><a class="nav-link <?php echo $active('/erp/digital-license-control.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/digital-license-control.php"><?php echo t('Digital Licenses', 'التراخيص الرقمية'); ?></a><?php endif; ?>
        <?php if ($can('wishlist_control')): ?><a class="nav-link <?php echo $active('/erp/wishlist-control.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/wishlist-control.php"><?php echo t('Wishlists', 'قوائم الرغبات'); ?></a><?php endif; ?>
        <?php if ($can('product_comparison_control')): ?><a class="nav-link <?php echo $active('/erp/product-comparison-control.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/product-comparison-control.php"><?php echo t('Product Compare', 'مقارنة المنتجات'); ?></a><?php endif; ?>
        <?php if ($can('advanced_quote_requests')): ?><a class="nav-link <?php echo $active('/erp/advanced-quote-requests.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/advanced-quote-requests.php"><?php echo t('Quote Requests 2.0', 'طلبات عروض الأسعار 2.0'); ?></a><?php endif; ?>
        <?php if ($can('advanced_ecommerce_settings')): ?><a class="nav-link <?php echo $active('/erp/advanced-ecommerce-settings.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/advanced-ecommerce-settings.php"><?php echo t('Advanced Ecommerce', 'المتجر المتقدم'); ?></a><?php endif; ?>

        <?php if ($can('document_library_2')): ?><a class="nav-link <?php echo $active('/erp/document-library.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-library.php"><?php echo t('Document Library', 'مكتبة المستندات'); ?></a><?php endif; ?>
        <?php if ($can('document_folders')): ?><a class="nav-link <?php echo $active('/erp/document-folders.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-folders.php"><?php echo t('Document Folders', 'مجلدات المستندات'); ?></a><?php endif; ?>
        <?php if ($can('document_versions')): ?><a class="nav-link <?php echo $active('/erp/document-versions.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-versions.php"><?php echo t('Document Versions', 'إصدارات المستندات'); ?></a><?php endif; ?>
        <?php if ($can('document_approvals')): ?><a class="nav-link <?php echo $active('/erp/document-approvals.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-approvals.php"><?php echo t('Document Approvals', 'موافقات المستندات'); ?></a><?php endif; ?>
        <?php if ($can('document_expiry_alerts')): ?><a class="nav-link <?php echo $active('/erp/document-expiry-alerts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-expiry-alerts.php"><?php echo t('Document Expiry', 'انتهاء المستندات'); ?></a><?php endif; ?>
        <?php if ($can('document_access_logs')): ?><a class="nav-link <?php echo $active('/erp/document-access-logs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-access-logs.php"><?php echo t('Document Access Logs', 'سجلات الوصول للمستندات'); ?></a><?php endif; ?>

        <?php if ($can('compliance_checklists')): ?><a class="nav-link <?php echo $active('/erp/compliance-checklists.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/compliance-checklists.php"><?php echo t('Compliance Checklists', 'قوائم الامتثال'); ?></a><?php endif; ?>

        <?php if ($can('security_center')): ?><a class="nav-link <?php echo $active('/erp/security-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/security-center.php"><?php echo t('Security Center', 'مركز الأمان'); ?></a><?php endif; ?>
        <?php if ($can('system_health')): ?><a class="nav-link <?php echo $active('/erp/system-health.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/system-health.php"><?php echo t('System Health', 'صحة النظام'); ?></a><?php endif; ?>
        <?php if ($can('backup_restore')): ?><a class="nav-link <?php echo $active('/erp/backup-restore.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/backup-restore.php"><?php echo t('Backup & Restore', 'النسخ الاحتياطي والاستعادة'); ?></a><?php endif; ?>
        <?php if ($can('error_logs')): ?><a class="nav-link <?php echo $active('/erp/error-logs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/error-logs.php"><?php echo t('Error Logs', 'سجلات الأخطاء'); ?></a><?php endif; ?>
        <?php if ($can('cron_runner')): ?><a class="nav-link <?php echo $active('/erp/cron-runner.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/cron-runner.php"><?php echo t('Cron Runner', 'مشغل Cron'); ?></a><?php endif; ?>
        <?php if ($can('email_templates')): ?><a class="nav-link <?php echo $active('/erp/email-templates.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/email-templates.php"><?php echo t('Email Templates', 'قوالب البريد'); ?></a><?php endif; ?>
        <?php if ($can('dashboard_widgets')): ?><a class="nav-link <?php echo $active('/erp/dashboard-widgets.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/dashboard-widgets.php"><?php echo t('Dashboard Widgets', 'ودجات اللوحة'); ?></a><?php endif; ?>
        <div class="nav-section mt-3"><?php echo t('Documentation & Commercial', 'التوثيق والتجهيز التجاري'); ?></div>        <?php if ($isModuleBundleDeveloper): ?><a class="nav-link <?php echo $active('/erp/module-bundle-manager.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/module-bundle-manager.php"><?php echo t('Module Bundles', 'حزم الوحدات'); ?></a><?php endif; ?>

        <?php if ($can('documentation_center')): ?><a class="nav-link <?php echo $active('/erp/documentation-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/documentation-center.php"><?php echo t('Documentation Center', 'مركز التوثيق'); ?></a><?php endif; ?>
        <?php if ($can('training_center')): ?><a class="nav-link <?php echo $active('/erp/training-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/training-center.php"><?php echo t('Training Center', 'مركز التدريب'); ?></a><?php endif; ?>
        <?php if ($can('demo_credentials')): ?><a class="nav-link <?php echo $active('/erp/demo-credentials.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/demo-credentials.php"><?php echo t('Demo Credentials', 'بيانات الدخول التجريبية'); ?></a><?php endif; ?>
        <?php if ($can('commercial_packaging')): ?><a class="nav-link <?php echo $active('/erp/commercial-packaging.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/commercial-packaging.php"><?php echo t('Commercial Packaging', 'التجهيز التجاري'); ?></a><?php endif; ?>
        <?php if ($can('client_onboarding_checklist')): ?><a class="nav-link <?php echo $active('/erp/client-onboarding-checklist.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/client-onboarding-checklist.php"><?php echo t('Client Onboarding', 'تهيئة العميل'); ?></a><?php endif; ?>
        <?php if ($can('feature_comparison_sheet')): ?><a class="nav-link <?php echo $active('/erp/feature-comparison-sheet.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/feature-comparison-sheet.php"><?php echo t('Feature Comparison', 'مقارنة المميزات'); ?></a><?php endif; ?>
        <?php if ($can('sales_brochure_builder')): ?><a class="nav-link <?php echo $active('/erp/sales-brochure-builder.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/sales-brochure-builder.php"><?php echo t('Sales Brochure', 'بروشور المبيعات'); ?></a><?php endif; ?>
        <?php if ($can('handover_center')): ?><a class="nav-link <?php echo $active('/erp/handover-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/handover-center.php"><?php echo t('Handover Center', 'مركز التسليم'); ?></a><?php endif; ?>

        <?php if ($can('production_hardening_dashboard')): ?><a class="nav-link <?php echo $active('/erp/production-hardening-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/production-hardening-dashboard.php"><?php echo t('Production Hardening', 'تجهيز الإنتاج'); ?></a><?php endif; ?>
        <?php if ($can('system_repair_center')): ?><a class="nav-link <?php echo $active('/erp/system-repair-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/system-repair-center.php"><?php echo t('Repair Center', 'مركز الإصلاح'); ?></a><?php endif; ?>
        <?php if ($can('database_migration_updater')): ?><a class="nav-link <?php echo $active('/erp/database-migration-updater.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/database-migration-updater.php"><?php echo t('Migration Updater', 'محدث الترحيل'); ?></a><?php endif; ?>
        <?php if ($can('installer_health_check')): ?><a class="nav-link <?php echo $active('/erp/installer-health-check.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/installer-health-check.php"><?php echo t('Installer Health', 'صحة المثبت'); ?></a><?php endif; ?>
        <?php if ($can('demo_data_manager')): ?><a class="nav-link <?php echo $active('/erp/demo-data-manager.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/demo-data-manager.php"><?php echo t('Demo Data', 'البيانات التجريبية'); ?></a><?php endif; ?>
        <?php if ($can('permission_repair')): ?><a class="nav-link <?php echo $active('/erp/permission-repair.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/permission-repair.php"><?php echo t('Permission Repair', 'إصلاح الصلاحيات'); ?></a><?php endif; ?>
        <?php if ($can('settings_repair')): ?><a class="nav-link <?php echo $active('/erp/settings-repair.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/settings-repair.php"><?php echo t('Settings Repair', 'إصلاح الإعدادات'); ?></a><?php endif; ?>
        <?php if ($can('table_column_checker')): ?><a class="nav-link <?php echo $active('/erp/table-column-checker.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/table-column-checker.php"><?php echo t('Table Checker', 'فاحص الجداول'); ?></a><?php endif; ?>
        <?php if ($can('production_error_log_viewer')): ?><a class="nav-link <?php echo $active('/erp/error-log-viewer.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/error-log-viewer.php"><?php echo t('Error Log Viewer', 'عارض سجل الأخطاء'); ?></a><?php endif; ?>

        <?php if ($can('deployment_checklist')): ?><a class="nav-link <?php echo $active('/erp/deployment-checklist.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/deployment-checklist.php"><?php echo t('Deployment Checklist', 'قائمة النشر'); ?></a><?php endif; ?>
        <?php if ($can('migration_center')): ?><a class="nav-link <?php echo $active('/erp/migration-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/migration-center.php"><?php echo t('Migration Center', 'مركز الترحيل'); ?></a><?php endif; ?>
        <?php endif; ?>
        <?php if ($can('subscription_plans') || $can('license_center') || $can('update_center') || $can('tenant_usage')): ?>
        <div class="nav-section mt-3"><?php echo t('SaaS & Licensing', 'SaaS والتراخيص'); ?></div>
        <?php if ($can('saas_dashboard_2')): ?><a class="nav-link <?php echo $active('/erp/saas-dashboard-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/saas-dashboard-2.php"><?php echo t('SaaS Dashboard 2.0', 'لوحة SaaS 2.0'); ?></a><?php endif; ?>
        <?php if ($can('tenant_subscriptions_2')): ?><a class="nav-link <?php echo $active('/erp/tenant-subscriptions-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/tenant-subscriptions-2.php"><?php echo t('Tenant Subscriptions', 'اشتراكات العملاء'); ?></a><?php endif; ?>
        <?php if ($can('tenant_billing')): ?><a class="nav-link <?php echo $active('/erp/tenant-billing.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/tenant-billing.php"><?php echo t('Tenant Billing', 'فوترة العملاء'); ?></a><?php endif; ?>
        <?php if ($can('trial_accounts')): ?><a class="nav-link <?php echo $active('/erp/trial-accounts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/trial-accounts.php"><?php echo t('Trial Accounts', 'حسابات تجريبية'); ?></a><?php endif; ?>
        <?php if ($can('plan_module_matrix')): ?><a class="nav-link <?php echo $active('/erp/plan-module-matrix.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/plan-module-matrix.php"><?php echo t('Plan Modules', 'وحدات الخطط'); ?></a><?php endif; ?>
        <?php if ($can('usage_enforcement')): ?><a class="nav-link <?php echo $active('/erp/usage-enforcement.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/usage-enforcement.php"><?php echo t('Usage Enforcement', 'تطبيق حدود الاستخدام'); ?></a><?php endif; ?>
        <?php if ($can('tenant_onboarding')): ?><a class="nav-link <?php echo $active('/erp/tenant-onboarding.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/tenant-onboarding.php"><?php echo t('Tenant Onboarding', 'تهيئة العميل المستأجر'); ?></a><?php endif; ?>

        <?php if ($can('subscription_plans')): ?><a class="nav-link <?php echo $active('/erp/subscription-plans.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/subscription-plans.php"><?php echo t('Subscription Plans', 'خطط الاشتراك'); ?></a><?php endif; ?>
        <?php if ($can('tenant_usage')): ?><a class="nav-link <?php echo $active('/erp/tenant-usage.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/tenant-usage.php"><?php echo t('Tenant Usage', 'استخدام العملاء'); ?></a><?php endif; ?>
        <?php if ($can('license_center')): ?><a class="nav-link <?php echo $active('/erp/license-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/license-center.php"><?php echo t('License Center', 'مركز التراخيص'); ?></a><?php endif; ?>
        <?php if ($can('update_center')): ?><a class="nav-link <?php echo $active('/erp/update-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/update-center.php"><?php echo t('Update Center', 'مركز التحديث'); ?></a><?php endif; ?>
        <?php endif; ?>
        <?php if ($can('customer_portal_admin') || $can('vendor_portal_admin') || $can('technician_portal') || $can('mobile_erp') || $can('field_dispatch') || $can('offline_job_cards') || $can('barcode_qr') || $can('customer_signoff')): ?>
        <div class="nav-section mt-3"><?php echo t('Portals & Mobile', 'البوابات والموبايل'); ?></div>
        <?php if ($can('pwa_settings')): ?><a class="nav-link <?php echo $active('/erp/pwa-settings.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/pwa-settings.php"><?php echo t('PWA Settings', 'إعدادات PWA'); ?></a><?php endif; ?>
        <?php if ($can('mobile_app_readiness')): ?><a class="nav-link <?php echo $active('/erp/mobile-app-readiness.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/mobile-app-readiness.php"><?php echo t('Mobile Readiness', 'جاهزية الموبايل'); ?></a><?php endif; ?>
        <?php if ($can('push_notifications')): ?><a class="nav-link <?php echo $active('/erp/push-notifications.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/push-notifications.php"><?php echo t('Push Notifications', 'إشعارات الدفع'); ?></a><?php endif; ?>
        <?php if ($can('mobile_quick_actions_2')): ?><a class="nav-link <?php echo $active('/erp/mobile-quick-actions-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/mobile-quick-actions-2.php"><?php echo t('Mobile Actions 2.0', 'إجراءات الموبايل 2.0'); ?></a><?php endif; ?>
        <?php if ($can('mobile_offline_sync')): ?><a class="nav-link <?php echo $active('/erp/offline-sync-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/offline-sync-center.php"><?php echo t('Offline Sync', 'مزامنة دون اتصال'); ?></a><?php endif; ?>

        <?php if ($can('mobile_erp')): ?><a class="nav-link <?php echo $active('/erp/mobile-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/mobile-dashboard.php"><?php echo t('Mobile ERP', 'ERP الموبايل'); ?></a><?php endif; ?>
        <?php if ($can('technician_portal')): ?><a class="nav-link <?php echo $active('/erp/technician-mobile.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/technician-mobile.php"><?php echo t('Technician Mobile', 'موبايل الفني'); ?></a><?php endif; ?>
        <?php if ($can('field_dispatch')): ?><a class="nav-link <?php echo $active('/erp/field-dispatch.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/field-dispatch.php"><?php echo t('Field Dispatch', 'توجيه الفرق الميدانية'); ?></a><?php endif; ?>
        <?php if ($can('offline_job_cards')): ?><a class="nav-link <?php echo $active('/erp/offline-job-cards.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/offline-job-cards.php"><?php echo t('Offline Jobs', 'مهام دون اتصال'); ?></a><?php endif; ?>
        <?php if ($can('barcode_qr')): ?><a class="nav-link <?php echo $active('/erp/barcode-qr.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/barcode-qr.php"><?php echo t('Barcode / QR', 'باركود / QR'); ?></a><?php endif; ?>
        <?php if ($can('technician_checklists')): ?><a class="nav-link <?php echo $active('/erp/mobile-checklists.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/mobile-checklists.php"><?php echo t('Mobile Checklists', 'قوائم فحص الموبايل'); ?></a><?php endif; ?>
        <?php if ($can('customer_signoff')): ?><a class="nav-link <?php echo $active('/erp/customer-signoff.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-signoff.php"><?php echo t('Customer Sign-off', 'توقيع العميل'); ?></a><?php endif; ?>
        <?php if ($can('customer_portal_dashboard')): ?><a class="nav-link <?php echo $active('/erp/customer-portal-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-portal-dashboard.php"><?php echo t('Customer 2.0', 'العميل 2.0'); ?></a><?php endif; ?>
        <?php if ($can('customer_documents')): ?><a class="nav-link <?php echo $active('/erp/customer-documents.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-documents.php"><?php echo t('Customer Documents', 'مستندات العميل'); ?></a><?php endif; ?>
        <?php if ($can('customer_feedback')): ?><a class="nav-link <?php echo $active('/erp/customer-feedback.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-feedback.php"><?php echo t('Customer Feedback', 'ملاحظات العميل'); ?></a><?php endif; ?>
        <?php if ($can('customer_announcements')): ?><a class="nav-link <?php echo $active('/erp/customer-announcements.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-announcements.php"><?php echo t('Customer Notices', 'إشعارات العميل'); ?></a><?php endif; ?>
        <?php if ($can('customer_disputes')): ?><a class="nav-link <?php echo $active('/erp/customer-disputes.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-disputes.php"><?php echo t('Customer Disputes', 'نزاعات العميل'); ?></a><?php endif; ?>
        <?php if ($can('customer_portal_admin')): ?><a class="nav-link <?php echo $active('/erp/customer-portal-requests.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/customer-portal-requests.php"><?php echo t('Customer Portal', 'بوابة العميل'); ?></a><?php endif; ?>
        <?php if ($can('vendor_portal_admin')): ?><a class="nav-link <?php echo $active('/erp/vendor-access.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/vendor-access.php"><?php echo t('Vendor Access', 'دخول المورد'); ?></a><?php endif; ?>
        <?php if ($can('technician_portal')): ?><a class="nav-link <?php echo $active('/erp/technician-dispatch.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/technician-dispatch.php"><?php echo t('Technician Dispatch', 'توجيه الفنيين'); ?></a><?php endif; ?>
        <?php endif; ?>
        <?php if ($can('projects') || $can('budget_control') || $can('cost_centers')): ?>
        <div class="nav-section mt-3"><?php echo t('Projects & Budgets', 'المشاريع والموازنات'); ?></div>
        <?php if ($can('projects')): ?><a class="nav-link <?php echo $active('/erp/projects.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/projects.php"><?php echo t('Projects', 'المشاريع'); ?></a><?php endif; ?>
        <?php if ($can('budget_control')): ?><a class="nav-link <?php echo $active('/erp/budget-control.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/budget-control.php"><?php echo t('Budget Control', 'التحكم في الميزانية'); ?></a><?php endif; ?>
        <?php if ($can('cost_centers')): ?><a class="nav-link <?php echo $active('/erp/cost-centers.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/cost-centers.php"><?php echo t('Cost Centers', 'مراكز التكلفة'); ?></a><?php endif; ?>
        <?php endif; ?>
        <?php if ($can('org_structure')): ?>
        <div class="nav-section mt-3"><?php echo t('Organization', 'الهيكل التنظيمي'); ?></div>
        <a class="nav-link <?php echo $active('/erp/companies.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/companies.php"><?php echo t('Companies', 'الشركات'); ?></a>
        <a class="nav-link <?php echo $active('/erp/branches.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/branches.php"><?php echo t('Branches', 'الفروع'); ?></a>
        <a class="nav-link <?php echo $active('/erp/warehouses.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/warehouses.php"><?php echo t('Warehouses', 'المخازن'); ?></a>
        <a class="nav-link <?php echo $active('/erp/document-sequences.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/document-sequences.php"><?php echo t('Document Sequences', 'تسلسل المستندات'); ?></a>
        <a class="nav-link <?php echo $active('/erp/user-branch-access.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/user-branch-access.php"><?php echo t('User Branch Access', 'صلاحيات الفروع للمستخدمين'); ?></a>
        <?php endif; ?>
        <?php if ($can('accounting') || $can('financial_close') || $can('bank_reconciliation') || $can('fixed_assets') || $can('tax_filing') || $can('audit_controls')): ?>
        <div class="nav-section mt-3"><?php echo t('Accounting Core', 'المحاسبة الأساسية'); ?></div>
        <a class="nav-link <?php echo $active('/erp/chart-of-accounts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/chart-of-accounts.php"><?php echo t('Chart of Accounts', 'دليل الحسابات'); ?></a>
        <a class="nav-link <?php echo $active('/erp/account-ledger.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/account-ledger.php"><?php echo t('Account Ledger', 'كشف الحساب'); ?></a>
        <a class="nav-link <?php echo $active('/erp/journal-entries'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/journal-entries.php"><?php echo t('Journal Entries', 'قيود اليومية'); ?></a>
        <a class="nav-link <?php echo $active('/erp/opening-balances.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/opening-balances.php"><?php echo t('Opening Balances', 'الأرصدة الافتتاحية'); ?></a>
        <a class="nav-link <?php echo $active('/erp/accounting-periods.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/accounting-periods.php"><?php echo t('Fiscal Periods', 'الفترات المالية'); ?></a>
        <a class="nav-link <?php echo $active('/erp/financial-close.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/financial-close.php"><?php echo t('Financial Close', 'الإغلاق المالي'); ?></a>
        <a class="nav-link <?php echo $active('/erp/trial-balance.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/trial-balance.php"><?php echo t('Trial Balance', 'ميزان المراجعة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/profit-loss.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/profit-loss.php"><?php echo t('Profit & Loss', 'الأرباح والخسائر'); ?></a>
        <a class="nav-link <?php echo $active('/erp/balance-sheet.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/balance-sheet.php"><?php echo t('Balance Sheet', 'الميزانية العمومية'); ?></a>
        <a class="nav-link <?php echo $active('/erp/ar-customer-ledger.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ar-customer-ledger.php"><?php echo t('AR Customer Ledger', 'دفتر العملاء'); ?></a>
        <a class="nav-link <?php echo $active('/erp/ar-aging.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ar-aging.php"><?php echo t('AR Aging', 'أعمار العملاء'); ?></a>
        <a class="nav-link <?php echo $active('/erp/credit-notes'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/credit-notes.php"><?php echo t('Credit Notes', 'إشعارات دائنة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/ap-supplier-ledger.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ap-supplier-ledger.php"><?php echo t('AP Supplier Ledger', 'دفتر الموردين'); ?></a>
        <a class="nav-link <?php echo $active('/erp/ap-aging.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/ap-aging.php"><?php echo t('AP Aging', 'أعمار الموردين'); ?></a>
        <a class="nav-link <?php echo $active('/erp/debit-notes'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/debit-notes.php"><?php echo t('Debit Notes', 'إشعارات مدينة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/bank-accounts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/bank-accounts.php"><?php echo t('Bank Accounts', 'الحسابات البنكية'); ?></a>
        <a class="nav-link <?php echo $active('/erp/bank-reconciliation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/bank-reconciliation.php"><?php echo t('Bank Reconciliation', 'تسوية البنك'); ?></a>
        <a class="nav-link <?php echo $active('/erp/vat-report.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/vat-report.php"><?php echo t('VAT Report', 'تقرير ضريبة القيمة المضافة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/vat-periods.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/vat-periods.php"><?php echo t('VAT Periods', 'فترات الضريبة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/tax-filing.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/tax-filing.php"><?php echo t('Tax Filing', 'تقديم الإقرار الضريبي'); ?></a>
        <a class="nav-link <?php echo $active('/erp/fixed-assets.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/fixed-assets.php"><?php echo t('Fixed Assets', 'الأصول الثابتة'); ?></a>
        <a class="nav-link <?php echo $active('/erp/audit-controls.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/audit-controls.php"><?php echo t('Audit Controls', 'ضوابط التدقيق'); ?></a>
        <a class="nav-link <?php echo $active('/erp/cash-flow.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/cash-flow.php"><?php echo t('Cash Flow', 'التدفق النقدي'); ?></a>
        <?php endif; ?>

        <?php if ($can('inventory') || $can('stock_transfers') || $can('inventory_valuation') || $can('suppliers') || $can('purchase_requisitions') || $can('purchase_orders') || $can('goods_receipts') || $can('supplier_invoices')): ?>
        <div class="nav-section mt-3"><?php echo t('Supply & Stock', 'التوريد والمخزون'); ?></div>
        <?php if ($can('inventory')): ?><a class="nav-link <?php echo $active('/erp/inventory.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/inventory.php"><?php echo t('Inventory', 'المخزون'); ?></a><a class="nav-link <?php echo $active('/erp/warehouse-stock.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/warehouse-stock.php"><?php echo t('Warehouse Stock', 'مخزون المخزن'); ?></a><?php endif; ?>
        <?php if ($can('inventory_valuation')): ?><a class="nav-link <?php echo $active('/erp/inventory-valuation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/inventory-valuation.php"><?php echo t('Inventory Valuation', 'تقييم المخزون'); ?></a><a class="nav-link <?php echo $active('/erp/in-transit-stock.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/in-transit-stock.php"><?php echo t('In-Transit Stock', 'مخزون بالطريق'); ?></a><?php endif; ?>
        <?php if ($can('stock_transfers')): ?><a class="nav-link <?php echo $active('/erp/stock-transfers.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/stock-transfers.php"><?php echo t('Stock Transfers', 'تحويلات المخزون'); ?></a><?php endif; ?>
        <?php if ($can('procurement_dashboard')): ?><a class="nav-link <?php echo $active('/erp/procurement-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/procurement-dashboard.php"><?php echo t('Procurement', 'المشتريات'); ?></a><?php endif; ?>
        <?php if ($can('supplier_onboarding')): ?><a class="nav-link <?php echo $active('/erp/supplier-onboarding.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-onboarding.php"><?php echo t('Supplier Onboarding', 'تهيئة المورد'); ?></a><?php endif; ?>
        <?php if ($can('supplier_scorecards')): ?><a class="nav-link <?php echo $active('/erp/supplier-scorecards.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-scorecards.php"><?php echo t('Supplier Scorecards', 'تقييم الموردين'); ?></a><?php endif; ?>
        <?php if ($can('supplier_price_lists')): ?><a class="nav-link <?php echo $active('/erp/supplier-price-lists.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-price-lists.php"><?php echo t('Supplier Prices', 'أسعار الموردين'); ?></a><?php endif; ?>
        <?php if ($can('supplier_contracts')): ?><a class="nav-link <?php echo $active('/erp/supplier-contracts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-contracts.php"><?php echo t('Supplier Contracts', 'عقود الموردين'); ?></a><?php endif; ?>
        <?php if ($can('suppliers')): ?><a class="nav-link <?php echo $active('/erp/suppliers.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/suppliers.php"><?php echo t('Suppliers', 'الموردون'); ?></a><?php endif; ?>
        <?php if ($can('purchase_requisitions')): ?><a class="nav-link <?php echo $active('/erp/purchase-requisitions.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/purchase-requisitions.php"><?php echo t('Purchase Requisitions', 'طلبات الشراء'); ?></a><?php endif; ?>
        <?php if ($can('purchase_orders')): ?><a class="nav-link <?php echo $active('/erp/purchase-orders'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/purchase-orders.php"><?php echo t('Purchase Orders', 'أوامر الشراء'); ?></a><?php endif; ?>
        <?php if ($can('rfq_management')): ?><a class="nav-link <?php echo $active('/erp/rfqs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/rfqs.php"><?php echo t('RFQs', 'طلبات التسعير'); ?></a><?php endif; ?>
        <?php if ($can('rfq_comparison')): ?><a class="nav-link <?php echo $active('/erp/rfq-comparison.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/rfq-comparison.php"><?php echo t('RFQ Comparison', 'مقارنة عروض الموردين'); ?></a><?php endif; ?>
        <?php if ($can('tender_management')): ?><a class="nav-link <?php echo $active('/erp/procurement-tenders.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/procurement-tenders.php"><?php echo t('Tenders', 'المناقصات'); ?></a><?php endif; ?>
        <?php if ($can('workflow_builder_2')): ?><a class="nav-link <?php echo $active('/erp/workflow-builder-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/workflow-builder-2.php"><?php echo t('Workflow Builder 2.0', 'منشئ سير العمل 2.0'); ?></a><?php endif; ?>
        <?php if ($can('workflow_run_history_2')): ?><a class="nav-link <?php echo $active('/erp/workflow-run-history-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/workflow-run-history-2.php"><?php echo t('Workflow Runs 2.0', 'تشغيلات سير العمل 2.0'); ?></a><?php endif; ?>
        <?php if ($can('workflow_approval_automation')): ?><a class="nav-link <?php echo $active('/erp/workflow-approval-automation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/workflow-approval-automation.php"><?php echo t('Approval Automation', 'أتمتة الموافقات'); ?></a><?php endif; ?>
        <?php if ($can('workflow_templates_2')): ?><a class="nav-link <?php echo $active('/erp/workflow-templates-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/workflow-templates-2.php"><?php echo t('Workflow Templates', 'قوالب سير العمل'); ?></a><?php endif; ?>

        <?php if ($can('workflow_automation')): ?><a class="nav-link <?php echo $active('/erp/workflow-automation.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/workflow-automation.php"><?php echo t('Automation', 'الأتمتة'); ?></a><?php endif; ?>
        <?php if ($can('goods_receipts')): ?><a class="nav-link <?php echo $active('/erp/goods-receipts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/goods-receipts.php"><?php echo t('Goods Receipt Notes', 'أذون استلام البضاعة'); ?></a><?php endif; ?>
        <?php if ($can('supplier_invoices')): ?><a class="nav-link <?php echo $active('/erp/supplier-invoices.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/supplier-invoices.php"><?php echo t('Supplier Invoices', 'فواتير الموردين'); ?></a><?php endif; ?>
        <?php endif; ?>

        <?php if ($can('hr') || $can('attendance') || $can('payroll') || $can('employee_expenses') || $can('commissions') || $can('performance_management') || $can('reports') || $can('activity_log') || $isAdmin): ?>
        <div class="nav-section mt-3"><?php echo t('People & Control', 'الموظفون والرقابة'); ?></div>
        <?php if ($can('hr_dashboard_2')): ?><a class="nav-link <?php echo $active('/erp/hr-dashboard-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/hr-dashboard-2.php"><?php echo t('HR 2.0', 'الموارد البشرية 2.0'); ?></a><?php endif; ?>
        <?php if ($can('employee_contracts')): ?><a class="nav-link <?php echo $active('/erp/employee-contracts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/employee-contracts.php"><?php echo t('Contracts & Docs', 'العقود والمستندات'); ?></a><?php endif; ?>
        <?php if ($can('shift_scheduling')): ?><a class="nav-link <?php echo $active('/erp/shift-scheduling.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/shift-scheduling.php"><?php echo t('Shift Scheduling', 'جدولة الورديات'); ?></a><?php endif; ?>
        <?php if ($can('leave_balances')): ?><a class="nav-link <?php echo $active('/erp/leave-balances.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/leave-balances.php"><?php echo t('Leave Balances', 'أرصدة الإجازات'); ?></a><?php endif; ?>
        <?php if ($can('employee_loans')): ?><a class="nav-link <?php echo $active('/erp/employee-loans.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/employee-loans.php"><?php echo t('Employee Loans', 'سلف الموظفين'); ?></a><?php endif; ?>
        <?php if ($can('employee_self_service_admin')): ?><a class="nav-link <?php echo $active('/erp/employee-self-service-admin.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/employee-self-service-admin.php"><?php echo t('ESS Admin', 'إدارة الخدمة الذاتية'); ?></a><?php endif; ?>
        <?php if ($can('hr')): ?>
        <a class="nav-link <?php echo $active('/erp/employees'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/employees.php"><?php echo t('Employees', 'الموظفون'); ?></a>
        <a class="nav-link <?php echo $active('/erp/leave-requests.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/leave-requests.php"><?php echo t('Leave Requests', 'طلبات الإجازة'); ?></a>
        <?php endif; ?>
        <?php if ($can('attendance')): ?><a class="nav-link <?php echo $active('/erp/attendance.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/attendance.php"><?php echo t('Attendance', 'الحضور والانصراف'); ?></a><?php endif; ?>
        <?php if ($can('payroll')): ?><a class="nav-link <?php echo $active('/erp/payroll-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/payroll-dashboard.php"><?php echo t('Payroll Dashboard', 'لوحة الرواتب'); ?></a><a class="nav-link <?php echo $active('/erp/payroll-runs.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/payroll-runs.php"><?php echo t('Payroll Runs', 'تشغيل الرواتب'); ?></a><?php endif; ?>
        <?php if ($can('employee_expenses')): ?><a class="nav-link <?php echo $active('/erp/employee-expenses.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/employee-expenses.php"><?php echo t('Employee Expenses', 'مصروفات الموظفين'); ?></a><?php endif; ?>
        <?php if ($can('commissions')): ?><a class="nav-link <?php echo $active('/erp/commissions.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/commissions.php"><?php echo t('Commissions', 'العمولات'); ?></a><?php endif; ?>
        <?php if ($can('performance_management')): ?><a class="nav-link <?php echo $active('/erp/performance-management.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/performance-management.php"><?php echo t('Performance', 'الأداء'); ?></a><?php endif; ?>
        <?php if ($can('reports')): ?><a class="nav-link <?php echo $active('/erp/reports.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/reports.php"><?php echo t('Reports', 'التقارير'); ?></a><a class="nav-link <?php echo $active('/erp/branch-reports.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/branch-reports.php"><?php echo t('Branch Summary', 'ملخص الفروع'); ?></a><?php endif; ?>
        <?php if ($can('bi_dashboard_2')): ?><a class="nav-link <?php echo $active('/erp/bi-dashboard-2.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/bi-dashboard-2.php"><?php echo t('BI Dashboard 2.0', 'لوحة ذكاء الأعمال 2.0'); ?></a><?php endif; ?>
        <?php if ($can('metric_library')): ?><a class="nav-link <?php echo $active('/erp/metric-library.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/metric-library.php"><?php echo t('Metric Library', 'مكتبة المؤشرات'); ?></a><?php endif; ?>
        <?php if ($can('kpi_alerts')): ?><a class="nav-link <?php echo $active('/erp/kpi-alerts.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/kpi-alerts.php"><?php echo t('KPI Alerts', 'تنبيهات مؤشرات الأداء'); ?></a><?php endif; ?>
        <?php if ($can('report_drilldowns')): ?><a class="nav-link <?php echo $active('/erp/report-drilldowns.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/report-drilldowns.php"><?php echo t('Drilldowns', 'تفصيل البيانات'); ?></a><?php endif; ?>
        <?php if ($can('report_storyboards')): ?><a class="nav-link <?php echo $active('/erp/report-storyboards.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/report-storyboards.php"><?php echo t('Storyboards', 'عروض التقارير'); ?></a><?php endif; ?>
        <?php if ($can('dataset_cache')): ?><a class="nav-link <?php echo $active('/erp/dataset-cache.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/dataset-cache.php"><?php echo t('Dataset Cache', 'كاش البيانات'); ?></a><?php endif; ?>
        <?php if ($can('advanced_reporting')): ?><a class="nav-link <?php echo $active('/erp/advanced-reporting.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/advanced-reporting.php"><?php echo t('Advanced Reporting', 'تقارير متقدمة'); ?></a><?php endif; ?>
        <?php if ($can('management_dashboards')): ?><a class="nav-link <?php echo $active('/erp/management-dashboard.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/management-dashboard.php"><?php echo t('Management Dashboard', 'لوحة الإدارة'); ?></a><?php endif; ?>
        <?php if ($can('kpi_builder')): ?><a class="nav-link <?php echo $active('/erp/kpi-builder.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/kpi-builder.php"><?php echo t('KPI Builder', 'منشئ مؤشرات الأداء'); ?></a><?php endif; ?>
        <?php if ($can('scheduled_reports')): ?><a class="nav-link <?php echo $active('/erp/scheduled-reports.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/scheduled-reports.php"><?php echo t('Scheduled Reports', 'التقارير المجدولة'); ?></a><?php endif; ?>
        <?php if ($can('report_exports')): ?><a class="nav-link <?php echo $active('/erp/report-export-center.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/report-export-center.php"><?php echo t('Export Center', 'مركز التصدير'); ?></a><?php endif; ?>
        <?php if ($can('activity_log')): ?><a class="nav-link <?php echo $active('/erp/activity-log.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/activity-log.php"><?php echo t('Activity Log', 'سجل النشاط'); ?></a><?php endif; ?>
        <?php if ($isAdmin): ?>
        <a class="nav-link <?php echo $active('/erp/access-control.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/access-control.php"><?php echo t('Employee Access', 'دخول الموظفين'); ?></a>
        <a class="nav-link <?php echo $active('/erp/roles.php'); ?>" href="<?php echo esc(ADMIN_URL); ?>/erp/roles.php"><?php echo t('Permission Roles', 'أدوار الصلاحيات'); ?></a>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </nav>
</aside>
<section class="col-xl-10 col-lg-9 admin-content">
<div class="admin-topbar card-admin">
    <div>
        <div class="topbar-kicker"><?php echo $isAdmin ? 'Administrator Console' : 'Employee ERP Workspace'; ?></div>
        <h1 class="h3 mb-1"><?php echo esc($pageTitle); ?></h1>
        <div class="text-secondary">Signed in as <?php echo esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['email'] ?? 'User')); ?></div>
    </div>
    <div class="toolbar-actions">
        <a class="btn btn-outline-secondary" href="<?php echo esc(SITE_URL); ?>/index.php">View Storefront</a>
        <?php if ($can('invoices')): ?><a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/create-invoice.php">New Invoice</a><?php endif; ?>
        <a class="btn btn-dark" href="<?php echo esc(SITE_URL); ?>/employee/logout.php">Logout</a>
    </div>
</div>
<?php if ($notice = flash('success')): ?><div class="alert alert-success shadow-sm"><?php echo esc($notice); ?></div><?php endif; ?>
<?php if ($notice = flash('error')): ?><div class="alert alert-danger shadow-sm"><?php echo esc($notice); ?></div><?php endif; ?>









<script>
(function(){
  function ready(fn){
    if(document.readyState !== 'loading'){ fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function(){
    var sidebar = document.querySelector('.admin-sidebar, .sidebar, aside[class*="sidebar"], .admin-aside');
    var toggle = document.getElementById('adminSidebarToggle');
    var overlay = document.getElementById('adminSidebarOverlay');

    function setMobileOpen(open){
      document.body.classList.toggle('admin-sidebar-open', !!open);
      if(toggle){ toggle.setAttribute('aria-expanded', open ? 'true' : 'false'); }
    }

    if(toggle && !toggle.dataset.bound){
      toggle.dataset.bound = '1';
      toggle.addEventListener('click', function(){
        setMobileOpen(!document.body.classList.contains('admin-sidebar-open'));
      });
    }

    if(overlay && !overlay.dataset.bound){
      overlay.dataset.bound = '1';
      overlay.addEventListener('click', function(){ setMobileOpen(false); });
    }

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){ setMobileOpen(false); }
    });

    if(!sidebar || sidebar.dataset.nestedSectionReady === '1'){ return; }
    sidebar.dataset.nestedSectionReady = '1';

    function txt(el){
      return (el && el.textContent ? el.textContent : '').replace(/\s+/g,' ').trim();
    }

    var known = [
      'WEBSITE SALES',
      'ERP COMMAND',
      'CRM',
      'SERVICE OPERATIONS',
      'BI & AUTOMATION',
      'SYSTEM HARDENING',
      'DOCUMENTATION & COMMERCIAL',
      'SAAS & LICENSING'
    ];

    function isHeading(el){
      if(!el || el.nodeType !== 1){ return false; }
      if(el.tagName === 'A' || el.tagName === 'BUTTON' || el.tagName === 'INPUT' || el.tagName === 'SELECT'){ return false; }
      if(el.closest('.admin-sidebar-stable-tools') || el.closest('.admin-sidebar-controlbox')){ return false; }

      var t = txt(el).toUpperCase();
      if(!t || t.length > 70){ return false; }
      if(t.indexOf('ADMINISTRATOR ACCESS') >= 0){ return false; }

      if(known.indexOf(t) >= 0){ return true; }

      var cls = (el.className || '').toString().toLowerCase();
      if((cls.indexOf('kicker') >= 0 || cls.indexOf('heading') >= 0 || cls.indexOf('section') >= 0 || cls.indexOf('text-uppercase') >= 0) && /^[A-Z0-9 &\/.-]+$/.test(t)){
        return true;
      }

      return false;
    }

    function nearestRow(el){
      return el.closest('li, .nav-item, .menu-item, .sidebar-item, .menu-row') || el;
    }

    function visibleMenuRows(){
      var nodes = [];
      sidebar.querySelectorAll('a').forEach(function(a){
        var row = nearestRow(a);
        if(nodes.indexOf(row) === -1 && !row.closest('.admin-sidebar-stable-tools') && !row.closest('.admin-sidebar-controlbox')){
          nodes.push(row);
        }
      });
      return nodes;
    }

    function findHeadings(){
      return Array.prototype.slice.call(sidebar.querySelectorAll('*')).filter(isHeading);
    }

    function follows(a, b){
      return !!(b.compareDocumentPosition(a) & Node.DOCUMENT_POSITION_FOLLOWING);
    }

    function itemsForHeading(heading, headings){
      var nextHeading = null;
      for(var i=0;i<headings.length;i++){
        if(headings[i] === heading && headings[i+1]){
          nextHeading = headings[i+1];
          break;
        }
      }

      return visibleMenuRows().filter(function(row){
        if(!follows(row, heading)){ return false; }
        if(nextHeading && follows(row, nextHeading)){ return false; }
        return true;
      });
    }

    var headings = findHeadings();

    headings.forEach(function(heading, index){
      if(heading.dataset.sectionToggleReady === '1'){ return; }

      var items = itemsForHeading(heading, headings);
      if(!items.length){ return; }

      heading.dataset.sectionToggleReady = '1';
      heading.classList.add('admin-sidebar-section-toggle');

      var key = 'sidebar-section-nested-' + txt(heading).toLowerCase().replace(/[^a-z0-9]+/g,'-') + '-' + index;
      var saved = localStorage.getItem(key);

      function apply(closed){
        heading.classList.toggle('is-collapsed', !!closed);
        items.forEach(function(item){
          item.classList.toggle('admin-sidebar-section-collapsed', !!closed);
        });
        localStorage.setItem(key, closed ? 'closed' : 'open');
      }

      if(saved === 'closed'){
        apply(true);
      }

      heading.addEventListener('click', function(e){
        e.preventDefault();
        apply(!heading.classList.contains('is-collapsed'));
      });
    });

    sidebar.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click', function(){
        if(window.innerWidth < 992){ setMobileOpen(false); }
      });
    });

    window.addEventListener('resize', function(){
      if(window.innerWidth >= 992){ setMobileOpen(false); }
    });
  });
})();
</script>