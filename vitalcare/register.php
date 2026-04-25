<?php
// register.php – Patient Registration
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (!empty($_SESSION['patient_id'])) {
    header('Location: /vitalcare/patient/dashboard.php');
    exit;
}

$errors  = [];
$success = '';
$old     = []; // repopulate form fields

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    // ── Collect & sanitise ──────────────────────────────────
    $first_name  = trim($_POST['first_name']  ?? '');
    $last_name   = trim($_POST['last_name']   ?? '');
    $dob         = trim($_POST['dob']          ?? '');
    $nic         = trim($_POST['nic_passport'] ?? '');
    $gender      = trim($_POST['gender']       ?? 'unknown');
    $phone       = trim($_POST['phone']        ?? '');
    $email       = strtolower(trim($_POST['email'] ?? ''));
    $address     = trim($_POST['address']      ?? '');
    $password    = $_POST['password']           ?? '';
    $confirm_pw  = $_POST['confirm_password']  ?? '';

    $old = compact('first_name','last_name','dob','nic','gender','phone','email','address');

    // ── Server-side validation ──────────────────────────────
    if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/u', $first_name) || strlen($first_name) < 2)
        $errors['first_name'] = 'Enter a valid first name (letters only, min 2 chars).';

    if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/u', $last_name) || strlen($last_name) < 2)
        $errors['last_name'] = 'Enter a valid last name.';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Enter a valid email address.';

    if (!preg_match('/^(\+230\s?)?\d{3}\s?\d{4}$/', $phone))
        $errors['phone'] = 'Enter a valid Mauritian phone number (e.g. 5123 4567).';

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password))
        $errors['password'] = 'Password must be 8+ characters with uppercase, lowercase and a number.';

    if ($password !== $confirm_pw)
        $errors['confirm_password'] = 'Passwords do not match.';

    // ── Check email unique ──────────────────────────────────
    if (empty($errors['email'])) {
        $stmt = $conn->prepare('SELECT patient_id FROM human_patients WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0)
            $errors['email'] = 'This email is already registered. Please log in.';
        $stmt->close();
    }

    // ── Insert ──────────────────────────────────────────────
    if (empty($errors)) {
        $patient_id    = uuid4();
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $gender_safe   = in_array($gender, ['male','female','other','unknown']) ? $gender : 'unknown';

        $stmt = $conn->prepare(
            'INSERT INTO human_patients
             (patient_id, first_name, last_name, dob, nic_passport, gender, phone, email, address, password_hash)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $dob_val = $dob ?: null;
        $stmt->bind_param('ssssssssss',
            $patient_id, $first_name, $last_name, $dob_val,
            $nic, $gender_safe, $phone, $email, $address, $password_hash
        );

        if ($stmt->execute()) {
            $success = 'Account created successfully! You can now log in.';
            $old = [];
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}

$pageTitle = 'Register';
$navActive  = '';
include __DIR__ . '/includes/header.php';
?>

<main>
  <div class="container page-content">
    <h1>VitalCare</h1>
    <h2>Create Your Account</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <?= h($success) ?>
        <a href="/vitalcare/login.php"><strong>Click here to log in →</strong></a>
      </div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= h($errors['general']) ?></div>
    <?php endif; ?>

    <div style="max-width:680px; margin:0 auto;">
      <div class="card">
        <form id="registerForm" method="POST" action="/vitalcare/register.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

          <div class="form-row">
            <div class="form-group">
              <label for="first_name">First Name *</label>
              <input type="text" id="first_name" name="first_name"
                     value="<?= h($old['first_name'] ?? '') ?>"
                     class="<?= isset($errors['first_name']) ? 'input-error' : '' ?>" required>
              <span class="form-error <?= isset($errors['first_name']) ? 'show' : '' ?>">
                <?= h($errors['first_name'] ?? '') ?>
              </span>
            </div>
            <div class="form-group">
              <label for="last_name">Last Name *</label>
              <input type="text" id="last_name" name="last_name"
                     value="<?= h($old['last_name'] ?? '') ?>"
                     class="<?= isset($errors['last_name']) ? 'input-error' : '' ?>" required>
              <span class="form-error <?= isset($errors['last_name']) ? 'show' : '' ?>">
                <?= h($errors['last_name'] ?? '') ?>
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="dob">Date of Birth</label>
              <input type="date" id="dob" name="dob" value="<?= h($old['dob'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="nic_passport">NIC / Passport No.</label>
              <input type="text" id="nic_passport" name="nic_passport"
                     value="<?= h($old['nic'] ?? '') ?>" placeholder="e.g. A12345678">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="gender">Gender</label>
              <select id="gender" name="gender">
                <option value="unknown" <?= ($old['gender'] ?? '') === 'unknown' ? 'selected' : '' ?>>Prefer not to say</option>
                <option value="male"    <?= ($old['gender'] ?? '') === 'male'    ? 'selected' : '' ?>>Male</option>
                <option value="female"  <?= ($old['gender'] ?? '') === 'female'  ? 'selected' : '' ?>>Female</option>
                <option value="other"   <?= ($old['gender'] ?? '') === 'other'   ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="phone">Phone Number *</label>
              <input type="tel" id="phone" name="phone"
                     value="<?= h($old['phone'] ?? '') ?>"
                     placeholder="e.g. 5123 4567"
                     class="<?= isset($errors['phone']) ? 'input-error' : '' ?>" required>
              <span class="form-error <?= isset($errors['phone']) ? 'show' : '' ?>">
                <?= h($errors['phone'] ?? '') ?>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email"
                   value="<?= h($old['email'] ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>" required>
            <span class="form-error <?= isset($errors['email']) ? 'show' : '' ?>">
              <?= h($errors['email'] ?? '') ?>
            </span>
          </div>

          <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="2"><?= h($old['address'] ?? '') ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="reg_password">Password *</label>
              <input type="password" id="reg_password" name="password"
                     class="<?= isset($errors['password']) ? 'input-error' : '' ?>" required>
              <small class="hint">Min 8 chars, uppercase, lowercase &amp; number</small>
              <!-- Strength bar -->
              <div style="height:5px;background:#e9ecef;border-radius:3px;margin-top:6px;">
                <div id="pw-strength-bar" style="height:100%;width:0;border-radius:3px;transition:width .3s,background .3s;"></div>
              </div>
              <span class="form-error <?= isset($errors['password']) ? 'show' : '' ?>">
                <?= h($errors['password'] ?? '') ?>
              </span>
            </div>
            <div class="form-group">
              <label for="reg_confirm_password">Confirm Password *</label>
              <input type="password" id="reg_confirm_password" name="confirm_password"
                     class="<?= isset($errors['confirm_password']) ? 'input-error' : '' ?>" required>
              <span class="form-error <?= isset($errors['confirm_password']) ? 'show' : '' ?>">
                <?= h($errors['confirm_password'] ?? '') ?>
              </span>
            </div>
          </div>

          <button type="submit" class="btn btn-block" onclick="validateRegisterForm(event)">
            Create Account
          </button>
          <p style="text-align:center;margin-top:16px;font-size:.9rem;">
            Already have an account? <a href="/vitalcare/login.php">Log in here</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
