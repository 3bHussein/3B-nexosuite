<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(licenseStatusSummary(getDB()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);