<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$pdo=getDB();$requests=safeAiRows($pdo,'SELECT request_number,subject,status,priority,created_at FROM '.table('customer_service_requests').' ORDER BY created_at DESC LIMIT 12');
?><!doctype html><html lang="<?php echo esc(siteLanguage()); ?>" dir="<?php echo esc(siteDirection()); ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="<?php echo esc(setting('pwa_theme_color','#0f172a')); ?>"><link rel="manifest" href="/manifest.webmanifest"><title>Customer Mobile</title><?php echo <<<'CSS'
<style>
:root{--m-bg:#f5f7fb;--m-card:#fff;--m-text:#0f172a;--m-muted:#64748b;--m-brand:#0f172a;--m-line:#e2e8f0}
body.mobile-app{margin:0;background:var(--m-bg);font-family:Inter,Arial,sans-serif;color:var(--m-text)}
.mobile-shell{max-width:520px;margin:0 auto;min-height:100vh;padding:14px 14px 86px}
.mobile-top{position:sticky;top:0;background:rgba(245,247,251,.9);backdrop-filter:blur(12px);z-index:5;padding:12px 0;display:flex;justify-content:space-between;align-items:center}
.mobile-title{font-size:22px;font-weight:800;letter-spacing:-.02em}.mobile-sub{color:var(--m-muted);font-size:13px}
.mobile-card{background:var(--m-card);border:1px solid var(--m-line);border-radius:24px;padding:18px;box-shadow:0 12px 30px rgba(15,23,42,.06)}
.mobile-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.mobile-action{display:block;text-decoration:none;color:var(--m-text);min-height:112px}
.mobile-action strong{display:block;margin-top:12px}.mobile-badge{display:inline-block;background:#fee2e2;color:#991b1b;border-radius:999px;padding:4px 10px;font-size:12px;margin-top:8px}
.mobile-list{display:grid;gap:10px}.mobile-row{background:#fff;border:1px solid var(--m-line);border-radius:18px;padding:12px;text-decoration:none;color:var(--m-text);display:flex;justify-content:space-between;gap:8px}
.mobile-nav{position:fixed;left:50%;bottom:12px;transform:translateX(-50%);width:min(520px,calc(100% - 24px));background:#0f172a;color:#fff;border-radius:26px;padding:10px;display:grid;grid-template-columns:repeat(4,1fr);gap:6px;box-shadow:0 20px 45px rgba(15,23,42,.22)}
.mobile-nav a{color:#cbd5e1;text-decoration:none;text-align:center;font-size:11px;padding:8px 4px;border-radius:18px}.mobile-nav a.active,.mobile-nav a:hover{background:rgba(255,255,255,.12);color:#fff}
.status-dot{width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block}
.install-banner{display:none;margin-bottom:12px}.install-banner.show{display:block}
</style>
CSS; ?></head><body class="mobile-app"><main class="mobile-shell"><div class="mobile-top"><div><div class="mobile-title">Customer</div><div class="mobile-sub">Requests, assets and updates</div></div><a href="/mobile/index.php">Home</a></div><div class="mobile-card"><strong>Customer mobile UX foundation</strong><p class="mobile-sub">Optimized customer portal shell for mobile service requests, documents and order updates.</p></div><h3>Latest requests</h3><div class="mobile-list"><?php foreach($requests as $r): ?><a class="mobile-row" href="/customer/dashboard.php"><span><strong><?php echo esc($r['request_number']); ?></strong><br><small><?php echo esc($r['subject']); ?></small></span><span><?php echo esc($r['status']); ?></span></a><?php endforeach; ?><?php if(!$requests): ?><div class="mobile-card mobile-sub">No customer requests yet.</div><?php endif; ?></div></main><nav class="mobile-nav"><a href="/mobile/index.php">Home</a><a class="active" href="/mobile/customer.php">Customer</a><a href="/mobile/employee.php">Employee</a><a href="/mobile/technician.php">Tech</a></nav><?php echo <<<'JS'
<script>
(function(){
  if('serviceWorker' in navigator){navigator.serviceWorker.register('/service-worker.js').catch(function(){});}
  let deferredPrompt; window.addEventListener('beforeinstallprompt',function(e){e.preventDefault();deferredPrompt=e;var b=document.querySelector('[data-install-banner]');if(b)b.classList.add('show');});
  window.erpInstallPwa=function(){if(deferredPrompt){deferredPrompt.prompt();deferredPrompt.userChoice.finally(function(){deferredPrompt=null;});}}
})();
</script>
JS; ?></body></html>