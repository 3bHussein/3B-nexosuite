<?php
$pageTitle='Settings';
require_once dirname(__DIR__) . '/includes/functions.php';
adminGuard();
$pdo=getDB();
$scopeOptions=scopeSelectOptions($pdo);

$seoPages=[
    'home'=>'Homepage',
    'products'=>'Products Listing',
    'services'=>'Services',
    'downloads'=>'Downloads',
    'blog'=>'Blog Listing',
    'contact'=>'Contact',
    'booking'=>'Booking',
    'cart'=>'Cart',
    'checkout'=>'Checkout',
    'login'=>'Login / Account',
    'register'=>'Registration',
];
$seoFields=['title','description','keywords','robots','canonical'];
$defaultRobots="User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /user/\nDisallow: /employee/\nDisallow: /checkout.php\nDisallow: /payment.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $keys=[
        'shop_name','shop_email','shop_phone','shop_address','tax_rate','shipping_cost','booking_page_title','booking_service_type_label','booking_submit_label','booking_service_types','website_brand_name','header_brand_name','header_brand_tagline','header_brand_mark','footer_brand_name','footer_brand_mark','checkout_payment_provider','checkout_pay_later_enabled','checkout_pay_later_label','checkout_pay_later_note','paypal_business_email','paypal_mode','paypal_currency','paypal_sandbox_url','paypal_live_url','default_display_currency','currency_rate_aed','currency_rate_usd','currency_rate_eur','currency_rate_egp',
        'contact_details_enabled','contact_page_title','contact_page_intro','contact_email','contact_phone','contact_whatsapp','contact_address','contact_hours','contact_map_url','footer_contact_title','footer_shop_links_enabled','footer_hide_quote_for_guest','footer_hide_booking_for_guest','website_permission_denied_message',
        'default_company_id','default_branch_id','default_warehouse_id','default_location_id',
        'inventory_valuation_method','inventory_default_cost_ratio','intercompany_auto_journals',
        'supplier_invoice_match_tolerance','customer_credit_include_open_sales_orders','customer_credit_block_when_exceeded',
        'default_labor_hourly_rate','default_technician_hourly_cost','budget_warning_threshold_percent',
        'executive_dashboard_margin_warning_percent','report_export_max_rows','notification_auto_generate_enabled','api_key_default_expiry_days',
        'maintenance_mode_enabled','login_max_attempts','session_timeout_minutes','backup_directory','backup_retention_days','cron_secret','system_error_logging_enabled','health_disk_warning_mb','dashboard_widget_refresh_seconds','email_from_name','email_from_address',
        'license_enforcement_mode','license_grace_days','update_channel','upgrade_mode_enabled','saas_mode_enabled','default_subscription_plan',
        'customer_portal_enabled','customer_dashboard_invoice_disputes_enabled','customer_dashboard_payment_promises_enabled','vendor_portal_enabled','technician_portal_enabled','mobile_erp_enabled','portal_service_request_prefix','vendor_quote_response_prefix',
        'frontend_security_enabled','frontend_security_disable_right_click','frontend_security_block_devtools_keys','frontend_security_block_view_source','frontend_security_block_text_select','frontend_security_block_copy','frontend_security_block_image_drag','frontend_security_devtools_overlay','frontend_security_noscript_warning','frontend_security_warning_message',
        'footer_pills_enabled','footer_pill_1','footer_pill_2','footer_pill_3','footer_pill_4','mobile_drawer_pills_enabled','mobile_drawer_pill_1','mobile_drawer_pill_2','mobile_drawer_pill_3','mobile_drawer_pill_4',
        'header_utility_enabled','header_utility_primary','header_utility_secondary','header_b2b_enquiry_enabled','header_b2b_enquiry_label','header_b2b_enquiry_url','header_request_quote_label','header_request_quote_url','header_book_support_label','header_book_support_url',
        'homepage_hero_enabled','homepage_promo_ribbon_enabled','homepage_categories_enabled',
        'homepage_featured_products_enabled','homepage_commercial_split_enabled',
        'homepage_new_arrivals_enabled','homepage_services_enabled','homepage_trust_grid_enabled',
        'footer_newsletter_enabled','footer_newsletter_eyebrow','footer_newsletter_title','footer_newsletter_text','footer_newsletter_placeholder','footer_newsletter_button','footer_about_enabled','footer_about_text','footer_bottom_note_enabled','footer_bottom_note',
        'commercial_package_prefix','documentation_article_prefix','documentation_asset_prefix','training_course_prefix','training_checklist_prefix','demo_credential_prefix','client_onboarding_prefix','feature_comparison_prefix','sales_brochure_prefix','commercial_default_currency','product_page_slogan_enabled','product_page_slogan_title','product_page_slogan_text','product_page_slogan_icon','product_page_trust_enabled','product_page_trust_1_icon','product_page_trust_1_text','product_page_trust_2_icon','product_page_trust_2_text','product_page_trust_3_icon','product_page_trust_3_text','product_page_commercial_notes_enabled','product_page_commercial_notes_title','product_page_commercial_note_1_title','product_page_commercial_note_1_text','product_page_commercial_note_2_title','product_page_commercial_note_2_text','product_page_commercial_note_3_title','product_page_commercial_note_3_text','commercial_default_implementation_days','documentation_export_format','training_default_duration_minutes','production_repair_prefix','production_schema_check_prefix','production_backup_prefix','production_demo_batch_prefix','production_installer_event_prefix','production_release_checklist_prefix','production_mode_enabled','upgrade_safe_mode_enabled','backup_before_upgrade_enabled','installer_rollback_enabled','repair_center_enabled','schema_checker_enabled','demo_data_manager_enabled','production_health_min_score',
        'database_encryption_tools_enabled','database_encrypted_backup_prefix','database_encryption_algorithm','site_language_mode','site_translation_enabled','site_default_language','translation_manual_json','seo_default_title_suffix','seo_default_description','seo_default_keywords','seo_default_robots','seo_default_og_image','seo_robots_txt'
    ];
    foreach($seoPages as $seoKey=>$seoLabel){
        foreach($seoFields as $seoField){
            $keys[]='seo_'.$seoKey.'_'.$seoField;
        }
    }
    $stmt=$pdo->prepare('INSERT INTO ' . table('settings') . ' (key_name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)');
    foreach($keys as $key){
        $value=(string)($_POST[$key]??'');
        if($key==='seo_robots_txt' && trim($value)===''){
            $value=$defaultRobots;
        }
        $stmt->execute([$key,$value]);
    }
    $robotsContent=(string)($_POST['seo_robots_txt']??$defaultRobots);
    if(trim($robotsContent)===''){
        $robotsContent=$defaultRobots;
    }
    @file_put_contents(dirname(__DIR__) . '/robots.txt', rtrim($robotsContent) . PHP_EOL);
    flash('success','Settings, SEO metadata, and robots.txt have been updated.');
    redirect(ADMIN_URL.'/settings.php');
}
include __DIR__.'/header.php';
?>
<form method="post" class="d-grid gap-4">
  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Store Settings</h2>
        <p class="text-secondary mb-0">Core company, tax, and contact details.</p>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-12"><hr class="my-2"><h3 class="h6 mb-0">Language / Translation</h3></div>
      <div class="col-md-4"><label class="form-label">Language Mode</label><select class="form-select" name="site_language_mode"><option value="en" <?php echo setting('site_language_mode',SITE_LANGUAGE)==='en'?'selected':''; ?>>English only</option><option value="ar" <?php echo setting('site_language_mode',SITE_LANGUAGE)==='ar'?'selected':''; ?>>Arabic only</option><option value="both" <?php echo setting('site_language_mode',SITE_LANGUAGE)==='both'?'selected':''; ?>>English + Arabic</option></select></div>
      <div class="col-md-4"><label class="form-label">Default Language</label><select class="form-select" name="site_default_language"><option value="en" <?php echo setting('site_default_language','en')==='en'?'selected':''; ?>>English</option><option value="ar" <?php echo setting('site_default_language','en')==='ar'?'selected':''; ?>>Arabic</option></select></div>
      <div class="col-md-4"><label class="form-label">Translation Status</label><select class="form-select" name="site_translation_enabled"><option value="1" <?php echo setting('site_translation_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('site_translation_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select><div class="form-text"><a href="<?php echo esc(ADMIN_URL); ?>/translations.php">Manual translation page</a></div></div>
      <div class="col-12"><div class="alert alert-light border mb-0">Use /en and /ar URLs for separate language pages. Manual translations can be edited from the Translation page in the sidebar.</div></div>
      <div class="col-md-6"><label class="form-label">Shop Name</label><input class="form-control" name="shop_name" value="<?php echo esc(setting('shop_name',SHOP_NAME)); ?>"></div>
      <div class="col-md-6"><label class="form-label">Shop Email</label><input class="form-control" name="shop_email" value="<?php echo esc(setting('shop_email',SHOP_EMAIL)); ?>"></div>
      <div class="col-12"><hr class="my-2"><h3 class="h6 mb-0">Website Header / Footer Brand</h3></div>
      <div class="col-md-6"><label class="form-label">Website Brand Name</label><input class="form-control" name="website_brand_name" value="<?php echo esc(setting('website_brand_name','Your Store Name')); ?>"></div>
      <div class="col-md-6"><label class="form-label">Header Brand Name</label><input class="form-control" name="header_brand_name" value="<?php echo esc(setting('header_brand_name',setting('website_brand_name','Your Store Name'))); ?>"></div>
      <div class="col-md-4"><label class="form-label">Header Short Mark</label><input class="form-control" name="header_brand_mark" value="<?php echo esc(setting('header_brand_mark','YS')); ?>"></div>
      <div class="col-md-8"><label class="form-label">Header Tagline</label><input class="form-control" name="header_brand_tagline" value="<?php echo esc(setting('header_brand_tagline','Diagnostic Software Store')); ?>"></div>
      <div class="col-md-4"><label class="form-label">Footer Short Mark</label><input class="form-control" name="footer_brand_mark" value="<?php echo esc(setting('footer_brand_mark','YS')); ?>"></div>
      <div class="col-md-8"><label class="form-label">Footer Brand Name</label><input class="form-control" name="footer_brand_name" value="<?php echo esc(setting('footer_brand_name',setting('website_brand_name','Your Store Name'))); ?>"></div>
      <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="shop_phone" value="<?php echo esc(setting('shop_phone',SHOP_PHONE)); ?>"></div>
      <div class="col-md-3"><label class="form-label">Tax Rate %</label><input class="form-control" type="number" step="0.01" name="tax_rate" value="<?php echo esc(setting('tax_rate','5')); ?>"></div>
      <div class="col-md-3"><label class="form-label">Shipping Cost</label><input class="form-control" type="number" step="0.01" name="shipping_cost" value="<?php echo esc(setting('shipping_cost','0')); ?>"></div>
      <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="shop_address" rows="3"><?php echo esc(setting('shop_address',SHOP_ADDRESS)); ?></textarea></div>
    </div>
  </section>


  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Newsletter Section</h2>
        <p class="text-secondary mb-0">Control the footer newsletter-shell section shown on the storefront.</p>
      </div>
      <span class="badge bg-info text-dark">footer-newsletter</span>
    </div>

    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Show Newsletter</label>
        <select class="form-select" name="footer_newsletter_enabled">
          <option value="1" <?php echo setting('footer_newsletter_enabled','1')==='1'?'selected':''; ?>>Show</option>
          <option value="0" <?php echo setting('footer_newsletter_enabled','1')==='0'?'selected':''; ?>>Hide</option>
        </select>
      </div>

      <div class="col-md-9">
        <label class="form-label">Small Eyebrow Text</label>
        <input class="form-control" name="footer_newsletter_eyebrow" value="<?php echo esc(setting('footer_newsletter_eyebrow','Commercial updates')); ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Main Title</label>
        <input class="form-control" name="footer_newsletter_title" value="<?php echo esc(setting('footer_newsletter_title','Get product launches, B2B offers, and service updates.')); ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Description Text</label>
        <textarea class="form-control" name="footer_newsletter_text" rows="3"><?php echo esc(setting('footer_newsletter_text','Use the newsletter form for campaigns, product releases, and procurement promotions.')); ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email Placeholder</label>
        <input class="form-control" name="footer_newsletter_placeholder" value="<?php echo esc(setting('footer_newsletter_placeholder','Business email address')); ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Button Text</label>
        <input class="form-control" name="footer_newsletter_button" value="<?php echo esc(setting('footer_newsletter_button','Subscribe')); ?>">
      </div>
    </div>
  </section>


  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Currency Switcher Settings</h2>
        <p class="text-secondary mb-0">Control storefront currency display and conversion rates. Rates are relative to AED by default.</p>
      </div>
      <span class="badge bg-info text-dark">AED / USD / EUR / L.E</span>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Default Display Currency</label>
        <select class="form-select" name="default_display_currency">
          <?php foreach(['AED'=>'AED','USD'=>'USD','EUR'=>'EUR','EGP'=>'L.E / Egypt'] as $code=>$label): ?>
            <option value="<?php echo esc($code); ?>" <?php echo setting('default_display_currency','AED')===$code?'selected':''; ?>><?php echo esc($label); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><label class="form-label">AED Rate</label><input class="form-control" name="currency_rate_aed" value="<?php echo esc(setting('currency_rate_aed','1')); ?>"></div>
      <div class="col-md-2"><label class="form-label">USD Rate</label><input class="form-control" name="currency_rate_usd" value="<?php echo esc(setting('currency_rate_usd','0.2723')); ?>"></div>
      <div class="col-md-2"><label class="form-label">EUR Rate</label><input class="form-control" name="currency_rate_eur" value="<?php echo esc(setting('currency_rate_eur','0.2520')); ?>"></div>
      <div class="col-md-2"><label class="form-label">L.E Rate</label><input class="form-control" name="currency_rate_egp" value="<?php echo esc(setting('currency_rate_egp','13.20')); ?>"></div>
      <div class="col-12"><div class="alert alert-light border mb-0">Example: if your base currency is AED, keep AED = 1 and set USD/EUR/L.E as the value of 1 AED in that currency.</div></div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">PayPal Checkout Settings</h2>
        <p class="text-secondary mb-0">Checkout can show PayPal and Pay Later. Pay Later completes the order while keeping payment pending.</p>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-3"><label class="form-label">Payment Provider</label><select class="form-select" name="checkout_payment_provider"><option value="paypal_pay_later" <?php echo setting('checkout_payment_provider','paypal_pay_later')==='paypal_pay_later'?'selected':''; ?>>PayPal + Pay Later</option><option value="paypal" <?php echo setting('checkout_payment_provider','paypal_pay_later')==='paypal'?'selected':''; ?>>PayPal only</option></select></div>
      <div class="col-lg-3"><label class="form-label">Pay Later Option</label><select class="form-select" name="checkout_pay_later_enabled"><option value="1" <?php echo setting('checkout_pay_later_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('checkout_pay_later_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-3"><label class="form-label">Pay Later Label</label><input class="form-control" name="checkout_pay_later_label" value="<?php echo esc(setting('checkout_pay_later_label','Pay Later')); ?>"></div>
      <div class="col-lg-3"><label class="form-label">Pay Later Note</label><input class="form-control" name="checkout_pay_later_note" value="<?php echo esc(setting('checkout_pay_later_note','Submit the order now and our team will confirm payment later.')); ?>"></div>
      <div class="col-lg-3"><label class="form-label">PayPal Business Email</label><input class="form-control" name="paypal_business_email" value="<?php echo esc(setting('paypal_business_email','paypal.me/EcuWarrior')); ?>"><div class="form-text">Default: paypal.me/EcuWarrior</div></div>
      <div class="col-lg-2"><label class="form-label">Mode</label><select class="form-select" name="paypal_mode"><option value="sandbox" <?php echo setting('paypal_mode','sandbox')==='sandbox'?'selected':''; ?>>Sandbox</option><option value="live" <?php echo setting('paypal_mode','sandbox')==='live'?'selected':''; ?>>Live</option></select></div>
      <div class="col-lg-2"><label class="form-label">Currency</label><input class="form-control" name="paypal_currency" value="<?php echo esc(setting('paypal_currency','USD')); ?>"></div>
      <div class="col-lg-6"><label class="form-label">Sandbox URL</label><input class="form-control" name="paypal_sandbox_url" value="<?php echo esc(setting('paypal_sandbox_url','https://paypal.me/EcuWarrior')); ?>"></div>
      <div class="col-lg-6"><label class="form-label">Live URL</label><input class="form-control" name="paypal_live_url" value="<?php echo esc(setting('paypal_live_url','https://paypal.me/EcuWarrior')); ?>"></div>
      <div class="col-12"><div class="alert alert-light border mb-0">To change <code>paypal.me/EcuWarrior</code> later, update <strong>PayPal Business Email</strong> here. PayPal redirects customers to PayPal with this value. Pay Later completes the order and keeps payment pending.</div></div>
    </div>
  </section>


  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Frontend Security Controls</h2>
        <p class="text-secondary mb-0">Add client-side protection controls for the public website. These are deterrents; real protection still depends on server-side permissions and authentication.</p>
      </div>
      <div class="form-check form-switch pt-1">
        <input type="hidden" name="frontend_security_enabled" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="frontend_security_enabled" name="frontend_security_enabled" value="1" <?php echo setting('frontend_security_enabled','0')==='1'?'checked':''; ?>>
        <label class="form-check-label" for="frontend_security_enabled">Enable frontend protection</label>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-4">
        <label class="form-label">Disable Right Click</label>
        <select class="form-select" name="frontend_security_disable_right_click">
          <option value="1" <?php echo setting('frontend_security_disable_right_click','1')==='1'?'selected':''; ?>>Enabled</option>
          <option value="0" <?php echo setting('frontend_security_disable_right_click','1')==='0'?'selected':''; ?>>Disabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Block F12 / DevTools Shortcuts</label>
        <select class="form-select" name="frontend_security_block_devtools_keys">
          <option value="1" <?php echo setting('frontend_security_block_devtools_keys','1')==='1'?'selected':''; ?>>Enabled</option>
          <option value="0" <?php echo setting('frontend_security_block_devtools_keys','1')==='0'?'selected':''; ?>>Disabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Block View Source / Save Shortcuts</label>
        <select class="form-select" name="frontend_security_block_view_source">
          <option value="1" <?php echo setting('frontend_security_block_view_source','1')==='1'?'selected':''; ?>>Enabled</option>
          <option value="0" <?php echo setting('frontend_security_block_view_source','1')==='0'?'selected':''; ?>>Disabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Disable Text Selection</label>
        <select class="form-select" name="frontend_security_block_text_select">
          <option value="0" <?php echo setting('frontend_security_block_text_select','0')==='0'?'selected':''; ?>>Disabled</option>
          <option value="1" <?php echo setting('frontend_security_block_text_select','0')==='1'?'selected':''; ?>>Enabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Disable Copy / Cut</label>
        <select class="form-select" name="frontend_security_block_copy">
          <option value="0" <?php echo setting('frontend_security_block_copy','0')==='0'?'selected':''; ?>>Disabled</option>
          <option value="1" <?php echo setting('frontend_security_block_copy','0')==='1'?'selected':''; ?>>Enabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Disable Image Drag</label>
        <select class="form-select" name="frontend_security_block_image_drag">
          <option value="1" <?php echo setting('frontend_security_block_image_drag','1')==='1'?'selected':''; ?>>Enabled</option>
          <option value="0" <?php echo setting('frontend_security_block_image_drag','1')==='0'?'selected':''; ?>>Disabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">DevTools Overlay Detector</label>
        <select class="form-select" name="frontend_security_devtools_overlay">
          <option value="0" <?php echo setting('frontend_security_devtools_overlay','0')==='0'?'selected':''; ?>>Disabled</option>
          <option value="1" <?php echo setting('frontend_security_devtools_overlay','0')==='1'?'selected':''; ?>>Enabled</option>
        </select>
        <div class="form-text">Can be aggressive; keep off unless required.</div>
      </div>
      <div class="col-lg-4">
        <label class="form-label">No-JavaScript Warning</label>
        <select class="form-select" name="frontend_security_noscript_warning">
          <option value="0" <?php echo setting('frontend_security_noscript_warning','0')==='0'?'selected':''; ?>>Disabled</option>
          <option value="1" <?php echo setting('frontend_security_noscript_warning','0')==='1'?'selected':''; ?>>Enabled</option>
        </select>
      </div>
      <div class="col-lg-4">
        <label class="form-label">Warning Message</label>
        <input class="form-control" name="frontend_security_warning_message" value="<?php echo esc(setting('frontend_security_warning_message','This action is disabled for website security.')); ?>">
      </div>
      <div class="col-12">
        <div class="alert alert-warning border mb-0">
          Browser protections cannot fully stop a technical user from inspecting source or disabling JavaScript. Use these settings as deterrents only. Sensitive data, files, prices, downloads, and permissions must remain protected server-side.
        </div>
      </div>
    </div>
  </section>


  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Header, Footer & Contact Details</h2>
        <p class="text-secondary mb-0">Control the contact information shown in header announcement, footer, and Contact Us page.</p>
      </div>
      <div class="form-check form-switch pt-1">
        <input type="hidden" name="contact_details_enabled" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="contact_details_enabled" name="contact_details_enabled" value="1" <?php echo setting('contact_details_enabled','1')==='1'?'checked':''; ?>>
        <label class="form-check-label" for="contact_details_enabled">Show contact details</label>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-4"><label class="form-label">Contact Page Title</label><input class="form-control" name="contact_page_title" value="<?php echo esc(setting('contact_page_title','Contact Us')); ?>"></div>
      <div class="col-lg-8"><label class="form-label">Contact Page Intro</label><input class="form-control" name="contact_page_intro" value="<?php echo esc(setting('contact_page_intro','Send us your requirement and our team will contact you shortly.')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Contact Email</label><input class="form-control" name="contact_email" value="<?php echo esc(setting('contact_email',SHOP_EMAIL)); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Contact Phone</label><input class="form-control" name="contact_phone" value="<?php echo esc(setting('contact_phone',SHOP_PHONE)); ?>"></div>
      <div class="col-lg-4"><label class="form-label">WhatsApp Number</label><input class="form-control" name="contact_whatsapp" value="<?php echo esc(setting('contact_whatsapp',SHOP_PHONE)); ?>"></div>
      <div class="col-lg-6"><label class="form-label">Address</label><textarea class="form-control" name="contact_address" rows="2"><?php echo esc(setting('contact_address',SHOP_ADDRESS)); ?></textarea></div>
      <div class="col-lg-3"><label class="form-label">Working Hours</label><textarea class="form-control" name="contact_hours" rows="2"><?php echo esc(setting('contact_hours','Monday to Saturday, 9:00 AM - 6:00 PM')); ?></textarea></div>
      <div class="col-lg-3"><label class="form-label">Map URL</label><textarea class="form-control" name="contact_map_url" rows="2"><?php echo esc(setting('contact_map_url','')); ?></textarea></div>
      <div class="col-lg-4"><label class="form-label">Footer Contact Title</label><input class="form-control" name="footer_contact_title" value="<?php echo esc(setting('footer_contact_title','Contact')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Guest Request Quote</label><select class="form-select" name="footer_hide_quote_for_guest"><option value="1" <?php echo setting('footer_hide_quote_for_guest','1')==='1'?'selected':''; ?>>Hide by default</option><option value="0" <?php echo setting('footer_hide_quote_for_guest','1')==='0'?'selected':''; ?>>Show if permission allows</option></select></div>
      <div class="col-lg-4"><label class="form-label">Guest Book Support</label><select class="form-select" name="footer_hide_booking_for_guest"><option value="1" <?php echo setting('footer_hide_booking_for_guest','1')==='1'?'selected':''; ?>>Hide by default</option><option value="0" <?php echo setting('footer_hide_booking_for_guest','1')==='0'?'selected':''; ?>>Show if permission allows</option></select></div>
      <div class="col-12"><label class="form-label">Permission Denied Message</label><input class="form-control" name="website_permission_denied_message" value="<?php echo esc(setting('website_permission_denied_message','Please login first or contact us to access this feature.')); ?>"><div class="form-text">This replaces the old technical message shown when a customer/guest opens a restricted website feature.</div></div>
      <div class="col-12"><div class="alert alert-light border mb-0">Guest/Public Visitor is hidden from Request Quote and Book Support by default using E-commerce Website Permissions. If a restricted page is opened directly, the visitor will see the friendly message above and be redirected to login or contact page.</div></div>
    </div>
  </section>


  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Booking Page Settings</h2>
        <p class="text-secondary mb-0">Change booking page text and service type dropdown options.</p>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-4"><label class="form-label">Booking Page Title</label><input class="form-control" name="booking_page_title" value="<?php echo esc(setting('booking_page_title','Book a Service')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Service Type Label</label><input class="form-control" name="booking_service_type_label" value="<?php echo esc(setting('booking_service_type_label','Service Type')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Submit Button Label</label><input class="form-control" name="booking_submit_label" value="<?php echo esc(setting('booking_submit_label','Submit Booking')); ?>"></div>
      <div class="col-12">
        <label class="form-label">Service Types</label>
        <textarea class="form-control" name="booking_service_types" rows="6" placeholder="One service type per line"><?php echo esc(setting('booking_service_types',"Remote Installation\nDiagnostic Software Support\nVehicle Diagnostic Consultation\nAccount / Download Support")); ?></textarea>
        <div class="form-text">Write one service type per line. These values appear in the booking page dropdown.</div>
      </div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Header Announcement Bar</h2>
        <p class="text-secondary mb-0">Show, hide, or rewrite the public message above the store header.</p>
      </div>
      <div class="form-check form-switch pt-1">
        <input type="hidden" name="header_utility_enabled" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="header_utility_enabled" name="header_utility_enabled" value="1" <?php echo setting('header_utility_enabled','0')==='1'?'checked':''; ?>>
        <label class="form-check-label" for="header_utility_enabled">Show bar</label>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-6"><label class="form-label">Primary Header Message</label><input class="form-control" name="header_utility_primary" value="<?php echo esc(setting('header_utility_primary','ERP-connected B2B + B2C commerce')); ?>"></div>
      <div class="col-lg-6"><label class="form-label">Secondary Header Message</label><input class="form-control" name="header_utility_secondary" value="<?php echo esc(setting('header_utility_secondary','Quote, invoice, stock, sales in one system')); ?>"></div>
      <div class="col-12"><hr class="my-2"></div>
      <div class="col-lg-4">
        <label class="form-label">B2B Enquiry Button</label>
        <select class="form-select" name="header_b2b_enquiry_enabled">
          <option value="0" <?php echo setting('header_b2b_enquiry_enabled','0')==='0'?'selected':''; ?>>Hide by default</option>
          <option value="1" <?php echo setting('header_b2b_enquiry_enabled','0')==='1'?'selected':''; ?>>Show</option>
        </select>
      </div>
      <div class="col-lg-4"><label class="form-label">B2B Button Label</label><input class="form-control" name="header_b2b_enquiry_label" value="<?php echo esc(setting('header_b2b_enquiry_label','B2B Enquiry')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">B2B Button URL</label><input class="form-control" name="header_b2b_enquiry_url" value="<?php echo esc(setting('header_b2b_enquiry_url','/contact.php')); ?>"></div>
      <div class="col-12"><hr class="my-2"><h3 class="h6 mb-0">Website B2B / B2C / ERP Labels</h3></div>
      <div class="col-lg-4"><label class="form-label">Footer Pills</label><select class="form-select" name="footer_pills_enabled"><option value="0" <?php echo setting('footer_pills_enabled','0')==='0'?'selected':''; ?>>Hide by default</option><option value="1" <?php echo setting('footer_pills_enabled','0')==='1'?'selected':''; ?>>Show</option></select></div>
      <div class="col-lg-2"><label class="form-label">Footer Pill 1</label><input class="form-control" name="footer_pill_1" value="<?php echo esc(setting('footer_pill_1','ERP')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Footer Pill 2</label><input class="form-control" name="footer_pill_2" value="<?php echo esc(setting('footer_pill_2','B2B')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Footer Pill 3</label><input class="form-control" name="footer_pill_3" value="<?php echo esc(setting('footer_pill_3','B2C')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Footer Pill 4</label><input class="form-control" name="footer_pill_4" value="<?php echo esc(setting('footer_pill_4','Inventory')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Mobile Drawer Pills</label><select class="form-select" name="mobile_drawer_pills_enabled"><option value="0" <?php echo setting('mobile_drawer_pills_enabled','0')==='0'?'selected':''; ?>>Hide by default</option><option value="1" <?php echo setting('mobile_drawer_pills_enabled','0')==='1'?'selected':''; ?>>Show</option></select></div>
      <div class="col-lg-2"><label class="form-label">Mobile Pill 1</label><input class="form-control" name="mobile_drawer_pill_1" value="<?php echo esc(setting('mobile_drawer_pill_1','ERP')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Mobile Pill 2</label><input class="form-control" name="mobile_drawer_pill_2" value="<?php echo esc(setting('mobile_drawer_pill_2','B2B')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Mobile Pill 3</label><input class="form-control" name="mobile_drawer_pill_3" value="<?php echo esc(setting('mobile_drawer_pill_3','B2C')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Mobile Pill 4</label><input class="form-control" name="mobile_drawer_pill_4" value="<?php echo esc(setting('mobile_drawer_pill_4','Inventory')); ?>"></div>
      <div class="col-12"><div class="alert alert-light border mb-0">B2B, B2C, ERP and Inventory labels are hidden by default. Enable these pills only when the customer wants those website badges.</div></div>
      <div class="col-12"><hr class="my-2"></div>
      <div class="col-lg-3"><label class="form-label">Header Request Quote Label</label><input class="form-control" name="header_request_quote_label" value="<?php echo esc(setting('header_request_quote_label','Contact Us')); ?>"><div class="form-text">Example: Contact Us, Request Quote, Enquire Now.</div></div>
      <div class="col-lg-3"><label class="form-label">Header Request Quote URL</label><input class="form-control" name="header_request_quote_url" value="<?php echo esc(setting('header_request_quote_url','/contact.php')); ?>"></div>
      <div class="col-lg-3"><label class="form-label">Header Book Support Label</label><input class="form-control" name="header_book_support_label" value="<?php echo esc(setting('header_book_support_label','Book Support')); ?>"></div>
      <div class="col-lg-3"><label class="form-label">Header Book Support URL</label><input class="form-control" name="header_book_support_url" value="<?php echo esc(setting('header_book_support_url','/booking.php')); ?>"></div>
      <div class="col-12"><div class="alert alert-light border mb-0">The header label can now be changed from “Request Quote” to “Contact Us” or any label you need. Visibility is still controlled by website permissions.</div></div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Homepage Section Visibility</h2>
        <p class="text-secondary mb-0">Turn major homepage blocks on or off with one click. Detailed copy lives in Homepage Builder.</p>
      </div>
    </div>
    <div class="row g-3">
      <?php
      $homepageToggles = [
          'homepage_hero_enabled' => 'Hero banner',
          'homepage_promo_ribbon_enabled' => 'Promo ribbon',
          'homepage_categories_enabled' => 'Shop-by-category block',
          'homepage_featured_products_enabled' => 'Featured products',
          'homepage_commercial_split_enabled' => 'B2B / B2C commercial split',
          'homepage_new_arrivals_enabled' => 'New arrivals',
          'homepage_services_enabled' => 'Service commerce cards',
          'homepage_trust_grid_enabled' => 'Trust / platform benefit grid',
      ];
      foreach ($homepageToggles as $toggleKey => $toggleLabel):
      ?>
      <div class="col-md-6">
        <div class="border rounded-4 p-3 h-100 bg-light-subtle d-flex justify-content-between align-items-center gap-3">
          <div>
            <strong class="d-block"><?php echo esc($toggleLabel); ?></strong>
            <small class="text-secondary">Visible on the public homepage</small>
          </div>
          <div class="form-check form-switch m-0">
            <input type="hidden" name="<?php echo esc($toggleKey); ?>" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="<?php echo esc($toggleKey); ?>" name="<?php echo esc($toggleKey); ?>" value="1" <?php echo setting($toggleKey,$toggleKey==='homepage_commercial_split_enabled'?'0':'1')==='1'?'checked':''; ?>>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Footer Brand Messaging</h2>
        <p class="text-secondary mb-0">Control the promotional text visible in the public footer.</p>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-12">
        <div class="form-check form-switch">
          <input type="hidden" name="footer_newsletter_enabled" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="footer_newsletter_enabled" name="footer_newsletter_enabled" value="1" <?php echo setting('footer_newsletter_enabled','1')==='1'?'checked':''; ?>>
          <label class="form-check-label" for="footer_newsletter_enabled">Show newsletter shell above footer</label>
        </div>
      </div>
      <div class="col-12">
        <div class="form-check form-switch">
          <input type="hidden" name="footer_about_enabled" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="footer_about_enabled" name="footer_about_enabled" value="1" <?php echo setting('footer_about_enabled','1')==='1'?'checked':''; ?>>
          <label class="form-check-label" for="footer_about_enabled">Show footer description</label>
        </div>
      </div>
      <div class="col-12"><label class="form-label">Footer Description</label><textarea class="form-control" name="footer_about_text" rows="3"><?php echo esc(setting('footer_about_text','Commerce frontend, B2B quote journey, customer checkout, and ERP-linked stock and sales operations.')); ?></textarea></div>
      <div class="col-12">
        <div class="form-check form-switch">
          <input type="hidden" name="footer_bottom_note_enabled" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="footer_bottom_note_enabled" name="footer_bottom_note_enabled" value="1" <?php echo setting('footer_bottom_note_enabled','1')==='1'?'checked':''; ?>>
          <label class="form-check-label" for="footer_bottom_note_enabled">Show footer bottom note</label>
        </div>
      </div>
      <div class="col-12"><label class="form-label">Footer Bottom Note</label><input class="form-control" name="footer_bottom_note" value="<?php echo esc(setting('footer_bottom_note','Built as an ERP-connected commerce suite.')); ?>"></div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div><h2 class="h4 mb-1">Default Operational Scope</h2><p class="text-secondary mb-0">Set the company, branch, warehouse, and stock location used by website orders and default ERP transactions.</p></div>
      <a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/companies.php">Manage Structure</a>
    </div>
    <div class="row g-3">
      <div class="col-lg-3"><label class="form-label">Default Company</label><select class="form-select" name="default_company_id"><?php foreach($scopeOptions['companies'] as $company): ?><option value="<?php echo (int)$company['id']; ?>" <?php echo (int)setting('default_company_id','0')===(int)$company['id']?'selected':''; ?>><?php echo esc($company['company_code'].' · '.$company['company_name']); ?></option><?php endforeach; ?></select></div>
      <div class="col-lg-3"><label class="form-label">Default Branch</label><select class="form-select" name="default_branch_id"><?php foreach($scopeOptions['branches'] as $branch): ?><option value="<?php echo (int)$branch['id']; ?>" <?php echo (int)setting('default_branch_id','0')===(int)$branch['id']?'selected':''; ?>><?php echo esc($branch['branch_code'].' · '.$branch['branch_name']); ?></option><?php endforeach; ?></select></div>
      <div class="col-lg-3"><label class="form-label">Default Warehouse</label><select class="form-select" name="default_warehouse_id"><?php foreach($scopeOptions['warehouses'] as $warehouse): ?><option value="<?php echo (int)$warehouse['id']; ?>" <?php echo (int)setting('default_warehouse_id','0')===(int)$warehouse['id']?'selected':''; ?>><?php echo esc($warehouse['warehouse_code'].' · '.$warehouse['warehouse_name']); ?></option><?php endforeach; ?></select></div>
      <div class="col-lg-3"><label class="form-label">Default Stock Location</label><select class="form-select" name="default_location_id"><?php foreach($scopeOptions['locations'] as $location): ?><option value="<?php echo (int)$location['id']; ?>" <?php echo (int)setting('default_location_id','0')===(int)$location['id']?'selected':''; ?>><?php echo esc($location['location_code'].' · '.$location['location_name']); ?></option><?php endforeach; ?></select></div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div><h2 class="h4 mb-1">Inventory Valuation & Intercompany Defaults</h2><p class="text-secondary mb-0">Control moving-average valuation defaults and automatic intercompany recognition journals.</p></div>
      <a class="btn btn-outline-primary" href="<?php echo esc(ADMIN_URL); ?>/erp/inventory-valuation.php">Open Valuation</a>
    </div>
    <div class="row g-3">
      <div class="col-lg-4"><label class="form-label">Valuation Method</label><select class="form-select" name="inventory_valuation_method"><option value="moving_average" <?php echo setting('inventory_valuation_method','moving_average')==='moving_average'?'selected':''; ?>>Moving Average</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Cost Ratio for New Demo / Blank-Cost Products</label><input class="form-control" type="number" step="0.01" min="0" max="1" name="inventory_default_cost_ratio" value="<?php echo esc(setting('inventory_default_cost_ratio','0.60')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Intercompany Journals</label><select class="form-select" name="intercompany_auto_journals"><option value="1" <?php echo setting('intercompany_auto_journals','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('intercompany_auto_journals','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Supplier Invoice Match Tolerance</label><input class="form-control" type="number" step="0.01" min="0" name="supplier_invoice_match_tolerance" value="<?php echo esc(setting('supplier_invoice_match_tolerance','1.00')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Credit Exposure Includes Open Sales Orders</label><select class="form-select" name="customer_credit_include_open_sales_orders"><option value="1" <?php echo setting('customer_credit_include_open_sales_orders','1')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo setting('customer_credit_include_open_sales_orders','1')==='0'?'selected':''; ?>>No</option></select></div>
      <div class="col-lg-4"><label class="form-label">Block Orders Above Credit Limit</label><select class="form-select" name="customer_credit_block_when_exceeded"><option value="1" <?php echo setting('customer_credit_block_when_exceeded','1')==='1'?'selected':''; ?>>Block & Route Approval</option><option value="0" <?php echo setting('customer_credit_block_when_exceeded','1')==='0'?'selected':''; ?>>Warn Only</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Labor Hourly Rate</label><input class="form-control" type="number" step="0.01" name="default_labor_hourly_rate" value="<?php echo esc(setting('default_labor_hourly_rate','150')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Technician Hourly Cost</label><input class="form-control" type="number" step="0.01" name="default_technician_hourly_cost" value="<?php echo esc(setting('default_technician_hourly_cost','45')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Budget Warning Threshold %</label><input class="form-control" type="number" step="0.01" name="budget_warning_threshold_percent" value="<?php echo esc(setting('budget_warning_threshold_percent','80')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Executive Margin Warning %</label><input class="form-control" type="number" step="0.01" name="executive_dashboard_margin_warning_percent" value="<?php echo esc(setting('executive_dashboard_margin_warning_percent','15')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Report Export Max Rows</label><input class="form-control" type="number" min="100" name="report_export_max_rows" value="<?php echo esc(setting('report_export_max_rows','5000')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Notifications</label><select class="form-select" name="notification_auto_generate_enabled"><option value="1" <?php echo setting('notification_auto_generate_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('notification_auto_generate_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default API Key Expiry Days</label><input class="form-control" type="number" min="1" name="api_key_default_expiry_days" value="<?php echo esc(setting('api_key_default_expiry_days','365')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Maintenance Mode</label><select class="form-select" name="maintenance_mode_enabled"><option value="0" <?php echo setting('maintenance_mode_enabled','0')==='0'?'selected':''; ?>>Disabled</option><option value="1" <?php echo setting('maintenance_mode_enabled','0')==='1'?'selected':''; ?>>Enabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Login Max Attempts</label><input class="form-control" type="number" min="1" name="login_max_attempts" value="<?php echo esc(setting('login_max_attempts','5')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Session Timeout Minutes</label><input class="form-control" type="number" min="5" name="session_timeout_minutes" value="<?php echo esc(setting('session_timeout_minutes','120')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Backup Directory</label><input class="form-control" name="backup_directory" value="<?php echo esc(setting('backup_directory','backups')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Backup Retention Days</label><input class="form-control" type="number" min="1" name="backup_retention_days" value="<?php echo esc(setting('backup_retention_days','14')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Cron Secret</label><input class="form-control" name="cron_secret" value="<?php echo esc(setting('cron_secret','')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">System Error Logging</label><select class="form-select" name="system_error_logging_enabled"><option value="1" <?php echo setting('system_error_logging_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('system_error_logging_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Disk Warning MB</label><input class="form-control" type="number" min="64" name="health_disk_warning_mb" value="<?php echo esc(setting('health_disk_warning_mb','512')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Widget Refresh Seconds</label><input class="form-control" type="number" min="60" name="dashboard_widget_refresh_seconds" value="<?php echo esc(setting('dashboard_widget_refresh_seconds','300')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Email From Name</label><input class="form-control" name="email_from_name" value="<?php echo esc(setting('email_from_name',SHOP_NAME)); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Email From Address</label><input class="form-control" type="email" name="email_from_address" value="<?php echo esc(setting('email_from_address',SHOP_EMAIL)); ?>"></div>
      <div class="col-lg-4"><label class="form-label">License Enforcement Mode</label><select class="form-select" name="license_enforcement_mode"><option value="monitor" <?php echo setting('license_enforcement_mode','monitor')==='monitor'?'selected':''; ?>>Monitor Only</option><option value="enforce" <?php echo setting('license_enforcement_mode','monitor')==='enforce'?'selected':''; ?>>Enforce Limits</option></select></div>
      <div class="col-lg-4"><label class="form-label">License Grace Days</label><input class="form-control" type="number" min="0" name="license_grace_days" value="<?php echo esc(setting('license_grace_days','14')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Update Channel</label><select class="form-select" name="update_channel"><option value="stable" <?php echo setting('update_channel','stable')==='stable'?'selected':''; ?>>Stable</option><option value="beta" <?php echo setting('update_channel','stable')==='beta'?'selected':''; ?>>Beta</option><option value="lts" <?php echo setting('update_channel','stable')==='lts'?'selected':''; ?>>LTS</option></select></div>
      <div class="col-lg-4"><label class="form-label">Upgrade Mode</label><select class="form-select" name="upgrade_mode_enabled"><option value="0" <?php echo setting('upgrade_mode_enabled','0')==='0'?'selected':''; ?>>Disabled</option><option value="1" <?php echo setting('upgrade_mode_enabled','0')==='1'?'selected':''; ?>>Enabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">SaaS Mode</label><select class="form-select" name="saas_mode_enabled"><option value="0" <?php echo setting('saas_mode_enabled','0')==='0'?'selected':''; ?>>Single Installation</option><option value="1" <?php echo setting('saas_mode_enabled','0')==='1'?'selected':''; ?>>SaaS Ready</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Subscription Plan</label><input class="form-control" name="default_subscription_plan" value="<?php echo esc(setting('default_subscription_plan','GROWTH')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Customer Portal</label><select class="form-select" name="customer_portal_enabled"><option value="1" <?php echo setting('customer_portal_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('customer_portal_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Dashboard Invoice Disputes Button</label><select class="form-select" name="customer_dashboard_invoice_disputes_enabled"><option value="0" <?php echo setting('customer_dashboard_invoice_disputes_enabled','0')==='0'?'selected':''; ?>>Hidden by default</option><option value="1" <?php echo setting('customer_dashboard_invoice_disputes_enabled','0')==='1'?'selected':''; ?>>Show</option></select><div class="form-text">Controls the button linking to /user/invoice-disputes.php on customer dashboard.</div></div>
      <div class="col-lg-4"><label class="form-label">Dashboard Payment Promises Button</label><select class="form-select" name="customer_dashboard_payment_promises_enabled"><option value="0" <?php echo setting('customer_dashboard_payment_promises_enabled','0')==='0'?'selected':''; ?>>Hidden by default</option><option value="1" <?php echo setting('customer_dashboard_payment_promises_enabled','0')==='1'?'selected':''; ?>>Show</option></select><div class="form-text">Controls the button linking to /user/payment-promises.php on customer dashboard.</div></div>
      <div class="col-lg-4"><label class="form-label">Vendor Portal</label><select class="form-select" name="vendor_portal_enabled"><option value="1" <?php echo setting('vendor_portal_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('vendor_portal_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Technician Portal</label><select class="form-select" name="technician_portal_enabled"><option value="1" <?php echo setting('technician_portal_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('technician_portal_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Mobile ERP</label><select class="form-select" name="mobile_erp_enabled"><option value="1" <?php echo setting('mobile_erp_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('mobile_erp_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Commercial Package Prefix</label><input class="form-control" name="commercial_package_prefix" value="<?php echo esc(setting('commercial_package_prefix','PKG')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Documentation Article Prefix</label><input class="form-control" name="documentation_article_prefix" value="<?php echo esc(setting('documentation_article_prefix','DOCART')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Documentation Asset Prefix</label><input class="form-control" name="documentation_asset_prefix" value="<?php echo esc(setting('documentation_asset_prefix','DOCAS')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Training Course Prefix</label><input class="form-control" name="training_course_prefix" value="<?php echo esc(setting('training_course_prefix','TRN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Training Checklist Prefix</label><input class="form-control" name="training_checklist_prefix" value="<?php echo esc(setting('training_checklist_prefix','TRNCHK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Demo Credential Prefix</label><input class="form-control" name="demo_credential_prefix" value="<?php echo esc(setting('demo_credential_prefix','DEMOCR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Client Onboarding Prefix</label><input class="form-control" name="client_onboarding_prefix" value="<?php echo esc(setting('client_onboarding_prefix','ONB')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Feature Comparison Prefix</label><input class="form-control" name="feature_comparison_prefix" value="<?php echo esc(setting('feature_comparison_prefix','FCMP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Sales Brochure Prefix</label><input class="form-control" name="sales_brochure_prefix" value="<?php echo esc(setting('sales_brochure_prefix','SBR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Commercial Currency</label><select class="form-select" name="commercial_default_currency">
        <?php foreach(['AED','USD','EUR','GBP','SAR','QAR','KWD','OMR','BHD'] as $cur): ?>
          <option value="<?php echo esc($cur); ?>" <?php echo setting('commercial_default_currency','AED')===$cur?'selected':''; ?>><?php echo esc($cur); ?></option>
        <?php endforeach; ?>
      </select><div class="form-text">This now saves correctly. Use USD for dollar-based commercial packages.</div></div>
      <div class="col-12"><hr class="my-2"><h3 class="h6 mb-0">Product Page E-commerce Slogan</h3></div>
      <div class="col-lg-4"><label class="form-label">Show Slogan Band</label><select class="form-select" name="product_page_slogan_enabled"><option value="1" <?php echo setting('product_page_slogan_enabled','1')==='1'?'selected':''; ?>>Show</option><option value="0" <?php echo setting('product_page_slogan_enabled','1')==='0'?'selected':''; ?>>Hide</option></select></div>
      <div class="col-lg-4"><label class="form-label">Slogan Icon</label><input class="form-control" name="product_page_slogan_icon" value="<?php echo esc(setting('product_page_slogan_icon','bi-bag-check')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Slogan Title</label><input class="form-control" name="product_page_slogan_title" value="<?php echo esc(setting('product_page_slogan_title','E-commerce made simple for every customer.')); ?>"></div>
      <div class="col-12"><label class="form-label">Slogan Text</label><textarea class="form-control" name="product_page_slogan_text" rows="2"><?php echo esc(setting('product_page_slogan_text','Browse products, add to cart, pay online, and access your digital resources from one clean customer account.')); ?></textarea></div>
      <div class="col-12"><hr class="my-2"><h3 class="h6 mb-0">Product Page Trust Labels</h3></div>
      <div class="col-lg-4"><label class="form-label">Trust Labels</label><select class="form-select" name="product_page_trust_enabled"><option value="0" <?php echo setting('product_page_trust_enabled','0')==='0'?'selected':''; ?>>Hide by default</option><option value="1" <?php echo setting('product_page_trust_enabled','0')==='1'?'selected':''; ?>>Show</option></select></div>
      <div class="col-lg-2"><label class="form-label">Label 1 Icon</label><input class="form-control" name="product_page_trust_1_icon" value="<?php echo esc(setting('product_page_trust_1_icon','bi-building')); ?>"></div>
      <div class="col-lg-6"><label class="form-label">Label 1 Text</label><input class="form-control" name="product_page_trust_1_text" value="<?php echo esc(setting('product_page_trust_1_text','Company pricing path')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Label 2 Icon</label><input class="form-control" name="product_page_trust_2_icon" value="<?php echo esc(setting('product_page_trust_2_icon','bi-receipt')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Label 2 Text</label><input class="form-control" name="product_page_trust_2_text" value="<?php echo esc(setting('product_page_trust_2_text','ERP invoice workflow')); ?>"></div>
      <div class="col-lg-2"><label class="form-label">Label 3 Icon</label><input class="form-control" name="product_page_trust_3_icon" value="<?php echo esc(setting('product_page_trust_3_icon','bi-headset')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Label 3 Text</label><input class="form-control" name="product_page_trust_3_text" value="<?php echo esc(setting('product_page_trust_3_text','Sales support CTA')); ?>"></div>
      <div class="col-12"><hr class="my-2"><h3 class="h6 mb-0">Product Page Commercial Notes</h3></div>
      <div class="col-lg-4"><label class="form-label">Commercial Notes Tab</label><select class="form-select" name="product_page_commercial_notes_enabled"><option value="0" <?php echo setting('product_page_commercial_notes_enabled','0')==='0'?'selected':''; ?>>Hide by default</option><option value="1" <?php echo setting('product_page_commercial_notes_enabled','0')==='1'?'selected':''; ?>>Show</option></select></div>
      <div class="col-lg-8"><label class="form-label">Commercial Notes Tab Title</label><input class="form-control" name="product_page_commercial_notes_title" value="<?php echo esc(setting('product_page_commercial_notes_title','Commercial Notes')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Note 1 Title</label><input class="form-control" name="product_page_commercial_note_1_title" value="<?php echo esc(setting('product_page_commercial_note_1_title','Direct online purchase')); ?>"></div>
      <div class="col-lg-8"><label class="form-label">Note 1 Text</label><input class="form-control" name="product_page_commercial_note_1_text" value="<?php echo esc(setting('product_page_commercial_note_1_text','Customers can buy ready products through a clean cart and checkout journey.')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Note 2 Title</label><input class="form-control" name="product_page_commercial_note_2_title" value="<?php echo esc(setting('product_page_commercial_note_2_title','Customer account access')); ?>"></div>
      <div class="col-lg-8"><label class="form-label">Note 2 Text</label><input class="form-control" name="product_page_commercial_note_2_text" value="<?php echo esc(setting('product_page_commercial_note_2_text','Digital files, order history, invoices, and resources can be accessed from the customer account.')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Note 3 Title</label><input class="form-control" name="product_page_commercial_note_3_title" value="<?php echo esc(setting('product_page_commercial_note_3_title','Support-led selling')); ?>"></div>
      <div class="col-lg-8"><label class="form-label">Note 3 Text</label><input class="form-control" name="product_page_commercial_note_3_text" value="<?php echo esc(setting('product_page_commercial_note_3_text','High-value products can still connect to support, contact, and quotation workflows when needed.')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Implementation Days</label><input class="form-control" type="number" name="commercial_default_implementation_days" value="<?php echo esc(setting('commercial_default_implementation_days','7')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Documentation Export Format</label><input class="form-control" name="documentation_export_format" value="<?php echo esc(setting('documentation_export_format','print_pdf')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Training Duration</label><input class="form-control" type="number" name="training_default_duration_minutes" value="<?php echo esc(setting('training_default_duration_minutes','60')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Production Repair Prefix</label><input class="form-control" name="production_repair_prefix" value="<?php echo esc(setting('production_repair_prefix','PRUN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Schema Check Prefix</label><input class="form-control" name="production_schema_check_prefix" value="<?php echo esc(setting('production_schema_check_prefix','SCHK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Upgrade Backup Prefix</label><input class="form-control" name="production_backup_prefix" value="<?php echo esc(setting('production_backup_prefix','PBACK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Demo Batch Prefix</label><input class="form-control" name="production_demo_batch_prefix" value="<?php echo esc(setting('production_demo_batch_prefix','DEMO')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Installer Event Prefix</label><input class="form-control" name="production_installer_event_prefix" value="<?php echo esc(setting('production_installer_event_prefix','IEVT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Release Checklist Prefix</label><input class="form-control" name="production_release_checklist_prefix" value="<?php echo esc(setting('production_release_checklist_prefix','REL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Production Mode</label><select class="form-select" name="production_mode_enabled"><option value="1" <?php echo setting('production_mode_enabled','0')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('production_mode_enabled','0')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Safe Upgrade Mode</label><select class="form-select" name="upgrade_safe_mode_enabled"><option value="1" <?php echo setting('upgrade_safe_mode_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('upgrade_safe_mode_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Backup Before Upgrade</label><select class="form-select" name="backup_before_upgrade_enabled"><option value="1" <?php echo setting('backup_before_upgrade_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('backup_before_upgrade_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Installer Rollback</label><select class="form-select" name="installer_rollback_enabled"><option value="1" <?php echo setting('installer_rollback_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('installer_rollback_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Repair Center</label><select class="form-select" name="repair_center_enabled"><option value="1" <?php echo setting('repair_center_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('repair_center_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Schema Checker</label><select class="form-select" name="schema_checker_enabled"><option value="1" <?php echo setting('schema_checker_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('schema_checker_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Demo Data Manager</label><select class="form-select" name="demo_data_manager_enabled"><option value="1" <?php echo setting('demo_data_manager_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('demo_data_manager_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Production Health Min Score</label><input class="form-control" type="number" name="production_health_min_score" value="<?php echo esc(setting('production_health_min_score','85')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">B2B Price List Prefix</label><input class="form-control" name="b2b_price_list_prefix" value="<?php echo esc(setting('b2b_price_list_prefix','B2BPL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Customer Price Rule Prefix</label><input class="form-control" name="customer_price_rule_prefix" value="<?php echo esc(setting('customer_price_rule_prefix','CPR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Product Bundle Prefix</label><input class="form-control" name="product_bundle_prefix" value="<?php echo esc(setting('product_bundle_prefix','BNDL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Digital License Pool Prefix</label><input class="form-control" name="digital_license_pool_prefix" value="<?php echo esc(setting('digital_license_pool_prefix','DLIC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Digital License Assignment Prefix</label><input class="form-control" name="digital_license_assignment_prefix" value="<?php echo esc(setting('digital_license_assignment_prefix','DLAS')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Wishlist Prefix</label><input class="form-control" name="wishlist_prefix" value="<?php echo esc(setting('wishlist_prefix','WISH')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Comparison Prefix</label><input class="form-control" name="comparison_prefix" value="<?php echo esc(setting('comparison_prefix','COMP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Quote Request Prefix</label><input class="form-control" name="quote_request_prefix" value="<?php echo esc(setting('quote_request_prefix','QRQ')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Bulk Order Prefix</label><input class="form-control" name="bulk_order_prefix" value="<?php echo esc(setting('bulk_order_prefix','BULK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Discount Rule Prefix</label><input class="form-control" name="ecommerce_discount_rule_prefix" value="<?php echo esc(setting('ecommerce_discount_rule_prefix','EDISC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Ecommerce Activity Prefix</label><input class="form-control" name="ecommerce_activity_prefix" value="<?php echo esc(setting('ecommerce_activity_prefix','EACT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Advanced Ecommerce</label><select class="form-select" name="advanced_ecommerce_enabled"><option value="1" <?php echo setting('advanced_ecommerce_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('advanced_ecommerce_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">B2B Pricing</label><select class="form-select" name="b2b_pricing_enabled"><option value="1" <?php echo setting('b2b_pricing_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('b2b_pricing_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Wishlist</label><select class="form-select" name="wishlist_enabled"><option value="1" <?php echo setting('wishlist_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('wishlist_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Comparison</label><select class="form-select" name="comparison_enabled"><option value="1" <?php echo setting('comparison_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('comparison_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Request Quote</label><select class="form-select" name="request_quote_enabled"><option value="1" <?php echo setting('request_quote_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('request_quote_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Bulk Order</label><select class="form-select" name="bulk_order_enabled"><option value="1" <?php echo setting('bulk_order_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('bulk_order_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Digital License Delivery</label><select class="form-select" name="digital_license_delivery_enabled"><option value="1" <?php echo setting('digital_license_delivery_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('digital_license_delivery_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Bundle Builder</label><select class="form-select" name="bundle_builder_enabled"><option value="1" <?php echo setting('bundle_builder_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('bundle_builder_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default B2B Discount %</label><input class="form-control" type="number" step="0.01" name="default_b2b_discount_percent" value="<?php echo esc(setting('default_b2b_discount_percent','5')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Document Prefix</label><input class="form-control" name="document_library_prefix" value="<?php echo esc(setting('document_library_prefix','DOC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Folder Prefix</label><input class="form-control" name="document_folder_prefix" value="<?php echo esc(setting('document_folder_prefix','FLD')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Category Prefix</label><input class="form-control" name="document_category_prefix" value="<?php echo esc(setting('document_category_prefix','DCAT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Approval Prefix</label><input class="form-control" name="document_approval_prefix" value="<?php echo esc(setting('document_approval_prefix','DAPP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Expiry Alert Prefix</label><input class="form-control" name="document_expiry_alert_prefix" value="<?php echo esc(setting('document_expiry_alert_prefix','DEXP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Expiry Alert Days</label><input class="form-control" type="number" name="document_default_expiry_alert_days" value="<?php echo esc(setting('document_default_expiry_alert_days','30')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Max Upload MB</label><input class="form-control" type="number" name="document_max_upload_mb" value="<?php echo esc(setting('document_max_upload_mb','25')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Allowed Extensions</label><input class="form-control" name="document_allowed_extensions" value="<?php echo esc(setting('document_allowed_extensions','pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt,csv,zip')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Approval Default</label><select class="form-select" name="document_require_approval_default"><option value="1" <?php echo setting('document_require_approval_default','0')==='1'?'selected':''; ?>>Required</option><option value="0" <?php echo setting('document_require_approval_default','0')==='0'?'selected':''; ?>>Not required</option></select></div>
      <div class="col-lg-4"><label class="form-label">Versioning</label><select class="form-select" name="document_versioning_enabled"><option value="1" <?php echo setting('document_versioning_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('document_versioning_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Access Logging</label><select class="form-select" name="document_access_logging_enabled"><option value="1" <?php echo setting('document_access_logging_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('document_access_logging_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">PWA App Name</label><input class="form-control" name="pwa_app_name" value="<?php echo esc(setting('pwa_app_name',setting('shop_name','ERP').' ERP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">PWA Short Name</label><input class="form-control" name="pwa_short_name" value="<?php echo esc(setting('pwa_short_name','ERP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Theme Color</label><input class="form-control" name="pwa_theme_color" value="<?php echo esc(setting('pwa_theme_color','#0f172a')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Background Color</label><input class="form-control" name="pwa_background_color" value="<?php echo esc(setting('pwa_background_color','#ffffff')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Display Mode</label><select class="form-select" name="pwa_display_mode"><option value="standalone" <?php echo setting('pwa_display_mode','standalone')==='standalone'?'selected':''; ?>>standalone</option><option value="fullscreen" <?php echo setting('pwa_display_mode','standalone')==='fullscreen'?'selected':''; ?>>fullscreen</option><option value="browser" <?php echo setting('pwa_display_mode','standalone')==='browser'?'selected':''; ?>>browser</option></select></div>
      <div class="col-lg-4"><label class="form-label">Start URL</label><input class="form-control" name="pwa_start_url" value="<?php echo esc(setting('pwa_start_url','/mobile/index.php')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Offline Page</label><input class="form-control" name="pwa_offline_page" value="<?php echo esc(setting('pwa_offline_page','/offline.php')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Service Worker</label><select class="form-select" name="pwa_service_worker_enabled"><option value="1" <?php echo setting('pwa_service_worker_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('pwa_service_worker_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Install Prompt</label><select class="form-select" name="mobile_install_prompt_enabled"><option value="1" <?php echo setting('mobile_install_prompt_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('mobile_install_prompt_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Offline Mode</label><select class="form-select" name="mobile_offline_mode_enabled"><option value="1" <?php echo setting('mobile_offline_mode_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('mobile_offline_mode_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Push Notifications</label><select class="form-select" name="push_notifications_enabled"><option value="1" <?php echo setting('push_notifications_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('push_notifications_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">VAPID Public Key</label><input class="form-control" name="push_vapid_public_key" value="<?php echo esc(setting('push_vapid_public_key','')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">VAPID Private Key</label><input class="form-control" name="push_vapid_private_key" value="<?php echo esc(setting('push_vapid_private_key','')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Mobile App Version</label><input class="form-control" name="mobile_app_version" value="<?php echo esc(setting('mobile_app_version','1.0.0')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Cache Version</label><input class="form-control" name="mobile_cache_version" value="<?php echo esc(setting('mobile_cache_version','v1')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Sync Retry Limit</label><input class="form-control" type="number" name="mobile_sync_retry_limit" value="<?php echo esc(setting('mobile_sync_retry_limit','3')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">PWA Asset Prefix</label><input class="form-control" name="pwa_asset_prefix" value="<?php echo esc(setting('pwa_asset_prefix','PWA')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Push Queue Prefix</label><input class="form-control" name="push_queue_prefix" value="<?php echo esc(setting('push_queue_prefix','PUSH')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Device Session Prefix</label><input class="form-control" name="device_session_prefix" value="<?php echo esc(setting('device_session_prefix','MOBDEV')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Install Event Prefix</label><input class="form-control" name="mobile_install_event_prefix" value="<?php echo esc(setting('mobile_install_event_prefix','MOBINS')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Mobile Sync Prefix</label><input class="form-control" name="mobile_sync_prefix" value="<?php echo esc(setting('mobile_sync_prefix','MSYNC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">API Endpoint Prefix</label><input class="form-control" name="api_endpoint_prefix" value="<?php echo esc(setting('api_endpoint_prefix','APIEND')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">API Usage Limit Prefix</label><input class="form-control" name="api_usage_limit_prefix" value="<?php echo esc(setting('api_usage_limit_prefix','APILIM')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Webhook Template Prefix</label><input class="form-control" name="webhook_template_prefix" value="<?php echo esc(setting('webhook_template_prefix','WHTPL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Webhook Retry Prefix</label><input class="form-control" name="webhook_retry_prefix" value="<?php echo esc(setting('webhook_retry_prefix','WHRTY')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Integration Mapping Prefix</label><input class="form-control" name="integration_mapping_prefix" value="<?php echo esc(setting('integration_mapping_prefix','IMAP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Integration Error Prefix</label><input class="form-control" name="integration_error_prefix" value="<?php echo esc(setting('integration_error_prefix','IERR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Connector Template Prefix</label><input class="form-control" name="integration_template_prefix" value="<?php echo esc(setting('integration_template_prefix','ICONN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Accounting Export Prefix</label><input class="form-control" name="accounting_export_batch_prefix" value="<?php echo esc(setting('accounting_export_batch_prefix','AEXP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Marketplace Queue Prefix</label><input class="form-control" name="marketplace_sync_prefix" value="<?php echo esc(setting('marketplace_sync_prefix','MKTQ')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Daily API Limit</label><input class="form-control" type="number" name="api_default_daily_limit" value="<?php echo esc(setting('api_default_daily_limit','1000')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">API Rate Limit</label><select class="form-select" name="api_rate_limit_enabled"><option value="1" <?php echo setting('api_rate_limit_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('api_rate_limit_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Webhook Max Retries</label><input class="form-control" type="number" name="webhook_max_retries" value="<?php echo esc(setting('webhook_max_retries','3')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Webhook Retry Minutes</label><input class="form-control" type="number" name="webhook_retry_minutes" value="<?php echo esc(setting('webhook_retry_minutes','15')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Integration Payload Logging</label><select class="form-select" name="integration_payload_logging_enabled"><option value="1" <?php echo setting('integration_payload_logging_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('integration_payload_logging_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Subscription Invoice Prefix</label><input class="form-control" name="saas_subscription_invoice_prefix" value="<?php echo esc(setting('saas_subscription_invoice_prefix','SUBINV')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Subscription Payment Prefix</label><input class="form-control" name="saas_subscription_payment_prefix" value="<?php echo esc(setting('saas_subscription_payment_prefix','SUBPAY')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Trial Account Prefix</label><input class="form-control" name="trial_account_prefix" value="<?php echo esc(setting('trial_account_prefix','TRIAL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Plan Change Prefix</label><input class="form-control" name="plan_change_prefix" value="<?php echo esc(setting('plan_change_prefix','PLNCHG')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Usage Enforcement Prefix</label><input class="form-control" name="usage_enforcement_prefix" value="<?php echo esc(setting('usage_enforcement_prefix','USG')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Tenant Domain Prefix</label><input class="form-control" name="tenant_domain_prefix" value="<?php echo esc(setting('tenant_domain_prefix','DOM')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Tenant Onboarding Prefix</label><input class="form-control" name="tenant_onboarding_prefix" value="<?php echo esc(setting('tenant_onboarding_prefix','ONB')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Trial Days</label><input class="form-control" type="number" name="default_trial_days" value="<?php echo esc(setting('default_trial_days','14')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">SaaS Tax Rate %</label><input class="form-control" type="number" step="0.01" name="saas_tax_rate_percent" value="<?php echo esc(setting('saas_tax_rate_percent','5')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Usage Enforcement</label><select class="form-select" name="usage_enforcement_enabled"><option value="1" <?php echo setting('usage_enforcement_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('usage_enforcement_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Auto Suspend Expired Trials</label><select class="form-select" name="auto_suspend_expired_trials"><option value="1" <?php echo setting('auto_suspend_expired_trials','0')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('auto_suspend_expired_trials','0')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Security Event Prefix</label><input class="form-control" name="security_event_prefix" value="<?php echo esc(setting('security_event_prefix','SEV')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Login Session Prefix</label><input class="form-control" name="login_session_prefix" value="<?php echo esc(setting('login_session_prefix','LGS')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Permission Change Prefix</label><input class="form-control" name="permission_change_prefix" value="<?php echo esc(setting('permission_change_prefix','PCH')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Data Export Prefix</label><input class="form-control" name="data_export_prefix" value="<?php echo esc(setting('data_export_prefix','DEXP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Sensitive Action Prefix</label><input class="form-control" name="sensitive_action_prefix" value="<?php echo esc(setting('sensitive_action_prefix','SAAP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">IP Rule Prefix</label><input class="form-control" name="ip_rule_prefix" value="<?php echo esc(setting('ip_rule_prefix','IPR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Compliance Checklist Prefix</label><input class="form-control" name="compliance_checklist_prefix" value="<?php echo esc(setting('compliance_checklist_prefix','COMP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Password Min Length</label><input class="form-control" type="number" name="password_min_length" value="<?php echo esc(setting('password_min_length','8')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Require Uppercase</label><select class="form-select" name="password_require_uppercase"><option value="1" <?php echo setting('password_require_uppercase','1')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo setting('password_require_uppercase','1')==='0'?'selected':''; ?>>No</option></select></div>
      <div class="col-lg-4"><label class="form-label">Require Number</label><select class="form-select" name="password_require_number"><option value="1" <?php echo setting('password_require_number','1')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo setting('password_require_number','1')==='0'?'selected':''; ?>>No</option></select></div>
      <div class="col-lg-4"><label class="form-label">2FA Foundation</label><select class="form-select" name="two_factor_foundation_enabled"><option value="1" <?php echo setting('two_factor_foundation_enabled','0')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('two_factor_foundation_enabled','0')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">IP Access Control</label><select class="form-select" name="ip_access_control_enabled"><option value="1" <?php echo setting('ip_access_control_enabled','0')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('ip_access_control_enabled','0')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Export Tracking</label><select class="form-select" name="data_export_tracking_enabled"><option value="1" <?php echo setting('data_export_tracking_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('data_export_tracking_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Sensitive Action Approval</label><select class="form-select" name="sensitive_action_approval_enabled"><option value="1" <?php echo setting('sensitive_action_approval_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('sensitive_action_approval_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Workflow Builder Prefix</label><input class="form-control" name="workflow_builder_rule_prefix" value="<?php echo esc(setting('workflow_builder_rule_prefix','WFB')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Workflow Log Prefix</label><input class="form-control" name="workflow_builder_log_prefix" value="<?php echo esc(setting('workflow_builder_log_prefix','WFLOG')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Workflow Escalation Prefix</label><input class="form-control" name="workflow_escalation_prefix" value="<?php echo esc(setting('workflow_escalation_prefix','WFESC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Workflow Builder Enabled</label><select class="form-select" name="workflow_builder_enabled"><option value="1" <?php echo setting('workflow_builder_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('workflow_builder_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Task Due Days</label><input class="form-control" type="number" name="workflow_default_task_due_days" value="<?php echo esc(setting('workflow_default_task_due_days','2')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Approval Escalation Days</label><input class="form-control" type="number" name="workflow_approval_escalation_days" value="<?php echo esc(setting('workflow_approval_escalation_days','2')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Overdue Invoice Days</label><input class="form-control" type="number" name="workflow_overdue_invoice_days" value="<?php echo esc(setting('workflow_overdue_invoice_days','7')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">AI Run Prefix</label><input class="form-control" name="ai_automation_run_prefix" value="<?php echo esc(setting('ai_automation_run_prefix','AIRUN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">AI Risk Prefix</label><input class="form-control" name="ai_risk_score_prefix" value="<?php echo esc(setting('ai_risk_score_prefix','RSK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">AI Recommendation Prefix</label><input class="form-control" name="ai_decision_recommendation_prefix" value="<?php echo esc(setting('ai_decision_recommendation_prefix','AIREC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Action Suggestion Prefix</label><input class="form-control" name="ai_action_suggestion_prefix" value="<?php echo esc(setting('ai_action_suggestion_prefix','ACTSUG')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">AI Playbook Prefix</label><input class="form-control" name="ai_playbook_prefix" value="<?php echo esc(setting('ai_playbook_prefix','AIPB')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Risk Threshold</label><input class="form-control" type="number" step="0.01" name="ai_default_risk_threshold" value="<?php echo esc(setting('ai_default_risk_threshold','70')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Confidence Threshold</label><input class="form-control" type="number" step="0.01" name="ai_default_confidence_threshold" value="<?php echo esc(setting('ai_default_confidence_threshold','65')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Create Action Suggestions</label><select class="form-select" name="ai_auto_create_action_suggestions"><option value="1" <?php echo setting('ai_auto_create_action_suggestions','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('ai_auto_create_action_suggestions','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">BI Metric Prefix</label><input class="form-control" name="bi_metric_prefix" value="<?php echo esc(setting('bi_metric_prefix','BIM')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">KPI Alert Rule Prefix</label><input class="form-control" name="kpi_alert_rule_prefix" value="<?php echo esc(setting('kpi_alert_rule_prefix','KAL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Dashboard Filter Prefix</label><input class="form-control" name="dashboard_filter_prefix" value="<?php echo esc(setting('dashboard_filter_prefix','BIF')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Dashboard Share Prefix</label><input class="form-control" name="dashboard_share_prefix" value="<?php echo esc(setting('dashboard_share_prefix','BISHARE')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Report Drilldown Prefix</label><input class="form-control" name="report_drilldown_prefix" value="<?php echo esc(setting('report_drilldown_prefix','RDD')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Report Storyboard Prefix</label><input class="form-control" name="report_storyboard_prefix" value="<?php echo esc(setting('report_storyboard_prefix','STORY')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Dataset Cache TTL Minutes</label><input class="form-control" type="number" name="dataset_cache_ttl_minutes" value="<?php echo esc(setting('dataset_cache_ttl_minutes','60')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default BI Date Range Days</label><input class="form-control" type="number" name="bi_default_date_range_days" value="<?php echo esc(setting('bi_default_date_range_days','30')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Finance Automation Run Prefix</label><input class="form-control" name="finance_automation_run_prefix" value="<?php echo esc(setting('finance_automation_run_prefix','FAR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Recurring Journal Prefix</label><input class="form-control" name="recurring_journal_prefix" value="<?php echo esc(setting('recurring_journal_prefix','RJ')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Budget Prefix</label><input class="form-control" name="budget_prefix" value="<?php echo esc(setting('budget_prefix','BUD')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Cash Flow Forecast Prefix</label><input class="form-control" name="cash_flow_forecast_prefix" value="<?php echo esc(setting('cash_flow_forecast_prefix','CFF')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">AR Aging Prefix</label><input class="form-control" name="ar_aging_prefix" value="<?php echo esc(setting('ar_aging_prefix','ARAGE')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">AP Aging Prefix</label><input class="form-control" name="ap_aging_prefix" value="<?php echo esc(setting('ap_aging_prefix','APAGE')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Collection Task Prefix</label><input class="form-control" name="collection_task_prefix" value="<?php echo esc(setting('collection_task_prefix','COLL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Supplier Payment Run Prefix</label><input class="form-control" name="supplier_payment_run_prefix" value="<?php echo esc(setting('supplier_payment_run_prefix','SPR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Forecast Days</label><input class="form-control" type="number" name="finance_forecast_days" value="<?php echo esc(setting('finance_forecast_days','30')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Collection Task Days Overdue</label><input class="form-control" type="number" name="collection_task_days_overdue" value="<?php echo esc(setting('collection_task_days_overdue','7')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Budget Variance Warning %</label><input class="form-control" type="number" step="0.01" name="budget_variance_warning_percent" value="<?php echo esc(setting('budget_variance_warning_percent','10')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Employee Contract Prefix</label><input class="form-control" name="employee_contract_prefix" value="<?php echo esc(setting('employee_contract_prefix','ECON')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Employee Loan Prefix</label><input class="form-control" name="employee_loan_prefix" value="<?php echo esc(setting('employee_loan_prefix','ELOAN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Employee Document Prefix</label><input class="form-control" name="employee_document_prefix" value="<?php echo esc(setting('employee_document_prefix','EDOC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Payslip Prefix</label><input class="form-control" name="employee_payslip_prefix" value="<?php echo esc(setting('employee_payslip_prefix','PAYSLIP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Shift Prefix</label><input class="form-control" name="shift_template_prefix" value="<?php echo esc(setting('shift_template_prefix','SHIFT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Annual Leave Days</label><input class="form-control" type="number" step="0.01" name="default_annual_leave_days" value="<?php echo esc(setting('default_annual_leave_days','30')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Deduct Employee Loans</label><select class="form-select" name="payroll_auto_deduct_employee_loans"><option value="1" <?php echo setting('payroll_auto_deduct_employee_loans','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('payroll_auto_deduct_employee_loans','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Employee Self-Service</label><select class="form-select" name="employee_self_service_enabled"><option value="1" <?php echo setting('employee_self_service_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('employee_self_service_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">CRM Follow-up Prefix</label><input class="form-control" name="crm_followup_task_prefix" value="<?php echo esc(setting('crm_followup_task_prefix','CFT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Quote Follow-up Prefix</label><input class="form-control" name="crm_quote_followup_prefix" value="<?php echo esc(setting('crm_quote_followup_prefix','QFU')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Sales Forecast Prefix</label><input class="form-control" name="crm_sales_forecast_prefix" value="<?php echo esc(setting('crm_sales_forecast_prefix','FCST')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">CRM Touchpoint Prefix</label><input class="form-control" name="crm_touchpoint_prefix" value="<?php echo esc(setting('crm_touchpoint_prefix','TCH')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Campaign Action Prefix</label><input class="form-control" name="crm_campaign_action_prefix" value="<?php echo esc(setting('crm_campaign_action_prefix','CAMP-ACT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Quote Follow-up Days</label><input class="form-control" type="number" name="crm_quote_followup_days" value="<?php echo esc(setting('crm_quote_followup_days','3')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Task Due Days</label><input class="form-control" type="number" name="crm_task_due_days" value="<?php echo esc(setting('crm_task_due_days','1')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Forecast Probability Floor</label><input class="form-control" type="number" name="crm_forecast_probability_floor" value="<?php echo esc(setting('crm_forecast_probability_floor','10')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Service Request Prefix</label><input class="form-control" name="portal_service_request_prefix" value="<?php echo esc(setting('portal_service_request_prefix','CSR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Vendor Quote Prefix</label><input class="form-control" name="vendor_quote_response_prefix" value="<?php echo esc(setting('vendor_quote_response_prefix','VQR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">RFQ Prefix</label><input class="form-control" name="rfq_prefix" value="<?php echo esc(setting('rfq_prefix','RFQ')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">RFQ Invitation Prefix</label><input class="form-control" name="rfq_invitation_prefix" value="<?php echo esc(setting('rfq_invitation_prefix','RFI')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Supplier Quote Prefix</label><input class="form-control" name="supplier_quote_prefix" value="<?php echo esc(setting('supplier_quote_prefix','SQ')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Tender Prefix</label><input class="form-control" name="tender_prefix" value="<?php echo esc(setting('tender_prefix','TND')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Minimum RFQ Suppliers</label><input class="form-control" type="number" min="1" name="rfq_min_supplier_count" value="<?php echo esc(setting('rfq_min_supplier_count','3')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto-close RFQ on Award</label><select class="form-select" name="rfq_auto_close_on_award"><option value="1" <?php echo setting('rfq_auto_close_on_award','1')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo setting('rfq_auto_close_on_award','1')==='0'?'selected':''; ?>>No</option></select></div>
      <div class="col-lg-4"><label class="form-label">Workflow Automation</label><select class="form-select" name="workflow_automation_enabled"><option value="1" <?php echo setting('workflow_automation_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('workflow_automation_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Auto RFQ for Low Stock</label><select class="form-select" name="workflow_auto_low_stock_rfq"><option value="0" <?php echo setting('workflow_auto_low_stock_rfq','0')==='0'?'selected':''; ?>>Manual only</option><option value="1" <?php echo setting('workflow_auto_low_stock_rfq','0')==='1'?'selected':''; ?>>Allow automation</option></select></div>
      <div class="col-lg-4"><label class="form-label">Auto RFQ for Approved Requisitions</label><select class="form-select" name="workflow_auto_requisition_rfq"><option value="0" <?php echo setting('workflow_auto_requisition_rfq','0')==='0'?'selected':''; ?>>Manual only</option><option value="1" <?php echo setting('workflow_auto_requisition_rfq','0')==='1'?'selected':''; ?>>Allow automation</option></select></div>
      <div class="col-lg-4"><label class="form-label">CRM Hot Lead Score</label><input class="form-control" type="number" min="0" name="crm_lead_score_hot_threshold" value="<?php echo esc(setting('crm_lead_score_hot_threshold','70')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">CRM Warm Lead Score</label><input class="form-control" type="number" min="0" name="crm_lead_score_warm_threshold" value="<?php echo esc(setting('crm_lead_score_warm_threshold','40')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Follow-up Days</label><input class="form-control" type="number" min="1" name="crm_auto_followup_days" value="<?php echo esc(setting('crm_auto_followup_days','3')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Campaign Prefix</label><input class="form-control" name="crm_campaign_code_prefix" value="<?php echo esc(setting('crm_campaign_code_prefix','CMP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Opportunity Prefix</label><input class="form-control" name="sales_opportunity_prefix" value="<?php echo esc(setting('sales_opportunity_prefix','OPP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">CRM Automation</label><select class="form-select" name="crm_automation_enabled"><option value="1" <?php echo setting('crm_automation_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('crm_automation_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Payroll Period Prefix</label><input class="form-control" name="payroll_period_prefix" value="<?php echo esc(setting('payroll_period_prefix','PAYP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Payroll Run Prefix</label><input class="form-control" name="payroll_run_prefix" value="<?php echo esc(setting('payroll_run_prefix','PAY')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Expense Claim Prefix</label><input class="form-control" name="employee_expense_prefix" value="<?php echo esc(setting('employee_expense_prefix','EXPCL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Commission Prefix</label><input class="form-control" name="commission_prefix" value="<?php echo esc(setting('commission_prefix','COM')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Performance Review Prefix</label><input class="form-control" name="performance_review_prefix" value="<?php echo esc(setting('performance_review_prefix','REV')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Working Days</label><input class="form-control" type="number" min="1" name="payroll_default_working_days" value="<?php echo esc(setting('payroll_default_working_days','30')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Overtime Hour Rate</label><input class="form-control" type="number" step="0.01" min="0" name="payroll_overtime_hour_rate" value="<?php echo esc(setting('payroll_overtime_hour_rate','25')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Include Commissions in Payroll</label><select class="form-select" name="payroll_auto_include_commissions"><option value="1" <?php echo setting('payroll_auto_include_commissions','1')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo setting('payroll_auto_include_commissions','1')==='0'?'selected':''; ?>>No</option></select></div>
      <div class="col-lg-4"><label class="form-label">Include Approved Expenses in Payroll</label><select class="form-select" name="payroll_auto_include_approved_expenses"><option value="1" <?php echo setting('payroll_auto_include_approved_expenses','1')==='1'?'selected':''; ?>>Yes</option><option value="0" <?php echo setting('payroll_auto_include_approved_expenses','1')==='0'?'selected':''; ?>>No</option></select></div>
      <div class="col-lg-4"><label class="form-label">Financial Close Prefix</label><input class="form-control" name="financial_close_prefix" value="<?php echo esc(setting('financial_close_prefix','CLOSE')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Bank Reconciliation Prefix</label><input class="form-control" name="bank_reconciliation_prefix" value="<?php echo esc(setting('bank_reconciliation_prefix','BREC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Fixed Asset Prefix</label><input class="form-control" name="fixed_asset_prefix" value="<?php echo esc(setting('fixed_asset_prefix','FA')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Tax Return Prefix</label><input class="form-control" name="tax_return_prefix" value="<?php echo esc(setting('tax_return_prefix','TAX')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Audit Control Prefix</label><input class="form-control" name="audit_control_prefix" value="<?php echo esc(setting('audit_control_prefix','CTRL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Asset Life Months</label><input class="form-control" type="number" min="1" name="fixed_asset_default_life_months" value="<?php echo esc(setting('fixed_asset_default_life_months','36')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Depreciation Method</label><select class="form-select" name="fixed_asset_default_depreciation_method"><option value="straight_line" <?php echo setting('fixed_asset_default_depreciation_method','straight_line')==='straight_line'?'selected':''; ?>>Straight Line</option></select></div>
      <div class="col-lg-4"><label class="form-label">Auto Close Tasks</label><select class="form-select" name="financial_close_auto_tasks"><option value="1" <?php echo setting('financial_close_auto_tasks','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('financial_close_auto_tasks','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Tax Rate %</label><input class="form-control" type="number" step="0.01" min="0" name="tax_default_rate" value="<?php echo esc(setting('tax_default_rate','5')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Report Export Max Rows</label><input class="form-control" type="number" min="100" name="report_export_max_rows" value="<?php echo esc(setting('report_export_max_rows','5000')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Report Scheduling</label><select class="form-select" name="report_schedule_enabled"><option value="1" <?php echo setting('report_schedule_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('report_schedule_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Report Export Prefix</label><input class="form-control" name="report_export_prefix" value="<?php echo esc(setting('report_export_prefix','EXP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Report Schedule Prefix</label><input class="form-control" name="report_schedule_prefix" value="<?php echo esc(setting('report_schedule_prefix','RSC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto KPI Snapshots</label><select class="form-select" name="kpi_snapshot_auto_enabled"><option value="1" <?php echo setting('kpi_snapshot_auto_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('kpi_snapshot_auto_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Dashboard Code</label><input class="form-control" name="management_dashboard_default" value="<?php echo esc(setting('management_dashboard_default','MGMT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Developer Docs Public</label><select class="form-select" name="developer_docs_public_enabled"><option value="1" <?php echo setting('developer_docs_public_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('developer_docs_public_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Webhook Retry Limit</label><input class="form-control" type="number" min="0" name="webhook_retry_limit" value="<?php echo esc(setting('webhook_retry_limit','3')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Webhook Signing</label><select class="form-select" name="webhook_signing_enabled"><option value="1" <?php echo setting('webhook_signing_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('webhook_signing_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">WhatsApp Provider</label><input class="form-control" name="whatsapp_provider" value="<?php echo esc(setting('whatsapp_provider','manual')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Default Country Code</label><input class="form-control" name="whatsapp_default_country_code" value="<?php echo esc(setting('whatsapp_default_country_code','+971')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Integration Sync</label><select class="form-select" name="integration_sync_enabled"><option value="1" <?php echo setting('integration_sync_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('integration_sync_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">API Marketplace</label><select class="form-select" name="api_marketplace_enabled"><option value="1" <?php echo setting('api_marketplace_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('api_marketplace_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Field Dispatch Prefix</label><input class="form-control" name="field_dispatch_prefix" value="<?php echo esc(setting('field_dispatch_prefix','DISP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Field Route Prefix</label><input class="form-control" name="field_route_prefix" value="<?php echo esc(setting('field_route_prefix','ROUTE')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Offline Draft Prefix</label><input class="form-control" name="offline_draft_prefix" value="<?php echo esc(setting('offline_draft_prefix','OFF')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Asset QR Prefix</label><input class="form-control" name="asset_qr_prefix" value="<?php echo esc(setting('asset_qr_prefix','QR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Customer Signoff Prefix</label><input class="form-control" name="customer_signoff_prefix" value="<?php echo esc(setting('customer_signoff_prefix','SIGN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Mobile Offline Mode</label><select class="form-select" name="mobile_offline_enabled"><option value="1" <?php echo setting('mobile_offline_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('mobile_offline_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">QR Public Lookup</label><select class="form-select" name="qr_public_lookup_enabled"><option value="1" <?php echo setting('qr_public_lookup_enabled','0')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('qr_public_lookup_enabled','0')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Default Dispatch Status</label><input class="form-control" name="field_default_dispatch_status" value="<?php echo esc(setting('field_default_dispatch_status','scheduled')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Service Request Prefix</label><input class="form-control" name="portal_service_request_prefix" value="<?php echo esc(setting('portal_service_request_prefix','CSR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Invoice Dispute Prefix</label><input class="form-control" name="customer_invoice_dispute_prefix" value="<?php echo esc(setting('customer_invoice_dispute_prefix','DISP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Payment Promise Prefix</label><input class="form-control" name="customer_payment_promise_prefix" value="<?php echo esc(setting('customer_payment_promise_prefix','PROM')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Customer Documents</label><select class="form-select" name="customer_portal_documents_enabled"><option value="1" <?php echo setting('customer_portal_documents_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('customer_portal_documents_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Customer Feedback</label><select class="form-select" name="customer_portal_feedback_enabled"><option value="1" <?php echo setting('customer_portal_feedback_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('customer_portal_feedback_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Announcements</label><select class="form-select" name="customer_portal_announcements_enabled"><option value="1" <?php echo setting('customer_portal_announcements_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('customer_portal_announcements_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Payment Promises</label><select class="form-select" name="customer_portal_payment_promises_enabled"><option value="1" <?php echo setting('customer_portal_payment_promises_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('customer_portal_payment_promises_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Supplier Onboarding Prefix</label><input class="form-control" name="supplier_onboarding_prefix" value="<?php echo esc(setting('supplier_onboarding_prefix','SON')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Supplier Scorecard Prefix</label><input class="form-control" name="supplier_scorecard_prefix" value="<?php echo esc(setting('supplier_scorecard_prefix','SSC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Supplier Price List Prefix</label><input class="form-control" name="supplier_price_list_prefix" value="<?php echo esc(setting('supplier_price_list_prefix','SPL')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Supplier Contract Prefix</label><input class="form-control" name="supplier_contract_prefix" value="<?php echo esc(setting('supplier_contract_prefix','SCON')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Procurement Award Prefix</label><input class="form-control" name="procurement_award_prefix" value="<?php echo esc(setting('procurement_award_prefix','AWD')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">RFQ Quote Response Prefix</label><input class="form-control" name="rfq_quote_response_prefix" value="<?php echo esc(setting('rfq_quote_response_prefix','RQR')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto PO Status</label><input class="form-control" name="procurement_auto_po_status" value="<?php echo esc(setting('procurement_auto_po_status','approved')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Min Approved Supplier Score</label><input class="form-control" type="number" name="supplier_min_approved_score" value="<?php echo esc(setting('supplier_min_approved_score','70')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Bin Prefix</label><input class="form-control" name="warehouse_bin_prefix" value="<?php echo esc(setting('warehouse_bin_prefix','BIN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Lot Prefix</label><input class="form-control" name="inventory_lot_prefix" value="<?php echo esc(setting('inventory_lot_prefix','LOT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Stock Count Prefix</label><input class="form-control" name="stock_count_prefix" value="<?php echo esc(setting('stock_count_prefix','COUNT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Adjustment Prefix</label><input class="form-control" name="inventory_adjustment_prefix" value="<?php echo esc(setting('inventory_adjustment_prefix','ADJ')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Replenishment Prefix</label><input class="form-control" name="replenishment_prefix" value="<?php echo esc(setting('replenishment_prefix','REP')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Picking Prefix</label><input class="form-control" name="picking_prefix" value="<?php echo esc(setting('picking_prefix','PICK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Packing Prefix</label><input class="form-control" name="packing_prefix" value="<?php echo esc(setting('packing_prefix','PACK')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Warehouse Dispatch Prefix</label><input class="form-control" name="warehouse_dispatch_prefix" value="<?php echo esc(setting('warehouse_dispatch_prefix','DISP-WH')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Reserve on Pick</label><select class="form-select" name="warehouse_auto_reserve_on_pick"><option value="1" <?php echo setting('warehouse_auto_reserve_on_pick','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('warehouse_auto_reserve_on_pick','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Auto Stock Out on Dispatch</label><select class="form-select" name="warehouse_auto_stock_out_on_dispatch"><option value="1" <?php echo setting('warehouse_auto_stock_out_on_dispatch','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('warehouse_auto_stock_out_on_dispatch','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">BOM Prefix</label><input class="form-control" name="manufacturing_bom_prefix" value="<?php echo esc(setting('manufacturing_bom_prefix','BOM')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Work Order Prefix</label><input class="form-control" name="manufacturing_work_order_prefix" value="<?php echo esc(setting('manufacturing_work_order_prefix','MO')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Production Plan Prefix</label><input class="form-control" name="production_plan_prefix" value="<?php echo esc(setting('production_plan_prefix','PLAN')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Material Issue Prefix</label><input class="form-control" name="production_issue_prefix" value="<?php echo esc(setting('production_issue_prefix','ISSUE')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Production Receipt Prefix</label><input class="form-control" name="production_receipt_prefix" value="<?php echo esc(setting('production_receipt_prefix','RECEIPT')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Cost Rollup Prefix</label><input class="form-control" name="production_cost_rollup_prefix" value="<?php echo esc(setting('production_cost_rollup_prefix','COST')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Quality Check Prefix</label><input class="form-control" name="quality_check_prefix" value="<?php echo esc(setting('quality_check_prefix','QC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Work Center Prefix</label><input class="form-control" name="work_center_prefix" value="<?php echo esc(setting('work_center_prefix','WC')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Manufacturing Overhead %</label><input class="form-control" type="number" step="0.01" name="manufacturing_overhead_percent" value="<?php echo esc(setting('manufacturing_overhead_percent','10')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Stock Update</label><select class="form-select" name="manufacturing_auto_stock_update"><option value="1" <?php echo setting('manufacturing_auto_stock_update','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('manufacturing_auto_stock_update','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">AI Assistant</label><select class="form-select" name="ai_assistant_enabled"><option value="1" <?php echo setting('ai_assistant_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('ai_assistant_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">AI Assistant Mode</label><input class="form-control" name="ai_assistant_mode" value="<?php echo esc(setting('ai_assistant_mode','rules_based')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Smart Search Auto Index</label><select class="form-select" name="smart_search_auto_index"><option value="1" <?php echo setting('smart_search_auto_index','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('smart_search_auto_index','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Predictive Alert Threshold</label><input class="form-control" type="number" min="0" max="100" name="predictive_alert_score_threshold" value="<?php echo esc(setting('predictive_alert_score_threshold','60')); ?>"></div>
      <div class="col-lg-4"><label class="form-label">Auto Recommendations</label><select class="form-select" name="recommendation_auto_generate"><option value="1" <?php echo setting('recommendation_auto_generate','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('recommendation_auto_generate','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Anomaly Detection</label><select class="form-select" name="anomaly_detection_enabled"><option value="1" <?php echo setting('anomaly_detection_enabled','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('anomaly_detection_enabled','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
      <div class="col-lg-4"><label class="form-label">Decision Support Scoring</label><select class="form-select" name="decision_support_scoring"><option value="1" <?php echo setting('decision_support_scoring','1')==='1'?'selected':''; ?>>Enabled</option><option value="0" <?php echo setting('decision_support_scoring','1')==='0'?'selected':''; ?>>Disabled</option></select></div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Global SEO Defaults</h2>
        <p class="text-secondary mb-0">Fallback values used when a page does not have its own SEO values.</p>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-lg-6"><label class="form-label">Default Title Suffix</label><input class="form-control" name="seo_default_title_suffix" value="<?php echo esc(setting('seo_default_title_suffix',SHOP_NAME)); ?>"></div>
      <div class="col-lg-6"><label class="form-label">Default Robots Meta</label><input class="form-control" name="seo_default_robots" value="<?php echo esc(setting('seo_default_robots','index,follow')); ?>" placeholder="index,follow"></div>
      <div class="col-12"><label class="form-label">Default Meta Description</label><textarea class="form-control" name="seo_default_description" rows="3"><?php echo esc(setting('seo_default_description','')); ?></textarea></div>
      <div class="col-lg-6"><label class="form-label">Default Meta Keywords</label><textarea class="form-control" name="seo_default_keywords" rows="2"><?php echo esc(setting('seo_default_keywords','')); ?></textarea></div>
      <div class="col-lg-6"><label class="form-label">Default Open Graph Image URL / Path</label><textarea class="form-control" name="seo_default_og_image" rows="2"><?php echo esc(setting('seo_default_og_image','')); ?></textarea></div>
      <div class="col-12"><div class="alert alert-light border mb-0">Page-level values below override these defaults. Product and blog detail pages also use their own title/description as dynamic fallbacks.</div></div>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">Page SEO Manager</h2>
        <p class="text-secondary mb-0">Configure meta title, description, keywords, robots, and canonical URL for main storefront pages.</p>
      </div>
    </div>
    <div class="accordion seo-page-accordion" id="seoPagesAccordion">
      <?php $seoCounter=0; foreach($seoPages as $seoKey=>$seoLabel): $seoCounter++; $collapseId='seo_page_'.$seoCounter; ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button <?php echo $seoCounter===1?'':'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc($collapseId); ?>" aria-expanded="<?php echo $seoCounter===1?'true':'false'; ?>" aria-controls="<?php echo esc($collapseId); ?>">
              <?php echo esc($seoLabel); ?> SEO
            </button>
          </h2>
          <div id="<?php echo esc($collapseId); ?>" class="accordion-collapse collapse <?php echo $seoCounter===1?'show':''; ?>" data-bs-parent="#seoPagesAccordion">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-lg-8"><label class="form-label">Meta Title</label><input class="form-control" name="seo_<?php echo esc($seoKey); ?>_title" value="<?php echo esc(setting('seo_'.$seoKey.'_title','')); ?>"></div>
                <div class="col-lg-4"><label class="form-label">Robots Meta</label><input class="form-control" name="seo_<?php echo esc($seoKey); ?>_robots" value="<?php echo esc(setting('seo_'.$seoKey.'_robots',setting('seo_default_robots','index,follow'))); ?>" placeholder="index,follow"></div>
                <div class="col-12"><label class="form-label">Meta Description</label><textarea class="form-control" name="seo_<?php echo esc($seoKey); ?>_description" rows="3"><?php echo esc(setting('seo_'.$seoKey.'_description','')); ?></textarea></div>
                <div class="col-lg-6"><label class="form-label">Meta Keywords</label><textarea class="form-control" name="seo_<?php echo esc($seoKey); ?>_keywords" rows="2"><?php echo esc(setting('seo_'.$seoKey.'_keywords','')); ?></textarea></div>
                <div class="col-lg-6"><label class="form-label">Canonical URL</label><textarea class="form-control" name="seo_<?php echo esc($seoKey); ?>_canonical" rows="2" placeholder="Leave empty to use current URL automatically"><?php echo esc(setting('seo_'.$seoKey.'_canonical','')); ?></textarea></div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="card-admin p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h2 class="h4 mb-1">robots.txt Editor</h2>
        <p class="text-secondary mb-0">Saving this field updates the project root <code>robots.txt</code> file.</p>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-12"><label class="form-label">robots.txt Content</label><textarea class="form-control font-monospace" name="seo_robots_txt" rows="9"><?php echo esc(setting('seo_robots_txt',$defaultRobots)); ?></textarea></div>
      <div class="col-12"><div class="alert alert-light border mb-0">Use robots.txt for crawler directives. Use page Robots Meta above for page-level index/noindex control.</div></div>
    </div>
  </section>

  <div class="pb-3"><button class="btn btn-brand btn-lg">Save Settings</button></div>
</form>
<?php include __DIR__.'/footer.php'; ?>