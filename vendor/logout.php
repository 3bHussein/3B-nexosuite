<?php
require_once dirname(__DIR__) . '/includes/functions.php';
session_unset();session_destroy();session_start();flash('success','Vendor session closed.');redirect(SITE_URL.'/vendor/login.php');
?>