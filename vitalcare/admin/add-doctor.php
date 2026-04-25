<?php
// admin/add-doctor.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$errors  = [];
$success = '';
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name       = trim($_POST['staff_name']       ?? '');
    $email      = strtolower(trim($_POST['staff_email'] ?? ''));
    $speciality = trim($_POST['speciality']        ?? '');
    $education  = trim($_POST['education']         ?? '');
    $experience = (int)($_POST['experience']       ?? 0);
    $fees       = (float)($_POST['fees']           ?? 0);
    $about      = trim($_POST['about']             ?? '');
    $password   = $_POST['staff_pass']             ?? '';

    $old = compact('name','email','speciality','education','experience','fees','about');

    // Validate
    if (!preg_match('/^[A-Za-zÀ-ÿ\s\'.,-]+$/u', $name) || strlen($name) < 3)
        $errors['staff_name'] = 'Enter a valid full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['staff_email'] = 'Enter a valid email address.';
    if ($speciality === '')
        $errors['speciality'] = 'Speciality is required.';
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password))
        $errors['staff_pass'] = 'Password: 8+ chars, uppercase, lowercase, number.';

    // Email unique
    if (empty($errors['staff_email'])) {
        $stmt = $conn->prepare('SELECT doctor_id FROM human_doctors WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0)
            $errors['staff_email'] = 'This email is already registered.';
        $stmt->close();
    }

    if (empty($errors)) {
        $did  = uuid4();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare(
            'INSERT INTO human_doctors
             (doctor_id,full_name,speciality,email,education,password_hash,experience,fees,about)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->bind_param('ssssssiDs',
            $did, $name, $speciality, $email, $education, $hash, $experience, $fees, $about
        );
        if ($stmt->execute()) {
            $success = "Dr. " . h($name) . " added successfully!";
            $old = [];
        } else {
            $errors['general'] = 'Database error. Please try again.';
        }
        $stmt->close();
    }
}

$specialities = ['General Medicine','Cardiology','Dermatology','Pediatrics',
                 'Orthopedics','Neurology','Gynecology','Psychiatry'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – Add Doctor</title>
  <link rel="stylesheet" href="/vitalcare/css/style.css">
</head>
<body>
<header>
  <div class="container">
    <nav class="navbar admin-nav">
      <a href="/vitalcare/index.php" class="logo">VitalCare</a>
      <ul class="nav-links">
        <li><a href="/vitalcare/admin/dashboard.php">Dashboard</a></li>
        <li><a href="/vitalcare/admin/appointments.php">Appointments</a></li>
        <li><a href="/vitalcare/admin/staff.php">Manage Staff</a></li>
        <li><a href="/vitalcare/admin/add-doctor.php" class="active">Add Doctor</a></li>
        <li><a href="/vitalcare/admin/add-nurse.php">Add Nurse</a></li>
      </ul>
      <div class="user-info">
        <span>Admin: <?= h($_SESSION['admin_name'] ?? '') ?></span>
        <a href="/vitalcare/admin-logout.php" class="btn btn-sm btn-danger">Log Out</a>
      </div>
    </nav>
  </div>
</header>
<main>
<div class="container page-content">
  <h1>VitalCare</h1>
  <h2>Add New Doctor</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>
  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= h($errors['general']) ?></div>
  <?php endif; ?>

  <div style="max-width:700px;">
  <div class="card">
    <form id="staffForm" method="POST" action="/vitalcare/admin/add-doctor.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-row">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" id="staff_name" name="staff_name"
                 value="<?= h($old['name'] ?? '') ?>"
                 placeholder="Dr. Firstname Lastname"
                 class="<?= isset($errors['staff_name']) ? 'input-error' : '' ?>">
          <span class="form-error <?= isset($errors['staff_name']) ? 'show' : '' ?>">
            <?= h($errors['staff_name'] ?? '') ?>
          </span>
        </div>
        <div class="form-group">
          <label>Speciality *</label>
          <select id="speciality" name="speciality"
                  class="<?= isset($errors['speciality']) ? 'input-error' : '' ?>">
            <option value="">— Select —</option>
            <?php foreach ($specialities as $sp): ?>
              <option value="<?= h($sp) ?>" <?= ($old['speciality'] ?? '') === $sp ? 'selected' : '' ?>>
                <?= h($sp) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <span class="form-error <?= isset($errors['speciality']) ? 'show' : '' ?>">
            <?= h($errors['speciality'] ?? '') ?>
          </span>
        </div>
      </div>

      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" id="staff_email" name="staff_email"
               value="<?= h($old['email'] ?? '') ?>"
               class="<?= isset($errors['staff_email']) ? 'input-error' : '' ?>">
        <span class="form-error <?= isset($errors['staff_email']) ? 'show' : '' ?>">
          <?= h($errors['staff_email'] ?? '') ?>
        </span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Years of Experience</label>
          <input type="number" name="experience" min="0" max="60"
                 value="<?= (int)($old['experience'] ?? 0) ?>">
        </div>
        <div class="form-group">
          <label>Consultation Fee (Rs)</label>
          <input type="number" name="fees" min="0" step="0.01"
                 value="<?= number_format((float)($old['fees'] ?? 0), 2, '.', '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Education / Qualifications</label>
        <input type="text" name="education" value="<?= h($old['education'] ?? '') ?>"
               placeholder="e.g. MBBS, MD Cardiology">
      </div>

      <div class="form-group">
        <label>About</label>
        <textarea name="about" rows="3"><?= h($old['about'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Login Password *</label>
        <input type="password" id="staff_pass" name="staff_pass"
               class="<?= isset($errors['staff_pass']) ? 'input-error' : '' ?>">
        <small class="hint">Min 8 chars, uppercase, lowercase &amp; number</small>
        <div style="height:5px;background:#e9ecef;border-radius:3px;margin-top:6px;">
          <div id="staff-pw-bar" style="height:100%;width:0;border-radius:3px;transition:width .3s,background .3s;"></div>
        </div>
        <span class="form-error <?= isset($errors['staff_pass']) ? 'show' : '' ?>">
          <?= h($errors['staff_pass'] ?? '') ?>
        </span>
      </div>

      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn" onclick="validateStaffForm(event)">Add Doctor</button>
        <a href="/vitalcare/admin/staff.php" class="btn btn-secondary">View All Staff</a>
      </div>
    </form>
  </div>
  </div>
</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
