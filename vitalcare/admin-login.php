<?php
// admin-login.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: /vitalcare/admin/dashboard.php');
    exit;
}

$error = '';
$old_user = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $old_user  = h($username);

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = $conn->prepare(
            'SELECT admin_id, username, password_hash FROM human_admins WHERE username = ? LIMIT 1'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $row['admin_id'];
            $_SESSION['admin_name'] = $row['username'];
            header('Location: /vitalcare/admin/dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

$pageTitle = 'Admin Login';
include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container auth-wrapper">
    <div class="auth-box">
      <h2>🔒 Admin Portal</h2>
      <p>VitalCare Staff Access</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/vitalcare/admin-login.php">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username"
                 value="<?= $old_user ?>" autofocus required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-block">Log In</button>
      </form>

      <p style="text-align:center;margin-top:16px;font-size:.85rem;">
        <a href="/vitalcare/login.php">← Patient portal</a>
      </p>
    </div>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
