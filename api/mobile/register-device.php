<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();
$payload=json_decode(file_get_contents('php://input'),true) ?: [];
$userId=(int)($payload['user_id'] ?? ($_SESSION['user_id'] ?? 0)) ?: null;
$deviceId=(string)($payload['device_id'] ?? ('web-'.substr(sha1($_SERVER['HTTP_USER_AGENT']??''),0,12)));
$id=registerMobileDeviceSession($pdo,$userId,$deviceId,(string)($payload['device_name']??'Web PWA'),(string)($payload['platform']??'web'),(int)($payload['installed']??0));
apiJsonResponse(['success'=>true,'device_session_id'=>$id]);
?>