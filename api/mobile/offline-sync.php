<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();
$payload=json_decode(file_get_contents('php://input'),true) ?: [];
$userId=(int)($payload['user_id'] ?? ($_SESSION['user_id'] ?? 0)) ?: null;
$id=queueMobileOfflineSync($pdo,$userId,(string)($payload['device_id']??'web'),(string)($payload['entity_type']??'job_card_draft'),(int)($payload['entity_id']??0)?:null,(string)($payload['operation']??'upsert'),(array)($payload['payload']??$payload));
apiJsonResponse(['success'=>true,'sync_id'=>$id]);
?>