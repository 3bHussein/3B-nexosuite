<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDB();
$contact = contactDetails();
$canRequestQuote = websitePermissionAllowed('b2b_quote_request');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source = $canRequestQuote ? 'website_contact_quote' : 'website_contact';
    $stmt = $pdo->prepare('INSERT INTO ' . table('crm_leads') . ' (name,email,phone,company,status,source,notes) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'] ?? '', $_POST['company'] ?? '', 'new', $source, $_POST['message']]);
    flash('success', 'Message received. It has been added to CRM leads.');
    redirect(SITE_URL . '/contact.php');
}
siteHeader($contact['title'], 'contact');
?>
<style>
.contact-page-grid{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(280px,.9fr);gap:24px;align-items:start}
.contact-info-card{background:#fff;border:1px solid #e6eaf2;border-radius:26px;padding:24px;box-shadow:0 16px 36px rgba(15,23,42,.06)}
.contact-info-list{display:grid;gap:12px;margin-top:18px}
.contact-info-item{display:flex;gap:12px;align-items:flex-start;border:1px solid #edf2f7;background:#f8fafc;border-radius:18px;padding:14px}
.contact-info-item i{width:38px;height:38px;border-radius:14px;background:#fff1f7;color:#e6005c;display:grid;place-items:center;flex:0 0 auto}
.contact-info-item strong{display:block;color:#0f172a}.contact-info-item span,.contact-info-item a{color:#64748b;text-decoration:none}
.contact-guest-note{border:1px solid #fde68a;background:#fffbeb;color:#92400e;border-radius:18px;padding:14px;margin-bottom:16px}
@media(max-width:900px){.contact-page-grid{grid-template-columns:1fr}}
</style>

<div class="contact-page-grid">
  <section>
    <h1 class="mb-2"><?php echo esc($contact['title']); ?></h1>
    <?php if($contact['intro'] !== ''): ?><p class="text-secondary mb-4"><?php echo esc($contact['intro']); ?></p><?php endif; ?>
    <?php if(!$canRequestQuote): ?>
      <div class="contact-guest-note">
        <strong>Request quote is available for registered customers.</strong>
        <div class="small mt-1">You can still send a normal contact message below, or login to access quote/support options.</div>
      </div>
    <?php endif; ?>
    <form method="post" class="form-card">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
        <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" name="company"></div>
        <div class="col-12"><label class="form-label"><?php echo $canRequestQuote ? 'Message / Quote Requirement' : 'Message'; ?></label><textarea class="form-control" name="message" rows="5" required></textarea></div>
        <div class="col-12"><button class="btn btn-brand"><?php echo $canRequestQuote ? 'Send Request' : 'Send Message'; ?></button></div>
      </div>
    </form>
  </section>

  <aside class="contact-info-card">
    <span class="eyebrow">Contact Details</span>
    <h2 class="h4 mt-2">Reach our team</h2>
    <div class="contact-info-list">
      <div class="contact-info-item"><i class="bi bi-envelope"></i><div><strong>Email</strong><a href="mailto:<?php echo esc($contact['email']); ?>"><?php echo esc($contact['email']); ?></a></div></div>
      <div class="contact-info-item"><i class="bi bi-telephone"></i><div><strong>Phone</strong><a href="tel:<?php echo esc($contact['phone']); ?>"><?php echo esc($contact['phone']); ?></a></div></div>
      <div class="contact-info-item"><i class="bi bi-whatsapp"></i><div><strong>WhatsApp</strong><a href="<?php echo esc(whatsappUrl($contact['whatsapp'])); ?>" target="_blank" rel="noopener"><?php echo esc($contact['whatsapp']); ?></a></div></div>
      <div class="contact-info-item"><i class="bi bi-geo-alt"></i><div><strong>Address</strong><span><?php echo esc($contact['address']); ?></span></div></div>
      <?php if($contact['hours'] !== ''): ?><div class="contact-info-item"><i class="bi bi-clock"></i><div><strong>Working Hours</strong><span><?php echo esc($contact['hours']); ?></span></div></div><?php endif; ?>
      <?php if($contact['map_url'] !== ''): ?><div class="contact-info-item"><i class="bi bi-map"></i><div><strong>Location</strong><a href="<?php echo esc($contact['map_url']); ?>" target="_blank" rel="noopener">Open Map</a></div></div><?php endif; ?>
    </div>
  </aside>
</div>
<?php siteFooter(); ?>