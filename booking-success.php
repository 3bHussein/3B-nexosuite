<?php
require_once __DIR__ . '/includes/functions.php';
siteHeader('Booking Submitted', 'booking');
?>
<div class="card-clean p-5 text-center"><h1 class="h2">Booking Submitted</h1><p class="lead">Reference: <strong><?php echo esc($_SESSION['last_booking_number'] ?? 'Pending'); ?></strong></p><a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/index.php">Back to Home</a></div>
<?php siteFooter(); ?>