<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();$key=apiAuthenticateRequest($pdo,'read:orders');
$limit=max(1,min(200,(int)($_GET['limit']??50)));
$rows=$pdo->query('SELECT id,order_number,customer_name,customer_email,total,status,created_at FROM '.table('orders').' ORDER BY id DESC LIMIT '.$limit)->fetchAll();
apiRecordAccess($pdo,(int)$key['id'],'/api/v1/orders.php','GET',200);
apiJsonResponse(['success'=>true,'count'=>count($rows),'data'=>$rows]);
?>