<?php
require_once dirname(__DIR__) . '/includes/functions.php';
session_unset();
session_destroy();
session_start();
flash('success', 'You have been logged out.');
redirect(SITE_URL . '/index.php');