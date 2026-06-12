<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$user=currentUser();if(!$user || ($user['role']??'')!=='vendor'){redirect(SITE_URL.'/vendor/login.php');}
$pdo=getDB();$a=$pdo->prepare('SELECT sua.supplier_id,s.* FROM '.table('supplier_user_access').' sua LEFT JOIN '.table('suppliers').' s ON s.id=sua.supplier_id WHERE sua.user_id=? AND sua.status="active" LIMIT 1');$a->execute([$user['id']]);$supplier=$a->fetch();if(!$supplier){redirect(SITE_URL.'/vendor/login.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{$pdo->prepare('UPDATE '.table('suppliers').' SET contact_name=?,email=?,phone=?,address=? WHERE id=?')->execute([trim((string)$_POST['contact_name']),trim((string)$_POST['email']),trim((string)$_POST['phone']),trim((string)$_POST['address']),(int)$supplier['supplier_id']]);flash('success','Profile updated.');}catch(Throwable $e){flash('error',$e->getMessage());}
  redirect(SITE_URL.'/vendor/profile.php');
}
siteHeader('Vendor Profile','login');
?>
<h1 class="mb-4">Vendor Profile</h1>
<form method="post" class="form-card" style="max-width:760px"><div class="mb-3"><label class="form-label">Company</label><input class="form-control" value="<?php echo esc($supplier['company_name']); ?>" disabled></div><div class="mb-3"><label class="form-label">Contact Name</label><input class="form-control" name="contact_name" value="<?php echo esc($supplier['contact_name']); ?>"></div><div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?php echo esc($supplier['email']); ?>"></div><div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo esc($supplier['phone']); ?>"></div><div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="4"><?php echo esc($supplier['address']); ?></textarea></div><button class="btn btn-brand">Update Profile</button> <a class="btn btn-outline-secondary" href="<?php echo esc(SITE_URL); ?>/vendor/dashboard.php">Back</a></form>
<?php siteFooter(); ?>