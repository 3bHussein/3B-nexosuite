<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();$key=apiAuthenticateRequest($pdo,'read:invoices');
$limit=max(1,min(200,(int)($_GET['limit']??50)));
$rows=$pdo->query('SELECT id,invoice_number,customer_name,total,balance_due,status,created_at FROM '.table('invoices').' ORDER BY id DESC LIMIT '.$limit)->fetchAll();
apiRecordAccess($pdo,(int)$key['id'],'/api/v1/invoices.php','GET',200);
apiJsonResponse(['success'=>true,'count'=>count($rows),'data'=>$rows]);
?>