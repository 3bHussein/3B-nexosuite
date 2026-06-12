<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();$user=currentUser();$pdo=getDB();
websitePermissionGuard('customer_invoices');
$stmt=$pdo->prepare('SELECT * FROM '.table('invoices').' WHERE customer_email=? ORDER BY created_at DESC');$stmt->execute([$user['email']]);$invoices=$stmt->fetchAll();
siteHeader('My Invoices', 'login');
?>
<h1 class="mb-4">My Invoices</h1><div class="table-card table-responsive"><table class="table align-middle"><thead><tr><th>Invoice</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody><?php foreach($invoices as $invoice): ?><tr><td><strong><?php echo esc($invoice['invoice_number']); ?></strong></td><td><?php echo money($invoice['total']); ?></td><td><?php echo money($invoice['amount_paid']); ?></td><td><?php echo money($invoice['balance_due']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($invoice['status'])); ?>"><?php echo esc($invoice['status']); ?></span></td><td><?php echo esc(date('M d, Y',strtotime($invoice['created_at']))); ?></td><td><a class="btn btn-sm btn-outline-danger" href="<?php echo esc(SITE_URL); ?>/user/invoice-disputes.php">Dispute</a> <a class="btn btn-sm btn-outline-primary" href="<?php echo esc(SITE_URL); ?>/user/payment-promises.php">Promise</a></td></tr><?php endforeach; ?><?php if(!$invoices): ?><tr><td colspan="7" class="text-secondary">No invoices found for your email address.</td></tr><?php endif; ?></tbody></table></div>
<?php siteFooter(); ?>