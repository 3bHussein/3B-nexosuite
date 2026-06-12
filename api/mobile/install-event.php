<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();
$payload=json_decode(file_get_contents('php://input'),true) ?: [];
$userId=(int)($payload['user_id'] ?? ($_SESSION['user_id'] ?? 0)) ?: null;
$id=recordMobileInstallEvent($pdo,$userId,(string)($payload['device_id']??'web'),(string)($payload['event_type']??'install_prompt'),(string)($payload['platform']??'web'),(string)($payload['status']??'captured'));
apiJsonResponse(['success'=>true,'event_id'=>$id]);
?>