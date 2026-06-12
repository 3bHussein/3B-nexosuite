<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
$user = currentUser();
$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket = 'TKT-' . date('YmdHis') . '-' . random_int(100, 999);
    $stmt = $pdo->prepare('INSERT INTO ' . table('support_tickets') . ' (ticket_number,user_id,subject,message,status,priority) VALUES (?,?,?,?,"open",?)');
    $stmt->execute([$ticket, $user['id'], $_POST['subject'], $_POST['message'], $_POST['priority']]);
    flash('success', 'Support ticket created.');
    redirect(SITE_URL . '/user/support.php');
}
$stmt = $pdo->prepare('SELECT * FROM ' . table('support_tickets') . ' WHERE user_id = ? ORDER BY created_at DESC'); $stmt->execute([$user['id']]); $tickets = $stmt->fetchAll();
siteHeader('Support', 'login');
?>
<h1 class="mb-4">Support</h1><div class="row g-4"><div class="col-lg-5"><form method="post" class="form-card"><h2 class="h4">Open Ticket</h2><div class="mb-3"><label class="form-label">Subject</label><input class="form-control" name="subject" required></div><div class="mb-3"><label class="form-label">Priority</label><select class="form-select" name="priority"><option>low</option><option selected>medium</option><option>high</option></select></div><div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required></textarea></div><button class="btn btn-brand">Submit</button></form></div><div class="col-lg-7"><div class="table-card table-responsive"><table class="table"><thead><tr><th>Ticket</th><th>Subject</th><th>Status</th><th>Priority</th></tr></thead><tbody><?php foreach ($tickets as $ticket): ?><tr><td><?php echo esc($ticket['ticket_number']); ?></td><td><?php echo esc($ticket['subject']); ?></td><td><?php echo esc($ticket['status']); ?></td><td><?php echo esc($ticket['priority']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php siteFooter(); ?>