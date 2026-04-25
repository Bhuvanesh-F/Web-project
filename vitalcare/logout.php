<?php
// logout.php
require_once __DIR__ . '/includes/auth.php';
$_SESSION = [];
session_destroy();
header('Location: /vitalcare/login.php?msg=logged_out');
exit;
