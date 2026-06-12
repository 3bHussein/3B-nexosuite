<?php
$pageTitle='Bookings';
require_once dirname(__DIR__) . '/includes/functions.php';
permissionGuard('online_sales_orders');
$pdo=getDB();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt=$pdo->prepare('UPDATE ' . table('bookings') . ' SET status=? WHERE id=?');
    $stmt->execute([$_POST['status'],(int)$_POST['id']]);
    flash('success','Booking updated.');
    redirect(ADMIN_URL.'/bookings.php');
}

$bookings=$pdo->query('SELECT * FROM ' . table('bookings') . ' ORDER BY created_at DESC')->fetchAll();

$counts=['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
foreach($bookings as $booking){
    $status=(string)($booking['status']??'pending');
    if(isset($counts[$status])){$counts[$status]++;}
}

include __DIR__.'/header.php';
?>
<style>
.booking-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px}
.booking-stat{background:#fff;border:1px solid #e6eaf2;border-radius:22px;padding:18px;box-shadow:0 12px 30px rgba(15,23,42,.05)}
.booking-stat span{display:block;color:#64748b;font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.booking-stat strong{font-size:2rem;color:#0f172a}
.booking-detail-panel{background:#f8fafc;border:1px solid #e6eaf2;border-radius:22px;padding:18px;margin-top:12px}
.booking-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.booking-info-box{background:#fff;border:1px solid #edf2f7;border-radius:18px;padding:14px}
.booking-info-box small{display:block;color:#64748b;text-transform:uppercase;font-weight:800;font-size:.72rem;letter-spacing:.04em;margin-bottom:4px}.booking-info-box strong,.booking-info-box span{color:#0f172a;word-break:break-word}
.booking-row-toggle{border:0;background:transparent;color:#0d6efd;font-weight:800;padding:0}
@media(max-width:991px){.booking-stats,.booking-detail-grid{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.booking-stats,.booking-detail-grid{grid-template-columns:1fr}}
</style>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <div class="erp-kicker">Service Requests</div>
    <h1 class="h3 mb-1">Bookings</h1>
    <p class="text-secondary mb-0">Review booking details, customer contact, notes, date/time and update booking status.</p>
  </div>
  <a class="btn btn-outline-primary" href="<?php echo esc(SITE_URL); ?>/booking.php" target="_blank">Open Booking Page</a>
</div>

<div class="booking-stats">
  <div class="booking-stat"><span>Pending</span><strong><?php echo (int)$counts['pending']; ?></strong></div>
  <div class="booking-stat"><span>Confirmed</span><strong><?php echo (int)$counts['confirmed']; ?></strong></div>
  <div class="booking-stat"><span>Completed</span><strong><?php echo (int)$counts['completed']; ?></strong></div>
  <div class="booking-stat"><span>Cancelled</span><strong><?php echo (int)$counts['cancelled']; ?></strong></div>
</div>

<div class="table-wrap table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Booking</th><th>Customer</th><th>Service</th><th>Date</th><th>Status</th><th>Details</th></tr></thead>
    <tbody>
      <?php foreach($bookings as $booking): ?>
        <tr>
          <td><strong><?php echo esc($booking['booking_number']); ?></strong><div class="small text-secondary"><?php echo esc($booking['created_at'] ?? ''); ?></div></td>
          <td><?php echo esc($booking['customer_name']); ?><div class="small text-secondary"><?php echo esc($booking['customer_email']); ?></div></td>
          <td><?php echo esc($booking['service_type']); ?></td>
          <td><?php echo esc($booking['booking_date'].' '.$booking['booking_time']); ?></td>
          <td>
            <form method="post" class="d-flex gap-2">
              <input type="hidden" name="id" value="<?php echo (int)$booking['id']; ?>">
              <select class="form-select form-select-sm" name="status">
                <option <?php echo $booking['status']==='pending'?'selected':''; ?>>pending</option>
                <option <?php echo $booking['status']==='confirmed'?'selected':''; ?>>confirmed</option>
                <option <?php echo $booking['status']==='completed'?'selected':''; ?>>completed</option>
                <option <?php echo $booking['status']==='cancelled'?'selected':''; ?>>cancelled</option>
              </select>
              <button class="btn btn-brand btn-sm">Save</button>
            </form>
          </td>
          <td><button class="booking-row-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#bookingDetails<?php echo (int)$booking['id']; ?>">Show Details</button></td>
        </tr>
        <tr class="collapse" id="bookingDetails<?php echo (int)$booking['id']; ?>">
          <td colspan="6">
            <div class="booking-detail-panel">
              <div class="booking-detail-grid">
                <div class="booking-info-box"><small>Booking Number</small><strong><?php echo esc($booking['booking_number']); ?></strong></div>
                <div class="booking-info-box"><small>Customer Name</small><strong><?php echo esc($booking['customer_name']); ?></strong></div>
                <div class="booking-info-box"><small>Email</small><a href="mailto:<?php echo esc($booking['customer_email']); ?>"><?php echo esc($booking['customer_email']); ?></a></div>
                <div class="booking-info-box"><small>Phone</small><a href="tel:<?php echo esc($booking['customer_phone']); ?>"><?php echo esc($booking['customer_phone']); ?></a></div>
                <div class="booking-info-box"><small>Service Type</small><strong><?php echo esc($booking['service_type']); ?></strong></div>
                <div class="booking-info-box"><small>Date & Time</small><strong><?php echo esc($booking['booking_date'].' '.$booking['booking_time']); ?></strong></div>
                <div class="booking-info-box"><small>Status</small><span class="badge bg-<?php echo esc(statusTone($booking['status'])); ?>"><?php echo esc($booking['status']); ?></span></div>
                <div class="booking-info-box"><small>Created</small><span><?php echo esc($booking['created_at'] ?? ''); ?></span></div>
                <div class="booking-info-box"><small>User ID</small><span><?php echo esc((string)($booking['user_id'] ?? 'Guest')); ?></span></div>
              </div>
              <div class="booking-info-box mt-3"><small>Notes</small><p class="mb-0"><?php echo nl2br(esc($booking['notes'] ?: 'No notes added.')); ?></p></div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <a class="btn btn-outline-primary btn-sm" href="mailto:<?php echo esc($booking['customer_email']); ?>">Email Customer</a>
                <a class="btn btn-outline-dark btn-sm" href="tel:<?php echo esc($booking['customer_phone']); ?>">Call Customer</a>
              </div>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$bookings): ?><tr><td colspan="6" class="text-secondary">No bookings found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__.'/footer.php'; ?>