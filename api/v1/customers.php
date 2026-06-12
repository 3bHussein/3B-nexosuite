<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();$key=apiAuthenticateRequest($pdo,'read:customers');
$limit=max(1,min(200,(int)($_GET['limit']??50)));
$rows=$pdo->query('SELECT id,name,email,phone,company,status,created_at FROM '.table('customers').' ORDER BY id DESC LIMIT '.$limit)->fetchAll();
apiRecordAccess($pdo,(int)$key['id'],'/api/v1/customers.php','GET',200);
apiJsonResponse(['success'=>true,'count'=>count($rows),'data'=>$rows]);
?>