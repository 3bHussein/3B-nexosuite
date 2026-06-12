<?php
require_once dirname(__DIR__) . '/includes/functions.php';
if (isLoggedIn()) { redirect(SITE_URL . '/vendor/dashboard.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo=getDB();
    $stmt=$pdo->prepare('SELECT * FROM '.table('users').' WHERE email=? AND role="vendor" AND status="active" LIMIT 1');
    $stmt->execute([trim((string)$_POST['email'])]);
    $user=$stmt->fetch();
    if($user && password_verify((string)$_POST['password'],$user['password'])){
        $access=$pdo->prepare('SELECT COUNT(*) FROM '.table('supplier_user_access').' WHERE user_id=? AND status="active"');$access->execute([(int)$user['id']]);
        if((int)$access->fetchColumn()>0){$_SESSION['user_id']=(int)$user['id'];flash('success','Welcome to the vendor portal.');redirect(SITE_URL.'/vendor/dashboard.php');}
    }
    flash('error','Invalid vendor credentials or no supplier access assigned.');redirect(SITE_URL.'/vendor/login.php');
}
siteHeader('Vendor Login','login');
?>
<h1 class="mb-4">Vendor Portal Login</h1><form method="post" class="form-card" style="max-width:560px"><div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div><div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div><button class="btn btn-brand">Login</button></form>
<?php siteFooter(); ?>