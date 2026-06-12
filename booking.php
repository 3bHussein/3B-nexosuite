<?php
require_once __DIR__ . '/includes/functions.php';
websitePermissionGuard('website_booking');
$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingNumber = 'BKG-' . date('YmdHis') . '-' . random_int(100, 999);
    $stmt = $pdo->prepare('INSERT INTO ' . table('bookings') . ' (booking_number,user_id,customer_name,customer_email,customer_phone,service_type,booking_date,booking_time,notes,status) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$bookingNumber, $_SESSION['user_id'] ?? null, $_POST['customer_name'], $_POST['customer_email'], $_POST['customer_phone'], $_POST['service_type'], $_POST['booking_date'], $_POST['booking_time'], $_POST['notes'] ?? '', 'pending']);
    $_SESSION['last_booking_number'] = $bookingNumber;
    redirect(SITE_URL . '/booking-success.php');
}
$serviceTypesRaw = trim((string)setting('booking_service_types', ''));
$services = [];
if ($serviceTypesRaw !== '') {
    foreach (preg_split('/\r\n|\r|\n/', $serviceTypesRaw) as $line) {
        $name = trim($line);
        if ($name !== '') {
            $services[] = ['name' => $name];
        }
    }
}
if (!$services) {
    $services = $pdo->query('SELECT * FROM ' . table('services') . ' WHERE active = 1 ORDER BY name')->fetchAll();
}
siteHeader(setting('booking_page_title','Book a Service'), 'booking');
?>
<h1 class="mb-4"><?php echo esc(setting('booking_page_title','Book a Service')); ?></h1>
<form method="post" class="form-card"><div class="row g-3"><div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="customer_name" required></div><div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="customer_email" required></div><div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="customer_phone" required></div><div class="col-md-6"><label class="form-label"><?php echo esc(setting('booking_service_type_label','Service Type')); ?></label><select class="form-select" name="service_type"><?php foreach ($services as $service): ?><option><?php echo esc($service['name']); ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Date</label><input class="form-control" type="date" name="booking_date" required></div><div class="col-md-6"><label class="form-label">Time</label><input class="form-control" type="time" name="booking_time" required></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div><div class="col-12"><button class="btn btn-brand"><?php echo esc(setting('booking_submit_label','Submit Booking')); ?></button></div></div></form>
<?php siteFooter(); ?>