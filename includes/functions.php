<?php
require_once __DIR__ . '/db_connect.php';

function esc($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function siteLanguageMode(): string
{
    $fallback = defined('SITE_LANGUAGE') && in_array(SITE_LANGUAGE, ['en', 'ar', 'both'], true) ? SITE_LANGUAGE : 'en';
    if (function_exists('setting')) {
        $mode = (string)setting('site_language_mode', $fallback);
        return in_array($mode, ['en', 'ar', 'both'], true) ? $mode : $fallback;
    }
    return $fallback;
}

function siteDefaultLanguage(): string
{
    $default = function_exists('setting') ? (string)setting('site_default_language','en') : 'en';
    return in_array($default, ['en','ar'], true) ? $default : 'en';
}

function siteTranslationEnabled(): bool
{
    return function_exists('setting') ? setting('site_translation_enabled','1') === '1' : true;
}

function currentLanguageFromPath(): ?string
{
    $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
    $first = strtolower(trim(explode('/', trim($path, '/'))[0] ?? ''));
    return in_array($first, ['en','ar'], true) ? $first : null;
}

function siteLanguage(): string
{
    $mode = siteLanguageMode();
    if (!siteTranslationEnabled()) {
        return siteDefaultLanguage();
    }
    if ($mode !== 'both') {
        return $mode;
    }
    $requested = strtolower(trim((string)(currentLanguageFromPath() ?? ($_GET['lang'] ?? ($_SESSION['site_language'] ?? siteDefaultLanguage())))));
    if (!in_array($requested, ['en', 'ar'], true)) {
        $requested = siteDefaultLanguage();
    }
    $_SESSION['site_language'] = $requested;
    return $requested;
}

function siteDirection(): string
{
    return siteLanguage() === 'ar' ? 'rtl' : 'ltr';
}

function isArabicSite(): bool
{
    return siteLanguage() === 'ar';
}

function bilingualEnabled(): bool
{
    return siteTranslationEnabled() && siteLanguageMode() === 'both';
}

function manualTranslations(): array
{
    static $translations = null;
    if ($translations !== null) { return $translations; }
    $json = function_exists('setting') ? (string)setting('translation_manual_json','{}') : '{}';
    $decoded = json_decode($json, true);
    $translations = is_array($decoded) ? $decoded : [];
    return $translations;
}

function t(string $english, ?string $arabic = null): string
{
    if (!isArabicSite()) { return $english; }
    $manual = manualTranslations();
    if (isset($manual[$english]) && trim((string)$manual[$english]) !== '') {
        return (string)$manual[$english];
    }
    return $arabic !== null && $arabic !== '' ? $arabic : $english;
}

function languageSwitchUrl(string $language): string
{
    $language = in_array($language, ['en', 'ar'], true) ? $language : 'en';
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $parts = parse_url($uri);
    $path = $parts['path'] ?? '/';
    $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
    $base = trim($path, '/');
    $segments = $base === '' ? [] : explode('/', $base);
    if (isset($segments[0]) && in_array(strtolower($segments[0]), ['en','ar'], true)) {
        $segments[0] = $language;
    } else {
        array_unshift($segments, $language);
    }
    return '/' . implode('/', $segments) . $query;
}

function translationClientScript(): string
{
    if (!isArabicSite() || !siteTranslationEnabled()) { return ''; }
    $json = json_encode(manualTranslations(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return '<script>window.MANUAL_TRANSLATIONS=' . ($json ?: '{}') . ';(function(){const d=window.MANUAL_TRANSLATIONS||{};function w(n){if(!n||n.nodeType!==3)return;const k=n.nodeValue.trim();if(k&&d[k])n.nodeValue=n.nodeValue.replace(k,d[k]);}function scan(r){const tw=document.createTreeWalker(r,NodeFilter.SHOW_TEXT,{acceptNode:function(n){return n.parentNode&&/^(SCRIPT|STYLE|TEXTAREA)$/.test(n.parentNode.nodeName)?NodeFilter.FILTER_REJECT:NodeFilter.FILTER_ACCEPT;}});let n;while(n=tw.nextNode())w(n);document.querySelectorAll("input[placeholder],textarea[placeholder],img[alt]").forEach(function(el){["placeholder","alt","title"].forEach(function(a){const v=el.getAttribute(a);if(v&&d[v])el.setAttribute(a,d[v]);});});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",function(){scan(document.body);});}else{scan(document.body);}})();</script>';
}

function renderLanguageSwitcher(): string
{
    if (!bilingualEnabled()) {
        return '';
    }
    $current = siteLanguage();
    $enActive = $current === 'en' ? ' active' : '';
    $arActive = $current === 'ar' ? ' active' : '';
    return '<div class="language-switcher" dir="ltr" aria-label="Language switcher">'
        . '<a class="language-switch-link' . $enActive . '" href="' . esc(languageSwitchUrl('en')) . '"><span>EN</span><small>English</small></a>'
        . '<a class="language-switch-link' . $arActive . '" href="' . esc(languageSwitchUrl('ar')) . '"><span>AR</span><small>العربية</small></a>'
        . '</div>'
        . '<style>.language-switcher{display:inline-flex;align-items:center;gap:4px;background:#f4f7fb;border:1px solid #d8e0ea;border-radius:999px;padding:4px;box-shadow:0 8px 24px rgba(15,23,42,.08)}.language-switch-link{display:flex;align-items:center;gap:6px;border-radius:999px;padding:7px 10px;text-decoration:none;color:#334155;font-weight:700;line-height:1;white-space:nowrap}.language-switch-link span{font-size:12px;letter-spacing:.04em}.language-switch-link small{font-size:11px;font-weight:600;opacity:.78}.language-switch-link.active{background:#111827;color:#fff;box-shadow:0 6px 16px rgba(17,24,39,.18)}@media(max-width:991px){.language-switcher{margin-inline:auto}.language-switch-link{padding:8px 9px}.language-switch-link small{display:none}}@media(max-width:575px){.header-actions .language-switcher{order:-1;width:100%;justify-content:center}.language-switcher{border-radius:16px}.language-switch-link{flex:1;justify-content:center}}</style>';
}




function availableCurrencies(): array
{
    return [
        'AED' => ['label' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'AED ', 'flag' => '🇦🇪', 'default_rate' => '1'],
        'USD' => ['label' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸', 'default_rate' => '0.2723'],
        'EUR' => ['label' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺', 'default_rate' => '0.2520'],
        'EGP' => ['label' => 'L.E', 'name' => 'Egyptian Pound', 'symbol' => 'L.E ', 'flag' => '🇪🇬', 'default_rate' => '13.20'],
    ];
}

function normalizeCurrencyCode(?string $currency): string
{
    $currency = strtoupper(trim((string)$currency));
    $currency = str_replace([' ', '.'], '', $currency);
    if ($currency === 'LE' || $currency === 'EGYPT' || $currency === 'EGYPTIANPOUND') {
        $currency = 'EGP';
    }
    return array_key_exists($currency, availableCurrencies()) ? $currency : 'AED';
}

function activeCurrencyCode(): string
{
    $fromRequest = $_GET['currency'] ?? ($_REQUEST['currency'] ?? null);
    if ($fromRequest !== null && $fromRequest !== '') {
        $code = normalizeCurrencyCode((string)$fromRequest);
        $_SESSION['site_currency'] = $code;
        return $code;
    }
    return normalizeCurrencyCode((string)($_SESSION['site_currency'] ?? setting('default_display_currency', defined('CURRENCY') ? CURRENCY : 'AED')));
}

function currencyRate(string $currency): float
{
    $currency = normalizeCurrencyCode($currency);
    $meta = availableCurrencies()[$currency];
    $rate = (float)setting('currency_rate_' . strtolower($currency), $meta['default_rate']);
    return $rate > 0 ? $rate : (float)$meta['default_rate'];
}

function convertCurrencyAmount($amount, ?string $targetCurrency = null): float
{
    $targetCurrency = normalizeCurrencyCode($targetCurrency ?? activeCurrencyCode());
    $baseCurrency = normalizeCurrencyCode(defined('CURRENCY') ? CURRENCY : setting('base_currency', setting('default_display_currency', 'AED')));
    $clean = is_string($amount) ? str_replace(',', '', $amount) : $amount;
    $numeric = (float)$clean;
    $baseRate = currencyRate($baseCurrency);
    $targetRate = currencyRate($targetCurrency);
    if ($baseRate <= 0) {
        $baseRate = 1;
    }
    return $numeric * ($targetRate / $baseRate);
}

function currencySwitchUrl(string $currency): string
{
    $currency = normalizeCurrencyCode($currency);
    $params = $_GET;
    $params['currency'] = $currency;

    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = strtok($requestUri, '?') ?: '/';

    // Prevent /ar/index.php or /en/index.php 404 when switching currency.
    // Keep the language in query/session and use the real existing PHP path.
    $segments = explode('/', trim($path, '/'));
    if (isset($segments[0]) && in_array(strtolower($segments[0]), ['ar', 'en'], true)) {
        $params['lang'] = strtolower($segments[0]);
        array_shift($segments);
        $path = '/' . implode('/', $segments);
        if ($path === '/' || $path === '') {
            $path = '/index.php';
        }
    }

    return $path . '?' . http_build_query($params);
}

function renderCurrencySwitcher(string $context = 'header'): string
{
    $current = activeCurrencyCode();
    $items = availableCurrencies();

    if ($context === 'drawer') {
        $gridStyle = 'display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;width:100%!important;max-width:100%!important;min-width:0!important;padding:8px!important;margin:0!important;border-radius:22px!important;background:rgba(255,255,255,.08)!important;border:1px solid rgba(255,255,255,.12)!important;box-shadow:none!important;direction:ltr!important;white-space:normal!important;overflow:hidden!important;box-sizing:border-box!important;';
        $btnBase = 'display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;width:100%!important;min-width:0!important;height:62px!important;padding:8px 6px!important;border-radius:16px!important;background:rgba(255,255,255,.94)!important;color:#0f172a!important;text-decoration:none!important;text-align:center!important;font-weight:900!important;line-height:1!important;box-sizing:border-box!important;overflow:hidden!important;';
        $btnActive = 'background:linear-gradient(135deg,#ec008c,#7c3aed)!important;color:#fff!important;box-shadow:0 10px 24px rgba(236,0,140,.22)!important;';
        $codeStyle = 'display:block!important;width:100%!important;max-width:100%!important;font-size:13px!important;font-weight:900!important;line-height:1!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;';
        $countryStyle = 'display:block!important;margin-top:5px!important;font-size:11px!important;font-weight:900!important;line-height:1!important;opacity:.95!important;';
        $html = '<div class="drawer-currency-buttons-v3" dir="ltr" aria-label="Currency switcher" style="' . $gridStyle . '">';
        foreach ($items as $code => $meta) {
            $active = $current === $code;
            $country = $code === 'EGP' ? 'EG' : ($code === 'AED' ? 'AE' : ($code === 'USD' ? 'US' : 'EU'));
            $style = $btnBase . ($active ? $btnActive : '');
            $html .= '<a data-currency-switch-link class="drawer-currency-button-v3' . ($active ? ' is-active' : '') . '" href="' . esc(currencySwitchUrl($code)) . '" style="' . $style . '">'
                . '<b style="' . $codeStyle . '">' . esc($meta['label']) . '</b>'
                . '<small style="' . $countryStyle . '">' . esc($country) . '</small>'
                . '</a>';
        }
        return $html . '</div>';
    }

    $html = '<form class="header-currency-select-form" method="get" action="">';
    foreach ($_GET as $key => $value) {
        if ($key === 'currency' || is_array($value)) {
            continue;
        }
        $html .= '<input type="hidden" name="' . esc((string)$key) . '" value="' . esc((string)$value) . '">';
    }
    $html .= '<select class="header-currency-select" name="currency" aria-label="Currency" onchange="this.form.submit()">';
    foreach ($items as $code => $meta) {
        $selected = $current === $code ? ' selected' : '';
        $html .= '<option value="' . esc($code) . '"' . $selected . '>' . esc($meta['label']) . '</option>';
    }
    $html .= '</select></form>';
    return $html;
}

function renderTrustedRichText(?string $html): string
{
    return (string)$html;
}

function localUploadRoot(string $subdir = ''): string
{
    $root = dirname(__DIR__) . '/uploads';
    $subdir = trim($subdir, '/');
    return $subdir !== '' ? $root . '/' . $subdir : $root;
}

function uploadAssetUrl(?string $value, string $subdir = 'products'): string
{
    $asset = trim((string)$value);
    if ($asset === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $asset)) {
        return $asset;
    }
    return UPLOADS_URL . '/' . trim($subdir, '/') . '/' . ltrim($asset, '/');
}

function ensureUploadDirectory(string $subdir): string
{
    $dir = localUploadRoot($subdir);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create upload directory.');
    }
    return $dir;
}

function validImageUploadMime(string $tmpFile): ?string
{
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmpFile);
    } elseif (function_exists('mime_content_type')) {
        $mime = (string)mime_content_type($tmpFile);
    }
    return $allowed[$mime] ?? null;
}

function uploadAdminImage(string $field, string $subdir = 'products', int $maxBytes = 8388608): ?string
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }
    $file = $_FILES[$field];
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        throw new RuntimeException('Image must be smaller than 8 MB.');
    }
    $tmp = (string)($file['tmp_name'] ?? '');
    $extension = validImageUploadMime($tmp);
    if (!$extension) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }
    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $destination = ensureUploadDirectory($subdir) . '/' . $filename;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('Unable to save uploaded image.');
    }
    return $filename;
}

function uploadAdminImages(string $field, string $subdir = 'products', int $maxBytes = 8388608): array
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return [];
    }
    $files = $_FILES[$field];
    $names = $files['name'] ?? [];
    if (!is_array($names)) {
        return [];
    }
    $saved = [];
    foreach ($names as $index => $unused) {
        $error = (int)($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('One gallery image failed to upload.');
        }
        $size = (int)($files['size'][$index] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new RuntimeException('Each gallery image must be smaller than 8 MB.');
        }
        $tmp = (string)($files['tmp_name'][$index] ?? '');
        $extension = validImageUploadMime($tmp);
        if (!$extension) {
            throw new RuntimeException('Gallery images must be JPG, PNG, WEBP, or GIF.');
        }
        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '_' . $index . '.' . $extension;
        $destination = ensureUploadDirectory($subdir) . '/' . $filename;
        if (!move_uploaded_file($tmp, $destination)) {
            throw new RuntimeException('Unable to save one of the gallery images.');
        }
        $saved[] = $filename;
    }
    return $saved;
}

function mergeGalleryValues(string $typed, array $uploaded): string
{
    $lines = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $typed) ?: []));
    foreach ($uploaded as $item) {
        $item = trim((string)$item);
        if ($item !== '') {
            $lines[] = $item;
        }
    }
    return implode("\n", array_values(array_unique($lines)));
}

function storeRelativeUrl(string $url, string $fallback): string
{
    $url = trim($url);
    return $url !== '' ? $url : $fallback;
}


function appEncryptionKeyBytes(?string $overrideKey = null): string
{
    $key = trim((string)($overrideKey ?? (defined('APP_ENCRYPTION_KEY') ? APP_ENCRYPTION_KEY : '')));
    if ($key === '') {
        throw new RuntimeException('Application encryption key is missing.');
    }
    $decoded = base64_decode($key, true);
    if ($decoded !== false && strlen($decoded) >= 32) {
        return substr($decoded, 0, 32);
    }
    return hash('sha256', $key, true);
}

function encryptPayloadString(string $plainText, ?string $key = null): string
{
    if (!extension_loaded('openssl')) {
        throw new RuntimeException('OpenSSL extension is required for encryption.');
    }
    $iv = random_bytes(12);
    $tag = '';
    $cipherText = openssl_encrypt($plainText, 'aes-256-gcm', appEncryptionKeyBytes($key), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipherText === false) {
        throw new RuntimeException('Encryption failed.');
    }
    return json_encode([
        'format' => 'ERPENC-1',
        'cipher' => 'aes-256-gcm',
        'iv' => base64_encode($iv),
        'tag' => base64_encode($tag),
        'data' => base64_encode($cipherText),
        'created_at' => date('c'),
    ], JSON_UNESCAPED_SLASHES);
}

function decryptPayloadString(string $encryptedJson, ?string $key = null): string
{
    if (!extension_loaded('openssl')) {
        throw new RuntimeException('OpenSSL extension is required for decryption.');
    }
    $payload = json_decode($encryptedJson, true);
    if (!is_array($payload) || ($payload['format'] ?? '') !== 'ERPENC-1') {
        throw new RuntimeException('Invalid encrypted backup format.');
    }
    $iv = base64_decode((string)($payload['iv'] ?? ''), true);
    $tag = base64_decode((string)($payload['tag'] ?? ''), true);
    $data = base64_decode((string)($payload['data'] ?? ''), true);
    if ($iv === false || $tag === false || $data === false) {
        throw new RuntimeException('Encrypted payload is corrupted.');
    }
    $plain = openssl_decrypt($data, 'aes-256-gcm', appEncryptionKeyBytes($key), OPENSSL_RAW_DATA, $iv, $tag);
    if ($plain === false) {
        throw new RuntimeException('Decryption failed. Check the encryption key.');
    }
    return $plain;
}

function writeDownloadResponse(string $fileName, string $content, string $contentType = 'application/octet-stream'): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . strlen($content));
    header('X-Content-Type-Options: nosniff');
    echo $content;
    exit;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text ?: 'item', '-');
}

 
function money($amount): string
{
    $currency = activeCurrencyCode();
    $currencies = availableCurrencies();
    $symbol = $currencies[$currency]['symbol'] ?? 'AED ';
    $numeric = round(convertCurrencyAmount($amount, $currency), 2);

    if (floor($numeric) == $numeric) {
        return $symbol . number_format($numeric, 0, '.', ',');
    }

    return $symbol . rtrim(rtrim(number_format($numeric, 2, '.', ','), '0'), '.');
}
function setting($key, $default = '')
{
    // setting() null-safe key guard
    if ($key === null || $key === '') {
        return $default;
    }
    $key = (string)$key;

    try {
        $pdo = getDB();

        try {
            $stmt = $pdo->prepare('SELECT `value` FROM ' . table('settings') . ' WHERE `key_name` = ? LIMIT 1');
            $stmt->execute([$key]);
            $value = $stmt->fetchColumn();
            if ($value !== false) {
                return $value;
            }
        } catch (Throwable $ignored) {
            // Continue to license_settings fallback.
        }

        if (str_starts_with($key, 'license_') || in_array($key, ['allow_unsigned_license'], true)) {
            try {
                $stmt = $pdo->prepare('SELECT `setting_value` FROM ' . table('license_settings') . ' WHERE `setting_key` = ? LIMIT 1');
                $stmt->execute([$key]);
                $value = $stmt->fetchColumn();
                if ($value !== false) {
                    return $value;
                }
            } catch (Throwable $ignored) {
                try {
                    $stmt = $pdo->prepare('SELECT `setting_value` FROM license_settings WHERE `setting_key` = ? LIMIT 1');
                    $stmt->execute([$key]);
                    $value = $stmt->fetchColumn();
                    if ($value !== false) {
                        return $value;
                    }
                } catch (Throwable $ignored2) {
                    // Ignore.
                }
            }
        }

        return $default;
    } catch (Throwable $e) {
        return $default;
    }
}



function licenseEntityLabels(): array
{
    return [
        'products' => 'Products',
        'categories' => 'Categories',
        'customers' => 'Customers',
        'orders' => 'Orders',
        'users' => 'Users',
        'branches' => 'Branches',
        'warehouses' => 'Warehouses',
        'invoices' => 'ERP Invoices',
    ];
}

function licenseNormalizeEntity(string $entity): string
{
    $entity = strtolower(trim($entity));
    $aliases = [
        'product' => 'products',
        'category' => 'categories',
        'customer' => 'customers',
        'order' => 'orders',
        'user' => 'users',
        'branch' => 'branches',
        'warehouse' => 'warehouses',
    ];
    $entity = $aliases[$entity] ?? $entity;
    return array_key_exists($entity, licenseEntityLabels()) ? $entity : 'products';
}

function trialRecordLimit(string $entity = 'products'): int
{
    $entity = licenseNormalizeEntity($entity);
    $specificKey = [
        'products' => 'license_trial_product_limit',
        'categories' => 'license_trial_category_limit',
        'customers' => 'license_trial_customer_limit',
        'orders' => 'license_trial_order_limit',
        'users' => 'license_trial_user_limit',
        'branches' => 'license_trial_branch_limit',
        'warehouses' => 'license_trial_warehouse_limit',
    ][$entity];
    $configured = (int)setting($specificKey, setting('license_trial_record_limit', defined('TRIAL_PRODUCT_LIMIT') ? (string)TRIAL_PRODUCT_LIMIT : '5'));
    return $configured > 0 ? $configured : 5;
}

function trialProductLimit(): int
{
    return trialRecordLimit('products');
}

function licenseInstallationUid(): string
{
    $uid = trim((string)setting('license_installation_uid', ''));
    if ($uid === '') {
        try {
            $uid = bin2hex(random_bytes(16));
        } catch (Throwable $e) {
            $uid = hash('sha256', APP_ENCRYPTION_KEY . '|' . DB_NAME . '|' . microtime(true));
        }
        saveSetting('license_installation_uid', $uid);
    }
    return $uid;
}

function licenseCustomerFingerprint(): string
{
    $host = strtolower((string)(parse_url(SITE_URL, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost')));
    $parts = [
        'suite' => 'ERP-COMMERCE-SUITE-V2',
        'install_uid' => licenseInstallationUid(),
        'db_name' => DB_NAME,
        'db_prefix' => DB_PREFIX,
        'host' => $host,
        'shop_name' => strtolower(trim((string)setting('shop_name', ''))),
        'shop_email' => strtolower(trim((string)setting('shop_email', ''))),
        'app_key_hash' => hash('sha256', APP_ENCRYPTION_KEY),
    ];
    return strtoupper(hash('sha256', json_encode($parts, JSON_UNESCAPED_SLASHES)));
}

function licenseBase64UrlDecode(string $value)
{
    $value = trim(preg_replace('/\s+/', '', $value));
    $value = strtr($value, '-_', '+/');
    $padding = strlen($value) % 4;
    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }
    return base64_decode($value, true);
}

function licenseBase64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function licenseRequestCode(): string
{
    // Unique per customer installation. It is bound to installation UID, database, domain, shop identity and app key.
    $hash = licenseCustomerFingerprint();
    return implode('-', str_split(substr($hash, 0, 40), 4));
}

function licensePublicKeyPem(): string
{
    if (!defined('LICENSE_PUBLIC_KEY_B64') || LICENSE_PUBLIC_KEY_B64 === '') {
        return '';
    }
    $pem = base64_decode(LICENSE_PUBLIC_KEY_B64, true);
    return is_string($pem) ? $pem : '';
}

function licenseDecodeSignedPayload(string $activationCode): ?array
{
    $activationCode = trim($activationCode);
    if (!preg_match('/^LIC2\.([A-Za-z0-9\-_]+)\.([A-Za-z0-9\-_]+)$/', $activationCode, $m)) {
        return null;
    }
    $payloadJson = licenseBase64UrlDecode($m[1]);
    $signature = licenseBase64UrlDecode($m[2]);
    if (!is_string($payloadJson) || !is_string($signature) || $payloadJson === '' || $signature === '') {
        return null;
    }
    $publicKey = licensePublicKeyPem();
    if ($publicKey === '' || !function_exists('openssl_verify')) {
        return null;
    }
    $verified = openssl_verify($m[1], $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) {
        return null;
    }
    $payload = json_decode($payloadJson, true);
    return is_array($payload) ? $payload : null;
}

function licenseHostMatches(array $payload): bool
{
    if ((string)setting('license_bind_domain', '1') !== '1') {
        return true;
    }
    $host = strtolower((string)(parse_url(SITE_URL, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost')));
    $allowed = $payload['domains'] ?? $payload['domain'] ?? [];
    if (is_string($allowed)) {
        $allowed = [$allowed];
    }
    if (!is_array($allowed) || !$allowed) {
        return true;
    }
    foreach ($allowed as $domain) {
        $domain = strtolower(trim((string)$domain));
        if ($domain === '' || $domain === '*' || $domain === $host) {
            return true;
        }
        if (str_starts_with($domain, '*.')) {
            $suffix = substr($domain, 1);
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }
    }
    return false;
}

function licenseFingerprintMatches(array $payload): bool
{
    if ((string)setting('license_bind_fingerprint', '1') !== '1') {
        return true;
    }
    $fingerprint = strtoupper((string)($payload['fingerprint'] ?? ''));
    $requestCode = strtoupper((string)($payload['request_code'] ?? ''));
    $installUid = (string)($payload['installation_uid'] ?? '');
    if ($fingerprint !== '' && hash_equals($fingerprint, licenseCustomerFingerprint())) {
        return true;
    }
    if ($requestCode !== '' && hash_equals($requestCode, licenseRequestCode())) {
        return true;
    }
    if ($installUid !== '' && hash_equals($installUid, licenseInstallationUid())) {
        return true;
    }
    // If the license server does not bind to fingerprint, do not reject.
    return $fingerprint === '' && $requestCode === '' && $installUid === '';
}

function licensePayloadExpired(array $payload): bool
{
    $expires = trim((string)($payload['expires_at'] ?? $payload['expiry'] ?? ''));
    return $expires !== '' && strtotime($expires) !== false && strtotime($expires) < time();
}

function licenseValidatePayload(array $payload): array
{
    if (strtolower((string)($payload['status'] ?? 'active')) !== 'active') {
        return ['valid' => false, 'message' => 'License is not active.'];
    }
    if (!empty($payload['revoked'])) {
        return ['valid' => false, 'message' => 'License was revoked.'];
    }
    if (licensePayloadExpired($payload)) {
        return ['valid' => false, 'message' => 'License has expired.'];
    }
    if (!licenseHostMatches($payload)) {
        return ['valid' => false, 'message' => 'License is not valid for this domain.'];
    }
    if (!licenseFingerprintMatches($payload)) {
        return ['valid' => false, 'message' => 'License is not valid for this installation fingerprint.'];
    }
    return ['valid' => true, 'message' => 'License payload is valid.'];
}

function licenseValidateCode(string $activationCode, ?string $requestCode = null): bool
{
    $activationCode = trim($activationCode);
    if ($activationCode === '') {
        return false;
    }

    // New signed JSON license format: LIC2.<payloadB64Url>.<signatureB64Url>
    $payload = licenseDecodeSignedPayload($activationCode);
    if (is_array($payload)) {
        return licenseValidatePayload($payload)['valid'] === true;
    }

    // Backward-compatible legacy format: signature over request code.
    $requestCode = $requestCode ?: licenseRequestCode();
    $signature = licenseBase64UrlDecode($activationCode);
    if (!is_string($signature) || $signature === '') {
        return false;
    }
    $publicKey = licensePublicKeyPem();
    if ($publicKey === '' || !function_exists('openssl_verify')) {
        return false;
    }
    $verified = openssl_verify($requestCode, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    return $verified === 1;
}

function licenseStoredPayload(): array
{
    $raw = trim((string)setting('license_payload_json', ''));
    $payload = json_decode($raw, true);
    if (is_array($payload)) {
        return $payload;
    }
    $code = (string)setting('license_activation_code', '');
    $decoded = licenseDecodeSignedPayload($code);
    return is_array($decoded) ? $decoded : [];
}

function licenseGraceDays(): int
{
    return max(0, (int)setting('license_grace_days', defined('LICENSE_GRACE_DAYS') ? (string)LICENSE_GRACE_DAYS : '3'));
}

function licenseHeartbeatIntervalHours(): int
{
    return max(1, (int)setting('license_heartbeat_interval_hours', defined('LICENSE_HEARTBEAT_INTERVAL_HOURS') ? (string)LICENSE_HEARTBEAT_INTERVAL_HOURS : '12'));
}

function licenseServerUrl(): string
{
    $configured = trim((string)setting('license_server_url', defined('LICENSE_SERVER_URL') ? LICENSE_SERVER_URL : ''));
    return $configured;
}

function licenseHeartbeatRequired(): bool
{
    return (string)setting('license_heartbeat_enabled', '0') === '1' && licenseServerUrl() !== '';
}

function licenseHeartbeatDue(): bool
{
    if (!licenseHeartbeatRequired()) {
        return false;
    }
    $last = strtotime((string)setting('license_last_heartbeat_at', '')) ?: 0;
    return (time() - $last) >= licenseHeartbeatIntervalHours() * 3600;
}

function licenseRunHeartbeat(bool $force = false): array
{
    if (!$force && !licenseHeartbeatDue()) {
        return ['ok' => true, 'message' => 'Heartbeat not due.'];
    }
    $url = licenseServerUrl();
    if ($url === '') {
        return ['ok' => true, 'message' => 'No license server configured.'];
    }
    $payload = [
        'license_code' => (string)setting('license_activation_code', ''),
        'request_code' => licenseRequestCode(),
        'installation_uid' => licenseInstallationUid(),
        'fingerprint' => licenseCustomerFingerprint(),
        'domain' => parse_url(SITE_URL, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? ''),
        'site_url' => SITE_URL,
        'app_version' => defined('INSTALLER_VERSION') ? INSTALLER_VERSION : 'installed',
    ];
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 8,
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $body,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        saveSetting('license_last_heartbeat_status', 'failed');
        saveSetting('license_last_heartbeat_message', 'Unable to contact license server.');
        return ['ok' => false, 'message' => 'Unable to contact license server.'];
    }
    $json = json_decode((string)$response, true);
    if (!is_array($json) || empty($json['ok'])) {
        saveSetting('license_last_heartbeat_status', 'rejected');
        saveSetting('license_last_heartbeat_message', is_array($json) ? (string)($json['message'] ?? 'Rejected by license server.') : 'Invalid license server response.');
        return ['ok' => false, 'message' => (string)setting('license_last_heartbeat_message', 'Rejected by license server.')];
    }
    saveSetting('license_last_heartbeat_at', date('Y-m-d H:i:s'));
    saveSetting('license_last_heartbeat_status', 'ok');
    saveSetting('license_last_heartbeat_message', (string)($json['message'] ?? 'License heartbeat accepted.'));
    if (!empty($json['license_payload']) && is_array($json['license_payload'])) {
        saveSetting('license_payload_json', json_encode($json['license_payload'], JSON_UNESCAPED_SLASHES));
    }
    return ['ok' => true, 'message' => (string)setting('license_last_heartbeat_message', 'License heartbeat accepted.')];
}

function licenseHeartbeatWithinGrace(): bool
{
    if (!licenseHeartbeatRequired()) {
        return true;
    }
    $lastOk = strtotime((string)setting('license_last_heartbeat_at', '')) ?: 0;
    if ($lastOk <= 0) {
        return false;
    }
    return (time() - $lastOk) <= (licenseGraceDays() * 86400);
}

function licenseIsActivated(): bool
{
    if ((string)setting('license_status', 'trial') !== 'active') {
        return false;
    }
    $activationCode = (string)setting('license_activation_code', '');
    if (!licenseValidateCode($activationCode)) {
        return false;
    }
    if (licenseHeartbeatDue()) {
        licenseRunHeartbeat(false);
    }
    return licenseHeartbeatWithinGrace();
}

function licenseApplyCode(string $activationCode): bool
{
    $activationCode = trim($activationCode);
    if (!licenseValidateCode($activationCode)) {
        return false;
    }
    $payload = licenseDecodeSignedPayload($activationCode) ?: [];
    saveSetting('license_status', 'active');
    saveSetting('license_activation_code', $activationCode);
    saveSetting('license_payload_json', $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES) : '');

    if ($payload) {
        if (!empty($payload['limits']) && is_array($payload['limits'])) {
            saveSetting('license_limits', json_encode($payload['limits'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        if (!empty($payload['modules']) && is_array($payload['modules'])) {
            saveSetting('license_modules', json_encode($payload['modules'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        if (!empty($payload['plan'])) {
            saveSetting('license_plan', (string)$payload['plan']);
        }
        if (!empty($payload['expires_at'])) {
            saveSetting('license_expires_at', (string)$payload['expires_at']);
        }
    }

    saveSetting('license_signature_hash', hash('sha256', $activationCode));
    saveSetting('license_activated_at', date('Y-m-d H:i:s'));
    saveSetting('license_last_validated_at', date('Y-m-d H:i:s'));
    saveSetting('license_last_heartbeat_at', date('Y-m-d H:i:s'));
    saveSetting('license_last_heartbeat_status', 'ok');
    saveSetting('license_last_heartbeat_message', 'Activated locally.');
    try {
        $pdo = getDB();
        if (function_exists('table')) {
            $stmt = $pdo->prepare('INSERT INTO ' . table('license_validation_logs') . ' (status,domain_name,ip_address,message) VALUES (?,?,?,?)');
            $stmt->execute(['active', parse_url(SITE_URL, PHP_URL_HOST) ?: SITE_URL, $_SERVER['REMOTE_ADDR'] ?? '', 'Software activated with signed license.']);
        }
    } catch (Throwable $e) {
        // Do not block activation if the optional log table is unavailable.
    }
    return true;
}


function licenseSavedLimitFallback(string $entity): ?int
{
    $entity = licenseNormalizeEntity($entity);
    $raw = trim((string)setting('license_limits', ''));
    $decoded = $raw !== '' ? json_decode($raw, true) : null;

    if (!is_array($decoded)) {
        return null;
    }

    $value = $decoded[$entity] ?? null;

    if ($value === null || $value === '' || (int)$value <= 0) {
        return null;
    }

    return (int)$value;
}

function licensePlanLimit(string $entity): ?int
{
    $entity = licenseNormalizeEntity($entity);

    if (!licenseIsActivated()) {
        return trialRecordLimit($entity);
    }

    $payload = licenseStoredPayload();
    $limits = $payload['limits'] ?? [];

    if (!is_array($limits)) {
        $limits = [];
    }

    $value = $limits[$entity] ?? $payload['max_' . $entity] ?? null;

    if ($value === null || $value === '' || (int)$value <= 0) {
        $fallback = licenseSavedLimitFallback($entity);
        if ($fallback !== null) {
            return $fallback;
        }

        return null;
    }

    return (int)$value;
}

function licenseEnforcementMode(): string
{
    $mode = (string)setting('license_enforcement_mode', defined('LICENSE_ENFORCEMENT_MODE') ? LICENSE_ENFORCEMENT_MODE : 'enforce');
    return in_array($mode, ['enforce', 'monitor'], true) ? $mode : 'enforce';
}

function licenseCoreFiles(): array
{
    return [
        dirname(__DIR__) . '/config.php',
        __DIR__ . '/functions.php',
        dirname(__DIR__) . '/admin/products.php',
        dirname(__DIR__) . '/admin/categories.php',
        dirname(__DIR__) . '/admin/customers.php',
        dirname(__DIR__) . '/admin/orders.php',
        dirname(__DIR__) . '/activation-loader.php',
    ];
}

function licenseCoreIntegrityHash(): string
{
    $hashes = [];
    foreach (licenseCoreFiles() as $file) {
        if (is_file($file)) {
            $hashes[] = basename($file) . ':' . hash_file('sha256', $file);
        }
    }
    return hash('sha256', implode('|', $hashes));
}

function licenseCheckCoreIntegrity(): bool
{
    $current = licenseCoreIntegrityHash();
    $stored = (string)setting('license_core_integrity_hash', '');
    if ($stored === '') {
        saveSetting('license_core_integrity_hash', $current);
        return true;
    }
    if (!hash_equals($stored, $current)) {
        saveSetting('license_last_tamper_warning', date('Y-m-d H:i:s') . ' Core license files changed.');
        return false;
    }
    return true;
}

function licenseMutationAllowed(): bool
{
    if (licenseEnforcementMode() === 'monitor') {
        return true;
    }
    if ((string)setting('license_readonly_when_invalid', '1') !== '1') {
        return true;
    }
    // Trial mode is allowed, but entity limits still apply.
    if ((string)setting('license_status', 'trial') === 'trial') {
        return true;
    }
    return licenseIsActivated();
}

function requireLicenseMutationAllowed(): void
{
    if (function_exists('licenseRecoveryIsToolPage') && licenseRecoveryIsToolPage()) {
        return;
    }
    if (!licenseMutationAllowed()) {
        flash('error', 'License is invalid, expired, offline beyond grace period, or not verified. The system is locked in read-only mode.');
        redirect(SITE_URL . '/activation-loader.php');
    }
}

function currentLicenseEntityTotal(string $entity, ?PDO $pdo = null): int
{
    $entity = licenseNormalizeEntity($entity);
    $pdo = $pdo ?: getDB();
    $tableMap = [
        'products' => 'products',
        'categories' => 'categories',
        'customers' => 'customers',
        'orders' => 'orders',
        'users' => 'users',
        'branches' => 'branches',
        'warehouses' => 'warehouses',
        'invoices' => 'invoices',
    ];
    try {
        if (!isset($tableMap[$entity])) { return 0; }
        return (int)$pdo->query('SELECT COUNT(*) FROM ' . table($tableMap[$entity]))->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function licenseTrialBaselines(?PDO $pdo = null): array
{
    $pdo = $pdo ?: getDB();
    $raw = trim((string)setting('license_trial_baseline_json', ''));
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return [
            'products' => max(0, (int)($decoded['products'] ?? 0)),
            'categories' => max(0, (int)($decoded['categories'] ?? 0)),
            'customers' => max(0, (int)($decoded['customers'] ?? 0)),
            'orders' => max(0, (int)($decoded['orders'] ?? 0)),
            'users' => max(0, (int)($decoded['users'] ?? 0)),
            'branches' => max(0, (int)($decoded['branches'] ?? 0)),
            'warehouses' => max(0, (int)($decoded['warehouses'] ?? 0)),
        ];
    }
    $baseline = [];
    foreach (array_keys(licenseEntityLabels()) as $entity) {
        $baseline[$entity] = currentLicenseEntityTotal($entity, $pdo);
    }
    saveSetting('license_trial_baseline_json', json_encode($baseline, JSON_UNESCAPED_SLASHES));
    return $baseline;
}

function currentLicenseEntityCount(string $entity, ?PDO $pdo = null): int
{
    $entity = licenseNormalizeEntity($entity);
    $pdo = $pdo ?: getDB();

    if (licenseIsActivated()) {
        return currentLicenseEntityTotal($entity, $pdo);
    }

    $baseline = licenseTrialBaselines($pdo)[$entity] ?? 0;
    return max(0, currentLicenseEntityTotal($entity, $pdo) - (int)$baseline);
}

function currentProductCount(?PDO $pdo = null): int
{
    return currentLicenseEntityCount('products', $pdo);
}

function licenseTrialLimitReached(string $entity = 'products', ?PDO $pdo = null): bool
{
    $entity = licenseNormalizeEntity($entity);
    $limit = licensePlanLimit($entity);
    if ($limit === null) {
        return false;
    }
    return currentLicenseEntityCount($entity, $pdo) >= $limit;
}

function licenseTrialProductLimitReached(?PDO $pdo = null): bool
{
    return licenseTrialLimitReached('products', $pdo);
}

function requireTrialCreationAllowed(string $entity = 'products', ?PDO $pdo = null): void
{
    requireLicenseMutationAllowed();

    $entity = licenseNormalizeEntity($entity);

    if (licenseTrialLimitReached($entity, $pdo)) {
        $labels = licenseEntityLabels();
        $label = $labels[$entity] ?? 'Records';
        $limit = licensePlanLimit($entity);
        $count = currentLicenseEntityCount($entity, $pdo);

        flash('error', 'License limit reached for ' . $label . '. Current: ' . (int)$count . ' / Limit: ' . (int)$limit . '. Existing records are kept, but creating new records is blocked. Please upgrade or activate a higher plan.');

        $back = (string)($_SERVER['HTTP_REFERER'] ?? '');
        redirect($back !== '' ? $back : SITE_URL . '/activation-loader.php');
    }
}

function requireProductCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('products', $pdo);
}

function requireCategoryCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('categories', $pdo);
}

function requireCustomerCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('customers', $pdo);
}

function requireOrderCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('orders', $pdo);
}

function requireUserCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('users', $pdo);
}

function requireBranchCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('branches', $pdo);
}

function requireWarehouseCreationAllowed(?PDO $pdo = null): void
{
    requireTrialCreationAllowed('warehouses', $pdo);
}

function requireInvoiceCreationAllowed(?PDO $pdo = null): void
{
    $pdo = $pdo ?: getDB();
    requireLicenseMutationAllowed();

    $limit = licensePlanLimit('invoices');
    if ($limit !== null) {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM ' . table('invoices'))->fetchColumn();
        if ($count >= (int)$limit) {
            flash('error', 'ERP invoice license limit reached. Current: ' . $count . ' / Limit: ' . (int)$limit . '. Creating a new invoice is blocked for this plan.');
            redirect(ADMIN_URL . '/erp/invoices.php');
        }
    }
}



function licenseAutoGuardCreateLimits(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $uri = strtolower((string)($_SERVER['REQUEST_URI'] ?? ''));

    $map = [
        '/admin/warehouses' => ['entity' => 'warehouses', 'guard' => 'requireWarehouseCreationAllowed'],
        '/admin/branches' => ['entity' => 'branches', 'guard' => 'requireBranchCreationAllowed'],
        '/admin/users' => ['entity' => 'users', 'guard' => 'requireUserCreationAllowed'],
        '/admin/products' => ['entity' => 'products', 'guard' => 'requireProductCreationAllowed'],
        '/admin/erp/invoices' => ['entity' => 'invoices', 'guard' => 'requireInvoiceCreationAllowed'],
        '/admin/erp/create-invoice' => ['entity' => 'invoices', 'guard' => 'requireInvoiceCreationAllowed'],
        '/admin/erp/convert-quotation' => ['entity' => 'invoices', 'guard' => 'requireInvoiceCreationAllowed'],
                '/admin/add-product' => ['entity' => 'products', 'guard' => 'requireProductCreationAllowed'],
];

    $matched = null;
    foreach ($map as $needle => $info) {
        if (str_contains($uri, $needle)) {
            $matched = $info;
            break;
        }
    }

    if (!$matched) {
        return;
    }

    // Do not block delete.
    $action = strtolower((string)($_POST['action'] ?? $_GET['action'] ?? ''));
    if (in_array($action, ['delete', 'remove'], true)) {
        return;
    }

    // Detect update/edit by common id fields. If none exists, treat POST as create.
    $idKeys = ['id', 'warehouse_id', 'branch_id', 'user_id', 'product_id'];
    $hasExistingId = false;
    foreach ($idKeys as $idKey) {
        if (!empty($_POST[$idKey]) || !empty($_GET[$idKey])) {
            $hasExistingId = true;
            break;
        }
    }

    if ($hasExistingId || in_array($action, ['edit', 'update'], true)) {
        return;
    }

    $guard = $matched['guard'];
    if (function_exists($guard)) {
        $guard();
    }
}

licenseAutoGuardCreateLimits();


function licenseStatusSummary(?PDO $pdo = null): array
{
    $active = licenseIsActivated();
    $entities = [];
    foreach (licenseEntityLabels() as $entity => $label) {
        $limit = licensePlanLimit($entity);
        $count = currentLicenseEntityCount($entity, $pdo);
        $locked = $limit !== null && $count >= $limit;
        $entities[$entity] = [
            'label' => $label,
            'count' => $count,
            'limit' => $limit,
            'remaining' => $limit === null ? null : max(0, $limit - $count),
            'locked' => $locked,
            'can_create' => !$locked,
        ];
    }
    $payload = licenseStoredPayload();
    $expiresAt = trim((string)($payload['expires_at'] ?? $payload['expiry'] ?? ''));
    $expired = $expiresAt !== '' && strtotime($expiresAt) !== false && strtotime($expiresAt) < time();
    $daysRemaining = null;
    if ($expiresAt !== '' && strtotime($expiresAt) !== false) {
        $daysRemaining = (int)floor((strtotime($expiresAt) - time()) / 86400);
    }
    return [
        'active' => $active,
        'valid' => $active,
        'expired' => $expired,
        'expires_at' => $expiresAt,
        'days_remaining' => $daysRemaining,
        'plan' => (string)($payload['plan'] ?? setting('license_plan', $active ? 'paid' : 'trial')),
        'status' => $expired ? 'expired' : ($active ? 'active' : 'trial'),
        'product_count' => $entities['products']['count'],
        'product_limit' => $entities['products']['limit'],
        'remaining_products' => $entities['products']['remaining'],
        'entities' => $entities,
        'limit_locked_entities' => array_keys(array_filter($entities, fn($item) => !empty($item['locked']))),
        'request_code' => licenseRequestCode(),
        'installation_uid' => licenseInstallationUid(),
        'customer_fingerprint' => licenseCustomerFingerprint(),
        'activated_at' => (string)setting('license_activated_at', ''),
        'last_heartbeat_at' => (string)setting('license_last_heartbeat_at', ''),
        'heartbeat_status' => (string)setting('license_last_heartbeat_status', ''),
        'heartbeat_message' => (string)setting('license_last_heartbeat_message', ''),
        'core_integrity_ok' => licenseCheckCoreIntegrity(),
        'tamper_warning' => (string)setting('license_last_tamper_warning', ''),
        'mutation_allowed' => licenseMutationAllowed(),
        'creation_mutation_allowed' => licenseMutationAllowed() && empty(array_filter($entities, fn($item) => !empty($item['locked']))),
    ];
}

function renderLicenseAdminNotice(?PDO $pdo = null): void
{
    $state = licenseStatusSummary($pdo);
    if ($state['active']) {
        return;
    }
    $locked = array_filter($state['entities'], fn($item) => !empty($item['locked']));
    $class = $locked ? 'alert-danger' : 'alert-warning';
    echo '<div class="alert ' . $class . ' mt-3">';
    echo '<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">';
    echo '<div><strong>License protection:</strong> Trial and paid plans enforce record limits. Invalid, expired or offline licenses can lock the system in read-only mode.';
    echo '<div class="small text-muted">Request code: <code>' . esc($state['request_code']) . '</code></div></div>';
    echo '<a class="btn btn-sm btn-dark" href="' . esc(SITE_URL) . '/activation-loader.php">Activate Software</a>';
    echo '</div><div class="row g-2 mt-3">';
    foreach ($state['entities'] as $item) {
        $tone = !empty($item['locked']) ? 'border-danger text-danger' : 'border-secondary';
        echo '<div class="col-md-3"><div class="border rounded-3 p-2 ' . $tone . '"><div class="small text-muted">' . esc($item['label']) . '</div><strong>' . (int)$item['count'] . ' / ' . (int)$item['limit'] . '</strong></div></div>';
    }
    echo '</div></div>';
}


function selectedAppModules(): array
{
    $raw = (string)setting('enabled_modules_json', '');
    $modules = json_decode($raw, true);
    if (!is_array($modules) || empty($modules)) {
        return ['__all__'];
    }
    return array_values(array_unique(array_map('strval', $modules)));
}

function moduleSelectionIsConfigured(): bool
{
    $raw = trim((string)setting('enabled_modules_json', ''));
    return $raw !== '';
}

function appModuleEnabled(string $moduleCode): bool
{
    $modules = selectedAppModules();
    return in_array('__all__', $modules, true) || in_array($moduleCode, $modules, true);
}

function appAnyModuleEnabled(array $moduleCodes): bool
{
    $modules = selectedAppModules();
    if (in_array('__all__', $modules, true)) {
        return true;
    }
    foreach ($moduleCodes as $moduleCode) {
        if (in_array((string)$moduleCode, $modules, true)) {
            return true;
        }
    }
    return false;
}

function permissionModuleSelectionMap(): array
{
    return [
        'accounting'=>['accounting_finance'],
        'activity_log'=>['security_deployment'],
        'advanced_ecommerce_settings'=>['advanced_ecommerce'],
        'advanced_quote_requests'=>['advanced_ecommerce'],
        'advanced_reporting'=>['reporting_bi'],
        'ai_assistant'=>['ai_decision'],
        'ai_assistant_2'=>['ai_decision'],
        'ai_automation_dashboard'=>['ai_decision'],
        'ai_risk_scoring'=>['ai_decision'],
        'anomaly_detection'=>['ai_decision'],
        'api_dashboard_2'=>['api_integrations'],
        'api_docs_2'=>['api_integrations'],
        'api_endpoint_catalog'=>['api_integrations'],
        'api_keys'=>['api_integrations'],
        'api_marketplace'=>['api_integrations'],
        'api_usage_limits'=>['api_integrations'],
        'approval_rules'=>['approval_workflow'],
        'approvals'=>['approval_workflow'],
        'ar_ap_aging'=>['accounting_finance'],
        'attendance'=>['hr_payroll'],
        'audit_controls'=>['security_deployment'],
        'audit_trail'=>['security_deployment'],
        'b2b_price_lists'=>['advanced_ecommerce'],
        'backup_restore'=>['security_deployment'],
        'bank_reconciliation'=>['accounting_finance'],
        'barcode_qr'=>['multicompany_inventory'],
        'bi_dashboard_2'=>['reporting_bi'],
        'bin_locations'=>['multicompany_inventory'],
        'bom_management'=>['manufacturing_bom'],
        'budget_control'=>['accounting_finance'],
        'budgeting'=>['accounting_finance'],
        'campaign_automation_2'=>['crm_pipeline'],
        'cash_flow_forecast'=>['accounting_finance'],
        'client_onboarding_checklist'=>['documentation_training'],
        'commercial_packaging'=>['documentation_training'],
        'commissions'=>['hr_payroll'],
        'communication_automation'=>['api_integrations'],
        'compliance_checklists'=>['security_deployment'],
        'consolidation'=>['accounting_finance'],
        'cost_centers'=>['accounting_finance'],
        'credit_control'=>['sales_operations'],
        'crm'=>['crm_pipeline'],
        'crm_advanced'=>['crm_pipeline'],
        'crm_automation'=>['crm_pipeline'],
        'crm_followups'=>['crm_pipeline'],
        'cron_runner'=>['api_integrations'],
        'customer_announcements'=>['customer_portal'],
        'customer_disputes'=>['customer_portal'],
        'customer_documents'=>['customer_portal'],
        'customer_feedback'=>['customer_portal'],
        'customer_portal_admin'=>['customer_portal'],
        'customer_portal_dashboard'=>['customer_portal'],
        'customer_price_rules'=>['advanced_ecommerce'],
        'customer_segments'=>['crm_pipeline'],
        'customer_signoff'=>['service_projects'],
        'customers'=>['sales_operations'],
        'dashboard_widgets'=>['reporting_bi'],
        'data_export_tracking'=>['reporting_bi'],
        'data_import_export'=>['api_integrations'],
        'database_migration_updater'=>['security_deployment'],
        'dataset_cache'=>['reporting_bi'],
        'decision_engine_2'=>['ai_decision'],
        'decision_support'=>['ai_decision'],
        'delivery_notes'=>['sales_operations'],
        'demo_credentials'=>['documentation_training'],
        'demo_data_manager'=>['security_deployment'],
        'deployment_checklist'=>['security_deployment'],
        'developer_docs'=>['documentation_training'],
        'digital_license_control'=>['advanced_ecommerce'],
        'document_access_logs'=>['document_management'],
        'document_approvals'=>['document_management'],
        'document_expiry_alerts'=>['document_management'],
        'document_folders'=>['document_management'],
        'document_library_2'=>['document_management'],
        'document_versions'=>['document_management'],
        'documentation_center'=>['documentation_training'],
        'email_templates'=>['api_integrations'],
        'employee_contracts'=>['hr_payroll'],
        'employee_expenses'=>['hr_payroll'],
        'employee_loans'=>['hr_payroll'],
        'employee_self_service_admin'=>['hr_payroll'],
        'error_logs'=>['security_deployment'],
        'executive_bi'=>['reporting_bi'],
        'feature_comparison_sheet'=>['documentation_training'],
        'field_dispatch'=>['service_projects'],
        'finance'=>['accounting_finance'],
        'finance_automation_dashboard'=>['accounting_finance'],
        'financial_close'=>['accounting_finance'],
        'fixed_assets'=>['accounting_finance'],
        'goods_receipts'=>['multicompany_inventory','procurement_supplier_portal'],
        'handover_center'=>['documentation_training'],
        'hr'=>['hr_payroll'],
        'hr_dashboard_2'=>['hr_payroll'],
        'installer_health_check'=>['security_deployment'],
        'integration_connectors_2'=>['api_integrations'],
        'integration_error_logs'=>['api_integrations'],
        'integration_field_mappings'=>['api_integrations'],
        'integrations'=>['api_integrations'],
        'intercompany'=>['multicompany_inventory'],
        'inventory'=>['multicompany_inventory'],
        'inventory_adjustments'=>['multicompany_inventory'],
        'inventory_valuation'=>['multicompany_inventory'],
        'invoices'=>['accounting_finance'],
        'job_cards'=>['service_projects'],
        'kpi_alerts'=>['reporting_bi'],
        'kpi_builder'=>['reporting_bi'],
        'lead_scoring'=>['crm_pipeline'],
        'leave_balances'=>['hr_payroll'],
        'license_center'=>['saas_subscription'],
        'login_session_monitor'=>['security_deployment'],
        'lot_serial_tracking'=>['multicompany_inventory'],
        'maintenance_mode_enabled'=>['security_deployment'],
        'management_dashboards'=>['reporting_bi'],
        'manufacturing_costing'=>['manufacturing_bom'],
        'manufacturing_dashboard'=>['manufacturing_bom'],
        'marketing_campaigns'=>['crm_pipeline'],
        'marketplace_sync'=>['api_integrations'],
        'material_issue'=>['manufacturing_bom'],
        'metric_library'=>['reporting_bi'],
        'migration_center'=>['saas_subscription'],
        'mobile_app_readiness'=>['mobile_pwa'],
        'mobile_erp'=>['mobile_pwa'],
        'mobile_offline_sync'=>['mobile_pwa'],
        'mobile_quick_actions_2'=>['mobile_pwa'],
        'notifications'=>['api_integrations'],
        'offline_job_cards'=>['service_projects','mobile_pwa'],
        'online_products'=>['website_sales'],
        'online_sales_orders'=>['website_sales'],
        'org_structure'=>['multicompany_inventory'],
        'payroll'=>['hr_payroll'],
        'performance_management'=>['hr_payroll'],
        'permission_change_history'=>['security_deployment'],
        'permission_repair'=>['security_deployment'],
        'picking_packing'=>['multicompany_inventory'],
        'plan_module_matrix'=>['saas_subscription'],
        'predictive_alerts'=>['ai_decision'],
        'procurement_dashboard'=>['procurement_supplier_portal'],
        'product_bundles'=>['advanced_ecommerce'],
        'product_comparison_control'=>['advanced_ecommerce'],
        'production_error_log_viewer'=>['security_deployment'],
        'production_hardening_dashboard'=>['security_deployment'],
        'production_planning'=>['manufacturing_bom'],
        'production_receipts'=>['manufacturing_bom'],
        'projects'=>['service_projects'],
        'purchase_orders'=>['procurement_supplier_portal'],
        'purchase_requisitions'=>['rfq_tender'],
        'push_notifications'=>['mobile_pwa'],
        'pwa_settings'=>['mobile_pwa'],
        'quality_checks'=>['manufacturing_bom'],
        'quotations'=>['sales_operations'],
        'quote_followups'=>['crm_pipeline'],
        'recommendations'=>['ai_decision'],
        'recurring_journals'=>['accounting_finance'],
        'replenishment'=>['multicompany_inventory'],
        'report_builder'=>['reporting_bi'],
        'report_drilldowns'=>['reporting_bi'],
        'report_exports'=>['reporting_bi'],
        'report_storyboards'=>['reporting_bi'],
        'reports'=>['reporting_bi'],
        'returns_rma'=>['sales_operations'],
        'rfq_comparison'=>['rfq_tender'],
        'rfq_management'=>['rfq_tender'],
        'saas_dashboard_2'=>['saas_subscription'],
        'sales_brochure_builder'=>['documentation_training'],
        'sales_crm_dashboard'=>['crm_pipeline'],
        'sales_forecast'=>['crm_pipeline'],
        'sales_opportunities_2'=>['crm_pipeline'],
        'sales_orders'=>['sales_operations'],
        'sales_pipeline'=>['crm_pipeline'],
        'scheduled_reports'=>['reporting_bi'],
        'security_center'=>['security_deployment'],
        'security_compliance_dashboard'=>['security_deployment'],
        'security_policy_center'=>['security_deployment'],
        'sensitive_action_approvals'=>['security_deployment'],
        'service_contracts'=>['service_projects'],
        'settings_repair'=>['security_deployment','seo_frontend_settings'],
        'shift_scheduling'=>['hr_payroll'],
        'smart_action_suggestions'=>['ai_decision'],
        'smart_search'=>['ai_decision'],
        'stock_counts'=>['multicompany_inventory'],
        'stock_transfers'=>['multicompany_inventory'],
        'subscription_plans'=>['saas_subscription'],
        'supplier_contracts'=>['procurement_supplier_portal'],
        'supplier_invoices'=>['accounting_finance','procurement_supplier_portal'],
        'supplier_onboarding'=>['procurement_supplier_portal'],
        'supplier_payment_runs'=>['accounting_finance'],
        'supplier_price_lists'=>['procurement_supplier_portal'],
        'supplier_scorecards'=>['procurement_supplier_portal'],
        'suppliers'=>['procurement_supplier_portal'],
        'system_health'=>['security_deployment'],
        'system_repair_center'=>['security_deployment'],
        'table_column_checker'=>['security_deployment'],
        'tax_automation_2'=>['accounting_finance'],
        'tax_filing'=>['accounting_finance'],
        'technician_checklists'=>['service_projects'],
        'technician_portal'=>['service_projects'],
        'technician_timesheets'=>['service_projects'],
        'tenant_billing'=>['saas_subscription'],
        'tenant_onboarding'=>['saas_subscription'],
        'tenant_subscriptions_2'=>['saas_subscription'],
        'tenant_usage'=>['saas_subscription'],
        'tender_management'=>['rfq_tender'],
        'training_center'=>['documentation_training'],
        'trial_accounts'=>['saas_subscription'],
        'update_center'=>['saas_subscription'],
        'usage_enforcement'=>['saas_subscription'],
        'vendor_portal_admin'=>['procurement_supplier_portal'],
        'warehouse_dashboard'=>['multicompany_inventory'],
        'warehouse_dispatch'=>['multicompany_inventory'],
        'warranty_claims'=>['service_projects'],
        'webhook_builder_2'=>['api_integrations'],
        'webhooks'=>['api_integrations'],
        'whatsapp_automation'=>['api_integrations'],
        'wishlist_control'=>['advanced_ecommerce'],
        'work_centers'=>['manufacturing_bom'],
        'work_orders'=>['manufacturing_bom'],
        'workflow_approval_automation'=>['approval_workflow'],
        'workflow_automation'=>['approval_workflow'],
        'workflow_builder_2'=>['approval_workflow'],
        'workflow_run_history_2'=>['approval_workflow'],
        'workflow_templates_2'=>['approval_workflow']
    ];
}

function permissionAllowedBySelectedModules(string $permission): bool
{
    if ($permission === 'module_bundle_manager') {
        return isModuleBundleDeveloper();
    }
    if (setting('module_bundle_enforcement_enabled','1') !== '1') {
        return true;
    }
    $always = ['access_erp','dashboard','settings_repair','permission_repair','demo_data_manager'];
    if (in_array($permission, $always, true)) {
        return true;
    }
    if (!moduleSelectionIsConfigured()) {
        return true;
    }
    $map = permissionModuleSelectionMap();
    if (!isset($map[$permission])) {
        return true;
    }
    return appAnyModuleEnabled((array)$map[$permission]);
}


function websiteUserRoleOptions(): array
{
    return [
        'customer' => 'Customer',
        'vendor' => 'Vendor',
        'technician' => 'Technician',
        'employee' => 'Employee',
        'admin' => 'Admin',
    ];
}

function normalizeWebsiteUserRole(string $role): string
{
    $role = strtolower(trim($role));
    return array_key_exists($role, websiteUserRoleOptions()) ? $role : 'customer';
}

function userRoleLabel(string $role): string
{
    $options = websiteUserRoleOptions();
    return $options[$role] ?? ucfirst($role);
}

function roleBadgeClass(string $role): string
{
    return match ($role) {
        'admin' => 'danger',
        'employee' => 'primary',
        'vendor' => 'warning text-dark',
        'technician' => 'info text-dark',
        default => 'secondary',
    };
}

function roleIsWebsiteOnly(string $role): bool
{
    return in_array($role, ['customer','vendor','technician'], true);
}

function websiteUserTypesCatalog(): array
{
    return [
        'guest' => 'Guest / Public Visitor',
        'customer' => 'Customer',
        'employee' => 'Employee',
        'vendor' => 'Vendor',
        'technician' => 'Technician',
        'admin' => 'Admin',
    ];
}

function websitePermissionCatalog(): array
{
    return [
        'website_product_view' => [
            'label' => 'View Products',
            'module' => 'website_storefront',
            'group' => 'Public Website',
            'description' => 'Can open the product catalog and product detail pages.',
            'default_user_types' => ['guest','customer','employee','admin'],
        ],
        'website_cart' => [
            'label' => 'Use Cart',
            'module' => 'website_storefront',
            'group' => 'Public Website',
            'description' => 'Can add/view cart and continue to checkout flow.',
            'default_user_types' => ['guest','customer'],
        ],
        'website_checkout' => [
            'label' => 'Checkout / Place Order',
            'module' => 'website_storefront',
            'group' => 'Public Website',
            'description' => 'Can open checkout and place online orders.',
            'default_user_types' => ['guest','customer'],
        ],
        'website_public_downloads' => [
            'label' => 'Public Downloads',
            'module' => 'website_storefront',
            'group' => 'Public Website',
            'description' => 'Can open the public downloads/resource library.',
            'default_user_types' => ['guest','customer','employee','admin'],
        ],
        'website_booking' => [
            'label' => 'Book Support / Service',
            'module' => 'service_projects',
            'group' => 'Public Website',
            'description' => 'Can open booking/support appointment page.',
            'default_user_types' => ['customer','admin'],
        ],
        'b2b_quote_request' => [
            'label' => 'B2B Quote Request',
            'module' => 'advanced_ecommerce',
            'group' => 'B2B E-commerce',
            'description' => 'Can submit B2B quote / enquiry request from website.',
            'default_user_types' => ['customer','employee','admin'],
        ],
        'b2b_bulk_order' => [
            'label' => 'Bulk Order Request',
            'module' => 'advanced_ecommerce',
            'group' => 'B2B E-commerce',
            'description' => 'Can submit bulk order requests.',
            'default_user_types' => ['customer','employee','admin'],
        ],
        'website_wishlist' => [
            'label' => 'Wishlist',
            'module' => 'advanced_ecommerce',
            'group' => 'Customer Shopping Tools',
            'description' => 'Can use wishlist/favorites.',
            'default_user_types' => ['customer'],
        ],
        'website_compare' => [
            'label' => 'Product Compare',
            'module' => 'advanced_ecommerce',
            'group' => 'Customer Shopping Tools',
            'description' => 'Can compare products.',
            'default_user_types' => ['guest','customer'],
        ],
        'customer_dashboard' => [
            'label' => 'Customer Dashboard',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can open the customer portal dashboard.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_orders' => [
            'label' => 'Customer Orders',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can view orders and order details in customer portal.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_invoices' => [
            'label' => 'Customer Invoices',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can view invoices and payment pages.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_downloads' => [
            'label' => 'Customer Downloads',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can access purchased/downloadable product files.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_documents' => [
            'label' => 'Customer Documents',
            'module' => 'document_management',
            'group' => 'Customer Portal',
            'description' => 'Can open customer documents page.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_service_requests' => [
            'label' => 'Customer Service Requests',
            'module' => 'service_projects',
            'group' => 'Customer Portal',
            'description' => 'Can open service requests and assets pages.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_payment_promises' => [
            'label' => 'Payment Promises',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can use payment promise workflow.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_invoice_disputes' => [
            'label' => 'Invoice Disputes',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can use invoice dispute workflow.',
            'default_user_types' => ['customer','admin'],
        ],
        'customer_feedback' => [
            'label' => 'Customer Feedback',
            'module' => 'customer_portal',
            'group' => 'Customer Portal',
            'description' => 'Can submit customer feedback.',
            'default_user_types' => ['customer','admin'],
        ],
        'vendor_portal_access' => [
            'label' => 'Vendor Portal',
            'module' => 'procurement_supplier_portal',
            'group' => 'Vendor Portal',
            'description' => 'Can access vendor portal features.',
            'default_user_types' => ['vendor','admin'],
        ],
        'technician_portal_access' => [
            'label' => 'Technician Portal',
            'module' => 'service_projects',
            'group' => 'Technician Portal',
            'description' => 'Can access technician/mobile job pages.',
            'default_user_types' => ['technician','employee','admin'],
        ],
    ];
}

function defaultWebsitePermissionConfig(): array
{
    $config = [];
    foreach (websitePermissionCatalog() as $key => $permission) {
        $config[$key] = $permission['default_user_types'] ?? [];
    }
    return $config;
}

function websitePermissionConfig(): array
{
    $raw = trim((string)setting('website_permission_config_json', ''));
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        return defaultWebsitePermissionConfig();
    }
    $defaults = defaultWebsitePermissionConfig();
    foreach ($defaults as $permission => $userTypes) {
        if (!array_key_exists($permission, $decoded) || !is_array($decoded[$permission])) {
            $decoded[$permission] = $userTypes;
        }
    }
    return $decoded;
}

function websiteUserType(?array $user = null): string
{
    $user = $user ?: currentUser();
    if (!$user) {
        return 'guest';
    }
    $role = strtolower(trim((string)($user['role'] ?? 'customer')));
    if (in_array($role, ['admin','employee','vendor','technician','customer'], true)) {
        return $role;
    }
    return 'customer';
}

function websitePermissionAllowed(string $permission, ?array $user = null): bool
{
    if (setting('website_permission_enforcement_enabled', '1') !== '1') {
        return true;
    }
    $catalog = websitePermissionCatalog();
    if (!isset($catalog[$permission])) {
        return true;
    }
    $requiredModule = (string)($catalog[$permission]['module'] ?? '');
    $requireActiveModule = setting('website_permission_require_active_module', '0') === '1';
    if ($requireActiveModule && $requiredModule !== '' && !appModuleEnabled($requiredModule)) {
        return false;
    }
    $userType = websiteUserType($user);
    $config = websitePermissionConfig();
    $allowedTypes = $config[$permission] ?? ($catalog[$permission]['default_user_types'] ?? []);
    return in_array($userType, (array)$allowedTypes, true);
}

function websitePermissionGuard(string $permission, string $redirectTo = ''): void
{
    if (websitePermissionAllowed($permission)) {
        return;
    }

    $message = trim((string)setting('website_permission_denied_message', 'Please login first or contact us to access this feature.'));
    if ($message === '') {
        $message = 'Please login first or contact us to access this feature.';
    }

    flash('error', $message);

    if ($redirectTo !== '') {
        redirect($redirectTo);
    }

    if (!currentUser()) {
        $currentUrl = (string)($_SERVER['REQUEST_URI'] ?? '');
        $loginUrl = SITE_URL . '/user/login.php';
        if ($currentUrl !== '') {
            $loginUrl .= '?redirect=' . urlencode($currentUrl);
        }
        redirect($loginUrl);
    }

    redirect(SITE_URL . '/contact.php');
}



function saasModuleCatalogDefaults(): array
{
    return [
        ['dashboard','ERP Dashboard','Core','Command center, KPIs and operational summary'],
        ['ecommerce','E-commerce Storefront','Commerce','Product catalog, product pages, cart and B2C/B2B checkout foundation'],
        ['sales_crm','Sales CRM','Sales','Leads, opportunities, follow-ups and quotation automation'],
        ['inventory','Inventory & Warehouses','Operations','Stock, warehouses, movements and replenishment'],
        ['finance','Finance & Accounting','Finance','Invoices, payments, journals, budgets, cash flow and tax automation'],
        ['procurement','Procurement & Supplier Portal','Procurement','Suppliers, RFQs, POs, contracts, scorecards and supplier portal'],
        ['service','Service & Technician Portal','Service','Job cards, technician mobile flow, contracts and warranties'],
        ['manufacturing','Manufacturing','Production','BOM, work orders, production planning and costing'],
        ['hr_payroll','HR & Payroll','People','Employees, attendance, leave, expenses and payroll'],
        ['bi_reporting','BI & Reporting','Analytics','KPI builder, dashboards, storyboards and reports'],
        ['ai_automation','AI Automation','Automation','Risk scoring, decision engine and smart assistant'],
        ['workflow_automation','Workflow Automation','Automation','Workflow builder, triggers, conditions and actions'],
        ['security_compliance','Security & Compliance','Security','Audit logs, security policies and compliance checklists'],
        ['api_integrations','API & Integrations','Platform','API keys, webhooks and integration connectors'],
    ];
}

function installSaasModuleCatalog(PDO $pdo): int
{
    $created=0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('saas_module_catalog').' (module_key,module_name,module_group,description,default_enabled,status) VALUES (?,?,?,?,1,"active")');
    foreach(saasModuleCatalogDefaults() as $m){$stmt->execute($m);$created += $stmt->rowCount()>0?1:0;}
    return $created;
}

function installPlanModuleMatrix(PDO $pdo, int $planId = 0): int
{
    installSaasModuleCatalog($pdo);
    $plans=$planId>0 ? (function() use ($pdo,$planId){$s=$pdo->prepare('SELECT * FROM '.table('subscription_plans').' WHERE id=?');$s->execute([$planId]);return $s->fetchAll();})() : $pdo->query('SELECT * FROM '.table('subscription_plans').' ORDER BY monthly_price ASC')->fetchAll();
    $modules=$pdo->query('SELECT * FROM '.table('saas_module_catalog').' WHERE status="active" ORDER BY module_group,module_name')->fetchAll();
    $count=0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('saas_plan_modules').' (subscription_plan_id,module_key,module_name,is_enabled,included_limit,overage_price,status) VALUES (?,?,?,?,?,"0","active")');
    foreach($plans as $plan){
        $price=(float)$plan['monthly_price'];
        foreach($modules as $m){
            $enabled=1;
            if($price<=0 && !in_array($m['module_key'],['dashboard','ecommerce','inventory'],true)){$enabled=0;}
            if($price>0 && $price<99 && in_array($m['module_key'],['manufacturing','ai_automation','workflow_automation','api_integrations'],true)){$enabled=0;}
            $stmt->execute([(int)$plan['id'],$m['module_key'],$m['module_name'],$enabled,'included']);
            $count += $stmt->rowCount()>0?1:0;
        }
    }
    return $count;
}

function createSaasTrialAccount(PDO $pdo, int $companyId, ?int $planId, string $name, string $email, string $phone='', string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'trial_account',setting('trial_account_prefix','TRIAL'),operationalScope($pdo));
    $days=max(1,(int)setting('default_trial_days','14'));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('saas_trial_accounts').' (trial_number,company_id,subscription_plan_id,contact_name,contact_email,contact_phone,trial_start,trial_end,status,notes,created_by) VALUES (?,?,?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL '.$days.' DAY),"active",?,?)')->execute([$number,$companyId,$planId,$name,$email,$phone,$notes,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function createTenantSubscription2(PDO $pdo, int $companyId, int $planId, string $status='active', string $cycle='monthly', string $start='', string $end='', string $notes=''): int
{
    $planStmt=$pdo->prepare('SELECT * FROM '.table('subscription_plans').' WHERE id=? LIMIT 1');$planStmt->execute([$planId]);$plan=$planStmt->fetch();
    if(!$plan){throw new RuntimeException('Subscription plan not found.');}
    $number=nextScopedDocumentNumber($pdo,'tenant_subscription','SUB',operationalScope($pdo));
    $amount=$cycle==='yearly'?(float)$plan['yearly_price']:(float)$plan['monthly_price'];
    $start=$start ?: date('Y-m-d');
    $end=$end ?: date('Y-m-d',strtotime($cycle==='yearly'?'+1 year':'+1 month'));
    $next=$cycle==='yearly'?date('Y-m-d',strtotime($start.' +1 year')):date('Y-m-d',strtotime($start.' +1 month'));
    $pdo->prepare('INSERT INTO '.table('tenant_subscriptions').' (company_id,subscription_plan_id,subscription_number,status,start_date,end_date,next_billing_date,billing_cycle,amount,auto_renew,notes) VALUES (?,?,?,?,?,?,?,?,?,1,?)')->execute([$companyId,$planId,$number,$status,$start,$end,$next,$cycle,$amount,$notes]);
    installPlanModuleMatrix($pdo,$planId);
    return (int)$pdo->lastInsertId();
}

function createSaasSubscriptionInvoice(PDO $pdo, int $subscriptionId): int
{
    $stmt=$pdo->prepare('SELECT ts.*,sp.plan_name,sp.monthly_price,sp.yearly_price,sp.id plan_id FROM '.table('tenant_subscriptions').' ts LEFT JOIN '.table('subscription_plans').' sp ON sp.id=ts.subscription_plan_id WHERE ts.id=? LIMIT 1');
    $stmt->execute([$subscriptionId]);$sub=$stmt->fetch();
    if(!$sub){throw new RuntimeException('Tenant subscription not found.');}
    $number=nextScopedDocumentNumber($pdo,'saas_subscription_invoice',setting('saas_subscription_invoice_prefix','SUBINV'),operationalScope($pdo));
    $subtotal=(float)$sub['amount'];
    if($subtotal<=0){$subtotal=$sub['billing_cycle']==='yearly'?(float)$sub['yearly_price']:(float)$sub['monthly_price'];}
    $tax=round($subtotal*((float)setting('saas_tax_rate_percent','5')/100),2);
    $total=$subtotal+$tax;
    $periodStart=$sub['next_billing_date'] ?: date('Y-m-d');
    $periodEnd=$sub['billing_cycle']==='yearly'?date('Y-m-d',strtotime($periodStart.' +1 year -1 day')):date('Y-m-d',strtotime($periodStart.' +1 month -1 day'));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('saas_subscription_invoices').' (invoice_number,company_id,tenant_subscription_id,subscription_plan_id,invoice_date,due_date,billing_period_start,billing_period_end,subtotal,tax_amount,total,balance_due,status,notes,created_by) VALUES (?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 7 DAY),?,?,?,?,?,"unpaid",?,?)')->execute([$number,(int)$sub['company_id'],(int)$sub['id'],(int)$sub['plan_id'],$periodStart,$periodEnd,$subtotal,$tax,$total,$total,'Subscription billing for '.$sub['plan_name'],(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function recordSaasSubscriptionPayment(PDO $pdo, int $invoiceId, float $amount, string $method, string $reference='', string $notes=''): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('saas_subscription_invoices').' WHERE id=? LIMIT 1');$stmt->execute([$invoiceId]);$invoice=$stmt->fetch();
    if(!$invoice){throw new RuntimeException('Subscription invoice not found.');}
    $number=nextScopedDocumentNumber($pdo,'saas_subscription_payment',setting('saas_subscription_payment_prefix','SUBPAY'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('saas_subscription_payments').' (payment_number,saas_subscription_invoice_id,company_id,payment_date,amount,payment_method,payment_reference,status,notes,created_by) VALUES (?,?,?,CURDATE(),?,?,?,"received",?,?)')->execute([$number,$invoiceId,(int)$invoice['company_id'],$amount,$method,$reference,$notes,(int)($user['id']??0)?:null]);
    $pdo->prepare('UPDATE '.table('saas_subscription_invoices').' SET balance_due=GREATEST(balance_due-?,0),status=CASE WHEN GREATEST(balance_due-?,0)<=0 THEN "paid" ELSE "partial" END WHERE id=?')->execute([$amount,$amount,$invoiceId]);
    return (int)$pdo->lastInsertId();
}

function createSaasPlanChangeRequest(PDO $pdo, int $companyId, int $requestedPlanId, string $type, string $notes=''): int
{
    $current=currentCompanySubscription($pdo,$companyId);
    $number=nextScopedDocumentNumber($pdo,'plan_change',setting('plan_change_prefix','PLNCHG'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('saas_plan_change_requests').' (request_number,company_id,current_plan_id,requested_plan_id,change_type,status,requested_by,requested_at,effective_date,notes) VALUES (?,?,?,?,?,"pending",?,NOW(),CURDATE(),?)')->execute([$number,$companyId,(int)($current['subscription_plan_id']??0)?:null,$requestedPlanId,$type,(int)($user['id']??0)?:null,$notes]);
    return (int)$pdo->lastInsertId();
}

function approveSaasPlanChange(PDO $pdo, int $requestId): void
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('saas_plan_change_requests').' WHERE id=? LIMIT 1');$stmt->execute([$requestId]);$req=$stmt->fetch();
    if(!$req){throw new RuntimeException('Plan change request not found.');}
    $user=currentUser();
    $pdo->prepare('UPDATE '.table('tenant_subscriptions').' SET subscription_plan_id=?,status="active",start_date=CURDATE(),next_billing_date=DATE_ADD(CURDATE(), INTERVAL 1 MONTH),notes=CONCAT(COALESCE(notes,""), "\nPlan changed by request '.$req['request_number'].'") WHERE company_id=? ORDER BY id DESC LIMIT 1')->execute([(int)$req['requested_plan_id'],(int)$req['company_id']]);
    $pdo->prepare('UPDATE '.table('saas_plan_change_requests').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,$requestId]);
    installPlanModuleMatrix($pdo,(int)$req['requested_plan_id']);
}

function enforceTenantUsageLimits(PDO $pdo, int $companyId): array
{
    if(setting('usage_enforcement_enabled','1')!=='1'){return ['created'=>0,'violations'=>[]];}
    $status=tenantUsageLimitStatus($pdo,$companyId);
    $created=0;$sub=$status['subscription']??null;
    foreach($status['violations'] as $violation){
        $metric=trim(explode(' exceeded:', $violation)[0]);
        $current=(float)($status['usage'][$metric]??0);
        $limit=(float)($status['limits'][$metric]??0);
        $exists=$pdo->prepare('SELECT id FROM '.table('saas_usage_enforcement_logs').' WHERE company_id=? AND metric_key=? AND status="open" LIMIT 1');
        $exists->execute([$companyId,$metric]);
        if($exists->fetchColumn()){continue;}
        $number=nextScopedDocumentNumber($pdo,'usage_enforcement',setting('usage_enforcement_prefix','USG'),operationalScope($pdo));
        $pdo->prepare('INSERT INTO '.table('saas_usage_enforcement_logs').' (enforcement_number,company_id,tenant_subscription_id,module_key,metric_key,current_value,limit_value,severity,status,message) VALUES (?,?,?,?,?,?,?,"warning","open",?)')->execute([$number,$companyId,(int)($sub['id']??0)?:null,'tenant_limits',$metric,$current,$limit,$violation]);
        $created++;
    }
    return ['created'=>$created,'violations'=>$status['violations'],'usage'=>$status['usage'],'limits'=>$status['limits']];
}

function createTenantOnboardingTask(PDO $pdo, int $companyId, string $title, string $type='setup', string $due='', ?int $assignedTo=null, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'tenant_onboarding',setting('tenant_onboarding_prefix','ONB'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('saas_onboarding_tasks').' (task_number,company_id,task_title,task_type,status,due_date,assigned_to,notes,created_by) VALUES (?,?,?,?,"pending",?,?,?,?)')->execute([$number,$companyId,$title,$type,$due?:date('Y-m-d',strtotime('+3 days')),$assignedTo,$notes,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}


function currentCompanySubscription(PDO $pdo, ?int $companyId = null): ?array
{
    $companyId = $companyId ?: (int)(operationalScope($pdo)['company_id'] ?? 0);
    if ($companyId <= 0) {return null;}
    $stmt=$pdo->prepare('SELECT ts.*,sp.plan_code,sp.plan_name,sp.user_limit,sp.branch_limit,sp.warehouse_limit,sp.product_limit,sp.storage_limit_mb,sp.api_call_limit_monthly FROM '.table('tenant_subscriptions').' ts LEFT JOIN '.table('subscription_plans').' sp ON sp.id=ts.subscription_plan_id WHERE ts.company_id=? ORDER BY ts.id DESC LIMIT 1');
    $stmt->execute([$companyId]);
    return $stmt->fetch() ?: null;
}

function captureTenantUsage(PDO $pdo, int $companyId): array
{
    if($companyId<=0){throw new RuntimeException('Company is required for usage capture.');}
    $userCount=(int)$pdo->query('SELECT COUNT(*) FROM '.table('users').' WHERE status="active"')->fetchColumn();
    $branchStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('branches').' WHERE company_id=? AND status="active"');$branchStmt->execute([$companyId]);$branchCount=(int)$branchStmt->fetchColumn();
    $warehouseStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('warehouses').' WHERE company_id=? AND status="active"');$warehouseStmt->execute([$companyId]);$warehouseCount=(int)$warehouseStmt->fetchColumn();
    $productCount=(int)$pdo->query('SELECT COUNT(*) FROM '.table('products').' WHERE active=1')->fetchColumn();
    $storageMb=0.0;$root=localUploadRoot();
    if(is_dir($root)){
        $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach($iterator as $file){if($file->isFile()){$storageMb += $file->getSize()/1048576;}}
    }
    $apiStmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('api_access_logs').' WHERE created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")');$apiStmt->execute();$apiCalls=(int)$apiStmt->fetchColumn();
    $stmt=$pdo->prepare('INSERT INTO '.table('tenant_usage_snapshots').' (company_id,snapshot_date,user_count,branch_count,warehouse_count,product_count,storage_used_mb,api_calls_month) VALUES (?,DATE(NOW()),?,?,?,?,?,?) ON DUPLICATE KEY UPDATE user_count=VALUES(user_count),branch_count=VALUES(branch_count),warehouse_count=VALUES(warehouse_count),product_count=VALUES(product_count),storage_used_mb=VALUES(storage_used_mb),api_calls_month=VALUES(api_calls_month)');
    $stmt->execute([$companyId,$userCount,$branchCount,$warehouseCount,$productCount,round($storageMb,2),$apiCalls]);
    return ['user_count'=>$userCount,'branch_count'=>$branchCount,'warehouse_count'=>$warehouseCount,'product_count'=>$productCount,'storage_used_mb'=>round($storageMb,2),'api_calls_month'=>$apiCalls];
}

function tenantUsageLimitStatus(PDO $pdo, int $companyId): array
{
    $sub=currentCompanySubscription($pdo,$companyId);
    $usage=captureTenantUsage($pdo,$companyId);
    if(!$sub){return ['subscription'=>null,'usage'=>$usage,'limits'=>[],'violations'=>['No subscription found']];}
    $limits=[
        'user_count'=>(int)$sub['user_limit'],
        'branch_count'=>(int)$sub['branch_limit'],
        'warehouse_count'=>(int)$sub['warehouse_limit'],
        'product_count'=>(int)$sub['product_limit'],
        'storage_used_mb'=>(int)$sub['storage_limit_mb'],
        'api_calls_month'=>(int)$sub['api_call_limit_monthly'],
    ];
    $violations=[];
    foreach($limits as $key=>$limit){if($limit>0 && (float)$usage[$key] > $limit){$violations[]=$key.' exceeded: '.$usage[$key].' / '.$limit;}}
    return ['subscription'=>$sub,'usage'=>$usage,'limits'=>$limits,'violations'=>$violations];
}

function licenseStatus(PDO $pdo, ?int $companyId = null): array
{
    $companyId=$companyId ?: (int)(operationalScope($pdo)['company_id'] ?? 0);
    $stmt=$pdo->prepare('SELECT lk.*,sp.plan_name FROM '.table('license_keys').' lk LEFT JOIN '.table('subscription_plans').' sp ON sp.id=lk.subscription_plan_id WHERE (lk.company_id=? OR lk.company_id IS NULL) ORDER BY lk.company_id DESC,lk.id DESC LIMIT 1');
    $stmt->execute([$companyId]);
    $license=$stmt->fetch();
    if(!$license){return ['status'=>'missing','license'=>null,'message'=>'No license key registered.'];}
    $status=(string)$license['status'];$message='License status: '.$status;
    if(!empty($license['expires_at']) && strtotime((string)$license['expires_at']) < time()){
        $status='expired';$message='License expired on '.$license['expires_at'];
    }
    $pdo->prepare('UPDATE '.table('license_keys').' SET last_validated_at=NOW() WHERE id=?')->execute([(int)$license['id']]);
    $pdo->prepare('INSERT INTO '.table('license_validation_logs').' (license_key_id,status,domain_name,ip_address,message) VALUES (?,?,?,?,?)')->execute([(int)$license['id'],$status,$_SERVER['HTTP_HOST']??SHOP_URL,$_SERVER['REMOTE_ADDR']??'', $message]);
    return ['status'=>$status,'license'=>$license,'message'=>$message];
}

function moduleEnabled(PDO $pdo, string $moduleKey, ?int $companyId = null): bool
{
    $companyId=$companyId ?: (int)(operationalScope($pdo)['company_id'] ?? 0);
    $stmt=$pdo->prepare('SELECT is_enabled FROM '.table('module_entitlements').' WHERE module_key=? AND (company_id=? OR company_id IS NULL) ORDER BY company_id DESC LIMIT 1');
    $stmt->execute([$moduleKey,$companyId]);
    $value=$stmt->fetchColumn();
    return $value === false ? true : ((int)$value === 1);
}

function recordUpgradeEvent(PDO $pdo, string $eventType, string $status, string $fromVersion, string $toVersion, string $description): void
{
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('upgrade_mode_events').' (event_type,status,from_version,to_version,description,created_by,completed_at) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$eventType,$status,$fromVersion,$toVersion,$description,(int)($user['id']??0)?:null,$status==='completed'?date('Y-m-d H:i:s'):null]);
}



function recordLoginSession(PDO $pdo, ?int $userId, string $email, string $status='success', float $riskScore=0, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'login_session',setting('login_session_prefix','LGS'),operationalScope($pdo));
    $hash=hash('sha256',session_id().($email).microtime(true));
    $pdo->prepare('INSERT INTO '.table('login_session_logs').' (session_number,user_id,email,login_status,ip_address,user_agent,login_at,last_activity_at,session_hash,risk_score,notes) VALUES (?,?,?,?,?,?,NOW(),NOW(),?,?,?)')->execute([$number,$userId,$email,$status,$_SERVER['REMOTE_ADDR']??'',substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255),$hash,$riskScore,$notes]);
    return (int)$pdo->lastInsertId();
}

function recordPermissionChange(PDO $pdo, ?int $roleId, ?int $userId, string $type, string $old, string $new, string $description): int
{
    $number=nextScopedDocumentNumber($pdo,'permission_change',setting('permission_change_prefix','PCH'),operationalScope($pdo));
    $actor=currentUser();
    $pdo->prepare('INSERT INTO '.table('permission_change_history').' (change_number,role_id,user_id,changed_by,change_type,old_permissions,new_permissions,description,ip_address) VALUES (?,?,?,?,?,?,?,?,?)')->execute([$number,$roleId,$userId,(int)($actor['id']??0)?:null,$type,$old,$new,$description,$_SERVER['REMOTE_ADDR']??'']);
    logActivity($pdo,'Security','permission_change',$description,'permission_change',$roleId ?: $userId);
    return (int)$pdo->lastInsertId();
}

function recordDataExport(PDO $pdo, string $module, string $type, string $fileName, int $rows, array $filters=[]): int
{
    if(setting('data_export_tracking_enabled','1')!=='1'){return 0;}
    $number=nextScopedDocumentNumber($pdo,'data_export',setting('data_export_prefix','DEXP'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('data_export_logs').' (export_number,user_id,module,export_type,file_name,row_count,filter_json,ip_address,status) VALUES (?,?,?,?,?,?,?,?,"created")')->execute([$number,(int)($user['id']??0)?:null,$module,$type,$fileName,$rows,json_encode($filters,JSON_UNESCAPED_SLASHES),$_SERVER['REMOTE_ADDR']??'']);
    recordSecurityEvent($pdo,'data_export','Data export created: '.$module.' / '.$fileName,'info',(int)($user['id']??0)?:null);
    return (int)$pdo->lastInsertId();
}

function createSensitiveActionApproval(PDO $pdo, string $actionKey, string $module, string $referenceType, ?int $referenceId, string $reason, string $riskLevel='medium'): int
{
    $number=nextScopedDocumentNumber($pdo,'sensitive_action',setting('sensitive_action_prefix','SAAP'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('sensitive_action_approvals').' (approval_number,requested_by,action_key,module,reference_type,reference_id,reason,risk_level,status,requested_at) VALUES (?,?,?,?,?,?,?,?, "pending", NOW())')->execute([$number,(int)($user['id']??0)?:null,$actionKey,$module,$referenceType,$referenceId,$reason,$riskLevel]);
    recordSecurityEvent($pdo,'sensitive_action_requested','Sensitive action approval requested: '.$actionKey,'warning',(int)($user['id']??0)?:null);
    return (int)$pdo->lastInsertId();
}

function createSuspiciousActivityEvent(PDO $pdo, string $type, string $description, float $riskScore=60, string $severity='warning', ?int $userId=null): int
{
    $number=nextScopedDocumentNumber($pdo,'security_event',setting('security_event_prefix','SEV'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('suspicious_activity_events').' (event_number,user_id,event_type,severity,ip_address,user_agent,risk_score,description,status) VALUES (?,?,?,?,?,?,?,?,"open")')->execute([$number,$userId ?: ($_SESSION['user_id']??null),$type,$severity,$_SERVER['REMOTE_ADDR']??'',substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255),$riskScore,$description]);
    recordSecurityEvent($pdo,$type,$description,$severity,$userId);
    return (int)$pdo->lastInsertId();
}

function passwordPolicyCheck(string $password): array
{
    $errors=[];
    $min=(int)setting('password_min_length','8');
    if(strlen($password)<$min){$errors[]='Password must be at least '.$min.' characters.';}
    if(setting('password_require_uppercase','1')==='1' && !preg_match('/[A-Z]/',$password)){$errors[]='Password must include an uppercase letter.';}
    if(setting('password_require_number','1')==='1' && !preg_match('/[0-9]/',$password)){$errors[]='Password must include a number.';}
    return ['valid'=>empty($errors),'errors'=>$errors];
}

function securityScore(PDO $pdo): int
{
    $score=100;
    if(setting('two_factor_foundation_enabled','0')!=='1'){$score-=10;}
    if(setting('ip_access_control_enabled','0')!=='1'){$score-=5;}
    if((int)setting('password_min_length','8')<10){$score-=5;}
    $open=(int)$pdo->query('SELECT COUNT(*) FROM '.table('suspicious_activity_events').' WHERE status="open"')->fetchColumn();
    if($open>0){$score-=min(25,$open*3);}
    $pending=(int)$pdo->query('SELECT COUNT(*) FROM '.table('sensitive_action_approvals').' WHERE status="pending"')->fetchColumn();
    if($pending>0){$score-=min(15,$pending*2);}
    return max(0,min(100,$score));
}


function recordSecurityEvent(PDO $pdo, string $eventType, string $description, string $severity = 'info', ?int $userId = null): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO ' . table('security_events') . ' (user_id,event_type,severity,ip_address,user_agent,description) VALUES (?,?,?,?,?,?)');
        $stmt->execute([
            $userId ?: ($_SESSION['user_id'] ?? null),
            $eventType,
            $severity,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            $description,
        ]);
    } catch (Throwable $e) {
    }
}

function recordSystemError(PDO $pdo, Throwable $error, array $context = []): void
{
    if (setting('system_error_logging_enabled','1') !== '1') {
        return;
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO ' . table('system_error_logs') . ' (severity,message,file_path,line_number,context_json,status) VALUES ("error",?,?,?,?, "open")');
        $stmt->execute([
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
            json_encode($context, JSON_UNESCAPED_SLASHES),
        ]);
    } catch (Throwable $e) {
    }
}

function dashboardWidgetDefaults(): array
{
    return [
        'executive_kpis' => 'Executive KPI Summary',
        'approval_queue' => 'Approval Queue',
        'cash_receivables' => 'Cash & Receivables',
        'inventory_alerts' => 'Inventory Alerts',
        'sales_pipeline' => 'Sales Pipeline',
        'service_profitability' => 'Service Profitability',
        'project_margin' => 'Project Margin',
        'system_health' => 'System Health',
    ];
}


if(!function_exists('saveSetting')){
function saveSetting($key, $value): void
{
    // saveSetting() null-safe key guard
    if ($key === null || $key === '') {
        return;
    }
    $key = (string)$key;
    $value = (string)$value;

    $pdo = getDB();

    try {
        $stmt = $pdo->prepare('INSERT INTO ' . table('settings') . ' (`key_name`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
        $stmt->execute([$key, $value]);
    } catch (Throwable $ignored) {
        // Some installs may not have normal settings table here.
    }

    if (str_starts_with($key, 'license_') || in_array($key, ['allow_unsigned_license'], true)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO ' . table('license_settings') . ' (`setting_key`,`setting_value`,`updated_at`) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`), `updated_at`=NOW()');
            $stmt->execute([$key, $value]);
            return;
        } catch (Throwable $ignored) {
            try {
                $stmt = $pdo->prepare('INSERT INTO license_settings (`setting_key`,`setting_value`,`updated_at`) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`), `updated_at`=NOW()');
                $stmt->execute([$key, $value]);
                return;
            } catch (Throwable $ignored2) {
                // Ignore.
            }
        }
    }
}
}


function p35DocumentationDefaults(): array
{
    return [
        ['README','readme','owner','general','readme','System overview, module map, installation flow and release position.'],
        ['Installation Guide','installation','technical','install','installation-guide','Server setup, database setup, installer workflow and post-install checklist.'],
        ['Admin Manual','admin_manual','admin','admin','admin-manual','Admin dashboard, roles, settings, repair center and operational workflows.'],
        ['User Manual','user_manual','staff','erp','user-manual','Daily ERP, sales, inventory, finance, documents and portal usage.'],
        ['Developer Handover','developer_handover','developer','developer','developer-handover','File structure, database tables, extension points and safe upgrade notes.'],
        ['Commercial Package','commercial','sales','commercial','commercial-package','Pricing model, packages, implementation scope and sales positioning.'],
    ];
}

function p35InstallDefaults(PDO $pdo): int
{
    $created=0;$user=currentUser();
    foreach(p35DocumentationDefaults() as $d){
        $exists=$pdo->prepare('SELECT id FROM '.table('documentation_articles').' WHERE slug=? LIMIT 1');$exists->execute([$d[4]]);
        if(!$exists->fetchColumn()){
            $number=nextScopedDocumentNumber($pdo,'documentation_article',setting('documentation_article_prefix','DOCART'),operationalScope($pdo));
            $content="# ".$d[0]."\n\n".$d[5]."\n\n## Recommended Usage\nUse this article from the documentation center. Use browser print or Save as PDF for a PDF-ready version.";
            $pdo->prepare('INSERT INTO '.table('documentation_articles').' (article_number,title,doc_type,audience,module_key,slug,content,status,sort_order,created_by) VALUES (?,?,?,?,?,?,?,"published",?,?)')->execute([$number,$d[0],$d[1],$d[2],$d[3],$d[4],$content,$created+1,(int)($user['id']??0)?:null]);
            $created++;
        }
    }
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('commercial_packages'))===0){
        $starter=p35CreateCommercialPackage($pdo,'Starter ERP Package','license','Small business / first client','one_time',2500,(string)setting('commercial_default_currency','AED'),7,'Entry package for small shops and service businesses.');
        p35AddPackageFeature($pdo,$starter,'Core','Products, customers, orders, invoices','Included core commerce and ERP workflow.','Unlimited basic records',1);
        p35AddPackageFeature($pdo,$starter,'Support','Starter onboarding','Basic setup guidance and training checklist.','1 onboarding session',2);
        $pro=p35CreateCommercialPackage($pdo,'Professional ERP Package','license','Growing B2B/B2C business','one_time',7500,(string)setting('commercial_default_currency','AED'),14,'Professional package for companies needing workflows, documents, reporting and portals.');
        p35AddPackageFeature($pdo,$pro,'ERP','Sales, inventory, finance, procurement, DMS','Full operational system for multi-user ERP rollout.','Core modules',1);
        p35AddPackageFeature($pdo,$pro,'Commercial','B2B pricing, quote requests, bundles','Advanced ecommerce and sales tools.','Advanced commerce included',2);
        $created+=2;
    }
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('training_courses'))===0){
        p35CreateTrainingCourse($pdo,'Admin Training','admin_training','admin',90,'Admin setup, settings, users, permissions and repair center.');
        p35CreateTrainingCourse($pdo,'Sales & Store Training','sales_training','sales',60,'Products, quotes, orders, customers and ecommerce workflows.');
        p35CreateTrainingCourse($pdo,'Inventory & Finance Training','operations_training','staff',75,'Stock, invoices, payments, supplier and reporting workflows.');
        $created+=3;
    }
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('feature_comparison_items'))===0){
        p35CreateFeatureComparisonItem($pdo,'Core ERP','Multi-module ERP','ERP + ecommerce + portals + repair center','SAP/Oracle require heavier implementation and licensing.','Lower-cost implementation path for SMEs.',1);
        p35CreateFeatureComparisonItem($pdo,'Commerce','B2B/B2C commerce','Built-in storefront, quotes, wishlists and bundles','Enterprise suites often need external commerce modules.','Faster online selling and quoting.',2);
        p35CreateFeatureComparisonItem($pdo,'Deployment','PHP/MySQL installer','Simple hosting deployment with repair tools','Large suites require specialist infrastructure.','Lower technical barrier for small teams.',3);
        $created+=3;
    }
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('sales_brochure_sections'))===0){
        p35CreateSalesBrochureSection($pdo,'Hero','brochure','ERP + E-commerce system for modern businesses','Sell online, manage operations, support customers and train staff from one connected system.','Book a demo',1);
        p35CreateSalesBrochureSection($pdo,'Why It Wins','brochure','Built for practical business rollout','Includes ERP, commerce, mobile/PWA, document control, repair center and commercial packaging.','Start implementation',2);
        $created+=2;
    }
    p35CreateDemoCredentialsIfEmpty($pdo);
    return $created;
}

function p35CreateCommercialPackage(PDO $pdo, string $name, string $type, string $target, string $cycle, float $price, string $currency, int $days, string $description): int
{
    $number=nextScopedDocumentNumber($pdo,'commercial_package',setting('commercial_package_prefix','PKG'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('commercial_packages').' (package_number,package_name,package_type,target_customer,billing_cycle,base_price,currency,implementation_days,status,description,created_by,updated_at) VALUES (?,?,?,?,?,?,?,?,"active",?,?,NOW())')->execute([$number,$name,$type,$target,$cycle,$price,$currency,$days,$description,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function p35AddPackageFeature(PDO $pdo, int $packageId, string $group, string $name, string $description, string $limit='', int $sort=0): int
{
    $pdo->prepare('INSERT INTO '.table('commercial_package_features').' (commercial_package_id,feature_group,feature_name,feature_description,included_limit,sort_order,status) VALUES (?,?,?,?,?,?,"active")')->execute([$packageId,$group,$name,$description,$limit,$sort]);
    return (int)$pdo->lastInsertId();
}

function p35CreateTrainingCourse(PDO $pdo, string $title, string $type, string $audience, int $duration, string $description): int
{
    $number=nextScopedDocumentNumber($pdo,'training_course',setting('training_course_prefix','TRN'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('training_courses').' (course_number,course_title,course_type,audience,duration_minutes,status,description,created_by) VALUES (?,?,?,?,?,"active",?,?)')->execute([$number,$title,$type,$audience,$duration,$description,(int)($user['id']??0)?:null]);
    $courseId=(int)$pdo->lastInsertId();
    $lessons=['Overview and objective','Daily workflow practice','Questions, checklist and handover'];
    foreach($lessons as $i=>$lesson){$pdo->prepare('INSERT INTO '.table('training_lessons').' (training_course_id,lesson_title,lesson_type,lesson_content,duration_minutes,sort_order,status) VALUES (?,?,?,?,?,?,"active")')->execute([$courseId,$lesson,'lesson','Training content for '.$title.' - '.$lesson,(int)max(10,round($duration/3)),$i+1]);}
    return $courseId;
}

function p35CreateDemoCredentialsIfEmpty(PDO $pdo): int
{
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('demo_credentials'))>0){return 0;}
    $rows=[['Admin Portal','Admin','/admin/login.php','admin@example.com','Configured during install','Full admin access.'],['Customer Portal','Customer','/customer/login.php','demo.customer@example.com','Set manually','Customer portal demonstration.'],['Employee Portal','Employee','/employee/login.php','employee@example.com','Set manually','Employee ERP/self-service demonstration.']];
    $created=0;$user=currentUser();
    foreach($rows as $r){$number=nextScopedDocumentNumber($pdo,'demo_credential',setting('demo_credential_prefix','DEMOCR'),operationalScope($pdo));$pdo->prepare('INSERT INTO '.table('demo_credentials').' (credential_number,portal_name,role_label,login_url,username,password_hint,access_notes,status,created_by) VALUES (?,?,?,?,?,?,?,"active",?)')->execute([$number,$r[0],$r[1],$r[2],$r[3],$r[4],$r[5],(int)($user['id']??0)?:null]);$created++;}
    return $created;
}

function p35CreateOnboardingChecklist(PDO $pdo, string $client, string $package, string $goLive=''): int
{
    $number=nextScopedDocumentNumber($pdo,'client_onboarding',setting('client_onboarding_prefix','ONB'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('client_onboarding_checklists').' (onboarding_number,client_name,package_name,status,start_date,target_go_live_date,created_by) VALUES (?,?,?,"open",CURDATE(),?,?)')->execute([$number,$client,$package,$goLive?:null,(int)($user['id']??0)?:null]);
    $id=(int)$pdo->lastInsertId();
    $items=[['Discovery','Confirm business type, users and modules','Consultant'],['Setup','Install system and configure settings','Technical'],['Data','Import products, customers and opening data','Operations'],['Training','Train admin, sales, inventory and finance users','Trainer'],['Go Live','Final test, backup and production handover','Project Lead']];
    $stmt=$pdo->prepare('INSERT INTO '.table('client_onboarding_checklist_items').' (client_onboarding_checklist_id,phase,item_title,owner_role,status,sort_order) VALUES (?,?,?,?, "open", ?)');
    foreach($items as $i=>$it){$stmt->execute([$id,$it[0],$it[1],$it[2],$i+1]);}
    return $id;
}

function p35CreateFeatureComparisonItem(PDO $pdo, string $area, string $name, string $ours, string $comparison, string $value, int $sort=0): int
{
    $number=nextScopedDocumentNumber($pdo,'feature_comparison',setting('feature_comparison_prefix','FCMP'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('feature_comparison_items').' (item_number,feature_area,feature_name,our_system,sap_oracle_comparison,business_value,status,sort_order,created_by) VALUES (?,?,?,?,?,?,"active",?,?)')->execute([$number,$area,$name,$ours,$comparison,$value,$sort,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function p35CreateSalesBrochureSection(PDO $pdo, string $title, string $type, string $headline, string $body, string $cta, int $sort=0): int
{
    $number=nextScopedDocumentNumber($pdo,'sales_brochure',setting('sales_brochure_prefix','SBR'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('sales_brochure_sections').' (section_number,section_title,section_type,headline,body_text,cta_text,sort_order,status,created_by) VALUES (?,?,?,?,?,?,?,"active",?)')->execute([$number,$title,$type,$headline,$body,$cta,$sort,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function p35ReadinessCounts(PDO $pdo): array
{
    return [
        'docs'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('documentation_articles')),
        'training'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('training_courses')),
        'packages'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('commercial_packages')),
        'demo_credentials'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('demo_credentials')),
        'onboarding'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('client_onboarding_checklists')),
        'feature_comparison'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('feature_comparison_items')),
    ];
}


function p34TableExists(PDO $pdo, string $table): bool
{
    try{$stmt=$pdo->prepare('SHOW TABLES LIKE ?');$stmt->execute([DB_PREFIX.$table]);return (bool)$stmt->fetchColumn();}catch(Throwable $e){return false;}
}

function p34ColumnExists(PDO $pdo, string $table, string $column): bool
{
    try{$stmt=$pdo->prepare('SHOW COLUMNS FROM `'.DB_PREFIX.$table.'` LIKE ?');$stmt->execute([$column]);return (bool)$stmt->fetchColumn();}catch(Throwable $e){return false;}
}

function p34ExpectedCoreTables(): array
{
    return ['users'=>'Authentication users','settings'=>'System settings','erp_roles'=>'ERP roles and permissions','products'=>'Product catalog','categories'=>'Product categories','customers'=>'Customers','suppliers'=>'Suppliers','invoices'=>'Invoices','orders'=>'Orders','payments'=>'Payments','inventory_movements'=>'Inventory movements','document_sequences'=>'Document numbering','system_error_logs'=>'System error logs','system_health_checks'=>'System health checks','migration_history'=>'Migration history','production_repair_runs'=>'Production repair runs','production_schema_checks'=>'Production schema checks'];
}

function p34ExpectedCoreColumns(): array
{
    return ['users'=>['id','email','password','role','created_at'],'settings'=>['key_name','value'],'erp_roles'=>['id','name','slug','permissions','active'],'products'=>['id','name','slug','price','stock','active','created_at'],'customers'=>['id','customer_code','contact_name','email','status','created_at'],'invoices'=>['id','invoice_number','customer_name','total','balance_due','status','created_at'],'orders'=>['id','order_number','customer_name','total','status','created_at'],'document_sequences'=>['id','document_type','prefix','next_number','status'],'system_error_logs'=>['id','severity','message','status','created_at'],'production_repair_runs'=>['id','run_number','run_type','status','created_at']];
}

function p34ExpectedSettings(): array
{
    return ['production_repair_prefix'=>'PRUN','production_schema_check_prefix'=>'SCHK','production_backup_prefix'=>'PBACK','production_demo_batch_prefix'=>'DEMO','production_installer_event_prefix'=>'IEVT','production_release_checklist_prefix'=>'REL','production_mode_enabled'=>'0','upgrade_safe_mode_enabled'=>'1','backup_before_upgrade_enabled'=>'1','installer_rollback_enabled'=>'1','repair_center_enabled'=>'1','schema_checker_enabled'=>'1','demo_data_manager_enabled'=>'1','production_health_min_score'=>'85','system_error_logging_enabled'=>'1','health_disk_warning_mb'=>'512','backup_directory'=>'backups'];
}

function p34ProductionPermissions(): array
{
    return ['production_hardening_dashboard'=>'View production readiness dashboard and release hardening score','system_repair_center'=>'Run system repair scans, safe repairs and production checks','database_migration_updater'=>'Manage database migration updater, schema repair and upgrade records','installer_health_check'=>'Run installer health checks and review installation safety','demo_data_manager'=>'Install, review and reset demo data batches','permission_repair'=>'Repair missing ERP permissions and admin role access','settings_repair'=>'Repair missing ERP settings and production defaults','table_column_checker'=>'Check database tables and columns against expected ERP schema','production_error_log_viewer'=>'Review and resolve system PHP/ERP error logs'];
}

function p34LogInstallerEvent(PDO $pdo, string $type, string $message, string $severity='info', array $context=[]): int
{
    $number=nextScopedDocumentNumber($pdo,'production_installer_event',setting('production_installer_event_prefix','IEVT'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_installer_events').' (event_number,event_type,severity,status,message,context_json,created_by) VALUES (?,?,?,?,?,?,?)')->execute([$number,$type,$severity,'open',$message,json_encode($context,JSON_UNESCAPED_SLASHES),(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function p34CreateUpgradeBackup(PDO $pdo, string $type='pre_upgrade', string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'production_backup',setting('production_backup_prefix','PBACK'),operationalScope($pdo));
    $dir=ensureUploadDirectory(trim((string)setting('backup_directory','backups'),'/') ?: 'backups');
    $file='backup-'.$number.'-'.date('Ymd-His').'.txt';$path=$dir.'/'.$file;
    file_put_contents($path,"ERP backup placeholder\nNumber: ".$number."\nType: ".$type."\nCreated: ".date('c')."\nNotes: ".$notes."\n");
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_upgrade_backups').' (backup_number,backup_type,file_name,file_path,file_size,status,notes,created_by) VALUES (?,?,?,?,?,"created",?,?)')->execute([$number,$type,$file,'backups/'.$file,(int)@filesize($path),$notes,(int)($user['id']??0)?:null]);
    p34LogInstallerEvent($pdo,'backup_created','Production backup placeholder created: '.$number,'info',['file'=>$file]);
    return (int)$pdo->lastInsertId();
}

function p34RunSchemaCheck(PDO $pdo): array
{
    $number=nextScopedDocumentNumber($pdo,'production_schema_check',setting('production_schema_check_prefix','SCHK'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_schema_checks').' (check_number,check_type,status,started_by,checked_at,summary) VALUES (?,"full_schema","running",?,NOW(),"Running schema check")')->execute([$number,(int)($user['id']??0)?:null]);
    $checkId=(int)$pdo->lastInsertId();$checkedTables=0;$checkedColumns=0;$missingTables=0;$missingColumns=0;
    $item=$pdo->prepare('INSERT INTO '.table('production_schema_check_items').' (production_schema_check_id,table_name,column_name,item_type,expected_definition,exists_flag,status,repair_sql) VALUES (?,?,?,?,?,?,?,?)');
    foreach(p34ExpectedCoreTables() as $table=>$label){$exists=p34TableExists($pdo,$table);$checkedTables++;if(!$exists){$missingTables++;}$item->execute([$checkId,$table,null,'table',$label,$exists?1:0,$exists?'ok':'missing','']);}
    foreach(p34ExpectedCoreColumns() as $table=>$cols){foreach($cols as $col){$exists=p34TableExists($pdo,$table)&&p34ColumnExists($pdo,$table,$col);$checkedColumns++;if(!$exists){$missingColumns++;}$item->execute([$checkId,$table,$col,'column',$table.'.'.$col,$exists?1:0,$exists?'ok':'missing','']);}}
    $summary='Checked '.$checkedTables.' tables and '.$checkedColumns.' columns. Missing tables: '.$missingTables.'. Missing columns: '.$missingColumns.'.';
    $pdo->prepare('UPDATE '.table('production_schema_checks').' SET status="completed",tables_checked=?,columns_checked=?,missing_tables=?,missing_columns=?,summary=? WHERE id=?')->execute([$checkedTables,$checkedColumns,$missingTables,$missingColumns,$summary,$checkId]);
    return ['id'=>$checkId,'number'=>$number,'tables'=>$checkedTables,'columns'=>$checkedColumns,'missing_tables'=>$missingTables,'missing_columns'=>$missingColumns,'summary'=>$summary];
}

function p34RepairMissingSettings(PDO $pdo): int
{
    $created=0;foreach(p34ExpectedSettings() as $key=>$value){$stmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('settings').' WHERE key_name=?');$stmt->execute([$key]);if((int)$stmt->fetchColumn()===0){saveSetting($key,(string)$value);$created++;}}
    p34LogInstallerEvent($pdo,'settings_repair','Settings repair completed. Created '.$created.' missing setting(s).','info');return $created;
}

function p34RepairPermissions(PDO $pdo): int
{
    $added=0;$perms=p34ProductionPermissions();
    $stmt=$pdo->prepare('SELECT id,permissions FROM '.table('erp_roles').' WHERE slug="admin" OR name="Administrator" OR name="Admin" ORDER BY id LIMIT 1');$stmt->execute();$role=$stmt->fetch();
    if(!$role){return 0;}
    $current=json_decode((string)($role['permissions']??''),true);if(!is_array($current)){$current=[];}
    foreach($perms as $key=>$label){if(empty($current[$key])){$current[$key]=true;$added++;}}
    $pdo->prepare('UPDATE '.table('erp_roles').' SET permissions=? WHERE id=?')->execute([json_encode($current,JSON_UNESCAPED_SLASHES),(int)$role['id']]);
    p34LogInstallerEvent($pdo,'permission_repair','Permission repair completed. Added '.$added.' admin permission(s).','info');return $added;
}

function p34RunRepairScan(PDO $pdo, bool $repair=false): array
{
    $number=nextScopedDocumentNumber($pdo,'production_repair',setting('production_repair_prefix','PRUN'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_repair_runs').' (run_number,run_type,status,started_by,started_at,summary) VALUES (?,"production_scan","running",?,NOW(),"Running production scan")')->execute([$number,(int)($user['id']??0)?:null]);
    $runId=(int)$pdo->lastInsertId();$items=0;$issues=0;$repairs=0;
    $itemStmt=$pdo->prepare('INSERT INTO '.table('production_repair_items').' (production_repair_run_id,item_key,item_type,severity,status,description,recommendation,repair_action,repaired_at) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach(p34ExpectedCoreTables() as $table=>$label){$ok=p34TableExists($pdo,$table);$items++;if(!$ok){$issues++;}$itemStmt->execute([$runId,'table_'.$table,'table',$ok?'info':'critical',$ok?'ok':'open',$label.' table: '.$table,$ok?'No action required.':'Run installer health check or migration updater.','schema_check',null]);}
    foreach(p34ExpectedSettings() as $key=>$value){$stmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('settings').' WHERE key_name=?');$stmt->execute([$key]);$ok=(int)$stmt->fetchColumn()>0;$items++;if(!$ok){$issues++;}$itemStmt->execute([$runId,'setting_'.$key,'setting',$ok?'info':'warning',$ok?'ok':'open','Setting exists: '.$key,$ok?'No action required.':'Run settings repair.','settings_repair',null]);}
    if($repair && setting('repair_center_enabled','1')==='1'){$repairs+=p34RepairMissingSettings($pdo);$repairs+=p34RepairPermissions($pdo);p34RunSchemaCheck($pdo);}
    $summary='Checked '.$items.' items. Issues found: '.$issues.'. Repairs applied: '.$repairs.'.';
    $pdo->prepare('UPDATE '.table('production_repair_runs').' SET status="completed",items_checked=?,issues_found=?,repairs_applied=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$items,$issues,$repairs,$summary,$runId]);
    return ['id'=>$runId,'number'=>$number,'items'=>$items,'issues'=>$issues,'repairs'=>$repairs,'summary'=>$summary];
}

function p34InstallDemoData(PDO $pdo): int
{
    if(setting('demo_data_manager_enabled','1')!=='1'){throw new RuntimeException('Demo data manager is disabled.');}
    $number=nextScopedDocumentNumber($pdo,'production_demo_batch',setting('production_demo_batch_prefix','DEMO'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_demo_data_batches').' (batch_number,batch_name,batch_type,status,created_by,notes) VALUES (?,"Starter Demo Data","starter","running",?,"Installing starter records")')->execute([$number,(int)($user['id']??0)?:null]);
    $batchId=(int)$pdo->lastInsertId();$created=0;
    if(p34TableExists($pdo,'categories')){$slug='demo-garage-equipment';$exists=$pdo->prepare('SELECT id FROM '.table('categories').' WHERE slug=? LIMIT 1');$exists->execute([$slug]);if(!$exists->fetchColumn()){$pdo->prepare('INSERT INTO '.table('categories').' (name,slug,description,active) VALUES ("Demo Garage Equipment",?,"Demo category created by production demo manager.",1)')->execute([$slug]);$id=(int)$pdo->lastInsertId();$created++;$pdo->prepare('INSERT INTO '.table('production_demo_data_items').' (production_demo_data_batch_id,entity_type,entity_id,entity_label) VALUES (?,?,?,?)')->execute([$batchId,'category',$id,'Demo Garage Equipment']);}}
    if(p34TableExists($pdo,'products')){$slug='demo-two-post-lift';$exists=$pdo->prepare('SELECT id FROM '.table('products').' WHERE slug=? LIMIT 1');$exists->execute([$slug]);if(!$exists->fetchColumn()){$pdo->prepare('INSERT INTO '.table('products').' (name,slug,description,short_description,price,stock,sku,active,featured) VALUES ("Demo Two Post Lift",?,"Demo product for showroom and training.","Demo workshop lift product.",7500,5,"DEMO-LIFT-001",1,1)')->execute([$slug]);$id=(int)$pdo->lastInsertId();$created++;$pdo->prepare('INSERT INTO '.table('production_demo_data_items').' (production_demo_data_batch_id,entity_type,entity_id,entity_label) VALUES (?,?,?,?)')->execute([$batchId,'product',$id,'Demo Two Post Lift']);}}
    if(p34TableExists($pdo,'customers')){$code='DEMO-CUST-001';$exists=$pdo->prepare('SELECT id FROM '.table('customers').' WHERE customer_code=? LIMIT 1');$exists->execute([$code]);if(!$exists->fetchColumn()){$pdo->prepare('INSERT INTO '.table('customers').' (customer_code,customer_type,company_name,contact_name,email,phone,status) VALUES (?,"b2b","Demo Auto Workshop","Demo Customer","demo.customer@example.com","+971500000000","active")')->execute([$code]);$id=(int)$pdo->lastInsertId();$created++;$pdo->prepare('INSERT INTO '.table('production_demo_data_items').' (production_demo_data_batch_id,entity_type,entity_id,entity_label) VALUES (?,?,?,?)')->execute([$batchId,'customer',$id,'Demo Auto Workshop']);}}
    $pdo->prepare('UPDATE '.table('production_demo_data_batches').' SET status="completed",records_created=?,notes="Demo data installation completed." WHERE id=?')->execute([$created,$batchId]);
    return $created;
}


function p34DemoEntityTableMap(): array
{
    return [
        'category' => 'categories',
        'product' => 'products',
        'customer' => 'customers',
        'supplier' => 'suppliers',
        'quotation' => 'quotations',
        'sales_order' => 'sales_orders',
        'invoice' => 'invoices',
        'order' => 'orders',
        'download' => 'downloads',
        'blog_post' => 'blog_posts',
        'service' => 'services',
        'document' => 'document_library',
    ];
}

function p34RemoveKnownUntrackedDemoData(PDO $pdo): int
{
    $removed = 0;
    $safeDeletes = [
        ['categories', 'slug', ['demo-garage-equipment','demo-diagnostic-tools','demo-software']],
        ['products', 'slug', ['demo-two-post-lift','demo-diagnostic-scanner','demo-erp-product']],
        ['customers', 'customer_code', ['DEMO-CUST-001']],
        ['customers', 'email', ['demo.customer@example.com']],
        ['downloads', 'file_name', ['demo-manual.pdf','demo-download.pdf']],
        ['blog_posts', 'slug', ['demo-blog-post','demo-product-guide']],
        ['services', 'slug', ['demo-service','demo-installation-service']],
    ];
    foreach ($safeDeletes as [$table, $column, $values]) {
        if (!p34TableExists($pdo, $table)) {
            continue;
        }
        foreach ($values as $value) {
            $stmt = $pdo->prepare('DELETE FROM ' . table($table) . ' WHERE `' . str_replace('`','',$column) . '` = ?');
            $stmt->execute([$value]);
            $removed += $stmt->rowCount();
        }
    }
    return $removed;
}


function p56WhiteLabelContentRows(): array
{
    $json = <<<'WHITE_LABEL_CONTENT_JSON'
{"categories": [["BMW Diagnostic Software", "bmw-diagnostic-software", "BMW ISTA, coding, programming and installation packages.", 1], ["Mercedes-Benz Diagnostic Software", "mercedes-diagnostic-software", "XENTRY, DAS and coding-related setup packages.", 2], ["OEM Diagnostic Software", "oem-diagnostic-software", "Multi-brand OEM diagnostic software packages for workshops.", 3], ["Remote Installation", "remote-installation", "Remote installation, activation and setup support services.", 4], ["Downloads & Guides", "downloads-guides", "Installation guides, checklists and downloadable customer resources.", 5]], "products": [["BMW ISTA+ Diagnostic & Programming Package", "bmw-ista-diagnostic-programming-package", "bmw-diagnostic-software", "BMW diagnostic software package for professional workshops that need guided diagnostics, service workflow support, coding preparation and remote installation assistance.", "BMW ISTA-style diagnostic and programming workflow package with remote setup support.", 499, 699, 999, "PROD-BMW-ISTA", "Popular", "Remote Setup Included", "Platform: BMW / MINI / Rolls-Royce\nUse: diagnostics, programming support and service workflow\nDelivery: digital setup + remote support", 1, "bmw-ista-installation-guide.pdf"], ["Mercedes-Benz XENTRY / DAS Diagnostic Package", "mercedes-xentry-das-diagnostic-package", "mercedes-diagnostic-software", "Mercedes-Benz diagnostic software package for workshop-level service, troubleshooting, guided tests and remote installation support.", "Mercedes XENTRY/DAS diagnostic workflow package for professional service teams.", 599, 799, 999, "PROD-MB-XENTRY", "Workshop Pick", "Remote Setup Included", "Platform: Mercedes-Benz\nUse: diagnostics and XENTRY/DAS workflow support\nDelivery: digital setup + remote support", 1, "mercedes-xentry-setup-checklist.pdf"], ["VAG ODIS Service & Engineering Package", "vag-odis-service-engineering-package", "oem-diagnostic-software", "VAG diagnostic software package for Volkswagen, Audi, Skoda and Seat service workflows, installation support and technician onboarding.", "ODIS Service and Engineering workflow package for VAG workshops.", 449, 649, 999, "PROD-VAG-ODIS", "OEM Workflow", "Remote Setup Included", "Platform: Volkswagen / Audi / Skoda / Seat\nUse: service diagnostics and engineering workflow\nDelivery: digital setup + remote support", 1, "vag-odis-installation-guide.pdf"], ["Toyota Techstream Diagnostic Package", "toyota-techstream-diagnostic-package", "oem-diagnostic-software", "Toyota and Lexus diagnostic software package for service functions, system scans, utility workflows and remote setup support.", "Toyota Techstream-style diagnostic workflow package with setup guidance.", 299, 449, 999, "PROD-TOY-TECH", "Digital Delivery", "Remote Setup Included", "Platform: Toyota / Lexus\nUse: diagnostics and service functions\nDelivery: digital setup + remote support", 0, "toyota-techstream-quick-start.pdf"], ["JLR SDD / Pathfinder Diagnostic Package", "jlr-sdd-pathfinder-diagnostic-package", "oem-diagnostic-software", "Jaguar Land Rover diagnostic software package for SDD and Pathfinder-style workflows, installation guidance and support.", "JLR diagnostic workflow package for modern service support.", 499, 699, 999, "PROD-JLR-SDD", "Specialist", "Remote Setup Included", "Platform: Jaguar / Land Rover\nUse: SDD and Pathfinder diagnostic workflow\nDelivery: digital setup + remote support", 0, "jlr-sdd-pathfinder-guide.pdf"], ["Porsche PIWIS Diagnostic Package", "porsche-piwis-diagnostic-package", "oem-diagnostic-software", "Porsche diagnostic software workflow package for specialist technicians, workshop setup and diagnostic process support.", "Porsche PIWIS-style diagnostic workflow package.", 699, 899, 999, "PROD-POR-PIWIS", "Premium", "Remote Setup Included", "Platform: Porsche\nUse: diagnostic and service workflow support\nDelivery: digital setup + remote support", 0, "porsche-piwis-setup-guide.pdf"], ["GM Techline Connect / GDS2 Package", "gm-techline-connect-gds2-package", "oem-diagnostic-software", "GM diagnostic software workflow package for Chevrolet, Cadillac and GM service support with remote installation guidance.", "GM Techline Connect/GDS2 workflow package for service teams.", 399, 549, 999, "PROD-GM-TLC", "Digital Setup", "Remote Setup Included", "Platform: GM / Chevrolet / Cadillac\nUse: Techline Connect and GDS2 workflow support\nDelivery: digital setup + remote support", 0, "gm-techline-gds2-guide.pdf"], ["Ford IDS / FDRS Diagnostic Package", "ford-ids-fdrs-diagnostic-package", "oem-diagnostic-software", "Ford diagnostic software workflow package for IDS and FDRS-style service support, remote setup and workshop onboarding.", "Ford IDS/FDRS diagnostic workflow package.", 399, 549, 999, "PROD-FORD-IDS", "Digital Setup", "Remote Setup Included", "Platform: Ford / Lincoln\nUse: IDS/FDRS diagnostic workflow support\nDelivery: digital setup + remote support", 0, "ford-ids-fdrs-guide.pdf"], ["Remote Installation Support Session", "remote-installation-support-session", "remote-installation", "Book a remote installation support session for diagnostic software setup, compatibility checks, driver configuration and basic onboarding.", "Remote setup session for diagnostic software customers.", 149, 199, 999, "PROD-REMOTE-SETUP", "Service", "Scheduled Support", "Service: remote setup\nUse: installation, configuration and basic onboarding\nDelivery: scheduled remote session", 1, ""], ["Automotive Software Starter Guide Pack", "automotive-software-starter-guide-pack", "downloads-guides", "Downloadable guide pack covering diagnostic software installation preparation, customer checklist, remote support notes and common setup questions.", "Downloadable guide pack for software buyers and support teams.", 49, 99, 999, "PROD-GUIDE-PACK", "Instant Download", "Digital Resource", "Resource: downloadable guide pack\nUse: installation checklist and troubleshooting notes\nDelivery: customer downloads", 1, "automotive-software-starter-guide.pdf"]], "downloads": [["BMW ISTA Installation Guide", "Upload the BMW ISTA installation guide into uploads/downloads/ to activate this customer file.", "bmw-ista-installation-guide.pdf", "1.8 MB"], ["Mercedes XENTRY Setup Checklist", "Upload the Mercedes XENTRY setup checklist into uploads/downloads/.", "mercedes-xentry-setup-checklist.pdf", "1.5 MB"], ["VAG ODIS Installation Guide", "Upload the VAG ODIS guide into uploads/downloads/.", "vag-odis-installation-guide.pdf", "1.4 MB"], ["Toyota Techstream Quick Start", "Upload the Techstream quick-start guide into uploads/downloads/.", "toyota-techstream-quick-start.pdf", "900 KB"], ["JLR SDD Pathfinder Guide", "Upload the JLR guide into uploads/downloads/.", "jlr-sdd-pathfinder-guide.pdf", "1.2 MB"], ["Automotive Software Starter Guide", "Upload the starter guide pack into uploads/downloads/.", "automotive-software-starter-guide.pdf", "1.0 MB"]], "blog_posts": [["Automotive Diagnostic Software Buying Guide", "automotive-diagnostic-software-buying-guide", "<h2>How to choose diagnostic software</h2><p>Professional workshops should choose diagnostic software based on vehicle coverage, installation support, update workflow, licence delivery, interface compatibility and after-sales guidance.</p><p>A clear product page should explain supported brands, remote installation process, required laptop specifications and what the customer receives after purchase.</p>", "A practical buying guide for workshops choosing automotive diagnostic software."], ["Why Remote Installation Support Matters", "why-remote-installation-support-matters", "<h2>Remote setup reduces customer friction</h2><p>Diagnostic software buyers often need help with drivers, VCI connection, Windows settings, account activation and first launch checks. A remote installation service makes the buying process easier and reduces support tickets.</p>", "Why remote installation support increases customer confidence."], ["BMW ISTA Workshop Setup Checklist", "bmw-ista-workshop-setup-checklist", "<h2>Prepare before installing BMW diagnostic software</h2><p>Before installation, confirm Windows compatibility, storage space, network stability, interface type and customer expectations for diagnostics, programming and coding workflows.</p>", "Checklist-style blog for BMW ISTA setup preparation."], ["Mercedes XENTRY / DAS Support Workflow", "mercedes-xentry-das-support-workflow", "<h2>Build a clean Mercedes support workflow</h2><p>Mercedes diagnostic software sales should include installation steps, customer onboarding, common troubleshooting notes and a clear support boundary for workshop users.</p>", "Mercedes diagnostic software support workflow overview."], ["How to Sell Diagnostic Software Online", "how-to-sell-diagnostic-software-online", "<h2>Digital products need trust signals</h2><p>For software products, strong ecommerce pages should include delivery type, remote installation option, supported use case, requirements, refund policy and customer download area.</p>", "SEO blog for selling diagnostic software online."], ["Customer Downloads for Software Products", "customer-downloads-for-software-products", "<h2>Use downloads to improve after-sales support</h2><p>Linking software products with downloadable guides, setup checklists and quick-start PDFs helps customers find resources after purchase from their account dashboard.</p>", "How customer downloads improve software product support."]]}
WHITE_LABEL_CONTENT_JSON;
    $rows = json_decode($json, true);
    return is_array($rows) ? $rows : [];
}

function p56InstallWhiteLabelWebsiteContent(PDO $pdo): int
{
    $rows = p56WhiteLabelContentRows();
    $created = 0;
    $categoryIds = [];
    foreach (($rows['categories'] ?? []) as $cat) {
        $stmt = $pdo->prepare('SELECT id FROM ' . table('categories') . ' WHERE slug=? LIMIT 1');
        $stmt->execute([$cat[1]]);
        $id = (int)$stmt->fetchColumn();
        if (!$id) {
            $pdo->prepare('INSERT INTO ' . table('categories') . ' (name,slug,description,sort_order) VALUES (?,?,?,?)')->execute($cat);
            $id = (int)$pdo->lastInsertId();
            $created++;
        }
        $categoryIds[$cat[1]] = $id;
    }
    $companyId = (int)setting('default_company_id',0) ?: (int)safeAiScalar($pdo,'SELECT id FROM '.table('companies').' ORDER BY id LIMIT 1');
    $branchId = (int)setting('default_branch_id',0) ?: (int)safeAiScalar($pdo,'SELECT id FROM '.table('branches').' ORDER BY id LIMIT 1');
    $warehouseId = (int)setting('default_warehouse_id',0) ?: (int)safeAiScalar($pdo,'SELECT id FROM '.table('warehouses').' ORDER BY id LIMIT 1');
    $locationId = (int)setting('default_location_id',0) ?: (int)safeAiScalar($pdo,'SELECT id FROM '.table('warehouse_locations').' ORDER BY id LIMIT 1');
    foreach (($rows['products'] ?? []) as $p) {
        $stmt = $pdo->prepare('SELECT id FROM ' . table('products') . ' WHERE slug=? LIMIT 1');
        $stmt->execute([$p[1]]);
        if ($stmt->fetchColumn()) { continue; }
        $categoryId = $categoryIds[$p[2]] ?? null;
        $downloadable = trim((string)($p[13] ?? '')) !== '' ? 1 : 0;
        $pdo->prepare('INSERT INTO ' . table('products') . ' (category_id,name,slug,description,short_description,price,compare_price,stock,sku,image,gallery,badge,warranty,specifications,featured,active,downloadable,download_file) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$categoryId,$p[0],$p[1],$p[3],$p[4],$p[5],$p[6],$p[7],$p[8],'','',$p[9],$p[10],$p[11],(int)$p[12],1,$downloadable,$p[13]]);
        $productId = (int)$pdo->lastInsertId();
        $created++;
        if (p34TableExists($pdo,'inventory')) {
            $pdo->prepare('INSERT INTO ' . table('inventory') . ' (product_id,sku,quantity,reorder_level,location) VALUES (?,?,?,?,?)')->execute([$productId,$p[8],$p[7],5,$downloadable?'Digital':'Main Store']);
        }
        if (p34TableExists($pdo,'warehouse_stock') && $companyId && $branchId && $warehouseId) {
            $unitCost = round(((float)$p[5]) * 0.60, 2);
            $stockValue = round(((float)$p[7]) * $unitCost, 2);
            $pdo->prepare('INSERT INTO ' . table('warehouse_stock') . ' (product_id,company_id,branch_id,warehouse_id,location_id,quantity,reserved_quantity,reorder_level,average_unit_cost,stock_value) VALUES (?,?,?,?,?,?,0,?,?,?)')->execute([$productId,$companyId,$branchId,$warehouseId,$locationId ?: null,$p[7],5,$unitCost,$stockValue]);
        }
    }
    foreach (($rows['downloads'] ?? []) as $d) {
        $stmt = $pdo->prepare('SELECT id FROM ' . table('downloads') . ' WHERE file_name=? LIMIT 1');
        $stmt->execute([$d[2]]);
        if (!$stmt->fetchColumn()) {
            $pdo->prepare('INSERT INTO ' . table('downloads') . ' (title,description,file_name,file_size,active) VALUES (?,?,?,?,1)')->execute($d);
            $created++;
        }
    }
    foreach (($rows['blog_posts'] ?? []) as $b) {
        $stmt = $pdo->prepare('SELECT id FROM ' . table('blog_posts') . ' WHERE slug=? LIMIT 1');
        $stmt->execute([$b[1]]);
        if (!$stmt->fetchColumn()) { /* checked in next loop */ }
    }
    foreach (($rows['blog_posts'] ?? []) as $b) {
        $stmt = $pdo->prepare('SELECT id FROM ' . table('blog_posts') . ' WHERE slug=? LIMIT 1');
        $stmt->execute([$b[1]]);
        if (!$stmt->fetchColumn()) {
            $pdo->prepare('INSERT INTO ' . table('blog_posts') . ' (title,slug,content,excerpt,status) VALUES (?,?,?,?,?)')->execute([$b[0],$b[1],$b[2],$b[3],'published']);
            $created++;
        }
    }
    return $created;
}

function p34RemoveDemoData(PDO $pdo, int $batchId = 0, bool $includeKnownUntracked = true): array
{
    if (setting('demo_data_manager_enabled','1') !== '1') {
        throw new RuntimeException('Demo data manager is disabled.');
    }

    $map = p34DemoEntityTableMap();
    $where = $batchId > 0 ? ' WHERE production_demo_data_batch_id = ? AND status <> "removed"' : ' WHERE status <> "removed"';
    $params = $batchId > 0 ? [$batchId] : [];

    $stmt = $pdo->prepare('SELECT * FROM ' . table('production_demo_data_items') . $where . ' ORDER BY id DESC');
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    $deleted = 0;
    $skipped = 0;
    $errors = 0;
    $knownRemoved = 0;

    $pdo->beginTransaction();
    try {
        foreach ($items as $item) {
            $entityType = (string)($item['entity_type'] ?? '');
            $entityId = (int)($item['entity_id'] ?? 0);
            if ($entityId <= 0 || !isset($map[$entityType]) || !p34TableExists($pdo, $map[$entityType])) {
                $skipped++;
                $pdo->prepare('UPDATE ' . table('production_demo_data_items') . ' SET status="skipped" WHERE id=?')->execute([(int)$item['id']]);
                continue;
            }

            try {
                $delete = $pdo->prepare('DELETE FROM ' . table($map[$entityType]) . ' WHERE id=? LIMIT 1');
                $delete->execute([$entityId]);
                $deleted += $delete->rowCount();
                $pdo->prepare('UPDATE ' . table('production_demo_data_items') . ' SET status="removed" WHERE id=?')->execute([(int)$item['id']]);
            } catch (Throwable $e) {
                $errors++;
                $pdo->prepare('UPDATE ' . table('production_demo_data_items') . ' SET status="error" WHERE id=?')->execute([(int)$item['id']]);
                recordSystemError($pdo, $e, ['function'=>'p34RemoveDemoData','entity_type'=>$entityType,'entity_id'=>$entityId]);
            }
        }

        if ($includeKnownUntracked) {
            $knownRemoved = p34RemoveKnownUntrackedDemoData($pdo);
            $deleted += $knownRemoved;
        }

        if ($batchId > 0) {
            $pdo->prepare('UPDATE ' . table('production_demo_data_batches') . ' SET status="removed", notes=CONCAT(COALESCE(notes,""), "\nRemoved demo data after installation.") WHERE id=?')->execute([$batchId]);
        } else {
            $pdo->prepare('UPDATE ' . table('production_demo_data_batches') . ' SET status="removed", notes=CONCAT(COALESCE(notes,""), "\nAll tracked demo data removed after installation.") WHERE status <> "removed"')->execute();
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    return [
        'tracked_items' => count($items),
        'deleted' => $deleted,
        'known_untracked_removed' => $knownRemoved,
        'skipped' => $skipped,
        'errors' => $errors,
    ];
}

function p34InstallerHealthSummary(PDO $pdo): array
{
    $backupDir=ensureUploadDirectory(trim((string)setting('backup_directory','backups'),'/') ?: 'backups');
    return ['php_version'=>PHP_VERSION,'pdo_mysql'=>extension_loaded('pdo_mysql')?'ok':'missing','uploads_writable'=>is_writable(localUploadRoot())?'ok':'fail','backup_dir_writable'=>is_writable($backupDir)?'ok':'fail','service_worker_file'=>file_exists(dirname(__DIR__).'/service-worker.js')?'ok':'missing','manifest_file'=>file_exists(dirname(__DIR__).'/manifest.webmanifest')?'ok':'missing','error_log_table'=>p34TableExists($pdo,'system_error_logs')?'ok':'missing','repair_tables'=>p34TableExists($pdo,'production_repair_runs')?'ok':'missing'];
}

function p34CreateReleaseChecklist(PDO $pdo, string $releaseName='Production Release', string $version='1.0'): int
{
    $number=nextScopedDocumentNumber($pdo,'production_release_checklist',setting('production_release_checklist_prefix','REL'),operationalScope($pdo));$user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_release_checklists').' (checklist_number,release_name,release_version,status,created_by) VALUES (?,?,?,"open",?)')->execute([$number,$releaseName,$version,(int)($user['id']??0)?:null]);
    $id=(int)$pdo->lastInsertId();
    $items=[['backup_before_upgrade','Create backup before upgrade','Safety','critical','Create or verify pre-upgrade backup before deploying.'],['schema_check','Run schema table/column check','Database','critical','Confirm no missing core table or column.'],['permission_repair','Repair permissions','Access','high','Ensure admin role has production and repair permissions.'],['settings_repair','Repair settings','Config','high','Ensure production settings are present.'],['error_logs','Review open errors','Monitoring','high','Resolve open system errors before release.'],['mobile_check','Check mobile/PWA pages','Frontend','medium','Open mobile shell and PWA readiness dashboard.'],['store_check','Check storefront pages','Frontend','medium','Open store, quote, wishlist and compare pages.'],['demo_flow','Test demo sales flow','QA','high','Create product/customer/quote/invoice flow with test data.']];
    $stmt=$pdo->prepare('INSERT INTO '.table('production_release_checklist_items').' (production_release_checklist_id,item_key,title,category,severity,status,recommendation) VALUES (?,?,?,?,?,"open",?)');
    foreach($items as $i){$stmt->execute([$id,$i[0],$i[1],$i[2],$i[3],$i[4]]);}
    return $id;
}


function runSystemHealthChecks(PDO $pdo): array
{
    $checks = [];
    $uploadRoot = localUploadRoot();
    $backupDir = ensureUploadDirectory(trim((string)setting('backup_directory','backups'), '/') ?: 'backups');
    $diskFree = @disk_free_space(dirname(__DIR__));
    $diskMb = $diskFree !== false ? round($diskFree / 1048576, 2) : 0;
    $diskWarning = max(1, (float)setting('health_disk_warning_mb','512'));

    $checks[] = ['php_version','PHP Version', version_compare(PHP_VERSION, '8.0.0', '>=') ? 'ok' : 'fail', PHP_VERSION, 'Use PHP 8.0+; PHP 8.2+ is preferred for production.'];
    $checks[] = ['pdo_mysql','PDO MySQL Extension', extension_loaded('pdo_mysql') ? 'ok' : 'fail', extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing', 'Enable the pdo_mysql extension.'];
    $checks[] = ['uploads_writable','Uploads Directory', is_writable($uploadRoot) ? 'ok' : 'fail', $uploadRoot, 'Make uploads writable by the web server user.'];
    $checks[] = ['backup_writable','Backup Directory', is_writable($backupDir) ? 'ok' : 'fail', $backupDir, 'Make the backup directory writable and protect it from public listing.'];
    $checks[] = ['disk_space','Free Disk Space', $diskMb >= $diskWarning ? 'ok' : 'warning', $diskMb . ' MB', 'Free disk space is below the configured warning threshold.'];
    $checks[] = ['https','HTTPS / SSL', str_starts_with((string)SHOP_URL, 'https://') ? 'ok' : 'warning', SHOP_URL, 'Use HTTPS in production.'];
    $checks[] = ['display_errors','Display Errors', ini_get('display_errors') ? 'warning' : 'ok', ini_get('display_errors') ? 'Enabled' : 'Disabled', 'Keep display_errors disabled in production.'];

    try {
        $tableCount = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
        $checks[] = ['database_tables','Database Tables','ok',(string)$tableCount,'Database is reachable and tables are visible.'];
    } catch (Throwable $e) {
        $checks[] = ['database_tables','Database Tables','fail',$e->getMessage(),'Check database credentials and privileges.'];
    }

    $pdo->exec('DELETE FROM ' . table('system_health_checks'));
    $stmt = $pdo->prepare('INSERT INTO ' . table('system_health_checks') . ' (check_key,check_label,status,value_text,recommendation) VALUES (?,?,?,?,?)');
    foreach ($checks as $check) {
        $stmt->execute($check);
    }
    return $checks;
}

function sqlValue(PDO $pdo, $value): string
{
    if ($value === null) {
        return 'NULL';
    }
    return $pdo->quote((string)$value);
}

function createDatabaseBackup(PDO $pdo, string $notes = ''): array
{
    $backupDirName = trim((string)setting('backup_directory','backups'), '/') ?: 'backups';
    $dir = ensureUploadDirectory($backupDirName);
    $backupNumber = 'BKP-' . date('Ymd-His') . '-' . random_int(100,999);
    $fileName = $backupNumber . '.sql';
    $filePath = $dir . '/' . $fileName;

    $fh = fopen($filePath, 'wb');
    if (!$fh) {
        throw new RuntimeException('Unable to create backup file.');
    }

    fwrite($fh, "-- E-commerce ERP Commerce Suite database backup\n");
    fwrite($fh, "-- Created: " . date('c') . "\n");
    fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    $tables = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll(PDO::FETCH_NUM);
    foreach ($tables as $row) {
        $tableName = (string)$row[0];
        $createStmt = $pdo->query('SHOW CREATE TABLE `' . str_replace('`','``',$tableName) . '`')->fetch(PDO::FETCH_ASSOC);
        $createSql = $createStmt['Create Table'] ?? array_values($createStmt)[1] ?? '';
        fwrite($fh, "DROP TABLE IF EXISTS `" . str_replace('`','``',$tableName) . "`;\n");
        fwrite($fh, $createSql . ";\n\n");
        $dataStmt = $pdo->query('SELECT * FROM `' . str_replace('`','``',$tableName) . '`');
        while ($record = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_map(static fn($col) => '`' . str_replace('`','``',$col) . '`', array_keys($record));
            $values = array_map(fn($value) => sqlValue($pdo, $value), array_values($record));
            fwrite($fh, 'INSERT INTO `' . str_replace('`','``',$tableName) . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
        }
        fwrite($fh, "\n");
    }
    fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fh);

    $user = currentUser();
    $stmt = $pdo->prepare('INSERT INTO ' . table('backup_jobs') . ' (backup_number,backup_type,file_name,file_path,file_size,status,created_by,notes) VALUES (?,?,?,?,?,"created",?,?)');
    $stmt->execute([$backupNumber,'database',$fileName,$backupDirName . '/' . $fileName,(int)filesize($filePath),(int)($user['id'] ?? 0) ?: null,$notes]);
    logActivity($pdo,'System','backup_created','Database backup '.$backupNumber.' created.','backup_job',(int)$pdo->lastInsertId());
    return ['backup_number'=>$backupNumber,'file_name'=>$fileName,'file_path'=>$filePath,'relative_path'=>$backupDirName . '/' . $fileName];
}

function runAutomationCycle(PDO $pdo, string $runKey = 'manual'): array
{
    $user = currentUser();
    $stmt = $pdo->prepare('INSERT INTO ' . table('cron_runs') . ' (run_key,started_at,status,created_by) VALUES (?,NOW(),"running",?)');
    $stmt->execute([$runKey,(int)($user['id'] ?? 0) ?: null]);
    $runId = (int)$pdo->lastInsertId();
    $created = 0;
    $summary = [];

    try {
        if (setting('notification_auto_generate_enabled','1') === '1') {
            $lowStock = $pdo->query('SELECT p.name,p.sku,p.stock FROM ' . table('products') . ' p WHERE p.active=1 AND p.stock<=3 ORDER BY p.stock ASC LIMIT 20')->fetchAll();
            foreach ($lowStock as $product) {
                createNotification($pdo, [
                    'role_slug'=>'inventory-procurement',
                    'title'=>'Low stock alert',
                    'message'=>'Product '.($product['sku'] ?: '').' '.$product['name'].' is at stock level '.$product['stock'].'.',
                    'severity'=>'warning',
                    'link_url'=>ADMIN_URL.'/products.php',
                ]);
                $created++;
            }
            $summary[] = count($lowStock).' low-stock alerts checked';

            $oldApprovals = $pdo->query('SELECT request_number,document_type,submitted_at FROM ' . table('approval_requests') . ' WHERE status="pending" AND submitted_at < DATE_SUB(NOW(), INTERVAL 2 DAY) ORDER BY submitted_at ASC LIMIT 20')->fetchAll();
            foreach ($oldApprovals as $approval) {
                createNotification($pdo, [
                    'role_slug'=>'erp-manager',
                    'title'=>'Approval aging alert',
                    'message'=>'Approval request '.$approval['request_number'].' has been pending since '.$approval['submitted_at'].'.',
                    'severity'=>'danger',
                    'link_url'=>ADMIN_URL.'/erp/approvals.php',
                ]);
                $created++;
            }
            $summary[] = count($oldApprovals).' aging approvals checked';
        }

        $summary[] = $created.' notifications generated';
        $pdo->prepare('UPDATE ' . table('cron_runs') . ' SET status="success",finished_at=NOW(),summary=? WHERE id=?')->execute([implode('; ', $summary),$runId]);
        return ['status'=>'success','summary'=>implode('; ', $summary),'created'=>$created,'run_id'=>$runId];
    } catch (Throwable $e) {
        recordSystemError($pdo,$e,['run_key'=>$runKey]);
        $pdo->prepare('UPDATE ' . table('cron_runs') . ' SET status="failed",finished_at=NOW(),summary=? WHERE id=?')->execute([$e->getMessage(),$runId]);
        throw $e;
    }
}


function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = getDB()->prepare('SELECT * FROM ' . table('users') . ' WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function userGuard(): void
{
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/user/login.php');
    }
}


function createNotification(PDO $pdo, array $data): int
{
    $stmt=$pdo->prepare('INSERT INTO '.table('notifications').' (company_id,branch_id,user_id,role_slug,title,message,severity,link_url) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([
        (int)($data['company_id']??0)?:null,
        (int)($data['branch_id']??0)?:null,
        (int)($data['user_id']??0)?:null,
        trim((string)($data['role_slug']??''))?:null,
        trim((string)($data['title']??'Notification')),
        trim((string)($data['message']??'')),
        trim((string)($data['severity']??'info'))?:'info',
        trim((string)($data['link_url']??''))?:null,
    ]);
    return (int)$pdo->lastInsertId();
}

function unreadNotificationCount(PDO $pdo, ?array $user = null): int
{
    $user=$user ?: currentUser();
    if(!$user){return 0;}
    if(($user['role']??'')==='admin'){
        return (int)$pdo->query('SELECT COUNT(*) FROM '.table('notifications').' WHERE is_read=0')->fetchColumn();
    }
    $roleSlug=currentErpRoleSlug($pdo,$user);
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('notifications').' WHERE is_read=0 AND (user_id=? OR (user_id IS NULL AND role_slug=?) OR (user_id IS NULL AND role_slug IS NULL))');
    $stmt->execute([(int)$user['id'],$roleSlug]);
    return (int)$stmt->fetchColumn();
}


function kpiMetricValue(PDO $pdo, string $sourceKey): float
{
    if ($sourceKey === 'revenue_mtd') {return (float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM '.table('invoices').' WHERE status NOT IN ("cancelled","draft") AND DATE(created_at)>=DATE_FORMAT(CURDATE(), "%Y-%m-01")')->fetchColumn();}
    if ($sourceKey === 'orders_mtd') {return (float)$pdo->query('SELECT COUNT(*) FROM '.table('orders').' WHERE DATE(created_at)>=DATE_FORMAT(CURDATE(), "%Y-%m-01")')->fetchColumn();}
    if ($sourceKey === 'open_ar') {return (float)$pdo->query('SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE status NOT IN ("paid","cancelled")')->fetchColumn();}
    if ($sourceKey === 'low_stock') {return (float)$pdo->query('SELECT COUNT(*) FROM '.table('products').' WHERE active=1 AND stock<=3')->fetchColumn();}
    if ($sourceKey === 'open_approvals') {return (float)$pdo->query('SELECT COUNT(*) FROM '.table('approval_requests').' WHERE status IN ("pending","pending_approval")')->fetchColumn();}
    if ($sourceKey === 'open_pipeline') {return (float)$pdo->query('SELECT COALESCE(SUM(weighted_value),0) FROM '.table('sales_opportunities').' WHERE status="open"')->fetchColumn();}
    if ($sourceKey === 'payroll_exposure') {return (float)$pdo->query('SELECT COALESCE(SUM(net_total),0) FROM '.table('payroll_runs').' WHERE status IN ("draft","approved")')->fetchColumn();}
    return 0.0;
}

function snapshotAllKpis(PDO $pdo, ?string $date = null): array
{
    $date=$date ?: date('Y-m-d');
    $kpis=$pdo->query('SELECT * FROM '.table('report_kpis').' WHERE active=1 ORDER BY sort_order,kpi_name')->fetchAll();
    $stmt=$pdo->prepare('INSERT INTO '.table('kpi_snapshots').' (report_kpi_id,snapshot_date,metric_value,target_value,status,notes) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE metric_value=VALUES(metric_value),target_value=VALUES(target_value),status=VALUES(status),notes=VALUES(notes)');
    $count=0;
    foreach($kpis as $kpi){
        $value=kpiMetricValue($pdo,(string)$kpi['metric_source']);
        $target=(float)$kpi['target_value'];
        $status=$target>0 ? ($value >= $target ? 'above_target' : 'below_target') : 'ok';
        $stmt->execute([(int)$kpi['id'],$date,$value,$target,$status,'Snapshot generated by KPI engine.']);
        $count++;
    }
    return ['count'=>$count,'date'=>$date];
}

function createReportExportRecord(PDO $pdo, ?int $savedReportId, string $reportType, string $format, string $fileName, int $rowCount): int
{
    $number=nextScopedDocumentNumber($pdo,'report_export',setting('report_export_prefix','EXP'),operationalScope($pdo));
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('report_export_files').' (saved_report_id,report_type,export_number,format,file_name,row_count,created_by) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$savedReportId,$reportType,$number,$format,$fileName,$rowCount,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function runReportSchedule(PDO $pdo, int $scheduleId): array
{
    $stmt=$pdo->prepare('SELECT s.*,r.report_type,r.report_name FROM '.table('report_schedules').' s LEFT JOIN '.table('saved_reports').' r ON r.id=s.saved_report_id WHERE s.id=? LIMIT 1');
    $stmt->execute([$scheduleId]);
    $schedule=$stmt->fetch();
    if(!$schedule){throw new RuntimeException('Report schedule not found.');}
    $rows=reportRows($pdo,(string)$schedule['report_type'],['limit'=>(int)setting('report_export_max_rows','5000')]);
    $runNumber=nextScopedDocumentNumber($pdo,'report_schedule_run','RSR',operationalScope($pdo));
    $fileName='scheduled-report-'.$schedule['schedule_code'].'-'.date('Ymd-His').'.csv';
    $run=$pdo->prepare('INSERT INTO '.table('report_schedule_runs').' (report_schedule_id,run_number,status,row_count,export_path,started_at,finished_at,notes) VALUES (?,?, "completed",?,?,NOW(),NOW(),?)');
    $run->execute([$scheduleId,$runNumber,count($rows),$fileName,'Manual schedule run completed.']);
    $pdo->prepare('UPDATE '.table('report_schedules').' SET last_run_at=NOW(),next_run_at=CASE frequency WHEN "daily" THEN DATE_ADD(NOW(), INTERVAL 1 DAY) WHEN "monthly" THEN DATE_ADD(NOW(), INTERVAL 1 MONTH) ELSE DATE_ADD(NOW(), INTERVAL 1 WEEK) END WHERE id=?')->execute([$scheduleId]);
    return ['run_number'=>$runNumber,'rows'=>count($rows),'file_name'=>$fileName];
}


function reportTypeOptions(): array
{
    return [
        'sales_pipeline'=>'Sales Pipeline & Order Performance',
        'inventory_value'=>'Inventory Valuation & Low Stock',
        'ap_matching'=>'Supplier Invoice Match Variance',
        'project_margin'=>'Project Margin & Budget Usage',
        'service_profit'=>'Service Job Card Profitability',
        'credit_exposure'=>'Customer Credit Exposure',
        'approval_sla'=>'Approval Queue & Governance',
        'budget_variance'=>'Budget Variance Control',
        'finance_automation'=>'Finance Automation Runs',
        'customer_portal'=>'Customer Portal Experience',
        'hr_payroll'=>'HR Payroll Summary',
        'procurement_score'=>'Supplier Scorecard Performance',
        'manufacturing_cost'=>'Manufacturing Cost Rollups',
    ];
}

function reportRows(PDO $pdo, string $type, array $filters = []): array
{
    $limit=max(1,min((int)setting('report_export_max_rows','5000'),(int)($filters['limit']??300)));
    if($type==='sales_pipeline'){
        $sql='SELECT sales_order_number document,customer_name,total,status,credit_check_status,created_at FROM '.table('sales_orders').' ORDER BY created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='inventory_value'){
        $sql='SELECT p.sku,p.name product,ws.quantity,ws.reserved_quantity,ws.average_unit_cost,ws.stock_value,w.warehouse_name,b.branch_name FROM '.table('warehouse_stock').' ws LEFT JOIN '.table('products').' p ON p.id=ws.product_id LEFT JOIN '.table('warehouses').' w ON w.id=ws.warehouse_id LEFT JOIN '.table('branches').' b ON b.id=ws.branch_id ORDER BY ws.stock_value DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='ap_matching'){
        $sql='SELECT internal_number,supplier_invoice_number,supplier_name,total,matched_total,difference_amount,match_status,status,created_at FROM '.table('supplier_invoices').' ORDER BY difference_amount DESC,created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='project_margin'){
        $sql='SELECT project_number,project_name,budget_amount,revenue_target,cost_amount,margin_amount,status,created_at FROM '.table('projects').' ORDER BY margin_amount ASC,created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='service_profit'){
        $sql='SELECT job_card_number,customer_name,plate_number,status,labor_total,parts_total,total,actual_cost,(total-actual_cost) gross_profit,created_at FROM '.table('job_cards').' ORDER BY created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='credit_exposure'){
        $customers=$pdo->query('SELECT id,customer_code,company_name,contact_name,credit_limit,credit_status FROM '.table('customers').' WHERE customer_type="b2b" ORDER BY company_name,contact_name LIMIT '.$limit)->fetchAll();
        $rows=[];
        foreach($customers as $customer){
            $snap=customerCreditSnapshot($pdo,(int)$customer['id'],0);
            $rows[]=[
                'customer_code'=>$customer['customer_code'],
                'customer'=>trim(($customer['company_name']?:'').' '.($customer['contact_name']?:'')),
                'credit_limit'=>$snap['limit'],
                'exposure'=>$snap['exposure'],
                'available'=>$snap['available'],
                'credit_status'=>$customer['credit_status'],
            ];
        }
        return $rows;
    }
    if($type==='approval_sla'){
        $sql='SELECT request_number,document_type,document_number,request_amount,status,submitted_at,resolved_at,current_step FROM '.table('approval_requests').' ORDER BY created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='budget_variance'){
        $sql='SELECT bp.period_code,cc.cost_center_code,cc.cost_center_name,bp.budget_amount,bp.committed_amount,bp.actual_amount,bp.variance_amount,bp.status FROM '.table('budget_periods').' bp LEFT JOIN '.table('cost_centers').' cc ON cc.id=bp.cost_center_id ORDER BY bp.period_code DESC,bp.variance_amount ASC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='finance_automation'){
        $sql='SELECT run_number,run_type,status,items_processed,total_amount,created_at FROM '.table('finance_automation_runs').' ORDER BY created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='customer_portal'){
        $sql='SELECT request_number,subject,priority,status,created_at FROM '.table('customer_service_requests').' ORDER BY created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='hr_payroll'){
        $sql='SELECT pr.payroll_number,pp.period_name,pr.gross_total,pr.deduction_total,pr.net_total,pr.status,pr.created_at FROM '.table('payroll_runs').' pr LEFT JOIN '.table('payroll_periods').' pp ON pp.id=pr.payroll_period_id ORDER BY pr.created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='procurement_score'){
        $sql='SELECT sc.scorecard_number,s.company_name,sc.period_label,sc.total_score,sc.rating,sc.status,sc.created_at FROM '.table('supplier_scorecards').' sc LEFT JOIN '.table('suppliers').' s ON s.id=sc.supplier_id ORDER BY sc.total_score DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    if($type==='manufacturing_cost'){
        $sql='SELECT cost_number,source_type,source_id,material_cost,labor_cost,overhead_cost,total_cost,unit_cost,created_at FROM '.table('production_cost_rollups').' ORDER BY created_at DESC LIMIT '.$limit;
        return $pdo->query($sql)->fetchAll();
    }
    return [];
}

function writeCsvResponse(string $filename, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $out=fopen('php://output','w');
    if($rows){
        fputcsv($out,array_keys($rows[0]));
        foreach($rows as $row){fputcsv($out,$row);}
    }else{
        fputcsv($out,['No data']);
    }
    fclose($out);
    exit;
}



function mobileQrValue(string $type, int $id, string $number = ''): string
{
    return strtoupper($type) . ':' . $id . ':' . preg_replace('/[^A-Za-z0-9\-]/','',$number);
}

function createFieldDispatch(PDO $pdo, int $jobCardId, int $technicianUserId, string $dispatchDate, string $startTime = '', string $address = ''): int
{
    $number=nextScopedDocumentNumber($pdo,'field_dispatch',setting('field_dispatch_prefix','DISP'),operationalScope($pdo));
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('field_service_dispatches').' (dispatch_number,job_card_id,technician_user_id,dispatch_date,start_time,service_address,dispatch_status,created_by) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$number,$jobCardId,$technicianUserId,$dispatchDate,$startTime?:null,$address,setting('field_default_dispatch_status','scheduled'),(int)($user['id']??0)?:null]);
    $pdo->prepare('UPDATE '.table('job_cards').' SET technician_user_id=?,status=CASE WHEN status="draft" THEN "in_progress" ELSE status END WHERE id=?')->execute([$technicianUserId,$jobCardId]);
    logActivity($pdo,'Field Dispatch','field_dispatch_created','Dispatch '.$number.' created.','field_service_dispatch',$pdo->lastInsertId());
    return (int)$pdo->lastInsertId();
}

function createAssetQrCode(PDO $pdo, ?int $customerAssetId, ?int $jobCardId): int
{
    $number=nextScopedDocumentNumber($pdo,'asset_qr_code',setting('asset_qr_prefix','QR'),operationalScope($pdo));
    $value=mobileQrValue('ASSET',$customerAssetId ?: 0,$number);
    $url=rtrim(SITE_URL,'/').'/asset-lookup.php?qr='.urlencode($value);
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('asset_qr_codes').' (qr_number,customer_asset_id,job_card_id,qr_value,qr_url,status,created_by) VALUES (?,?,?,?,?,"active",?)');
    $stmt->execute([$number,$customerAssetId,$jobCardId,$value,$url,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function logBarcodeScan(PDO $pdo, string $value, string $scanType, string $module, string $referenceType = '', ?int $referenceId = null, string $result = 'captured'): int
{
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('barcode_scan_logs').' (scan_value,scan_type,source_module,reference_type,reference_id,scanned_by,scan_result,notes) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$value,$scanType,$module,$referenceType,$referenceId,(int)($user['id']??0)?:null,$result,'Captured from ERP mobile workflow.']);
    return (int)$pdo->lastInsertId();
}

function createOfflineJobDraft(PDO $pdo, ?int $jobCardId, array $payload, string $deviceId = ''): int
{
    $number=nextScopedDocumentNumber($pdo,'offline_job_card_draft',setting('offline_draft_prefix','OFF'),operationalScope($pdo));
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('offline_job_card_drafts').' (draft_number,user_id,job_card_id,device_id,draft_payload,sync_status,notes) VALUES (?,?,?,?,?,"pending",?)');
    $stmt->execute([$number,(int)($user['id']??0)?:null,$jobCardId,$deviceId,json_encode($payload,JSON_UNESCAPED_SLASHES),'Created from offline/mobile draft form.']);
    return (int)$pdo->lastInsertId();
}

function syncOfflineJobDraft(PDO $pdo, int $draftId): void
{
    $pdo->prepare('UPDATE '.table('offline_job_card_drafts').' SET sync_status="completed_sync",last_sync_at=NOW() WHERE id=?')->execute([$draftId]);
}

function createCustomerSignoff(PDO $pdo, int $jobCardId, string $name, string $phone, string $signature, int $rating, string $notes = ''): int
{
    $number=nextScopedDocumentNumber($pdo,'customer_signoff',setting('customer_signoff_prefix','SIGN'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('field_customer_signoffs').' (signoff_number,job_card_id,customer_name,customer_phone,signature_data,rating,status,signed_at,notes) VALUES (?,?,?,?,?,?,"signed",NOW(),?)');
    $stmt->execute([$number,$jobCardId,$name,$phone,$signature,$rating,$notes]);
    $pdo->prepare('UPDATE '.table('job_cards').' SET status="completed",completed_at=COALESCE(completed_at,NOW()) WHERE id=?')->execute([$jobCardId]);
    return (int)$pdo->lastInsertId();
}








function createCrmFollowupTask(PDO $pdo, array $data): int
{
    $number=nextScopedDocumentNumber($pdo,'crm_followup_task',setting('crm_followup_task_prefix','CFT'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('crm_followup_tasks').' (task_number,related_type,related_id,lead_id,customer_id,opportunity_id,quotation_id,assigned_to,subject,task_type,priority,due_date,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"open",?)');
    $stmt->execute([
        $number,
        (string)($data['related_type']??'lead'),
        (int)($data['related_id']??0)?:null,
        (int)($data['lead_id']??0)?:null,
        (int)($data['customer_id']??0)?:null,
        (int)($data['opportunity_id']??0)?:null,
        (int)($data['quotation_id']??0)?:null,
        (int)($data['assigned_to']??0)?:null,
        trim((string)($data['subject']??'CRM follow-up')),
        trim((string)($data['task_type']??'call')),
        trim((string)($data['priority']??'medium')),
        trim((string)($data['due_date']??''))?:null,
        trim((string)($data['notes']??''))
    ]);
    return (int)$pdo->lastInsertId();
}

function completeCrmFollowupTask(PDO $pdo, int $taskId, string $notes=''): void
{
    $pdo->prepare('UPDATE '.table('crm_followup_tasks').' SET status="completed",completed_at=NOW(),notes=CONCAT(COALESCE(notes,""),"\n",?) WHERE id=?')->execute([$notes,$taskId]);
}

function createCrmTouchpoint(PDO $pdo, array $data): int
{
    $number=nextScopedDocumentNumber($pdo,'crm_touchpoint',setting('crm_touchpoint_prefix','TCH'),operationalScope($pdo));
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('crm_customer_touchpoints').' (touchpoint_number,customer_id,lead_id,opportunity_id,user_id,touchpoint_type,subject,notes,touchpoint_at,next_follow_up,outcome) VALUES (?,?,?,?,?,?,?,?,NOW(),?,?)');
    $stmt->execute([
        $number,
        (int)($data['customer_id']??0)?:null,
        (int)($data['lead_id']??0)?:null,
        (int)($data['opportunity_id']??0)?:null,
        (int)($user['id']??0)?:null,
        trim((string)($data['touchpoint_type']??'note')),
        trim((string)($data['subject']??'CRM touchpoint')),
        trim((string)($data['notes']??'')),
        trim((string)($data['next_follow_up']??''))?:null,
        trim((string)($data['outcome']??''))
    ]);
    return (int)$pdo->lastInsertId();
}

function generateQuoteFollowups(PDO $pdo): int
{
    $days=max(1,(int)setting('crm_quote_followup_days','3'));
    $quotes=$pdo->query('SELECT q.* FROM '.table('quotations').' q LEFT JOIN '.table('crm_quote_followups').' f ON f.quotation_id=q.id AND f.status IN ("open","waiting") WHERE q.status IN ("sent","draft","pending") AND q.converted_invoice_id IS NULL AND f.id IS NULL ORDER BY q.created_at DESC LIMIT 200')->fetchAll();
    $stmt=$pdo->prepare('INSERT INTO '.table('crm_quote_followups').' (followup_number,quotation_id,customer_id,customer_email,quotation_total,followup_stage,next_follow_up,status,notes) VALUES (?,?,?,?,?,?,DATE_ADD(CURDATE(), INTERVAL '.$days.' DAY),"open",?)');
    $count=0;
    foreach($quotes as $q){
        $num=nextScopedDocumentNumber($pdo,'crm_quote_followup',setting('crm_quote_followup_prefix','QFU'),operationalScope($pdo));
        $stmt->execute([$num,(int)$q['id'],(int)($q['customer_id']??0)?:null,(string)($q['customer_email']??''),(float)$q['total'],'first_followup','Auto-created for quotation '.$q['quotation_number']]);
        createCrmFollowupTask($pdo,['related_type'=>'quotation','related_id'=>(int)$q['id'],'quotation_id'=>(int)$q['id'],'customer_id'=>(int)($q['customer_id']??0),'subject'=>'Follow up quotation '.$q['quotation_number'],'task_type'=>'call','priority'=>'medium','due_date'=>date('Y-m-d',strtotime('+'.$days.' days')),'notes'=>'Auto-generated quotation follow-up.']);
        $count++;
    }
    return $count;
}

function generateLeadFollowupTasks(PDO $pdo): int
{
    $days=max(0,(int)setting('crm_task_due_days','1'));
    $rows=$pdo->query('SELECT l.* FROM '.table('crm_leads').' l LEFT JOIN '.table('crm_followup_tasks').' t ON t.lead_id=l.id AND t.status="open" WHERE l.converted_customer_id IS NULL AND (l.next_follow_up IS NULL OR l.next_follow_up<=DATE_ADD(CURDATE(), INTERVAL '.$days.' DAY)) AND t.id IS NULL ORDER BY l.estimated_value DESC,l.created_at DESC LIMIT 200')->fetchAll();
    $count=0;
    foreach($rows as $lead){
        createCrmFollowupTask($pdo,['related_type'=>'lead','related_id'=>(int)$lead['id'],'lead_id'=>(int)$lead['id'],'assigned_to'=>(int)($lead['assigned_to']??0),'subject'=>'Follow up lead '.$lead['name'],'task_type'=>'call','priority'=>((float)$lead['estimated_value']>=5000?'high':'medium'),'due_date'=>!empty($lead['next_follow_up'])?$lead['next_follow_up']:date('Y-m-d',strtotime('+'.$days.' days')),'notes'=>'Auto-generated from due lead follow-up.']);
        $count++;
    }
    return $count;
}

function createSalesForecast(PDO $pdo, string $period=''): int
{
    $period=$period!==''?$period:date('Y-m');
    $floor=max(0,(int)setting('crm_forecast_probability_floor','10'));
    $stmt=$pdo->prepare('SELECT COALESCE(SUM(value_amount),0) pipeline_value,COALESCE(SUM(weighted_value),0) weighted_value,COUNT(*) open_count FROM '.table('sales_opportunities').' WHERE status="open" AND probability>=?');
    $stmt->execute([$floor]);
    $open=$stmt->fetch() ?: ['pipeline_value'=>0,'weighted_value'=>0,'open_count'=>0];
    $won=(float)$pdo->query('SELECT COALESCE(SUM(value_amount),0) FROM '.table('sales_opportunities').' WHERE status="won"')->fetchColumn();
    $lost=(float)$pdo->query('SELECT COALESCE(SUM(value_amount),0) FROM '.table('sales_opportunities').' WHERE status="lost"')->fetchColumn();
    $number=nextScopedDocumentNumber($pdo,'crm_sales_forecast',setting('crm_sales_forecast_prefix','FCST'),operationalScope($pdo));
    $expected=(float)$open['weighted_value']+$won;
    $pdo->prepare('INSERT INTO '.table('crm_sales_forecasts').' (forecast_number,period_label,pipeline_value,weighted_value,open_opportunities,expected_revenue,won_value,lost_value,status) VALUES (?,?,?,?,?,?,?,?,"calculated")')->execute([$number,$period,(float)$open['pipeline_value'],(float)$open['weighted_value'],(int)$open['open_count'],$expected,$won,$lost]);
    return (int)$pdo->lastInsertId();
}

function createCampaignActions(PDO $pdo, int $campaignId, string $channel='email'): int
{
    $members=$pdo->prepare('SELECT m.* FROM '.table('campaign_members').' m LEFT JOIN '.table('crm_campaign_actions').' a ON a.campaign_member_id=m.id WHERE m.marketing_campaign_id=? AND a.id IS NULL LIMIT 500');
    $members->execute([$campaignId]);
    $stmt=$pdo->prepare('INSERT INTO '.table('crm_campaign_actions').' (action_number,marketing_campaign_id,campaign_member_id,channel,action_type,subject,status,scheduled_at,notes) VALUES (?,?,?,?,?,"Campaign outreach","scheduled",NOW(),?)');
    $count=0;
    foreach($members->fetchAll() as $m){
        $num=nextScopedDocumentNumber($pdo,'crm_campaign_action',setting('crm_campaign_action_prefix','CAMP-ACT'),operationalScope($pdo));
        $stmt->execute([$num,$campaignId,(int)$m['id'],$channel,'message','Auto-created campaign action for member '.$m['id']]);
        $count++;
    }
    return $count;
}



function employeeForCurrentUser(PDO $pdo, array $user): ?array
{
    $email=(string)($user['email']??'');
    if($email===''){return null;}
    $stmt=$pdo->prepare('SELECT * FROM '.table('employees').' WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $employee=$stmt->fetch();
    return $employee ?: null;
}

function calculateLeaveDays(string $start, string $end): float
{
    if($start==='' || $end===''){return 0;}
    $s=strtotime($start);$e=strtotime($end);
    if(!$s || !$e || $e<$s){return 0;}
    return (float)(floor(($e-$s)/86400)+1);
}

function ensureEmployeeLeaveBalance(PDO $pdo, int $employeeId, string $leaveType='Annual Leave', ?int $year=null): int
{
    $year=$year ?: (int)date('Y');
    $stmt=$pdo->prepare('SELECT id FROM '.table('employee_leave_balances').' WHERE employee_id=? AND leave_type=? AND year_no=? LIMIT 1');
    $stmt->execute([$employeeId,$leaveType,$year]);
    $id=(int)$stmt->fetchColumn();
    if($id>0){return $id;}
    $default=(float)setting('default_annual_leave_days','30');
    $pdo->prepare('INSERT INTO '.table('employee_leave_balances').' (employee_id,leave_type,opening_balance,accrued_days,used_days,remaining_days,year_no,status) VALUES (?,?,?,?,0,?,?, "active")')->execute([$employeeId,$leaveType,0,$default,$default,$year]);
    return (int)$pdo->lastInsertId();
}

function refreshEmployeeLeaveBalance(PDO $pdo, int $employeeId, string $leaveType='Annual Leave', ?int $year=null): void
{
    $year=$year ?: (int)date('Y');
    ensureEmployeeLeaveBalance($pdo,$employeeId,$leaveType,$year);
    $stmt=$pdo->prepare('SELECT COALESCE(SUM(DATEDIFF(end_date,start_date)+1),0) FROM '.table('leave_requests').' WHERE employee_id=? AND leave_type=? AND status="approved" AND YEAR(start_date)=?');
    $stmt->execute([$employeeId,$leaveType,$year]);
    $used=(float)$stmt->fetchColumn();
    $pdo->prepare('UPDATE '.table('employee_leave_balances').' SET used_days=?, remaining_days=(opening_balance+accrued_days-?) WHERE employee_id=? AND leave_type=? AND year_no=?')->execute([$used,$used,$employeeId,$leaveType,$year]);
}

function createEmployeePayslipDocument(PDO $pdo, int $payrollRunItemId): int
{
    $stmt=$pdo->prepare('SELECT pri.*,pr.payroll_number,pp.period_name,e.employee_code,e.first_name,e.last_name FROM '.table('payroll_run_items').' pri LEFT JOIN '.table('payroll_runs').' pr ON pr.id=pri.payroll_run_id LEFT JOIN '.table('payroll_periods').' pp ON pp.id=pr.payroll_period_id LEFT JOIN '.table('employees').' e ON e.id=pri.employee_id WHERE pri.id=? LIMIT 1');
    $stmt->execute([$payrollRunItemId]);
    $item=$stmt->fetch();
    if(!$item){throw new RuntimeException('Payroll item not found.');}
    $existing=$pdo->prepare('SELECT id FROM '.table('employee_payroll_documents').' WHERE payroll_run_item_id=? LIMIT 1');
    $existing->execute([$payrollRunItemId]);
    $existingId=(int)$existing->fetchColumn();
    if($existingId>0){return $existingId;}
    $number=nextScopedDocumentNumber($pdo,'employee_payslip',setting('employee_payslip_prefix','PAYSLIP'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('employee_payroll_documents').' (payroll_run_item_id,employee_id,document_number,period_label,gross_pay,net_pay,status) VALUES (?,?,?,?,?,?,"published")')->execute([$payrollRunItemId,(int)$item['employee_id'],$number,$item['period_name']?:$item['payroll_number'],(float)$item['gross_pay'],(float)$item['net_pay']]);
    return (int)$pdo->lastInsertId();
}

function createEmployeeLoan(PDO $pdo, int $employeeId, string $type, float $amount, float $installment, string $startDate, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'employee_loan',setting('employee_loan_prefix','ELOAN'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('employee_loans').' (loan_number,employee_id,loan_type,principal_amount,installment_amount,balance_amount,start_date,status,approved_by,approved_at,notes) VALUES (?,?,?,?,?,?,?,"open",?,NOW(),?)')->execute([$number,$employeeId,$type,$amount,$installment,$amount,$startDate?:null,(int)($user['id']??0)?:null,$notes]);
    return (int)$pdo->lastInsertId();
}

function createEmployeeDocument(PDO $pdo, int $employeeId, array $upload, string $type, string $expiry='', string $notes=''): int
{
    $pdo->prepare('INSERT INTO '.table('employee_documents').' (employee_id,document_type,file_name,stored_path,mime_type,file_size,expiry_date,status,notes) VALUES (?,?,?,?,?,?,?,"active",?)')->execute([$employeeId,$type,$upload['file_name'],$upload['stored_path'],$upload['mime_type'],$upload['file_size'],$expiry?:null,$notes]);
    return (int)$pdo->lastInsertId();
}


function customerPortalContext(PDO $pdo, array $user): array
{
    $stmt=$pdo->prepare('SELECT id FROM '.table('customers').' WHERE email=? LIMIT 1');
    $stmt->execute([$user['email'] ?? '']);
    $customerId=(int)($stmt->fetchColumn()?:0);
    return ['user_id'=>(int)($user['id']??0),'customer_id'=>$customerId,'email'=>(string)($user['email']??'')];
}

function createCustomerPortalNotification(PDO $pdo, ?int $userId, ?int $customerId, string $title, string $message, string $link=''): int
{
    $stmt=$pdo->prepare('INSERT INTO '.table('customer_portal_notifications').' (user_id,customer_id,title,message,link_url,status) VALUES (?,?,?,?,?,"unread")');
    $stmt->execute([$userId?:null,$customerId?:null,$title,$message,$link]);
    return (int)$pdo->lastInsertId();
}

function createCustomerInvoiceDispute(PDO $pdo, int $invoiceId, int $userId, int $customerId, string $reason, string $description): int
{
    $number=nextScopedDocumentNumber($pdo,'customer_invoice_dispute',setting('customer_invoice_dispute_prefix','DISP'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('customer_invoice_disputes').' (dispute_number,invoice_id,user_id,customer_id,reason,description,status) VALUES (?,?,?,?,?,?,"open")')->execute([$number,$invoiceId,$userId?:null,$customerId?:null,$reason,$description]);
    createCustomerPortalNotification($pdo,$userId,$customerId,'Invoice dispute received','We received your invoice dispute '.$number.'.','/user/invoice-disputes.php');
    return (int)$pdo->lastInsertId();
}

function createCustomerPaymentPromise(PDO $pdo, int $invoiceId, int $userId, int $customerId, float $amount, string $date, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'customer_payment_promise',setting('customer_payment_promise_prefix','PROM'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('customer_payment_promises').' (promise_number,invoice_id,user_id,customer_id,promised_amount,promised_date,status,notes) VALUES (?,?,?,?,?,?,"open",?)')->execute([$number,$invoiceId,$userId?:null,$customerId?:null,$amount,$date?:null,$notes]);
    createCustomerPortalNotification($pdo,$userId,$customerId,'Payment promise recorded','Your payment promise '.$number.' has been recorded.','/user/payment-promises.php');
    return (int)$pdo->lastInsertId();
}

function unreadCustomerPortalNotifications(PDO $pdo, int $userId, int $customerId): int
{
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM '.table('customer_portal_notifications').' WHERE status="unread" AND (user_id=? OR customer_id=?)');
    $stmt->execute([$userId,$customerId]);
    return (int)$stmt->fetchColumn();
}


function supplierScoreRating(float $score): string
{
    if($score >= 90){return 'A+';}
    if($score >= 80){return 'A';}
    if($score >= 70){return 'B';}
    if($score >= 60){return 'C';}
    return 'D';
}

function createSupplierScorecard(PDO $pdo, int $supplierId, string $period, float $quality, float $delivery, float $price, float $response, float $compliance, string $notes=''): int
{
    $total=round(($quality+$delivery+$price+$response+$compliance)/5,2);
    $rating=supplierScoreRating($total);
    $number=nextScopedDocumentNumber($pdo,'supplier_scorecard',setting('supplier_scorecard_prefix','SSC'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('supplier_scorecards').' (scorecard_number,supplier_id,period_label,quality_score,delivery_score,price_score,response_score,compliance_score,total_score,rating,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,"published",?)')->execute([$number,$supplierId,$period,$quality,$delivery,$price,$response,$compliance,$total,$rating,$notes]);
    return (int)$pdo->lastInsertId();
}

function createSupplierOnboarding(PDO $pdo, array $data): int
{
    $number=nextScopedDocumentNumber($pdo,'supplier_onboarding',setting('supplier_onboarding_prefix','SON'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('supplier_onboarding_requests').' (onboarding_number,supplier_id,company_name,contact_name,email,phone,tax_number,category,risk_level,status,submitted_at,notes) VALUES (?,?,?,?,?,?,?,?,?,"submitted",NOW(),?)')->execute([
        $number,(int)($data['supplier_id']??0)?:null,trim((string)($data['company_name']??'')),trim((string)($data['contact_name']??'')),trim((string)($data['email']??'')),trim((string)($data['phone']??'')),trim((string)($data['tax_number']??'')),trim((string)($data['category']??'')),trim((string)($data['risk_level']??'medium')),trim((string)($data['notes']??''))
    ]);
    $id=(int)$pdo->lastInsertId();
    $step=$pdo->prepare('INSERT INTO '.table('supplier_onboarding_steps').' (supplier_onboarding_request_id,step_name,step_type,required_flag,status) VALUES (?,?,?,?, "pending")');
    foreach(['Trade License','VAT Certificate / Tax Number','Bank Details','Product / Service Category Review','Compliance Approval'] as $name){$step->execute([$id,$name,'document',1]);}
    return $id;
}

function convertRfqQuoteToPurchaseOrder(PDO $pdo, int $quoteId, string $reason=''): int
{
    $q=$pdo->prepare('SELECT q.*,s.company_name supplier_name,r.company_id,r.branch_id,r.warehouse_id,r.source_requisition_id FROM '.table('rfq_supplier_quotes').' q LEFT JOIN '.table('suppliers').' s ON s.id=q.supplier_id LEFT JOIN '.table('rfqs').' r ON r.id=q.rfq_id WHERE q.id=? LIMIT 1');
    $q->execute([$quoteId]);
    $quote=$q->fetch();
    if(!$quote){throw new RuntimeException('Supplier quote not found.');}
    $poNumber=nextScopedDocumentNumber($pdo,'purchase_order',setting('purchase_order_prefix','PO'),operationalScope($pdo));
    $status=setting('procurement_auto_po_status','approved');
    $pdo->prepare('INSERT INTO '.table('purchase_orders').' (company_id,branch_id,warehouse_id,source_requisition_id,po_number,supplier_id,supplier_name,order_date,expected_date,subtotal,tax,shipping,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([
        (int)($quote['company_id']??0)?:null,(int)($quote['branch_id']??0)?:null,(int)($quote['warehouse_id']??0)?:null,(int)($quote['source_requisition_id']??0)?:null,
        $poNumber,(int)$quote['supplier_id'],$quote['supplier_name'],date('Y-m-d'),date('Y-m-d',strtotime('+'.max(1,(int)$quote['delivery_days']).' days')),
        (float)$quote['subtotal'],(float)$quote['tax'],(float)$quote['shipping'],(float)$quote['total_amount'],$status,'Auto-created from RFQ quote '.$quote['response_number'].'. '.$reason
    ]);
    $poId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('rfq_supplier_quote_items').' WHERE rfq_supplier_quote_id=?');
    $items->execute([$quoteId]);
    $ins=$pdo->prepare('INSERT INTO '.table('purchase_order_items').' (purchase_order_id,product_id,description,quantity,unit_cost,tax_rate,line_total) VALUES (?,?,?,?,?,?,?)');
    foreach($items->fetchAll() as $item){
        $ins->execute([$poId,(int)($item['product_id']??0)?:null,$item['description'],(float)$item['quantity'],(float)$item['unit_price'],(float)$item['tax_rate'],(float)$item['line_total']]);
    }
    $award=nextScopedDocumentNumber($pdo,'procurement_award',setting('procurement_award_prefix','AWD'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('procurement_award_decisions').' (award_number,rfq_id,rfq_supplier_quote_id,supplier_id,decision_reason,total_score,status,created_by) VALUES (?,?,?,?,?,?,"awarded",?)')->execute([$award,(int)$quote['rfq_id'],$quoteId,(int)$quote['supplier_id'],$reason,(float)$quote['rank_score'],(int)($user['id']??0)?:null]);
    $pdo->prepare('UPDATE '.table('rfqs').' SET status="awarded",awarded_supplier_id=?,awarded_quote_id=?,converted_po_id=? WHERE id=?')->execute([(int)$quote['supplier_id'],$quoteId,$poId,(int)$quote['rfq_id']]);
    $pdo->prepare('UPDATE '.table('rfq_supplier_quotes').' SET status=CASE WHEN id=? THEN "awarded" ELSE status END WHERE rfq_id=?')->execute([$quoteId,(int)$quote['rfq_id']]);
    return $poId;
}


function createWarehouseBin(PDO $pdo, int $warehouseId, int $locationId, string $binCode, string $binName, string $binType, float $capacity): int
{
    if($binCode===''){$binCode=nextScopedDocumentNumber($pdo,'warehouse_bin',setting('warehouse_bin_prefix','BIN'),operationalScope($pdo));}
    $stmt=$pdo->prepare('INSERT INTO '.table('warehouse_bins').' (warehouse_id,location_id,bin_code,bin_name,bin_type,capacity_qty,status) VALUES (?,?,?,?,?,?,"active")');
    $stmt->execute([$warehouseId,$locationId?:null,$binCode,$binName,$binType,$capacity]);
    return (int)$pdo->lastInsertId();
}

function createInventoryLot(PDO $pdo, int $productId, int $warehouseId, int $locationId, int $binId, float $qty, string $mfg='', string $exp='', string $notes=''): int
{
    $lot=nextScopedDocumentNumber($pdo,'inventory_lot',setting('inventory_lot_prefix','LOT'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('inventory_lots').' (lot_number,product_id,warehouse_id,location_id,bin_id,quantity,manufacture_date,expiry_date,status,notes) VALUES (?,?,?,?,?,?,?,?,"active",?)')->execute([$lot,$productId,$warehouseId?:null,$locationId?:null,$binId?:null,$qty,$mfg?:null,$exp?:null,$notes]);
    return (int)$pdo->lastInsertId();
}

function createInventorySerial(PDO $pdo, int $productId, string $serial, int $warehouseId=0, int $locationId=0, int $binId=0, int $lotId=0, string $notes=''): int
{
    $pdo->prepare('INSERT INTO '.table('inventory_serial_numbers').' (serial_number,product_id,warehouse_id,location_id,bin_id,lot_id,status,notes) VALUES (?,?,?,?,?,?,"available",?)')->execute([$serial,$productId,$warehouseId?:null,$locationId?:null,$binId?:null,$lotId?:null,$notes]);
    return (int)$pdo->lastInsertId();
}

function createStockCountSession(PDO $pdo, int $warehouseId, int $locationId=0, int $binId=0, string $type='cycle', string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'stock_count',setting('stock_count_prefix','COUNT'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('stock_count_sessions').' (count_number,warehouse_id,location_id,bin_id,count_type,status,started_by,started_at,notes) VALUES (?,?,?,?,?,"in_progress",?,NOW(),?)')->execute([$number,$warehouseId,$locationId?:null,$binId?:null,$type,(int)($user['id']??0)?:null,$notes]);
    return (int)$pdo->lastInsertId();
}

function addStockCountLine(PDO $pdo, int $sessionId, int $productId, float $counted, string $notes=''): int
{
    $session=$pdo->prepare('SELECT * FROM '.table('stock_count_sessions').' WHERE id=? LIMIT 1');$session->execute([$sessionId]);$s=$session->fetch();
    if(!$s){throw new RuntimeException('Stock count session not found.');}
    $scope=['company_id'=>0,'branch_id'=>0,'warehouse_id'=>(int)$s['warehouse_id'],'location_id'=>(int)($s['location_id']??0)];
    $system=warehouseAvailableQuantity($pdo,$productId,$scope);
    $unit=productDefaultCost($pdo,$productId);
    $variance=$counted-$system;
    $pdo->prepare('INSERT INTO '.table('stock_count_lines').' (stock_count_session_id,product_id,system_qty,counted_qty,variance_qty,unit_cost,variance_value,status,notes) VALUES (?,?,?,?,?,?,?,"counted",?)')->execute([$sessionId,$productId,$system,$counted,$variance,$unit,$variance*$unit,$notes]);
    return (int)$pdo->lastInsertId();
}

function createInventoryAdjustmentFromCount(PDO $pdo, int $sessionId): int
{
    $s=$pdo->prepare('SELECT * FROM '.table('stock_count_sessions').' WHERE id=? LIMIT 1');$s->execute([$sessionId]);$session=$s->fetch();
    if(!$session){throw new RuntimeException('Stock count session not found.');}
    $number=nextScopedDocumentNumber($pdo,'inventory_adjustment',setting('inventory_adjustment_prefix','ADJ'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('inventory_adjustment_requests').' (adjustment_number,warehouse_id,location_id,bin_id,reason,status,requested_by,requested_at,notes) VALUES (?,?,?,?, "Stock count variance", "draft", ?, NOW(), ?)')->execute([$number,(int)$session['warehouse_id'],(int)($session['location_id']??0)?:null,(int)($session['bin_id']??0)?:null,(int)($user['id']??0)?:null,'Created from stock count '.$session['count_number']]);
    $adjId=(int)$pdo->lastInsertId();
    $lines=$pdo->prepare('SELECT * FROM '.table('stock_count_lines').' WHERE stock_count_session_id=? AND ABS(variance_qty)>0.0001');$lines->execute([$sessionId]);
    $ins=$pdo->prepare('INSERT INTO '.table('inventory_adjustment_lines').' (inventory_adjustment_request_id,product_id,adjustment_qty,unit_cost,total_value,notes) VALUES (?,?,?,?,?,?)');
    foreach($lines->fetchAll() as $line){$ins->execute([$adjId,(int)$line['product_id'],(float)$line['variance_qty'],(float)$line['unit_cost'],(float)$line['variance_value'],'From count line '.$line['id']]);}
    return $adjId;
}

function postInventoryAdjustment(PDO $pdo, int $adjId): void
{
    $adj=$pdo->prepare('SELECT * FROM '.table('inventory_adjustment_requests').' WHERE id=? LIMIT 1');$adj->execute([$adjId]);$a=$adj->fetch();
    if(!$a){throw new RuntimeException('Adjustment not found.');}
    $scope=['company_id'=>0,'branch_id'=>0,'warehouse_id'=>(int)$a['warehouse_id'],'location_id'=>(int)($a['location_id']??0)];
    $lines=$pdo->prepare('SELECT * FROM '.table('inventory_adjustment_lines').' WHERE inventory_adjustment_request_id=?');$lines->execute([$adjId]);
    foreach($lines->fetchAll() as $line){adjustWarehouseStock($pdo,(int)$line['product_id'],(float)$line['adjustment_qty'],$scope,'inventory_adjustment',$adjId,'Inventory adjustment '.$a['adjustment_number'],(float)$line['unit_cost']);}
    $user=currentUser();
    $pdo->prepare('UPDATE '.table('inventory_adjustment_requests').' SET status="posted",posted_by=?,posted_at=NOW() WHERE id=?')->execute([(int)($user['id']??0)?:null,$adjId]);
}

function createReplenishmentSuggestions(PDO $pdo): int
{
    $rules=$pdo->query('SELECT r.*,COALESCE(ws.quantity,0) current_qty FROM '.table('replenishment_rules').' r LEFT JOIN '.table('warehouse_stock').' ws ON ws.product_id=r.product_id AND ws.warehouse_id=r.warehouse_id WHERE r.status="active"')->fetchAll();
    $created=0;$stmt=$pdo->prepare('INSERT INTO '.table('replenishment_suggestions').' (suggestion_number,replenishment_rule_id,product_id,warehouse_id,current_qty,recommended_qty,source_type,status) VALUES (?,?,?,?,?,?, "rule", "open")');
    foreach($rules as $r){if((float)$r['current_qty'] <= (float)$r['min_qty']){$num=nextScopedDocumentNumber($pdo,'replenishment_suggestion',setting('replenishment_prefix','REP'),operationalScope($pdo));$qty=(float)$r['reorder_qty']>0?(float)$r['reorder_qty']:max(0,(float)$r['max_qty']-(float)$r['current_qty']);$stmt->execute([$num,(int)$r['id'],(int)$r['product_id'],(int)$r['warehouse_id'],(float)$r['current_qty'],$qty]);$created++;}}
    return $created;
}

function createPickingList(PDO $pdo, string $sourceType, int $sourceId, int $warehouseId): int
{
    $number=nextScopedDocumentNumber($pdo,'picking_list',setting('picking_prefix','PICK'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('picking_lists').' (picking_number,source_type,source_id,warehouse_id,status) VALUES (?,?,?,?, "draft")')->execute([$number,$sourceType,$sourceId,$warehouseId]);
    return (int)$pdo->lastInsertId();
}


function bomCostSummary(PDO $pdo, int $bomId): array
{
    $stmt=$pdo->prepare('SELECT COALESCE(SUM(line_cost),0) FROM '.table('bom_lines').' WHERE bill_of_material_id=?');
    $stmt->execute([$bomId]);
    $material=(float)$stmt->fetchColumn();
    $routing=$pdo->prepare('SELECT b.routing_id FROM '.table('bill_of_materials').' b WHERE b.id=?');
    $routing->execute([$bomId]);
    $routingId=(int)$routing->fetchColumn();
    $labor=0.0;
    if($routingId>0){
        $q=$pdo->prepare('SELECT COALESCE(SUM((standard_minutes/60)*labor_rate),0) FROM '.table('manufacturing_routing_steps').' WHERE manufacturing_routing_id=?');
        $q->execute([$routingId]);
        $labor=(float)$q->fetchColumn();
    }
    $overhead=round(($material+$labor)*((float)setting('manufacturing_overhead_percent','10')/100),2);
    $total=round($material+$labor+$overhead,2);
    return ['material'=>$material,'labor'=>$labor,'overhead'=>$overhead,'total'=>$total];
}

function createManufacturingWorkOrder(PDO $pdo, int $bomId, float $qty, string $plannedStart='', string $plannedFinish=''): int
{
    $bom=$pdo->prepare('SELECT * FROM '.table('bill_of_materials').' WHERE id=? LIMIT 1');
    $bom->execute([$bomId]);
    $b=$bom->fetch();
    if(!$b){throw new RuntimeException('BOM not found.');}
    $cost=bomCostSummary($pdo,$bomId);
    $number=nextScopedDocumentNumber($pdo,'manufacturing_work_order',setting('manufacturing_work_order_prefix','MO'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('manufacturing_work_orders').' (work_order_number,bill_of_material_id,finished_product_id,planned_quantity,planned_start,planned_finish,status,estimated_cost,notes) VALUES (?,?,?,?,?,?,"planned",?,?)');
    $stmt->execute([$number,$bomId,(int)$b['finished_product_id'],$qty,$plannedStart?:null,$plannedFinish?:null,$cost['total']*$qty,'Created from BOM '.$b['bom_number']]);
    $woId=(int)$pdo->lastInsertId();
    $lines=$pdo->prepare('SELECT * FROM '.table('bom_lines').' WHERE bill_of_material_id=?');
    $lines->execute([$bomId]);
    $mat=$pdo->prepare('INSERT INTO '.table('work_order_materials').' (manufacturing_work_order_id,component_product_id,required_quantity,unit_cost,total_cost,status) VALUES (?,?,?,?,?,"required")');
    foreach($lines->fetchAll() as $line){
        $required=(float)$line['quantity']*$qty*(1+((float)$line['wastage_percent']/100));
        $unit=(float)$line['unit_cost'];
        $mat->execute([$woId,(int)$line['component_product_id'],$required,$unit,$required*$unit]);
    }
    if((int)($b['routing_id']??0)>0){
        $ops=$pdo->prepare('SELECT * FROM '.table('manufacturing_routing_steps').' WHERE manufacturing_routing_id=? ORDER BY step_number,id');
        $ops->execute([(int)$b['routing_id']]);
        $opIns=$pdo->prepare('INSERT INTO '.table('work_order_operations').' (manufacturing_work_order_id,work_center_id,operation_name,planned_minutes,labor_cost,status) VALUES (?,?,?,?,?,"pending")');
        foreach($ops->fetchAll() as $op){
            $minutes=(float)$op['standard_minutes']*$qty;
            $labor=($minutes/60)*(float)$op['labor_rate'];
            $opIns->execute([$woId,(int)$op['work_center_id']?:null,$op['operation_name'],$minutes,$labor]);
        }
    }
    return $woId;
}

function issueProductionMaterial(PDO $pdo, int $woId, int $productId, float $qty, string $notes=''): int
{
    // Hotfix: products table uses cost_price / average_cost, not a column named cost.
    $unit=productDefaultCost($pdo,$productId);
    $number=nextScopedDocumentNumber($pdo,'production_issue',setting('production_issue_prefix','ISSUE'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_material_issues').' (issue_number,manufacturing_work_order_id,component_product_id,quantity,unit_cost,total_cost,issued_by,issued_at,notes) VALUES (?,?,?,?,?,?,?,NOW(),?)')->execute([$number,$woId,$productId,$qty,$unit,$qty*$unit,(int)($user['id']??0)?:null,$notes]);
    $pdo->prepare('UPDATE '.table('work_order_materials').' SET issued_quantity=issued_quantity+?, status=CASE WHEN issued_quantity+?>=required_quantity THEN "issued" ELSE "partial" END WHERE manufacturing_work_order_id=? AND component_product_id=?')->execute([$qty,$qty,$woId,$productId]);
    if(setting('manufacturing_auto_stock_update','1')==='1'){
        $pdo->prepare('UPDATE '.table('products').' SET stock=GREATEST(stock-?,0) WHERE id=?')->execute([$qty,$productId]);
    }
    return (int)$pdo->lastInsertId();
}

function receiveProductionOutput(PDO $pdo, int $woId, float $qty, float $scrap=0, string $notes=''): int
{
    $wo=$pdo->prepare('SELECT * FROM '.table('manufacturing_work_orders').' WHERE id=? LIMIT 1');
    $wo->execute([$woId]);
    $w=$wo->fetch();
    if(!$w){throw new RuntimeException('Work order not found.');}
    $unit=(float)$w['estimated_cost']/max(1,(float)$w['planned_quantity']);
    $number=nextScopedDocumentNumber($pdo,'production_receipt',setting('production_receipt_prefix','RECEIPT'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('production_output_receipts').' (receipt_number,manufacturing_work_order_id,finished_product_id,quantity,scrap_quantity,unit_cost,total_cost,received_by,received_at,notes) VALUES (?,?,?,?,?,?,?,?,NOW(),?)')->execute([$number,$woId,(int)$w['finished_product_id'],$qty,$scrap,$unit,$unit*$qty,(int)($user['id']??0)?:null,$notes]);
    $pdo->prepare('UPDATE '.table('manufacturing_work_orders').' SET completed_quantity=completed_quantity+?,scrap_quantity=scrap_quantity+?,status=CASE WHEN completed_quantity+?>=planned_quantity THEN "completed" ELSE "in_progress" END,actual_finish=CASE WHEN completed_quantity+?>=planned_quantity THEN NOW() ELSE actual_finish END WHERE id=?')->execute([$qty,$scrap,$qty,$qty,$woId]);
    if(setting('manufacturing_auto_stock_update','1')==='1'){
        $pdo->prepare('UPDATE '.table('products').' SET stock=stock+? WHERE id=?')->execute([$qty,(int)$w['finished_product_id']]);
    }
    return (int)$pdo->lastInsertId();
}

function createProductionCostRollup(PDO $pdo, ?int $bomId, ?int $woId): int
{
    $material=0;$labor=0;$overhead=0;$total=0;$unit=0;
    if($bomId){
        $cost=bomCostSummary($pdo,$bomId);
        $material=$cost['material'];$labor=$cost['labor'];$overhead=$cost['overhead'];$total=$cost['total'];
        $qty=(float)$pdo->query('SELECT quantity FROM '.table('bill_of_materials').' WHERE id='.(int)$bomId)->fetchColumn();
        $unit=$total/max(1,$qty);
    }
    if($woId){
        $m=$pdo->prepare('SELECT COALESCE(SUM(total_cost),0) FROM '.table('production_material_issues').' WHERE manufacturing_work_order_id=?');$m->execute([$woId]);$material=(float)$m->fetchColumn();
        $l=$pdo->prepare('SELECT COALESCE(SUM(labor_cost),0) FROM '.table('work_order_operations').' WHERE manufacturing_work_order_id=?');$l->execute([$woId]);$labor=(float)$l->fetchColumn();
        $overhead=round(($material+$labor)*((float)setting('manufacturing_overhead_percent','10')/100),2);$total=$material+$labor+$overhead;
        $q=$pdo->prepare('SELECT planned_quantity FROM '.table('manufacturing_work_orders').' WHERE id=?');$q->execute([$woId]);$unit=$total/max(1,(float)$q->fetchColumn());
    }
    $number=nextScopedDocumentNumber($pdo,'production_cost_rollup',setting('production_cost_rollup_prefix','COST'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('production_cost_rollups').' (rollup_number,bill_of_material_id,manufacturing_work_order_id,material_cost,labor_cost,overhead_cost,total_cost,unit_cost,status) VALUES (?,?,?,?,?,?,?,?,"calculated")')->execute([$number,$bomId,$woId,$material,$labor,$overhead,$total,$unit]);
    return (int)$pdo->lastInsertId();
}


function rebuildSmartSearchIndex(PDO $pdo): array
{
    $pdo->exec('DELETE FROM '.table('smart_search_index'));
    $insert=$pdo->prepare('INSERT INTO '.table('smart_search_index').' (entity_type,entity_id,title,summary,keywords,url_path,status,last_indexed_at) VALUES (?,?,?,?,?,?, "active", NOW())');
    $count=0;
    foreach($pdo->query('SELECT id,sku,name,description,category_id FROM '.table('products').' ORDER BY id DESC LIMIT 1000')->fetchAll() as $p){$insert->execute(['product',(int)$p['id'],$p['name'],$p['description'],trim(($p['sku']??'').' product item stock price'),'/admin/products.php','active']);$count++;}
    foreach($pdo->query('SELECT id,order_number,status,total,created_at FROM '.table('orders').' ORDER BY id DESC LIMIT 1000')->fetchAll() as $o){$insert->execute(['order',(int)$o['id'],$o['order_number'],'Order '.$o['status'].' total '.money($o['total']),'order customer sale checkout','/admin/orders.php','active']);$count++;}
    foreach($pdo->query('SELECT id,customer_code,company_name,contact_name,email,phone FROM '.table('customers').' ORDER BY id DESC LIMIT 1000')->fetchAll() as $c){$insert->execute(['customer',(int)$c['id'],trim(($c['company_name']?:$c['contact_name']) ?: $c['customer_code']),trim(($c['email']??'').' '.($c['phone']??'')),'customer crm account contact','/admin/erp/crm.php','active']);$count++;}
    foreach($pdo->query('SELECT id,invoice_number,customer_name,total,balance_due,status FROM '.table('invoices').' ORDER BY id DESC LIMIT 1000')->fetchAll() as $i){$insert->execute(['invoice',(int)$i['id'],$i['invoice_number'],trim($i['customer_name'].' total '.money($i['total']).' balance '.money($i['balance_due'])),'invoice finance receivable payment','/admin/erp/invoices.php','active']);$count++;}
    foreach($pdo->query('SELECT id,job_number,customer_name,vehicle_info,status FROM '.table('job_cards').' ORDER BY id DESC LIMIT 1000')->fetchAll() as $j){$insert->execute(['job_card',(int)$j['id'],$j['job_number'],trim($j['customer_name'].' '.$j['vehicle_info'].' '.$j['status']),'service job technician dispatch repair','/admin/erp/job-cards.php','active']);$count++;}
    return ['indexed'=>$count];
}

function smartSearch(PDO $pdo, string $query, int $limit = 50): array
{
    $query = trim(mb_strtolower($query));
    if ($query === '') { return []; }
    $limit = max(1, min(200, (int)$limit));
    $rows = $pdo->query('SELECT * FROM ' . table('smart_search_index') . ' ORDER BY last_indexed_at DESC, id DESC LIMIT 2000')->fetchAll();
    $matched = [];
    foreach ($rows as $row) {
        $haystack = mb_strtolower((string)($row['title'] ?? '') . ' ' . (string)($row['summary'] ?? '') . ' ' . (string)($row['keywords'] ?? '') . ' ' . (string)($row['entity_type'] ?? ''));
        if (str_contains($haystack, $query)) {
            $matched[] = $row;
            if (count($matched) >= $limit) { break; }
        }
    }
    return $matched;
}

function generatePredictiveAlerts(PDO $pdo): array
{
    $created=0;$threshold=(float)setting('predictive_alert_score_threshold','60');
    $insert=$pdo->prepare('INSERT INTO '.table('predictive_alerts').' (alert_number,alert_type,severity,title,message,recommended_action,source_module,reference_type,reference_id,score,status) VALUES (?,?,?,?,?,?,?,?,?,?,"open")');
    foreach($pdo->query('SELECT id,sku,name,stock FROM '.table('products').' WHERE active=1 AND stock<=3 ORDER BY stock ASC LIMIT 25')->fetchAll() as $p){$score=90-(float)$p['stock']*10;if($score >= $threshold){$number=nextScopedDocumentNumber($pdo,'predictive_alert','PAL',operationalScope($pdo));$insert->execute([$number,'low_stock','high','Low stock risk: '.$p['name'],'Product '.$p['sku'].' has stock level '.$p['stock'].'.','Create purchase order, transfer stock, or pause promotion.','Inventory','product',(int)$p['id'],$score]);$created++;}}
    foreach($pdo->query('SELECT id,invoice_number,customer_name,balance_due FROM '.table('invoices').' WHERE status NOT IN ("paid","cancelled") AND balance_due>0 ORDER BY balance_due DESC LIMIT 25')->fetchAll() as $inv){$score=min(100,max(60,(float)$inv['balance_due']/100));if($score >= $threshold){$number=nextScopedDocumentNumber($pdo,'predictive_alert','PAL',operationalScope($pdo));$insert->execute([$number,'cash_collection','medium','Receivable follow-up: '.$inv['invoice_number'],$inv['customer_name'].' has balance due '.money($inv['balance_due']).'.','Send reminder, call customer, or escalate to finance.','Finance','invoice',(int)$inv['id'],$score]);$created++;}}
    foreach($pdo->query('SELECT id,job_number,customer_name,status,created_at FROM '.table('job_cards').' WHERE status NOT IN ("completed","cancelled","closed") ORDER BY created_at ASC LIMIT 25')->fetchAll() as $job){$ageDays=max(0,(time()-strtotime($job['created_at']))/86400);if($ageDays>=3){$number=nextScopedDocumentNumber($pdo,'predictive_alert','PAL',operationalScope($pdo));$insert->execute([$number,'service_delay','medium','Delayed service job: '.$job['job_number'],$job['customer_name'].' job is open for '.round($ageDays,1).' days.','Assign technician, update customer, or escalate dispatch.','Service','job_card',(int)$job['id'],min(100,$ageDays*20)]);$created++;}}
    return ['created'=>$created];
}

function generateRecommendations(PDO $pdo): array
{
    $created=0;$rules=$pdo->query('SELECT * FROM '.table('recommendation_rules').' WHERE status="active" ORDER BY priority,id')->fetchAll();
    $insert=$pdo->prepare('INSERT INTO '.table('recommendation_results').' (recommendation_number,recommendation_rule_id,title,recommendation_text,impact_score,effort_score,status,reference_type,reference_id) VALUES (?,?,?,?,?,?,"open",?,?)');
    foreach($rules as $rule){$number=nextScopedDocumentNumber($pdo,'recommendation_result','REC',operationalScope($pdo));$impact=75;$effort=35;if($rule['trigger_metric']==='low_stock'){$impact=90;$effort=40;}if($rule['trigger_metric']==='open_ar'){$impact=85;$effort=30;}if($rule['trigger_metric']==='hot_leads'){$impact=80;$effort=25;}$insert->execute([$number,(int)$rule['id'],$rule['rule_name'],$rule['recommendation_template'],$impact,$effort,$rule['module'],null]);$created++;}
    return ['created'=>$created];
}

function generateAnomalyDetections(PDO $pdo): array
{
    $created=0;$insert=$pdo->prepare('INSERT INTO '.table('anomaly_detections').' (anomaly_number,anomaly_type,module,severity,title,description,baseline_value,observed_value,variance_value,status) VALUES (?,?,?,?,?,?,?,?,?,"open")');
    $avgOrder=(float)$pdo->query('SELECT COALESCE(AVG(total),0) FROM '.table('orders').' WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)')->fetchColumn();
    $todayOrder=(float)$pdo->query('SELECT COALESCE(AVG(total),0) FROM '.table('orders').' WHERE DATE(created_at)=CURDATE()')->fetchColumn();
    if($avgOrder>0 && $todayOrder>0 && abs($todayOrder-$avgOrder)/$avgOrder>0.5){$number=nextScopedDocumentNumber($pdo,'anomaly_detection','ANM',operationalScope($pdo));$insert->execute([$number,'order_value_variance','Sales','medium','Order value anomaly','Today average order value differs by more than 50% from 90-day average.',$avgOrder,$todayOrder,$todayOrder-$avgOrder]);$created++;}
    $expenses=(float)$pdo->query('SELECT COALESCE(SUM(amount+tax),0) FROM '.table('expenses').' WHERE expense_date>=DATE_FORMAT(CURDATE(), "%Y-%m-01")')->fetchColumn();
    $sales=(float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM '.table('invoices').' WHERE DATE(created_at)>=DATE_FORMAT(CURDATE(), "%Y-%m-01")')->fetchColumn();
    if($sales>0 && ($expenses/$sales)>0.6){$number=nextScopedDocumentNumber($pdo,'anomaly_detection','ANM',operationalScope($pdo));$insert->execute([$number,'expense_ratio','Finance','high','Expense ratio anomaly','Month expenses are above 60% of invoiced sales.',$sales*0.6,$expenses,$expenses-($sales*0.6)]);$created++;}
    return ['created'=>$created];
}

function assistantRuleResponse(PDO $pdo, string $message): string
{
    $m=strtolower($message);
    if(str_contains($m,'low stock') || str_contains($m,'inventory')){$count=(int)$pdo->query('SELECT COUNT(*) FROM '.table('products').' WHERE active=1 AND stock<=3')->fetchColumn();return 'Inventory check: '.$count.' products are currently at low-stock level. Recommended action: open Low Stock report and create purchase/transfer actions.';}
    if(str_contains($m,'sales') || str_contains($m,'revenue')){$sales=(float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM '.table('invoices').' WHERE DATE(created_at)>=DATE_FORMAT(CURDATE(), "%Y-%m-01") AND status NOT IN ("cancelled","draft")')->fetchColumn();return 'Sales check: Month-to-date invoiced revenue is '.money($sales).'. Recommended action: review pipeline and open quotations to improve conversion.';}
    if(str_contains($m,'cash') || str_contains($m,'receivable') || str_contains($m,'invoice')){$ar=(float)$pdo->query('SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE status NOT IN ("paid","cancelled")')->fetchColumn();return 'Finance check: Open receivables total '.money($ar).'. Recommended action: send reminders to high-balance invoices first.';}
    if(str_contains($m,'job') || str_contains($m,'technician') || str_contains($m,'dispatch')){$open=(int)$pdo->query('SELECT COUNT(*) FROM '.table('job_cards').' WHERE status NOT IN ("completed","cancelled","closed")')->fetchColumn();return 'Service check: '.$open.' service job cards are open. Recommended action: use Field Dispatch and assign priority jobs.';}
    return 'I checked the ERP context. Try asking about low stock, sales, receivables, open jobs, technicians, or cash flow.';
}






function ecom33SessionToken(): string
{
    if(session_status() !== PHP_SESSION_ACTIVE){@session_start();}
    if(empty($_SESSION['ecom33_token'])){$_SESSION['ecom33_token']=bin2hex(random_bytes(16));}
    return (string)$_SESSION['ecom33_token'];
}

function ecom33LogActivity(PDO $pdo, string $type, string $entityType='', ?int $entityId=null, ?int $productId=null, string $message=''): void
{
    try{
        $number=nextScopedDocumentNumber($pdo,'ecommerce_activity',setting('ecommerce_activity_prefix','EACT'),operationalScope($pdo));
        $user=currentUser();
        $pdo->prepare('INSERT INTO '.table('ecommerce_activity_logs').' (activity_number,user_id,session_token,activity_type,entity_type,entity_id,product_id,message,ip_address,user_agent) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([$number,(int)($user['id']??0)?:null,ecom33SessionToken(),$type,$entityType,$entityId,$productId,$message,$_SERVER['REMOTE_ADDR']??'',substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255)]);
    }catch(Throwable $e){}
}

function ecom33EffectivePrice(PDO $pdo, int $productId, ?int $customerId=null, float $qty=1, string $customerType='b2c'): float
{
    $stmt=$pdo->prepare('SELECT price,category_id FROM '.table('products').' WHERE id=? LIMIT 1');$stmt->execute([$productId]);$p=$stmt->fetch();
    if(!$p){return 0.0;}
    $price=(float)$p['price'];
    if(setting('b2b_pricing_enabled','1')!=='1'){return $price;}
    $params=[];$where='status="active" AND min_quantity<=? AND (valid_from IS NULL OR valid_from<=CURDATE()) AND (valid_to IS NULL OR valid_to>=CURDATE()) AND (product_id IS NULL OR product_id=?) AND (category_id IS NULL OR category_id=?)';
    $params[]=$qty;$params[]=$productId;$params[]=(int)$p['category_id'];
    if($customerId){$where.=' AND (customer_id IS NULL OR customer_id=?)';$params[]=$customerId;}
    if($customerType){$where.=' AND (customer_type IS NULL OR customer_type="" OR customer_type=?)';$params[]=$customerType;}
    $sql='SELECT * FROM '.table('customer_price_rules').' WHERE '.$where.' ORDER BY priority DESC,customer_id DESC,product_id DESC LIMIT 1';
    $s=$pdo->prepare($sql);$s->execute($params);$rule=$s->fetch();
    if($rule){
        if($rule['rule_type']==='fixed_price'){$price=(float)$rule['rule_value'];}
        elseif($rule['rule_type']==='amount_discount'){$price=max(0,$price-(float)$rule['rule_value']);}
        else{$price=max(0,$price-($price*((float)$rule['rule_value']/100)));}
    } elseif($customerType==='b2b'){
        $price=max(0,$price-($price*((float)setting('default_b2b_discount_percent','5')/100)));
    }
    return round($price,2);
}

function ecom33CreatePriceList(PDO $pdo, string $name, string $customerType='b2b', float $discount=0, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'b2b_price_list',setting('b2b_price_list_prefix','B2BPL'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('b2b_price_lists').' (price_list_number,price_list_name,customer_type,currency,discount_percent,status,notes,created_by) VALUES (?,?,?,?,?,"active",?,?)')->execute([$number,$name,$customerType,setting('currency','AED'),$discount,$notes,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function ecom33CreateCustomerPriceRule(PDO $pdo, array $data): int
{
    $number=nextScopedDocumentNumber($pdo,'customer_price_rule',setting('customer_price_rule_prefix','CPR'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('customer_price_rules').' (rule_number,customer_id,customer_type,customer_group,product_id,category_id,rule_type,rule_value,min_quantity,priority,valid_from,valid_to,status,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([
        $number,(int)($data['customer_id']??0)?:null,trim((string)($data['customer_type']??'')),trim((string)($data['customer_group']??'')),(int)($data['product_id']??0)?:null,(int)($data['category_id']??0)?:null,
        trim((string)($data['rule_type']??'percent_discount')),(float)($data['rule_value']??0),(float)($data['min_quantity']??1),(int)($data['priority']??50),trim((string)($data['valid_from']??''))?:null,trim((string)($data['valid_to']??''))?:null,trim((string)($data['status']??'active')),trim((string)($data['notes']??'')),(int)($user['id']??0)?:null
    ]);
    return (int)$pdo->lastInsertId();
}

function ecom33CreateBundle(PDO $pdo, array $data): int
{
    $number=nextScopedDocumentNumber($pdo,'product_bundle',setting('product_bundle_prefix','BNDL'),operationalScope($pdo));
    $slug=trim((string)($data['slug']??'')); if($slug===''){$slug=strtolower(preg_replace('/[^a-z0-9]+/i','-',trim((string)$data['bundle_name'])));}
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('product_bundles').' (bundle_number,bundle_name,slug,description,bundle_price,compare_price,total_cost,status,featured,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([$number,trim((string)$data['bundle_name']),trim($slug,'-'),trim((string)($data['description']??'')),(float)($data['bundle_price']??0),(float)($data['compare_price']??0),(float)($data['total_cost']??0),trim((string)($data['status']??'active')),(int)($data['featured']??0),(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function ecom33AddBundleItem(PDO $pdo, int $bundleId, int $productId, float $qty, ?float $unitPrice=null): int
{
    $p=$pdo->prepare('SELECT price FROM '.table('products').' WHERE id=? LIMIT 1');$p->execute([$productId]);$price=$unitPrice ?? (float)$p->fetchColumn();
    $line=round($price*$qty,2);
    $pdo->prepare('INSERT INTO '.table('product_bundle_items').' (product_bundle_id,product_id,quantity,unit_price,line_total,sort_order) VALUES (?,?,?,?,?,0)')->execute([$bundleId,$productId,$qty,$price,$line]);
    return (int)$pdo->lastInsertId();
}

function ecom33AddDigitalLicense(PDO $pdo, ?int $productId, string $poolName, string $code, string $serial='', string $link=''): int
{
    $number=nextScopedDocumentNumber($pdo,'digital_license_pool',setting('digital_license_pool_prefix','DLIC'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('digital_license_pools').' (pool_number,product_id,pool_name,license_code,serial_number,activation_link,status,created_by) VALUES (?,?,?,?,?,?, "available", ?)')->execute([$number,$productId,$poolName,$code,$serial,$link,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function ecom33AssignDigitalLicense(PDO $pdo, int $licensePoolId, ?int $orderId, ?int $customerId, string $email, string $name=''): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('digital_license_pools').' WHERE id=? AND status="available" LIMIT 1');$stmt->execute([$licensePoolId]);$license=$stmt->fetch();
    if(!$license){throw new RuntimeException('Available digital license not found.');}
    $number=nextScopedDocumentNumber($pdo,'digital_license_assignment',setting('digital_license_assignment_prefix','DLAS'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('digital_license_assignments').' (assignment_number,digital_license_pool_id,order_id,customer_id,customer_email,assigned_to_name,assigned_at,status,delivery_status,assigned_by) VALUES (?,?,?,?,?,?,NOW(),"assigned","pending",?)')->execute([$number,$licensePoolId,$orderId,$customerId,$email,$name,(int)($user['id']??0)?:null]);
    $assignmentId=(int)$pdo->lastInsertId();
    $pdo->prepare('UPDATE '.table('digital_license_pools').' SET status="assigned" WHERE id=?')->execute([$licensePoolId]);
    if(setting('digital_license_delivery_enabled','1')==='1'){
        $subject='Your digital license from '.setting('shop_name','Store');
        $body="License: ".$license['license_code']."\nSerial: ".$license['serial_number']."\nActivation: ".$license['activation_link'];
        $pdo->prepare('INSERT INTO '.table('digital_license_deliveries').' (digital_license_assignment_id,delivery_method,recipient_email,delivery_subject,delivery_body,status) VALUES (?,?,?,?,?,"queued")')->execute([$assignmentId,'email',$email,$subject,$body]);
    }
    return $assignmentId;
}

function ecom33GetWishlist(PDO $pdo): int
{
    $token=ecom33SessionToken();
    $stmt=$pdo->prepare('SELECT id FROM '.table('wishlists').' WHERE session_token=? AND status="active" LIMIT 1');$stmt->execute([$token]);$id=(int)$stmt->fetchColumn();
    if($id>0){return $id;}
    $number=nextScopedDocumentNumber($pdo,'wishlist',setting('wishlist_prefix','WISH'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('wishlists').' (wishlist_number,user_id,session_token,wishlist_name,status) VALUES (?,?,?,"My Wishlist","active")')->execute([$number,(int)($user['id']??0)?:null,$token]);
    return (int)$pdo->lastInsertId();
}

function ecom33AddWishlistItem(PDO $pdo, int $productId, float $qty=1): void
{
    $wishlistId=ecom33GetWishlist($pdo);
    $pdo->prepare('INSERT INTO '.table('wishlist_items').' (wishlist_id,product_id,quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity=quantity+VALUES(quantity)')->execute([$wishlistId,$productId,$qty]);
    ecom33LogActivity($pdo,'wishlist_add','product',$productId,$productId,'Product added to wishlist.');
}

function ecom33GetCompareSession(PDO $pdo): int
{
    $token=ecom33SessionToken();
    $stmt=$pdo->prepare('SELECT id FROM '.table('product_comparison_sessions').' WHERE session_token=? AND status="active" LIMIT 1');$stmt->execute([$token]);$id=(int)$stmt->fetchColumn();
    if($id>0){return $id;}
    $number=nextScopedDocumentNumber($pdo,'comparison',setting('comparison_prefix','COMP'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('product_comparison_sessions').' (compare_number,user_id,session_token,status) VALUES (?,?,?,"active")')->execute([$number,(int)($user['id']??0)?:null,$token]);
    return (int)$pdo->lastInsertId();
}

function ecom33AddCompareItem(PDO $pdo, int $productId): void
{
    $sessionId=ecom33GetCompareSession($pdo);
    $count=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('product_comparison_items').' WHERE product_comparison_session_id='.$sessionId);
    if($count>=4){throw new RuntimeException('Compare list supports up to 4 products.');}
    $pdo->prepare('INSERT IGNORE INTO '.table('product_comparison_items').' (product_comparison_session_id,product_id,sort_order) VALUES (?,?,?)')->execute([$sessionId,$productId,$count+1]);
    ecom33LogActivity($pdo,'compare_add','product',$productId,$productId,'Product added to comparison.');
}

function ecom33CreateQuoteRequest(PDO $pdo, array $data, array $items): int
{
    $number=nextScopedDocumentNumber($pdo,'quote_request',setting('quote_request_prefix','QRQ'),operationalScope($pdo));
    $user=currentUser();$subtotal=0;
    foreach($items as $it){$subtotal += ((float)($it['unit_price']??0))*((float)($it['quantity']??1));}
    $pdo->prepare('INSERT INTO '.table('quote_requests_2').' (quote_request_number,user_id,customer_type,company_name,contact_name,email,phone,source,subtotal,estimated_total,status,priority,notes) VALUES (?,?,?,?,?,?,?,?,?,?,"new",?,?)')->execute([$number,(int)($user['id']??0)?:null,trim((string)($data['customer_type']??'b2b')),trim((string)($data['company_name']??'')),trim((string)($data['contact_name']??'')),trim((string)($data['email']??'')),trim((string)($data['phone']??'')),trim((string)($data['source']??'store_request_quote')),$subtotal,$subtotal,trim((string)($data['priority']??'normal')),trim((string)($data['notes']??''))]);
    $id=(int)$pdo->lastInsertId();
    $stmt=$pdo->prepare('INSERT INTO '.table('quote_request_items').' (quote_request_id,product_id,product_name,sku,quantity,target_price,unit_price,line_total,notes) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach($items as $it){$qty=(float)($it['quantity']??1);$unit=(float)($it['unit_price']??0);$stmt->execute([$id,(int)($it['product_id']??0)?:null,(string)($it['product_name']??''),(string)($it['sku']??''),$qty,(float)($it['target_price']??0),$unit,$qty*$unit,(string)($it['notes']??'')]);}
    ecom33LogActivity($pdo,'quote_request','quote_request',$id,null,'Quote request created.');
    return $id;
}

function ecom33CreateBulkOrder(PDO $pdo, array $data, array $items): int
{
    $number=nextScopedDocumentNumber($pdo,'bulk_order',setting('bulk_order_prefix','BULK'),operationalScope($pdo));
    $user=currentUser();$subtotal=0;
    foreach($items as $it){$subtotal += ((float)($it['unit_price']??0))*((float)($it['quantity']??1));}
    $pdo->prepare('INSERT INTO '.table('bulk_order_requests').' (bulk_request_number,user_id,company_name,contact_name,email,phone,required_date,delivery_location,status,subtotal,notes) VALUES (?,?,?,?,?,?,?,?, "new", ?, ?)')->execute([$number,(int)($user['id']??0)?:null,trim((string)($data['company_name']??'')),trim((string)($data['contact_name']??'')),trim((string)($data['email']??'')),trim((string)($data['phone']??'')),trim((string)($data['required_date']??''))?:null,trim((string)($data['delivery_location']??'')),$subtotal,trim((string)($data['notes']??''))]);
    $id=(int)$pdo->lastInsertId();
    $stmt=$pdo->prepare('INSERT INTO '.table('bulk_order_request_items').' (bulk_order_request_id,product_id,product_name,sku,quantity,unit_price,line_total,notes) VALUES (?,?,?,?,?,?,?,?)');
    foreach($items as $it){$qty=(float)($it['quantity']??1);$unit=(float)($it['unit_price']??0);$stmt->execute([$id,(int)($it['product_id']??0)?:null,(string)($it['product_name']??''),(string)($it['sku']??''),$qty,$unit,$qty*$unit,(string)($it['notes']??'')]);}
    ecom33LogActivity($pdo,'bulk_order','bulk_order',$id,null,'Bulk order request created.');
    return $id;
}

function ecom33InstallDefaults(PDO $pdo): int
{
    $created=0;
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('b2b_price_lists'))===0){ecom33CreatePriceList($pdo,'Default B2B Workshop Price List','b2b',(float)setting('default_b2b_discount_percent','5'),'Default B2B price list.');$created++;}
    if((int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('ecommerce_discount_rules'))===0){
        $number=nextScopedDocumentNumber($pdo,'ecommerce_discount_rule',setting('ecommerce_discount_rule_prefix','EDISC'),operationalScope($pdo));
        $pdo->prepare('INSERT INTO '.table('ecommerce_discount_rules').' (rule_number,rule_name,rule_scope,rule_type,rule_value,min_subtotal,coupon_code,customer_type,status,notes) VALUES (?,?,?,?,?,?,?,?, "active", ?)')->execute([$number,'B2B Welcome Discount','cart','percent',5,1000,'B2B5','b2b','Default advanced ecommerce rule.']);$created++;
    }
    return $created;
}


function documentModuleOptions(): array
{
    return [
        'general'=>'General',
        'finance'=>'Finance',
        'sales'=>'Sales',
        'procurement'=>'Procurement',
        'inventory'=>'Inventory',
        'service'=>'Service',
        'hr'=>'HR',
        'customer'=>'Customer',
        'supplier'=>'Supplier',
        'product'=>'Product',
        'compliance'=>'Compliance',
        'contract'=>'Contract',
    ];
}

function dmsValidDocumentUploadExtension(string $name): bool
{
    $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
    $allowed=array_filter(array_map('trim',explode(',',setting('document_allowed_extensions','pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt,csv,zip'))));
    return in_array($ext,$allowed,true);
}

function dmsUploadDocumentFile(string $field='document_file'): ?array
{
    if(empty($_FILES[$field]) || !is_array($_FILES[$field])){return null;}
    $file=$_FILES[$field];
    $error=(int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if($error===UPLOAD_ERR_NO_FILE){return null;}
    if($error!==UPLOAD_ERR_OK){throw new RuntimeException('Document upload failed.');}
    $size=(int)($file['size'] ?? 0);
    $max=max(1,(int)setting('document_max_upload_mb','25'))*1024*1024;
    if($size<=0 || $size>$max){throw new RuntimeException('Document file is larger than the configured maximum.');}
    $original=basename((string)($file['name'] ?? 'document'));
    if(!dmsValidDocumentUploadExtension($original)){throw new RuntimeException('Document file extension is not allowed.');}
    $ext=strtolower(pathinfo($original,PATHINFO_EXTENSION));
    $dir=ensureUploadDirectory('documents/'.date('Y/m'));
    $stored=date('YmdHis').'_'.bin2hex(random_bytes(6)).'.'.$ext;
    $dest=$dir.'/'.$stored;
    if(!move_uploaded_file((string)$file['tmp_name'],$dest)){throw new RuntimeException('Unable to save uploaded document.');}
    $mime='';
    if(class_exists('finfo')){$finfo=new finfo(FILEINFO_MIME_TYPE);$mime=(string)$finfo->file($dest);}
    elseif(function_exists('mime_content_type')){$mime=(string)mime_content_type($dest);}
    return ['file_name'=>$original,'stored_path'=>'documents/'.date('Y/m').'/'.$stored,'mime_type'=>$mime,'file_size'=>$size];
}

function createDocumentLibraryRecord(PDO $pdo, array $data, ?array $file=null): int
{
    $scope=operationalScope($pdo);$user=currentUser();
    $number=nextScopedDocumentNumber($pdo,'document_library',setting('document_library_prefix','DOC'),$scope);
    $approvalStatus=((int)($data['requires_approval']??0)===1 || setting('document_require_approval_default','0')==='1') ? 'pending' : 'not_required';
    $stmt=$pdo->prepare('INSERT INTO '.table('document_library').' (document_number,company_id,branch_id,folder_id,category_id,title,document_type,module_key,linked_entity_type,linked_entity_id,version_number,file_name,stored_path,mime_type,file_size,expiry_date,review_date,confidentiality,approval_status,status,tags,description,uploaded_by,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
    $stmt->execute([
        $number,(int)($data['company_id']??0)?:($scope['company_id']??null),(int)($data['branch_id']??0)?:($scope['branch_id']??null),(int)($data['folder_id']??0)?:null,(int)($data['category_id']??0)?:null,
        trim((string)$data['title']),trim((string)($data['document_type']??'general')),trim((string)($data['module_key']??'general')),trim((string)($data['linked_entity_type']??'')),(int)($data['linked_entity_id']??0)?:null,'1.0',
        $file['file_name']??'', $file['stored_path']??'', $file['mime_type']??'', (int)($file['file_size']??0),
        trim((string)($data['expiry_date']??''))?:null,trim((string)($data['review_date']??''))?:null,trim((string)($data['confidentiality']??'internal')),$approvalStatus,'active',trim((string)($data['tags']??'')),trim((string)($data['description']??'')),(int)($user['id']??0)?:null
    ]);
    $id=(int)$pdo->lastInsertId();
    if($file && setting('document_versioning_enabled','1')==='1'){createDocumentVersion($pdo,$id,'1.0',$file,'Initial upload');}
    if(!empty($data['linked_entity_type'])){linkDocumentRecord($pdo,$id,(string)$data['module_key'],(string)$data['linked_entity_type'],(int)($data['linked_entity_id']??0),'','Initial link');}
    if($approvalStatus==='pending'){createDocumentApproval($pdo,$id,'document_review','Auto-created approval request.');}
    if(!empty($data['expiry_date'])){createDocumentExpiryAlert($pdo,$id,(int)setting('document_default_expiry_alert_days','30'));}
    logActivity($pdo,'DMS','document_created','Document '.$number.' created.','document_library',$id);
    return $id;
}

function createDocumentVersion(PDO $pdo, int $documentId, string $version, ?array $file, string $summary=''): int
{
    $user=currentUser();
    if($file){
        $pdo->prepare('UPDATE '.table('document_versions').' SET status="previous" WHERE document_library_id=?')->execute([$documentId]);
        $pdo->prepare('UPDATE '.table('document_library').' SET version_number=?,file_name=?,stored_path=?,mime_type=?,file_size=?,updated_at=NOW() WHERE id=?')->execute([$version,$file['file_name']??'',$file['stored_path']??'',$file['mime_type']??'',(int)($file['file_size']??0),$documentId]);
    }
    $pdo->prepare('INSERT INTO '.table('document_versions').' (document_library_id,version_number,file_name,stored_path,mime_type,file_size,change_summary,uploaded_by,status) VALUES (?,?,?,?,?,?,?,?,"current")')->execute([$documentId,$version,$file['file_name']??'', $file['stored_path']??'', $file['mime_type']??'', (int)($file['file_size']??0),$summary,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function createDocumentApproval(PDO $pdo, int $documentId, string $type='document_review', string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'document_approval',setting('document_approval_prefix','DAPP'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('document_approvals').' (approval_number,document_library_id,requested_by,approval_type,status,current_step,requested_at,notes) VALUES (?,?,?,?,"pending",1,NOW(),?)')->execute([$number,$documentId,(int)($user['id']??0)?:null,$type,$notes]);
    $approvalId=(int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO '.table('document_approval_steps').' (document_approval_id,step_number,approver_role,status) VALUES (?,1,"manager","pending")')->execute([$approvalId]);
    $pdo->prepare('UPDATE '.table('document_library').' SET approval_status="pending",updated_at=NOW() WHERE id=?')->execute([$documentId]);
    return $approvalId;
}

function decideDocumentApproval(PDO $pdo, int $approvalId, string $decision, string $notes=''): void
{
    $user=currentUser();
    $status=$decision==='approved'?'approved':'rejected';
    $pdo->prepare('UPDATE '.table('document_approval_steps').' SET status=?,decision_notes=?,decided_by=?,decided_at=NOW() WHERE document_approval_id=? AND status="pending" ORDER BY step_number ASC LIMIT 1')->execute([$status,$notes,(int)($user['id']??0)?:null,$approvalId]);
    $pdo->prepare('UPDATE '.table('document_approvals').' SET status=?,resolved_at=NOW(),notes=CONCAT(COALESCE(notes,""), ?) WHERE id=?')->execute([$status,"\nDecision: ".$notes,$approvalId]);
    $stmt=$pdo->prepare('SELECT document_library_id FROM '.table('document_approvals').' WHERE id=?');$stmt->execute([$approvalId]);$docId=(int)$stmt->fetchColumn();
    if($docId>0){$pdo->prepare('UPDATE '.table('document_library').' SET approval_status=?,status=CASE WHEN ?="rejected" THEN "on_hold" ELSE status END,updated_at=NOW() WHERE id=?')->execute([$status,$status,$docId]);}
}

function createDocumentExpiryAlert(PDO $pdo, int $documentId, int $daysBefore=30): int
{
    $stmt=$pdo->prepare('SELECT document_number,title,expiry_date FROM '.table('document_library').' WHERE id=? LIMIT 1');$stmt->execute([$documentId]);$doc=$stmt->fetch();
    if(!$doc || empty($doc['expiry_date'])){return 0;}
    $number=nextScopedDocumentNumber($pdo,'document_expiry_alert',setting('document_expiry_alert_prefix','DEXP'),operationalScope($pdo));
    $alertDate=date('Y-m-d',strtotime($doc['expiry_date'].' -'.$daysBefore.' days'));
    $message='Document '.$doc['document_number'].' expires on '.$doc['expiry_date'].': '.$doc['title'];
    $pdo->prepare('INSERT INTO '.table('document_expiry_alerts').' (alert_number,document_library_id,alert_type,days_before,alert_date,status,message) VALUES (?,?, "expiry", ?, ?, "open", ?)')->execute([$number,$documentId,$daysBefore,$alertDate,$message]);
    return (int)$pdo->lastInsertId();
}

function generateDocumentExpiryAlerts(PDO $pdo): int
{
    $days=(int)setting('document_default_expiry_alert_days','30');
    $rows=safeAiRows($pdo,'SELECT id FROM '.table('document_library').' WHERE expiry_date IS NOT NULL AND status="active" AND expiry_date<=DATE_ADD(CURDATE(), INTERVAL '.$days.' DAY)');
    $created=0;
    foreach($rows as $r){
        $exists=$pdo->prepare('SELECT id FROM '.table('document_expiry_alerts').' WHERE document_library_id=? AND status="open" LIMIT 1');$exists->execute([(int)$r['id']]);
        if($exists->fetchColumn()){continue;}
        if(createDocumentExpiryAlert($pdo,(int)$r['id'],$days)>0){$created++;}
    }
    return $created;
}

function linkDocumentRecord(PDO $pdo, int $documentId, string $module, string $entityType, ?int $entityId, string $entityNumber='', string $notes=''): int
{
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('document_linked_records').' (document_library_id,module_key,entity_type,entity_id,entity_number,link_notes,linked_by) VALUES (?,?,?,?,?,?,?)')->execute([$documentId,$module,$entityType,$entityId,$entityNumber,$notes,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function logDocumentAccess(PDO $pdo, int $documentId, string $type='view'): void
{
    if(setting('document_access_logging_enabled','1')!=='1'){return;}
    $user=currentUser();
    try{$pdo->prepare('INSERT INTO '.table('document_access_logs').' (document_library_id,user_id,access_type,ip_address,user_agent) VALUES (?,?,?,?,?)')->execute([$documentId,(int)($user['id']??0)?:null,$type,$_SERVER['REMOTE_ADDR']??'',substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255)]);}catch(Throwable $e){}
}

function installDocumentDefaults(PDO $pdo): int
{
    $user=currentUser();$count=0;
    $folders=[
        ['General','general','internal'],['Finance','finance','internal'],['Sales','sales','internal'],['Procurement','procurement','internal'],['HR','hr','restricted'],['Compliance','compliance','restricted'],['Contracts','contract','restricted'],['Customer Documents','customer','internal'],['Supplier Documents','supplier','internal'],['Product Documents','product','public'],
    ];
    $folderStmt=$pdo->prepare('INSERT IGNORE INTO '.table('document_folders').' (folder_code,folder_name,folder_path,module_key,visibility,status,sort_order,created_by) VALUES (?,?,?,?, "internal", "active", ?, ?)');
    foreach($folders as $i=>$f){$code=nextScopedDocumentNumber($pdo,'document_folder',setting('document_folder_prefix','FLD'),operationalScope($pdo));$folderStmt->execute([$code,$f[0],'/'.$f[0],$f[1],$i+1,(int)($user['id']??0)?:null]);$count += $folderStmt->rowCount()>0?1:0;}
    $cats=[
        ['Trade License','compliance',1,1],['Tax Certificate','finance',1,1],['Supplier Contract','supplier',1,1],['Customer Contract','customer',1,1],['Employee Document','hr',1,0],['Product Manual','product',0,0],['Warranty Document','service',1,0],['Invoice Attachment','finance',0,0],['Purchase Order Document','procurement',0,0],['Service Report','service',0,0],
    ];
    $catStmt=$pdo->prepare('INSERT IGNORE INTO '.table('document_categories').' (category_code,category_name,module_key,requires_expiry,requires_approval,default_retention_days,status,created_by) VALUES (?,?,?,?,?,3650,"active",?)');
    foreach($cats as $c){$code=nextScopedDocumentNumber($pdo,'document_category',setting('document_category_prefix','DCAT'),operationalScope($pdo));$catStmt->execute([$code,$c[0],$c[1],$c[2],$c[3],(int)($user['id']??0)?:null]);$count += $catStmt->rowCount()>0?1:0;}
    return $count;
}


function pwaDefaultSettings(): array
{
    return [
        'pwa_app_name'=>setting('pwa_app_name',setting('shop_name','ERP').' ERP'),
        'pwa_short_name'=>setting('pwa_short_name','ERP'),
        'pwa_theme_color'=>setting('pwa_theme_color','#0f172a'),
        'pwa_background_color'=>setting('pwa_background_color','#ffffff'),
        'pwa_display_mode'=>setting('pwa_display_mode','standalone'),
        'pwa_start_url'=>setting('pwa_start_url','/mobile/index.php'),
        'pwa_offline_page'=>setting('pwa_offline_page','/offline.php'),
        'mobile_app_version'=>setting('mobile_app_version','1.0.0'),
    ];
}

function installPwaDefaults(PDO $pdo): int
{
    $settings=pwaDefaultSettings();$count=0;$user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('pwa_settings').' (setting_key,setting_value,setting_group,description,updated_by,updated_at) VALUES (?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),updated_by=VALUES(updated_by),updated_at=NOW()');
    foreach($settings as $k=>$v){$stmt->execute([$k,$v,'pwa','PWA/mobile generated setting',(int)($user['id']??0)?:null]);$count += $stmt->rowCount()>0?1:0;}
    $assets=['/','/mobile/index.php','/mobile/customer.php','/mobile/employee.php','/mobile/technician.php','/offline.php','/assets/css/style.css'];
    $assetStmt=$pdo->prepare('INSERT IGNORE INTO '.table('pwa_cache_assets').' (asset_number,asset_url,asset_type,cache_strategy,is_required,status,created_by) VALUES (?,?,?,?,1,"active",?)');
    foreach($assets as $url){$assetStmt->execute([nextScopedDocumentNumber($pdo,'pwa_asset',setting('pwa_asset_prefix','PWA'),operationalScope($pdo)),$url,str_ends_with($url,'.css')?'asset':'page','stale_while_revalidate',(int)($user['id']??0)?:null]);$count += $assetStmt->rowCount()>0?1:0;}
    $shortcuts=[
        ['dashboard','Dashboard','/mobile/index.php','Open mobile ERP dashboard'],
        ['technician','Technician','/mobile/technician.php','Open technician workspace'],
        ['customer','Customer','/mobile/customer.php','Open customer portal'],
        ['employee','Employee','/mobile/employee.php','Open employee self-service'],
    ];
    $shortcutStmt=$pdo->prepare('INSERT IGNORE INTO '.table('pwa_app_shortcuts').' (shortcut_key,shortcut_name,shortcut_url,description,sort_order,is_enabled,created_by) VALUES (?,?,?,?,?,1,?)');
    foreach($shortcuts as $i=>$s){$shortcutStmt->execute([$s[0],$s[1],$s[2],$s[3],$i+1,(int)($user['id']??0)?:null]);$count += $shortcutStmt->rowCount()>0?1:0;}
    return $count;
}

function createPushNotification(PDO $pdo, ?int $userId, string $roleSlug, string $title, string $body, string $url='', string $priority='normal'): int
{
    $number=nextScopedDocumentNumber($pdo,'push_queue',setting('push_queue_prefix','PUSH'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('push_notification_queue').' (queue_number,user_id,role_slug,title,body,target_url,priority,status,scheduled_at,created_by) VALUES (?,?,?,?,?,?,?,"queued",NOW(),?)')->execute([$number,$userId,$roleSlug,$title,$body,$url,$priority,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function simulatePushDelivery(PDO $pdo, int $queueId): array
{
    $q=$pdo->prepare('SELECT * FROM '.table('push_notification_queue').' WHERE id=? LIMIT 1');$q->execute([$queueId]);$queue=$q->fetch();
    if(!$queue){throw new RuntimeException('Push notification not found.');}
    $subsSql='SELECT * FROM '.table('push_notification_subscriptions').' WHERE status="active"';
    $params=[];
    if(!empty($queue['user_id'])){$subsSql.=' AND user_id=?';$params[]=(int)$queue['user_id'];}
    $s=$pdo->prepare($subsSql);$s->execute($params);$subs=$s->fetchAll();
    $log=$pdo->prepare('INSERT INTO '.table('push_notification_logs').' (push_notification_queue_id,push_notification_subscription_id,user_id,delivery_status,response_message) VALUES (?,?,?,?,?)');
    $delivered=0;
    foreach($subs as $sub){$log->execute([$queueId,(int)$sub['id'],(int)$sub['user_id'],'simulated','Push delivery simulated for PWA readiness.']);$delivered++;}
    if(!$subs){$log->execute([$queueId,null,(int)($queue['user_id']??0)?:null,'no_subscriptions','No active push subscriptions found.']);}
    $pdo->prepare('UPDATE '.table('push_notification_queue').' SET status=?,sent_at=NOW(),error_message=? WHERE id=?')->execute([$delivered>0?'sent':'pending_subscription',$delivered>0?'':'No active subscriptions',$queueId]);
    return ['delivered'=>$delivered,'subscriptions'=>count($subs)];
}

function registerMobileDeviceSession(PDO $pdo, ?int $userId, string $deviceId, string $name='', string $platform='web', int $installed=0): int
{
    $number=nextScopedDocumentNumber($pdo,'device_session',setting('device_session_prefix','MOBDEV'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('mobile_device_sessions').' (device_number,user_id,device_id,device_name,platform,app_version,is_pwa_installed,last_seen_at,status,ip_address,user_agent) VALUES (?,?,?,?,?,?,?,NOW(),"active",?,?)');
    $stmt->execute([$number,$userId,$deviceId,$name,$platform,setting('mobile_app_version','1.0.0'),$installed,$_SERVER['REMOTE_ADDR']??'',substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255)]);
    return (int)$pdo->lastInsertId();
}

function recordMobileInstallEvent(PDO $pdo, ?int $userId, string $deviceId, string $type='install_prompt', string $platform='web', string $status='captured'): int
{
    $number=nextScopedDocumentNumber($pdo,'mobile_install_event',setting('mobile_install_event_prefix','MOBINS'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('mobile_app_install_events').' (event_number,user_id,device_id,event_type,platform,status) VALUES (?,?,?,?,?,?)')->execute([$number,$userId,$deviceId,$type,$platform,$status]);
    return (int)$pdo->lastInsertId();
}

function queueMobileOfflineSync(PDO $pdo, ?int $userId, string $deviceId, string $entityType, ?int $entityId, string $operation, array $payload): int
{
    $number=nextScopedDocumentNumber($pdo,'mobile_sync',setting('mobile_sync_prefix','MSYNC'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('mobile_offline_sync_queue').' (sync_number,user_id,device_id,entity_type,entity_id,operation,payload_json,sync_status) VALUES (?,?,?,?,?,?,?,"pending")')->execute([$number,$userId,$deviceId,$entityType,$entityId,$operation,json_encode($payload,JSON_UNESCAPED_SLASHES)]);
    return (int)$pdo->lastInsertId();
}

function processMobileOfflineSync(PDO $pdo, int $syncId): void
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('mobile_offline_sync_queue').' WHERE id=? LIMIT 1');$stmt->execute([$syncId]);$sync=$stmt->fetch();
    if(!$sync){throw new RuntimeException('Sync item not found.');}
    $status='synced';$message='Offline payload accepted and marked synced.';
    if($sync['entity_type']==='job_card_draft'){
        $draftNumber=nextScopedDocumentNumber($pdo,'offline_job_card_draft','OJCD',operationalScope($pdo));
        $pdo->prepare('INSERT INTO '.table('offline_job_card_drafts').' (draft_number,user_id,job_card_id,device_id,draft_payload,sync_status,last_sync_at,notes) VALUES (?,?,?,?,?,"synced",NOW(),?)')->execute([$draftNumber,(int)($sync['user_id']??0)?:null,(int)($sync['entity_id']??0)?:null,(string)$sync['device_id'],(string)$sync['payload_json'],'Created from mobile offline sync queue']);
        $message='Offline job-card draft created.';
    }
    $pdo->prepare('UPDATE '.table('mobile_offline_sync_queue').' SET sync_status=?,last_attempt_at=NOW(),error_message="" WHERE id=?')->execute([$status,$syncId]);
    $pdo->prepare('INSERT INTO '.table('mobile_offline_sync_logs').' (mobile_offline_sync_queue_id,sync_status,message) VALUES (?,?,?)')->execute([$syncId,$status,$message]);
}

function mobileDashboardCounts(PDO $pdo): array
{
    return [
        'pending_approvals'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('approval_requests').' WHERE status="pending"'),
        'open_jobs'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('job_cards').' WHERE status NOT IN ("completed","cancelled","closed")'),
        'customer_requests'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('customer_service_requests').' WHERE status IN ("open","reviewing","scheduled","in_progress")'),
        'low_stock'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('products').' WHERE active=1 AND stock<=3'),
        'offline_sync'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('mobile_offline_sync_queue').' WHERE sync_status="pending"'),
        'push_queue'=>(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('push_notification_queue').' WHERE status="queued"'),
    ];
}


function apiEndpointDefaults(): array
{
    return [
        ['products_list','Products List','GET','/api/v1/products.php','Products','read:products','List products for external catalog or marketplace sync','{}','{"success":true,"data":[]}'],
        ['customers_list','Customers List','GET','/api/v1/customers.php','Customers','read:customers','List customers for CRM or accounting integration','{}','{"success":true,"data":[]}'],
        ['orders_list','Orders List','GET','/api/v1/orders.php','Orders','read:orders','List orders for external reporting and fulfillment','{}','{"success":true,"data":[]}'],
        ['invoices_list','Invoices List','GET','/api/v1/invoices.php','Finance','read:invoices','List invoices and balances for accounting integration','{}','{"success":true,"data":[]}'],
        ['webhook_receive','Webhook Receiver','POST','/api/v1/webhook-receiver.php','Webhooks','write:webhooks','Receive external webhook payloads into ERP queue','{"event_type":"external.order"}','{"success":true,"event_id":1}'],
        ['health_check','API Health Check','GET','/api/v1/health.php','Platform','read:health','Check API availability and version','{}','{"success":true,"status":"ok"}'],
    ];
}

function installApiEndpointCatalog(PDO $pdo): int
{
    $created=0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('api_endpoint_catalog').' (endpoint_code,endpoint_name,http_method,route_path,module,required_scope,description,request_example,response_example,status) VALUES (?,?,?,?,?,?,?,?,?,"active")');
    foreach(apiEndpointDefaults() as $e){$stmt->execute($e);$created += $stmt->rowCount()>0?1:0;}
    return $created;
}

function installApiScopePolicies(PDO $pdo): int
{
    $scopes=[
        ['read:products','Read Products','Products','read','Read product catalog and stock data'],
        ['read:customers','Read Customers','Customers','read','Read customer records'],
        ['read:orders','Read Orders','Orders','read','Read order records'],
        ['read:invoices','Read Invoices','Finance','read','Read invoice and payment status'],
        ['write:webhooks','Write Webhooks','Webhooks','write','Create inbound webhook events'],
        ['write:orders','Write Orders','Orders','write','Create or update orders foundation'],
        ['read:reports','Read Reports','Analytics','read','Read report datasets'],
        ['admin:integrations','Integration Admin','Platform','admin','Manage integration settings'],
    ];
    $created=0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('api_scope_policies').' (scope_key,scope_name,module,access_level,description,status) VALUES (?,?,?,?,?,"active")');
    foreach($scopes as $s){$stmt->execute($s);$created += $stmt->rowCount()>0?1:0;}
    return $created;
}

function apiJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function apiBearerToken(): string
{
    $header=$_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if(preg_match('/Bearer\s+(.+)/i',$header,$m)){return trim($m[1]);}
    return trim((string)($_GET['token'] ?? ''));
}

function apiAuthenticateRequest(PDO $pdo, string $requiredScope = ''): array
{
    $token=apiBearerToken();
    if($token===''){apiJsonResponse(['success'=>false,'error'=>'Missing Bearer token.'],401);}
    $prefix=substr($token,0,12);
    $stmt=$pdo->prepare('SELECT * FROM '.table('api_keys').' WHERE key_prefix=? AND status="active" LIMIT 1');
    $stmt->execute([$prefix]);
    $key=$stmt->fetch();
    $ok=$key && password_verify($token,(string)$key['token_hash']);
    if(!$ok){apiJsonResponse(['success'=>false,'error'=>'Invalid API token.'],401);}
    if(!empty($key['expires_at']) && strtotime((string)$key['expires_at'])<time()){apiJsonResponse(['success'=>false,'error'=>'API token expired.'],403);}
    $scopes=array_map('trim',explode(',',(string)$key['scopes']));
    if($requiredScope!=='' && !in_array($requiredScope,$scopes,true) && !in_array('admin:integrations',$scopes,true)){apiJsonResponse(['success'=>false,'error'=>'Required scope missing: '.$requiredScope],403);}
    apiRecordUsage($pdo,(int)$key['id'],$requiredScope);
    $pdo->prepare('UPDATE '.table('api_keys').' SET last_used_at=NOW() WHERE id=?')->execute([(int)$key['id']]);
    return $key;
}

function apiRecordAccess(PDO $pdo, ?int $keyId, string $endpoint, string $method, int $statusCode): void
{
    try{
        $pdo->prepare('INSERT INTO '.table('api_access_logs').' (api_key_id,endpoint,method,status_code,ip_address,user_agent) VALUES (?,?,?,?,?,?)')->execute([$keyId,$endpoint,$method,$statusCode,$_SERVER['REMOTE_ADDR']??'',substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255)]);
    }catch(Throwable $e){}
}

function apiRecordUsage(PDO $pdo, int $apiKeyId, string $scope): void
{
    if(setting('api_rate_limit_enabled','1')!=='1'){return;}
    $scope=$scope ?: 'general';
    $stmt=$pdo->prepare('INSERT INTO '.table('api_usage_counters').' (api_key_id,scope_key,counter_date,request_count,last_request_at) VALUES (?,?,CURDATE(),1,NOW()) ON DUPLICATE KEY UPDATE request_count=request_count+1,last_request_at=NOW()');
    $stmt->execute([$apiKeyId,$scope]);
}

function createApiUsageLimit(PDO $pdo, ?int $apiKeyId, ?int $companyId, string $scope, int $limit, string $window='daily'): int
{
    $number=nextScopedDocumentNumber($pdo,'api_usage_limit',setting('api_usage_limit_prefix','APILIM'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('api_usage_limits').' (limit_number,api_key_id,company_id,scope_key,limit_window,request_limit,status,created_by) VALUES (?,?,?,?,?,?,"active",?)')->execute([$number,$apiKeyId,$companyId,$scope,$window,$limit,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function createWebhookEventTemplate(PDO $pdo, string $eventType, string $name, string $schema='{}', string $sample='{}'): int
{
    $code=nextScopedDocumentNumber($pdo,'webhook_template',setting('webhook_template_prefix','WHTPL'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('webhook_event_templates').' (template_code,event_type,template_name,payload_schema,sample_payload,status,created_by) VALUES (?,?,?,?,?,"active",?)')->execute([$code,$eventType,$name,$schema,$sample,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function queueWebhookRetry(PDO $pdo, int $eventId, ?int $subscriptionId, string $error=''): int
{
    $number=nextScopedDocumentNumber($pdo,'webhook_retry',setting('webhook_retry_prefix','WHRTY'),operationalScope($pdo));
    $minutes=max(1,(int)setting('webhook_retry_minutes','15'));
    $max=max(1,(int)setting('webhook_max_retries','3'));
    $pdo->prepare('INSERT INTO '.table('webhook_retry_queue').' (retry_number,webhook_event_id,webhook_subscription_id,next_retry_at,retry_count,max_retries,status,last_error) VALUES (?,?,?,DATE_ADD(NOW(), INTERVAL '.$minutes.' MINUTE),0,?,"queued",?)')->execute([$number,$eventId,$subscriptionId,$max,$error]);
    return (int)$pdo->lastInsertId();
}

function installIntegrationConnectorTemplates(PDO $pdo): int
{
    $templates=[
        ['WOOCOMMERCE','WooCommerce','WooCommerce','E-commerce','api_key','https://example.com/wp-json/wc/v3','order.created,product.updated','read:products,read:orders,write:orders','Sync products, orders and customers with WooCommerce storefronts.'],
        ['STRIPE','Stripe Payments','Stripe','Payments','secret_key','https://api.stripe.com/v1','payment.succeeded,payment.failed','read:payments,write:webhooks','Payment status and transaction sync foundation.'],
        ['PAYPAL','PayPal','PayPal','Payments','oauth2','https://api-m.paypal.com','payment.completed,payment.denied','read:payments,write:webhooks','PayPal payment sync foundation.'],
        ['ARAMEX','Aramex Shipping','Aramex','Shipping','api_key','https://ws.aramex.net','shipment.created,tracking.updated','read:shipments,write:shipments','Shipment booking and tracking connector foundation.'],
        ['DHL','DHL Express','DHL','Shipping','api_key','https://api-eu.dhl.com','shipment.created,tracking.updated','read:shipments,write:shipments','DHL label and tracking connector foundation.'],
        ['QUICKBOOKS','QuickBooks Online','Intuit','Accounting','oauth2','https://quickbooks.api.intuit.com','invoice.created,payment.received','read:accounting,write:accounting','Accounting export connector foundation.'],
        ['ZOHOBOOKS','Zoho Books','Zoho','Accounting','oauth2','https://www.zohoapis.com/books/v3','invoice.created,payment.received','read:accounting,write:accounting','Zoho Books export connector foundation.'],
        ['AMAZON','Amazon Marketplace','Amazon','Marketplace','oauth2','https://sellingpartnerapi-na.amazon.com','order.created,inventory.updated','read:orders,write:inventory','Marketplace order and stock sync foundation.'],
        ['NOON','Noon Marketplace','Noon','Marketplace','api_key','https://api.noon.partners','order.created,inventory.updated','read:orders,write:inventory','Noon product, order and stock sync foundation.'],
    ];
    $created=0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('integration_connector_templates').' (connector_code,connector_name,provider,category,auth_type,base_url,supported_events,default_scopes,status,description) VALUES (?,?,?,?,?,?,?,?,"available",?)');
    foreach($templates as $t){$stmt->execute($t);$created += $stmt->rowCount()>0?1:0;}
    return $created;
}

function createIntegrationFieldMapping(PDO $pdo, ?int $connectionId, string $sourceModule, string $targetModule, string $sourceField, string $targetField, string $transform='', int $required=0): int
{
    $number=nextScopedDocumentNumber($pdo,'integration_mapping',setting('integration_mapping_prefix','IMAP'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('integration_field_mappings').' (mapping_number,integration_connection_id,source_module,target_module,source_field,target_field,transform_rule,required_field,status,created_by) VALUES (?,?,?,?,?,?,?,?,"active",?)')->execute([$number,$connectionId,$sourceModule,$targetModule,$sourceField,$targetField,$transform,$required,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function recordIntegrationError(PDO $pdo, ?int $connectionId, ?int $jobId, string $type, string $message, string $severity='warning', string $payload=''): int
{
    $number=nextScopedDocumentNumber($pdo,'integration_error',setting('integration_error_prefix','IERR'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('integration_error_logs').' (error_number,integration_connection_id,integration_sync_job_id,error_type,severity,error_message,payload_json,status) VALUES (?,?,?,?,?,?,?,"open")')->execute([$number,$connectionId,$jobId,$type,$severity,$message,$payload]);
    return (int)$pdo->lastInsertId();
}

function createAccountingExportBatch(PDO $pdo, string $type, string $from, string $to): int
{
    $number=nextScopedDocumentNumber($pdo,'accounting_export_batch',setting('accounting_export_batch_prefix','AEXP'),operationalScope($pdo));
    $rows=safeAiRows($pdo,'SELECT je.* FROM '.table('journal_entries').' je WHERE je.entry_date BETWEEN '.$pdo->quote($from).' AND '.$pdo->quote($to).' ORDER BY je.entry_date ASC');
    $rowCount=count($rows);
    $debit=safeAiScalar($pdo,'SELECT COALESCE(SUM(debit),0) FROM '.table('journal_lines').' jl LEFT JOIN '.table('journal_entries').' je ON je.id=jl.journal_entry_id WHERE je.entry_date BETWEEN '.$pdo->quote($from).' AND '.$pdo->quote($to));
    $credit=safeAiScalar($pdo,'SELECT COALESCE(SUM(credit),0) FROM '.table('journal_lines').' jl LEFT JOIN '.table('journal_entries').' je ON je.id=jl.journal_entry_id WHERE je.entry_date BETWEEN '.$pdo->quote($from).' AND '.$pdo->quote($to));
    $file='accounting-export-'.$number.'.csv';
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('accounting_export_batches').' (batch_number,export_type,date_from,date_to,file_name,row_count,total_debit,total_credit,status,notes,created_by) VALUES (?,?,?,?,?,?,?,?, "ready", ?, ?)')->execute([$number,$type,$from,$to,$file,$rowCount,$debit,$credit,'Generated accounting export batch.',(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function queueMarketplaceSync(PDO $pdo, string $marketplace, string $entityType, ?int $entityId, string $direction='outbound', array $payload=[]): int
{
    $number=nextScopedDocumentNumber($pdo,'marketplace_sync',setting('marketplace_sync_prefix','MKTQ'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('marketplace_sync_queue').' (queue_number,marketplace,entity_type,entity_id,direction,payload_json,status) VALUES (?,?,?,?,?,?,"queued")')->execute([$number,$marketplace,$entityType,$entityId,$direction,json_encode($payload,JSON_UNESCAPED_SLASHES)]);
    return (int)$pdo->lastInsertId();
}


function queueWebhookEvent(PDO $pdo, string $eventType, array $payload, string $referenceType = '', ?int $referenceId = null): int
{
    $number=nextScopedDocumentNumber($pdo,'webhook_event','WHE',operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('webhook_events').' (event_number,event_type,payload_json,reference_type,reference_id,status) VALUES (?,?,?,?,?,"queued")');
    $stmt->execute([$number,$eventType,json_encode($payload,JSON_UNESCAPED_SLASHES),$referenceType,$referenceId]);
    return (int)$pdo->lastInsertId();
}

function deliverWebhookEvent(PDO $pdo, int $eventId): array
{
    $eventStmt=$pdo->prepare('SELECT * FROM '.table('webhook_events').' WHERE id=? LIMIT 1');
    $eventStmt->execute([$eventId]);
    $event=$eventStmt->fetch();
    if(!$event){throw new RuntimeException('Webhook event not found.');}
    $subs=$pdo->prepare('SELECT * FROM '.table('webhook_subscriptions').' WHERE status="active" AND event_type IN (?, "all") ORDER BY id');
    $subs->execute([$event['event_type']]);
    $subscriptions=$subs->fetchAll();
    $attempt=$pdo->prepare('INSERT INTO '.table('webhook_delivery_attempts').' (webhook_event_id,webhook_subscription_id,target_url,status,http_status,response_body,attempted_at) VALUES (?,?,?,?,?, ?, NOW())');
    $delivered=0;$failed=0;
    foreach($subscriptions as $subscription){
        $target=(string)$subscription['target_url'];
        $ok=str_starts_with($target,'https://') || str_starts_with($target,'http://localhost') || str_starts_with($target,'https://example.com');
        $status=$ok?'delivered':'failed';
        $attempt->execute([$eventId,(int)$subscription['id'],$target,$status,$ok?200:0,$ok?'Simulated delivery accepted.':'Invalid or blocked URL for safe simulated delivery.']);
        $pdo->prepare('UPDATE '.table('webhook_subscriptions').' SET last_delivery_at=NOW() WHERE id=?')->execute([(int)$subscription['id']]);
        $ok?$delivered++:$failed++;
    }
    $final=$failed>0 && $delivered===0 ? 'failed' : 'delivered';
    if(!$subscriptions){$final='queued';}
    $pdo->prepare('UPDATE '.table('webhook_events').' SET status=?,attempt_count=attempt_count+1,last_attempt_at=NOW() WHERE id=?')->execute([$final,$eventId]);
    return ['subscriptions'=>count($subscriptions),'delivered'=>$delivered,'failed'=>$failed,'status'=>$final];
}

function queueWhatsAppMessage(PDO $pdo, ?int $templateId, string $phone, string $name, string $message): int
{
    $number=nextScopedDocumentNumber($pdo,'whatsapp_queue',setting('whatsapp_queue_prefix','WA'),operationalScope($pdo));
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('whatsapp_queue').' (queue_number,template_id,recipient_phone,recipient_name,message_body,status,scheduled_at,created_by) VALUES (?,?,?,?,?,"queued",NOW(),?)');
    $stmt->execute([$number,$templateId,$phone,$name,$message,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function markWhatsAppSent(PDO $pdo, int $queueId): void
{
    $pdo->prepare('UPDATE '.table('whatsapp_queue').' SET status="sent",sent_at=NOW(),provider_message_id=? WHERE id=?')->execute(['manual-'.date('YmdHis').'-'.$queueId,$queueId]);
}

function runCommunicationRule(PDO $pdo, int $ruleId, string $referenceType = '', ?int $referenceId = null): int
{
    $ruleStmt=$pdo->prepare('SELECT * FROM '.table('communication_automation_rules').' WHERE id=? LIMIT 1');
    $ruleStmt->execute([$ruleId]);
    $rule=$ruleStmt->fetch();
    if(!$rule){throw new RuntimeException('Communication rule not found.');}
    $number=nextScopedDocumentNumber($pdo,'communication_automation_run','CARUN',operationalScope($pdo));
    $message='Rule '.$rule['rule_code'].' executed for '.$referenceType.' #'.(string)$referenceId.'. Channel: '.$rule['channel'];
    $pdo->prepare('INSERT INTO '.table('communication_automation_runs').' (communication_automation_rule_id,run_number,reference_type,reference_id,status,channel,message) VALUES (?,?,?,?, "completed", ?, ?)')->execute([(int)$rule['id'],$number,$referenceType,$referenceId,$rule['channel'],$message]);
    if($rule['channel']==='webhook'){
        queueWebhookEvent($pdo,(string)$rule['trigger_event'],['rule'=>$rule['rule_code'],'reference_type'=>$referenceType,'reference_id'=>$referenceId],$referenceType,$referenceId);
    }
    return (int)$pdo->lastInsertId();
}


function generateApiPlainToken(): array
{
    $secret='ec_' . bin2hex(random_bytes(24));
    return ['plain'=>$secret,'prefix'=>substr($secret,0,12),'hash'=>password_hash($secret,PASSWORD_DEFAULT)];
}


function permissionLabels(): array
{
    return [
        'access_erp' => 'Access employee ERP portal',
        'dashboard' => 'ERP command center',
        'manufacturing_dashboard' => 'Manufacturing dashboard',
        'bom_management' => 'BOM management',
        'work_orders' => 'Manufacturing work orders',
        'production_planning' => 'Production planning',
        'material_issue' => 'Material issue',
        'production_receipts' => 'Production receipts',
        'manufacturing_costing' => 'Manufacturing costing',
        'quality_checks' => 'Quality checks',
        'work_centers' => 'Work centers',
        'ai_assistant' => 'AI assistant',
        'smart_search' => 'Smart search',
        'predictive_alerts' => 'Predictive alerts',
        'recommendations' => 'Recommendations',
        'decision_support' => 'Decision support',
        'anomaly_detection' => 'Anomaly detection',
        'crm' => 'CRM leads',
        'crm_advanced' => 'Advanced CRM',
        'sales_pipeline' => 'Sales pipeline',
        'marketing_campaigns' => 'Marketing campaigns',
        'lead_scoring' => 'Lead scoring',
        'customer_segments' => 'Customer segments',
        'crm_automation' => 'CRM automation',
        'sales_crm_dashboard' => 'Sales CRM dashboard',
        'sales_opportunities_2' => 'Sales opportunities 2.0',
        'crm_followups' => 'CRM follow-ups',
        'quote_followups' => 'Quote follow-ups',
        'sales_forecast' => 'Sales forecast',
        'campaign_automation_2' => 'Campaign automation 2.0',
        'customers' => 'ERP customers',
        'quotations' => 'Quotations',
        'invoices' => 'Invoices',
        'finance_automation_dashboard' => 'Finance automation dashboard',
        'recurring_journals' => 'Recurring journals',
        'budgeting' => 'Budgeting',
        'cash_flow_forecast' => 'Cash flow forecast',
        'ar_ap_aging' => 'AR/AP aging',
        'supplier_payment_runs' => 'Supplier payment runs',
        'tax_automation_2' => 'Tax automation 2.0',
        'finance' => 'Finance',
        'accounting' => 'Accounting Core',
        'financial_close' => 'Financial close',
        'bank_reconciliation' => 'Bank reconciliation',
        'fixed_assets' => 'Fixed assets',
        'tax_filing' => 'Tax filing',
        'audit_controls' => 'Audit controls',
        'org_structure' => 'Companies, branches & warehouses',
        'stock_transfers' => 'Stock transfers & inter-branch approvals',
        'inventory_valuation' => 'Inventory valuation & in-transit stock',
        'intercompany' => 'Intercompany stock value control',
        'consolidation' => 'Branch & company financial consolidation',
        'approvals' => 'Approval center & workflow decisions',
        'approval_rules' => 'Approval rules & internal control settings',
        'procurement_dashboard' => 'Procurement dashboard',
        'supplier_onboarding' => 'Supplier onboarding',
        'supplier_scorecards' => 'Supplier scorecards',
        'supplier_price_lists' => 'Supplier price lists',
        'supplier_contracts' => 'Supplier contracts',
        'rfq_comparison' => 'RFQ comparison',
        'purchase_requisitions' => 'Purchase requisitions',
        'goods_receipts' => 'Goods receipt notes',
        'supplier_invoices' => 'Supplier invoices & three-way matching',
        'sales_orders' => 'Sales orders',
        'delivery_notes' => 'Delivery notes & fulfillment',
        'returns_rma' => 'Returns & RMA',
        'credit_control' => 'Customer credit control',
        'document_attachments' => 'ERP document attachments',
        'cost_centers' => 'Cost centers',
        'job_cards' => 'Job cards / workshop orders',
        'technician_timesheets' => 'Technician timesheets',
        'service_contracts' => 'Service contracts / AMC',
        'warranty_claims' => 'Warranty claims',
        'projects' => 'Projects and project costing',
        'budget_control' => 'Budget control',
        'executive_bi' => 'Executive BI dashboards',
        'advanced_reporting' => 'Advanced reporting',
        'kpi_builder' => 'KPI builder',
        'scheduled_reports' => 'Scheduled reports',
        'report_exports' => 'Report exports',
        'management_dashboards' => 'Management dashboards',
        'report_builder' => 'Report builder & analytics exports',
        'data_import_export' => 'Data import / export',
        'notifications' => 'Notifications',
        'api_keys' => 'API keys & integrations',
        'api_marketplace' => 'API marketplace',
        'integrations' => 'External integrations',
        'webhooks' => 'Webhooks',
        'whatsapp_automation' => 'WhatsApp automation',
        'communication_automation' => 'Communication automation',
        'developer_docs' => 'Developer docs',
        'audit_trail' => 'Audit trail & compliance',
        'security_center' => 'Security center',
        'backup_restore' => 'Backup & restore',
        'system_health' => 'System health checks',
        'error_logs' => 'System error logs',
        'cron_runner' => 'Cron automation runner',
        'email_templates' => 'Email templates',
        'dashboard_widgets' => 'Dashboard widgets',
        'module_bundle_manager' => 'Module bundle manager',
        'documentation_center' => 'Documentation center',
        'training_center' => 'Training center',
        'demo_credentials' => 'Demo credentials',
        'commercial_packaging' => 'Commercial packaging',
        'client_onboarding_checklist' => 'Client onboarding checklist',
        'feature_comparison_sheet' => 'Feature comparison sheet',
        'sales_brochure_builder' => 'Sales brochure builder',
        'handover_center' => 'Handover center',
        'production_hardening_dashboard' => 'Production hardening dashboard',
        'system_repair_center' => 'System repair center',
        'database_migration_updater' => 'Database migration updater',
        'installer_health_check' => 'Installer health check',
        'demo_data_manager' => 'Demo data manager',
        'permission_repair' => 'Permission repair',
        'settings_repair' => 'Settings repair',
        'table_column_checker' => 'Table and column checker',
        'production_error_log_viewer' => 'Production error log viewer',
        'deployment_checklist' => 'Deployment checklist',
        'migration_center' => 'Migration center',
        'subscription_plans' => 'Subscription plans',
        'license_center' => 'License center',
        'update_center' => 'Update center',
        'tenant_usage' => 'Tenant usage',
        'customer_portal_admin' => 'Customer portal admin',
        'customer_portal_dashboard' => 'Customer portal dashboard',
        'customer_documents' => 'Customer documents',
        'customer_feedback' => 'Customer feedback',
        'customer_announcements' => 'Customer announcements',
        'customer_disputes' => 'Customer disputes and promises',
        'vendor_portal_admin' => 'Vendor portal admin',
        'technician_portal' => 'Technician portal',
        'mobile_erp' => 'Mobile ERP',
        'field_dispatch' => 'Field dispatch',
        'field_service_routes' => 'Field service routes',
        'offline_job_cards' => 'Offline job cards',
        'barcode_qr' => 'Barcode / QR',
        'mobile_parts_usage' => 'Mobile parts usage',
        'customer_signoff' => 'Customer sign-off',
        'technician_checklists' => 'Technician checklists',
        'rfq_management' => 'RFQ management',
        'tender_management' => 'Procurement tenders',
        'workflow_builder_2' => 'Workflow Builder 2.0',
        'workflow_run_history_2' => 'Workflow run history 2.0',
        'workflow_approval_automation' => 'Approval automation',
        'workflow_templates_2' => 'Workflow templates 2.0',
        'workflow_automation' => 'Workflow automation',
        'inventory' => 'Inventory',
        'warehouse_dashboard' => 'Warehouse dashboard',
        'bin_locations' => 'Bin locations',
        'lot_serial_tracking' => 'Lot & serial tracking',
        'stock_counts' => 'Stock counts',
        'inventory_adjustments' => 'Inventory adjustments',
        'picking_packing' => 'Picking & packing',
        'warehouse_dispatch' => 'Warehouse dispatch',
        'replenishment' => 'Replenishment',
        'suppliers' => 'Suppliers',
        'purchase_orders' => 'Purchase orders',
        'hr_dashboard_2' => 'HR 2.0 dashboard',
        'employee_contracts' => 'Employee contracts',
        'shift_scheduling' => 'Shift scheduling',
        'leave_balances' => 'Leave balances',
        'employee_loans' => 'Employee loans',
        'employee_self_service_admin' => 'Employee self-service admin',
        'hr' => 'Employees & leave',
        'attendance' => 'Attendance',
        'payroll' => 'Payroll',
        'employee_expenses' => 'Employee expenses',
        'commissions' => 'Commissions',
        'performance_management' => 'Performance management',
        'reports' => 'Reports',
        'activity_log' => 'Activity log',
        'online_sales_orders' => 'Website orders & bookings',
        'online_products' => 'Website products & categories',
    ];
}

function rolePermissions(?array $user = null): array
{
    $user = $user ?: currentUser();
    if (!$user) {
        return [];
    }
    if (($user['role'] ?? '') === 'admin') {
        return array_fill_keys(array_keys(permissionLabels()), true);
    }
    if (($user['role'] ?? '') !== 'employee' || empty($user['erp_role_id'])) {
        return [];
    }
    try {
        $stmt = getDB()->prepare('SELECT permissions FROM ' . table('erp_roles') . ' WHERE id = ? AND active = 1 LIMIT 1');
        $stmt->execute([(int)$user['erp_role_id']]);
        $json = $stmt->fetchColumn();
        $permissions = json_decode((string)$json, true);
        return is_array($permissions) ? $permissions : [];
    } catch (Throwable $e) {
        return [];
    }
}

function hasPermission(string $permission, ?array $user = null): bool
{
    $user = $user ?: currentUser();
    if (!$user) {
        return false;
    }

    // Permission hotfix: the installer-created admin must have full access.
    // Previously admin users were still limited by selected modules, which caused
    // "You do not have permission to open this area." on admin Website Sales pages.
    if (($user['role'] ?? '') === 'admin') {
        return true;
    }

    if (($user['role'] ?? '') !== 'employee' || (int)($user['can_login_erp'] ?? 0) !== 1) {
        return false;
    }
    if ($permission === 'access_erp') {
        return permissionAllowedBySelectedModules($permission);
    }
    $permissions = rolePermissions($user);
    return !empty($permissions[$permission]) && permissionAllowedBySelectedModules($permission);
}

function adminGuard(): void
{
    $user = currentUser();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        redirect(SITE_URL . '/employee/login.php');
    }
}

function permissionGuard(string $permission): void
{
    $user = currentUser();
    if (!$user) {
        redirect(SITE_URL . '/employee/login.php');
    }

    // Permission hotfix: admin bypasses all module/page permission checks.
    if (($user['role'] ?? '') === 'admin') {
        return;
    }

    if (!hasPermission($permission, $user)) {
        flash('error', 'You do not have permission to open this area.');
        redirect(SITE_URL . '/employee/dashboard.php');
    }
}

function erpGuard(string $permission = 'access_erp'): void
{
    permissionGuard($permission);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        requireLicenseMutationAllowed();
    }
}

function employeePortalGuard(): void
{
    $user = currentUser();
    if (!$user) {
        redirect(SITE_URL . '/employee/login.php');
    }

    // Permission hotfix: allow admin to open employee/ERP portal pages.
    if (($user['role'] ?? '') === 'admin') {
        return;
    }

    if (!hasPermission('access_erp', $user)) {
        redirect(SITE_URL . '/employee/login.php');
    }
}


function protectedDeveloperRoleSlug(): string
{
    return 'module_bundle_developer';
}

function isProtectedDeveloperRole(array $role): bool
{
    return strtolower(trim((string)($role['slug'] ?? ''))) === protectedDeveloperRoleSlug()
        || strtolower(trim((string)($role['name'] ?? ''))) === 'developer module controller';
}

function isProtectedDeveloperRoleId(PDO $pdo, int $roleId): bool
{
    if ($roleId <= 0) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT slug,name FROM ' . table('erp_roles') . ' WHERE id=? LIMIT 1');
        $stmt->execute([$roleId]);
        $role = $stmt->fetch();
        return $role ? isProtectedDeveloperRole($role) : false;
    } catch (Throwable $e) {
        return false;
    }
}

function moduleBundleDeveloperEmail(): string
{
    return strtolower(trim((string)setting('module_bundle_developer_email', '3b@me.com')));
}

function isModuleBundleDeveloper(?array $user = null): bool
{
    $user = $user ?: currentUser();
    if (!$user || ($user['status'] ?? 'active') !== 'active') {
        return false;
    }
    return strtolower(trim((string)($user['email'] ?? ''))) === moduleBundleDeveloperEmail();
}

function moduleBundleDeveloperGuard(): void
{
    $user = currentUser();
    if (!$user) {
        redirect(SITE_URL . '/employee/login.php');
    }
    if (!isModuleBundleDeveloper($user)) {
        flash('error', 'Module Bundle Manager is developer-only. Admin users cannot change this page.');
        redirect(SITE_URL . '/employee/dashboard.php');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }
    return null;
}

function logActivity(PDO $pdo, string $module, string $action, string $description, ?string $referenceType = null, ?int $referenceId = null): void
{
    try {
        $user = currentUser();
        $scope = operationalScope($pdo, $user);
        $stmt = $pdo->prepare('INSERT INTO ' . table('activity_log') . ' (company_id,branch_id,actor_user_id,module,action,description,reference_type,reference_id) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([
            (int)($scope['company_id'] ?? 0) ?: null,
            (int)($scope['branch_id'] ?? 0) ?: null,
            $user['id'] ?? null,
            $module,
            $action,
            $description,
            $referenceType,
            $referenceId,
        ]);
    } catch (Throwable $e) {
    }
}

function statusTone(string $status): string
{
    return match (strtolower(trim($status))) {
        'paid', 'received', 'approved', 'accepted', 'won', 'active', 'converted', 'settled', 'delivered', 'fulfilled', 'matched', 'posted', 'credited', 'passed', 'overridden' => 'success',
        'partial', 'sent', 'qualified', 'contacted', 'partially_received', 'pending', 'pending_approval', 'dispatched', 'in_transit', 'variance' => 'warning text-dark',
        'recognized' => 'primary',
        'draft', 'new', 'open', 'queued', 'not_required' => 'secondary',
        'warning', 'degraded', 'created' => 'warning text-dark',
        'rejected', 'lost', 'cancelled', 'inactive', 'overdue', 'blocked', 'hold', 'zero_limit', 'exceeded', 'fail', 'failed', 'open' => 'danger',
        'resolved', 'completed', 'closed', 'ok', 'success', 'valid', 'trial' => 'success',
        'expired', 'suspended', 'revoked' => 'danger',
        'available', 'monitor', 'open', 'invited', 'submitted' => 'primary',
        'awarded', 'hot', 'won', 'converted' => 'success',
        'warm', 'targeted', 'responded' => 'warning text-dark',
        'cold', 'draft', 'none' => 'secondary',
        'present', 'earned', 'paid', 'posted', 'filed', 'reconciled', 'depreciated' => 'success',
        'unmatched', 'calculated', 'scheduled', 'queued', 'testing', 'info', 'planned', 'required', 'pending', 'calculated', 'draft', 'ready', 'partial', 'counted', 'in_progress', 'scheduled', 'open', 'waiting' => 'warning text-dark',
        'delivered', 'synced', 'signed', 'arrived', 'completed_sync' => 'success',
        'accepted', 'enroute', 'in_progress_mobile' => 'primary',
        'above_target' => 'success',
        'below_target' => 'danger',
        'absent', 'declined', 'non_compliant', 'lost', 'inactive' => 'danger',
        default => 'primary',
    };
}

function daysUntil(?string $date): ?int
{
    if (!$date) {
        return null;
    }
    try {
        $target = new DateTimeImmutable($date);
        $today = new DateTimeImmutable(date('Y-m-d'));
        return (int)$today->diff($target)->format('%r%a');
    } catch (Throwable $e) {
        return null;
    }
}

function dueLabel(?string $date): string
{
    $days = daysUntil($date);
    if ($days === null) {
        return 'No date';
    }
    if ($days < 0) {
        return abs($days) . ' day(s) overdue';
    }
    if ($days === 0) {
        return 'Due today';
    }
    return 'Due in ' . $days . ' day(s)';
}

function nextDocumentNumber(PDO $pdo, string $tableName, string $prefix): string
{
    $documentType=documentTypeFromTable($tableName);
    if($documentType!==''){
        return nextScopedDocumentNumber($pdo,$documentType,$prefix);
    }
    return nextNumber($pdo, $tableName, $prefix);
}

function accountingAccountId(PDO $pdo, string $code): int
{
    $stmt = $pdo->prepare('SELECT id FROM ' . table('accounts') . ' WHERE account_code=? AND active=1 LIMIT 1');
    $stmt->execute([$code]);
    $id = (int)$stmt->fetchColumn();
    if ($id <= 0) {
        throw new RuntimeException('Required accounting account ' . $code . ' is missing or inactive.');
    }
    return $id;
}

function accountingPeriodIsOpen(PDO $pdo, string $date): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . table('accounting_periods') . ' WHERE start_date<=? AND end_date>=? AND status="open"');
    $stmt->execute([$date, $date]);
    return (int)$stmt->fetchColumn() > 0;
}

function accountingEntryExists(PDO $pdo, string $referenceType, int $referenceId): bool
{
    if ($referenceId <= 0) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . table('journal_entries') . ' WHERE reference_type=? AND reference_id=?');
    $stmt->execute([$referenceType, $referenceId]);
    return (int)$stmt->fetchColumn() > 0;
}

function createAccountingJournal(PDO $pdo, array $entry, array $lines, bool $postNow = true): int
{
    $date = trim((string)($entry['entry_date'] ?? date('Y-m-d')));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new RuntimeException('A valid accounting entry date is required.');
    }
    if (!accountingPeriodIsOpen($pdo, $date)) {
        throw new RuntimeException('The accounting period is closed or missing for ' . $date . '.');
    }

    $normalized = [];
    $debitTotal = 0.0;
    $creditTotal = 0.0;
    foreach ($lines as $line) {
        $accountId = isset($line['account_id']) ? (int)$line['account_id'] : accountingAccountId($pdo, (string)($line['account_code'] ?? ''));
        $debit = round(max(0, (float)($line['debit'] ?? 0)), 2);
        $credit = round(max(0, (float)($line['credit'] ?? 0)), 2);
        if ($accountId <= 0 || ($debit <= 0 && $credit <= 0)) {
            continue;
        }
        if ($debit > 0 && $credit > 0) {
            throw new RuntimeException('A journal line cannot contain both debit and credit values.');
        }
        $normalized[] = [
            'account_id' => $accountId,
            'description' => trim((string)($line['description'] ?? '')),
            'debit' => $debit,
            'credit' => $credit,
        ];
        $debitTotal += $debit;
        $creditTotal += $credit;
    }

    $debitTotal = round($debitTotal, 2);
    $creditTotal = round($creditTotal, 2);
    if (count($normalized) < 2 || $debitTotal <= 0 || $creditTotal <= 0) {
        throw new RuntimeException('A journal entry needs at least two valid lines.');
    }
    if (abs($debitTotal - $creditTotal) > 0.01) {
        throw new RuntimeException('Journal entry is not balanced. Debit and credit totals must match.');
    }

    $scope = operationalScope($pdo);
    $scope['company_id']=(int)($entry['company_id'] ?? $scope['company_id'] ?? 0);
    $scope['branch_id']=(int)($entry['branch_id'] ?? $scope['branch_id'] ?? 0);
    $journalNumber = trim((string)($entry['journal_number'] ?? ''));
    if ($journalNumber === '') {
        $journalNumber = nextScopedDocumentNumber($pdo, 'journal', (string)setting('journal_prefix', 'JRN'), $scope);
    }

    $companyId = (int)($scope['company_id'] ?? 0) ?: null;
    $branchId = (int)($scope['branch_id'] ?? 0) ?: null;
    $stmt = $pdo->prepare('INSERT INTO ' . table('journal_entries') . ' (company_id,branch_id,journal_number,entry_date,reference_type,reference_id,memo,status,total_debit,total_credit,posted_at,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $companyId,
        $branchId,
        $journalNumber,
        $date,
        trim((string)($entry['reference_type'] ?? 'manual')) ?: 'manual',
        !empty($entry['reference_id']) ? (int)$entry['reference_id'] : null,
        trim((string)($entry['memo'] ?? '')),
        $postNow ? 'posted' : 'draft',
        $debitTotal,
        $creditTotal,
        $postNow ? date('Y-m-d H:i:s') : null,
        !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
    ]);
    $journalId = (int)$pdo->lastInsertId();

    $lineStmt = $pdo->prepare('INSERT INTO ' . table('journal_lines') . ' (journal_entry_id,account_id,description,debit,credit) VALUES (?,?,?,?,?)');
    foreach ($normalized as $line) {
        $lineStmt->execute([$journalId, $line['account_id'], $line['description'], $line['debit'], $line['credit']]);
    }

    logActivity($pdo, 'Accounting', $postNow ? 'journal_posted' : 'journal_draft_created', 'Journal ' . $journalNumber . ' created.', 'journal_entry', $journalId);
    return $journalId;
}

function postDraftJournal(PDO $pdo, int $journalId): void
{
    $stmt = $pdo->prepare('SELECT * FROM ' . table('journal_entries') . ' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$journalId]);
    $entry = $stmt->fetch();
    if (!$entry) {
        throw new RuntimeException('Journal entry not found.');
    }
    if ($entry['status'] !== 'draft') {
        throw new RuntimeException('Only draft journals can be posted.');
    }
    if (!accountingPeriodIsOpen($pdo, (string)$entry['entry_date'])) {
        throw new RuntimeException('The accounting period is closed for this entry date.');
    }
    if (abs((float)$entry['total_debit'] - (float)$entry['total_credit']) > 0.01) {
        throw new RuntimeException('Journal entry is not balanced.');
    }
    $pdo->prepare('UPDATE ' . table('journal_entries') . ' SET status="posted",posted_at=NOW() WHERE id=?')->execute([$journalId]);
    logActivity($pdo, 'Accounting', 'journal_posted', 'Draft journal ' . (string)$entry['journal_number'] . ' posted.', 'journal_entry', $journalId);
}

function postInvoiceAccounting(PDO $pdo, int $invoiceId): ?int
{
    if ($invoiceId <= 0 || accountingEntryExists($pdo, 'invoice', $invoiceId)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM ' . table('invoices') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch();
    if (!$invoice) {
        throw new RuntimeException('Invoice not found for accounting post.');
    }
    $total = round((float)$invoice['total'], 2);
    $tax = round(max(0, (float)$invoice['tax']), 2);
    $revenue = round(max(0, $total - $tax), 2);
    if ($total <= 0) {
        return null;
    }
    $lines = [
        ['account_code'=>'1100','description'=>'Accounts receivable - ' . (string)$invoice['invoice_number'],'debit'=>$total,'credit'=>0],
        ['account_code'=>'4000','description'=>'Sales revenue - ' . (string)$invoice['invoice_number'],'debit'=>0,'credit'=>$revenue],
    ];
    if ($tax > 0) {
        $lines[] = ['account_code'=>'2100','description'=>'VAT output - ' . (string)$invoice['invoice_number'],'debit'=>0,'credit'=>$tax];
    }
    $journalId = createAccountingJournal($pdo, [
        'company_id' => (int)($invoice['company_id'] ?? 0),
        'branch_id' => (int)($invoice['branch_id'] ?? 0),
        'entry_date' => substr((string)($invoice['approved_at'] ?: $invoice['created_at']), 0, 10),
        'reference_type' => 'invoice',
        'reference_id' => $invoiceId,
        'memo' => 'Automatic invoice accounting for ' . (string)$invoice['invoice_number'],
    ], $lines, true);
    logActivity($pdo, 'Accounting', 'invoice_accounted', 'Invoice ' . (string)$invoice['invoice_number'] . ' posted to the ledger.', 'journal_entry', $journalId);
    return $journalId;
}

function postPaymentAccounting(PDO $pdo, int $paymentId): ?int
{
    if ($paymentId <= 0 || accountingEntryExists($pdo, 'payment', $paymentId)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT p.*,i.invoice_number FROM ' . table('payments') . ' p LEFT JOIN ' . table('invoices') . ' i ON i.id=p.invoice_id WHERE p.id=? LIMIT 1');
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();
    if (!$payment) {
        throw new RuntimeException('Payment not found for accounting post.');
    }
    $amount = round(max(0, (float)$payment['amount']), 2);
    if ($amount <= 0) {
        return null;
    }
    $reference = (string)($payment['payment_number'] ?? ('Payment #' . $paymentId));
    $journalId = createAccountingJournal($pdo, [
        'company_id' => (int)($payment['company_id'] ?? 0),
        'branch_id' => (int)($payment['branch_id'] ?? 0),
        'entry_date' => substr((string)($payment['paid_at'] ?: $payment['created_at']), 0, 10),
        'reference_type' => 'payment',
        'reference_id' => $paymentId,
        'memo' => 'Automatic payment accounting for ' . $reference,
    ], [
        ['account_code'=>'1000','description'=>'Cash received - ' . $reference,'debit'=>$amount,'credit'=>0],
        ['account_code'=>'1100','description'=>'Receivable settlement - ' . $reference,'debit'=>0,'credit'=>$amount],
    ], true);
    logActivity($pdo, 'Accounting', 'payment_accounted', 'Payment ' . $reference . ' posted to the ledger.', 'journal_entry', $journalId);
    return $journalId;
}

function postExpenseAccounting(PDO $pdo, int $expenseId): ?int
{
    if ($expenseId <= 0 || accountingEntryExists($pdo, 'expense', $expenseId)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM ' . table('expenses') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$expenseId]);
    $expense = $stmt->fetch();
    if (!$expense) {
        throw new RuntimeException('Expense not found for accounting post.');
    }
    $amount = round(max(0, (float)$expense['amount']), 2);
    $tax = round(max(0, (float)$expense['tax']), 2);
    $total = round(max(0, (float)$expense['total']), 2);
    $amountPaid = round(max(0, (float)($expense['amount_paid'] ?? 0)), 2);
    $balanceDue = round(max(0, (float)($expense['balance_due'] ?? max(0, $total-$amountPaid))), 2);
    if ($total <= 0) {
        return null;
    }
    $reference = (string)($expense['expense_number'] ?? ('Expense #' . $expenseId));
    $lines = [];
    if ($amount > 0) {
        $lines[] = ['account_code'=>'6100','description'=>'Operating expense - ' . $reference,'debit'=>$amount,'credit'=>0];
    }
    if ($tax > 0) {
        $lines[] = ['account_code'=>'1300','description'=>'VAT input - ' . $reference,'debit'=>$tax,'credit'=>0];
    }
    if ($amountPaid > 0) {
        $lines[] = ['account_code'=>'1000','description'=>'Expense cash settlement - ' . $reference,'debit'=>0,'credit'=>$amountPaid];
    }
    if ($balanceDue > 0) {
        $lines[] = ['account_code'=>'2000','description'=>'Expense payable - ' . $reference,'debit'=>0,'credit'=>$balanceDue];
    }
    if (!$lines || abs(($amount+$tax)-($amountPaid+$balanceDue)) > 0.01) {
        throw new RuntimeException('Expense accounting split is not balanced.');
    }

    $journalId = createAccountingJournal($pdo, [
        'company_id' => (int)($expense['company_id'] ?? 0),
        'branch_id' => (int)($expense['branch_id'] ?? 0),
        'entry_date' => (string)($expense['expense_date'] ?: substr((string)$expense['created_at'], 0, 10)),
        'reference_type' => 'expense',
        'reference_id' => $expenseId,
        'memo' => 'Automatic expense accounting for ' . $reference,
    ], $lines, true);
    logActivity($pdo, 'Accounting', 'expense_accounted', 'Expense ' . $reference . ' posted to the ledger.', 'journal_entry', $journalId);
    return $journalId;
}

function reverseJournal(PDO $pdo, int $journalId, string $reversalDate, string $memo = ''): int
{
    $stmt = $pdo->prepare('SELECT * FROM ' . table('journal_entries') . ' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$journalId]);
    $entry = $stmt->fetch();
    if (!$entry) {
        throw new RuntimeException('Journal entry not found.');
    }
    if ($entry['status'] !== 'posted') {
        throw new RuntimeException('Only posted journals can be reversed.');
    }
    if (!empty($entry['reversed_entry_id'])) {
        throw new RuntimeException('This journal has already been reversed.');
    }
    if (!accountingPeriodIsOpen($pdo, $reversalDate)) {
        throw new RuntimeException('The reversal date belongs to a closed or missing accounting period.');
    }

    $lineStmt = $pdo->prepare('SELECT * FROM ' . table('journal_lines') . ' WHERE journal_entry_id=? ORDER BY id ASC');
    $lineStmt->execute([$journalId]);
    $lines = $lineStmt->fetchAll();
    if (!$lines) {
        throw new RuntimeException('Cannot reverse a journal without lines.');
    }

    $reversalLines = [];
    foreach ($lines as $line) {
        $reversalLines[] = [
            'account_id' => (int)$line['account_id'],
            'description' => 'Reversal of ' . (string)$entry['journal_number'] . ' - ' . trim((string)$line['description']),
            'debit' => (float)$line['credit'],
            'credit' => (float)$line['debit'],
        ];
    }

    $reversalId = createAccountingJournal($pdo, [
        'entry_date' => $reversalDate,
        'reference_type' => 'journal_reversal',
        'reference_id' => $journalId,
        'memo' => trim($memo) !== '' ? trim($memo) : 'Reversal of journal ' . (string)$entry['journal_number'],
    ], $reversalLines, true);

    $pdo->prepare('UPDATE ' . table('journal_entries') . ' SET status="reversed",reversed_entry_id=? WHERE id=?')->execute([$reversalId,$journalId]);
    logActivity($pdo, 'Accounting', 'journal_reversed', 'Journal ' . (string)$entry['journal_number'] . ' reversed through journal #' . $reversalId . '.', 'journal_entry', $journalId);
    return $reversalId;
}

function closeAccountingPeriod(PDO $pdo, int $periodId): void
{
    $stmt = $pdo->prepare('SELECT * FROM ' . table('accounting_periods') . ' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$periodId]);
    $period = $stmt->fetch();
    if (!$period) {
        throw new RuntimeException('Accounting period not found.');
    }
    if ($period['status'] === 'closed') {
        throw new RuntimeException('Accounting period is already closed.');
    }

    $draftStmt = $pdo->prepare('SELECT COUNT(*) FROM ' . table('journal_entries') . ' WHERE status="draft" AND entry_date BETWEEN ? AND ?');
    $draftStmt->execute([$period['start_date'], $period['end_date']]);
    if ((int)$draftStmt->fetchColumn() > 0) {
        throw new RuntimeException('Close or post all draft journals inside this period before closing it.');
    }

    $pdo->prepare('UPDATE ' . table('accounting_periods') . ' SET status="closed" WHERE id=?')->execute([$periodId]);
    logActivity($pdo, 'Accounting', 'period_closed', 'Accounting period ' . (string)$period['period_name'] . ' closed.', 'accounting_period', $periodId);
}

function reopenAccountingPeriod(PDO $pdo, int $periodId): void
{
    $stmt = $pdo->prepare('SELECT * FROM ' . table('accounting_periods') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$periodId]);
    $period = $stmt->fetch();
    if (!$period) {
        throw new RuntimeException('Accounting period not found.');
    }
    if ($period['status'] === 'open') {
        throw new RuntimeException('Accounting period is already open.');
    }
    $pdo->prepare('UPDATE ' . table('accounting_periods') . ' SET status="open" WHERE id=?')->execute([$periodId]);
    logActivity($pdo, 'Accounting', 'period_reopened', 'Accounting period ' . (string)$period['period_name'] . ' reopened.', 'accounting_period', $periodId);
}

function journalStatusCounts(PDO $pdo, string $startDate, string $endDate): array
{
    $stmt = $pdo->prepare('SELECT status,COUNT(*) total FROM ' . table('journal_entries') . ' WHERE entry_date BETWEEN ? AND ? GROUP BY status');
    $stmt->execute([$startDate,$endDate]);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return [
        'draft' => (int)($rows['draft'] ?? 0),
        'posted' => (int)($rows['posted'] ?? 0),
        'reversed' => (int)($rows['reversed'] ?? 0),
    ];
}

function recalculateInvoiceBalance(PDO $pdo, int $invoiceId): void
{
    $stmt = $pdo->prepare('SELECT id,total,amount_paid,credit_amount,status FROM ' . table('invoices') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch();
    if (!$invoice) {
        return;
    }
    $creditStmt = $pdo->prepare('SELECT COALESCE(SUM(total),0) FROM ' . table('credit_notes') . ' WHERE invoice_id=? AND status="approved"');
    $creditStmt->execute([$invoiceId]);
    $creditAmount = round((float)$creditStmt->fetchColumn(), 2);
    $balance = round(max(0, (float)$invoice['total'] - (float)$invoice['amount_paid'] - $creditAmount), 2);
    $status = (string)$invoice['status'];
    if (!in_array($status, ['cancelled','draft'], true)) {
        if ($balance <= 0) {
            $status = 'paid';
        } elseif ((float)$invoice['amount_paid'] > 0 || $creditAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'approved';
        }
    }
    $pdo->prepare('UPDATE ' . table('invoices') . ' SET credit_amount=?,balance_due=?,status=?,paid_at=CASE WHEN ?<=0 THEN COALESCE(paid_at,NOW()) ELSE paid_at END WHERE id=?')
        ->execute([$creditAmount,$balance,$status,$balance,$invoiceId]);
}

function recalculateExpenseBalance(PDO $pdo, int $expenseId): void
{
    $stmt = $pdo->prepare('SELECT id,total,amount_paid,payment_status FROM ' . table('expenses') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$expenseId]);
    $expense = $stmt->fetch();
    if (!$expense) {
        return;
    }
    $debitStmt = $pdo->prepare('SELECT COALESCE(SUM(total),0) FROM ' . table('debit_notes') . ' WHERE expense_id=? AND status="approved"');
    $debitStmt->execute([$expenseId]);
    $debitAmount = round((float)$debitStmt->fetchColumn(), 2);
    $balance = round(max(0, (float)$expense['total'] - (float)$expense['amount_paid'] - $debitAmount), 2);
    $status = $balance <= 0 ? 'paid' : ((float)$expense['amount_paid'] > 0 || $debitAmount > 0 ? 'partial' : 'pending');
    $pdo->prepare('UPDATE ' . table('expenses') . ' SET balance_due=?,payment_status=? WHERE id=?')->execute([$balance,$status,$expenseId]);
}

function postCreditNoteAccounting(PDO $pdo, int $creditNoteId): ?int
{
    if ($creditNoteId <= 0 || accountingEntryExists($pdo, 'credit_note', $creditNoteId)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM ' . table('credit_notes') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$creditNoteId]);
    $note = $stmt->fetch();
    if (!$note) {
        throw new RuntimeException('Credit note not found for accounting post.');
    }
    $subtotal = round(max(0, (float)$note['subtotal']), 2);
    $tax = round(max(0, (float)$note['tax']), 2);
    $total = round(max(0, (float)$note['total']), 2);
    if ($total <= 0) {
        return null;
    }
    $reference = (string)$note['credit_note_number'];
    $lines = [];
    if ($subtotal > 0) {
        $lines[] = ['account_code'=>'4000','description'=>'Sales credit note - ' . $reference,'debit'=>$subtotal,'credit'=>0];
    }
    if ($tax > 0) {
        $lines[] = ['account_code'=>'2100','description'=>'VAT output reversal - ' . $reference,'debit'=>$tax,'credit'=>0];
    }
    $lines[] = ['account_code'=>'1100','description'=>'Accounts receivable credit - ' . $reference,'debit'=>0,'credit'=>$total];

    $journalId = createAccountingJournal($pdo, [
        'entry_date' => (string)($note['issue_date'] ?: substr((string)$note['created_at'],0,10)),
        'reference_type' => 'credit_note',
        'reference_id' => $creditNoteId,
        'memo' => 'Automatic credit note accounting for ' . $reference,
    ], $lines, true);
    logActivity($pdo, 'Accounting', 'credit_note_accounted', 'Credit note ' . $reference . ' posted to the ledger.', 'journal_entry', $journalId);
    return $journalId;
}

function postDebitNoteAccounting(PDO $pdo, int $debitNoteId): ?int
{
    if ($debitNoteId <= 0 || accountingEntryExists($pdo, 'debit_note', $debitNoteId)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM ' . table('debit_notes') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$debitNoteId]);
    $note = $stmt->fetch();
    if (!$note) {
        throw new RuntimeException('Debit note not found for accounting post.');
    }
    $subtotal = round(max(0, (float)$note['subtotal']), 2);
    $tax = round(max(0, (float)$note['tax']), 2);
    $total = round(max(0, (float)$note['total']), 2);
    if ($total <= 0) {
        return null;
    }
    $reference = (string)$note['debit_note_number'];
    $lines = [
        ['account_code'=>'2000','description'=>'Accounts payable debit note - ' . $reference,'debit'=>$total,'credit'=>0],
    ];
    if ($subtotal > 0) {
        $lines[] = ['account_code'=>'6100','description'=>'Expense reversal - ' . $reference,'debit'=>0,'credit'=>$subtotal];
    }
    if ($tax > 0) {
        $lines[] = ['account_code'=>'1300','description'=>'VAT input reversal - ' . $reference,'debit'=>0,'credit'=>$tax];
    }

    $journalId = createAccountingJournal($pdo, [
        'entry_date' => (string)($note['issue_date'] ?: substr((string)$note['created_at'],0,10)),
        'reference_type' => 'debit_note',
        'reference_id' => $debitNoteId,
        'memo' => 'Automatic supplier debit note accounting for ' . $reference,
    ], $lines, true);
    logActivity($pdo, 'Accounting', 'debit_note_accounted', 'Debit note ' . $reference . ' posted to the ledger.', 'journal_entry', $journalId);
    return $journalId;
}

function vatLedgerSummary(PDO $pdo, string $from, string $to): array
{
    $sql = 'SELECT a.account_code,COALESCE(SUM(CASE WHEN je.id IS NOT NULL THEN jl.debit ELSE 0 END),0) debits,COALESCE(SUM(CASE WHEN je.id IS NOT NULL THEN jl.credit ELSE 0 END),0) credits
            FROM ' . table('accounts') . ' a
            LEFT JOIN ' . table('journal_lines') . ' jl ON jl.account_id=a.id
            LEFT JOIN ' . table('journal_entries') . ' je ON je.id=jl.journal_entry_id AND je.status IN ("posted","reversed") AND je.entry_date BETWEEN ? AND ?
            WHERE a.account_code IN ("1300","2100")
            GROUP BY a.id,a.account_code';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$from,$to]);
    $rows = $stmt->fetchAll();
    $inputVat = 0.0;
    $outputVat = 0.0;
    foreach ($rows as $row) {
        if ($row['account_code'] === '1300') {
            $inputVat = (float)$row['debits'] - (float)$row['credits'];
        }
        if ($row['account_code'] === '2100') {
            $outputVat = (float)$row['credits'] - (float)$row['debits'];
        }
    }
    return [
        'input_vat' => round($inputVat,2),
        'output_vat' => round($outputVat,2),
        'net_vat' => round($outputVat-$inputVat,2),
    ];
}

function cartItems(): array
{
    return $_SESSION['cart'] ?? [];
}

function cartCount(): int
{
    $count = 0;
    foreach (cartItems() as $item) {
        $count += max(0, (int)($item['quantity'] ?? 0));
    }
    return $count;
}

function cartSubtotal(): float
{
    $total = 0.0;
    foreach (cartItems() as $item) {
        $total += ((float)($item['price'] ?? 0)) * max(0, (int)($item['quantity'] ?? 0));
    }
    return $total;
}

function productImageUrl(array $product): string
{
    $image = trim((string)($product['image'] ?? ''));
    if ($image !== '') {
        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }
        return SITE_URL . '/uploads/products/' . ltrim($image, '/');
    }
    return 'https://placehold.co/900x700?text=' . rawurlencode((string)($product['name'] ?? 'Product'));
}

function productGallery(array $product): array
{
    $items = array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string)($product['gallery'] ?? '')) ?: []));
    $gallery = [];
    foreach ($items as $item) {
        $gallery[] = preg_match('#^https?://#i', $item) ? $item : SITE_URL . '/uploads/products/' . ltrim($item, '/');
    }
    return array_values(array_unique($gallery));
}

function localizedProductField(array $product, string $field): string
{
    $base = (string)($product[$field] ?? '');
    if (isArabicSite()) {
        $arabic = trim((string)($product[$field . '_ar'] ?? ''));
        if ($arabic !== '') {
            return $arabic;
        }
    }
    return $base;
}

function productName(array $product): string
{
    return localizedProductField($product, 'name');
}

function productShortDescription(array $product): string
{
    return localizedProductField($product, 'short_description');
}

function productDescription(array $product): string
{
    return localizedProductField($product, 'description');
}

function productSpecifications(array $product): string
{
    return localizedProductField($product, 'specifications');
}

function productWarranty(array $product): string
{
    return localizedProductField($product, 'warranty');
}

function specificationRows(?string $specifications): array
{
    $rows = [];
    foreach (preg_split('/[\r\n]+/', trim((string)$specifications)) ?: [] as $line) {
        if (trim($line) === '') {
            continue;
        }
        $parts = array_map('trim', explode(':', $line, 2));
        $rows[] = [
            'label' => $parts[0] ?? 'Detail',
            'value' => $parts[1] ?? '',
        ];
    }
    return $rows;
}

function stockBadge(int $stock): array
{
    if ($stock <= 0) {
        return ['class' => 'danger', 'text' => 'Out of stock'];
    }
    if ($stock <= 5) {
        return ['class' => 'warning text-dark', 'text' => 'Low stock: ' . $stock];
    }
    return ['class' => 'success', 'text' => 'In stock: ' . $stock];
}

function productStoreUrl(array $product): string
{
    if (bilingualEnabled()) { return SITE_URL . '/' . siteLanguage() . '/product/' . rawurlencode((string)($product['slug'] ?? '')); }
    return SITE_URL . '/products/product-details.php?slug=' . rawurlencode((string)($product['slug'] ?? ''));
}

function operationalScope(PDO $pdo, ?array $user = null): array
{
    $user = $user ?: currentUser();
    $companyId = (int)($user['default_company_id'] ?? setting('default_company_id', '0'));
    $branchId = (int)($user['default_branch_id'] ?? setting('default_branch_id', '0'));
    $warehouseId = (int)($user['default_warehouse_id'] ?? setting('default_warehouse_id', '0'));
    $locationId = (int)setting('default_location_id', '0');

    if ($companyId <= 0) {
        $companyId = (int)$pdo->query('SELECT id FROM ' . table('companies') . ' WHERE status="active" ORDER BY is_default DESC,id ASC LIMIT 1')->fetchColumn();
    }
    if ($branchId <= 0) {
        $stmt=$pdo->prepare('SELECT id FROM ' . table('branches') . ' WHERE company_id=? AND status="active" ORDER BY is_head_office DESC,id ASC LIMIT 1');
        $stmt->execute([$companyId]);
        $branchId=(int)$stmt->fetchColumn();
    }
    if ($warehouseId <= 0) {
        $stmt=$pdo->prepare('SELECT id FROM ' . table('warehouses') . ' WHERE branch_id=? AND status="active" ORDER BY is_default DESC,id ASC LIMIT 1');
        $stmt->execute([$branchId]);
        $warehouseId=(int)$stmt->fetchColumn();
    }
    if ($locationId <= 0) {
        $stmt=$pdo->prepare('SELECT id FROM ' . table('warehouse_locations') . ' WHERE warehouse_id=? AND status="active" ORDER BY id ASC LIMIT 1');
        $stmt->execute([$warehouseId]);
        $locationId=(int)$stmt->fetchColumn();
    }
    return [
        'company_id'=>$companyId,
        'branch_id'=>$branchId,
        'warehouse_id'=>$warehouseId,
        'location_id'=>$locationId,
    ];
}

function orgScopeLabels(PDO $pdo, array $scope): array
{
    $labels=['company'=>'','branch'=>'','warehouse'=>'','location'=>''];
    if(!empty($scope['company_id'])){$stmt=$pdo->prepare('SELECT company_name FROM '.table('companies').' WHERE id=?');$stmt->execute([(int)$scope['company_id']]);$labels['company']=(string)$stmt->fetchColumn();}
    if(!empty($scope['branch_id'])){$stmt=$pdo->prepare('SELECT branch_name FROM '.table('branches').' WHERE id=?');$stmt->execute([(int)$scope['branch_id']]);$labels['branch']=(string)$stmt->fetchColumn();}
    if(!empty($scope['warehouse_id'])){$stmt=$pdo->prepare('SELECT warehouse_name FROM '.table('warehouses').' WHERE id=?');$stmt->execute([(int)$scope['warehouse_id']]);$labels['warehouse']=(string)$stmt->fetchColumn();}
    if(!empty($scope['location_id'])){$stmt=$pdo->prepare('SELECT location_name FROM '.table('warehouse_locations').' WHERE id=?');$stmt->execute([(int)$scope['location_id']]);$labels['location']=(string)$stmt->fetchColumn();}
    return $labels;
}

function currentUserScopeAccess(PDO $pdo, bool $transact = false, ?array $user = null): array
{
    $user = $user ?: currentUser();
    if (!$user || ($user['role'] ?? '') === 'admin') {
        return ['unrestricted'=>true,'company_ids'=>[],'branch_ids'=>[],'warehouse_ids'=>[]];
    }
    $flag = $transact ? 'can_transact' : 'can_view';
    $stmt=$pdo->prepare('SELECT company_id,branch_id,warehouse_id FROM '.table('user_branch_access').' WHERE user_id=? AND '.$flag.'=1');
    $stmt->execute([(int)($user['id'] ?? 0)]);
    $companyIds=[];$branchIds=[];$warehouseIds=[];
    foreach($stmt->fetchAll() as $row){
        if(!empty($row['company_id'])){$companyIds[]=(int)$row['company_id'];}
        if(!empty($row['branch_id'])){$branchIds[]=(int)$row['branch_id'];}
        if(!empty($row['warehouse_id'])){$warehouseIds[]=(int)$row['warehouse_id'];}
    }
    return [
        'unrestricted'=>false,
        'company_ids'=>array_values(array_unique($companyIds)),
        'branch_ids'=>array_values(array_unique($branchIds)),
        'warehouse_ids'=>array_values(array_unique($warehouseIds)),
    ];
}

function sqlPlaceholders(array $values): string
{
    return implode(',', array_fill(0, count($values), '?'));
}

function scopeQueryCondition(PDO $pdo, string $alias, array &$params, bool $transact = false): string
{
    $access=currentUserScopeAccess($pdo,$transact);
    if(!empty($access['unrestricted'])){return '';}
    if(!empty($access['branch_ids'])){
        foreach($access['branch_ids'] as $id){$params[]=$id;}
        return ' AND '.$alias.'.branch_id IN ('.sqlPlaceholders($access['branch_ids']).')';
    }
    if(!empty($access['company_ids'])){
        foreach($access['company_ids'] as $id){$params[]=$id;}
        return ' AND '.$alias.'.company_id IN ('.sqlPlaceholders($access['company_ids']).')';
    }
    return ' AND 1=0';
}

function scopeWhereClause(PDO $pdo, string $alias, array &$params, bool $transact = false): string
{
    $condition=scopeQueryCondition($pdo,$alias,$params,$transact);
    return $condition!=='' ? ' WHERE '.substr($condition,5) : '';
}

function requestScopeFilters(): array
{
    return [
        'company_id'=>(int)($_GET['company_id'] ?? 0),
        'branch_id'=>(int)($_GET['branch_id'] ?? 0),
        'warehouse_id'=>(int)($_GET['warehouse_id'] ?? 0),
    ];
}

function selectedScopeCondition(string $alias, array &$params, array $filters, array $columns = ['company_id','branch_id','warehouse_id']): string
{
    $parts=[];
    foreach($columns as $column){
        $value=(int)($filters[$column] ?? 0);
        if($value>0){$parts[]=$alias.'.'.$column.'=?';$params[]=$value;}
    }
    return $parts ? ' AND '.implode(' AND ',$parts) : '';
}

function scopeAllowed(PDO $pdo, int $companyId = 0, int $branchId = 0, int $warehouseId = 0, bool $transact = false): bool
{
    $access=currentUserScopeAccess($pdo,$transact);
    if(!empty($access['unrestricted'])){return true;}
    if($branchId>0 && in_array($branchId,$access['branch_ids'],true)){return true;}
    if($companyId>0 && in_array($companyId,$access['company_ids'],true)){return true;}
    if($warehouseId>0 && in_array($warehouseId,$access['warehouse_ids'],true)){return true;}
    return false;
}

function enforceScopeAllowed(PDO $pdo, int $companyId = 0, int $branchId = 0, int $warehouseId = 0, bool $transact = false): void
{
    if(!scopeAllowed($pdo,$companyId,$branchId,$warehouseId,$transact)){
        throw new RuntimeException('You do not have access to the selected company, branch, or warehouse scope.');
    }
}

function branchTableAccessCondition(PDO $pdo, string $alias, array &$params, bool $transact = false): string
{
    $access=currentUserScopeAccess($pdo,$transact);
    if(!empty($access['unrestricted'])){return '';}
    if(!empty($access['branch_ids'])){
        foreach($access['branch_ids'] as $id){$params[]=$id;}
        return ' AND '.$alias.'.id IN ('.sqlPlaceholders($access['branch_ids']).')';
    }
    if(!empty($access['company_ids'])){
        foreach($access['company_ids'] as $id){$params[]=$id;}
        return ' AND '.$alias.'.company_id IN ('.sqlPlaceholders($access['company_ids']).')';
    }
    return ' AND 1=0';
}

function transferAccessCondition(PDO $pdo, string $alias, array &$params, bool $transact = false): string
{
    $access=currentUserScopeAccess($pdo,$transact);
    if(!empty($access['unrestricted'])){return '';}
    if(!empty($access['branch_ids'])){
        $left=[];$right=[];
        foreach($access['branch_ids'] as $id){$left[]='?';$params[]=$id;}
        foreach($access['branch_ids'] as $id){$right[]='?';$params[]=$id;}
        return ' AND ('.$alias.'.from_branch_id IN ('.implode(',',$left).') OR '.$alias.'.to_branch_id IN ('.implode(',',$right).'))';
    }
    if(!empty($access['company_ids'])){
        $left=[];$right=[];
        foreach($access['company_ids'] as $id){$left[]='?';$params[]=$id;}
        foreach($access['company_ids'] as $id){$right[]='?';$params[]=$id;}
        return ' AND ('.$alias.'.from_company_id IN ('.implode(',',$left).') OR '.$alias.'.to_company_id IN ('.implode(',',$right).'))';
    }
    return ' AND 1=0';
}

function transferScopeAllowed(PDO $pdo, array $transfer, bool $transact = false): bool
{
    return scopeAllowed($pdo,(int)($transfer['from_company_id']??0),(int)($transfer['from_branch_id']??0),(int)($transfer['from_warehouse_id']??0),$transact)
        || scopeAllowed($pdo,(int)($transfer['to_company_id']??0),(int)($transfer['to_branch_id']??0),(int)($transfer['to_warehouse_id']??0),$transact);
}

function enforceTransferScopeAllowed(PDO $pdo, array $transfer, bool $transact = false): void
{
    if(!transferScopeAllowed($pdo,$transfer,$transact)){
        throw new RuntimeException('You do not have access to this stock transfer scope.');
    }
}

function documentTypeFromTable(string $tableName): string
{
    $map=[
        'journal_entries'=>'journal',
        'invoices'=>'invoice',
        'quotations'=>'quotation',
        'purchase_orders'=>'purchase_order',
    ];
    return $map[$tableName] ?? '';
}

function nextScopedDocumentNumber(PDO $pdo, string $documentType, string $fallbackPrefix, ?array $scope = null): string
{
    $scope=$scope ?: operationalScope($pdo);
    $companyId=(int)($scope['company_id'] ?? 0) ?: null;
    $branchId=(int)($scope['branch_id'] ?? 0) ?: null;
    $stmt=$pdo->prepare('SELECT * FROM '.table('document_sequences').' WHERE document_type=? AND ((company_id IS NULL AND ? IS NULL) OR company_id=?) AND ((branch_id IS NULL AND ? IS NULL) OR branch_id=?) AND status="active" LIMIT 1 FOR UPDATE');
    $stmt->execute([$documentType,$companyId,$companyId,$branchId,$branchId]);
    $sequence=$stmt->fetch();
    if(!$sequence){
        $branchCode='';
        if($branchId){
            $branchStmt=$pdo->prepare('SELECT branch_code FROM '.table('branches').' WHERE id=? LIMIT 1');
            $branchStmt->execute([$branchId]);
            $branchCode=trim((string)$branchStmt->fetchColumn());
        }
        $prefix=$fallbackPrefix . ($branchCode!=='' ? '-'.$branchCode : '');
        $insert=$pdo->prepare('INSERT INTO '.table('document_sequences').' (company_id,branch_id,document_type,prefix,next_number,padding,status) VALUES (?,?,?,?,1,5,"active")');
        $insert->execute([$companyId,$branchId,$documentType,$prefix]);
        $sequenceId=(int)$pdo->lastInsertId();
        $sequence=['id'=>$sequenceId,'prefix'=>$prefix,'next_number'=>1,'padding'=>5];
    }
    $number=(int)($sequence['next_number'] ?? 1);
    $padding=max(3,(int)($sequence['padding'] ?? 5));
    $formatted=(string)($sequence['prefix'] ?? $fallbackPrefix) . '-' . str_pad((string)$number,$padding,'0',STR_PAD_LEFT);
    $pdo->prepare('UPDATE '.table('document_sequences').' SET next_number=? WHERE id=?')->execute([$number+1,(int)$sequence['id']]);
    return $formatted;
}

function scopeSelectOptions(PDO $pdo): array
{
    $access=currentUserScopeAccess($pdo,false);
    if(!empty($access['unrestricted'])){
        return [
            'companies'=>$pdo->query('SELECT id,company_code,company_name FROM '.table('companies').' WHERE status="active" ORDER BY is_default DESC,company_name ASC')->fetchAll(),
            'branches'=>$pdo->query('SELECT b.id,b.company_id,b.branch_code,b.branch_name,c.company_name FROM '.table('branches').' b LEFT JOIN '.table('companies').' c ON c.id=b.company_id WHERE b.status="active" ORDER BY c.company_name,b.branch_name')->fetchAll(),
            'warehouses'=>$pdo->query('SELECT w.id,w.company_id,w.branch_id,w.warehouse_code,w.warehouse_name,b.branch_name FROM '.table('warehouses').' w LEFT JOIN '.table('branches').' b ON b.id=w.branch_id WHERE w.status="active" ORDER BY b.branch_name,w.warehouse_name')->fetchAll(),
            'locations'=>$pdo->query('SELECT l.id,l.warehouse_id,l.location_code,l.location_name,w.warehouse_name FROM '.table('warehouse_locations').' l LEFT JOIN '.table('warehouses').' w ON w.id=l.warehouse_id WHERE l.status="active" ORDER BY w.warehouse_name,l.location_name')->fetchAll(),
        ];
    }
    $companyIds=$access['company_ids'];$branchIds=$access['branch_ids'];$warehouseIds=$access['warehouse_ids'];
    $companies=[];$branches=[];$warehouses=[];$locations=[];
    if($companyIds){
        $params=$companyIds;$stmt=$pdo->prepare('SELECT id,company_code,company_name FROM '.table('companies').' WHERE status="active" AND id IN ('.sqlPlaceholders($companyIds).') ORDER BY is_default DESC,company_name ASC');$stmt->execute($params);$companies=$stmt->fetchAll();
    }
    if($branchIds){
        $params=$branchIds;$stmt=$pdo->prepare('SELECT b.id,b.company_id,b.branch_code,b.branch_name,c.company_name FROM '.table('branches').' b LEFT JOIN '.table('companies').' c ON c.id=b.company_id WHERE b.status="active" AND b.id IN ('.sqlPlaceholders($branchIds).') ORDER BY c.company_name,b.branch_name');$stmt->execute($params);$branches=$stmt->fetchAll();
    }elseif($companyIds){
        $params=$companyIds;$stmt=$pdo->prepare('SELECT b.id,b.company_id,b.branch_code,b.branch_name,c.company_name FROM '.table('branches').' b LEFT JOIN '.table('companies').' c ON c.id=b.company_id WHERE b.status="active" AND b.company_id IN ('.sqlPlaceholders($companyIds).') ORDER BY c.company_name,b.branch_name');$stmt->execute($params);$branches=$stmt->fetchAll();
    }
    if($warehouseIds){
        $params=$warehouseIds;$stmt=$pdo->prepare('SELECT w.id,w.company_id,w.branch_id,w.warehouse_code,w.warehouse_name,b.branch_name FROM '.table('warehouses').' w LEFT JOIN '.table('branches').' b ON b.id=w.branch_id WHERE w.status="active" AND w.id IN ('.sqlPlaceholders($warehouseIds).') ORDER BY b.branch_name,w.warehouse_name');$stmt->execute($params);$warehouses=$stmt->fetchAll();
    }elseif($branchIds){
        $params=$branchIds;$stmt=$pdo->prepare('SELECT w.id,w.company_id,w.branch_id,w.warehouse_code,w.warehouse_name,b.branch_name FROM '.table('warehouses').' w LEFT JOIN '.table('branches').' b ON b.id=w.branch_id WHERE w.status="active" AND w.branch_id IN ('.sqlPlaceholders($branchIds).') ORDER BY b.branch_name,w.warehouse_name');$stmt->execute($params);$warehouses=$stmt->fetchAll();
    }
    $warehouseScopeIds=array_map(fn($row)=>(int)$row['id'],$warehouses);
    if($warehouseScopeIds){
        $params=$warehouseScopeIds;$stmt=$pdo->prepare('SELECT l.id,l.warehouse_id,l.location_code,l.location_name,w.warehouse_name FROM '.table('warehouse_locations').' l LEFT JOIN '.table('warehouses').' w ON w.id=l.warehouse_id WHERE l.status="active" AND l.warehouse_id IN ('.sqlPlaceholders($warehouseScopeIds).') ORDER BY w.warehouse_name,l.location_name');$stmt->execute($params);$locations=$stmt->fetchAll();
    }
    return ['companies'=>$companies,'branches'=>$branches,'warehouses'=>$warehouses,'locations'=>$locations];
}

function productDefaultCost(PDO $pdo, int $productId): float
{
    $stmt=$pdo->prepare('SELECT cost_price,average_cost,price FROM '.table('products').' WHERE id=? LIMIT 1');
    $stmt->execute([$productId]);
    $product=$stmt->fetch();
    if(!$product){return 0.0;}
    $average=(float)($product['average_cost']??0);
    $cost=(float)($product['cost_price']??0);
    $price=(float)($product['price']??0);
    if($average>0){return round($average,2);}
    if($cost>0){return round($cost,2);}
    $ratio=max(0,min(1,(float)setting('inventory_default_cost_ratio','0.60')));
    return round($price*$ratio,2);
}

function warehouseStockUnitCost(PDO $pdo, int $productId, array $scope): float
{
    $warehouseId=(int)($scope['warehouse_id']??0);
    $locationId=(int)($scope['location_id']??0);
    $stmt=$pdo->prepare('SELECT quantity,average_unit_cost,stock_value FROM '.table('warehouse_stock').' WHERE product_id=? AND warehouse_id=? AND ((location_id IS NULL AND ?=0) OR location_id=?) ORDER BY id ASC LIMIT 1');
    $stmt->execute([$productId,$warehouseId,$locationId,$locationId]);
    $row=$stmt->fetch();
    if($row){
        $average=(float)($row['average_unit_cost']??0);
        $qty=(float)($row['quantity']??0);
        $value=(float)($row['stock_value']??0);
        if($average>0){return round($average,2);}
        if($qty>0 && $value>0){return round($value/$qty,2);}
    }
    return productDefaultCost($pdo,$productId);
}

function logInventoryValuation(PDO $pdo, int $productId, array $scope, string $movementType, float $quantityDelta, float $unitCost, float $valueDelta, float $resultingQuantity, float $resultingValue, string $referenceType, int $referenceId, string $notes): void
{
    $stmt=$pdo->prepare('INSERT INTO '.table('inventory_valuation_entries').' (company_id,branch_id,warehouse_id,location_id,product_id,movement_type,quantity_delta,unit_cost,value_delta,resulting_quantity,resulting_value,reference_type,reference_id,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        (int)($scope['company_id']??0)?:null,
        (int)($scope['branch_id']??0)?:null,
        (int)($scope['warehouse_id']??0)?:null,
        (int)($scope['location_id']??0)?:null,
        $productId,
        $movementType,
        round($quantityDelta,2),
        round($unitCost,2),
        round($valueDelta,2),
        round($resultingQuantity,2),
        round($resultingValue,2),
        $referenceType,
        $referenceId,
        $notes,
    ]);
}

function refreshConsolidatedProductStock(PDO $pdo, int $productId): void
{
    $stmt=$pdo->prepare('SELECT COALESCE(SUM(quantity),0) total_qty,COALESCE(SUM(stock_value),0) total_value FROM '.table('warehouse_stock').' WHERE product_id=?');
    $stmt->execute([$productId]);
    $totals=$stmt->fetch()?:['total_qty'=>0,'total_value'=>0];
    $total=(float)($totals['total_qty']??0);
    $totalValue=(float)($totals['total_value']??0);
    $averageCost=$total>0 ? round($totalValue/$total,2) : productDefaultCost($pdo,$productId);
    $pdo->prepare('UPDATE '.table('products').' SET stock=?,average_cost=? WHERE id=?')->execute([$total,$averageCost,$productId]);
    $skuStmt=$pdo->prepare('SELECT sku FROM '.table('products').' WHERE id=? LIMIT 1');$skuStmt->execute([$productId]);$sku=(string)$skuStmt->fetchColumn();
    $inv=$pdo->prepare('SELECT id FROM '.table('inventory').' WHERE product_id=? LIMIT 1');$inv->execute([$productId]);$inventoryId=$inv->fetchColumn();
    if($inventoryId){
        $pdo->prepare('UPDATE '.table('inventory').' SET quantity=?,sku=? WHERE id=?')->execute([$total,$sku,(int)$inventoryId]);
    } else {
        $pdo->prepare('INSERT INTO '.table('inventory').' (product_id,sku,quantity,reorder_level,location) VALUES (?,?,?,?,?)')->execute([$productId,$sku,$total,5,'Consolidated']);
    }
}

function ensureWarehouseStock(PDO $pdo, int $productId, array $scope, float $quantity = 0, float $reorderLevel = 5): int
{
    $warehouseId=(int)($scope['warehouse_id']??0);
    $locationId=(int)($scope['location_id']??0);
    if($warehouseId<=0){throw new RuntimeException('A default warehouse is required for stock operations.');}
    $stmt=$pdo->prepare('SELECT id FROM '.table('warehouse_stock').' WHERE product_id=? AND warehouse_id=? AND ((location_id IS NULL AND ?=0) OR location_id=?) LIMIT 1');
    $stmt->execute([$productId,$warehouseId,$locationId,$locationId]);
    $id=(int)$stmt->fetchColumn();
    if($id>0){return $id;}
    $unitCost=productDefaultCost($pdo,$productId);
    $stockValue=round(max(0,$quantity)*$unitCost,2);
    $stmt=$pdo->prepare('INSERT INTO '.table('warehouse_stock').' (product_id,company_id,branch_id,warehouse_id,location_id,quantity,reserved_quantity,reorder_level,average_unit_cost,stock_value) VALUES (?,?,?,?,?,?,0,?,?,?)');
    $stmt->execute([$productId,(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,$warehouseId,$locationId?:null,$quantity,$reorderLevel,$unitCost,$stockValue]);
    return (int)$pdo->lastInsertId();
}

function warehouseAvailableQuantity(PDO $pdo, int $productId, array $scope): float
{
    $warehouseId=(int)($scope['warehouse_id']??0);
    $locationId=(int)($scope['location_id']??0);
    $stmt=$pdo->prepare('SELECT COALESCE(quantity-reserved_quantity,0) FROM '.table('warehouse_stock').' WHERE product_id=? AND warehouse_id=? AND ((location_id IS NULL AND ?=0) OR location_id=?) ORDER BY id ASC LIMIT 1');
    $stmt->execute([$productId,$warehouseId,$locationId,$locationId]);
    return (float)$stmt->fetchColumn();
}

function adjustWarehouseStock(PDO $pdo, int $productId, float $delta, array $scope, string $referenceType, int $referenceId, string $notes, ?float $unitCost = null): float
{
    if(abs($delta)<0.0001){return productDefaultCost($pdo,$productId);}
    $stockId=ensureWarehouseStock($pdo,$productId,$scope,0,5);
    $stmt=$pdo->prepare('SELECT quantity,reserved_quantity,average_unit_cost,stock_value FROM '.table('warehouse_stock').' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$stockId]);
    $row=$stmt->fetch();
    if(!$row){throw new RuntimeException('Warehouse stock record not found.');}
    $quantity=(float)($row['quantity']??0);
    $reserved=(float)($row['reserved_quantity']??0);
    $currentAverage=(float)($row['average_unit_cost']??0);
    $currentValue=(float)($row['stock_value']??0);
    if($currentAverage<=0 && $quantity>0 && $currentValue>0){$currentAverage=$currentValue/$quantity;}
    if($currentAverage<=0){$currentAverage=productDefaultCost($pdo,$productId);}
    $incomingCost=$unitCost!==null && $unitCost>=0 ? round($unitCost,2) : round($currentAverage,2);
    if($incomingCost<=0){$incomingCost=productDefaultCost($pdo,$productId);}
    $newQuantity=$quantity+$delta;
    if($newQuantity<-0.0001){throw new RuntimeException('Warehouse stock would become negative.');}
    $newReserved=max(0,min($reserved,$newQuantity));
    if($delta>0){
        $valueDelta=round($delta*$incomingCost,2);
        $newValue=round($currentValue+$valueDelta,2);
        $newAverage=$newQuantity>0 ? round($newValue/$newQuantity,2) : $incomingCost;
        $appliedCost=$incomingCost;
    }else{
        $appliedCost=round($currentAverage,2);
        $valueDelta=round($delta*$appliedCost,2);
        $newValue=max(0,round($currentValue+$valueDelta,2));
        $newAverage=$newQuantity>0 ? round($newValue/$newQuantity,2) : $appliedCost;
    }
    $pdo->prepare('UPDATE '.table('warehouse_stock').' SET quantity=?,reserved_quantity=?,average_unit_cost=?,stock_value=? WHERE id=?')->execute([$newQuantity,$newReserved,$newAverage,$newValue,$stockId]);
    refreshConsolidatedProductStock($pdo,$productId);
    inventoryAdjustment($pdo,$productId,$delta,$referenceType,$referenceId,$notes,$scope);
    logInventoryValuation($pdo,$productId,$scope,$delta>0?'stock_in':'stock_out',$delta,$appliedCost,$valueDelta,$newQuantity,$newValue,$referenceType,$referenceId,$notes);
    return $appliedCost;
}

function syncInventory(PDO $pdo, int $productId, string $sku, int $quantity, string $location = 'Main Store'): void
{
    $scope=operationalScope($pdo);
    $stockId=ensureWarehouseStock($pdo,$productId,$scope,0,5);
    $stmt=$pdo->prepare('SELECT quantity FROM '.table('warehouse_stock').' WHERE id=? LIMIT 1 FOR UPDATE');$stmt->execute([$stockId]);$currentQty=(float)$stmt->fetchColumn();
    $delta=(float)$quantity-$currentQty;
    if(abs($delta)>=0.0001){
        adjustWarehouseStock($pdo,$productId,$delta,$scope,'inventory_sync',0,'Stock synchronized from product form.');
    }
    refreshConsolidatedProductStock($pdo,$productId);
    $check = $pdo->prepare('SELECT id FROM ' . table('inventory') . ' WHERE product_id=? LIMIT 1');
    $check->execute([$productId]);
    $inventoryId = $check->fetchColumn();
    if ($inventoryId) {
        $stmt = $pdo->prepare('UPDATE ' . table('inventory') . ' SET sku=?, location=? WHERE id=?');
        $stmt->execute([$sku, $location, (int)$inventoryId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO ' . table('inventory') . ' (product_id,sku,quantity,reorder_level,location) VALUES (?,?,?,?,?)');
        $stmt->execute([$productId, $sku, $quantity, 5, $location]);
    }
}

function inventoryAdjustment(PDO $pdo, int $productId, float $delta, string $referenceType, int $referenceId, string $notes, ?array $scope = null): void
{
    if ($delta === 0) {
        return;
    }
    $scope=$scope ?: operationalScope($pdo);
    $stmt = $pdo->prepare('INSERT INTO ' . table('inventory_movements') . ' (company_id,branch_id,warehouse_id,location_id,product_id,movement_type,quantity,reference_type,reference_id,notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,(int)($scope['warehouse_id']??0)?:null,(int)($scope['location_id']??0)?:null,$productId, $delta > 0 ? 'stock_in' : 'stock_out', abs($delta), $referenceType, $referenceId, $notes]);
}

function createIntercompanyTransactionFromTransfer(PDO $pdo, array $transfer, int $transferId, array $items, float $totalValue): ?int
{
    $fromCompany=(int)($transfer['from_company_id']??0);
    $toCompany=(int)($transfer['to_company_id']??0);
    if($fromCompany<=0 || $toCompany<=0 || $fromCompany===$toCompany || $totalValue<=0){
        return null;
    }
    $existing=$pdo->prepare('SELECT id FROM '.table('intercompany_transactions').' WHERE stock_transfer_id=? LIMIT 1');
    $existing->execute([$transferId]);
    $existingId=(int)$existing->fetchColumn();
    if($existingId>0){return $existingId;}

    $scope=[
        'company_id'=>$fromCompany,
        'branch_id'=>(int)($transfer['from_branch_id']??0),
        'warehouse_id'=>(int)($transfer['from_warehouse_id']??0),
        'location_id'=>(int)($transfer['from_location_id']??0),
    ];
    $number=nextScopedDocumentNumber($pdo,'intercompany_transaction','ICT',$scope);
    $stmt=$pdo->prepare('INSERT INTO '.table('intercompany_transactions').' (transaction_number,stock_transfer_id,from_company_id,from_branch_id,to_company_id,to_branch_id,total_value,status,notes) VALUES (?,?,?,?,?,?,?,"in_transit",?)');
    $stmt->execute([
        $number,
        $transferId,
        $fromCompany,
        (int)($transfer['from_branch_id']??0)?:null,
        $toCompany,
        (int)($transfer['to_branch_id']??0)?:null,
        round($totalValue,2),
        'Created automatically from cross-company stock transfer '.$transfer['transfer_number'].'.',
    ]);
    $transactionId=(int)$pdo->lastInsertId();
    $itemStmt=$pdo->prepare('INSERT INTO '.table('intercompany_transaction_items').' (intercompany_transaction_id,product_id,quantity,unit_cost,total_value,notes) VALUES (?,?,?,?,?,?)');
    foreach($items as $item){
        $itemStmt->execute([
            $transactionId,
            (int)($item['product_id']??0),
            round((float)($item['quantity']??0),2),
            round((float)($item['unit_cost']??0),2),
            round((float)($item['total_value']??0),2),
            (string)($item['notes']??''),
        ]);
    }
    logActivity($pdo,'Intercompany','intercompany_transaction_created','Intercompany transaction '.$number.' created from transfer '.$transfer['transfer_number'].'.','intercompany_transaction',$transactionId);
    return $transactionId;
}

function recognizeIntercompanyTransaction(PDO $pdo, int $transferId): void
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('intercompany_transactions').' WHERE stock_transfer_id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$transferId]);
    $transaction=$stmt->fetch();
    if(!$transaction || in_array((string)($transaction['status']??''),['recognized','settled','cancelled'],true)){
        return;
    }
    $total=round((float)($transaction['total_value']??0),2);
    $sourceJournalId=(int)($transaction['source_journal_id']??0);
    $destinationJournalId=(int)($transaction['destination_journal_id']??0);
    if($total>0 && setting('intercompany_auto_journals','1')==='1'){
        if($sourceJournalId<=0){
            $sourceJournalId=createAccountingJournal($pdo,[
                'company_id'=>(int)($transaction['from_company_id']??0),
                'branch_id'=>(int)($transaction['from_branch_id']??0),
                'entry_date'=>date('Y-m-d'),
                'reference_type'=>'intercompany_source',
                'reference_id'=>(int)$transaction['id'],
                'memo'=>'Intercompany stock transfer value recognized: '.$transaction['transaction_number'],
            ],[
                ['account_code'=>'1400','description'=>'Intercompany receivable created for '.$transaction['transaction_number'],'debit'=>$total,'credit'=>0],
                ['account_code'=>'1200','description'=>'Inventory value released to affiliated company','debit'=>0,'credit'=>$total],
            ],true);
        }
        if($destinationJournalId<=0){
            $destinationJournalId=createAccountingJournal($pdo,[
                'company_id'=>(int)($transaction['to_company_id']??0),
                'branch_id'=>(int)($transaction['to_branch_id']??0),
                'entry_date'=>date('Y-m-d'),
                'reference_type'=>'intercompany_destination',
                'reference_id'=>(int)$transaction['id'],
                'memo'=>'Intercompany stock transfer receipt recognized: '.$transaction['transaction_number'],
            ],[
                ['account_code'=>'1200','description'=>'Inventory value received from affiliated company','debit'=>$total,'credit'=>0],
                ['account_code'=>'2200','description'=>'Intercompany payable created for '.$transaction['transaction_number'],'debit'=>0,'credit'=>$total],
            ],true);
        }
    }
    $update=$pdo->prepare('UPDATE '.table('intercompany_transactions').' SET status="recognized",source_journal_id=?,destination_journal_id=?,recognized_at=NOW() WHERE id=?');
    $update->execute([$sourceJournalId?:null,$destinationJournalId?:null,(int)$transaction['id']]);
    logActivity($pdo,'Intercompany','intercompany_transaction_recognized','Intercompany transaction '.$transaction['transaction_number'].' recognized after stock receipt.','intercompany_transaction',(int)$transaction['id']);
}

function intercompanyAccessCondition(PDO $pdo, string $alias, array &$params, bool $transact = false): string
{
    $access=currentUserScopeAccess($pdo,$transact);
    if(!empty($access['unrestricted'])){return '';}
    if(!empty($access['branch_ids'])){
        $left=[];$right=[];
        foreach($access['branch_ids'] as $id){$left[]='?';$params[]=$id;}
        foreach($access['branch_ids'] as $id){$right[]='?';$params[]=$id;}
        return ' AND ('.$alias.'.from_branch_id IN ('.implode(',',$left).') OR '.$alias.'.to_branch_id IN ('.implode(',',$right).'))';
    }
    if(!empty($access['company_ids'])){
        $left=[];$right=[];
        foreach($access['company_ids'] as $id){$left[]='?';$params[]=$id;}
        foreach($access['company_ids'] as $id){$right[]='?';$params[]=$id;}
        return ' AND ('.$alias.'.from_company_id IN ('.implode(',',$left).') OR '.$alias.'.to_company_id IN ('.implode(',',$right).'))';
    }
    return ' AND 1=0';
}

function inTransitAccessCondition(PDO $pdo, string $alias, array &$params, bool $transact = false): string
{
    $access=currentUserScopeAccess($pdo,$transact);
    if(!empty($access['unrestricted'])){return '';}
    if(!empty($access['branch_ids'])){
        $left=[];$right=[];
        foreach($access['branch_ids'] as $id){$left[]='?';$params[]=$id;}
        foreach($access['branch_ids'] as $id){$right[]='?';$params[]=$id;}
        return ' AND ('.$alias.'.from_branch_id IN ('.implode(',',$left).') OR '.$alias.'.to_branch_id IN ('.implode(',',$right).'))';
    }
    if(!empty($access['company_ids'])){
        $left=[];$right=[];
        foreach($access['company_ids'] as $id){$left[]='?';$params[]=$id;}
        foreach($access['company_ids'] as $id){$right[]='?';$params[]=$id;}
        return ' AND ('.$alias.'.from_company_id IN ('.implode(',',$left).') OR '.$alias.'.to_company_id IN ('.implode(',',$right).'))';
    }
    return ' AND 1=0';
}

function currentErpRoleSlug(PDO $pdo, ?array $user = null): string
{
    $user=$user ?: currentUser();
    if(!$user){return '';}
    if(($user['role']??'')==='admin'){return 'erp-manager';}
    $roleId=(int)($user['erp_role_id']??0);
    if($roleId<=0){return '';}
    $stmt=$pdo->prepare('SELECT slug FROM '.table('erp_roles').' WHERE id=? AND active=1 LIMIT 1');
    $stmt->execute([$roleId]);
    return trim((string)$stmt->fetchColumn());
}

function validDocumentUploadExtension(string $tmpFile): ?array
{
    $allowed = [
        'application/pdf' => ['pdf','application/pdf'],
        'image/jpeg' => ['jpg','image/jpeg'],
        'image/png' => ['png','image/png'],
        'image/webp' => ['webp','image/webp'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'text/csv' => ['csv','text/csv'],
        'application/csv' => ['csv','text/csv'],
        'text/comma-separated-values' => ['csv','text/csv'],
        'application/vnd.ms-excel' => ['csv','text/csv'],
        'text/plain' => ['txt','text/plain'],
    ];
    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmpFile);
    } elseif (function_exists('mime_content_type')) {
        $mime = (string)mime_content_type($tmpFile);
    }
    return $allowed[$mime] ?? null;
}

function uploadAdminDocument(string $field, string $subdir = 'documents', int $maxBytes = 15728640): ?array
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }
    $file = $_FILES[$field];
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Document upload failed.');
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        throw new RuntimeException('Document must be smaller than 15 MB.');
    }
    $tmp = (string)($file['tmp_name'] ?? '');
    $originalName = trim((string)($file['name'] ?? ''));
    $originalExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $meta = validDocumentUploadExtension($tmp);
    if (!$meta && $originalExt === 'csv') {
        $meta = ['csv','text/csv'];
    }
    if (!$meta) {
        throw new RuntimeException('Allowed documents: PDF, JPG, PNG, WEBP, DOCX, XLSX, TXT, and CSV.');
    }
    [$extension,$mime] = $meta;
    if ($originalExt === 'csv') {
        $extension = 'csv';
        $mime = 'text/csv';
    }
    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $destination = ensureUploadDirectory($subdir) . '/' . $filename;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('Unable to save uploaded document.');
    }
    return [
        'file_name' => trim((string)($file['name'] ?? $filename)) ?: $filename,
        'stored_path' => $filename,
        'mime_type' => $mime,
        'file_size' => $size,
    ];
}

function documentAttachmentUrl(string $storedPath): string
{
    return UPLOADS_URL . '/documents/' . ltrim($storedPath, '/');
}

function customerCreditExposure(PDO $pdo, int $customerId, int $excludeSalesOrderId = 0): float
{
    if ($customerId <= 0) {
        return 0.0;
    }
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE customer_id=? AND balance_due>0 AND status IN ("approved","sent","partial")');
    $stmt->execute([$customerId]);
    $exposure=(float)$stmt->fetchColumn();
    if (setting('customer_credit_include_open_sales_orders','1') === '1') {
        $sql='SELECT COALESCE(SUM(total),0) FROM '.table('sales_orders').' WHERE customer_id=? AND status IN ("draft","pending_approval","approved")';
        $params=[$customerId];
        if($excludeSalesOrderId>0){$sql.=' AND id<>?';$params[]=$excludeSalesOrderId;}
        $stmt=$pdo->prepare($sql);$stmt->execute($params);$exposure+=(float)$stmt->fetchColumn();
    }
    return round($exposure,2);
}

function customerCreditSnapshot(PDO $pdo, int $customerId, float $newOrderValue = 0, int $excludeSalesOrderId = 0): array
{
    $stmt=$pdo->prepare('SELECT id,customer_code,customer_type,company_name,contact_name,credit_limit,credit_status,credit_hold_reason FROM '.table('customers').' WHERE id=? LIMIT 1');
    $stmt->execute([$customerId]);
    $customer=$stmt->fetch();
    if(!$customer){
        return ['customer'=>null,'limit'=>0.0,'exposure'=>0.0,'available'=>0.0,'projected'=>round($newOrderValue,2),'shortfall'=>round($newOrderValue,2),'status'=>'not_found'];
    }
    $limit=round((float)($customer['credit_limit']??0),2);
    $exposure=customerCreditExposure($pdo,$customerId,$excludeSalesOrderId);
    $projected=round($exposure+$newOrderValue,2);
    $available=round($limit-$exposure,2);
    $shortfall=max(0,round($projected-$limit,2));
    $status='passed';
    if(($customer['credit_status']??'open')==='hold'){$status='hold';}
    elseif($limit>0 && $shortfall>0){$status='exceeded';}
    elseif($limit<=0 && ($customer['customer_type']??'b2c')==='b2b' && $newOrderValue>0){$status='zero_limit';}
    elseif(($customer['customer_type']??'b2c')!=='b2b'){$status='not_required';}
    return ['customer'=>$customer,'limit'=>$limit,'exposure'=>$exposure,'available'=>$available,'projected'=>$projected,'shortfall'=>$shortfall,'status'=>$status];
}

function salesOrderCreditDecision(PDO $pdo, array $order): array
{
    $customerId=(int)($order['customer_id']??0);
    $total=round((float)($order['total']??0),2);
    return customerCreditSnapshot($pdo,$customerId,$total,(int)($order['id']??0));
}

function supplierInvoiceMatchSummary(PDO $pdo, int $supplierInvoiceId, bool $persist = true): array
{
    $invoiceStmt=$pdo->prepare('SELECT * FROM '.table('supplier_invoices').' WHERE id=? LIMIT 1');
    $invoiceStmt->execute([$supplierInvoiceId]);
    $invoice=$invoiceStmt->fetch();
    if(!$invoice){throw new RuntimeException('Supplier invoice not found for three-way matching.');}
    $itemsStmt=$pdo->prepare('SELECT sii.*,poi.quantity po_quantity,poi.received_quantity po_received_quantity,poi.unit_cost po_unit_cost,poi.line_total po_line_total,COALESCE((SELECT SUM(gri.accepted_quantity) FROM '.table('goods_receipt_items').' gri LEFT JOIN '.table('goods_receipts').' gr ON gr.id=gri.goods_receipt_id WHERE gri.purchase_order_item_id=sii.purchase_order_item_id AND gr.status="posted"),0) received_accepted_quantity FROM '.table('supplier_invoice_items').' sii LEFT JOIN '.table('purchase_order_items').' poi ON poi.id=sii.purchase_order_item_id WHERE sii.supplier_invoice_id=? ORDER BY sii.id ASC');
    $itemsStmt->execute([$supplierInvoiceId]);
    $items=$itemsStmt->fetchAll();
    $matchedTotal=0.0;$difference=0.0;$allMatched=true;$update=$pdo->prepare('UPDATE '.table('supplier_invoice_items').' SET matched_quantity=?,variance_amount=? WHERE id=?');
    foreach($items as $item){
        $invoiceQty=max(0,(float)($item['quantity']??0));
        $accepted=max(0,(float)($item['received_accepted_quantity']??0));
        $matchedQty=min($invoiceQty,$accepted);
        $poCost=max(0,(float)($item['po_unit_cost']??0));
        $lineValue=round((float)($item['line_total']??0),2);
        $expected=round($matchedQty*$poCost,2);
        $variance=round($lineValue-$expected,2);
        if($matchedQty+0.0001<$invoiceQty || abs($variance)>0.01){$allMatched=false;}
        $matchedTotal+=$expected;
        $difference+=abs($variance)+max(0,$invoiceQty-$matchedQty)*$poCost;
        if($persist){$update->execute([$matchedQty,$variance,(int)$item['id']]);}
    }
    $matchedTotal=round($matchedTotal,2);$difference=round($difference,2);
    $tolerance=max(0,(float)setting('supplier_invoice_match_tolerance','1.00'));
    $matchStatus=($allMatched && $difference<=$tolerance)?'matched':'variance';
    if(!$items){$matchStatus='pending';}
    if($persist){
        $stmt=$pdo->prepare('UPDATE '.table('supplier_invoices').' SET matched_total=?,difference_amount=?,match_status=?,status=CASE WHEN status="draft" THEN ? ELSE status END WHERE id=?');
        $stmt->execute([$matchedTotal,$difference,$matchStatus,$matchStatus==='matched'?'matched':'draft',$supplierInvoiceId]);
    }
    return ['match_status'=>$matchStatus,'matched_total'=>$matchedTotal,'difference_amount'=>$difference,'items'=>$items];
}

function postSupplierInvoiceAccounting(PDO $pdo, int $supplierInvoiceId): ?int
{
    if($supplierInvoiceId<=0 || accountingEntryExists($pdo,'supplier_invoice',$supplierInvoiceId)){
        return null;
    }
    $stmt=$pdo->prepare('SELECT * FROM '.table('supplier_invoices').' WHERE id=? LIMIT 1');
    $stmt->execute([$supplierInvoiceId]);
    $invoice=$stmt->fetch();
    if(!$invoice){throw new RuntimeException('Supplier invoice not found for accounting post.');}
    if(($invoice['status']??'')!=='approved' && ($invoice['status']??'')!=='posted'){throw new RuntimeException('Supplier invoice must be approved before accounting post.');}
    $subtotal=round(max(0,(float)($invoice['subtotal']??0)),2);
    $tax=round(max(0,(float)($invoice['tax']??0)),2);
    $total=round(max(0,(float)($invoice['total']??0)),2);
    if($total<=0){return null;}
    $lines=[];
    if($subtotal>0){$lines[]=['account_code'=>'1200','description'=>'Inventory/AP accrual - '.$invoice['internal_number'],'debit'=>$subtotal,'credit'=>0];}
    if($tax>0){$lines[]=['account_code'=>'1300','description'=>'VAT input - '.$invoice['internal_number'],'debit'=>$tax,'credit'=>0];}
    $lines[]=['account_code'=>'2000','description'=>'Supplier payable - '.$invoice['internal_number'],'debit'=>0,'credit'=>$total];
    $journalId=createAccountingJournal($pdo,[
        'company_id'=>(int)($invoice['company_id']??0),
        'branch_id'=>(int)($invoice['branch_id']??0),
        'entry_date'=>(string)($invoice['invoice_date']?:date('Y-m-d')),
        'reference_type'=>'supplier_invoice',
        'reference_id'=>$supplierInvoiceId,
        'memo'=>'Automatic supplier invoice accounting for '.$invoice['internal_number'],
    ],$lines,true);
    $pdo->prepare('UPDATE '.table('supplier_invoices').' SET posted_journal_id=?,status="posted",posted_at=NOW() WHERE id=?')->execute([$journalId,$supplierInvoiceId]);
    logActivity($pdo,'Accounting','supplier_invoice_accounted','Supplier invoice '.$invoice['internal_number'].' posted to Accounts Payable.','supplier_invoice',$supplierInvoiceId);
    return $journalId;
}








function safeAiScalar(PDO $pdo, string $sql, float $default = 0): float
{
    try { return (float)$pdo->query($sql)->fetchColumn(); }
    catch(Throwable $e){ if(function_exists('recordSystemError')){ try{ recordSystemError($pdo,$e,['ai_sql'=>$sql]); }catch(Throwable $ignored){} } return $default; }
}

function safeAiRows(PDO $pdo, string $sql): array
{
    try { return $pdo->query($sql)->fetchAll(); }
    catch(Throwable $e){ if(function_exists('recordSystemError')){ try{ recordSystemError($pdo,$e,['ai_sql'=>$sql]); }catch(Throwable $ignored){} } return []; }
}

function aiRiskLevel(float $score): string
{
    if($score>=85){return 'critical';}
    if($score>=70){return 'high';}
    if($score>=45){return 'medium';}
    return 'low';
}

function createAiRiskScore(PDO $pdo, string $model, string $entityType, ?int $entityId, string $label, string $module, float $score, string $reason, string $action): int
{
    $number=nextScopedDocumentNumber($pdo,'ai_risk_score',setting('ai_risk_score_prefix','RSK'),operationalScope($pdo));
    $level=aiRiskLevel($score);
    $pdo->prepare('INSERT INTO '.table('ai_risk_scores').' (score_number,model_code,entity_type,entity_id,entity_label,module,risk_score,risk_level,reason,recommended_action,status) VALUES (?,?,?,?,?,?,?,?,?,?,"open")')->execute([$number,$model,$entityType,$entityId,$label,$module,$score,$level,$reason,$action]);
    return (int)$pdo->lastInsertId();
}

function createAiDecisionRecommendation(PDO $pdo, string $sourceType, ?int $sourceId, string $module, string $title, string $text, float $confidence, float $impact, float $effort, string $priority='medium'): int
{
    $number=nextScopedDocumentNumber($pdo,'ai_decision_recommendation',setting('ai_decision_recommendation_prefix','AIREC'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('ai_decision_recommendations').' (recommendation_number,source_type,source_id,module,recommendation_title,recommendation_text,confidence_score,impact_score,effort_score,priority,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,"open",?)')->execute([$number,$sourceType,$sourceId,$module,$title,$text,$confidence,$impact,$effort,$priority,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function createAiActionSuggestion(PDO $pdo, string $module, string $title, string $text, string $label, string $url, string $priority='medium', ?int $userId=null): int
{
    $number=nextScopedDocumentNumber($pdo,'ai_action_suggestion',setting('ai_action_suggestion_prefix','ACTSUG'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('ai_assistant_action_suggestions').' (suggestion_number,user_id,module,suggestion_title,suggestion_text,action_label,action_url,priority,status) VALUES (?,?,?,?,?,?,?,?,"open")')->execute([$number,$userId,$module,$title,$text,$label,$url,$priority]);
    return (int)$pdo->lastInsertId();
}

function runAiDecisionEngine(PDO $pdo, string $runType='full'): array
{
    $user=currentUser();
    $runNumber=nextScopedDocumentNumber($pdo,'ai_automation_run',setting('ai_automation_run_prefix','AIRUN'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('ai_automation_runs').' (run_number,run_type,status,created_by,notes) VALUES (?,?,"running",?,?)')->execute([$runNumber,$runType,(int)($user['id']??0)?:null,'AI rules-based scoring engine started.']);
    $runId=(int)$pdo->lastInsertId();
    $items=0;$recommendations=0;$alerts=0;
    $riskThreshold=(float)setting('ai_default_risk_threshold','70');

    $itemStmt=$pdo->prepare('INSERT INTO '.table('ai_automation_run_items').' (ai_automation_run_id,item_type,item_id,item_label,score,result_status,notes) VALUES (?,?,?,?,?,"processed",?)');

    // Invoice payment risk
    foreach(safeAiRows($pdo,'SELECT id,invoice_number,customer_name,balance_due,due_date,created_at FROM '.table('invoices').' WHERE balance_due>0 AND status NOT IN ("paid","cancelled") ORDER BY balance_due DESC LIMIT 100') as $inv){
        $days=max(0,(time()-strtotime((string)($inv['due_date']?:$inv['created_at'])))/86400);
        $score=min(100,round(($days*2)+min(60,((float)$inv['balance_due']/500)),2));
        $itemStmt->execute([$runId,'invoice',(int)$inv['id'],$inv['invoice_number'],$score,'Payment risk score.']);
        $items++;
        if($score>=$riskThreshold){
            createAiRiskScore($pdo,'INVOICE_PAYMENT_RISK','invoice',(int)$inv['id'],$inv['invoice_number'].' · '.$inv['customer_name'],'Finance',$score,'Invoice is overdue or has high balance due.','Create collection task, send reminder, or escalate to finance manager.');
            createAiDecisionRecommendation($pdo,'invoice',(int)$inv['id'],'Finance','Collect invoice '.$inv['invoice_number'],'Prioritize collection for '.$inv['customer_name'].' because payment risk score is '.number_format($score,2).'.',82,85,25,'high');
            if(setting('ai_auto_create_action_suggestions','1')==='1'){createAiActionSuggestion($pdo,'Finance','Follow up invoice '.$inv['invoice_number'],'High payment risk detected for '.$inv['customer_name'].'.','Open invoice','/admin/erp/invoices.php','high');}
            $alerts++;$recommendations++;
        }
    }

    // Product low-stock / stockout risk
    foreach(safeAiRows($pdo,'SELECT id,sku,name,stock FROM '.table('products').' WHERE active=1 ORDER BY stock ASC LIMIT 100') as $p){
        $stock=(float)$p['stock'];
        $score=max(0,min(100,100-($stock*15)));
        $itemStmt->execute([$runId,'product',(int)$p['id'],$p['sku'].' · '.$p['name'],$score,'Low stock score.']);
        $items++;
        if($score>=$riskThreshold){
            createAiRiskScore($pdo,'LOW_STOCK_RISK','product',(int)$p['id'],$p['sku'].' · '.$p['name'],'Inventory',$score,'Product stock is low compared with configured rule thresholds.','Create purchase requisition, replenish warehouse, or pause promotion.');
            createAiDecisionRecommendation($pdo,'product',(int)$p['id'],'Inventory','Replenish '.$p['sku'],'Stock risk is high. Review supplier price list or purchase requisition.',78,80,30,'high');
            if(setting('ai_auto_create_action_suggestions','1')==='1'){createAiActionSuggestion($pdo,'Inventory','Replenish '.$p['sku'],'Low-stock risk detected for '.$p['name'].'.','Open replenishment','/admin/erp/replenishment.php','high');}
            $alerts++;$recommendations++;
        }
    }

    // Lead score / sales risk
    foreach(safeAiRows($pdo,'SELECT id,lead_name,company_name,email,lead_score,status,created_at FROM '.table('crm_leads').' WHERE status NOT IN ("won","lost") ORDER BY lead_score DESC,created_at DESC LIMIT 100') as $lead){
        $base=(float)($lead['lead_score']??0);
        $age=max(0,(time()-strtotime((string)$lead['created_at']))/86400);
        $score=min(100,round($base + max(0,20-$age),2));
        $itemStmt->execute([$runId,'lead',(int)$lead['id'],trim($lead['lead_name'].' '.$lead['company_name']),$score,'Lead opportunity score.']);
        $items++;
        if($score>=75){
            createAiRiskScore($pdo,'LEAD_CONVERSION_OPPORTUNITY','lead',(int)$lead['id'],trim($lead['lead_name'].' '.$lead['company_name']),'Sales',$score,'Lead shows high potential based on score and recency.','Create follow-up task, call customer, and send quotation quickly.');
            createAiDecisionRecommendation($pdo,'lead',(int)$lead['id'],'Sales','Follow up hot lead','Lead '.$lead['lead_name'].' is high opportunity. Create CRM follow-up now.',86,88,20,'high');
            if(setting('ai_auto_create_action_suggestions','1')==='1'){createAiActionSuggestion($pdo,'Sales','Call hot lead '.$lead['lead_name'],'High-potential lead needs quick follow-up.','Open CRM','/admin/erp/crm-followups.php','high');}
            $alerts++;$recommendations++;
        }
    }

    // Supplier score/risk
    foreach(safeAiRows($pdo,'SELECT s.id,s.company_name,COALESCE(MAX(sc.total_score),0) total_score FROM '.table('suppliers').' s LEFT JOIN '.table('supplier_scorecards').' sc ON sc.supplier_id=s.id GROUP BY s.id,s.company_name ORDER BY total_score ASC LIMIT 80') as $s){
        $supplierScore=(float)$s['total_score'];
        if($supplierScore<=0){continue;}
        $risk=max(0,100-$supplierScore);
        $itemStmt->execute([$runId,'supplier',(int)$s['id'],$s['company_name'],$risk,'Supplier risk score.']);
        $items++;
        if($risk>=40){
            createAiRiskScore($pdo,'SUPPLIER_PERFORMANCE_RISK','supplier',(int)$s['id'],$s['company_name'],'Procurement',$risk,'Supplier scorecard is below expected performance level.','Review supplier contract, request corrective action, or find alternate supplier.');
            createAiDecisionRecommendation($pdo,'supplier',(int)$s['id'],'Procurement','Review supplier '.$s['company_name'],'Supplier performance risk detected from latest scorecard.',76,70,35,'medium');
            $alerts++;$recommendations++;
        }
    }

    $pdo->prepare('UPDATE '.table('ai_automation_runs').' SET status="completed",items_scored=?,recommendations_created=?,alerts_created=?,notes=? WHERE id=?')->execute([$items,$recommendations,$alerts,'AI scoring completed: '.$items.' items processed.',$runId]);
    return ['run_id'=>$runId,'run_number'=>$runNumber,'items'=>$items,'recommendations'=>$recommendations,'alerts'=>$alerts];
}

function smartAssistant2Response(PDO $pdo, string $message): string
{
    $m=mb_strtolower($message);
    if(str_contains($m,'risk') || str_contains($m,'danger')){
        $count=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('ai_risk_scores').' WHERE status="open"');
        $high=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('ai_risk_scores').' WHERE status="open" AND risk_level IN ("high","critical")');
        return 'Risk summary: '.$count.' open risk score(s), including '.$high.' high/critical. Recommended next step: open AI Risk Scoring and resolve finance, stock, supplier, and sales risks by priority.';
    }
    if(str_contains($m,'invoice') || str_contains($m,'collection') || str_contains($m,'payment')){
        $ar=safeAiScalar($pdo,'SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE balance_due>0 AND status NOT IN ("paid","cancelled")');
        return 'Invoice collection summary: open receivables are '.money($ar).'. Recommended next step: run Decision Engine, then create collection actions for high-risk invoices.';
    }
    if(str_contains($m,'stock') || str_contains($m,'inventory') || str_contains($m,'reorder')){
        $low=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('products').' WHERE active=1 AND stock<=3');
        return 'Inventory summary: '.$low.' product(s) are low-stock. Recommended next step: open Replenishment and generate purchase suggestions.';
    }
    if(str_contains($m,'lead') || str_contains($m,'sales') || str_contains($m,'quote')){
        $hot=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('crm_leads').' WHERE lead_score>=70 AND status NOT IN ("won","lost")');
        return 'Sales summary: '.$hot.' hot lead(s) need action. Recommended next step: use CRM Follow-ups and Quote Follow-ups to improve conversion.';
    }
    if(str_contains($m,'supplier') || str_contains($m,'procurement')){
        $weak=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('supplier_scorecards').' WHERE total_score<70');
        return 'Supplier summary: '.$weak.' supplier scorecard(s) are below 70. Recommended next step: review supplier scorecards and contracts.';
    }
    if(str_contains($m,'technician') || str_contains($m,'service') || str_contains($m,'job')){
        $open=(int)safeAiScalar($pdo,'SELECT COUNT(*) FROM '.table('job_cards').' WHERE status NOT IN ("completed","cancelled","closed")');
        return 'Service summary: '.$open.' open job card(s). Recommended next step: review technician mobile queue and dispatch high-priority jobs.';
    }
    return assistantRuleResponse($pdo,$message);
}

function createDefaultAiPlaybooks(PDO $pdo): int
{
    $user=currentUser();
    $rows=[
        ['PB-RISK','Risk Command Center','Risk','risk summary','Review open risk scores, high/critical items, and action suggestions.','/admin/erp/ai-risk-scoring.php'],
        ['PB-CASH','Cash Collection Coach','Finance','invoice collection','Review risky invoices and create collection tasks.','/admin/erp/ar-ap-aging.php'],
        ['PB-STOCK','Stock Replenishment Coach','Inventory','low stock','Open replenishment and create purchase suggestions.','/admin/erp/replenishment.php'],
        ['PB-SALES','Sales Conversion Coach','Sales','hot leads','Review hot leads and quotation follow-ups.','/admin/erp/crm-followups.php'],
        ['PB-SUPPLIER','Supplier Risk Coach','Procurement','supplier risk','Review low-scoring suppliers and contract risks.','/admin/erp/supplier-scorecards.php'],
    ];
    $created=0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('ai_assistant_playbooks').' (playbook_code,playbook_name,module,trigger_phrase,response_template,action_url,status,created_by) VALUES (?,?,?,?,?,?,"active",?)');
    foreach($rows as $r){$stmt->execute([$r[0],$r[1],$r[2],$r[3],$r[4],$r[5],(int)($user['id']??0)?:null]);$created += $stmt->rowCount()>0?1:0;}
    return $created;
}


function biExecutiveMetrics(PDO $pdo): array
{
    $safe=function(string $sql) use ($pdo): float {
        try{return (float)$pdo->query($sql)->fetchColumn();}catch(Throwable $e){return 0.0;}
    };
    return [
        'revenue_mtd'=>$safe('SELECT COALESCE(SUM(total),0) FROM '.table('invoices').' WHERE status<>"cancelled" AND DATE_FORMAT(created_at,"%Y-%m")=DATE_FORMAT(CURDATE(),"%Y-%m")'),
        'cash_collected_mtd'=>$safe('SELECT COALESCE(SUM(amount),0) FROM '.table('payments').' WHERE status="received" AND DATE_FORMAT(COALESCE(paid_at,created_at),"%Y-%m")=DATE_FORMAT(CURDATE(),"%Y-%m")'),
        'open_ar'=>$safe('SELECT COALESCE(SUM(balance_due),0) FROM '.table('invoices').' WHERE balance_due>0 AND status<>"cancelled"'),
        'open_ap'=>$safe('SELECT COALESCE(SUM(balance_due),0) FROM '.table('supplier_invoices').' WHERE balance_due>0 AND status<>"cancelled"'),
        'inventory_value'=>$safe('SELECT COALESCE(SUM(stock_value),0) FROM '.table('warehouse_stock')),
        'open_pipeline'=>$safe('SELECT COALESCE(SUM(value_amount*probability_percent/100),0) FROM '.table('sales_opportunities').' WHERE status="open"'),
        'gross_margin_service'=>$safe('SELECT COALESCE(SUM(total-actual_cost),0) FROM '.table('job_cards')),
        'budget_variance'=>$safe('SELECT COALESCE(SUM(variance_amount),0) FROM '.table('budget_lines')),
    ];
}

function createBiMetric(PDO $pdo, array $data): int
{
    $code=trim((string)($data['metric_code']??''));
    if($code===''){$code=nextScopedDocumentNumber($pdo,'bi_metric',setting('bi_metric_prefix','BIM'),operationalScope($pdo));}
    $code=preg_replace('/[^A-Z0-9_\-]/','',strtoupper($code));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('bi_metric_library').' (metric_code,metric_name,metric_group,metric_source,calculation_type,unit_label,target_value,warning_value,filter_json,active,created_by) VALUES (?,?,?,?,?,?,?,?,?,1,?)')->execute([
        $code,trim((string)($data['metric_name']??$code)),trim((string)($data['metric_group']??'General')),trim((string)($data['metric_source']??'revenue_mtd')),trim((string)($data['calculation_type']??'sum')),trim((string)($data['unit_label']??'')),(float)($data['target_value']??0),(float)($data['warning_value']??0),trim((string)($data['filter_json']??'{}'))?:'{}',(int)($user['id']??0)?:null
    ]);
    return (int)$pdo->lastInsertId();
}

function biMetricLibraryValue(PDO $pdo, string $source): float
{
    $metrics=biExecutiveMetrics($pdo);
    if(isset($metrics[$source])){return (float)$metrics[$source];}
    if(function_exists('kpiMetricValue')){
        try{return (float)kpiMetricValue($pdo,$source);}catch(Throwable $e){return 0.0;}
    }
    return 0.0;
}

function runBiKpiAlertRules(PDO $pdo): int
{
    $rules=$pdo->query('SELECT r.*,k.kpi_name,k.metric_source,k.target_value FROM '.table('bi_kpi_alert_rules').' r LEFT JOIN '.table('report_kpis').' k ON k.id=r.report_kpi_id WHERE r.status="active"')->fetchAll();
    $created=0;
    $ins=$pdo->prepare('INSERT INTO '.table('bi_kpi_alert_events').' (bi_kpi_alert_rule_id,metric_value,threshold_value,severity,status,event_message) VALUES (?,?,?,?, "open", ?)');
    foreach($rules as $rule){
        $value=biMetricLibraryValue($pdo,(string)$rule['metric_source']);
        $threshold=(float)$rule['threshold_value'];
        $trigger=false;
        if($rule['condition_type']==='below_target'){$trigger=$value<$threshold;}
        elseif($rule['condition_type']==='above_limit'){$trigger=$value>$threshold;}
        elseif($rule['condition_type']==='equals'){$trigger=abs($value-$threshold)<0.0001;}
        if($trigger){
            $ins->execute([(int)$rule['id'],$value,$threshold,(string)$rule['severity'],'KPI alert: '.$rule['kpi_name'].' value '.number_format($value,2).' threshold '.number_format($threshold,2)]);
            $pdo->prepare('UPDATE '.table('bi_kpi_alert_rules').' SET last_triggered_at=NOW() WHERE id=?')->execute([(int)$rule['id']]);
            $created++;
        }
    }
    return $created;
}

function createDatasetCache(PDO $pdo, string $reportType, array $filters=[]): int
{
    $rows=reportRows($pdo,$reportType,['limit'=>(int)($filters['limit']??300)]);
    $hash=substr(sha1(json_encode($filters)),0,24);
    $key=$reportType.'-'.$hash.'-'.date('YmdHis');
    $ttl=max(5,(int)setting('dataset_cache_ttl_minutes','60'));
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('report_dataset_cache').' (cache_key,report_type,filter_hash,row_count,data_json,expires_at,created_by) VALUES (?,?,?,?,?,DATE_ADD(NOW(), INTERVAL '.$ttl.' MINUTE),?)');
    $stmt->execute([$key,$reportType,$hash,count($rows),json_encode($rows,JSON_UNESCAPED_SLASHES),(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function createDashboardFilterPreset(PDO $pdo, array $data): int
{
    $number=nextScopedDocumentNumber($pdo,'dashboard_filter',setting('dashboard_filter_prefix','BIF'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('bi_dashboard_filter_presets').' (preset_number,preset_name,dashboard_scope,date_from,date_to,company_id,branch_id,filter_json,is_default,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([
        $number,trim((string)($data['preset_name']??'BI Filter')),trim((string)($data['dashboard_scope']??'executive')),trim((string)($data['date_from']??''))?:null,trim((string)($data['date_to']??''))?:null,(int)($data['company_id']??0)?:null,(int)($data['branch_id']??0)?:null,trim((string)($data['filter_json']??'{}'))?:'{}',!empty($data['is_default'])?1:0,(int)($user['id']??0)?:null
    ]);
    return (int)$pdo->lastInsertId();
}


function createFinanceAutomationRun(PDO $pdo, string $type, int $items, float $amount, string $notes='', ?int $ruleId=null): int
{
    $number=nextScopedDocumentNumber($pdo,'finance_automation_run',setting('finance_automation_run_prefix','FAR'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('finance_automation_runs').' (run_number,finance_automation_rule_id,run_type,status,items_processed,total_amount,run_notes,created_by) VALUES (?,?,?,"completed",?,?,?,?)')->execute([$number,$ruleId,$type,$items,$amount,$notes,(int)($user['id']??0)?:null]);
    return (int)$pdo->lastInsertId();
}

function createSimpleJournal(PDO $pdo, string $entryDate, string $memo, array $lines, string $referenceType='automation', ?int $referenceId=null): int
{
    $debit=0.0;$credit=0.0;
    foreach($lines as $line){$debit += (float)($line['debit']??0);$credit += (float)($line['credit']??0);}
    if(abs($debit-$credit)>0.01){throw new RuntimeException('Journal is not balanced.');}
    $number=nextScopedDocumentNumber($pdo,'journal',setting('journal_prefix','JRN'),operationalScope($pdo));
    $scope=operationalScope($pdo);
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('journal_entries').' (company_id,branch_id,journal_number,entry_date,reference_type,reference_id,memo,status,total_debit,total_credit,posted_at,created_by) VALUES (?,?,?,?,?,?,?,"posted",?,?,NOW(),?)')->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,$number,$entryDate,$referenceType,$referenceId,$memo,$debit,$credit,(int)($user['id']??0)?:null]);
    $journalId=(int)$pdo->lastInsertId();
    $stmt=$pdo->prepare('INSERT INTO '.table('journal_lines').' (journal_entry_id,account_id,description,debit,credit) VALUES (?,?,?,?,?)');
    foreach($lines as $line){$stmt->execute([$journalId,(int)$line['account_id'],(string)($line['description']??$memo),(float)($line['debit']??0),(float)($line['credit']??0)]);}
    return $journalId;
}

function runRecurringJournals(PDO $pdo, string $runDate): array
{
    $templates=$pdo->prepare('SELECT * FROM '.table('recurring_journal_templates').' WHERE status="active" AND (next_run_date IS NULL OR next_run_date<=?) ORDER BY id');
    $templates->execute([$runDate]);
    $created=0;$amount=0.0;
    foreach($templates->fetchAll() as $template){
        $linesStmt=$pdo->prepare('SELECT * FROM '.table('recurring_journal_template_lines').' WHERE recurring_journal_template_id=?');
        $linesStmt->execute([(int)$template['id']]);
        $lines=[];
        foreach($linesStmt->fetchAll() as $l){$lines[]=['account_id'=>(int)$l['account_id'],'description'=>$l['description'],'debit'=>(float)$l['debit'],'credit'=>(float)$l['credit']];$amount += (float)$l['debit'];}
        if($lines){
            createSimpleJournal($pdo,$runDate,(string)$template['memo'],$lines,'recurring_journal',(int)$template['id']);
            $next=match((string)$template['frequency']){'weekly'=>date('Y-m-d',strtotime($runDate.' +7 days')),'quarterly'=>date('Y-m-d',strtotime($runDate.' +3 months')),'yearly'=>date('Y-m-d',strtotime($runDate.' +1 year')),default=>date('Y-m-d',strtotime($runDate.' +1 month'))};
            $pdo->prepare('UPDATE '.table('recurring_journal_templates').' SET last_run_date=?,next_run_date=? WHERE id=?')->execute([$runDate,$next,(int)$template['id']]);
            $created++;
        }
    }
    createFinanceAutomationRun($pdo,'recurring_journals',$created,$amount,'Recurring journals run on '.$runDate);
    return ['created'=>$created,'amount'=>round($amount,2)];
}

function createBudgetVersion(PDO $pdo, string $name, string $from, string $to, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'budget_version',setting('budget_prefix','BUD'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('budget_versions').' (budget_number,budget_name,date_from,date_to,status,created_by,notes) VALUES (?,?,?,?, "draft", ?, ?)')->execute([$number,$name,$from,$to,(int)($user['id']??0)?:null,$notes]);
    return (int)$pdo->lastInsertId();
}

function refreshBudgetActuals(PDO $pdo, int $budgetVersionId): void
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('budget_versions').' WHERE id=? LIMIT 1');$stmt->execute([$budgetVersionId]);$budget=$stmt->fetch();
    if(!$budget){throw new RuntimeException('Budget version not found.');}
    $lines=$pdo->prepare('SELECT * FROM '.table('budget_lines').' WHERE budget_version_id=?');$lines->execute([$budgetVersionId]);
    $update=$pdo->prepare('UPDATE '.table('budget_lines').' SET actual_amount=?,variance_amount=?,variance_percent=? WHERE id=?');
    foreach($lines->fetchAll() as $line){
        $q=$pdo->prepare('SELECT COALESCE(SUM(debit-credit),0) FROM '.table('journal_lines').' jl INNER JOIN '.table('journal_entries').' je ON je.id=jl.journal_entry_id WHERE jl.account_id=? AND je.status="posted" AND je.entry_date BETWEEN ? AND ?');
        $q->execute([(int)$line['account_id'],$budget['date_from'],$budget['date_to']]);
        $actual=(float)$q->fetchColumn();$variance=$actual-(float)$line['budget_amount'];$percent=(float)$line['budget_amount']!=0?round(($variance/(float)$line['budget_amount'])*100,2):0;
        $update->execute([$actual,$variance,$percent,(int)$line['id']]);
    }
}

function generateCashFlowForecast(PDO $pdo, string $name, string $from, string $to, float $openingCash): int
{
    $number=nextScopedDocumentNumber($pdo,'cash_flow_forecast',setting('cash_flow_forecast_prefix','CFF'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('cash_flow_forecasts').' (forecast_number,forecast_name,date_from,date_to,opening_cash,status,created_by) VALUES (?,?,?,?,?,"generated",?)')->execute([$number,$name,$from,$to,$openingCash,(int)($user['id']??0)?:null]);
    $forecastId=(int)$pdo->lastInsertId();
    $line=$pdo->prepare('INSERT INTO '.table('cash_flow_forecast_lines').' (cash_flow_forecast_id,line_date,source_type,source_id,description,inflow,outflow) VALUES (?,?,?,?,?,?,?)');
    $in=0.0;$out=0.0;
    $inv=$pdo->prepare('SELECT id,invoice_number,customer_name,balance_due,created_at FROM '.table('invoices').' WHERE balance_due>0 AND DATE(created_at) BETWEEN ? AND ?');$inv->execute([$from,$to]);
    foreach($inv->fetchAll() as $r){$amt=(float)$r['balance_due'];$in+=$amt;$line->execute([$forecastId,substr((string)$r['created_at'],0,10),'invoice',(int)$r['id'],'Expected collection '.$r['invoice_number'].' · '.$r['customer_name'],$amt,0]);}
    $exp=$pdo->prepare('SELECT id,expense_number,vendor_name,balance_due,due_date FROM '.table('expenses').' WHERE balance_due>0 AND (due_date BETWEEN ? AND ? OR (due_date IS NULL AND expense_date BETWEEN ? AND ?))');$exp->execute([$from,$to,$from,$to]);
    foreach($exp->fetchAll() as $r){$amt=(float)$r['balance_due'];$out+=$amt;$line->execute([$forecastId,$r['due_date']?:$from,'expense',(int)$r['id'],'Expected payment '.$r['expense_number'].' · '.$r['vendor_name'],0,$amt]);}
    $net=$in-$out;$closing=$openingCash+$net;
    $pdo->prepare('UPDATE '.table('cash_flow_forecasts').' SET forecast_inflow=?,forecast_outflow=?,net_cash_flow=?,closing_cash=? WHERE id=?')->execute([$in,$out,$net,$closing,$forecastId]);
    createFinanceAutomationRun($pdo,'cash_flow_forecast',1,$closing,'Cash flow forecast generated.');
    return $forecastId;
}

function agingBuckets(PDO $pdo, string $source, string $date): array
{
    $b=['current'=>0.0,'1_30'=>0.0,'31_60'=>0.0,'61_90'=>0.0,'over_90'=>0.0];
    if($source==='ar'){
        $rows=$pdo->query('SELECT balance_due amount,DATE(created_at) due_date FROM '.table('invoices').' WHERE balance_due>0')->fetchAll();
    }else{
        $rows=$pdo->query('SELECT balance_due amount,COALESCE(due_date,expense_date) due_date FROM '.table('expenses').' WHERE balance_due>0')->fetchAll();
    }
    $asOf=strtotime($date);
    foreach($rows as $r){$days=floor(($asOf-strtotime((string)$r['due_date']))/86400);$amt=(float)$r['amount'];if($days<=0){$b['current']+=$amt;}elseif($days<=30){$b['1_30']+=$amt;}elseif($days<=60){$b['31_60']+=$amt;}elseif($days<=90){$b['61_90']+=$amt;}else{$b['over_90']+=$amt;}}
    return $b;
}

function generateAgingSnapshots(PDO $pdo, string $snapshotDate): array
{
    $ar=agingBuckets($pdo,'ar',$snapshotDate);$ap=agingBuckets($pdo,'ap',$snapshotDate);
    $arNo=nextScopedDocumentNumber($pdo,'ar_aging',setting('ar_aging_prefix','ARAGE'),operationalScope($pdo));
    $apNo=nextScopedDocumentNumber($pdo,'ap_aging',setting('ap_aging_prefix','APAGE'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('ar_aging_snapshots').' (snapshot_number,snapshot_date,current_amount,days_1_30,days_31_60,days_61_90,days_over_90,total_amount,status) VALUES (?,?,?,?,?,?,?,?,"generated")')->execute([$arNo,$snapshotDate,$ar['current'],$ar['1_30'],$ar['31_60'],$ar['61_90'],$ar['over_90'],array_sum($ar)]);
    $pdo->prepare('INSERT INTO '.table('ap_aging_snapshots').' (snapshot_number,snapshot_date,current_amount,days_1_30,days_31_60,days_61_90,days_over_90,total_amount,status) VALUES (?,?,?,?,?,?,?,?,"generated")')->execute([$apNo,$snapshotDate,$ap['current'],$ap['1_30'],$ap['31_60'],$ap['61_90'],$ap['over_90'],array_sum($ap)]);
    return ['ar'=>array_sum($ar),'ap'=>array_sum($ap)];
}

function generateCollectionTasks(PDO $pdo, string $asOfDate): int
{
    $days=max(1,(int)setting('collection_task_days_overdue','7'));
    $rows=$pdo->prepare('SELECT id,invoice_number,customer_id,customer_name,customer_email,balance_due,DATE(created_at) invoice_date FROM '.table('invoices').' WHERE balance_due>0 AND DATE(created_at)<=DATE_SUB(?, INTERVAL '.$days.' DAY)');
    $rows->execute([$asOfDate]);
    $created=0;
    $ins=$pdo->prepare('INSERT INTO '.table('collection_tasks').' (task_number,customer_id,invoice_id,customer_name,customer_email,balance_due,due_date,priority,status,next_followup_date,notes) VALUES (?,?,?,?,?,?,?,"high","open",?,?)');
    foreach($rows->fetchAll() as $r){$exists=$pdo->prepare('SELECT id FROM '.table('collection_tasks').' WHERE invoice_id=? AND status="open" LIMIT 1');$exists->execute([(int)$r['id']]);if($exists->fetchColumn()){continue;}$num=nextScopedDocumentNumber($pdo,'collection_task',setting('collection_task_prefix','COLL'),operationalScope($pdo));$next=date('Y-m-d',strtotime($asOfDate.' +2 days'));$ins->execute([$num,(int)($r['customer_id']??0)?:null,(int)$r['id'],$r['customer_name'],$r['customer_email'],(float)$r['balance_due'],$r['invoice_date'],$next,'Auto-generated for overdue invoice '.$r['invoice_number']]);$created++;}
    createFinanceAutomationRun($pdo,'collection_tasks',$created,0,'Collection tasks generated.');
    return $created;
}

function createSupplierPaymentRun(PDO $pdo, string $runDate, string $dateTo, string $notes=''): int
{
    $number=nextScopedDocumentNumber($pdo,'supplier_payment_run',setting('supplier_payment_run_prefix','SPR'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('supplier_payment_runs').' (payment_run_number,run_date,date_to,status,created_by,notes) VALUES (?,?,?,"draft",?,?)')->execute([$number,$runDate,$dateTo,(int)($user['id']??0)?:null,$notes]);
    $runId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('expenses').' WHERE balance_due>0 AND payment_status<>"paid" AND (due_date IS NULL OR due_date<=?) ORDER BY due_date,expense_number');
    $items->execute([$dateTo]);$total=0.0;
    $ins=$pdo->prepare('INSERT INTO '.table('supplier_payment_run_items').' (supplier_payment_run_id,expense_id,supplier_id,vendor_name,expense_number,due_date,amount_due,status) VALUES (?,?,?,?,?,?,?,"pending")');
    foreach($items->fetchAll() as $e){$amt=(float)$e['balance_due'];$total+=$amt;$ins->execute([$runId,(int)$e['id'],(int)($e['supplier_id']??0)?:null,$e['vendor_name'],$e['expense_number'],$e['due_date'],$amt]);}
    $pdo->prepare('UPDATE '.table('supplier_payment_runs').' SET total_amount=? WHERE id=?')->execute([$total,$runId]);
    return $runId;
}


function createFinancialClosePeriod(PDO $pdo, string $periodName, string $dateFrom, string $dateTo, string $notes = ''): int
{
    $number=nextScopedDocumentNumber($pdo,'financial_close',setting('financial_close_prefix','CLOSE'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('financial_close_periods').' (close_number,period_name,date_from,date_to,status,owner_user_id,notes) VALUES (?,?,?,?, "open", ?, ?)')->execute([$number,$periodName,$dateFrom,$dateTo,(int)($user['id']??0)?:null,$notes]);
    $periodId=(int)$pdo->lastInsertId();
    if(setting('financial_close_auto_tasks','1')==='1'){
        $tasks=[['ar_review','AR Aging Review','Receivables'],['ap_review','AP Aging Review','Payables'],['bank_reconciliation','Bank Reconciliation Complete','Cash & Bank'],['inventory_valuation','Inventory Valuation Reviewed','Inventory'],['fixed_asset_depreciation','Fixed Asset Depreciation Posted','Fixed Assets'],['payroll_posted','Payroll Posted','Payroll'],['tax_return_prepared','VAT/Tax Return Prepared','Tax'],['financial_reports_reviewed','Financial Statements Reviewed','Reporting'],['backup_archive','Backup & Close Evidence Archived','Controls']];
        $stmt=$pdo->prepare('INSERT INTO '.table('financial_close_tasks').' (financial_close_period_id,task_key,task_name,category,status) VALUES (?,?,?,?, "open")');
        foreach($tasks as $task){$stmt->execute([$periodId,$task[0],$task[1],$task[2]]);}
    }
    logActivity($pdo,'Financial Close','financial_close_created','Financial close period '.$number.' created.','financial_close_period',$periodId);
    return $periodId;
}

function calculateBankBookBalance(PDO $pdo, int $bankAccountId, string $dateTo): float
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('bank_accounts').' WHERE id=? LIMIT 1');$stmt->execute([$bankAccountId]);$bank=$stmt->fetch();
    if(!$bank){return 0.0;}
    $opening=(float)($bank['opening_balance']??0);$cashAccountId=(int)($bank['cash_account_id']??0);
    if($cashAccountId>0){
        $q=$pdo->prepare('SELECT COALESCE(SUM(jl.debit-jl.credit),0) FROM '.table('journal_lines').' jl INNER JOIN '.table('journal_entries').' je ON je.id=jl.journal_entry_id WHERE jl.account_id=? AND je.entry_date<=? AND je.status="posted"');
        $q->execute([$cashAccountId,$dateTo]);return round($opening+(float)$q->fetchColumn(),2);
    }
    $payments=$pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM '.table('payments').' WHERE bank_account_id=? AND DATE(paid_at)<=? AND status IN ("received","paid")');
    $payments->execute([$bankAccountId,$dateTo]);return round($opening+(float)$payments->fetchColumn(),2);
}

function createBankReconciliation(PDO $pdo, int $bankAccountId, string $dateFrom, string $dateTo, float $statementEndingBalance, string $notes=''): int
{
    $book=calculateBankBookBalance($pdo,$bankAccountId,$dateTo);$variance=round($statementEndingBalance-$book,2);
    $number=nextScopedDocumentNumber($pdo,'bank_reconciliation',setting('bank_reconciliation_prefix','BREC'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('bank_reconciliations').' (reconciliation_number,bank_account_id,date_from,date_to,statement_ending_balance,book_ending_balance,variance,status,notes) VALUES (?,?,?,?,?,?,?,"draft",?)');
    $stmt->execute([$number,$bankAccountId,$dateFrom,$dateTo,$statementEndingBalance,$book,$variance,$notes]);return (int)$pdo->lastInsertId();
}

function runFixedAssetDepreciation(PDO $pdo, string $periodDate): array
{
    $assets=$pdo->query('SELECT * FROM '.table('fixed_assets').' WHERE status="active" ORDER BY asset_number')->fetchAll();
    $created=0;$amount=0.0;
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('fixed_asset_depreciation').' (fixed_asset_id,period_date,depreciation_amount,accumulated_depreciation,book_value,status,notes) VALUES (?,?,?,?,?,"calculated",?)');
    $update=$pdo->prepare('UPDATE '.table('fixed_assets').' SET accumulated_depreciation=?,book_value=?,status=? WHERE id=?');
    foreach($assets as $asset){
        $base=max(0,(float)$asset['purchase_cost']-(float)$asset['salvage_value']);$life=max(1,(int)$asset['useful_life_months']);$monthly=round($base/$life,2);
        $newAccum=min($base,round((float)$asset['accumulated_depreciation']+$monthly,2));$book=max((float)$asset['salvage_value'],round((float)$asset['purchase_cost']-$newAccum,2));$status=$newAccum >= $base ? 'depreciated' : 'active';
        $before=$pdo->prepare('SELECT COUNT(*) FROM '.table('fixed_asset_depreciation').' WHERE fixed_asset_id=? AND period_date=?');$before->execute([(int)$asset['id'],$periodDate]);
        if((int)$before->fetchColumn()===0){$stmt->execute([(int)$asset['id'],$periodDate,$monthly,$newAccum,$book,'Monthly straight-line depreciation.']);$update->execute([$newAccum,$book,$status,(int)$asset['id']]);$created++;$amount+=$monthly;}
    }
    return ['created'=>$created,'amount'=>round($amount,2)];
}

function taxReturnSummary(PDO $pdo, string $from, string $to): array
{
    $outputStmt=$pdo->prepare('SELECT COALESCE(SUM(tax),0) tax,COALESCE(SUM(subtotal-discount),0) taxable FROM '.table('invoices').' WHERE DATE(created_at) BETWEEN ? AND ? AND status NOT IN ("cancelled","draft")');
    $outputStmt->execute([$from,$to]);$output=$outputStmt->fetch() ?: ['tax'=>0,'taxable'=>0];
    $inputStmt=$pdo->prepare('SELECT COALESCE(SUM(tax),0) tax,COALESCE(SUM(amount),0) taxable FROM '.table('expenses').' WHERE expense_date BETWEEN ? AND ? AND approval_status IN ("approved","not_required")');
    $inputStmt->execute([$from,$to]);$input=$inputStmt->fetch() ?: ['tax'=>0,'taxable'=>0];
    return ['output_tax'=>round((float)$output['tax'],2),'output_taxable'=>round((float)$output['taxable'],2),'input_tax'=>round((float)$input['tax'],2),'input_taxable'=>round((float)$input['taxable'],2),'net_tax'=>round((float)$output['tax']-(float)$input['tax'],2)];
}

function createTaxReturn(PDO $pdo, string $periodName, string $from, string $to, string $notes=''): int
{
    $summary=taxReturnSummary($pdo,$from,$to);
    $number=nextScopedDocumentNumber($pdo,'tax_return',setting('tax_return_prefix','TAX'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('tax_returns').' (return_number,period_name,date_from,date_to,output_tax,input_tax,net_tax,status,notes) VALUES (?,?,?,?,?,?,?,"draft",?)');
    $stmt->execute([$number,$periodName,$from,$to,$summary['output_tax'],$summary['input_tax'],$summary['net_tax'],$notes]);
    $returnId=(int)$pdo->lastInsertId();
    $line=$pdo->prepare('INSERT INTO '.table('tax_return_lines').' (tax_return_id,source_type,source_id,source_number,taxable_amount,tax_amount,direction) VALUES (?,?,?,?,?,?,?)');
    $invoices=$pdo->prepare('SELECT id,invoice_number,subtotal,discount,tax FROM '.table('invoices').' WHERE DATE(created_at) BETWEEN ? AND ? AND status NOT IN ("cancelled","draft")');$invoices->execute([$from,$to]);
    foreach($invoices->fetchAll() as $row){$line->execute([$returnId,'invoice',(int)$row['id'],$row['invoice_number'],(float)$row['subtotal']-(float)$row['discount'],(float)$row['tax'],'output']);}
    $expenses=$pdo->prepare('SELECT id,expense_number,amount,tax FROM '.table('expenses').' WHERE expense_date BETWEEN ? AND ? AND approval_status IN ("approved","not_required")');$expenses->execute([$from,$to]);
    foreach($expenses->fetchAll() as $row){$line->execute([$returnId,'expense',(int)$row['id'],$row['expense_number'],(float)$row['amount'],(float)$row['tax'],'input']);}
    return $returnId;
}


function employeeLabel(array $employee): string
{
    return trim(($employee['employee_code'] ?? '') . ' · ' . ($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
}

function generatePayrollRun(PDO $pdo, int $periodId): int
{
    $periodStmt=$pdo->prepare('SELECT * FROM '.table('payroll_periods').' WHERE id=? LIMIT 1 FOR UPDATE');
    $periodStmt->execute([$periodId]);
    $period=$periodStmt->fetch();
    if(!$period){throw new RuntimeException('Payroll period not found.');}
    $existing=$pdo->prepare('SELECT id FROM '.table('payroll_runs').' WHERE payroll_period_id=? LIMIT 1');
    $existing->execute([$periodId]);
    $existingId=(int)$existing->fetchColumn();
    if($existingId>0){return $existingId;}
    $number=nextScopedDocumentNumber($pdo,'payroll_run',setting('payroll_run_prefix','PAY'),operationalScope($pdo));
    $user=currentUser();
    $pdo->prepare('INSERT INTO '.table('payroll_runs').' (payroll_number,payroll_period_id,status,created_by,notes) VALUES (?,?,"draft",?,?)')->execute([$number,$periodId,(int)($user['id']??0)?:null,'Generated payroll for '.$period['period_name']]);
    $runId=(int)$pdo->lastInsertId();
    $employees=$pdo->query('SELECT * FROM '.table('employees').' WHERE status="active" ORDER BY employee_code')->fetchAll();
    $itemStmt=$pdo->prepare('INSERT INTO '.table('payroll_run_items').' (payroll_run_id,employee_id,basic_salary,allowances,overtime_amount,commission_amount,expense_reimbursement,deductions,gross_pay,net_pay) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $grossTotal=0.0;$deductionTotal=0.0;$netTotal=0.0;
    foreach($employees as $employee){
        $basic=(float)($employee['salary']??0);
        $overtimeStmt=$pdo->prepare('SELECT COALESCE(SUM(overtime_hours),0) FROM '.table('attendance_records').' WHERE employee_id=? AND work_date BETWEEN ? AND ?');
        $overtimeStmt->execute([(int)$employee['id'],$period['start_date'],$period['end_date']]);
        $overtimeHours=(float)$overtimeStmt->fetchColumn();
        $overtimeAmount=round($overtimeHours*(float)setting('payroll_overtime_hour_rate','25'),2);
        $commission=0.0;
        if(setting('payroll_auto_include_commissions','1')==='1'){
            $commissionStmt=$pdo->prepare('SELECT COALESCE(SUM(commission_amount),0) FROM '.table('commission_records').' WHERE employee_id=? AND status IN ("earned","approved") AND (period_date IS NULL OR period_date BETWEEN ? AND ?)');
            $commissionStmt->execute([(int)$employee['id'],$period['start_date'],$period['end_date']]);
            $commission=(float)$commissionStmt->fetchColumn();
        }
        $expense=0.0;
        if(setting('payroll_auto_include_approved_expenses','1')==='1'){
            $expenseStmt=$pdo->prepare('SELECT COALESCE(SUM(total_amount),0) FROM '.table('employee_expense_claims').' WHERE employee_id=? AND approval_status="approved" AND status IN ("approved","paid") AND claim_date BETWEEN ? AND ?');
            $expenseStmt->execute([(int)$employee['id'],$period['start_date'],$period['end_date']]);
            $expense=(float)$expenseStmt->fetchColumn();
        }
        $deductions=0.0;
        $gross=round($basic+$overtimeAmount+$commission+$expense,2);
        $net=round($gross-$deductions,2);
        $itemStmt->execute([$runId,(int)$employee['id'],$basic,0,$overtimeAmount,$commission,$expense,$deductions,$gross,$net]);
        $grossTotal+=$gross;$deductionTotal+=$deductions;$netTotal+=$net;
    }
    $pdo->prepare('UPDATE '.table('payroll_runs').' SET gross_total=?,deductions_total=?,net_total=? WHERE id=?')->execute([round($grossTotal,2),round($deductionTotal,2),round($netTotal,2),$runId]);
    logActivity($pdo,'Payroll','payroll_run_generated','Payroll run '.$number.' generated.','payroll_run',$runId);
    return $runId;
}

function approvePayrollRun(PDO $pdo, int $runId): void
{
    $user=currentUser();
    $pdo->prepare('UPDATE '.table('payroll_runs').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=? AND status="draft"')->execute([(int)($user['id']??0)?:null,$runId]);
    logActivity($pdo,'Payroll','payroll_run_approved','Payroll run #'.$runId.' approved.','payroll_run',$runId);
}

function createCommissionFromSale(PDO $pdo, int $employeeId, int $planId, float $saleAmount, ?int $invoiceId=null, ?int $salesOrderId=null, string $notes=''): int
{
    $planStmt=$pdo->prepare('SELECT * FROM '.table('commission_plans').' WHERE id=? LIMIT 1');
    $planStmt->execute([$planId]);
    $plan=$planStmt->fetch();
    if(!$plan){throw new RuntimeException('Commission plan not found.');}
    $threshold=(float)($plan['threshold_amount']??0);
    $base=max(0,$saleAmount-$threshold);
    $commission=round($base*((float)$plan['commission_rate']/100),2);
    $number=nextScopedDocumentNumber($pdo,'commission_record',setting('commission_prefix','COM'),operationalScope($pdo));
    $stmt=$pdo->prepare('INSERT INTO '.table('commission_records').' (commission_number,commission_plan_id,employee_id,invoice_id,sales_order_id,sale_amount,commission_amount,status,period_date,notes) VALUES (?,?,?,?,?,?,?,"earned",DATE(NOW()),?)');
    $stmt->execute([$number,$planId,$employeeId,$invoiceId,$salesOrderId,$saleAmount,$commission,$notes]);
    return (int)$pdo->lastInsertId();
}


function calculateLeadScore(PDO $pdo, int $leadId): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('crm_leads').' WHERE id=? LIMIT 1');
    $stmt->execute([$leadId]);
    $lead=$stmt->fetch();
    if(!$lead){throw new RuntimeException('Lead not found.');}
    $rules=$pdo->query('SELECT * FROM '.table('lead_score_rules').' WHERE status="active" ORDER BY id ASC')->fetchAll();
    $score=0;$events=[];
    foreach($rules as $rule){
        $delta=0;$key=(string)$rule['condition_key'];
        if($key==='has_email' && trim((string)$lead['email'])!==''){$delta=(int)$rule['score_value'];}
        if($key==='has_phone' && trim((string)$lead['phone'])!==''){$delta=(int)$rule['score_value'];}
        if($key==='high_value' && (float)$lead['estimated_value']>=5000){$delta=(int)$rule['score_value'];}
        if($key==='high_probability' && (int)$lead['probability']>=60){$delta=(int)$rule['score_value'];}
        if($key==='b2b_lead' && (string)$lead['customer_type']==='b2b'){$delta=(int)$rule['score_value'];}
        if($key==='followup_due' && !empty($lead['next_follow_up']) && strtotime((string)$lead['next_follow_up'])<=time()){$delta=(int)$rule['score_value'];}
        if($delta>0){$score+=$delta;$events[]=[(int)$rule['id'],$delta,$rule['rule_name']];}
    }
    $eventStmt=$pdo->prepare('INSERT INTO '.table('lead_score_events').' (lead_id,lead_score_rule_id,score_delta,total_score,reason) VALUES (?,?,?,?,?)');
    foreach($events as $event){$eventStmt->execute([$leadId,$event[0],$event[1],$score,$event[2]]);}
    if($score >= (int)setting('crm_lead_score_hot_threshold','70')){$status='hot';}
    elseif($score >= (int)setting('crm_lead_score_warm_threshold','40')){$status='warm';}
    else{$status='cold';}
    $pdo->prepare('UPDATE '.table('crm_leads').' SET status=? WHERE id=? AND converted_customer_id IS NULL')->execute([$status,$leadId]);
    return $score;
}

function createOpportunityFromLead(PDO $pdo, int $leadId): int
{
    $leadStmt=$pdo->prepare('SELECT * FROM '.table('crm_leads').' WHERE id=? LIMIT 1 FOR UPDATE');
    $leadStmt->execute([$leadId]);
    $lead=$leadStmt->fetch();
    if(!$lead){throw new RuntimeException('Lead not found.');}
    $existing=$pdo->prepare('SELECT id FROM '.table('sales_opportunities').' WHERE lead_id=? LIMIT 1');
    $existing->execute([$leadId]);
    $existingId=(int)$existing->fetchColumn();
    if($existingId>0){return $existingId;}
    $stageId=(int)$pdo->query('SELECT id FROM '.table('sales_pipeline_stages').' WHERE stage_key="qualified" LIMIT 1')->fetchColumn();
    $scope=['company_id'=>(int)($lead['company_id']??0),'branch_id'=>(int)($lead['branch_id']??0),'warehouse_id'=>(int)setting('default_warehouse_id','0'),'location_id'=>(int)setting('default_location_id','0')];
    $number=nextScopedDocumentNumber($pdo,'sales_opportunity',setting('sales_opportunity_prefix','OPP'),$scope);
    $value=(float)($lead['estimated_value']??0);$prob=(int)($lead['probability']??30);$weighted=round($value*$prob/100,2);
    $stmt=$pdo->prepare('INSERT INTO '.table('sales_opportunities').' (company_id,branch_id,opportunity_number,lead_id,stage_id,owner_user_id,title,source,value_amount,probability,weighted_value,expected_close_date,status,next_follow_up,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, "open", ?, ?)');
    $stmt->execute([(int)($lead['company_id']??0)?:null,(int)($lead['branch_id']??0)?:null,$number,$leadId,$stageId?:null,(int)($lead['assigned_to']??0)?:null,'Opportunity for '.$lead['name'],(string)($lead['source']??''),$value,$prob,$weighted,!empty($lead['next_follow_up'])?$lead['next_follow_up']:null,!empty($lead['next_follow_up'])?$lead['next_follow_up']:null,'Created from CRM lead.']);
    $oppId=(int)$pdo->lastInsertId();
    logActivity($pdo,'CRM','opportunity_created_from_lead','Opportunity '.$number.' created from lead '.$lead['name'].'.','sales_opportunity',$oppId);
    return $oppId;
}

function refreshCustomerSegment(PDO $pdo, int $segmentId): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('customer_segments').' WHERE id=? LIMIT 1');
    $stmt->execute([$segmentId]);
    $segment=$stmt->fetch();
    if(!$segment){throw new RuntimeException('Segment not found.');}
    $criteria=json_decode((string)$segment['criteria_json'],true) ?: [];
    $pdo->prepare('DELETE FROM '.table('customer_segment_members').' WHERE customer_segment_id=?')->execute([$segmentId]);
    $insert=$pdo->prepare('INSERT IGNORE INTO '.table('customer_segment_members').' (customer_segment_id,customer_id,lead_id,member_type) VALUES (?,?,?,?)');
    $count=0;
    if(isset($criteria['customer_type'])){
        $q=$pdo->prepare('SELECT id FROM '.table('customers').' WHERE customer_type=? AND status="active"');
        $q->execute([(string)$criteria['customer_type']]);
        foreach($q->fetchAll() as $r){$insert->execute([$segmentId,(int)$r['id'],null,'customer']);$count++;}
    } elseif(isset($criteria['credit_status'])){
        $q=$pdo->prepare('SELECT id FROM '.table('customers').' WHERE credit_status=?');
        $q->execute([(string)$criteria['credit_status']]);
        foreach($q->fetchAll() as $r){$insert->execute([$segmentId,(int)$r['id'],null,'customer']);$count++;}
    } elseif(isset($criteria['lead_score_gte'])){
        $leadIds=$pdo->query('SELECT lead_id,MAX(total_score) score FROM '.table('lead_score_events').' GROUP BY lead_id HAVING score >= '.(int)$criteria['lead_score_gte'])->fetchAll();
        foreach($leadIds as $r){$insert->execute([$segmentId,null,(int)$r['lead_id'],'lead']);$count++;}
    }
    $pdo->prepare('UPDATE '.table('customer_segments').' SET member_count=? WHERE id=?')->execute([$count,$segmentId]);
    return $count;
}

function runCrmAutomationRule(PDO $pdo, int $ruleId): array
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('crm_automation_rules').' WHERE id=? LIMIT 1');
    $stmt->execute([$ruleId]);
    $rule=$stmt->fetch();
    if(!$rule){throw new RuntimeException('CRM automation rule not found.');}
    $runNumber=nextScopedDocumentNumber($pdo,'crm_automation_run','CAR',operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('crm_automation_runs').' (crm_automation_rule_id,run_number,status,started_at) VALUES (?,?,"running",NOW())')->execute([$ruleId,$runNumber]);
    $runId=(int)$pdo->lastInsertId();
    $checked=0;$created=0;$summary='No action.';
    try{
        if((string)$rule['trigger_event']==='lead_followup_due'){
            $rows=$pdo->query('SELECT * FROM '.table('crm_leads').' WHERE converted_customer_id IS NULL AND next_follow_up IS NOT NULL AND next_follow_up <= DATE(NOW()) ORDER BY next_follow_up ASC LIMIT 100')->fetchAll();
            $checked=count($rows);
            foreach($rows as $lead){
                createNotification($pdo,['role_slug'=>'sales-online-orders','title'=>'CRM follow-up due','message'=>'Follow up with lead '.$lead['name'].' today.','severity'=>'warning','link_url'=>ADMIN_URL.'/erp/crm.php']);
                $created++;
            }
            $summary='Created '.$created.' CRM follow-up notifications.';
        } elseif((string)$rule['trigger_event']==='high_value_lead'){
            $rows=$pdo->query('SELECT id FROM '.table('crm_leads').' WHERE converted_customer_id IS NULL AND estimated_value >= 5000 ORDER BY estimated_value DESC LIMIT 100')->fetchAll();
            $checked=count($rows);
            foreach($rows as $lead){createOpportunityFromLead($pdo,(int)$lead['id']);$created++;}
            $summary='Created/confirmed '.$created.' opportunities from high-value leads.';
        }
        $pdo->prepare('UPDATE '.table('crm_automation_runs').' SET status="success",records_checked=?,actions_created=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$checked,$created,$summary,$runId]);
        $pdo->prepare('UPDATE '.table('crm_automation_rules').' SET last_run_at=NOW() WHERE id=?')->execute([$ruleId]);
        return ['status'=>'success','checked'=>$checked,'created'=>$created,'summary'=>$summary,'run_number'=>$runNumber];
    }catch(Throwable $e){
        $pdo->prepare('UPDATE '.table('crm_automation_runs').' SET status="failed",records_checked=?,actions_created=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$checked,$created,$e->getMessage(),$runId]);
        throw $e;
    }
}


function calculateRfqQuoteRank(PDO $pdo, int $quoteId): float
{
    $stmt=$pdo->prepare('SELECT total_amount,delivery_days FROM '.table('rfq_supplier_quotes').' WHERE id=? LIMIT 1');
    $stmt->execute([$quoteId]);
    $quote=$stmt->fetch();
    if(!$quote){return 0.0;}
    $price=(float)($quote['total_amount']??0);
    $days=max(0,(int)($quote['delivery_days']??0));
    $score=max(0,1000000-$price)-($days*100);
    $pdo->prepare('UPDATE '.table('rfq_supplier_quotes').' SET rank_score=? WHERE id=?')->execute([$score,$quoteId]);
    return $score;
}

function createRfqFromRequisition(PDO $pdo, int $requisitionId, array $supplierIds = []): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('purchase_requisitions').' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$requisitionId]);
    $req=$stmt->fetch();
    if(!$req){throw new RuntimeException('Purchase requisition not found.');}
    if(!in_array((string)$req['status'],['approved','converted'],true)){throw new RuntimeException('Only approved requisitions can be converted to RFQ.');}
    $scope=['company_id'=>(int)($req['company_id']??0),'branch_id'=>(int)($req['branch_id']??0),'warehouse_id'=>(int)($req['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $number=nextScopedDocumentNumber($pdo,'rfq',setting('rfq_prefix','RFQ'),$scope);
    $user=currentUser();
    $stmt=$pdo->prepare('INSERT INTO '.table('rfqs').' (company_id,branch_id,warehouse_id,rfq_number,source_requisition_id,title,description,request_date,due_date,status,created_by,notes) VALUES (?,?,?,?,?,?,?,?,?,"draft",?,?)');
    $stmt->execute([(int)($req['company_id']??0)?:null,(int)($req['branch_id']??0)?:null,(int)($req['warehouse_id']??0)?:null,$number,$requisitionId,'RFQ for '.$req['requisition_number'],(string)($req['justification']??''),date('Y-m-d'),$req['required_date']?:null,(int)($user['id']??0)?:null,'Created from requisition '.$req['requisition_number']]);
    $rfqId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('purchase_requisition_items').' WHERE purchase_requisition_id=? ORDER BY id ASC');
    $items->execute([$requisitionId]);
    $itemInsert=$pdo->prepare('INSERT INTO '.table('rfq_items').' (rfq_id,product_id,description,quantity,target_unit_cost,required_date,notes) VALUES (?,?,?,?,?,?,?)');
    foreach($items->fetchAll() as $item){
        $itemInsert->execute([$rfqId,(int)($item['product_id']??0)?:null,(string)($item['description']??''),(float)($item['quantity']??0),(float)($item['estimated_unit_cost']??0),$req['required_date']?:null,'From requisition line #'.$item['id']]);
    }
    foreach($supplierIds as $supplierId){if((int)$supplierId>0){inviteSupplierToRfq($pdo,$rfqId,(int)$supplierId);}}
    logActivity($pdo,'RFQ','rfq_created_from_requisition','RFQ '.$number.' created from requisition '.$req['requisition_number'].'.','rfq',$rfqId);
    return $rfqId;
}

function inviteSupplierToRfq(PDO $pdo, int $rfqId, int $supplierId): int
{
    $rfqStmt=$pdo->prepare('SELECT * FROM '.table('rfqs').' WHERE id=? LIMIT 1');
    $rfqStmt->execute([$rfqId]);$rfq=$rfqStmt->fetch();
    if(!$rfq){throw new RuntimeException('RFQ not found.');}
    $scope=['company_id'=>(int)($rfq['company_id']??0),'branch_id'=>(int)($rfq['branch_id']??0),'warehouse_id'=>(int)($rfq['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $number=nextScopedDocumentNumber($pdo,'rfq_invitation',setting('rfq_invitation_prefix','RFI'),$scope);
    $stmt=$pdo->prepare('INSERT IGNORE INTO '.table('rfq_supplier_invitations').' (rfq_id,supplier_id,invitation_number,status,sent_at) VALUES (?,?,?,"invited",NOW())');
    $stmt->execute([$rfqId,$supplierId,$number]);
    $invitationId=(int)$pdo->lastInsertId();
    if($invitationId<=0){
        $find=$pdo->prepare('SELECT id FROM '.table('rfq_supplier_invitations').' WHERE rfq_id=? AND supplier_id=? LIMIT 1');
        $find->execute([$rfqId,$supplierId]);
        $invitationId=(int)$find->fetchColumn();
    }
    $pdo->prepare('UPDATE '.table('rfqs').' SET status=CASE WHEN status="draft" THEN "open" ELSE status END WHERE id=?')->execute([$rfqId]);
    logActivity($pdo,'RFQ','rfq_supplier_invited','Supplier #'.$supplierId.' invited to RFQ #'.$rfqId.'.','rfq',$rfqId);
    return $invitationId;
}

function convertAwardedRfqQuoteToPurchaseOrder(PDO $pdo, int $quoteId): int
{
    $quoteStmt=$pdo->prepare('SELECT q.*,r.rfq_number,r.company_id,r.branch_id,r.warehouse_id,r.source_requisition_id FROM '.table('rfq_supplier_quotes').' q LEFT JOIN '.table('rfqs').' r ON r.id=q.rfq_id WHERE q.id=? LIMIT 1 FOR UPDATE');
    $quoteStmt->execute([$quoteId]);
    $quote=$quoteStmt->fetch();
    if(!$quote){throw new RuntimeException('Supplier quote not found.');}
    if(!in_array((string)$quote['status'],['awarded','submitted'],true)){throw new RuntimeException('Only submitted or awarded quotes can be converted to PO.');}
    $supplierStmt=$pdo->prepare('SELECT company_name FROM '.table('suppliers').' WHERE id=? LIMIT 1');
    $supplierStmt->execute([(int)$quote['supplier_id']]);
    $supplierName=(string)$supplierStmt->fetchColumn();
    $scope=['company_id'=>(int)($quote['company_id']??0),'branch_id'=>(int)($quote['branch_id']??0),'warehouse_id'=>(int)($quote['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $number=nextScopedDocumentNumber($pdo,'purchase_order','PO',$scope);
    $poStmt=$pdo->prepare('INSERT INTO '.table('purchase_orders').' (company_id,branch_id,warehouse_id,source_requisition_id,po_number,supplier_id,supplier_name,order_date,expected_date,subtotal,tax,shipping,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,"draft",?)');
    $poStmt->execute([(int)($quote['company_id']??0)?:null,(int)($quote['branch_id']??0)?:null,(int)($quote['warehouse_id']??0)?:null,(int)($quote['source_requisition_id']??0)?:null,$number,(int)$quote['supplier_id'],$supplierName,date('Y-m-d'),null,(float)$quote['subtotal'],(float)$quote['tax'],(float)$quote['shipping'],(float)$quote['total_amount'],'Converted from RFQ '.$quote['rfq_number'].' supplier quote '.$quote['response_number'].'.']);
    $poId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('rfq_supplier_quote_items').' WHERE rfq_supplier_quote_id=? ORDER BY id ASC');
    $items->execute([$quoteId]);
    $itemInsert=$pdo->prepare('INSERT INTO '.table('purchase_order_items').' (purchase_order_id,product_id,description,quantity,received_quantity,unit_cost,tax_rate,line_total) VALUES (?,?,?,?,0,?,?,?)');
    foreach($items->fetchAll() as $item){
        $itemInsert->execute([$poId,(int)($item['product_id']??0)?:null,(string)($item['description']??''),(float)($item['quantity']??0),(float)($item['unit_price']??0),(float)($item['tax_rate']??0),(float)($item['line_total']??0)]);
    }
    $pdo->prepare('UPDATE '.table('rfq_supplier_quotes').' SET status="awarded" WHERE id=?')->execute([$quoteId]);
    $pdo->prepare('UPDATE '.table('rfqs').' SET status=?,awarded_supplier_id=?,awarded_quote_id=?,converted_po_id=? WHERE id=?')->execute([setting('rfq_auto_close_on_award','1')==='1'?'awarded':'open',(int)$quote['supplier_id'],$quoteId,$poId,(int)$quote['rfq_id']]);
    $pdo->prepare('UPDATE '.table('rfq_supplier_invitations').' SET status=CASE WHEN supplier_id=? THEN "awarded" ELSE status END WHERE rfq_id=?')->execute([(int)$quote['supplier_id'],(int)$quote['rfq_id']]);
    logActivity($pdo,'RFQ','rfq_quote_converted_po','RFQ quote '.$quote['response_number'].' converted to PO '.$number.'.','purchase_order',$poId);
    return $poId;
}


function workflowBuilderTriggerOptions(): array
{
    return [
        'manual'=>'Manual Run',
        'invoice_overdue'=>'Invoice overdue',
        'low_stock'=>'Product low stock',
        'quote_accepted'=>'Quotation accepted',
        'approval_pending'=>'Approval pending too long',
        'lead_hot'=>'Hot lead created',
        'service_job_delayed'=>'Service job delayed',
    ];
}

function workflowBuilderActionOptions(): array
{
    return [
        'create_notification'=>'Create notification',
        'create_crm_task'=>'Create CRM follow-up task',
        'create_collection_task'=>'Create collection task',
        'create_purchase_requisition'=>'Create purchase requisition',
        'create_ai_action'=>'Create AI action suggestion',
        'update_status'=>'Update document status',
        'create_approval_escalation'=>'Create approval escalation',
    ];
}

function workflowMatchCondition(array $row, array $condition): bool
{
    $field=(string)($condition['field_key']??'');
    if($field===''){return true;}
    $value=(string)($row[$field]??'');
    $compare=(string)($condition['compare_value']??'');
    $op=(string)($condition['operator_key']??'equals');
    if($op==='equals'){return $value===$compare;}
    if($op==='not_equals'){return $value!==$compare;}
    if($op==='contains'){return str_contains(mb_strtolower($value),mb_strtolower($compare));}
    if($op==='greater_than'){return (float)$value>(float)$compare;}
    if($op==='less_than'){return (float)$value<(float)$compare;}
    if($op==='is_not_empty'){return trim($value)!=='';}
    return true;
}

function workflowBuilderCandidateRows(PDO $pdo, array $rule, array $trigger): array
{
    $event=(string)($trigger['event_key'] ?: ($trigger['trigger_type'] ?? 'manual'));
    if($event==='invoice_overdue'){
        $days=max(1,(int)($trigger['days_offset'] ?: setting('workflow_overdue_invoice_days','7')));
        return safeAiRows($pdo,'SELECT id,invoice_number reference_number,customer_name label,balance_due amount,status,due_date,DATEDIFF(CURDATE(),COALESCE(due_date,created_at)) age_days FROM '.table('invoices').' WHERE balance_due>0 AND status NOT IN ("paid","cancelled") AND DATEDIFF(CURDATE(),COALESCE(due_date,created_at))>='.$days.' ORDER BY balance_due DESC LIMIT 100');
    }
    if($event==='low_stock'){
        return safeAiRows($pdo,'SELECT id,sku reference_number,name label,stock amount,status,stock age_days FROM '.table('products').' WHERE active=1 AND stock<=3 ORDER BY stock ASC LIMIT 100');
    }
    if($event==='quote_accepted'){
        return safeAiRows($pdo,'SELECT id,quotation_number reference_number,customer_name label,total amount,status,DATEDIFF(CURDATE(),created_at) age_days FROM '.table('quotations').' WHERE status IN ("accepted","approved") AND converted_invoice_id IS NULL ORDER BY created_at ASC LIMIT 100');
    }
    if($event==='approval_pending'){
        $days=max(1,(int)($trigger['days_offset'] ?: setting('workflow_approval_escalation_days','2')));
        return safeAiRows($pdo,'SELECT id,request_number reference_number,document_number label,request_amount amount,status,DATEDIFF(CURDATE(),submitted_at) age_days,current_step FROM '.table('approval_requests').' WHERE status="pending" AND DATEDIFF(CURDATE(),submitted_at)>='.$days.' ORDER BY submitted_at ASC LIMIT 100');
    }
    if($event==='lead_hot'){
        return safeAiRows($pdo,'SELECT id,lead_name reference_number,company_name label,lead_score amount,status,DATEDIFF(CURDATE(),created_at) age_days FROM '.table('crm_leads').' WHERE lead_score>=70 AND status NOT IN ("won","lost") ORDER BY lead_score DESC LIMIT 100');
    }
    if($event==='service_job_delayed'){
        return safeAiRows($pdo,'SELECT id,job_number reference_number,customer_name label,0 amount,status,DATEDIFF(CURDATE(),created_at) age_days FROM '.table('job_cards').' WHERE status NOT IN ("completed","cancelled","closed") AND DATEDIFF(CURDATE(),created_at)>=3 ORDER BY created_at ASC LIMIT 100');
    }
    return [];
}

function workflowLogAction(PDO $pdo, int $ruleId, ?int $actionId, string $refType, ?int $refId, string $actionType, string $status, string $message): int
{
    $number=nextScopedDocumentNumber($pdo,'workflow_builder_log',setting('workflow_builder_log_prefix','WFLOG'),operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('workflow_builder_action_logs').' (log_number,workflow_builder_rule_id,workflow_builder_action_id,reference_type,reference_id,action_type,status,message) VALUES (?,?,?,?,?,?,?,?)')->execute([$number,$ruleId,$actionId,$refType,$refId,$actionType,$status,$message]);
    return (int)$pdo->lastInsertId();
}

function executeWorkflowBuilderAction(PDO $pdo, array $rule, array $action, array $row, int $runId, int $stepNo): array
{
    $type=(string)$action['action_type'];
    $refType=(string)($rule['module'] ?: 'workflow');
    $refId=(int)($row['id']??0);
    $label=(string)($row['reference_number']??($row['label']??'Record'));
    $message='No action executed.';

    if($type==='create_notification'){
        $title=$action['notification_title'] ?: ('Workflow: '.$rule['rule_name']);
        $body=$action['notification_message'] ?: ('Workflow matched '.$label.'.');
        $stmt=$pdo->prepare('INSERT INTO '.table('notifications').' (role_slug,title,message,severity,link_url) VALUES ("admin",?,?, "info", ?)');
        $stmt->execute([$title,$body,(string)($action['action_config_json']?:'/admin/erp/workflow-run-history-2.php')]);
        $message='Notification created for '.$label.'.';
    } elseif($type==='create_crm_task' && function_exists('createCrmFollowupTask')){
        createCrmFollowupTask($pdo,[
            'task_title'=>$action['action_label'] ?: ('Workflow follow-up '.$label),
            'task_type'=>'call',
            'priority'=>'high',
            'due_date'=>date('Y-m-d',strtotime('+'.max(1,(int)$action['task_due_days']).' days')),
            'notes'=>'Created by workflow '.$rule['rule_number'].' for '.$label,
            'lead_id'=>$refType==='Sales'?$refId:null,
        ]);
        $message='CRM follow-up task created for '.$label.'.';
    } elseif($type==='create_collection_task'){
        if(function_exists('generateCollectionTasks')){
            $pdo->prepare('INSERT INTO '.table('collection_tasks').' (task_number,invoice_id,customer_id,customer_name,invoice_number,balance_due,due_date,priority,status,notes) VALUES (?,?,?,?,?,?,?,?, "open", ?)')->execute([
                nextScopedDocumentNumber($pdo,'collection_task',setting('collection_task_prefix','COLL'),operationalScope($pdo)),
                $refId,null,(string)($row['label']??''),(string)($row['reference_number']??''),(float)($row['amount']??0),(string)($row['due_date']??null),'high','Workflow collection action from '.$rule['rule_number']
            ]);
            $message='Collection task created for '.$label.'.';
        }else{$message='Collection task function/table unavailable.';}
    } elseif($type==='create_purchase_requisition'){
        $scope=operationalScope($pdo);
        $number=nextScopedDocumentNumber($pdo,'purchase_requisition',setting('purchase_requisition_prefix','PR'),$scope);
        $pdo->prepare('INSERT INTO '.table('purchase_requisitions').' (company_id,branch_id,warehouse_id,requisition_number,requester_id,required_date,subtotal,tax,total,status,notes) VALUES (?,?,?,?,?,DATE_ADD(CURDATE(), INTERVAL 7 DAY),0,0,0,"draft",?)')->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,(int)($scope['warehouse_id']??0)?:null,$number,(int)(currentUser()['id']??0)?:null,'Workflow created from '.$label]);
        $reqId=(int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO '.table('purchase_requisition_items').' (purchase_requisition_id,product_id,description,quantity,estimated_unit_cost,tax_rate,line_total) VALUES (?,?,?,?,0,0,0)')->execute([$reqId,$refId,(string)($row['label']??$label),max(1,10-(float)($row['amount']??0))]);
        $message='Purchase requisition '.$number.' created for '.$label.'.';
    } elseif($type==='create_ai_action' && function_exists('createAiActionSuggestion')){
        createAiActionSuggestion($pdo,(string)($rule['module']?:'Workflow'),$action['action_label'] ?: ('Workflow action '.$label),'Workflow '.$rule['rule_number'].' matched '.$label.'.','Open workflow','/admin/erp/workflow-run-history-2.php','high');
        $message='AI action suggestion created for '.$label.'.';
    } elseif($type==='update_status'){
        $target=(string)$action['target_status'];
        $event=(string)($row['event_key']??'');
        $message='Status update skipped.';
        if($target!=='' && ($rule['module']==='Quotation' || str_contains(mb_strtolower($rule['rule_name']),'quote'))){
            $pdo->prepare('UPDATE '.table('quotations').' SET status=? WHERE id=?')->execute([$target,$refId]);
            $message='Quotation status updated to '.$target.' for '.$label.'.';
        }
    } elseif($type==='create_approval_escalation'){
        $number=nextScopedDocumentNumber($pdo,'workflow_escalation',setting('workflow_escalation_prefix','WFESC'),operationalScope($pdo));
        $pdo->prepare('INSERT INTO '.table('workflow_approval_escalations').' (escalation_number,approval_request_id,document_type,document_number,current_step,days_pending,escalated_to_role,status,message) VALUES (?,?,?,?,?,?,?,"open",?)')->execute([$number,$refId,'approval',(string)($row['reference_number']??''),(int)($row['current_step']??0),(int)($row['age_days']??0),'manager','Approval pending too long: '.$label]);
        $message='Approval escalation '.$number.' created for '.$label.'.';
    }

    $pdo->prepare('INSERT INTO '.table('workflow_builder_run_steps').' (workflow_automation_run_id,workflow_builder_rule_id,workflow_builder_action_id,step_number,step_type,step_status,reference_type,reference_id,message) VALUES (?,?,?,?,?,"success",?,?,?)')->execute([$runId,(int)$rule['id'],(int)$action['id'],$stepNo,$type,$refType,$refId,$message]);
    workflowLogAction($pdo,(int)$rule['id'],(int)$action['id'],$refType,$refId,$type,'success',$message);
    return ['status'=>'success','message'=>$message];
}

function runWorkflowBuilderRule(PDO $pdo, int $ruleId): array
{
    if(setting('workflow_builder_enabled','1')!=='1'){throw new RuntimeException('Workflow Builder 2.0 is disabled in settings.');}
    $stmt=$pdo->prepare('SELECT * FROM '.table('workflow_builder_rules').' WHERE id=? LIMIT 1');
    $stmt->execute([$ruleId]);
    $rule=$stmt->fetch();
    if(!$rule){throw new RuntimeException('Workflow builder rule not found.');}
    $trigger=$pdo->prepare('SELECT * FROM '.table('workflow_builder_triggers').' WHERE workflow_builder_rule_id=? ORDER BY id ASC LIMIT 1');
    $trigger->execute([$ruleId]);
    $trigger=$trigger->fetch() ?: ['trigger_type'=>'manual','event_key'=>'manual'];
    $conditions=$pdo->prepare('SELECT * FROM '.table('workflow_builder_conditions').' WHERE workflow_builder_rule_id=? ORDER BY sort_order,id');
    $conditions->execute([$ruleId]);$conditions=$conditions->fetchAll();
    $actions=$pdo->prepare('SELECT * FROM '.table('workflow_builder_actions').' WHERE workflow_builder_rule_id=? AND status="active" ORDER BY sort_order,id');
    $actions->execute([$ruleId]);$actions=$actions->fetchAll();

    $runNumber=nextScopedDocumentNumber($pdo,'workflow_run','WFR',operationalScope($pdo));
    $pdo->prepare('INSERT INTO '.table('workflow_automation_runs').' (workflow_automation_rule_id,run_number,status,started_at,summary) VALUES (NULL,?,"running",NOW(),?)')->execute([$runNumber,'Workflow Builder 2.0 rule '.$rule['rule_number'].' started.']);
    $runId=(int)$pdo->lastInsertId();
    $rows=workflowBuilderCandidateRows($pdo,$rule,$trigger);
    $checked=count($rows);$actionsCreated=0;$matched=0;$stepNo=1;
    try{
        foreach($rows as $row){
            $ok=true;
            foreach($conditions as $c){if(!workflowMatchCondition($row,$c)){$ok=false;break;}}
            if(!$ok){continue;}
            $matched++;
            foreach($actions as $action){
                executeWorkflowBuilderAction($pdo,$rule,$action,$row,$runId,$stepNo++);
                $actionsCreated++;
            }
        }
        $summary='Workflow Builder 2.0: checked '.$checked.', matched '.$matched.', actions '.$actionsCreated.'.';
        $pdo->prepare('UPDATE '.table('workflow_automation_runs').' SET status="success",records_checked=?,actions_created=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$checked,$actionsCreated,$summary,$runId]);
        $pdo->prepare('UPDATE '.table('workflow_builder_rules').' SET last_run_at=NOW() WHERE id=?')->execute([$ruleId]);
        return ['status'=>'success','checked'=>$checked,'matched'=>$matched,'actions'=>$actionsCreated,'summary'=>$summary,'run_number'=>$runNumber];
    }catch(Throwable $e){
        $pdo->prepare('UPDATE '.table('workflow_automation_runs').' SET status="failed",records_checked=?,actions_created=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$checked,$actionsCreated,$e->getMessage(),$runId]);
        throw $e;
    }
}

function installWorkflowBuilderTemplates(PDO $pdo): int
{
    $templates=[
        ['Overdue Invoice Collection','Finance','invoice_overdue','create_collection_task','Create collection task','Invoice overdue by configured days.'],
        ['Low Stock Purchase Requisition','Inventory','low_stock','create_purchase_requisition','Create purchase requisition','Product stock reaches critical level.'],
        ['Hot Lead Follow-up','Sales','lead_hot','create_crm_task','Create CRM task','Lead score reaches hot lead threshold.'],
        ['Approval Escalation','Approval','approval_pending','create_approval_escalation','Create approval escalation','Approval request pending too long.'],
        ['Delayed Service Job Notification','Service','service_job_delayed','create_notification','Notify admin','Service job remains open too long.'],
    ];
    $created=0;$user=currentUser();
    foreach($templates as $t){
        $exists=$pdo->prepare('SELECT id FROM '.table('workflow_builder_rules').' WHERE rule_name=? LIMIT 1');
        $exists->execute([$t[0]]);
        if($exists->fetchColumn()){continue;}
        $number=nextScopedDocumentNumber($pdo,'workflow_builder_rule',setting('workflow_builder_rule_prefix','WFB'),operationalScope($pdo));
        $pdo->prepare('INSERT INTO '.table('workflow_builder_rules').' (rule_number,rule_name,module,description,priority,status,run_mode,created_by) VALUES (?,?,?,?,50,"active","manual",?)')->execute([$number,$t[0],$t[1],$t[5],(int)($user['id']??0)?:null]);
        $ruleId=(int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO '.table('workflow_builder_triggers').' (workflow_builder_rule_id,trigger_type,event_key,days_offset,config_json) VALUES (?,?,?,?,?)')->execute([$ruleId,'event',$t[2],$t[2]==='invoice_overdue'?(int)setting('workflow_overdue_invoice_days','7'):($t[2]==='approval_pending'?(int)setting('workflow_approval_escalation_days','2'):0),'{}']);
        $pdo->prepare('INSERT INTO '.table('workflow_builder_actions').' (workflow_builder_rule_id,action_type,action_label,target_module,task_due_days,action_config_json,sort_order) VALUES (?,?,?,?,?,?,1)')->execute([$ruleId,$t[3],$t[4],$t[1],(int)setting('workflow_default_task_due_days','2'),'{}']);
        $created++;
    }
    return $created;
}


function runWorkflowAutomationRule(PDO $pdo, int $ruleId): array
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('workflow_automation_rules').' WHERE id=? LIMIT 1');
    $stmt->execute([$ruleId]);
    $rule=$stmt->fetch();
    if(!$rule){throw new RuntimeException('Workflow automation rule not found.');}
    $scope=operationalScope($pdo);
    $runNumber=nextScopedDocumentNumber($pdo,'workflow_run','WFR',$scope);
    $runStmt=$pdo->prepare('INSERT INTO '.table('workflow_automation_runs').' (workflow_automation_rule_id,run_number,status,started_at) VALUES (?,?,"running",NOW())');
    $runStmt->execute([$ruleId,$runNumber]);
    $runId=(int)$pdo->lastInsertId();
    $checked=0;$created=0;$summary='No action matched.';
    try{
        if(($rule['trigger_event']??'')==='low_stock_rfq'){
            $products=$pdo->query('SELECT id,name,sku,stock,cost_price,average_cost FROM '.table('products').' WHERE active=1 AND stock<=3 ORDER BY stock ASC LIMIT 50')->fetchAll();
            $checked=count($products);
            if($products){
                $number=nextScopedDocumentNumber($pdo,'rfq',setting('rfq_prefix','RFQ'),$scope);
                $user=currentUser();
                $pdo->prepare('INSERT INTO '.table('rfqs').' (company_id,branch_id,warehouse_id,rfq_number,title,description,request_date,due_date,status,created_by,notes) VALUES (?,?,?,?,?,?,DATE(NOW()),DATE_ADD(DATE(NOW()), INTERVAL 7 DAY),"draft",?,?)')->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,(int)($scope['warehouse_id']??0)?:null,$number,'Automated low-stock RFQ','Created automatically from low-stock products.',(int)($user['id']??0)?:null,'Workflow automation rule '.$rule['rule_code']]);
                $rfqId=(int)$pdo->lastInsertId();
                $itemStmt=$pdo->prepare('INSERT INTO '.table('rfq_items').' (rfq_id,product_id,description,quantity,target_unit_cost,required_date,notes) VALUES (?,?,?,?,?,DATE_ADD(DATE(NOW()), INTERVAL 7 DAY),?)');
                foreach($products as $product){
                    $target=max((float)($product['average_cost']??0),(float)($product['cost_price']??0));
                    $itemStmt->execute([$rfqId,(int)$product['id'],(($product['sku']?:'').' '.$product['name']),max(1,10-(float)$product['stock']),$target,'Auto RFQ from low stock.']);
                }
                $created=1;$summary='Created RFQ '.$number.' with '.count($products).' low-stock items.';
            }
        } elseif(($rule['trigger_event']??'')==='approved_requisition_rfq'){
            $reqs=$pdo->query('SELECT id FROM '.table('purchase_requisitions').' WHERE status="approved" AND converted_po_id IS NULL ORDER BY created_at ASC LIMIT 20')->fetchAll();
            $checked=count($reqs);
            foreach($reqs as $req){createRfqFromRequisition($pdo,(int)$req['id']);$created++;}
            $summary='Created '.$created.' RFQ records from approved requisitions.';
        }
        $pdo->prepare('UPDATE '.table('workflow_automation_runs').' SET status="success",records_checked=?,actions_created=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$checked,$created,$summary,$runId]);
        $pdo->prepare('UPDATE '.table('workflow_automation_rules').' SET last_run_at=NOW() WHERE id=?')->execute([$ruleId]);
        return ['status'=>'success','checked'=>$checked,'created'=>$created,'summary'=>$summary,'run_number'=>$runNumber];
    }catch(Throwable $e){
        $pdo->prepare('UPDATE '.table('workflow_automation_runs').' SET status="failed",records_checked=?,actions_created=?,summary=?,finished_at=NOW() WHERE id=?')->execute([$checked,$created,$e->getMessage(),$runId]);
        throw $e;
    }
}


function convertApprovedRequisitionToPurchaseOrder(PDO $pdo, int $requisitionId, ?int $supplierId = null, string $supplierName = ''): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('purchase_requisitions').' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$requisitionId]);$req=$stmt->fetch();
    if(!$req){throw new RuntimeException('Purchase requisition not found.');}
    if(($req['status']??'')!=='approved'){throw new RuntimeException('Only approved requisitions can be converted to purchase orders.');}
    if(!empty($req['converted_po_id'])){return (int)$req['converted_po_id'];}
    $scope=['company_id'=>(int)($req['company_id']??0),'branch_id'=>(int)($req['branch_id']??0),'warehouse_id'=>(int)($req['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $number=nextScopedDocumentNumber($pdo,'purchase_order','PO',$scope);
    $poStmt=$pdo->prepare('INSERT INTO '.table('purchase_orders').' (company_id,branch_id,warehouse_id,source_requisition_id,po_number,supplier_id,supplier_name,order_date,expected_date,subtotal,tax,shipping,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,"draft",?)');
    $poStmt->execute([(int)($req['company_id']??0)?:null,(int)($req['branch_id']??0)?:null,(int)($req['warehouse_id']??0)?:null,$requisitionId,$number,$supplierId,$supplierName,date('Y-m-d'),$req['required_date']?:null,(float)$req['subtotal'],(float)$req['tax'],0,(float)$req['total'],'Converted from requisition '.$req['requisition_number'].'.']);
    $poId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('purchase_requisition_items').' WHERE purchase_requisition_id=? ORDER BY id ASC');$items->execute([$requisitionId]);
    $itemInsert=$pdo->prepare('INSERT INTO '.table('purchase_order_items').' (purchase_order_id,product_id,description,quantity,received_quantity,unit_cost,tax_rate,line_total) VALUES (?,?,?,?,0,?,?,?)');
    foreach($items->fetchAll() as $item){
        $itemInsert->execute([$poId,(int)($item['product_id']??0)?:null,(string)($item['description']??''),(float)$item['quantity'],(float)$item['estimated_unit_cost'],(float)$item['tax_rate'],(float)$item['line_total']]);
    }
    $pdo->prepare('UPDATE '.table('purchase_requisitions').' SET status="converted",converted_po_id=? WHERE id=?')->execute([$poId,$requisitionId]);
    logActivity($pdo,'Procurement','requisition_converted','Purchase requisition '.$req['requisition_number'].' converted to PO '.$number.'.','purchase_requisition',$requisitionId);
    return $poId;
}

function createInvoiceFromSalesOrder(PDO $pdo, int $salesOrderId): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('sales_orders').' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$salesOrderId]);$order=$stmt->fetch();
    if(!$order){throw new RuntimeException('Sales order not found.');}
    if(!in_array((string)($order['status']??''),['approved','fulfilled'],true)){throw new RuntimeException('Only approved sales orders can be converted to invoices.');}
    if(!empty($order['converted_invoice_id'])){return (int)$order['converted_invoice_id'];}
    $scope=['company_id'=>(int)($order['company_id']??0),'branch_id'=>(int)($order['branch_id']??0),'warehouse_id'=>(int)($order['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $invoiceNumber=nextScopedDocumentNumber($pdo,'invoice',setting('invoice_prefix','INV'),$scope);
    $insert=$pdo->prepare('INSERT INTO '.table('invoices').' (company_id,branch_id,warehouse_id,invoice_number,customer_id,customer_name,customer_email,customer_type,billing_address,subtotal,discount,tax,shipping,total,amount_paid,credit_amount,balance_due,status,sales_channel,source_order_id,source_sales_order_id,due_date,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,0,?,"draft","sales_order",NULL,?,?,?)');
    $insert->execute([(int)($order['company_id']??0)?:null,(int)($order['branch_id']??0)?:null,(int)($order['warehouse_id']??0)?:null,$invoiceNumber,(int)($order['customer_id']??0)?:null,$order['customer_name'],$order['customer_email'],$order['customer_type'],$order['billing_address'],(float)$order['subtotal'],(float)$order['discount'],(float)$order['tax'],(float)$order['shipping'],(float)$order['total'],(float)$order['total'],$salesOrderId,$order['due_date']?:null,'Converted from sales order '.$order['sales_order_number'].'.']);
    $invoiceId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('sales_order_items').' WHERE sales_order_id=? ORDER BY id ASC');$items->execute([$salesOrderId]);
    $itemInsert=$pdo->prepare('INSERT INTO '.table('invoice_items').' (invoice_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
    foreach($items->fetchAll() as $item){
        $itemInsert->execute([$invoiceId,$item['item_type'],(int)($item['product_id']??0)?:null,$item['description'],(float)$item['quantity'],(float)$item['unit_price'],(float)$item['tax_rate'],(float)$item['line_total']]);
    }
    $pdo->prepare('UPDATE '.table('sales_orders').' SET converted_invoice_id=?,status="converted" WHERE id=?')->execute([$invoiceId,$salesOrderId]);
    logActivity($pdo,'Sales','sales_order_converted_invoice','Sales order '.$order['sales_order_number'].' converted to invoice '.$invoiceNumber.'.','sales_order',$salesOrderId);
    return $invoiceId;
}

function createDeliveryNoteFromSalesOrder(PDO $pdo, int $salesOrderId): int
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('sales_orders').' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$salesOrderId]);$order=$stmt->fetch();
    if(!$order){throw new RuntimeException('Sales order not found.');}
    if(!in_array((string)($order['status']??''),['approved','converted','fulfilled'],true)){throw new RuntimeException('Only approved sales orders can create delivery notes.');}
    if(!empty($order['delivery_note_id'])){return (int)$order['delivery_note_id'];}
    $scope=['company_id'=>(int)($order['company_id']??0),'branch_id'=>(int)($order['branch_id']??0),'warehouse_id'=>(int)($order['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $number=nextScopedDocumentNumber($pdo,'delivery_note','DN',$scope);
    $insert=$pdo->prepare('INSERT INTO '.table('delivery_notes').' (company_id,branch_id,warehouse_id,delivery_number,sales_order_id,invoice_id,customer_id,customer_name,shipping_address,delivery_date,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,"draft",?)');
    $insert->execute([(int)($order['company_id']??0)?:null,(int)($order['branch_id']??0)?:null,(int)($order['warehouse_id']??0)?:null,$number,$salesOrderId,(int)($order['converted_invoice_id']??0)?:null,(int)($order['customer_id']??0)?:null,$order['customer_name'],$order['shipping_address'],date('Y-m-d'),'Created from sales order '.$order['sales_order_number'].'.']);
    $deliveryId=(int)$pdo->lastInsertId();
    $items=$pdo->prepare('SELECT * FROM '.table('sales_order_items').' WHERE sales_order_id=? ORDER BY id ASC');$items->execute([$salesOrderId]);
    $itemInsert=$pdo->prepare('INSERT INTO '.table('delivery_note_items').' (delivery_note_id,sales_order_item_id,product_id,description,quantity_delivered,notes) VALUES (?,?,?,?,?,?)');
    foreach($items->fetchAll() as $item){
        $itemInsert->execute([$deliveryId,(int)$item['id'],(int)($item['product_id']??0)?:null,$item['description'],(float)$item['quantity'],'Planned from sales order.']);
    }
    $pdo->prepare('UPDATE '.table('sales_orders').' SET delivery_note_id=? WHERE id=?')->execute([$deliveryId,$salesOrderId]);
    logActivity($pdo,'Sales','delivery_note_created','Delivery note '.$number.' created from sales order '.$order['sales_order_number'].'.','delivery_note',$deliveryId);
    return $deliveryId;
}

function jobCardRecalculate(PDO $pdo, int $jobCardId): void
{
    if($jobCardId<=0){return;}
    $laborStmt=$pdo->prepare('SELECT COALESCE(SUM(line_total),0) FROM '.table('job_card_labor').' WHERE job_card_id=?');
    $laborStmt->execute([$jobCardId]);$labor=(float)$laborStmt->fetchColumn();
    $partsStmt=$pdo->prepare('SELECT COALESCE(SUM(line_total),0),COALESCE(SUM(quantity_used*unit_cost),0) FROM '.table('job_card_parts').' WHERE job_card_id=?');
    $partsStmt->execute([$jobCardId]);[$parts,$partCost]=$partsStmt->fetch(PDO::FETCH_NUM);
    $timeStmt=$pdo->prepare('SELECT COALESCE(SUM(cost_amount),0) FROM '.table('technician_timesheets').' WHERE job_card_id=? AND status IN ("approved","posted","draft")');
    $timeStmt->execute([$jobCardId]);$laborCost=(float)$timeStmt->fetchColumn();
    $pdo->prepare('UPDATE '.table('job_cards').' SET labor_total=?,parts_total=?,estimated_total=?,actual_cost=? WHERE id=?')->execute([round($labor,2),round((float)$parts,2),round($labor+(float)$parts,2),round($laborCost+(float)$partCost,2),$jobCardId]);
}

function issueJobCardParts(PDO $pdo, int $jobCardId): void
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('job_cards').' WHERE id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$jobCardId]);$job=$stmt->fetch();
    if(!$job){throw new RuntimeException('Job card not found.');}
    if(!empty($job['parts_issued_at'])){return;}
    $scope=['company_id'=>(int)($job['company_id']??0),'branch_id'=>(int)($job['branch_id']??0),'warehouse_id'=>(int)($job['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $parts=$pdo->prepare('SELECT * FROM '.table('job_card_parts').' WHERE job_card_id=? AND product_id IS NOT NULL AND quantity_used>0');
    $parts->execute([$jobCardId]);
    foreach($parts->fetchAll() as $part){
        $productId=(int)($part['product_id']??0);$qty=(float)($part['quantity_used']??0);
        if($productId<=0||$qty<=0){continue;}
        if(warehouseAvailableQuantity($pdo,$productId,$scope)<$qty){throw new RuntimeException('Insufficient stock to issue job card part: '.$part['description']);}
        adjustWarehouseStock($pdo,$productId,-$qty,$scope,'job_card',$jobCardId,'Parts issued to job card '.$job['job_card_number'].'.');
        $pdo->prepare('UPDATE '.table('job_card_parts').' SET status="issued" WHERE id=?')->execute([(int)$part['id']]);
    }
    $pdo->prepare('UPDATE '.table('job_cards').' SET parts_issued_at=NOW() WHERE id=?')->execute([$jobCardId]);
    logActivity($pdo,'Service','job_card_parts_issued','Parts issued for job card '.$job['job_card_number'].'.','job_card',$jobCardId);
}

function createInvoiceFromJobCard(PDO $pdo, int $jobCardId): int
{
    $stmt=$pdo->prepare('SELECT jc.*,c.email customer_email,c.customer_type,c.billing_address FROM '.table('job_cards').' jc LEFT JOIN '.table('customers').' c ON c.id=jc.customer_id WHERE jc.id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$jobCardId]);$job=$stmt->fetch();
    if(!$job){throw new RuntimeException('Job card not found.');}
    if(!in_array((string)($job['status']??''),['completed','ready_to_invoice'],true)){throw new RuntimeException('Only completed job cards can be invoiced.');}
    if(!empty($job['invoice_id'])){return (int)$job['invoice_id'];}
    jobCardRecalculate($pdo,$jobCardId);
    $stmt->execute([$jobCardId]);$job=$stmt->fetch();
    $scope=['company_id'=>(int)($job['company_id']??0),'branch_id'=>(int)($job['branch_id']??0),'warehouse_id'=>(int)($job['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $invoiceNumber=nextScopedDocumentNumber($pdo,'invoice',setting('invoice_prefix','INV'),$scope);
    $subtotal=round((float)($job['estimated_total']??0),2);
    $taxRate=(float)setting('tax_rate','5');$tax=round($subtotal*$taxRate/100,2);$total=round($subtotal+$tax,2);
    $insert=$pdo->prepare('INSERT INTO '.table('invoices').' (company_id,branch_id,warehouse_id,invoice_number,customer_id,customer_name,customer_email,customer_type,billing_address,subtotal,discount,tax,shipping,total,amount_paid,credit_amount,balance_due,status,sales_channel,source_order_id,source_sales_order_id,due_date,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,0,?,"draft","job_card",NULL,NULL,?,?)');
    $insert->execute([(int)($job['company_id']??0)?:null,(int)($job['branch_id']??0)?:null,(int)($job['warehouse_id']??0)?:null,$invoiceNumber,(int)($job['customer_id']??0)?:null,$job['customer_name'],$job['customer_email']??'',$job['customer_type']??'b2c',$job['billing_address']??'',(float)$subtotal,0,(float)$tax,0,(float)$total,(float)$total,date('Y-m-d',strtotime('+7 days')),'Converted from job card '.$job['job_card_number'].'.']);
    $invoiceId=(int)$pdo->lastInsertId();
    $itemInsert=$pdo->prepare('INSERT INTO '.table('invoice_items').' (invoice_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
    $labor=$pdo->prepare('SELECT * FROM '.table('job_card_labor').' WHERE job_card_id=? ORDER BY id ASC');$labor->execute([$jobCardId]);
    foreach($labor->fetchAll() as $line){if((float)$line['line_total']>0){$itemInsert->execute([$invoiceId,'service',null,$line['task_name']?:'Labor',(float)($line['actual_hours']?:$line['estimated_hours']?:1),(float)$line['hourly_rate'],$taxRate,(float)$line['line_total']]);}}
    $parts=$pdo->prepare('SELECT * FROM '.table('job_card_parts').' WHERE job_card_id=? ORDER BY id ASC');$parts->execute([$jobCardId]);
    foreach($parts->fetchAll() as $line){if((float)$line['line_total']>0){$itemInsert->execute([$invoiceId,'product',(int)($line['product_id']??0)?:null,$line['description'],(float)($line['quantity_used']?:$line['quantity_planned']?:1),(float)$line['unit_price'],$taxRate,(float)$line['line_total']]);}}
    $pdo->prepare('UPDATE '.table('job_cards').' SET invoice_id=?,status="invoiced",invoiced_at=NOW() WHERE id=?')->execute([$invoiceId,$jobCardId]);
    logActivity($pdo,'Service','job_card_invoiced','Job card '.$job['job_card_number'].' converted to invoice '.$invoiceNumber.'.','job_card',$jobCardId);
    return $invoiceId;
}

function projectRecalculate(PDO $pdo, int $projectId): void
{
    if($projectId<=0){return;}
    $costStmt=$pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM '.table('project_cost_entries').' WHERE project_id=?');
    $costStmt->execute([$projectId]);$cost=(float)$costStmt->fetchColumn();
    $stmt=$pdo->prepare('SELECT revenue_amount FROM '.table('projects').' WHERE id=? LIMIT 1');$stmt->execute([$projectId]);$revenue=(float)$stmt->fetchColumn();
    $pdo->prepare('UPDATE '.table('projects').' SET cost_amount=?,margin_amount=? WHERE id=?')->execute([round($cost,2),round($revenue-$cost,2),$projectId]);
}

function updateBudgetActuals(PDO $pdo, int $costCenterId, int $year, int $month = 0): void
{
    if($costCenterId<=0||$year<=0){return;}
    $params=[$costCenterId];
    $dateWhere='';
    if($month>0){$dateWhere=' AND YEAR(entry_date)=? AND MONTH(entry_date)=?';$params[]=$year;$params[]=$month;}else{$dateWhere=' AND YEAR(entry_date)=?';$params[]=$year;}
    $stmt=$pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM '.table('project_cost_entries').' WHERE cost_center_id=?'.$dateWhere);
    $stmt->execute($params);$actual=(float)$stmt->fetchColumn();
    $budget=$pdo->prepare('SELECT id,budget_amount,committed_amount FROM '.table('budget_periods').' WHERE cost_center_id=? AND fiscal_year=? AND period_month=? LIMIT 1');
    $budget->execute([$costCenterId,$year,$month]);$row=$budget->fetch();
    if($row){$pdo->prepare('UPDATE '.table('budget_periods').' SET actual_amount=?,variance_amount=? WHERE id=?')->execute([round($actual,2),round((float)$row['budget_amount']-(float)$row['committed_amount']-$actual,2),(int)$row['id']]);}
}

function costCenterBudgetSnapshot(PDO $pdo, int $costCenterId, int $year, int $month=0): array
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('budget_periods').' WHERE cost_center_id=? AND fiscal_year=? AND period_month=? LIMIT 1');
    $stmt->execute([$costCenterId,$year,$month]);$budget=$stmt->fetch();
    if(!$budget){return ['budget'=>0.0,'committed'=>0.0,'actual'=>0.0,'variance'=>0.0,'used_percent'=>0.0,'status'=>'no_budget'];}
    $used=(float)$budget['committed_amount']+(float)$budget['actual_amount'];$budgetAmount=(float)$budget['budget_amount'];
    return ['budget'=>$budgetAmount,'committed'=>(float)$budget['committed_amount'],'actual'=>(float)$budget['actual_amount'],'variance'=>(float)$budget['variance_amount'],'used_percent'=>$budgetAmount>0?round($used/$budgetAmount*100,2):0.0,'status'=>(string)$budget['status']];
}

function approvalDocumentLabel(string $documentType): string
{
    return match($documentType){
        'purchase_order'=>'Purchase Order',
        'purchase_requisition'=>'Purchase Requisition',
        'stock_transfer'=>'Stock Transfer',
        'expense'=>'Expense',
        'invoice'=>'Invoice',
        'quotation'=>'Quotation',
        'sales_order'=>'Sales Order',
        'supplier_invoice'=>'Supplier Invoice',
        'return_rma'=>'Return / RMA',
        'warranty_claim'=>'Warranty Claim',
        'project'=>'Project / Budget',
        default=>ucwords(str_replace('_',' ',$documentType)),
    };
}

function approvalActionLabel(string $actionKey): string
{
    return match($actionKey){
        'approve'=>'Approve',
        'accept'=>'Accept',
        'credit_override'=>'Credit Override',
        'budget_override'=>'Budget Override',
        default=>ucwords(str_replace('_',' ',$actionKey)),
    };
}

function approvalDocumentUrl(string $documentType, int $documentId): string
{
    return match($documentType){
        'purchase_order'=>ADMIN_URL.'/erp/view-purchase-order.php?id='.$documentId,
        'purchase_requisition'=>ADMIN_URL.'/erp/view-purchase-requisition.php?id='.$documentId,
        'stock_transfer'=>ADMIN_URL.'/erp/view-stock-transfer.php?id='.$documentId,
        'expense'=>ADMIN_URL.'/erp/finance.php',
        'invoice'=>ADMIN_URL.'/erp/view-invoice.php?id='.$documentId,
        'quotation'=>ADMIN_URL.'/erp/view-quotation.php?id='.$documentId,
        'sales_order'=>ADMIN_URL.'/erp/view-sales-order.php?id='.$documentId,
        'supplier_invoice'=>ADMIN_URL.'/erp/view-supplier-invoice.php?id='.$documentId,
        'return_rma'=>ADMIN_URL.'/erp/view-return-rma.php?id='.$documentId,
        'warranty_claim'=>ADMIN_URL.'/erp/view-warranty-claim.php?id='.$documentId,
        'project'=>ADMIN_URL.'/erp/view-project.php?id='.$documentId,
        default=>ADMIN_URL.'/erp/approvals.php',
    };
}

function approvalDocumentData(PDO $pdo, string $documentType, int $documentId): ?array
{
    if($documentId<=0){return null;}
    if($documentType==='purchase_order'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,po_number document_number,total request_amount,0 request_discount,status FROM '.table('purchase_orders').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);
        $row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>0.0,'status'=>(string)($row['status']??''),
        ];
    }
    if($documentType==='stock_transfer'){
        $stmt=$pdo->prepare('SELECT * FROM '.table('stock_transfers').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);
        $row=$stmt->fetch();
        if(!$row){return null;}
        $scope=['company_id'=>(int)($row['from_company_id']??0),'branch_id'=>(int)($row['from_branch_id']??0),'warehouse_id'=>(int)($row['from_warehouse_id']??0),'location_id'=>(int)($row['from_location_id']??0)];
        $itemStmt=$pdo->prepare('SELECT product_id,quantity FROM '.table('stock_transfer_items').' WHERE stock_transfer_id=?');
        $itemStmt->execute([$documentId]);
        $amount=0.0;
        foreach($itemStmt->fetchAll() as $item){
            $amount+=round((float)($item['quantity']??0)*warehouseStockUnitCost($pdo,(int)($item['product_id']??0),$scope),2);
        }
        return [
            'company_id'=>(int)($row['from_company_id']??0),'branch_id'=>(int)($row['from_branch_id']??0),'warehouse_id'=>(int)($row['from_warehouse_id']??0),
            'document_number'=>(string)($row['transfer_number']??''),'request_amount'=>round($amount,2),'request_discount'=>0.0,'status'=>(string)($row['status']??''),
        ];
    }
    if($documentType==='expense'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,expense_number document_number,total request_amount,0 request_discount,approval_status status FROM '.table('expenses').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);
        $row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>0.0,'status'=>(string)($row['status']??''),
        ];
    }
    if($documentType==='invoice'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,invoice_number document_number,total request_amount,discount request_discount,status FROM '.table('invoices').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);
        $row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>(float)($row['request_discount']??0),'status'=>(string)($row['status']??''),
        ];
    }
    if($documentType==='quotation'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,quotation_number document_number,total request_amount,discount request_discount,status FROM '.table('quotations').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);
        $row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>(float)($row['request_discount']??0),'status'=>(string)($row['status']??''),
        ];
    }
    if($documentType==='purchase_requisition'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,requisition_number document_number,total request_amount,0 request_discount,status FROM '.table('purchase_requisitions').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);$row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>0.0,'status'=>(string)($row['status']??'')
        ];
    }
    if($documentType==='sales_order'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,sales_order_number document_number,total request_amount,status,customer_id FROM '.table('sales_orders').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);$row=$stmt->fetch();
        if(!$row){return null;}
        $credit=customerCreditSnapshot($pdo,(int)($row['customer_id']??0),(float)($row['request_amount']??0),(int)$row['id']);
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>(float)($credit['shortfall']??0),'status'=>(string)($row['status']??'')
        ];
    }
    if($documentType==='supplier_invoice'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,internal_number document_number,total request_amount,difference_amount request_discount,status FROM '.table('supplier_invoices').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);$row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>(float)($row['request_discount']??0),'status'=>(string)($row['status']??'')
        ];
    }
    if($documentType==='return_rma'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,rma_number document_number,total_value request_amount,0 request_discount,status FROM '.table('returns_rma').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);$row=$stmt->fetch();
        if(!$row){return null;}
        return [
            'company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),
            'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>0.0,'status'=>(string)($row['status']??'')
        ];
    }
    if($documentType==='warranty_claim'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,warehouse_id,claim_number document_number,claim_value request_amount,0 request_discount,status FROM '.table('warranty_claims').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);$row=$stmt->fetch();
        if(!$row){return null;}
        return ['company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>(int)($row['warehouse_id']??0),'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>0.0,'status'=>(string)($row['status']??'')];
    }
    if($documentType==='project'){
        $stmt=$pdo->prepare('SELECT id,company_id,branch_id,project_number document_number,budget_amount request_amount,0 request_discount,status FROM '.table('projects').' WHERE id=? LIMIT 1');
        $stmt->execute([$documentId]);$row=$stmt->fetch();
        if(!$row){return null;}
        return ['company_id'=>(int)($row['company_id']??0),'branch_id'=>(int)($row['branch_id']??0),'warehouse_id'=>0,'document_number'=>(string)($row['document_number']??''),'request_amount'=>(float)($row['request_amount']??0),'request_discount'=>0.0,'status'=>(string)($row['status']??'')];
    }
    return null;
}

function approvalRuleFor(PDO $pdo, string $documentType, string $actionKey, array $documentData): ?array
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('approval_rules').' WHERE active=1 AND document_type=? AND action_key=? ORDER BY (branch_id IS NOT NULL) DESC,(company_id IS NOT NULL) DESC,min_discount DESC,min_amount DESC,id ASC');
    $stmt->execute([$documentType,$actionKey]);
    $amount=(float)($documentData['request_amount']??0);
    $discount=(float)($documentData['request_discount']??0);
    $companyId=(int)($documentData['company_id']??0);
    $branchId=(int)($documentData['branch_id']??0);
    foreach($stmt->fetchAll() as $rule){
        if(!empty($rule['company_id']) && (int)$rule['company_id']!==$companyId){continue;}
        if(!empty($rule['branch_id']) && (int)$rule['branch_id']!==$branchId){continue;}
        if($amount < (float)($rule['min_amount']??0)){continue;}
        if($rule['max_amount']!==null && $rule['max_amount']!=='' && $amount > (float)$rule['max_amount']){continue;}
        if($discount < (float)($rule['min_discount']??0)){continue;}
        if($rule['max_discount']!==null && $rule['max_discount']!=='' && $discount > (float)$rule['max_discount']){continue;}
        return $rule;
    }
    return null;
}

function activeApprovalRequest(PDO $pdo, string $documentType, int $documentId, string $actionKey): ?array
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('approval_requests').' WHERE document_type=? AND document_id=? AND action_key=? AND status="pending" ORDER BY id DESC LIMIT 1');
    $stmt->execute([$documentType,$documentId,$actionKey]);
    return $stmt->fetch()?:null;
}

function latestApprovalRequest(PDO $pdo, string $documentType, int $documentId, string $actionKey): ?array
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('approval_requests').' WHERE document_type=? AND document_id=? AND action_key=? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$documentType,$documentId,$actionKey]);
    return $stmt->fetch()?:null;
}

function logApprovalAction(PDO $pdo, int $requestId, ?int $stepId, string $action, ?int $actorUserId, string $notes=''): void
{
    $stmt=$pdo->prepare('INSERT INTO '.table('approval_logs').' (approval_request_id,approval_request_step_id,actor_user_id,action,notes) VALUES (?,?,?,?,?)');
    $stmt->execute([$requestId,$stepId,$actorUserId?:null,$action,$notes]);
}

function createApprovalRequestForDocument(PDO $pdo, string $documentType, int $documentId, string $actionKey, string $notes=''): ?array
{
    $active=activeApprovalRequest($pdo,$documentType,$documentId,$actionKey);
    if($active){return $active;}
    $data=approvalDocumentData($pdo,$documentType,$documentId);
    if(!$data){throw new RuntimeException('Approval document data could not be loaded.');}
    $rule=approvalRuleFor($pdo,$documentType,$actionKey,$data);
    if(!$rule){return null;}
    $scope=[
        'company_id'=>(int)($data['company_id']??0),
        'branch_id'=>(int)($data['branch_id']??0),
        'warehouse_id'=>(int)($data['warehouse_id']??0),
        'location_id'=>(int)setting('default_location_id','0'),
    ];
    $requestNumber=nextScopedDocumentNumber($pdo,'approval_request','APR',$scope);
    $user=currentUser();$makerUserId=(int)($user['id']??0)?:null;
    $stmt=$pdo->prepare('INSERT INTO '.table('approval_requests').' (request_number,approval_rule_id,company_id,branch_id,document_type,document_id,document_number,action_key,request_amount,request_discount,maker_user_id,current_step,status,submitted_at,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,1,"pending",NOW(),?)');
    $stmt->execute([
        $requestNumber,(int)$rule['id'],(int)($data['company_id']??0)?:null,(int)($data['branch_id']??0)?:null,
        $documentType,$documentId,(string)($data['document_number']??''),$actionKey,round((float)($data['request_amount']??0),2),round((float)($data['request_discount']??0),2),
        $makerUserId,$notes
    ]);
    $requestId=(int)$pdo->lastInsertId();
    $stepStmt=$pdo->prepare('SELECT * FROM '.table('approval_rule_steps').' WHERE approval_rule_id=? ORDER BY step_number ASC,id ASC');
    $stepStmt->execute([(int)$rule['id']]);
    $steps=$stepStmt->fetchAll();
    if(!$steps){throw new RuntimeException('Approval rule has no approval steps configured.');}
    $insertStep=$pdo->prepare('INSERT INTO '.table('approval_request_steps').' (approval_request_id,step_number,step_label,approver_role_slug,status) VALUES (?,?,?,?,?)');
    foreach($steps as $index=>$step){
        $insertStep->execute([$requestId,(int)$step['step_number'],(string)($step['step_label']??''),(string)$step['approver_role_slug'],$index===0?'pending':'queued']);
    }
    logApprovalAction($pdo,$requestId,null,'submitted',$makerUserId,'Approval request submitted. '.$notes);
    logActivity($pdo,'Approvals','approval_request_submitted','Approval request '.$requestNumber.' submitted for '.approvalDocumentLabel($documentType).' '.$data['document_number'].'.','approval_request',$requestId);
    return latestApprovalRequest($pdo,$documentType,$documentId,$actionKey);
}

function currentApprovalStep(PDO $pdo, int $requestId): ?array
{
    $stmt=$pdo->prepare('SELECT ars.*,ar.maker_user_id,ar.current_step,ar.status request_status,ar.approval_rule_id,r.maker_checker FROM '.table('approval_request_steps').' ars LEFT JOIN '.table('approval_requests').' ar ON ar.id=ars.approval_request_id LEFT JOIN '.table('approval_rules').' r ON r.id=ar.approval_rule_id WHERE ars.approval_request_id=? AND ars.step_number=ar.current_step LIMIT 1');
    $stmt->execute([$requestId]);
    return $stmt->fetch()?:null;
}

function userCanApproveStep(PDO $pdo, array $step, ?array $user = null): bool
{
    $user=$user ?: currentUser();
    if(!$user || !hasPermission('approvals',$user)){return false;}
    if(($user['role']??'')==='admin'){return true;}
    return currentErpRoleSlug($pdo,$user)===(string)($step['approver_role_slug']??'');
}

function executeInvoiceApproval(PDO $pdo, int $id): void
{
    $stmt=$pdo->prepare('SELECT * FROM '.table('invoices').' WHERE id=? LIMIT 1 FOR UPDATE');$stmt->execute([$id]);$invoice=$stmt->fetch();
    if(!$invoice){throw new RuntimeException('Invoice not found.');}
    $scope=operationalScope($pdo);$scope['company_id']=(int)($invoice['company_id']??$scope['company_id']);$scope['branch_id']=(int)($invoice['branch_id']??$scope['branch_id']);$scope['warehouse_id']=(int)($invoice['warehouse_id']??$scope['warehouse_id']);
    enforceScopeAllowed($pdo,(int)($invoice['company_id']??0),(int)($invoice['branch_id']??0),(int)($invoice['warehouse_id']??0),true);
    if(!in_array((string)$invoice['status'],['draft','pending_approval'],true)){throw new RuntimeException('Only draft or pending approval invoices can be approved.');}
    $itemsStmt=$pdo->prepare('SELECT ii.*,p.name FROM '.table('invoice_items').' ii LEFT JOIN '.table('products').' p ON p.id=ii.product_id WHERE ii.invoice_id=? AND ii.product_id IS NOT NULL');
    $itemsStmt->execute([$id]);$items=$itemsStmt->fetchAll();
    foreach($items as $item){if(warehouseAvailableQuantity($pdo,(int)$item['product_id'],$scope)<(float)$item['quantity']){throw new RuntimeException('Insufficient warehouse stock for '.($item['name']?:$item['description']).'.');}}
    foreach($items as $item){adjustWarehouseStock($pdo,(int)$item['product_id'],-(float)$item['quantity'],$scope,'invoice',$id,'Stock reserved on invoice approval.');}
    $pdo->prepare('UPDATE '.table('invoices').' SET status="approved",approved_at=NOW() WHERE id=?')->execute([$id]);
    postInvoiceAccounting($pdo,$id);
    logActivity($pdo,'Sales','invoice_approve','Invoice #'.$id.' approved, stock ledger updated, and accounting journal posted.','invoice',$id);
}

function finalizeApprovedDocument(PDO $pdo, array $request, ?int $actorUserId = null): void
{
    $documentType=(string)($request['document_type']??'');
    $documentId=(int)($request['document_id']??0);
    if($documentType==='purchase_order'){
        $pdo->prepare('UPDATE '.table('purchase_orders').' SET status="approved",approved_at=NOW() WHERE id=? AND status IN ("draft","pending_approval")')->execute([$documentId]);
        logActivity($pdo,'Procurement','purchase_order_approve','Purchase order #'.$documentId.' approved through workflow.','purchase_order',$documentId);
        return;
    }
    if($documentType==='stock_transfer'){
        $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="approved",approved_by=?,approved_at=NOW(),rejection_reason=NULL,rejected_at=NULL WHERE id=? AND status="pending_approval"')->execute([$actorUserId?:null,$documentId]);
        logActivity($pdo,'Inventory','stock_transfer_approved','Stock transfer #'.$documentId.' approved through workflow.','stock_transfer',$documentId);
        return;
    }
    if($documentType==='expense'){
        $pdo->prepare('UPDATE '.table('expenses').' SET approval_status="approved",approved_by=?,approved_at=NOW() WHERE id=? AND approval_status="pending_approval"')->execute([$actorUserId?:null,$documentId]);
        postExpenseAccounting($pdo,$documentId);
        logActivity($pdo,'Finance','expense_approved','Expense #'.$documentId.' approved and posted to accounting through workflow.','expense',$documentId);
        return;
    }
    if($documentType==='invoice'){
        executeInvoiceApproval($pdo,$documentId);
        return;
    }
    if($documentType==='quotation'){
        $pdo->prepare('UPDATE '.table('quotations').' SET status="accepted" WHERE id=? AND status IN ("draft","sent","pending_approval") AND converted_invoice_id IS NULL')->execute([$documentId]);
        logActivity($pdo,'Quotation','quotation_accepted','Quotation #'.$documentId.' accepted through workflow.','quotation',$documentId);
        return;
    }
    if($documentType==='purchase_requisition'){
        $pdo->prepare('UPDATE '.table('purchase_requisitions').' SET status="approved",approved_by=?,approved_at=NOW() WHERE id=? AND status="pending_approval"')->execute([$actorUserId?:null,$documentId]);
        logActivity($pdo,'Procurement','purchase_requisition_approved','Purchase requisition #'.$documentId.' approved through workflow.','purchase_requisition',$documentId);
        return;
    }
    if($documentType==='sales_order'){
        $pdo->prepare('UPDATE '.table('sales_orders').' SET status="approved",credit_check_status="overridden",credit_override_by=?,credit_override_at=NOW(),approved_by=?,approved_at=NOW() WHERE id=? AND status="pending_approval"')->execute([$actorUserId?:null,$actorUserId?:null,$documentId]);
        logActivity($pdo,'Credit Control','sales_order_credit_override','Sales order #'.$documentId.' credit override approved through workflow.','sales_order',$documentId);
        return;
    }
    if($documentType==='supplier_invoice'){
        $pdo->prepare('UPDATE '.table('supplier_invoices').' SET approval_status="approved",status="approved",approved_by=?,approved_at=NOW() WHERE id=? AND approval_status="pending_approval"')->execute([$actorUserId?:null,$documentId]);
        logActivity($pdo,'Accounts Payable','supplier_invoice_variance_approved','Supplier invoice #'.$documentId.' variance approved through workflow.','supplier_invoice',$documentId);
        return;
    }
    if($documentType==='return_rma'){
        $pdo->prepare('UPDATE '.table('returns_rma').' SET approval_status="approved",status="approved",approved_by=?,approved_at=NOW() WHERE id=? AND approval_status="pending_approval"')->execute([$actorUserId?:null,$documentId]);
        logActivity($pdo,'Returns','rma_approved','RMA #'.$documentId.' approved through workflow.','return_rma',$documentId);
        return;
    }
    if($documentType==='warranty_claim'){
        $pdo->prepare('UPDATE '.table('warranty_claims').' SET approval_status="approved",status="approved",approved_by=?,approved_at=NOW() WHERE id=? AND approval_status="pending_approval"')->execute([$actorUserId?:null,$documentId]);
        logActivity($pdo,'Warranty','warranty_claim_approved','Warranty claim #'.$documentId.' approved through workflow.','warranty_claim',$documentId);
        return;
    }
    if($documentType==='project'){
        $pdo->prepare('UPDATE '.table('projects').' SET status="active" WHERE id=? AND status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Budget','project_budget_override_approved','Project #'.$documentId.' budget override approved through workflow.','project',$documentId);
        return;
    }
}

function rejectApprovalDocument(PDO $pdo, array $request, string $reason, ?int $actorUserId = null): void
{
    $documentType=(string)($request['document_type']??'');
    $documentId=(int)($request['document_id']??0);
    if($documentType==='purchase_order'){
        $pdo->prepare('UPDATE '.table('purchase_orders').' SET status="draft" WHERE id=? AND status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Procurement','purchase_order_approval_rejected','Purchase order #'.$documentId.' approval rejected; document returned to draft.','purchase_order',$documentId);
        return;
    }
    if($documentType==='stock_transfer'){
        $pdo->prepare('UPDATE '.table('stock_transfers').' SET status="rejected",rejected_at=NOW(),rejection_reason=? WHERE id=? AND status="pending_approval"')->execute([$reason,$documentId]);
        logActivity($pdo,'Inventory','stock_transfer_rejected','Stock transfer #'.$documentId.' approval rejected.','stock_transfer',$documentId);
        return;
    }
    if($documentType==='expense'){
        $pdo->prepare('UPDATE '.table('expenses').' SET approval_status="rejected" WHERE id=? AND approval_status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Finance','expense_approval_rejected','Expense #'.$documentId.' approval rejected.','expense',$documentId);
        return;
    }
    if($documentType==='invoice'){
        $pdo->prepare('UPDATE '.table('invoices').' SET status="draft" WHERE id=? AND status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Sales','invoice_approval_rejected','Invoice #'.$documentId.' approval rejected; document returned to draft.','invoice',$documentId);
        return;
    }
    if($documentType==='quotation'){
        $pdo->prepare('UPDATE '.table('quotations').' SET status="rejected" WHERE id=? AND status="pending_approval" AND converted_invoice_id IS NULL')->execute([$documentId]);
        logActivity($pdo,'Quotation','quotation_approval_rejected','Quotation #'.$documentId.' approval rejected.','quotation',$documentId);
        return;
    }
    if($documentType==='purchase_requisition'){
        $pdo->prepare('UPDATE '.table('purchase_requisitions').' SET status="rejected" WHERE id=? AND status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Procurement','purchase_requisition_rejected','Purchase requisition #'.$documentId.' approval rejected.','purchase_requisition',$documentId);
        return;
    }
    if($documentType==='sales_order'){
        $pdo->prepare('UPDATE '.table('sales_orders').' SET status="draft",credit_check_status="blocked" WHERE id=? AND status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Credit Control','sales_order_credit_override_rejected','Sales order #'.$documentId.' credit override rejected.','sales_order',$documentId);
        return;
    }
    if($documentType==='supplier_invoice'){
        $pdo->prepare('UPDATE '.table('supplier_invoices').' SET approval_status="rejected",status="variance" WHERE id=? AND approval_status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Accounts Payable','supplier_invoice_variance_rejected','Supplier invoice #'.$documentId.' variance approval rejected.','supplier_invoice',$documentId);
        return;
    }
    if($documentType==='return_rma'){
        $pdo->prepare('UPDATE '.table('returns_rma').' SET approval_status="rejected",status="rejected" WHERE id=? AND approval_status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Returns','rma_rejected','RMA #'.$documentId.' approval rejected.','return_rma',$documentId);
        return;
    }
    if($documentType==='warranty_claim'){
        $pdo->prepare('UPDATE '.table('warranty_claims').' SET approval_status="rejected",status="rejected" WHERE id=? AND approval_status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Warranty','warranty_claim_rejected','Warranty claim #'.$documentId.' approval rejected.','warranty_claim',$documentId);
        return;
    }
    if($documentType==='project'){
        $pdo->prepare('UPDATE '.table('projects').' SET status="planning" WHERE id=? AND status="pending_approval"')->execute([$documentId]);
        logActivity($pdo,'Budget','project_budget_override_rejected','Project #'.$documentId.' budget override rejected.','project',$documentId);
        return;
    }
}

function decideApprovalRequest(PDO $pdo, int $requestId, string $decision, string $notes=''): void
{
    $decision=strtolower(trim($decision));
    if(!in_array($decision,['approve','reject'],true)){throw new RuntimeException('Unknown approval decision.');}
    $requestStmt=$pdo->prepare('SELECT ar.*,r.maker_checker FROM '.table('approval_requests').' ar LEFT JOIN '.table('approval_rules').' r ON r.id=ar.approval_rule_id WHERE ar.id=? LIMIT 1 FOR UPDATE');
    $requestStmt->execute([$requestId]);$request=$requestStmt->fetch();
    if(!$request){throw new RuntimeException('Approval request not found.');}
    if(($request['status']??'')!=='pending'){throw new RuntimeException('This approval request is no longer pending.');}
    enforceScopeAllowed($pdo,(int)($request['company_id']??0),(int)($request['branch_id']??0),0,true);
    $step=currentApprovalStep($pdo,$requestId);
    if(!$step || ($step['status']??'')!=='pending'){throw new RuntimeException('The current approval step is not actionable.');}
    $user=currentUser();$actorId=(int)($user['id']??0)?:null;
    if(!userCanApproveStep($pdo,$step,$user)){throw new RuntimeException('You are not assigned to the current approval step.');}
    if(!empty($request['maker_checker']) && (int)($request['maker_user_id']??0)>0 && (int)($request['maker_user_id']??0)===$actorId && ($user['role']??'')!=='admin'){
        throw new RuntimeException('Maker-checker control blocks the creator from approving this request.');
    }
    if($decision==='reject'){
        $reason=trim($notes);
        if($reason===''){throw new RuntimeException('A rejection note is required.');}
        $pdo->prepare('UPDATE '.table('approval_request_steps').' SET status="rejected",decided_by=?,decided_at=NOW(),decision_notes=? WHERE id=?')->execute([$actorId,$reason,(int)$step['id']]);
        $pdo->prepare('UPDATE '.table('approval_requests').' SET status="rejected",resolved_at=NOW(),rejection_reason=? WHERE id=?')->execute([$reason,$requestId]);
        logApprovalAction($pdo,$requestId,(int)$step['id'],'rejected',$actorId,$reason);
        rejectApprovalDocument($pdo,$request,$reason,$actorId);
        logActivity($pdo,'Approvals','approval_request_rejected','Approval request '.$request['request_number'].' rejected.','approval_request',$requestId);
        return;
    }
    $pdo->prepare('UPDATE '.table('approval_request_steps').' SET status="approved",decided_by=?,decided_at=NOW(),decision_notes=? WHERE id=?')->execute([$actorId,$notes,(int)$step['id']]);
    $nextStmt=$pdo->prepare('SELECT * FROM '.table('approval_request_steps').' WHERE approval_request_id=? AND step_number>? ORDER BY step_number ASC LIMIT 1');
    $nextStmt->execute([$requestId,(int)$step['step_number']]);$next=$nextStmt->fetch();
    if($next){
        $pdo->prepare('UPDATE '.table('approval_request_steps').' SET status="pending" WHERE id=?')->execute([(int)$next['id']]);
        $pdo->prepare('UPDATE '.table('approval_requests').' SET current_step=? WHERE id=?')->execute([(int)$next['step_number'],$requestId]);
        logApprovalAction($pdo,$requestId,(int)$step['id'],'step_approved',$actorId,$notes);
        logActivity($pdo,'Approvals','approval_step_approved','Approval request '.$request['request_number'].' advanced to step '.(int)$next['step_number'].'.','approval_request',$requestId);
        return;
    }
    $pdo->prepare('UPDATE '.table('approval_requests').' SET status="approved",resolved_at=NOW() WHERE id=?')->execute([$requestId]);
    logApprovalAction($pdo,$requestId,(int)$step['id'],'approved',$actorId,$notes);
    finalizeApprovedDocument($pdo,$request,$actorId);
    logActivity($pdo,'Approvals','approval_request_approved','Approval request '.$request['request_number'].' fully approved.','approval_request',$requestId);
}

function customerFromOrder(PDO $pdo, array $order): int
{
    $email = trim((string)($order['customer_email'] ?? ''));
    if ($email !== '') {
        $find = $pdo->prepare('SELECT id FROM ' . table('customers') . ' WHERE email=? LIMIT 1');
        $find->execute([$email]);
        $existing = $find->fetchColumn();
        if ($existing) {
            return (int)$existing;
        }
    }

    $next = (int)$pdo->query('SELECT COUNT(*) FROM ' . table('customers'))->fetchColumn() + 1;
    $code = 'CUS-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    $scope=operationalScope($pdo);
    $stmt = $pdo->prepare('INSERT INTO ' . table('customers') . ' (company_id,branch_id,customer_code,customer_type,company_name,contact_name,email,phone,billing_address,shipping_address,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        (int)($order['company_id'] ?? $scope['company_id'] ?? 0) ?: null,
        (int)($order['branch_id'] ?? $scope['branch_id'] ?? 0) ?: null,
        $code,
        'b2c',
        '',
        (string)($order['customer_name'] ?? 'Website Customer'),
        $email,
        (string)($order['customer_phone'] ?? ''),
        (string)($order['billing_address'] ?? ''),
        (string)($order['shipping_address'] ?? ''),
        'active',
    ]);
    return (int)$pdo->lastInsertId();
}

function nextNumber(PDO $pdo, string $tableName, string $prefix): string
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM ' . table($tableName))->fetchColumn() + 1;
    return $prefix . '-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
}

function createErpInvoiceFromOrder(PDO $pdo, int $orderId, bool $autoPaid = false): int
{
    $orderStmt = $pdo->prepare('SELECT * FROM ' . table('orders') . ' WHERE id=? LIMIT 1');
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order) {
        throw new RuntimeException('Order was not found.');
    }
    if (!empty($order['erp_invoice_id'])) {
        return (int)$order['erp_invoice_id'];
    }

    $customerId = customerFromOrder($pdo, $order);
    $orderScope=[
        'company_id'=>(int)($order['company_id']??0),
        'branch_id'=>(int)($order['branch_id']??0),
        'warehouse_id'=>(int)($order['warehouse_id']??0),
        'location_id'=>(int)setting('default_location_id','0'),
    ];
    $invoiceNumber = nextScopedDocumentNumber($pdo, 'invoice', (string)setting('invoice_prefix', 'INV'), $orderScope);
    $status = $autoPaid ? 'paid' : 'approved';
    $paidAmount = $autoPaid ? (float)$order['total'] : 0.0;
    $balance = max(0, (float)$order['total'] - $paidAmount);
    $now = date('Y-m-d H:i:s');

    $invoice = $pdo->prepare('INSERT INTO ' . table('invoices') . ' (company_id,branch_id,warehouse_id,invoice_number,customer_id,customer_name,customer_email,customer_type,billing_address,subtotal,discount,tax,shipping,total,amount_paid,balance_due,status,sales_channel,source_order_id,due_date,approved_at,paid_at,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $invoice->execute([
        (int)($order['company_id']??0) ?: null,
        (int)($order['branch_id']??0) ?: null,
        (int)($order['warehouse_id']??0) ?: null,
        $invoiceNumber,
        $customerId,
        (string)$order['customer_name'],
        (string)$order['customer_email'],
        'b2c',
        (string)$order['billing_address'],
        (float)$order['subtotal'],
        (float)$order['discount'],
        (float)$order['tax'],
        (float)$order['shipping_cost'],
        (float)$order['total'],
        $paidAmount,
        $balance,
        $status,
        'website',
        $orderId,
        date('Y-m-d', strtotime('+7 days')),
        $now,
        $autoPaid ? $now : null,
        'Automatically created from website order ' . (string)$order['order_number'],
    ]);
    $invoiceId = (int)$pdo->lastInsertId();

    $items = $pdo->prepare('SELECT * FROM ' . table('order_items') . ' WHERE order_id=? ORDER BY id ASC');
    $items->execute([$orderId]);
    $insertLine = $pdo->prepare('INSERT INTO ' . table('invoice_items') . ' (invoice_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
    foreach ($items->fetchAll() as $item) {
        $insertLine->execute([
            $invoiceId,
            'product',
            (int)($item['product_id'] ?? 0) ?: null,
            (string)$item['product_name'],
            (float)$item['quantity'],
            (float)$item['price'],
            0,
            (float)$item['total'],
        ]);
    }

    postInvoiceAccounting($pdo, $invoiceId);
    if ($autoPaid) {
        $payment = $pdo->prepare('INSERT INTO ' . table('payments') . ' (company_id,branch_id,payment_number,invoice_id,customer_id,amount,method,reference,status,paid_at,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $payment->execute([
            (int)($order['company_id']??0) ?: null,
            (int)($order['branch_id']??0) ?: null,
            nextScopedDocumentNumber($pdo, 'payment', (string)setting('payment_prefix', 'PAY'), $orderScope),
            $invoiceId,
            $customerId,
            (float)$order['total'],
            (string)($order['payment_method'] ?? 'website'),
            (string)$order['order_number'],
            'received',
            $now,
            'Auto-recorded from website checkout.',
        ]);
        postPaymentAccounting($pdo, (int)$pdo->lastInsertId());
    }

    $update = $pdo->prepare('UPDATE ' . table('orders') . ' SET erp_invoice_id=? WHERE id=?');
    $update->execute([$invoiceId, $orderId]);
    logActivity($pdo, 'Sales', 'website_order_to_invoice', 'ERP invoice ' . $invoiceNumber . ' created from website order ' . (string)$order['order_number'], 'invoice', $invoiceId);
    return $invoiceId;
}

function releaseOrderStock(PDO $pdo, int $orderId): void
{
    $orderStmt = $pdo->prepare('SELECT company_id,branch_id,warehouse_id,inventory_reserved, stock_released FROM ' . table('orders') . ' WHERE id=? LIMIT 1');
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order || !(int)$order['inventory_reserved'] || (int)$order['stock_released']) {
        return;
    }

    $items = $pdo->prepare('SELECT product_id, quantity FROM ' . table('order_items') . ' WHERE order_id=?');
    $items->execute([$orderId]);
    foreach ($items->fetchAll() as $item) {
        $productId = (int)$item['product_id'];
        $qty = (int)$item['quantity'];
        if ($productId <= 0 || $qty <= 0) {
            continue;
        }
        $scope=operationalScope($pdo);
        $scope['company_id']=(int)($order['company_id']??$scope['company_id']);
        $scope['branch_id']=(int)($order['branch_id']??$scope['branch_id']);
        $scope['warehouse_id']=(int)($order['warehouse_id']??$scope['warehouse_id']);
        adjustWarehouseStock($pdo,$productId,$qty,$scope,'order_cancel',$orderId,'Stock returned from cancelled website order.');
    }
    $pdo->prepare('UPDATE ' . table('orders') . ' SET stock_released=1 WHERE id=?')->execute([$orderId]);
    logActivity($pdo, 'Inventory', 'release_order_stock', 'Stock released from cancelled website order #' . $orderId, 'order', $orderId);
}

function seoPageKey(string $pageKey): string
{
    $pageKey = preg_replace('/[^a-z0-9_\-]/i', '', strtolower($pageKey));
    return $pageKey !== '' ? $pageKey : 'default';
}

function seoFallbackTitle(string $title = ''): string
{
    $base = trim($title);
    if ($base === '') {
        return SHOP_NAME;
    }
    return $base . ' | ' . SHOP_NAME;
}

function seoCanonicalUrl(string $pageKey, string $configuredCanonical = ''): string
{
    $configuredCanonical = trim($configuredCanonical);
    if ($configuredCanonical !== '') {
        return $configuredCanonical;
    }
    $requestPath = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $requestPath = strtok($requestPath, '?') ?: '/';
    return rtrim(SITE_URL, '/') . '/' . ltrim($requestPath, '/');
}

function seoMetaValue(string $pageKey, string $field, string $fallback = ''): string
{
    $pageKey = seoPageKey($pageKey);
    $value = trim((string)setting('seo_' . $pageKey . '_' . $field, ''));
    if ($value !== '') {
        return $value;
    }
    return trim($fallback);
}




function websiteBrandSettings(): array
{
    $websiteName = trim((string)setting('website_brand_name', 'Your Store Name')) ?: SHOP_NAME;
    return [
        'website_name' => $websiteName,
        'header_name' => trim((string)setting('header_brand_name', $websiteName)) ?: $websiteName,
        'header_tagline' => trim((string)setting('header_brand_tagline', 'Diagnostic Software Store')) ?: 'Diagnostic Software Store',
        'header_mark' => strtoupper(substr(trim((string)setting('header_brand_mark', 'CS')) ?: 'CS', 0, 4)),
        'footer_name' => trim((string)setting('footer_brand_name', $websiteName)) ?: $websiteName,
        'footer_mark' => strtoupper(substr(trim((string)setting('footer_brand_mark', 'CS')) ?: 'CS', 0, 4)),
    ];
}


function ensureCustomPagesTable(?PDO $pdo = null): void
{
    $pdo = $pdo ?: getDB();
    $pdo->exec('CREATE TABLE IF NOT EXISTS ' . table('custom_pages') . ' (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content_html LONGTEXT,
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords TEXT,
        header_label VARCHAR(120),
        show_in_header TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB');

    $requiredColumns = [
        'content_html' => 'LONGTEXT',
        'meta_title' => 'VARCHAR(255)',
        'meta_description' => 'TEXT',
        'meta_keywords' => 'TEXT',
        'header_label' => 'VARCHAR(120)',
        'show_in_header' => 'TINYINT(1) DEFAULT 0',
        'sort_order' => 'INT DEFAULT 0',
        'active' => 'TINYINT(1) DEFAULT 1',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    ];

    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([DB_PREFIX . 'custom_pages']);
    $existing = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $existing, true)) {
            $pdo->exec('ALTER TABLE ' . table('custom_pages') . ' ADD COLUMN `' . $column . '` ' . $definition);
        }
    }
}

function customPageUrl(string $slug): string
{
    return SITE_URL . '/page.php?slug=' . urlencode($slug);
}

function headerCustomPages(): array
{
    try {
        $pdo = getDB();
        ensureCustomPagesTable($pdo);
        $stmt = $pdo->query('SELECT title,slug,header_label FROM ' . table('custom_pages') . ' WHERE active=1 AND show_in_header=1 ORDER BY sort_order ASC,title ASC LIMIT 10');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function contactDetails(): array
{
    return [
        'enabled' => setting('contact_details_enabled','1') === '1',
        'title' => trim((string)setting('contact_page_title','Contact Us')) ?: 'Contact Us',
        'intro' => trim((string)setting('contact_page_intro','Send us your requirement and our team will contact you shortly.')),
        'email' => trim((string)setting('contact_email', SHOP_EMAIL)) ?: SHOP_EMAIL,
        'phone' => trim((string)setting('contact_phone', SHOP_PHONE)) ?: SHOP_PHONE,
        'whatsapp' => trim((string)setting('contact_whatsapp', SHOP_PHONE)) ?: SHOP_PHONE,
        'address' => trim((string)setting('contact_address', SHOP_ADDRESS)) ?: SHOP_ADDRESS,
        'hours' => trim((string)setting('contact_hours','Monday to Saturday, 9:00 AM - 6:00 PM')),
        'map_url' => trim((string)setting('contact_map_url','')),
        'footer_title' => trim((string)setting('footer_contact_title','Contact')) ?: 'Contact',
    ];
}

function whatsappUrl(string $phone): string
{
    $number = preg_replace('/\D+/', '', $phone);
    return $number !== '' ? 'https://wa.me/' . $number : '#';
}

function frontendSecurityOptions(): array
{
    return [
        'enabled' => setting('frontend_security_enabled','0') === '1',
        'disable_right_click' => setting('frontend_security_disable_right_click','1') === '1',
        'block_devtools_keys' => setting('frontend_security_block_devtools_keys','1') === '1',
        'block_view_source' => setting('frontend_security_block_view_source','1') === '1',
        'block_text_select' => setting('frontend_security_block_text_select','0') === '1',
        'block_copy' => setting('frontend_security_block_copy','0') === '1',
        'block_image_drag' => setting('frontend_security_block_image_drag','1') === '1',
        'devtools_overlay' => setting('frontend_security_devtools_overlay','0') === '1',
        'noscript_warning' => setting('frontend_security_noscript_warning','0') === '1',
        'warning_message' => trim((string)setting('frontend_security_warning_message','This action is disabled for website security.')) ?: 'This action is disabled for website security.',
    ];
}

function frontendSecurityHeadAssets(): void
{
    $o = frontendSecurityOptions();
    if (!$o['enabled']) {
        return;
    }
    echo '<style id="frontend-security-style">';
    echo '.frontend-security-toast{position:fixed;left:50%;bottom:28px;transform:translateX(-50%) translateY(20px);z-index:999999;background:#0f172a;color:#fff;padding:12px 18px;border-radius:16px;box-shadow:0 18px 45px rgba(15,23,42,.28);font-weight:800;font-size:14px;opacity:0;pointer-events:none;transition:.22s ease;text-align:center;max-width:min(92vw,520px)}';
    echo '.frontend-security-toast.is-visible{opacity:1;transform:translateX(-50%) translateY(0)}';
    echo '.frontend-security-overlay{position:fixed;inset:0;z-index:999998;background:rgba(15,23,42,.92);color:#fff;display:none;align-items:center;justify-content:center;text-align:center;padding:28px}';
    echo '.frontend-security-overlay.is-visible{display:flex}';
    echo '.frontend-security-overlay-box{max-width:620px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:28px;padding:32px;box-shadow:0 24px 70px rgba(0,0,0,.32)}';
    echo '.frontend-security-overlay-box h2{font-size:28px;font-weight:900;margin:0 0 10px}.frontend-security-overlay-box p{opacity:.82;margin:0;line-height:1.6}';
    if ($o['block_text_select']) {
        echo 'body.frontend-security-active,body.frontend-security-active *{-webkit-user-select:none!important;-moz-user-select:none!important;user-select:none!important} input,textarea,[contenteditable=true]{-webkit-user-select:text!important;-moz-user-select:text!important;user-select:text!important}';
    }
    if ($o['block_image_drag']) {
        echo 'body.frontend-security-active img{-webkit-user-drag:none!important;user-drag:none!important}';
    }
    echo '</style>';
    if ($o['noscript_warning']) {
        echo '<noscript><style>.frontend-noscript-security{position:fixed;inset:0;z-index:999999;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;text-align:center;padding:30px;font-family:Arial,sans-serif}.frontend-noscript-security div{max-width:620px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:24px;padding:30px}.frontend-noscript-security h2{margin:0 0 12px;font-size:28px}</style><div class="frontend-noscript-security"><div><h2>JavaScript Required</h2><p>Please enable JavaScript to use this website securely.</p></div></div></noscript>';
    }
}

function frontendSecurityFooterAssets(): void
{
    $o = frontendSecurityOptions();
    if (!$o['enabled']) {
        return;
    }
    $json = json_encode($o, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    echo '<div class="frontend-security-toast" data-frontend-security-toast></div>';
    echo '<div class="frontend-security-overlay" data-frontend-security-overlay><div class="frontend-security-overlay-box"><h2>Protected Website</h2><p>' . esc($o['warning_message']) . '</p></div></div>';
    echo '<script id="frontend-security-script">window.FrontendSecurityOptions=' . $json . ';
(function(){
  var o = window.FrontendSecurityOptions || {};
  if(!o.enabled){return;}
  document.body.classList.add("frontend-security-active");
  var toast = document.querySelector("[data-frontend-security-toast]");
  var overlay = document.querySelector("[data-frontend-security-overlay]");
  var timer = null;
  function warn(message){
    if(!toast){return;}
    toast.textContent = message || o.warning_message || "This action is disabled.";
    toast.classList.add("is-visible");
    clearTimeout(timer);
    timer = setTimeout(function(){ toast.classList.remove("is-visible"); }, 1800);
  }
  function blockEvent(e, message){
    if(e.target && (e.target.matches("input, textarea, [contenteditable=true]") || e.target.closest("[data-security-allow]"))){ return true; }
    e.preventDefault();
    e.stopPropagation();
    warn(message);
    return false;
  }
  if(o.disable_right_click){
    document.addEventListener("contextmenu", function(e){ return blockEvent(e, o.warning_message); }, true);
  }
  if(o.block_image_drag){
    document.addEventListener("dragstart", function(e){
      if(e.target && e.target.tagName && e.target.tagName.toLowerCase()==="img"){ return blockEvent(e, o.warning_message); }
    }, true);
  }
  if(o.block_copy){
    ["copy","cut"].forEach(function(evt){
      document.addEventListener(evt, function(e){ return blockEvent(e, "Copy is disabled on this website."); }, true);
    });
  }
  if(o.block_devtools_keys || o.block_view_source){
    document.addEventListener("keydown", function(e){
      var key = (e.key || "").toLowerCase();
      var code = e.keyCode || e.which;
      var blocked = false;
      if(o.block_devtools_keys && (code === 123 || (e.ctrlKey && e.shiftKey && ["i","j","c"].indexOf(key) !== -1))){ blocked = true; }
      if(o.block_view_source && (e.ctrlKey && ["u","s"].indexOf(key) !== -1)){ blocked = true; }
      if(blocked){ return blockEvent(e, o.warning_message); }
    }, true);
  }
  if(o.devtools_overlay && overlay){
    var threshold = 170;
    setInterval(function(){
      var open = (window.outerWidth - window.innerWidth > threshold) || (window.outerHeight - window.innerHeight > threshold);
      overlay.classList.toggle("is-visible", !!open);
    }, 900);
    overlay.addEventListener("click", function(){ overlay.classList.remove("is-visible"); });
  }
})();</script>';
}

function siteHeader(string $title = '', string $seoPage = 'default', array $seoOverrides = []): void
{
    $seoPage = seoPageKey($seoPage);
    $displayTitle = trim((string)($seoOverrides['title'] ?? seoMetaValue($seoPage, 'title', seoFallbackTitle($title))));
    if ($displayTitle === '') {
        $displayTitle = seoFallbackTitle($title);
    }
    $defaultDescription = trim((string)setting('seo_default_description', ''));
    $metaDescription = trim((string)($seoOverrides['description'] ?? seoMetaValue($seoPage, 'description', $defaultDescription)));
    $metaKeywords = trim((string)($seoOverrides['keywords'] ?? seoMetaValue($seoPage, 'keywords', (string)setting('seo_default_keywords', ''))));
    $metaRobots = trim((string)($seoOverrides['robots'] ?? seoMetaValue($seoPage, 'robots', (string)setting('seo_default_robots', 'index,follow'))));
    $canonical = trim((string)($seoOverrides['canonical'] ?? seoCanonicalUrl($seoPage, seoMetaValue($seoPage, 'canonical', ''))));
    $ogImage = trim((string)($seoOverrides['image'] ?? setting('seo_default_og_image', '')));
    if ($ogImage !== '' && !preg_match('#^https?://#i', $ogImage)) {
        $ogImage = rtrim(SITE_URL, '/') . '/' . ltrim($ogImage, '/');
    }

    $user = currentUser();
    $canUseCart = websitePermissionAllowed('website_cart', $user);
    $canViewPublicDownloads = websitePermissionAllowed('website_public_downloads', $user);
    $canBookSupport = websitePermissionAllowed('website_booking', $user);
    $canRequestQuote = websitePermissionAllowed('b2b_quote_request', $user);
    $canUseB2BQuote = websitePermissionAllowed('b2b_quote_request', $user);
    $brandSettings = websiteBrandSettings();
    $requestQuoteLabel = trim((string)setting('header_request_quote_label', 'Contact Us')) ?: 'Contact Us';
    $requestQuoteUrl = trim((string)setting('header_request_quote_url', '/contact.php')) ?: '/contact.php';
    $requestQuoteHref = str_starts_with($requestQuoteUrl, 'http') ? $requestQuoteUrl : SITE_URL . '/' . ltrim($requestQuoteUrl, '/');
    $bookSupportLabel = trim((string)setting('header_book_support_label', 'Book Support')) ?: 'Book Support';
    $bookSupportUrl = trim((string)setting('header_book_support_url', '/booking.php')) ?: '/booking.php';
    $bookSupportHref = str_starts_with($bookSupportUrl, 'http') ? $bookSupportUrl : SITE_URL . '/' . ltrim($bookSupportUrl, '/');
    $headerCustomPages = headerCustomPages();
    $navCategories = [];
    try {
        $navCategories = getDB()->query('SELECT id,name FROM ' . table('categories') . ' ORDER BY sort_order ASC, name ASC LIMIT 8')->fetchAll();
    } catch (Throwable $e) {
        $navCategories = [];
    }

    echo '<!doctype html><html lang="' . esc(siteLanguage()) . '" dir="' . esc(siteDirection()) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . esc($displayTitle) . '</title>';
    if ($metaDescription !== '') {
        echo '<meta name="description" content="' . esc($metaDescription) . '">';
    }
    if ($metaKeywords !== '') {
        echo '<meta name="keywords" content="' . esc($metaKeywords) . '">';
    }
    if ($metaRobots !== '') {
        echo '<meta name="robots" content="' . esc($metaRobots) . '">';
    }
    if ($canonical !== '') {
        echo '<link rel="canonical" href="' . esc($canonical) . '">';
    }
    echo '<meta property="og:title" content="' . esc($displayTitle) . '">';
    if ($metaDescription !== '') {
        echo '<meta property="og:description" content="' . esc($metaDescription) . '">';
        echo '<meta name="twitter:description" content="' . esc($metaDescription) . '">';
    }
    echo '<meta property="og:type" content="website">';
    if ($canonical !== '') {
        echo '<meta property="og:url" content="' . esc($canonical) . '">';
    }
    if ($ogImage !== '') {
        echo '<meta property="og:image" content="' . esc($ogImage) . '">';
        echo '<meta name="twitter:image" content="' . esc($ogImage) . '">';
    }
    echo '<meta name="twitter:card" content="summary_large_image">';
    echo '<meta name="twitter:title" content="' . esc($displayTitle) . '">';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';
    echo '<link rel="stylesheet" href="' . esc(ASSETS_URL) . '/css/style.css">';
    frontendSecurityHeadAssets();
    if (siteDirection() === 'rtl') {
        echo '<style>body{direction:rtl;text-align:right}.ms-auto{margin-right:auto!important;margin-left:0!important}.me-auto{margin-left:auto!important;margin-right:0!important}.dropdown-menu{text-align:right}.header-main,.utility-inner,.nav-shell{direction:rtl}.bi-chevron-right:before{transform:scaleX(-1)}</style>';
    }
    echo '</head><body class="' . (siteDirection() === 'rtl' ? 'rtl-site' : 'ltr-site') . '">';

    $showUtilityBar = setting('header_utility_enabled', '1') === '1';
    $utilityPrimary = trim((string)setting('header_utility_primary', 'ERP-connected B2B + B2C commerce'));
    $utilitySecondary = trim((string)setting('header_utility_secondary', 'Quote, invoice, stock, sales in one system'));
    if ($showUtilityBar && ($utilityPrimary !== '' || $utilitySecondary !== '' || SHOP_PHONE !== '' || SHOP_EMAIL !== '')) {
        echo '<div class="utility-bar"><div class="container utility-inner">';
        echo '<div class="utility-left">';
        if ($utilityPrimary !== '') {
            echo '<span><i class="bi bi-lightning-charge-fill"></i> ' . esc($utilityPrimary) . '</span>';
        }
        if ($utilitySecondary !== '') {
            echo '<span class="utility-secondary"><i class="bi bi-shield-check"></i> ' . esc($utilitySecondary) . '</span>';
        }
        echo '</div>';
        echo '<div class="utility-right">';
        if (SHOP_PHONE !== '') {
            echo '<a href="tel:' . esc(SHOP_PHONE) . '"><i class="bi bi-telephone"></i> ' . esc(SHOP_PHONE) . '</a>';
        }
        if (SHOP_EMAIL !== '') {
            echo '<a href="mailto:' . esc(SHOP_EMAIL) . '"><i class="bi bi-envelope"></i> ' . esc(SHOP_EMAIL) . '</a>';
        }
        echo '</div>';
        echo '</div></div>';
    }

    echo '';
    echo '<header class="store-header sticky-top">';
echo '</div>';
    echo '</div>';

    echo '<div class="container header-main">';
    echo '<a class="brand-lockup" href="' . esc(SITE_URL) . '/index.php"><span class="brand-mark">' . esc($brandSettings['header_mark']) . '</span><span><strong>' . esc($brandSettings['header_name']) . '</strong><small>' . esc($brandSettings['header_tagline']) . '</small></span></a>';
    echo '<form class="header-search" action="' . esc(SITE_URL) . '/products/index.php" method="get"><i class="bi bi-search"></i><input type="search" name="q" placeholder="Search products, SKU, or keyword"><button type="submit">Search</button></form>';
    echo '<div class="header-actions">';
    echo renderLanguageSwitcher();
    echo renderCurrencySwitcher('header');
    echo translationClientScript();
    if ($user) {
        $accountTarget = (($user['role'] ?? '') === 'employee' || ($user['role'] ?? '') === 'admin')
            ? SITE_URL . '/employee/dashboard.php'
            : SITE_URL . '/user/dashboard.php';
        echo '<a class="header-action" href="' . esc($accountTarget) . '"><i class="bi bi-person-circle"></i><span>Account</span></a>';
        if (($user['role'] ?? '') === 'admin') {
            echo '<a class="header-action" href="' . esc(ADMIN_URL) . '/dashboard.php"><i class="bi bi-grid-1x2-fill"></i><span>Admin</span></a>';
        }
        echo '<a class="header-action d-none d-md-flex" href="' . esc(SITE_URL) . '/user/logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>';
    } else {
        echo '<a class="header-action" href="' . esc(SITE_URL) . '/user/login.php"><i class="bi bi-person"></i><span>Login</span></a>';
    }
    if ($canUseCart) {
        echo '<a class="header-action cart-action" href="' . esc(SITE_URL) . '/cart.php"><i class="bi bi-bag"></i><span>Cart</span><b>' . cartCount() . '</b></a>';
    }
    echo '</div></div>';

    echo '<nav class="category-nav desktop-category-nav"><div class="container category-nav-inner">';
    echo '<button class="category-toggle" type="button" data-category-toggle><i class="bi bi-list"></i> All Categories</button>';
    echo '<div class="category-panel" data-category-panel>';
    if ($navCategories) {
        foreach ($navCategories as $cat) {
            echo '<a href="' . esc(SITE_URL) . '/products/index.php?category=' . (int)$cat['id'] . '"><i class="bi bi-chevron-right"></i>' . esc($cat['name']) . '</a>';
        }
    } else {
        echo '<a href="' . esc(SITE_URL) . '/products/index.php"><i class="bi bi-chevron-right"></i> Browse Products</a>';
    }
    echo '</div>';
    echo '<div class="main-links"><a href="' . esc(SITE_URL) . '/products/index.php">Shop</a><a href="' . esc(SITE_URL) . '/services/index.php">Services</a>';
    if ($canViewPublicDownloads) { echo '<a href="' . esc(SITE_URL) . '/downloads/index.php">Downloads</a>'; }
    echo '<a href="' . esc(SITE_URL) . '/blog/index.php">Insights</a>';
    foreach ($headerCustomPages as $pageLink) {
        $label = trim((string)($pageLink['header_label'] ?? '')) ?: (string)($pageLink['title'] ?? 'Page');
        echo '<a href="' . esc(customPageUrl((string)$pageLink['slug'])) . '">' . esc($label) . '</a>';
    }
    if ($canBookSupport) { echo '<a href="' . esc($bookSupportHref) . '">' . esc($bookSupportLabel) . '</a>'; }
    if ($canRequestQuote) { echo '<a href="' . esc($requestQuoteHref) . '">' . esc($requestQuoteLabel) . '</a>'; }
    echo '</div>';
    $showB2BEnquiry = setting('header_b2b_enquiry_enabled', '0') === '1';
    $b2bEnquiryLabel = trim((string)setting('header_b2b_enquiry_label', 'B2B Enquiry')) ?: 'B2B Enquiry';
    $b2bEnquiryUrl = trim((string)setting('header_b2b_enquiry_url', '/contact.php')) ?: '/contact.php';
    $b2bEnquiryHref = str_starts_with($b2bEnquiryUrl, 'http') ? $b2bEnquiryUrl : SITE_URL . '/' . ltrim($b2bEnquiryUrl, '/');
    if ($showB2BEnquiry && $canUseB2BQuote) {
        echo '<a class="nav-quote" href="' . esc($b2bEnquiryHref) . '"><i class="bi bi-building"></i> ' . esc($b2bEnquiryLabel) . '</a>';
    }
    echo '</div></nav>';

    $mobileAccountTarget = SITE_URL . '/user/login.php';
    $mobileAccountLabel = 'Login';
    $mobileAccountIcon = 'bi-person';
    if ($user) {
        $mobileAccountTarget = (($user['role'] ?? '') === 'employee' || ($user['role'] ?? '') === 'admin')
            ? SITE_URL . '/employee/dashboard.php'
            : SITE_URL . '/user/dashboard.php';
        $mobileAccountLabel = 'Account';
        $mobileAccountIcon = 'bi-person-circle';
    }

    echo '<div class="container mobile-amt-header d-lg-none">';
    echo '<div class="mobile-amt-topline">';
    echo '<button type="button" class="mobile-amt-menu-btn" data-mobile-drawer-open aria-label="Open menu"><i class="bi bi-list"></i></button>';
    echo '<a class="mobile-amt-brand" href="' . esc(SITE_URL) . '/index.php"><span class="mobile-amt-brand-mark">EW</span><strong>' . esc(SHOP_NAME) . '</strong></a>';
    echo '<div class="mobile-amt-actions">';
    echo '<a href="' . esc($mobileAccountTarget) . '" aria-label="' . esc($mobileAccountLabel) . '"><i class="bi ' . esc($mobileAccountIcon) . '"></i></a>';
    if ($canUseCart) { echo '<a class="mobile-amt-cart" href="' . esc(SITE_URL) . '/cart.php" aria-label="Cart"><i class="bi bi-bag"></i><b>' . cartCount() . '</b></a>'; }
    echo '</div>';
    echo '</div>';
    echo '<form class="mobile-amt-search" action="' . esc(SITE_URL) . '/products/index.php" method="get"><i class="bi bi-search"></i><input type="search" name="q" placeholder="Search products, categories, SKU"><button type="submit">Search</button></form>';
    echo '</div>';

    echo '<div class="mobile-drawer-backdrop d-lg-none" data-mobile-drawer-backdrop></div>';
    echo '<aside class="mobile-commerce-drawer d-lg-none" data-mobile-commerce-drawer aria-hidden="true">';
    echo '<div class="mobile-drawer-shell">';
    echo '<div class="mobile-drawer-handle"></div>';
    echo '<div class="mobile-drawer-topbar">';
    echo '<div class="mobile-drawer-brand"><span class="mobile-drawer-brand-mark">EW</span><div><strong>' . esc(SHOP_NAME) . '</strong><small>Commerce frontend and ERP-linked sales operations.</small></div></div>';
    echo '<button type="button" class="mobile-drawer-close-btn" data-mobile-drawer-close aria-label="Close menu"><i class="bi bi-x-lg"></i></button>';
    echo '</div>';
    $drawerLanguageSwitcher = renderLanguageSwitcher();
    if ($drawerLanguageSwitcher !== '') {
        echo '<section class="mobile-drawer-card mobile-drawer-language-card">';
        echo '<div class="mobile-drawer-card-head"><span>Language</span><strong>Choose Language</strong></div>';
        echo '<div class="mobile-drawer-language-switcher">' . $drawerLanguageSwitcher . '</div>';
        echo '</section>';
    }

    echo '<section class="mobile-drawer-card mobile-drawer-currency-card">';
    echo '<div class="mobile-drawer-card-head"><span>Currency</span><strong>Choose Currency</strong></div>';
    echo '<div class="mobile-drawer-currency-switcher" style="display:block!important;width:100%!important;max-width:100%!important;min-width:0!important;overflow:hidden!important;box-sizing:border-box!important;">' . renderCurrencySwitcher('drawer') . '</div>';
    echo '</section>';

    if (setting('mobile_drawer_pills_enabled','0') === '1') {
        echo '<div class="mobile-drawer-pills">';
        foreach (['mobile_drawer_pill_1','mobile_drawer_pill_2','mobile_drawer_pill_3','mobile_drawer_pill_4'] as $pillKey) {
            $pill = trim((string)setting($pillKey,''));
            if ($pill !== '') { echo '<span>' . esc($pill) . '</span>'; }
        }
        echo '</div>';
    }

    echo '<section class="mobile-drawer-card">';
    echo '<div class="mobile-drawer-card-head"><span>Browse</span><strong>Quick Store Menu</strong></div>';
    echo '<div class="mobile-drawer-grid">';
    echo '<a href="' . esc(SITE_URL) . '/products/index.php"><i class="bi bi-grid-3x3-gap"></i><span>All Products</span></a>';
    echo '<a href="' . esc(SITE_URL) . '/services/index.php"><i class="bi bi-tools"></i><span>Services</span></a>';
    if ($canViewPublicDownloads) { echo '<a href="' . esc(SITE_URL) . '/downloads/index.php"><i class="bi bi-download"></i><span>Downloads</span></a>'; }
    echo '<a href="' . esc(SITE_URL) . '/blog/index.php"><i class="bi bi-journal-richtext"></i><span>Insights</span></a>';
    foreach ($headerCustomPages as $pageLink) {
        $label = trim((string)($pageLink['header_label'] ?? '')) ?: (string)($pageLink['title'] ?? 'Page');
        echo '<a href="' . esc(customPageUrl((string)$pageLink['slug'])) . '"><i class="bi bi-file-earmark-richtext"></i><span>' . esc($label) . '</span></a>';
    }
    if ($canBookSupport) { echo '<a href="' . esc($bookSupportHref) . '"><i class="bi bi-calendar2-check"></i><span>' . esc($bookSupportLabel) . '</span></a>'; }
    if ($canRequestQuote) { echo '<a href="' . esc($requestQuoteHref) . '"><i class="bi bi-chat-square-text"></i><span>' . esc($requestQuoteLabel) . '</span></a>'; }
    echo '</div>';
    echo '</section>';

    echo '<section class="mobile-drawer-card mobile-drawer-categories-card">';
    echo '<div class="mobile-drawer-card-head"><span>Shop</span><strong>Categories</strong></div>';
    echo '<div class="mobile-drawer-categories">';
    if ($navCategories) {
        foreach ($navCategories as $cat) {
            echo '<a href="' . esc(SITE_URL) . '/products/index.php?category=' . (int)$cat['id'] . '"><em><i class="bi bi-chevron-right"></i></em><span>' . esc($cat['name']) . '</span></a>';
        }
    } else {
        echo '<a href="' . esc(SITE_URL) . '/products/index.php"><em><i class="bi bi-chevron-right"></i></em><span>Browse Products</span></a>';
    }
    echo '</div>';
    echo '</section>';

    if ($canUseB2BQuote) { echo '<div class="mobile-drawer-actions"><a class="drawer-quote" href="' . esc(SITE_URL) . '/contact.php"><i class="bi bi-building"></i><span>B2B Enquiry</span><small>Start a commercial quote</small></a></div>'; }
    echo '</div>';
    echo '</aside>';

    echo '</header>';

    echo '<main class="site-main py-4"><div class="container">';
    if ($notice = flash('success')) {
        echo '<div class="alert alert-success shadow-sm">' . esc($notice) . '</div>';
    }
    if ($notice = flash('error')) {
        echo '<div class="alert alert-danger shadow-sm">' . esc($notice) . '</div>';
    }
}

function siteFooter(): void
{
    $showNewsletter = setting('footer_newsletter_enabled', '1') === '1';
    $showFooterAbout = setting('footer_about_enabled', '1') === '1';
    $footerAbout = trim((string)setting('footer_about_text', 'Commerce frontend, B2B quote journey, customer checkout, and ERP-linked stock and sales operations.'));
    $showFooterBottomNote = setting('footer_bottom_note_enabled', '1') === '1';
    $footerBottomNote = trim((string)setting('footer_bottom_note', 'Built as an ERP-connected commerce suite.'));
    $brandSettings = websiteBrandSettings();

    echo '</div></main>';
    if ($showNewsletter) {
        echo '<section class="footer-newsletter"><div class="container newsletter-shell"><div><span class="eyebrow-light">' . esc(setting('footer_newsletter_eyebrow', 'Commercial updates')) . '</span><h2>' . esc(setting('footer_newsletter_title', 'Get product launches, B2B offers, and service updates.')) . '</h2><p>' . esc(setting('footer_newsletter_text', 'Use the newsletter form for campaigns, product releases, and procurement promotions.')) . '</p></div><form action="' . esc(SITE_URL) . '/subscribe.php" method="post"><input type="email" name="email" placeholder="' . esc(setting('footer_newsletter_placeholder', 'Business email address')) . '" required><button type="submit">' . esc(setting('footer_newsletter_button', 'Subscribe')) . '</button></form></div></section>';
    }
    echo '<footer class="site-footer"><div class="container footer-grid-premium">';
    echo '<div><a class="footer-brand" href="' . esc(SITE_URL) . '/index.php"><span>' . esc($brandSettings['footer_mark']) . '</span><strong>' . esc($brandSettings['footer_name']) . '</strong></a>';
    if ($showFooterAbout && $footerAbout !== '') {
        echo '<p>' . esc($footerAbout) . '</p>';
    }
    if (setting('footer_pills_enabled','0') === '1') {
        echo '<div class="footer-pills">';
        foreach (['footer_pill_1','footer_pill_2','footer_pill_3','footer_pill_4'] as $pillKey) {
            $pill = trim((string)setting($pillKey,''));
            if ($pill !== '') { echo '<span>' . esc($pill) . '</span>'; }
        }
        echo '</div>';
    }
    echo '</div>';
    $user = currentUser();
    $canUseCart = websitePermissionAllowed('website_cart', $user);
    $canViewPublicDownloads = websitePermissionAllowed('website_public_downloads', $user);
    $canBookSupport = websitePermissionAllowed('website_booking', $user);
    $canRequestQuote = websitePermissionAllowed('b2b_quote_request', $user);
    $contact = contactDetails();
    echo '<div><h3>Shop</h3><a href="' . esc(SITE_URL) . '/products/index.php">All products</a><a href="' . esc(SITE_URL) . '/services/index.php">Services</a>';
    if ($canViewPublicDownloads) { echo '<a href="' . esc(SITE_URL) . '/downloads/index.php">Downloads</a>'; }
    if ($canBookSupport) { echo '<a href="' . esc(SITE_URL) . '/booking.php">Book support</a>'; }
    echo '</div>';
    echo '<div><h3>Company</h3><a href="' . esc(SITE_URL) . '/blog/index.php">Insights</a>';
    if ($canRequestQuote) { echo '<a href="' . esc(SITE_URL) . '/contact.php">Request quote</a>'; }
    echo '<a href="' . esc(SITE_URL) . '/contact.php">Contact us</a><a href="' . esc(SITE_URL) . '/user/login.php">Customer login</a>';
    if ($canUseCart) { echo '<a href="' . esc(SITE_URL) . '/cart.php">Cart</a>'; }
    echo '</div>';
    echo '<div><h3>' . esc($contact['footer_title']) . '</h3>';
    if ($contact['enabled']) {
        echo '<a href="mailto:' . esc($contact['email']) . '">' . esc($contact['email']) . '</a><a href="tel:' . esc($contact['phone']) . '">' . esc($contact['phone']) . '</a><a href="' . esc(whatsappUrl($contact['whatsapp'])) . '" target="_blank" rel="noopener">WhatsApp: ' . esc($contact['whatsapp']) . '</a><p class="mb-1">' . esc($contact['address']) . '</p>';
        if ($contact['hours'] !== '') { echo '<p class="mb-0">' . esc($contact['hours']) . '</p>'; }
    } else {
        echo '<a href="mailto:' . esc(SHOP_EMAIL) . '">' . esc(SHOP_EMAIL) . '</a><a href="tel:' . esc(SHOP_PHONE) . '">' . esc(SHOP_PHONE) . '</a><p class="mb-0">' . esc(SHOP_ADDRESS) . '</p>';
    }
    echo '</div>';
    echo '</div><div class="container footer-bottom"><span>&copy; ' . date('Y') . ' ' . esc($brandSettings['footer_name']) . '. All rights reserved.</span>';
    if ($showFooterBottomNote && $footerBottomNote !== '') {
        echo '<span>' . esc($footerBottomNote) . '</span>';
    }
    echo '</div></footer>';
    frontendSecurityFooterAssets();
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    echo '<script src="' . esc(ASSETS_URL) . '/js/main.js"></script></body></html>';
}

/* ============================================================
   Commercial License Hardening Layer
   Added by installer hardening patch.
   This layer is local-first and can be connected to a remote
   license server by setting license_server_url in Settings.
   ============================================================ */

function hardeningCriticalFiles(): array
{
    return [
        'config.php',
        'includes/functions.php',
        'includes/db_connect.php',
        'admin/dashboard.php',
        'admin/products.php',
        'admin/add-product.php',
        'admin/edit-product.php',
        'admin/users.php',
        'admin/settings.php',
        'admin/translations.php',
    ];
}

function hardeningFileHash(string $relativePath): ?string
{
    $path = dirname(__DIR__) . '/' . ltrim($relativePath, '/');
    if (!is_file($path)) {
        return null;
    }
    return hash_file('sha256', $path) ?: null;
}

function hardeningBuildIntegrityManifest(): array
{
    $manifest = [];
    foreach (hardeningCriticalFiles() as $file) {
        $manifest[$file] = hardeningFileHash($file);
    }
    return $manifest;
}

function hardeningIntegrityManifest(): array
{
    $json = (string)setting('license_integrity_manifest_json', '');
    $manifest = json_decode($json, true);
    if (!is_array($manifest) || !$manifest) {
        $manifest = hardeningBuildIntegrityManifest();
        saveSetting('license_integrity_manifest_json', json_encode($manifest, JSON_UNESCAPED_SLASHES));
    }
    return $manifest;
}

function hardeningVerifyIntegrity(): array
{
    if (setting('license_integrity_enabled', '1') !== '1') {
        return ['ok' => true, 'changed' => [], 'missing' => []];
    }
    $baseline = hardeningIntegrityManifest();
    $changed = [];
    $missing = [];
    foreach ($baseline as $file => $expectedHash) {
        $actualHash = hardeningFileHash((string)$file);
        if ($actualHash === null) {
            $missing[] = $file;
            continue;
        }
        if ($expectedHash && $actualHash !== $expectedHash) {
            $changed[] = $file;
        }
    }
    $ok = !$changed && !$missing;
    if (!$ok) {
        $message = 'Integrity warning. Changed: ' . implode(', ', $changed) . ' Missing: ' . implode(', ', $missing);
        saveSetting('license_last_tamper_warning', $message);
        hardeningAudit('tamper_warning', $message, ['changed' => $changed, 'missing' => $missing]);
    }
    return ['ok' => $ok, 'changed' => $changed, 'missing' => $missing];
}

function hardeningStrongFingerprint(): string
{
    $parts = [
        'install_uid' => function_exists('licenseInstallationUid') ? licenseInstallationUid() : setting('license_installation_uid', ''),
        'host' => strtolower((string)($_SERVER['HTTP_HOST'] ?? php_uname('n'))),
        'server_name' => strtolower((string)($_SERVER['SERVER_NAME'] ?? '')),
        'server_addr' => (string)($_SERVER['SERVER_ADDR'] ?? ''),
        'document_root' => hash('sha256', (string)($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__))),
        'db_name' => defined('DB_NAME') ? hash('sha256', DB_NAME) : '',
        'db_prefix' => defined('DB_PREFIX') ? hash('sha256', DB_PREFIX) : '',
        'shop_email' => defined('SHOP_EMAIL') ? hash('sha256', SHOP_EMAIL) : '',
        'php_uname' => hash('sha256', php_uname()),
    ];
    return hash('sha256', json_encode($parts, JSON_UNESCAPED_SLASHES));
}

function hardeningWatermark(): string
{
    $wm = (string)setting('license_watermark_id', '');
    if ($wm === '') {
        $seed = hardeningStrongFingerprint() . '|' . microtime(true) . '|' . random_int(100000, 999999);
        $wm = 'WM-' . strtoupper(substr(hash('sha256', $seed), 0, 24));
        saveSetting('license_watermark_id', $wm);
        saveSetting('license_watermark_created_at', date('Y-m-d H:i:s'));
    }
    return $wm;
}

function hardeningRemoteStatus(): string
{
    $status = strtolower(trim((string)setting('license_remote_status', 'active')));
    return in_array($status, ['active','readonly','read_only','revoked','killed'], true) ? $status : 'active';
}

function hardeningIsReadOnly(): bool
{
    // Never lock the recovery/activation tooling itself.
    if (function_exists('licenseRecoveryIsToolPage') && licenseRecoveryIsToolPage()) {
        return false;
    }

    // Monitor mode never blocks writes. It only reports.
    if (function_exists('licenseEnforcementMode') && licenseEnforcementMode() === 'monitor') {
        return false;
    }

    // Local setting can disable read-only locking for invalid licenses.
    if (setting('license_readonly_when_invalid', '0') !== '1') {
        return false;
    }

    $status = hardeningRemoteStatus();
    if (in_array($status, ['readonly', 'read_only', 'revoked', 'killed'], true)) {
        return true;
    }

    // Trial mode must remain writable; entity limits are enforced separately.
    if (setting('license_status', 'trial') === 'trial') {
        return false;
    }

    if (function_exists('licenseIsActivated')) {
        return !licenseIsActivated();
    }

    return false;
}

function hardeningIsKilled(): bool
{
    return hardeningRemoteStatus() === 'killed';
}

function hardeningAllowedModules(): array
{
    $json = (string)setting('license_allowed_modules_json', '[]');
    $modules = json_decode($json, true);
    return is_array($modules) ? array_values(array_unique(array_map('strval', $modules))) : [];
}

function hardeningModuleAllowed(string $module): bool
{
    $module = trim($module);
    if ($module === '') {
        return false;
    }
    $modules = hardeningAllowedModules();
    if (!$modules) {
        return true; // Empty means no module restriction configured yet.
    }
    return in_array($module, $modules, true);
}

function hardeningRequireModule(string $module): void
{
    if (!hardeningModuleAllowed($module)) {
        hardeningAudit('module_blocked', 'Blocked module access: ' . $module, ['module' => $module]);
        http_response_code(403);
        exit('This module is not enabled for this license.');
    }
}

function hardeningAudit(string $event, string $message, array $context = []): void
{
    try {
        $pdo = getDB();
        $payload = json_encode([
            'event' => $event,
            'message' => $message,
            'context' => $context,
            'fingerprint' => hardeningStrongFingerprint(),
            'watermark' => hardeningWatermark(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'time' => date('c'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt = $pdo->prepare('INSERT INTO ' . table('license_validation_logs') . ' (license_key_id,status,domain_name,ip_address,message,created_at) VALUES (NULL,?,?,?,?,NOW())');
        $stmt->execute([$event, $_SERVER['HTTP_HOST'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '', $payload]);
    } catch (Throwable $e) {
        // Never break the application because logging failed.
    }
}

function hardeningSendAlert(string $subject, string $message): void
{
    if (setting('license_alert_email_enabled', '0') !== '1') {
        return;
    }
    $to = trim((string)setting('license_alert_email', defined('SHOP_EMAIL') ? SHOP_EMAIL : ''));
    if ($to === '') {
        return;
    }
    @mail($to, '[License Security] ' . $subject, $message);
}

function hardeningRemoteHeartbeat(bool $force = false): array
{
    $url = trim((string)setting('license_server_url', defined('LICENSE_SERVER_URL') ? LICENSE_SERVER_URL : ''));
    if ($url === '') {
        return ['ok' => true, 'skipped' => true, 'message' => 'No license server configured.'];
    }
    $interval = max(1, (int)setting('license_heartbeat_interval_hours', defined('LICENSE_HEARTBEAT_INTERVAL_HOURS') ? (string)LICENSE_HEARTBEAT_INTERVAL_HOURS : '12'));
    $last = strtotime((string)setting('license_last_remote_heartbeat_at', '')) ?: 0;
    if (!$force && $last > 0 && (time() - $last) < ($interval * 3600)) {
        return ['ok' => true, 'skipped' => true, 'message' => 'Heartbeat interval not reached.'];
    }

    $payload = [
        'install_uid' => function_exists('licenseInstallationUid') ? licenseInstallationUid() : setting('license_installation_uid', ''),
        'fingerprint' => hardeningStrongFingerprint(),
        'watermark' => hardeningWatermark(),
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'ip' => $_SERVER['SERVER_ADDR'] ?? '',
        'shop_email' => defined('SHOP_EMAIL') ? SHOP_EMAIL : '',
        'version' => defined('APP_VERSION') ? APP_VERSION : 'local',
    ];

    $result = ['ok' => false, 'message' => 'Heartbeat failed.'];
    try {
        $ch = curl_init($url);
        if ($ch) {
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);
            $raw = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            $data = json_decode((string)$raw, true);
            if ($code >= 200 && $code < 300 && is_array($data)) {
                if (isset($data['remote_status'])) {
                    saveSetting('license_remote_status', (string)$data['remote_status']);
                }
                if (isset($data['allowed_modules']) && is_array($data['allowed_modules'])) {
                    saveSetting('license_allowed_modules_json', json_encode($data['allowed_modules'], JSON_UNESCAPED_SLASHES));
                }
                if (isset($data['limits']) && is_array($data['limits'])) {
                    saveSetting('license_remote_limits_json', json_encode($data['limits'], JSON_UNESCAPED_SLASHES));
                }
                saveSetting('license_last_remote_heartbeat_at', date('Y-m-d H:i:s'));
                saveSetting('license_last_remote_heartbeat_status', 'ok');
                $result = ['ok' => true, 'message' => (string)($data['message'] ?? 'Heartbeat OK')];
            } else {
                $result['message'] = 'HTTP ' . $code . ' ' . $err;
            }
        }
    } catch (Throwable $e) {
        $result['message'] = $e->getMessage();
    }

    if (!$result['ok']) {
        saveSetting('license_last_remote_heartbeat_status', 'failed');
        saveSetting('license_last_remote_heartbeat_message', $result['message']);
        hardeningAudit('heartbeat_failed', $result['message'], $payload);
    }
    return $result;
}

function licenseRecoveryIsToolPage(): bool
{
    $path = strtolower((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $base = basename($path);
    return in_array($base, [
        'activation-loader.php',
        'license-activation.php',
        'license-security.php',
        'code-integrity.php',
        'license-reset.php',
        'activation-loader.php',
    ], true);
}

function licenseRecoveryIsOwner(?array $user = null): bool
{
    if ($user === null && function_exists('currentUser')) {
        try { $user = currentUser(); } catch (Throwable $e) { $user = null; }
    }
    if (!is_array($user)) { return false; }
    $email = strtolower(trim((string)($user['email'] ?? '')));
    return $email === '3b@me.com' || (($user['role'] ?? '') === 'admin');
}

function hardeningBlockIfNeeded(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    // Critical: activation/recovery pages must always open, otherwise the owner
    // cannot fix an invalid or expired license.
    if (function_exists('licenseRecoveryIsToolPage') && licenseRecoveryIsToolPage()) {
        return;
    }

    hardeningWatermark();

    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $isAdmin = str_contains($uri, '/admin/');
    if ($isAdmin && random_int(1, 20) === 1) {
        hardeningVerifyIntegrity();
    }
    if ($isAdmin && random_int(1, 20) === 1) {
        hardeningRemoteHeartbeat(false);
    }

    if (hardeningIsKilled()) {
        hardeningAudit('kill_switch_block', 'Application blocked by kill switch.');
        http_response_code(423);
        exit('This installation is locked by license control.');
    }

    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method !== 'GET' && hardeningIsReadOnly()) {
        hardeningAudit('readonly_block', 'Mutation blocked because license is read-only or invalid.');
        http_response_code(423);
        exit('This installation is currently read-only because the license is invalid, expired, revoked, or pending validation.');
    }
}

hardeningBlockIfNeeded();