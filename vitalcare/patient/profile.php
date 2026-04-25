<?php
// patient/profile.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requirePatient();

$pid    = $_SESSION['patient_id'];
$errors = [];
$success = '';

// Fetch current data
$stmt = $conn->prepare('SELECT * FROM human_patients WHERE patient_id = ?');
$stmt->bind_param('s', $pid);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name']  ?? '');
    $phone  = trim($_POST['phone']      ?? '');
    $addr   = trim($_POST['address']    ?? '');

    if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/u', $first) || strlen($first) < 2)
        $errors['first_name'] = 'Enter a valid first name.';
    if (!preg_match('/^[A-Za-zÀ-ÿ\s\'-]+$/u', $last)  || strlen($last)  < 2)
        $errors['last_name']  = 'Enter a valid last name.';
    if (!preg_match('/^(\+230\s?)?\d{3}\s?\d{4}$/', $phone))
        $errors['phone'] = 'Enter a valid Mauritian phone number.';

    if (empty($errors)) {
        $stmt = $conn->prepare(
            'UPDATE human_patients SET first_name=?, last_name=?, phone=?, address=? WHERE patient_id=?'
        );
        $stmt->bind_param('sssss', $first, $last, $phone, $addr, $pid);
        if ($stmt->execute()) {
            $_SESSION['patient_name'] = $first . ' ' . $last;
            $success = 'Profile updated successfully.';
            $patient['first_name'] = $first;
            $patient['last_name']  = $last;
            $patient['phone']      = $phone;
            $patient['address']    = $addr;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – My Profile</title>
  <link rel="stylesheet" href="/vitalcare/css/style.css">
</head>
<body>
<header>
  <div class="container">
    <nav class="navbar">
      <a href="/vitalcare/index.php" class="logo">VitalCare</a>
      <ul class="nav-links">
        <li><a href="/vitalcare/patient/dashboard.php">Dashboard</a></li>
        <li><a href="/vitalcare/patient/appointments.php">Appointments</a></li>
        <li><a href="/vitalcare/patient/medical-records.php">Medical Records</a></li>
        <li><a href="/vitalcare/patient/profile.php" class="active">Profile</a></li>
      </ul>
      <div class="user-info">
        <span><?= h($_SESSION['patient_name'] ?? '') ?></span>
        <a href="/vitalcare/logout.php" class="btn btn-sm btn-danger">Log Out</a>
      </div>
    </nav>
  </div>
</header>
<main>
<div class="container page-content">
  <h1>VitalCare</h1>
  <h2>My Profile</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= h($success) ?></div>
  <?php endif; ?>

  <div style="max-width:600px;">
  <div class="card">
    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-row">
        <div class="form-group">
          <label>First Name *</label>
          <input type="text" name="first_name"
                 value="<?= h($patient['first_name']) ?>"
                 class="<?= isset($errors['first_name']) ? 'input-error' : '' ?>">
          <span class="form-error show"><?= h($errors['first_name'] ?? '') ?></span>
        </div>
        <div class="form-group">
          <label>Last Name *</label>
          <input type="text" name="last_name"
                 value="<?= h($patient['last_name']) ?>"
                 class="<?= isset($errors['last_name']) ? 'input-error' : '' ?>">
          <span class="form-error show"><?= h($errors['last_name'] ?? '') ?></span>
        </div>
      </div>

      <div class="form-group">
        <label>Email (read-only)</label>
        <input type="email" value="<?= h($patient['email']) ?>" readonly style="background:#f1f3f5;">
      </div>

      <div class="form-group">
        <label>Phone Number *</label>
        <input type="tel" name="phone"
               value="<?= h($patient['phone'] ?? '') ?>"
               class="<?= isset($errors['phone']) ? 'input-error' : '' ?>">
        <span class="form-error show"><?= h($errors['phone'] ?? '') ?></span>
      </div>

      <div class="form-group">
        <label>Address</label>
        <textarea name="address" rows="3"><?= h($patient['address'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Date of Birth</label>
        <input type="date" value="<?= h($patient['dob'] ?? '') ?>" readonly style="background:#f1f3f5;">
      </div>

      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>
  </div>
</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
