<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
header('Content-Type: application/json');
$pdo=getDB();
$token=(string)($_GET['token']??'');
if($token==='' || !hash_equals((string)setting('cron_secret',''),$token)){
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Invalid cron token']);
    exit;
}
try{
    $result=runAutomationCycle($pdo,'token_endpoint');
    echo json_encode(['ok'=>true,'result'=>$result]);
}catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
?>