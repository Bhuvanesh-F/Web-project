<?php
// login.php – Patient Login
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['patient_id'])) {
    header('Location: /vitalcare/patient/dashboard.php');
    exit;
}

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        // ── Fetch user (prepared statement – SQL injection safe) ──
        $stmt = $conn->prepare(
            'SELECT patient_id, first_name, last_name, password_hash
             FROM human_patients WHERE email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['patient_id']   = $row['patient_id'];
                $_SESSION['patient_name'] = $row['first_name'] . ' ' . $row['last_name'];
                $_SESSION['patient_email']= $email;

                header('Location: /vitalcare/patient/dashboard.php');
                exit;
            }
        }
        // Generic error – do NOT reveal whether email exists
        $error = 'Invalid email or password. Please try again.';
        $stmt->close();
    }
}

$pageTitle = 'Login';
$navActive  = '';
include __DIR__ . '/includes/header.php';
?>

<main>
  <div class="container auth-wrapper">
    <div class="auth-box">
      <h2>VitalCare</h2>
      <p>Patient Portal – please log in to your account</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>

      <form id="loginForm" method="POST" action="/vitalcare/login.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label for="login_email">Email Address</label>
          <input type="email" id="login_email" name="email"
                 value="<?= h($old_email) ?>"
                 placeholder="you@example.com" required autofocus>
          <span class="form-error"></span>
        </div>

        <div class="form-group">
          <label for="login_password">Password</label>
          <input type="password" id="login_password" name="password"
                 placeholder="••••••••" required>
          <span class="form-error"></span>
        </div>

        <button type="submit" class="btn btn-block" onclick="validateLoginForm(event)">
          Log In
        </button>

        <div class="auth-links">
          <a href="#">Forgot password?</a>
          <a href="/vitalcare/register.php">Create an account</a>
        </div>
      </form>

      <hr style="margin:24px 0; border-color:#e9ecef;">
      <p style="text-align:center;font-size:.85rem;color:var(--secondary);">
        Are you staff?
        <a href="/vitalcare/admin-login.php">Admin login</a>
      </p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
