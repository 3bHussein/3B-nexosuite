<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
$pdo=getDB();
apiRecordAccess($pdo,null,'/api/v1/health.php','GET',200);
apiJsonResponse(['success'=>true,'status'=>'ok','version'=>defined('INSTALLER_VERSION')?INSTALLER_VERSION:'installed','time'=>date('c')]);
?>