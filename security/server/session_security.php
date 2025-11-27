<?php
// session_security.php - include this at top of pages that need auth
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Strict cookie params
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
'lifetime' => 0,
'path' => '/',
'domain' => $_SERVER['HTTP_HOST'],
'secure' => $secure,
'httponly' => true,
'samesite' => 'Lax'
]);

// Regenerate ID on login and periodically
if (!isset($_SESSION['created'])) {
session_regenerate_id(true);
$_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 300) { // regen every 5 minutes
session_regenerate_id(true);
$_SESSION['created'] = time();
}

// Inactivity timeout (e.g., 15 minutes)
$timeout_seconds = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_seconds)) {
session_unset();
session_destroy();
// redirect to login
header('Location: /login.php');
exit;
}
$_SESSION['last_activity'] = time();

?>