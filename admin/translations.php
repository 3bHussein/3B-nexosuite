<?php
$pageTitle='Translation';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
$defaults=[
    'Home'=>'الرئيسية','Products'=>'المنتجات','Services'=>'الخدمات','Downloads'=>'التنزيلات','Blog'=>'المدونة','Contact'=>'اتصل بنا','Cart'=>'السلة','Checkout'=>'الدفع','Login'=>'تسجيل الدخول','Register'=>'إنشاء حساب','Search'=>'بحث','Settings'=>'الإعدادات','Translation'=>'الترجمة','Add to cart'=>'أضف إلى السلة','Book Support'=>'احجز الدعم','Contact Us'=>'اتصل بنا','Request Quote'=>'طلب عرض سعر','Product Details'=>'تفاصيل المنتج','Price'=>'السعر','Stock'=>'المخزون','Categories'=>'الأقسام','Online Orders'=>'طلبات الموقع','Bookings'=>'الحجوزات','Homepage Builder'=>'بناء الصفحة الرئيسية','Content'=>'المحتوى','HTML Pages'=>'صفحات HTML','Users'=>'المستخدمون','Save'=>'حفظ','Update'=>'تحديث','Delete'=>'حذف','Edit'=>'تعديل','English'=>'English','العربية'=>'العربية'
];
$currentJson=(string)setting('translation_manual_json','{}');
$current=json_decode($currentJson,true);
if(!is_array($current)){$current=[];}
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='load_defaults'){
        $current=array_merge($defaults,$current);
    } else {
        $en=$_POST['english']??[];
        $ar=$_POST['arabic']??[];
        $new=[];
        foreach($en as $i=>$key){
            $key=trim((string)$key);
            $val=trim((string)($ar[$i]??''));
            if($key!=='' && $val!==''){$new[$key]=$val;}
        }
        $current=$new;
    }
    $json=json_encode($current,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    $stmt=$pdo->prepare('INSERT INTO '.table('settings').' (key_name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
    $stmt->execute(['translation_manual_json',$json]);
    flash('success','Manual translations saved.');
    redirect(ADMIN_URL.'/translations.php');
}
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h3 mb-1">Translation</h1><p class="text-secondary mb-0">Add exact English text and its Arabic translation. Arabic pages replace matching text automatically.</p></div>
  <form method="post"><input type="hidden" name="action" value="load_defaults"><button class="btn btn-outline-primary">Load starter Arabic translations</button></form>
</div>
<form method="post" class="card-admin p-4">
  <div class="alert alert-light border">Separate URLs are /en and /ar. Enable or disable translation from Settings → Language / Translation.</div>
  <div id="translationRows" class="d-grid gap-2">
    <?php $rows=$current ?: $defaults; foreach($rows as $english=>$arabic): ?>
    <div class="row g-2 align-items-center translation-row">
      <div class="col-md-5"><input class="form-control" name="english[]" value="<?php echo esc($english); ?>" placeholder="English text exactly as shown"></div>
      <div class="col-md-6"><input class="form-control" name="arabic[]" value="<?php echo esc($arabic); ?>" placeholder="Arabic translation" dir="rtl"></div>
      <div class="col-md-1"><button class="btn btn-outline-danger w-100" type="button" onclick="this.closest('.translation-row').remove()">×</button></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="d-flex gap-2 mt-3"><button class="btn btn-outline-secondary" type="button" onclick="addTranslationRow()">Add row</button><button class="btn btn-brand">Save translations</button></div>
</form>
<script>
function addTranslationRow(){const w=document.getElementById('translationRows');const d=document.createElement('div');d.className='row g-2 align-items-center translation-row';d.innerHTML='<div class="col-md-5"><input class="form-control" name="english[]" placeholder="English text exactly as shown"></div><div class="col-md-6"><input class="form-control" name="arabic[]" placeholder="Arabic translation" dir="rtl"></div><div class="col-md-1"><button class="btn btn-outline-danger w-100" type="button" onclick="this.closest('.translation-row').remove()">×</button></div>';w.appendChild(d);}
</script>
<?php include __DIR__.'/footer.php'; ?>