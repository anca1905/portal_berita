<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
logoutAdmin();
header('Location: ' . APP_URL . '/admin/login.php');
exit;
