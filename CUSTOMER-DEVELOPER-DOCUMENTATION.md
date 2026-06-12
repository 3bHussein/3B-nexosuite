# Customer & Developer Documentation / توثيق العميل والمطور

## Customer Documentation / توثيق العميل

This guide explains how a business customer should use the ERP system after handover.

### Login / تسجيل الدخول

EN: Use the correct portal URL and login with your assigned account.  
AR: استخدم رابط البوابة الصحيح وسجل الدخول بالحساب المخصص لك.

### Dashboard / لوحة التحكم

EN: Review sales, invoices, stock, approvals, alerts and open tasks daily.  
AR: راجع المبيعات والفواتير والمخزون والموافقات والتنبيهات والمهام المفتوحة يومياً.

### Products / المنتجات

EN: Add product name, SKU, price, stock, category, description, images and SEO fields.  
AR: أضف اسم المنتج والرمز والسعر والمخزون والتصنيف والوصف والصور وحقول SEO.

### Customers / العملاء

EN: Store company details, contacts, communication notes, documents and sales history.  
AR: احفظ بيانات الشركة وجهات الاتصال والملاحظات والمستندات وسجل المبيعات.

### Sales Flow / تدفق المبيعات

EN: Use quote → order → invoice → payment as the standard sales process.  
AR: استخدم عرض السعر ← الطلب ← الفاتورة ← الدفع كتدفق المبيعات الأساسي.

### Documents / المستندات

EN: Upload contracts, trade licenses, invoices, certificates and warranty files.  
AR: ارفع العقود والرخص التجارية والفواتير والشهادات وملفات الضمان.

---

## Developer Documentation / توثيق المطور

### Structure / هيكل النظام

EN: Main code areas are installer.php, includes/functions.php, admin/erp, store, mobile and api.  
AR: المناطق الرئيسية للكود هي installer.php و includes/functions.php و admin/erp و store و mobile و api.

### Add Module / إضافة وحدة

EN:
1. Add tables in installer schema.
2. Add helper functions.
3. Add permissions.
4. Add admin page.
5. Add navigation link.
6. Run lint and schema checker.

AR:
1. أضف الجداول في المثبت.
2. أضف الدوال.
3. أضف الصلاحيات.
4. أضف صفحة الإدارة.
5. أضف رابط القائمة.
6. شغّل فحص PHP وفحص الجداول.

### Security / الأمان

EN: Use prepared statements, escape output, protect installer and do not expose secrets.  
AR: استخدم Prepared Statements، وقم بتأمين المخرجات، واحمِ المثبت، ولا تعرض الأسرار.

### Deployment / النشر

EN: Run Production Hardening, Table & Column Checker, Settings Repair, Permission Repair and backup before go-live.  
AR: شغّل Production Hardening و Table & Column Checker و Settings Repair و Permission Repair وأنشئ نسخة احتياطية قبل التشغيل.