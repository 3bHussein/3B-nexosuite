<?php
require_once dirname(__DIR__) . '/includes/functions.php';
if (isLoggedIn()) { redirect(SITE_URL . '/user/dashboard.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = getDB()->prepare('SELECT * FROM ' . table('users') . ' WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->execute([trim($_POST['email'])]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        flash('success', 'Welcome back.');
        if (($user['role'] ?? '') === 'admin') {
            redirect(ADMIN_URL . '/dashboard.php');
        }
        if (($user['role'] ?? '') === 'employee' && (int)($user['can_login_erp'] ?? 0) === 1) {
            redirect(SITE_URL . '/employee/dashboard.php');
        }
        redirect(SITE_URL . '/user/dashboard.php');
    }
    flash('error', 'Invalid login credentials.');
    redirect(SITE_URL . '/user/login.php');
}
siteHeader('Login', 'login');
?>
<h1 class="mb-4">Login</h1><form method="post" class="form-card" style="max-width:560px"><div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div><div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div><button class="btn btn-brand">Login</button> <a href="<?php echo esc(SITE_URL); ?>/user/register.php">Create account</a></form>
<?php siteFooter(); ?>