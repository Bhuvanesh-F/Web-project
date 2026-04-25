<?php
// includes/header.php
// Call:  include __DIR__ . '/includes/header.php';
// Before including, set $pageTitle and optionally $navActive ('home','services','book','contact')
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict']);
}
$pageTitle  = $pageTitle  ?? 'VitalCare';
$navActive  = $navActive  ?? '';
$isLoggedIn = !empty($_SESSION['patient_id']);
$patientName = h($_SESSION['patient_name'] ?? 'Guest');

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitalCare – <?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="/vitalcare/css/style.css">
    <meta name="description" content="VitalCare Clinic – Your Health. Their Health. One Clinic.">
</head>
<body>
<header>
    <div class="container">
        <nav class="navbar">
            <a href="/vitalcare/index.php" class="logo">VitalCare</a>
            <ul class="nav-links">
                <li><a href="/vitalcare/index.php"          class="<?= $navActive==='home'     ? 'active' : '' ?>">Home</a></li>
                <li><a href="/vitalcare/services.php"       class="<?= $navActive==='services' ? 'active' : '' ?>">Services</a></li>
                <li><a href="/vitalcare/book-appointment.php" class="<?= $navActive==='book'   ? 'active' : '' ?>">Book Appointment</a></li>
                <li><a href="/vitalcare/contact.php"        class="<?= $navActive==='contact'  ? 'active' : '' ?>">Contact</a></li>
            </ul>
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <span>Welcome, <?= $patientName ?></span>
                    <a href="/vitalcare/patient/dashboard.php" class="btn btn-sm">Dashboard</a>
                    <a href="/vitalcare/logout.php" class="btn btn-sm btn-danger">Log Out</a>
                <?php else: ?>
                    <span>Welcome, Guest</span>
                    <a href="/vitalcare/login.php" class="btn btn-sm">Log In</a>
                    <a href="/vitalcare/register.php" class="btn btn-sm btn-secondary">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
