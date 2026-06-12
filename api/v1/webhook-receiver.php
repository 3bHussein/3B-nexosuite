<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();$key=apiAuthenticateRequest($pdo,'write:webhooks');
$raw=file_get_contents('php://input');
$payload=json_decode($raw,true);
if(!is_array($payload)){apiJsonResponse(['success'=>false,'error'=>'Invalid JSON payload.'],422);}
$eventType=(string)($payload['event_type'] ?? $payload['event'] ?? 'external.event');
$eventId=queueWebhookEvent($pdo,$eventType,$payload,'external',0);
apiRecordAccess($pdo,(int)$key['id'],'/api/v1/webhook-receiver.php','POST',200);
apiJsonResponse(['success'=>true,'event_id'=>$eventId,'event_type'=>$eventType]);
?>