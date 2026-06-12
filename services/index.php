<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$services = getDB()->query('SELECT * FROM ' . table('services') . ' WHERE active = 1 ORDER BY name')->fetchAll();
siteHeader('Services', 'services');
?>
<style>
.services-hero{position:relative;overflow:hidden;border-radius:34px;background:linear-gradient(135deg,#0f172a,#1e293b 55%,#e6005c);color:#fff;padding:clamp(28px,5vw,58px);margin-bottom:28px;box-shadow:0 24px 60px rgba(15,23,42,.18)}
.services-hero:after{content:"";position:absolute;right:-80px;top:-80px;width:260px;height:260px;background:rgba(255,255,255,.12);border-radius:50%}
.services-hero .eyebrow{display:inline-flex;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.12);font-weight:800;letter-spacing:.04em;text-transform:uppercase;font-size:.75rem}
.services-hero h1{font-weight:900;letter-spacing:-.05em;margin:14px 0 10px;font-size:clamp(2rem,5vw,4rem)}
.services-hero p{max-width:760px;color:rgba(255,255,255,.82);font-size:1.05rem}
.service-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.service-card{position:relative;overflow:hidden;background:#fff;border:1px solid #e6eaf2;border-radius:26px;padding:24px;box-shadow:0 16px 36px rgba(15,23,42,.06);height:100%;display:flex;flex-direction:column;gap:14px;transition:.22s ease}
.service-card:hover{transform:translateY(-4px);box-shadow:0 24px 52px rgba(15,23,42,.1);border-color:#cbd5e1}
.service-icon{width:54px;height:54px;border-radius:20px;background:#fff1f7;color:#e6005c;display:grid;place-items:center;font-size:1.55rem}
.service-card h2{font-weight:900;letter-spacing:-.03em;margin:0}
.service-card p{color:#64748b;line-height:1.65;margin:0;flex:1}
.service-price{display:flex;align-items:center;justify-content:space-between;border-top:1px solid #edf2f7;padding-top:14px}
.service-price strong{font-size:1.35rem;color:#0f172a}
.service-actions{display:flex;gap:10px;flex-wrap:wrap}
.service-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:26px;padding:36px;text-align:center;color:#64748b}
@media(max-width:991px){.service-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.service-grid{grid-template-columns:1fr}.service-actions .btn{width:100%}}
</style>

<section class="services-hero">
  <span class="eyebrow">Service Support</span>
  <h1>Services built around your workflow</h1>
  <p>Book installation, support, software setup, consultation, or after-sales assistance directly from the website. Each request enters the booking workflow for admin follow-up.</p>
  <div class="d-flex flex-wrap gap-2 mt-4">
    <a class="btn btn-light btn-lg" href="<?php echo esc(SITE_URL); ?>/booking.php">Book a Service</a>
    <a class="btn btn-outline-light btn-lg" href="<?php echo esc(SITE_URL); ?>/contact.php">Contact Team</a>
  </div>
</section>

<?php if($services): ?>
<div class="service-grid">
  <?php foreach ($services as $index => $service): ?>
    <article class="service-card">
      <div class="service-icon"><i class="bi <?php echo esc(['bi-tools','bi-laptop','bi-headset','bi-shield-check','bi-gear','bi-truck'][$index % 6]); ?>"></i></div>
      <h2 class="h4"><?php echo esc($service['name']); ?></h2>
      <p><?php echo esc($service['description']); ?></p>
      <div class="service-price">
        <span class="text-secondary small">Starting from</span>
        <strong><?php echo money($service['price']); ?></strong>
      </div>
      <div class="service-actions">
        <a class="btn btn-brand" href="<?php echo esc(SITE_URL); ?>/booking.php">Book Now</a>
        <a class="btn btn-outline-primary" href="<?php echo esc(SITE_URL); ?>/contact.php">Ask Question</a>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php else: ?>
  <div class="service-empty">
    <h2 class="h5">No services are active yet.</h2>
    <p class="mb-0">Add active services from the admin panel to show them here.</p>
  </div>
<?php endif; ?>
<?php siteFooter(); ?>