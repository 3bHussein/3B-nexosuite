<?php
$pageTitle='Homepage Builder';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();

$homepageKeys=[
  'homepage_hero_enabled','homepage_kicker','homepage_title','homepage_intro',
  'homepage_hero_primary_label','homepage_hero_primary_url','homepage_hero_secondary_label','homepage_hero_secondary_url',
  'homepage_hero_trust_1','homepage_hero_trust_2','homepage_hero_trust_3',
  'homepage_hero_showcase_label','homepage_hero_showcase_title','homepage_hero_showcase_text','homepage_hero_image',
  'homepage_promo_ribbon_enabled',
  'homepage_promo_1_icon','homepage_promo_1_title','homepage_promo_1_text',
  'homepage_promo_2_icon','homepage_promo_2_title','homepage_promo_2_text',
  'homepage_promo_3_icon','homepage_promo_3_title','homepage_promo_3_text',
  'homepage_promo_4_icon','homepage_promo_4_title','homepage_promo_4_text',
  'homepage_categories_enabled','homepage_categories_eyebrow','homepage_categories_title','homepage_categories_description','homepage_categories_cta_label','homepage_categories_cta_url',
  'homepage_featured_products_enabled','homepage_featured_eyebrow','homepage_featured_title','homepage_featured_description','homepage_featured_cta_label','homepage_featured_cta_url','homepage_featured_limit',
  'homepage_commercial_split_enabled','homepage_b2b_eyebrow','homepage_b2b_title','homepage_b2b_text','homepage_b2b_primary_label','homepage_b2b_primary_url','homepage_b2b_secondary_label','homepage_b2b_secondary_url',
  'homepage_b2c_eyebrow','homepage_b2c_title','homepage_b2c_bullet_1','homepage_b2c_bullet_2','homepage_b2c_bullet_3','homepage_b2c_bullet_4','homepage_b2c_cta_label','homepage_b2c_cta_url',
  'homepage_new_arrivals_enabled','homepage_new_eyebrow','homepage_new_title','homepage_new_description','homepage_new_limit',
  'homepage_services_enabled','homepage_services_eyebrow','homepage_services_title','homepage_services_description','homepage_services_cta_label','homepage_services_cta_url','homepage_services_limit',
  'homepage_trust_grid_enabled',
  'homepage_trust_1_icon','homepage_trust_1_title','homepage_trust_1_text',
  'homepage_trust_2_icon','homepage_trust_2_title','homepage_trust_2_text',
  'homepage_trust_3_icon','homepage_trust_3_title','homepage_trust_3_text',
  'homepage_trust_4_icon','homepage_trust_4_title','homepage_trust_4_text',
  'footer_newsletter_enabled','footer_newsletter_eyebrow','footer_newsletter_title','footer_newsletter_text','footer_newsletter_placeholder','footer_newsletter_button'
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $heroImage=trim((string)($_POST['homepage_hero_image']??setting('homepage_hero_image','')));
        if(!empty($_POST['clear_homepage_hero_image'])){
            $heroImage='';
        }
        $uploadedHero=uploadAdminImage('homepage_hero_upload','homepage');
        if($uploadedHero){
            $heroImage=$uploadedHero;
        }
        $_POST['homepage_hero_image']=$heroImage;

        $stmt=$pdo->prepare('INSERT INTO ' . table('settings') . ' (key_name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
        foreach($homepageKeys as $key){
            $value=(string)($_POST[$key]??'');
            $stmt->execute([$key,$value]);
        }
        flash('success','Homepage builder settings saved.');
        redirect(ADMIN_URL.'/homepage.php');
    }catch(Throwable $e){
        flash('error','Unable to save homepage settings: '.$e->getMessage());
        redirect(ADMIN_URL.'/homepage.php');
    }
}
$heroPreview=uploadAssetUrl(setting('homepage_hero_image',''),'homepage');
include __DIR__.'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div><h1 class="h3 mb-1">Homepage Builder</h1><p class="text-secondary mb-0">Customize the homepage hero, promo ribbon, sections, CTAs, trust cards, service content, and newsletter shell.</p></div>
  <a class="btn btn-outline-primary" href="<?php echo esc(SITE_URL); ?>/index.php" target="_blank">Preview Homepage</a>
</div>

<form method="post" enctype="multipart/form-data" class="d-grid gap-4">
  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">Hero Area</h2><p class="text-secondary mb-0">Control the first screen customers see.</p></div><label class="form-check form-switch"><input type="hidden" name="homepage_hero_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_hero_enabled" value="1" <?php echo setting('homepage_hero_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show hero</span></label></div>
    <div class="row g-3">
      <div class="col-lg-4"><label class="form-label">Kicker</label><input class="form-control" name="homepage_kicker" value="<?php echo esc(setting('homepage_kicker','Commerce + ERP workflow')); ?>"></div>
      <div class="col-lg-8"><label class="form-label">Hero title</label><input class="form-control" name="homepage_title" value="<?php echo esc(setting('homepage_title','Build a storefront that sells and operates like a business system.')); ?>"></div>
      <div class="col-12"><label class="form-label">Hero intro</label><textarea class="form-control" rows="3" name="homepage_intro"><?php echo esc(setting('homepage_intro','Present products with a premium catalogue experience while keeping sales connected to stock, quotations, invoices, and ERP reporting.')); ?></textarea></div>
      <div class="col-md-6"><label class="form-label">Primary button label</label><input class="form-control" name="homepage_hero_primary_label" value="<?php echo esc(setting('homepage_hero_primary_label','Shop Products')); ?>"></div>
      <div class="col-md-6"><label class="form-label">Primary button URL</label><input class="form-control" name="homepage_hero_primary_url" value="<?php echo esc(setting('homepage_hero_primary_url','/products/index.php')); ?>"></div>
      <div class="col-md-6"><label class="form-label">Secondary button label</label><input class="form-control" name="homepage_hero_secondary_label" value="<?php echo esc(setting('homepage_hero_secondary_label','Request B2B Quote')); ?>"></div>
      <div class="col-md-6"><label class="form-label">Secondary button URL</label><input class="form-control" name="homepage_hero_secondary_url" value="<?php echo esc(setting('homepage_hero_secondary_url','/contact.php')); ?>"></div>
      <div class="col-md-4"><label class="form-label">Trust point 1</label><input class="form-control" name="homepage_hero_trust_1" value="<?php echo esc(setting('homepage_hero_trust_1','Product catalogue')); ?>"></div>
      <div class="col-md-4"><label class="form-label">Trust point 2</label><input class="form-control" name="homepage_hero_trust_2" value="<?php echo esc(setting('homepage_hero_trust_2','ERP-linked sales')); ?>"></div>
      <div class="col-md-4"><label class="form-label">Trust point 3</label><input class="form-control" name="homepage_hero_trust_3" value="<?php echo esc(setting('homepage_hero_trust_3','Procurement ready')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Showcase label</label><input class="form-control" name="homepage_hero_showcase_label" value="<?php echo esc(setting('homepage_hero_showcase_label','Featured Workflow')); ?>"></div>
      <div class="col-lg-8"><label class="form-label">Showcase title</label><input class="form-control" name="homepage_hero_showcase_title" value="<?php echo esc(setting('homepage_hero_showcase_title','Storefront → Cart → ERP Invoice')); ?>"></div>
      <div class="col-12"><label class="form-label">Showcase text</label><textarea class="form-control" rows="2" name="homepage_hero_showcase_text"><?php echo esc(setting('homepage_hero_showcase_text','Commerce actions become operational records.')); ?></textarea></div>
      <div class="col-lg-6">
        <label class="form-label">Upload hero image</label>
        <input class="form-control" type="file" name="homepage_hero_upload" accept="image/jpeg,image/png,image/webp,image/gif">
        <input type="hidden" name="homepage_hero_image" value="<?php echo esc(setting('homepage_hero_image','')); ?>">
      </div>
      <div class="col-lg-6">
        <label class="form-label">Hero image status</label>
        <div class="builder-preview-line">
          <?php if($heroPreview!==''): ?><img src="<?php echo esc($heroPreview); ?>" alt="Hero preview"><?php else: ?><span>No hero image uploaded.</span><?php endif; ?>
          <label class="form-check"><input class="form-check-input" type="checkbox" name="clear_homepage_hero_image" value="1"><span class="form-check-label">Remove current image</span></label>
        </div>
      </div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">Promo Ribbon</h2><p class="text-secondary mb-0">Customize the four compact value blocks below the hero.</p></div><label class="form-check form-switch"><input type="hidden" name="homepage_promo_ribbon_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_promo_ribbon_enabled" value="1" <?php echo setting('homepage_promo_ribbon_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show ribbon</span></label></div>
    <div class="row g-3">
      <?php for($i=1;$i<=4;$i++): ?>
        <div class="col-xl-6">
          <div class="builder-mini-card">
            <h3 class="h6">Promo item <?php echo $i; ?></h3>
            <div class="row g-2">
              <div class="col-md-4"><label class="form-label">Icon class</label><input class="form-control" name="homepage_promo_<?php echo $i; ?>_icon" value="<?php echo esc(setting('homepage_promo_'.$i.'_icon','bi-stars')); ?>"></div>
              <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="homepage_promo_<?php echo $i; ?>_title" value="<?php echo esc(setting('homepage_promo_'.$i.'_title','Business Feature')); ?>"></div>
              <div class="col-12"><label class="form-label">Text</label><textarea class="form-control" rows="2" name="homepage_promo_<?php echo $i; ?>_text"><?php echo esc(setting('homepage_promo_'.$i.'_text','Customize this feature.')); ?></textarea></div>
            </div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">Category & Featured Product Sections</h2><p class="text-secondary mb-0">Edit section headings, CTA labels, and product count.</p></div></div>
    <div class="row g-4">
      <div class="col-xl-6">
        <div class="builder-mini-card">
          <label class="form-check form-switch mb-3"><input type="hidden" name="homepage_categories_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_categories_enabled" value="1" <?php echo setting('homepage_categories_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show category section</span></label>
          <div class="row g-2">
            <div class="col-md-5"><label class="form-label">Eyebrow</label><input class="form-control" name="homepage_categories_eyebrow" value="<?php echo esc(setting('homepage_categories_eyebrow','Shop by department')); ?>"></div>
            <div class="col-md-7"><label class="form-label">Title</label><input class="form-control" name="homepage_categories_title" value="<?php echo esc(setting('homepage_categories_title','Browse high-intent product categories')); ?>"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="homepage_categories_description"><?php echo esc(setting('homepage_categories_description','Browse categories with clearer merchandising and ERP relevance.')); ?></textarea></div>
            <div class="col-md-6"><label class="form-label">CTA label</label><input class="form-control" name="homepage_categories_cta_label" value="<?php echo esc(setting('homepage_categories_cta_label','View catalogue')); ?>"></div>
            <div class="col-md-6"><label class="form-label">CTA URL</label><input class="form-control" name="homepage_categories_cta_url" value="<?php echo esc(setting('homepage_categories_cta_url','/products/index.php')); ?>"></div>
          </div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="builder-mini-card">
          <label class="form-check form-switch mb-3"><input type="hidden" name="homepage_featured_products_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_featured_products_enabled" value="1" <?php echo setting('homepage_featured_products_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show featured products</span></label>
          <div class="row g-2">
            <div class="col-md-5"><label class="form-label">Eyebrow</label><input class="form-control" name="homepage_featured_eyebrow" value="<?php echo esc(setting('homepage_featured_eyebrow','Featured catalogue')); ?>"></div>
            <div class="col-md-7"><label class="form-label">Title</label><input class="form-control" name="homepage_featured_title" value="<?php echo esc(setting('homepage_featured_title','Products designed to look more sellable')); ?>"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="homepage_featured_description"><?php echo esc(setting('homepage_featured_description','Sharper product hierarchy for commercial presentation.')); ?></textarea></div>
            <div class="col-md-4"><label class="form-label">Product limit</label><input class="form-control" type="number" min="1" max="24" name="homepage_featured_limit" value="<?php echo esc(setting('homepage_featured_limit','8')); ?>"></div>
            <div class="col-md-4"><label class="form-label">CTA label</label><input class="form-control" name="homepage_featured_cta_label" value="<?php echo esc(setting('homepage_featured_cta_label','Shop all')); ?>"></div>
            <div class="col-md-4"><label class="form-label">CTA URL</label><input class="form-control" name="homepage_featured_cta_url" value="<?php echo esc(setting('homepage_featured_cta_url','/products/index.php')); ?>"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">B2B / B2C Split Section</h2><p class="text-secondary mb-0">Edit the commercial conversion panel and retail convenience panel.</p></div><label class="form-check form-switch"><input type="hidden" name="homepage_commercial_split_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_commercial_split_enabled" value="1" <?php echo setting('homepage_commercial_split_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show split section</span></label></div>
    <div class="row g-4">
      <div class="col-xl-6">
        <div class="builder-mini-card">
          <h3 class="h6">B2B panel</h3>
          <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Eyebrow</label><input class="form-control" name="homepage_b2b_eyebrow" value="<?php echo esc(setting('homepage_b2b_eyebrow','B2B path')); ?>"></div>
            <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="homepage_b2b_title" value="<?php echo esc(setting('homepage_b2b_title','Turn larger enquiries into quotes, invoices, and account records.')); ?>"></div>
            <div class="col-12"><label class="form-label">Text</label><textarea class="form-control" rows="3" name="homepage_b2b_text"><?php echo esc(setting('homepage_b2b_text','The frontend should not only sell. It should send qualified business buyers into ERP quotations and sales operations.')); ?></textarea></div>
            <div class="col-md-6"><label class="form-label">Primary CTA label</label><input class="form-control" name="homepage_b2b_primary_label" value="<?php echo esc(setting('homepage_b2b_primary_label','Request Commercial Quote')); ?>"></div>
            <div class="col-md-6"><label class="form-label">Primary CTA URL</label><input class="form-control" name="homepage_b2b_primary_url" value="<?php echo esc(setting('homepage_b2b_primary_url','/contact.php')); ?>"></div>
            <div class="col-md-6"><label class="form-label">Secondary CTA label</label><input class="form-control" name="homepage_b2b_secondary_label" value="<?php echo esc(setting('homepage_b2b_secondary_label','View Services')); ?>"></div>
            <div class="col-md-6"><label class="form-label">Secondary CTA URL</label><input class="form-control" name="homepage_b2b_secondary_url" value="<?php echo esc(setting('homepage_b2b_secondary_url','/services/index.php')); ?>"></div>
          </div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="builder-mini-card">
          <h3 class="h6">B2C panel</h3>
          <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Eyebrow</label><input class="form-control" name="homepage_b2c_eyebrow" value="<?php echo esc(setting('homepage_b2c_eyebrow','B2C path')); ?>"></div>
            <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="homepage_b2c_title" value="<?php echo esc(setting('homepage_b2c_title','Make quick retail buying easier.')); ?>"></div>
            <?php for($i=1;$i<=4;$i++): ?><div class="col-md-6"><label class="form-label">Bullet <?php echo $i; ?></label><input class="form-control" name="homepage_b2c_bullet_<?php echo $i; ?>" value="<?php echo esc(setting('homepage_b2c_bullet_'.$i,'')); ?>"></div><?php endfor; ?>
            <div class="col-md-6"><label class="form-label">CTA label</label><input class="form-control" name="homepage_b2c_cta_label" value="<?php echo esc(setting('homepage_b2c_cta_label','Browse Catalogue')); ?>"></div>
            <div class="col-md-6"><label class="form-label">CTA URL</label><input class="form-control" name="homepage_b2c_cta_url" value="<?php echo esc(setting('homepage_b2c_cta_url','/products/index.php')); ?>"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">New Arrivals & Services</h2><p class="text-secondary mb-0">Customize two commercial discovery sections.</p></div></div>
    <div class="row g-4">
      <div class="col-xl-6">
        <div class="builder-mini-card">
          <label class="form-check form-switch mb-3"><input type="hidden" name="homepage_new_arrivals_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_new_arrivals_enabled" value="1" <?php echo setting('homepage_new_arrivals_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show new arrivals</span></label>
          <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Eyebrow</label><input class="form-control" name="homepage_new_eyebrow" value="<?php echo esc(setting('homepage_new_eyebrow','New arrivals')); ?>"></div>
            <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="homepage_new_title" value="<?php echo esc(setting('homepage_new_title','Freshly added products')); ?>"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="homepage_new_description"><?php echo esc(setting('homepage_new_description','A marketplace-style discovery row that keeps the homepage dynamic.')); ?></textarea></div>
            <div class="col-md-4"><label class="form-label">Product limit</label><input class="form-control" type="number" min="1" max="24" name="homepage_new_limit" value="<?php echo esc(setting('homepage_new_limit','4')); ?>"></div>
          </div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="builder-mini-card">
          <label class="form-check form-switch mb-3"><input type="hidden" name="homepage_services_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_services_enabled" value="1" <?php echo setting('homepage_services_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show services</span></label>
          <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Eyebrow</label><input class="form-control" name="homepage_services_eyebrow" value="<?php echo esc(setting('homepage_services_eyebrow','Service commerce')); ?>"></div>
            <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="homepage_services_title" value="<?php echo esc(setting('homepage_services_title','Sell products and services from the same business storefront')); ?>"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" name="homepage_services_description"><?php echo esc(setting('homepage_services_description','Useful for onboarding, remote support, installations, setup, and B2B implementation services.')); ?></textarea></div>
            <div class="col-md-4"><label class="form-label">Service limit</label><input class="form-control" type="number" min="1" max="12" name="homepage_services_limit" value="<?php echo esc(setting('homepage_services_limit','3')); ?>"></div>
            <div class="col-md-4"><label class="form-label">CTA label</label><input class="form-control" name="homepage_services_cta_label" value="<?php echo esc(setting('homepage_services_cta_label','All services')); ?>"></div>
            <div class="col-md-4"><label class="form-label">CTA URL</label><input class="form-control" name="homepage_services_cta_url" value="<?php echo esc(setting('homepage_services_cta_url','/services/index.php')); ?>"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">Trust Cards</h2><p class="text-secondary mb-0">Edit the four final benefit cards on the homepage.</p></div><label class="form-check form-switch"><input type="hidden" name="homepage_trust_grid_enabled" value="0"><input class="form-check-input" type="checkbox" name="homepage_trust_grid_enabled" value="1" <?php echo setting('homepage_trust_grid_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show cards</span></label></div>
    <div class="row g-3">
      <?php for($i=1;$i<=4;$i++): ?>
        <div class="col-xl-6">
          <div class="builder-mini-card">
            <h3 class="h6">Trust card <?php echo $i; ?></h3>
            <div class="row g-2">
              <div class="col-md-4"><label class="form-label">Icon class</label><input class="form-control" name="homepage_trust_<?php echo $i; ?>_icon" value="<?php echo esc(setting('homepage_trust_'.$i.'_icon','bi-stars')); ?>"></div>
              <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="homepage_trust_<?php echo $i; ?>_title" value="<?php echo esc(setting('homepage_trust_'.$i.'_title','Trust Card')); ?>"></div>
              <div class="col-12"><label class="form-label">Text</label><textarea class="form-control" rows="2" name="homepage_trust_<?php echo $i; ?>_text"><?php echo esc(setting('homepage_trust_'.$i.'_text','Customize this card.')); ?></textarea></div>
            </div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="builder-section-head"><div><h2 class="h4 mb-1">Newsletter Shell</h2><p class="text-secondary mb-0">Edit the subscription area above the footer.</p></div><label class="form-check form-switch"><input type="hidden" name="footer_newsletter_enabled" value="0"><input class="form-check-input" type="checkbox" name="footer_newsletter_enabled" value="1" <?php echo setting('footer_newsletter_enabled','1')==='1'?'checked':''; ?>><span class="form-check-label">Show newsletter</span></label></div>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Eyebrow</label><input class="form-control" name="footer_newsletter_eyebrow" value="<?php echo esc(setting('footer_newsletter_eyebrow','Commercial updates')); ?>"></div>
      <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="footer_newsletter_title" value="<?php echo esc(setting('footer_newsletter_title','Get product launches, B2B offers, and service updates.')); ?>"></div>
      <div class="col-12"><label class="form-label">Text</label><textarea class="form-control" rows="2" name="footer_newsletter_text"><?php echo esc(setting('footer_newsletter_text','Use the newsletter form for campaigns, product releases, and procurement promotions.')); ?></textarea></div>
      <div class="col-md-6"><label class="form-label">Input placeholder</label><input class="form-control" name="footer_newsletter_placeholder" value="<?php echo esc(setting('footer_newsletter_placeholder','Business email address')); ?>"></div>
      <div class="col-md-6"><label class="form-label">Button text</label><input class="form-control" name="footer_newsletter_button" value="<?php echo esc(setting('footer_newsletter_button','Subscribe')); ?>"></div>
    </div>
  </section>

  <div class="pb-4"><button class="btn btn-brand btn-lg">Save Homepage Builder</button></div>
</form>
<?php include __DIR__.'/footer.php'; ?>