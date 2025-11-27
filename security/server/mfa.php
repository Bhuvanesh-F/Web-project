<?php
// mfa.php - basic MFA example (email code). Integrate with your auth flow.

function send_mfa_code($email) {
$code = random_int(100000, 999999);
$_SESSION['mfa_code'] = $code;
$_SESSION['mfa_expires'] = time() + 300; // 5 minutes

$subject = "Your MFA code";
$message = "Your MFA code is: $code\nThis code expires in 5 minutes.";
// Use a proper mailer in production (PHPMailer, external provider)
mail($email, $subject, $message);
}

function verify_mfa_code($input) {
if (!isset($_SESSION['mfa_code']) || !isset($_SESSION['mfa_expires'])) return false;
if (time() > $_SESSION['mfa_expires']) return false;
return intval($input) === intval($_SESSION['mfa_code']);
}

?>