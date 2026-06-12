<?php
require_once dirname(__DIR__) . '/includes/functions.php';
session_unset();
session_destroy();
session_start();
flash('success', 'Employee session closed.');
redirect(SITE_URL . '/employee/login.php');