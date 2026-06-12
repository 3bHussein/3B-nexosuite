<?php
$pageTitle='Training Center';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('training_center');
$pdo=getDB();

if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'course');
    if($action==='install'){
      p35InstallDefaults($pdo);
      flash('success','Full EN/AR customer, developer, training and commercial documentation defaults installed.');
    }
    elseif($action==='checklist'){
      p35CreateOnboardingChecklist($pdo,trim((string)$_POST['client_name']),trim((string)$_POST['package_name']),trim((string)$_POST['go_live_date']));
      flash('success','Training/onboarding checklist created.');
    }
    else{
      p35CreateTrainingCourse($pdo,trim((string)$_POST['course_title']),trim((string)$_POST['course_type']),trim((string)$_POST['audience']),(int)$_POST['duration_minutes'],trim((string)$_POST['description']));
      flash('success','Training course created.');
    }
  }catch(Throwable $e){
    recordSystemError($pdo,$e,['page'=>'training-center']);
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/training-center.php');
}

$courses=$pdo->query('SELECT * FROM '.table('training_courses').' ORDER BY created_at DESC LIMIT 120')->fetchAll();
$lessons=$pdo->query('SELECT l.*,c.course_title FROM '.table('training_lessons').' l LEFT JOIN '.table('training_courses').' c ON c.id=l.training_course_id ORDER BY c.created_at DESC,l.sort_order LIMIT 200')->fetchAll();
$checklists=$pdo->query('SELECT * FROM '.table('training_checklists').' ORDER BY created_at DESC LIMIT 80')->fetchAll();
$docs=$pdo->query('SELECT * FROM '.table('documentation_articles').' ORDER BY sort_order,title LIMIT 80')->fetchAll();
$demoCreds=$pdo->query('SELECT * FROM '.table('demo_credentials').' ORDER BY portal_name,role_label LIMIT 80')->fetchAll();

include dirname(__DIR__).'/header.php';
?>
<style>
  .doc-hero{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:28px;padding:28px;box-shadow:0 22px 55px rgba(15,23,42,.18)}
  .doc-card{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:20px;box-shadow:0 14px 35px rgba(15,23,42,.06);height:100%}
  .doc-chip{display:inline-flex;align-items:center;gap:6px;border-radius:999px;background:#eef2ff;color:#3730a3;padding:6px 12px;font-size:12px;font-weight:700;margin:3px}
  .doc-section-title{font-size:1.35rem;font-weight:800;margin-bottom:12px;color:#0f172a}
  .doc-subtitle{font-weight:700;color:#334155;margin-top:14px;margin-bottom:6px}
  .doc-list{margin-bottom:0;padding-left:18px}
  .doc-list li{margin-bottom:7px}
  .rtl-box{direction:rtl;text-align:right;font-family:Tahoma,Arial,sans-serif;line-height:1.9}
  .en-box{line-height:1.75}
  .section-anchor{scroll-margin-top:90px}
  .doc-toc a{text-decoration:none}
  .manual-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
  .print-only{display:none}
  @media print{
    .no-print,.sidebar,.navbar,.admin-sidebar,.admin-header,form,.btn{display:none!important}
    .content,.main-content{margin:0!important;padding:0!important;width:100%!important}
    body{background:#fff!important}
    .doc-card,.doc-hero{box-shadow:none!important;border:1px solid #ddd!important;page-break-inside:avoid}
    .print-only{display:block}
  }
</style>

<div class="doc-hero mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
      <div class="erp-kicker text-white-50">Priority 35 / Documentation + Training</div>
      <h1 class="h3 mb-2">Customer & Developer Documentation / التوثيق الكامل للعميل والمطور</h1>
      <p class="mb-0 text-white-50">Full bilingual EN/AR documentation for client training, developer handover, every ERP section, and go-live readiness.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 no-print">
      <form method="post"><button name="action" value="install" class="btn btn-light">Install Defaults</button></form>
      <button class="btn btn-outline-light" onclick="window.print()">Print / Save PDF</button>
    </div>
  </div>
</div>

<div class="row g-4 mb-4 no-print">
  <div class="col-xl-4">
    <form method="post" class="card-admin p-4 mb-4">
      <h2 class="h5 mb-3">Create Training Course</h2>
      <input class="form-control mb-2" name="course_title" placeholder="Course title" required>
      <select class="form-select mb-2" name="course_type">
        <option>admin_training</option>
        <option>customer_training</option>
        <option>developer_handover</option>
        <option>sales_training</option>
        <option>operations_training</option>
      </select>
      <input class="form-control mb-2" name="audience" value="staff">
      <input class="form-control mb-2" type="number" name="duration_minutes" value="<?php echo esc(setting('training_default_duration_minutes','60')); ?>">
      <textarea class="form-control mb-3" name="description" rows="3" placeholder="Training description"></textarea>
      <button class="btn btn-outline-primary w-100">Create Course</button>
    </form>

    <form method="post" class="card-admin p-4">
      <input type="hidden" name="action" value="checklist">
      <h2 class="h5 mb-3">Create Client Onboarding Checklist</h2>
      <input class="form-control mb-2" name="client_name" placeholder="Client name">
      <input class="form-control mb-2" name="package_name" placeholder="Package">
      <input class="form-control mb-3" type="date" name="go_live_date">
      <button class="btn btn-brand w-100">Create Checklist</button>
    </form>
  </div>

  <div class="col-xl-8">
    <div class="doc-card doc-toc">
      <h2 class="h5 mb-3">Documentation Index</h2>
      <a class="doc-chip" href="#customer-doc">Customer / العميل</a>
      <a class="doc-chip" href="#developer-doc">Developer / المطور</a>
      <a class="doc-chip" href="#section-map">Every Section / شرح الأقسام</a>
      <a class="doc-chip" href="#training-plan">Training Plan / خطة التدريب</a>
      <a class="doc-chip" href="#handover-checklist">Handover / التسليم</a>
      <a class="doc-chip" href="#existing-data">Courses & Docs / البيانات الحالية</a>
      <hr>
      <p class="text-secondary mb-0">Use this page as the master training document. It is designed to be printed or saved as PDF and shared with clients, internal staff, or developers.</p>
    </div>
  </div>
</div>

<div id="customer-doc" class="section-anchor mb-4">
  <div class="doc-card">
    <h2 class="doc-section-title">1. Customer / Client User Documentation</h2>
    <div class="row g-4">
      <div class="col-lg-6 en-box">
        <h3 class="h5">English Guide</h3>
        <p>This section is for the business customer, owner, admin user, sales user, support staff, and any client team member who will use the ERP after handover.</p>

        <div class="doc-subtitle">1. Login and Access</div>
        <ul class="doc-list">
          <li>Open the correct portal URL: Admin, Customer, Employee, Technician, or Mobile.</li>
          <li>Login using the account created by the system administrator.</li>
          <li>Never share passwords between staff. Each user should have a separate role.</li>
          <li>After login, confirm the dashboard, menu, and permissions match the user job role.</li>
        </ul>

        <div class="doc-subtitle">2. Dashboard</div>
        <ul class="doc-list">
          <li>The dashboard is the daily control room for sales, stock, invoices, open jobs, approvals, and alerts.</li>
          <li>Use dashboard widgets to see what needs action today.</li>
          <li>Managers should review unpaid invoices, low stock, pending approvals, open job cards, and quote requests.</li>
        </ul>

        <div class="doc-subtitle">3. Products and Storefront</div>
        <ul class="doc-list">
          <li>Add products with title, SKU, price, stock, category, short description, long description, image, and SEO fields.</li>
          <li>Use clear product names and categories so customers can find items easily.</li>
          <li>For online sales, make sure product status is active and stock is correct.</li>
          <li>Use bundles, B2B price lists, and customer price rules for special business pricing.</li>
        </ul>

        <div class="doc-subtitle">4. Customers and Leads</div>
        <ul class="doc-list">
          <li>Create customer records before quotations, invoices, service requests, or support follow-up.</li>
          <li>Store company name, contact person, email, phone, address, tax details, and customer type.</li>
          <li>Use notes and documents to keep customer communication organized.</li>
        </ul>

        <div class="doc-subtitle">5. Quotes, Orders, Invoices and Payments</div>
        <ul class="doc-list">
          <li>Start with quote or request quote when the customer is still deciding.</li>
          <li>Convert approved quote into order or invoice according to your business flow.</li>
          <li>Record payments against invoice to keep balance due correct.</li>
          <li>Review open invoices and partial payments before delivery or service completion.</li>
        </ul>

        <div class="doc-subtitle">6. Inventory and Procurement</div>
        <ul class="doc-list">
          <li>Check stock before confirming delivery or installation.</li>
          <li>Use stock movement history to understand why quantity changed.</li>
          <li>Create purchase requests or supplier orders when stock is low.</li>
          <li>Use warehouse/location fields when operating multiple branches.</li>
        </ul>

        <div class="doc-subtitle">7. Service, Technician and Mobile Use</div>
        <ul class="doc-list">
          <li>Create job cards for service, repair, installation, support, or technical work.</li>
          <li>Technicians can use mobile pages to view jobs and update work progress.</li>
          <li>Offline sync is a foundation for saving field notes when internet is unstable.</li>
          <li>Use photos, notes, parts usage, and completion status to document the service properly.</li>
        </ul>

        <div class="doc-subtitle">8. Document Management</div>
        <ul class="doc-list">
          <li>Upload trade licenses, customer contracts, supplier documents, employee documents, invoices, manuals, and warranties.</li>
          <li>Use expiry dates for documents that need renewal.</li>
          <li>Use approval workflow for important controlled documents.</li>
          <li>Use linked records to connect documents with customers, invoices, products, suppliers, or employees.</li>
        </ul>

        <div class="doc-subtitle">9. Support and Handover</div>
        <ul class="doc-list">
          <li>Before go-live, test login, product creation, customer creation, quote/order/invoice flow, payment, stock update, and document upload.</li>
          <li>After go-live, keep one responsible admin user to manage settings, roles, and system repair tools.</li>
        </ul>
      </div>

      <div class="col-lg-6 rtl-box">
        <h3 class="h5">الدليل العربي</h3>
        <p>هذا القسم مخصص للعميل التجاري، مالك النظام، مسؤول النظام، فريق المبيعات، فريق الدعم، وأي موظف سيستخدم النظام بعد التسليم.</p>

        <div class="doc-subtitle">١. تسجيل الدخول والصلاحيات</div>
        <ul>
          <li>افتح رابط البوابة الصحيحة: الإدارة، العميل، الموظف، الفني، أو نسخة الجوال.</li>
          <li>سجل الدخول باستخدام الحساب الذي أنشأه مسؤول النظام.</li>
          <li>لا تشارك كلمة المرور بين الموظفين. يجب أن يكون لكل مستخدم حساب وصلاحية منفصلة.</li>
          <li>بعد الدخول تأكد أن لوحة التحكم والقائمة والصلاحيات مناسبة لطبيعة عمل المستخدم.</li>
        </ul>

        <div class="doc-subtitle">٢. لوحة التحكم</div>
        <ul>
          <li>لوحة التحكم هي مركز المتابعة اليومي للمبيعات والمخزون والفواتير والمهام والتنبيهات.</li>
          <li>استخدم المؤشرات لمعرفة الأعمال التي تحتاج إجراء سريع.</li>
          <li>يجب على الإدارة مراجعة الفواتير غير المدفوعة، المخزون المنخفض، الموافقات المعلقة، أوامر العمل المفتوحة، وطلبات الأسعار.</li>
        </ul>

        <div class="doc-subtitle">٣. المنتجات والمتجر</div>
        <ul>
          <li>أضف المنتج مع الاسم، SKU، السعر، المخزون، التصنيف، الوصف المختصر، الوصف الطويل، الصورة، وحقول SEO.</li>
          <li>استخدم أسماء وتصنيفات واضحة لتسهيل وصول العميل للمنتج.</li>
          <li>للبيع عبر الإنترنت تأكد أن المنتج مفعل وأن كمية المخزون صحيحة.</li>
          <li>استخدم الباقات، أسعار B2B، وقواعد الأسعار الخاصة للعملاء التجاريين.</li>
        </ul>

        <div class="doc-subtitle">٤. العملاء والعملاء المحتملون</div>
        <ul>
          <li>أنشئ سجل العميل قبل إنشاء عرض سعر أو فاتورة أو طلب خدمة أو متابعة دعم.</li>
          <li>احفظ اسم الشركة، الشخص المسؤول، البريد، الهاتف، العنوان، بيانات الضريبة، ونوع العميل.</li>
          <li>استخدم الملاحظات والمستندات لتنظيم التواصل مع العميل.</li>
        </ul>

        <div class="doc-subtitle">٥. عروض الأسعار والطلبات والفواتير والمدفوعات</div>
        <ul>
          <li>ابدأ بعرض سعر عندما يكون العميل في مرحلة القرار.</li>
          <li>حوّل عرض السعر المعتمد إلى طلب أو فاتورة حسب سير العمل.</li>
          <li>سجل المدفوعات على الفاتورة للحفاظ على الرصيد المستحق بدقة.</li>
          <li>راجع الفواتير المفتوحة والمدفوعات الجزئية قبل التسليم أو إغلاق الخدمة.</li>
        </ul>

        <div class="doc-subtitle">٦. المخزون والمشتريات</div>
        <ul>
          <li>تحقق من المخزون قبل تأكيد التسليم أو التركيب.</li>
          <li>استخدم سجل حركة المخزون لمعرفة سبب تغير الكمية.</li>
          <li>أنشئ طلب شراء عند انخفاض المخزون.</li>
          <li>استخدم المستودعات والفروع عند وجود أكثر من موقع.</li>
        </ul>

        <div class="doc-subtitle">٧. الخدمة والفني والجوال</div>
        <ul>
          <li>أنشئ بطاقة عمل للخدمة أو الإصلاح أو التركيب أو الدعم الفني.</li>
          <li>يمكن للفني استخدام صفحات الجوال لمتابعة الأعمال وتحديث الحالة.</li>
          <li>المزامنة دون اتصال تساعد في حفظ الملاحظات عند ضعف الإنترنت.</li>
          <li>استخدم الصور والملاحظات وقطع الغيار والحالة النهائية لتوثيق العمل.</li>
        </ul>

        <div class="doc-subtitle">٨. إدارة المستندات</div>
        <ul>
          <li>ارفع الرخص التجارية، عقود العملاء، مستندات الموردين، مستندات الموظفين، الفواتير، الكتالوجات والضمانات.</li>
          <li>استخدم تاريخ الانتهاء للمستندات التي تحتاج تجديد.</li>
          <li>استخدم الموافقات للمستندات المهمة والحساسة.</li>
          <li>اربط المستندات بالعملاء أو الفواتير أو المنتجات أو الموردين أو الموظفين.</li>
        </ul>

        <div class="doc-subtitle">٩. الدعم والتسليم</div>
        <ul>
          <li>قبل التشغيل النهائي اختبر الدخول، إضافة منتج، إضافة عميل، عرض سعر، فاتورة، دفعة، حركة مخزون ورفع مستند.</li>
          <li>بعد التشغيل يجب تحديد مسؤول نظام لمتابعة الإعدادات والصلاحيات وأدوات الإصلاح.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div id="developer-doc" class="section-anchor mb-4">
  <div class="doc-card">
    <h2 class="doc-section-title">2. Developer Documentation / توثيق المطور</h2>
    <div class="row g-4">
      <div class="col-lg-6 en-box">
        <h3 class="h5">English Developer Guide</h3>

        <div class="doc-subtitle">1. Project Structure</div>
        <ul class="doc-list">
          <li><code>installer.php</code> generates the full system files and database schema.</li>
          <li><code>includes/functions.php</code> contains shared database, auth, settings, helper and module functions.</li>
          <li><code>admin/erp/</code> contains ERP admin module pages.</li>
          <li><code>store/</code> contains frontend store pages such as wishlist, compare, bulk order and quote request.</li>
          <li><code>mobile/</code> contains PWA/mobile shell pages for customer, employee and technician flows.</li>
          <li><code>api/</code> contains REST/API and mobile sync endpoints.</li>
        </ul>

        <div class="doc-subtitle">2. Database Design</div>
        <ul class="doc-list">
          <li>All tables use the configured database prefix through <code>table('table_name')</code>.</li>
          <li>Core modules use separate tables for headers and line items where required.</li>
          <li>Document numbers are managed by document sequence helpers.</li>
          <li>When adding a module, create tables in installer schema and add a safe checker entry if critical.</li>
        </ul>

        <div class="doc-subtitle">3. Adding a New ERP Module</div>
        <ol>
          <li>Add database tables to installer schema.</li>
          <li>Add permission keys to permission catalog and permission labels.</li>
          <li>Add settings and document sequences if needed.</li>
          <li>Add helper functions in <code>includes/functions.php</code>.</li>
          <li>Create admin page under <code>admin/erp/</code>.</li>
          <li>Add navigation entry in <code>admin/header.php</code>.</li>
          <li>Run PHP lint and Table & Column Checker.</li>
        </ol>

        <div class="doc-subtitle">4. Permissions</div>
        <ul class="doc-list">
          <li>Use <code>erpGuard('permission_key')</code> at the top of protected admin pages.</li>
          <li>Add permission repair support for critical modules.</li>
          <li>Always test with admin and limited-role users.</li>
        </ul>

        <div class="doc-subtitle">5. Settings</div>
        <ul class="doc-list">
          <li>Use <code>setting('key','default')</code> to read settings.</li>
          <li>Use <code>saveSetting()</code> or settings page logic to update values.</li>
          <li>Add default settings in installer seed data and Settings Repair if production-critical.</li>
        </ul>

        <div class="doc-subtitle">6. Security Guidelines</div>
        <ul class="doc-list">
          <li>Use prepared statements for all SQL with user input.</li>
          <li>Escape output with <code>esc()</code>.</li>
          <li>Do not expose raw database errors to customers on production.</li>
          <li>Protect installer after installation.</li>
          <li>Do not store API secrets or passwords in public documentation.</li>
        </ul>

        <div class="doc-subtitle">7. API and Integrations</div>
        <ul class="doc-list">
          <li>API endpoints use Bearer token authentication and scope checks.</li>
          <li>Webhook builder manages event templates and delivery retry foundation.</li>
          <li>Integration connectors are foundations for WooCommerce, shipping, payments, accounting and marketplaces.</li>
        </ul>

        <div class="doc-subtitle">8. Deployment and Testing</div>
        <ul class="doc-list">
          <li>Run <code>php -l</code> on modified PHP files.</li>
          <li>Run Production Hardening scan after upgrade.</li>
          <li>Run Table & Column Checker after schema changes.</li>
          <li>Test fresh install and upgrade install separately.</li>
          <li>Create backup before moving to live hosting.</li>
        </ul>
      </div>

      <div class="col-lg-6 rtl-box">
        <h3 class="h5">دليل المطور العربي</h3>

        <div class="doc-subtitle">١. هيكل المشروع</div>
        <ul>
          <li>ملف <code>installer.php</code> ينشئ ملفات النظام وقاعدة البيانات.</li>
          <li>ملف <code>includes/functions.php</code> يحتوي دوال قاعدة البيانات، الدخول، الإعدادات، والدوال المشتركة.</li>
          <li>مجلد <code>admin/erp/</code> يحتوي صفحات الإدارة الخاصة بالـ ERP.</li>
          <li>مجلد <code>store/</code> يحتوي صفحات المتجر مثل المفضلة، المقارنة، طلبات الجملة وطلب السعر.</li>
          <li>مجلد <code>mobile/</code> يحتوي صفحات الجوال و PWA للعميل والموظف والفني.</li>
          <li>مجلد <code>api/</code> يحتوي واجهات API والمزامنة.</li>
        </ul>

        <div class="doc-subtitle">٢. تصميم قاعدة البيانات</div>
        <ul>
          <li>كل الجداول تستخدم بادئة قاعدة البيانات من خلال دالة <code>table()</code>.</li>
          <li>الوحدات الأساسية تستخدم جداول رئيسية وجداول تفاصيل عند الحاجة.</li>
          <li>أرقام المستندات تتم إدارتها من خلال دوال التسلسل.</li>
          <li>عند إضافة وحدة جديدة يجب إضافة الجداول في المثبت وإضافتها للفحص إذا كانت مهمة.</li>
        </ul>

        <div class="doc-subtitle">٣. إضافة وحدة ERP جديدة</div>
        <ol>
          <li>أضف جداول قاعدة البيانات في المثبت.</li>
          <li>أضف مفاتيح الصلاحيات وقائمة أسماء الصلاحيات.</li>
          <li>أضف الإعدادات وتسلسل المستندات إذا احتاجت الوحدة.</li>
          <li>أضف الدوال في <code>includes/functions.php</code>.</li>
          <li>أنشئ صفحة الإدارة داخل <code>admin/erp/</code>.</li>
          <li>أضف رابط القائمة في <code>admin/header.php</code>.</li>
          <li>شغّل فحص PHP وفحص الجداول والأعمدة.</li>
        </ol>

        <div class="doc-subtitle">٤. الصلاحيات</div>
        <ul>
          <li>استخدم <code>erpGuard('permission_key')</code> في بداية صفحات الإدارة المحمية.</li>
          <li>أضف دعم الإصلاح التلقائي للصلاحيات المهمة.</li>
          <li>اختبر الصفحة بحساب المدير وحساب بصلاحيات محدودة.</li>
        </ul>

        <div class="doc-subtitle">٥. الإعدادات</div>
        <ul>
          <li>استخدم <code>setting('key','default')</code> لقراءة الإعدادات.</li>
          <li>استخدم <code>saveSetting()</code> أو صفحة الإعدادات لتعديل القيم.</li>
          <li>أضف الإعدادات الافتراضية داخل المثبت وأداة إصلاح الإعدادات إذا كانت مهمة للإنتاج.</li>
        </ul>

        <div class="doc-subtitle">٦. إرشادات الأمان</div>
        <ul>
          <li>استخدم Prepared Statements مع أي مدخلات من المستخدم.</li>
          <li>استخدم <code>esc()</code> عند عرض البيانات.</li>
          <li>لا تعرض أخطاء قاعدة البيانات الخام للمستخدم النهائي في الإنتاج.</li>
          <li>احمِ ملف التثبيت بعد انتهاء التثبيت.</li>
          <li>لا تضع كلمات المرور أو مفاتيح API في التوثيق العام.</li>
        </ul>

        <div class="doc-subtitle">٧. API والتكاملات</div>
        <ul>
          <li>واجهات API تستخدم Bearer Token وصلاحيات Scope.</li>
          <li>Webhook Builder يدير قوالب الأحداث وإعادة المحاولة.</li>
          <li>التكاملات تشمل أساسيات WooCommerce والشحن والمدفوعات والمحاسبة والمتاجر الإلكترونية.</li>
        </ul>

        <div class="doc-subtitle">٨. النشر والاختبار</div>
        <ul>
          <li>شغّل <code>php -l</code> على أي ملف PHP يتم تعديله.</li>
          <li>شغّل فحص Production Hardening بعد أي ترقية.</li>
          <li>شغّل Table & Column Checker بعد أي تغيير في الجداول.</li>
          <li>اختبر التثبيت الجديد والترقية بشكل منفصل.</li>
          <li>أنشئ نسخة احتياطية قبل النقل إلى الاستضافة الحية.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div id="section-map" class="section-anchor mb-4">
  <div class="doc-card">
    <h2 class="doc-section-title">3. Every System Section Explained / شرح كل أقسام النظام</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead><tr><th>Section</th><th>English Explanation</th><th class="rtl-box">الشرح العربي</th></tr></thead>
        <tbody>
          <tr><td><strong>Admin Dashboard</strong></td><td>Main control screen for KPIs, alerts, tasks and quick decisions.</td><td class="rtl-box">الشاشة الرئيسية لمتابعة المؤشرات والتنبيهات والمهام واتخاذ القرار السريع.</td></tr>
          <tr><td><strong>Storefront</strong></td><td>Public online store where customers browse products, request quotes and place orders.</td><td class="rtl-box">واجهة المتجر التي يستخدمها العملاء لتصفح المنتجات وطلب الأسعار وتنفيذ الطلبات.</td></tr>
          <tr><td><strong>Products</strong></td><td>Catalog management for product title, SKU, price, stock, images, descriptions and SEO.</td><td class="rtl-box">إدارة كتالوج المنتجات من الاسم والرمز والسعر والمخزون والصور والوصف و SEO.</td></tr>
          <tr><td><strong>Categories</strong></td><td>Groups products into clear departments for easier browsing and reporting.</td><td class="rtl-box">تنظيم المنتجات داخل أقسام واضحة لتسهيل التصفح والتقارير.</td></tr>
          <tr><td><strong>Customers</strong></td><td>Central customer records for contact details, company data, notes, documents and sales history.</td><td class="rtl-box">ملف مركزي للعملاء يحتوي بيانات التواصل والشركة والملاحظات والمستندات وسجل المبيعات.</td></tr>
          <tr><td><strong>Quotations</strong></td><td>Used before sale confirmation to send pricing, terms and product/service proposal.</td><td class="rtl-box">تستخدم قبل تأكيد البيع لإرسال الأسعار والشروط والعرض للعميل.</td></tr>
          <tr><td><strong>Orders</strong></td><td>Tracks confirmed customer orders and prepares fulfillment workflow.</td><td class="rtl-box">متابعة طلبات العملاء المؤكدة وتجهيز عملية التنفيذ والتسليم.</td></tr>
          <tr><td><strong>Invoices</strong></td><td>Official billing record with totals, tax, balance due and payment status.</td><td class="rtl-box">مستند الفوترة الرسمي ويحتوي الإجمالي والضريبة والرصيد المستحق وحالة الدفع.</td></tr>
          <tr><td><strong>Payments</strong></td><td>Records received amounts and updates invoice balance.</td><td class="rtl-box">تسجيل المبالغ المستلمة وتحديث رصيد الفاتورة.</td></tr>
          <tr><td><strong>Inventory</strong></td><td>Controls stock levels, stock movement, warehouse availability and low-stock alerts.</td><td class="rtl-box">إدارة كميات المخزون وحركات المخزون والتوفر في المستودعات وتنبيهات النقص.</td></tr>
          <tr><td><strong>Procurement</strong></td><td>Supplier purchasing flow including purchase requests, POs and receiving foundation.</td><td class="rtl-box">إدارة المشتريات من الموردين وتشمل طلبات الشراء وأوامر الشراء والاستلام.</td></tr>
          <tr><td><strong>Suppliers</strong></td><td>Supplier profiles, contacts, terms, documents and purchase history.</td><td class="rtl-box">ملفات الموردين وتشمل بيانات التواصل والشروط والمستندات وسجل الشراء.</td></tr>
          <tr><td><strong>Finance</strong></td><td>Accounting foundation for invoices, payments, journals, reports and financial visibility.</td><td class="rtl-box">أساس المحاسبة لمتابعة الفواتير والمدفوعات والقيود والتقارير المالية.</td></tr>
          <tr><td><strong>HR / Employees</strong></td><td>Employee records, roles, attendance, leave and self-service foundation.</td><td class="rtl-box">ملفات الموظفين والصلاحيات والحضور والإجازات وبوابة الخدمة الذاتية.</td></tr>
          <tr><td><strong>Service / Job Cards</strong></td><td>Manages service jobs, technician tasks, parts usage, notes and completion status.</td><td class="rtl-box">إدارة أعمال الخدمة ومهام الفنيين وقطع الغيار والملاحظات وحالة الإنجاز.</td></tr>
          <tr><td><strong>Customer Portal</strong></td><td>Customer-facing area for requests, documents, orders and communication.</td><td class="rtl-box">بوابة خاصة بالعميل للطلبات والمستندات والطلبات والتواصل.</td></tr>
          <tr><td><strong>Technician Mobile</strong></td><td>Mobile workspace for job execution and field service updates.</td><td class="rtl-box">واجهة جوال للفني لمتابعة الأعمال وتحديثات الخدمة الميدانية.</td></tr>
          <tr><td><strong>PWA / Mobile App</strong></td><td>Installable web app foundation with service worker, offline page and mobile shell.</td><td class="rtl-box">تطبيق ويب قابل للتثبيت مع Service Worker وصفحة دون اتصال وواجهة جوال.</td></tr>
          <tr><td><strong>Document Management</strong></td><td>Central document library with versions, approvals, expiry alerts and linked records.</td><td class="rtl-box">مكتبة مستندات مركزية مع الإصدارات والموافقات وتنبيهات الانتهاء والربط بالسجلات.</td></tr>
          <tr><td><strong>Advanced E-commerce</strong></td><td>B2B pricing, bundles, digital licenses, wishlists, comparison, quote and bulk order tools.</td><td class="rtl-box">أدوات متقدمة مثل أسعار B2B والباقات والتراخيص الرقمية والمفضلة والمقارنة وطلبات الجملة.</td></tr>
          <tr><td><strong>API / Webhooks</strong></td><td>External system connection foundation using API tokens, scopes, logs and webhooks.</td><td class="rtl-box">أساس ربط الأنظمة الخارجية باستخدام API Token والصلاحيات والسجلات و Webhooks.</td></tr>
          <tr><td><strong>Reports / BI</strong></td><td>Management reporting and performance visibility across sales, stock and operations.</td><td class="rtl-box">تقارير إدارية ومؤشرات أداء للمبيعات والمخزون والعمليات.</td></tr>
          <tr><td><strong>Production Hardening</strong></td><td>Repair center, schema checker, installer health, demo data and release readiness tools.</td><td class="rtl-box">أدوات الإنتاج مثل مركز الإصلاح وفحص الجداول وصحة المثبت والبيانات التجريبية وجاهزية الإصدار.</td></tr>
          <tr><td><strong>Documentation & Training</strong></td><td>Guides, training courses, demo credentials, handover and commercial packaging.</td><td class="rtl-box">الأدلة والدورات وبيانات الدخول التجريبية والتسليم والتجهيز التجاري.</td></tr>
          <tr><td><strong>Settings & Roles</strong></td><td>System configuration, branding, prefixes, permissions, user roles and access control.</td><td class="rtl-box">إعدادات النظام والهوية والبادئات والصلاحيات وأدوار المستخدمين والتحكم بالوصول.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="training-plan" class="section-anchor mb-4">
  <div class="doc-card">
    <h2 class="doc-section-title">4. Training Delivery Plan / خطة التدريب</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><th>Session</th><th>Duration</th><th>Owner</th><th>Output</th><th class="rtl-box">العربي</th></tr></thead>
        <tbody>
          <tr><td>System overview and access</td><td>30 min</td><td>Implementation lead</td><td>Users understand portals and modules.</td><td class="rtl-box">تعريف عام بالنظام والبوابات والوحدات.</td></tr>
          <tr><td>Admin configuration</td><td>60–90 min</td><td>Admin/IT</td><td>Settings, roles and safety checks completed.</td><td class="rtl-box">إعدادات النظام والصلاحيات وفحوصات الأمان.</td></tr>
          <tr><td>Sales/store workflow</td><td>60 min</td><td>Sales trainer</td><td>Product, quote, order and customer flow tested.</td><td class="rtl-box">اختبار المنتجات والعملاء والعروض والطلبات.</td></tr>
          <tr><td>Inventory/procurement</td><td>60–75 min</td><td>Operations trainer</td><td>Stock and supplier process tested.</td><td class="rtl-box">اختبار المخزون والموردين والمشتريات.</td></tr>
          <tr><td>Finance/reporting</td><td>60 min</td><td>Finance/admin</td><td>Invoice, payment and report flow tested.</td><td class="rtl-box">اختبار الفواتير والمدفوعات والتقارير.</td></tr>
          <tr><td>Developer handover</td><td>60–120 min</td><td>Developer</td><td>Structure, schema, upgrade and repair process explained.</td><td class="rtl-box">شرح هيكل الملفات والجداول والترقية والإصلاح للمطور.</td></tr>
          <tr><td>Go-live handover</td><td>45 min</td><td>Project lead</td><td>Checklist, backup and support process confirmed.</td><td class="rtl-box">تأكيد قائمة التشغيل والنسخة الاحتياطية وخطة الدعم.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="handover-checklist" class="section-anchor mb-4">
  <div class="doc-card">
    <h2 class="doc-section-title">5. Acceptance Checklist Before Handover / قائمة القبول قبل التسليم</h2>
    <div class="row g-4">
      <div class="col-lg-6 en-box">
        <ul>
          <li>Admin login tested.</li>
          <li>Role permissions tested for admin, sales, inventory, finance and technician users.</li>
          <li>Product, customer, quote, order, invoice and payment sample flow tested.</li>
          <li>Stock movement and warehouse availability tested.</li>
          <li>Document Library upload, version, approval and expiry alert tested.</li>
          <li>Mobile/PWA dashboard opened on phone browser.</li>
          <li>API health endpoint checked if integrations are required.</li>
          <li>Production Hardening scan completed.</li>
          <li>Table & Column Checker completed.</li>
          <li>README, Installation Guide, User Manual, Admin Manual and Developer Handover reviewed.</li>
        </ul>
      </div>
      <div class="col-lg-6 rtl-box">
        <ul>
          <li>تم اختبار دخول المدير.</li>
          <li>تم اختبار صلاحيات المدير والمبيعات والمخزون والمالية والفني.</li>
          <li>تم اختبار تدفق المنتج والعميل والعرض والطلب والفاتورة والدفع.</li>
          <li>تم اختبار حركة المخزون والتوفر في المستودع.</li>
          <li>تم اختبار رفع المستندات والإصدارات والموافقات وتنبيهات الانتهاء.</li>
          <li>تم فتح لوحة الجوال / PWA من متصفح الهاتف.</li>
          <li>تم اختبار API Health عند الحاجة للتكاملات.</li>
          <li>تم تنفيذ فحص Production Hardening.</li>
          <li>تم تنفيذ Table & Column Checker.</li>
          <li>تمت مراجعة README ودليل التثبيت ودليل المستخدم ودليل الإدارة وتسليم المطور.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div id="existing-data" class="section-anchor row g-4 no-print">
  <div class="col-xl-6">
    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Existing Courses</h2>
      <table class="table"><thead><tr><th>Course</th><th>Audience</th><th>Duration</th><th>Status</th></tr></thead><tbody><?php foreach($courses as $c): ?><tr><td><strong><?php echo esc($c['course_number']); ?></strong><div class="small text-secondary"><?php echo esc($c['course_title'].' · '.$c['description']); ?></div></td><td><?php echo esc($c['audience']); ?></td><td><?php echo (int)$c['duration_minutes']; ?> min</td><td><span class="badge bg-<?php echo esc(statusTone($c['status'])); ?>"><?php echo esc($c['status']); ?></span></td></tr><?php endforeach; ?></tbody></table>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Documentation Articles</h2>
      <table class="table"><tbody><?php foreach($docs as $d): ?><tr><td><strong><?php echo esc($d['title']); ?></strong><div class="small text-secondary"><?php echo esc($d['doc_type'].' · '.$d['audience'].' · '.$d['slug']); ?></div></td></tr><?php endforeach; ?></tbody></table>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Lessons</h2>
      <table class="table"><tbody><?php foreach($lessons as $l): ?><tr><td><strong><?php echo esc($l['course_title']); ?></strong><div class="small text-secondary"><?php echo esc($l['lesson_title'].' · '.$l['duration_minutes'].' min'); ?></div></td></tr><?php endforeach; ?></tbody></table>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Demo Credentials</h2>
      <table class="table"><tbody><?php foreach($demoCreds as $dc): ?><tr><td><strong><?php echo esc($dc['portal_name'].' · '.$dc['role_label']); ?></strong><div class="small text-secondary"><?php echo esc($dc['login_url'].' · '.$dc['username'].' · '.$dc['password_hint']); ?></div></td></tr><?php endforeach; ?></tbody></table>
    </div>
  </div>
</div>

<?php include dirname(__DIR__).'/footer.php'; ?>