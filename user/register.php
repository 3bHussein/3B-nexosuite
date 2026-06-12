<?php
require_once dirname(__DIR__) . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCustomerCreationAllowed(getDB());
    if (($_POST['password'] ?? '') !== ($_POST['confirm_password'] ?? '')) { flash('error', 'Passwords do not match.'); redirect(SITE_URL . '/user/register.php'); }
    if (strlen($_POST['password']) < 8) { flash('error', 'Password must be at least 8 characters.'); redirect(SITE_URL . '/user/register.php'); }
    try {
        $stmt = getDB()->prepare('INSERT INTO ' . table('users') . ' (email,password,first_name,last_name,phone,address,role,status) VALUES (?,?,?,?,?,?,"customer","active")');
        $stmt->execute([trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['first_name'], $_POST['last_name'], $_POST['phone'] ?? '', $_POST['address'] ?? '']);
        flash('success', 'Account created. Log in now.');
        redirect(SITE_URL . '/user/login.php');
    } catch (Throwable $e) {
        flash('error', 'Registration failed. The email may already be in use.');
        redirect(SITE_URL . '/user/register.php');
    }
}
siteHeader('Register', 'register');
?>
<h1 class="mb-4">Create Account</h1><form method="post" class="form-card"><div class="row g-3"><div class="col-md-6"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div><div class="col-md-6"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div><div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div><div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"></div><div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div><div class="col-md-6"><label class="form-label">Password</label><input class="form-control" type="password" name="password" minlength="8" required></div><div class="col-md-6"><label class="form-label">Confirm Password</label><input class="form-control" type="password" name="confirm_password" minlength="8" required></div><div class="col-12"><button class="btn btn-brand">Register</button></div></div></form>
<?php siteFooter(); ?>