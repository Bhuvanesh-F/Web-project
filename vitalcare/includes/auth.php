<?php
// ============================================================
// VitalCare – Session & Auth helpers
// includes/auth.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
    ]);
}

/**
 * Require a logged-in patient; redirect to login if not.
 */
function requirePatient(): void {
    if (empty($_SESSION['patient_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Require a logged-in admin; redirect if not.
 */
function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ../admin-login.php');
        exit;
    }
}

/**
 * Generate & store a CSRF token in the session.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token from POST data.
 */
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF validation failed.');
    }
}
