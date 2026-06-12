<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
$user = currentUser();
$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE ' . table('users') . ' SET first_name=?,last_name=?,phone=?,address=? WHERE id=?');
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['address'], $user['id']]);
    if (!empty($_POST['password'])) {
        if ($_POST['password'] !== ($_POST['confirm_password'] ?? '')) { flash('error', 'Passwords do not match.'); redirect(SITE_URL . '/user/profile.php'); }
        $stmt = $pdo->prepare('UPDATE ' . table('users') . ' SET password=? WHERE id=?');
        $stmt->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $user['id']]);
    }
    flash('success', 'Profile updated.');
    redirect(SITE_URL . '/user/profile.php');
}
siteHeader('Profile', 'login');
?>
<h1 class="mb-4">Profile</h1><form method="post" class="form-card"><div class="row g-3"><div class="col-md-6"><label class="form-label">First Name</label><input class="form-control" name="first_name" value="<?php echo esc($user['first_name']); ?>" required></div><div class="col-md-6"><label class="form-label">Last Name</label><input class="form-control" name="last_name" value="<?php echo esc($user['last_name']); ?>" required></div><div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo esc($user['phone']); ?>"></div><div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3"><?php echo esc($user['address']); ?></textarea></div><div class="col-md-6"><label class="form-label">New Password</label><input class="form-control" type="password" name="password"></div><div class="col-md-6"><label class="form-label">Confirm Password</label><input class="form-control" type="password" name="confirm_password"></div><div class="col-12"><button class="btn btn-brand">Save Profile</button></div></div></form>
<?php siteFooter(); ?>