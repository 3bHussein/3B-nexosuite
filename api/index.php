<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json');
$pdo=getDB();
$auth=$_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token='';
if(preg_match('/Bearer\s+(.+)/i',$auth,$m)){$token=trim($m[1]);}
$keyId=null;$valid=false;
if($token!==''){
    $prefix=substr($token,0,12);
    $stmt=$pdo->prepare('SELECT * FROM '.table('api_keys').' WHERE key_prefix=? AND status="active" AND (expires_at IS NULL OR expires_at>NOW()) LIMIT 1');
    $stmt->execute([$prefix]);
    $key=$stmt->fetch();
    if($key && password_verify($token,$key['token_hash'])){$valid=true;$keyId=(int)$key['id'];$pdo->prepare('UPDATE '.table('api_keys').' SET last_used_at=NOW() WHERE id=?')->execute([$keyId]);}
}
if(!$valid){
    $pdo->prepare('INSERT INTO '.table('api_access_logs').' (api_key_id,endpoint,method,status_code,ip_address,user_agent) VALUES (NULL,?,?,?,?,?)')->execute([$_SERVER['REQUEST_URI']??'/api/index.php',$_SERVER['REQUEST_METHOD']??'GET',401,$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    http_response_code(401);echo json_encode(['ok'=>false,'error'=>'Invalid or missing bearer token']);exit;
}
$resource=(string)($_GET['resource']??'status');$data=[];
try{
    if($resource==='products'){$data=$pdo->query('SELECT id,sku,name,price,stock,active,created_at FROM '.table('products').' ORDER BY id DESC LIMIT 100')->fetchAll();}
    elseif($resource==='orders'){$data=$pdo->query('SELECT id,order_number,total,status,created_at FROM '.table('orders').' ORDER BY id DESC LIMIT 100')->fetchAll();}
    elseif($resource==='customers'){$data=$pdo->query('SELECT id,customer_code,company_name,contact_name,email,phone,customer_type,created_at FROM '.table('customers').' ORDER BY id DESC LIMIT 100')->fetchAll();}
    elseif($resource==='inventory'){$data=$pdo->query('SELECT p.sku,p.name,ws.quantity,ws.stock_value FROM '.table('warehouse_stock').' ws LEFT JOIN '.table('products').' p ON p.id=ws.product_id ORDER BY ws.stock_value DESC LIMIT 100')->fetchAll();}
    elseif($resource==='kpis'){$data=$pdo->query('SELECT kpi_code,kpi_name,metric_source,target_value,active FROM '.table('report_kpis').' ORDER BY sort_order,kpi_name')->fetchAll();}
    else{$data=['status'=>'ok','version'=>defined('INSTALLER_VERSION')?INSTALLER_VERSION:'installed','resources'=>['products','orders','customers','inventory','kpis']];}
    $pdo->prepare('INSERT INTO '.table('api_access_logs').' (api_key_id,endpoint,method,status_code,ip_address,user_agent) VALUES (?,?,?,?,?,?)')->execute([$keyId,$_SERVER['REQUEST_URI']??'/api/index.php',$_SERVER['REQUEST_METHOD']??'GET',200,$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    echo json_encode(['ok'=>true,'resource'=>$resource,'data'=>$data],JSON_UNESCAPED_SLASHES);
}catch(Throwable $e){
    recordSystemError($pdo,$e,['page'=>'api/index']);
    http_response_code(500);echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
?>