<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$user=currentUser();if(!$user || ($user['role']??'')!=='vendor'){redirect(SITE_URL.'/vendor/login.php');}
$pdo=getDB();$a=$pdo->prepare('SELECT supplier_id FROM '.table('supplier_user_access').' WHERE user_id=? AND status="active" LIMIT 1');$a->execute([$user['id']]);$supplierId=(int)($a->fetchColumn()?:0);
$stmt=$pdo->prepare('SELECT * FROM '.table('supplier_invoices').' WHERE supplier_id=? ORDER BY created_at DESC');$stmt->execute([$supplierId]);$rows=$stmt->fetchAll();
siteHeader('Vendor Invoices','login');
?>
<h1 class="mb-4">Supplier Invoices</h1><div class="table-card table-responsive"><table class="table align-middle"><thead><tr><th>Internal</th><th>Supplier Ref</th><th>Total</th><th>Match</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $inv): ?><tr><td><strong><?php echo esc($inv['internal_number']); ?></strong></td><td><?php echo esc($inv['supplier_invoice_number']); ?></td><td><?php echo money($inv['total']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($inv['match_status'])); ?>"><?php echo esc($inv['match_status']); ?></span></td><td><span class="badge bg-<?php echo esc(statusTone($inv['status'])); ?>"><?php echo esc($inv['status']); ?></span></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="text-secondary">No supplier invoices found.</td></tr><?php endif; ?></tbody></table></div>
<?php siteFooter(); ?>