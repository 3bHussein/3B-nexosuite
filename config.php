<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'general_trading_erp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PREFIX', 'ec_');

define('SHOP_NAME', 'General Trading ERP Store');
define('SHOP_EMAIL', '');
define('SHOP_PHONE', '');
define('SHOP_ADDRESS', '');
define('CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');
define('SITE_LANGUAGE', 'both');
define('SITE_DIRECTION', 'ltr');
define('SHOP_URL', 'http://localhost');
define('SITE_URL', SHOP_URL);
define('ASSETS_URL', SITE_URL . '/assets');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('BUSINESS_TYPE', 'general_trading');
define('BUSINESS_LABEL', 'General Trading ERP & E-commerce');
define('APP_ENCRYPTION_KEY', 'uRVtCk/SyAuTcUc/SF9VZZUI4P14ewFdHmgfRIQI4Pk=');
define('DATABASE_ENCRYPTION_TOOLS_ENABLED', '1');
define('TRIAL_PRODUCT_LIMIT', 5);
define('LICENSE_PUBLIC_KEY_B64', 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUFySkNQbHpwN1pHZHZxajhZZTlSTgovZGc4NFBORTI2STBacjFlU05jRGNVWUY1cnNDTkVHMGNJVVZ4YzFyTTdjL3g5SXI5R0NwWDR6VzFuYjFwUTVPCnliaGRSM2dtcC91VzZwWWk2N0JSalV3RGxodjN5RVJEa0hXRFBCdER5Qnc2ZDVaOVltc1R0ZGdFN1E1SW0welYKZVZITk1LWnpMaTJRaUt0ZGc4Z2trUFpMNXRVaEpJTVc1R1owS1M4MURIUkE1eDZGZVA5TWE5am9kaDNJRGQvSwo3UnJDTXRWR2hXc1o2N3h0UHVST0hFdHQwT1E5NTltZkJCaFRVdXF5b05aQnIxUVNJcW1pcGx4SncxMFhwbEllCmFHSThsNWtKdEU5c2ZYeEFqejEwNXJKSWtEUFdJZkFnamRQaWxoMEQ1TURQeWhQZnBWWmF0LzNBM1R6aUVCTzQKaVFJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg==');
define('LICENSE_SERVER_URL', ''); // Set to your remote license API endpoint, for example: https://license.yourdomain.com/api/validate
define('LICENSE_HEARTBEAT_INTERVAL_HOURS', 12);
define('LICENSE_GRACE_DAYS', 3);
define('LICENSE_ENFORCEMENT_MODE', 'enforce'); // enforce | monitor

date_default_timezone_set('Asia/Dubai');
error_reporting(E_ALL);
ini_set('display_errors', '0');