<?php
// admin/staff.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Remove staff
if (isset($_GET['remove_doctor'])) {
    $id   = trim($_GET['remove_doctor']);
    $stmt = $conn->prepare('DELETE FROM human_doctors WHERE doctor_id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: /vitalcare/admin/staff.php?msg=removed');
    exit;
}
if (isset($_GET['remove_nurse'])) {
    $id   = trim($_GET['remove_nurse']);
    $stmt = $conn->prepare('DELETE FROM human_nurses WHERE nurse_id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: /vitalcare/admin/staff.php?msg=removed');
    exit;
}

// Fetch
$doctors = [];
$res = $conn->query('SELECT doctor_id,full_name,speciality,email,experience,fees FROM human_doctors ORDER BY full_name');
while ($row = $res->fetch_assoc()) $doctors[] = $row;

$nurses = [];
$res = $conn->query('SELECT nurse_id,full_name,speciality,email,experience FROM human_nurses ORDER BY full_name');
while ($row = $res->fetch_assoc()) $nurses[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – Manage Staff</title>
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
        <li><a href="/vitalcare/admin/staff.php" class="active">Manage Staff</a></li>
        <li><a href="/vitalcare/admin/add-doctor.php">Add Doctor</a></li>
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
  <h2>Manage Staff</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Staff record removed successfully.</div>
  <?php endif; ?>

  <div class="staff-management">
    <div class="staff-sections">

      <!-- Doctors -->
      <div class="staff-category">
        <h2>🩺 Doctors (<?= count($doctors) ?>)</h2>
        <a href="/vitalcare/admin/add-doctor.php" class="btn btn-sm" style="margin-bottom:14px;">+ Add Doctor</a>
        <?php if (empty($doctors)): ?>
          <p style="color:var(--secondary);">No doctors registered yet.</p>
        <?php else: ?>
          <div class="staff-list">
            <?php foreach ($doctors as $d): ?>
            <div class="staff-member">
              <div>
                <strong><?= h($d['full_name']) ?></strong><br>
                <small><?= h($d['speciality']) ?> &bull; <?= h($d['email']) ?></small><br>
                <small><?= $d['experience'] ? $d['experience'] . ' yrs exp' : '' ?>
                       <?= $d['fees'] ? ' &bull; Rs ' . number_format($d['fees'], 0) : '' ?></small>
              </div>
              <div class="staff-actions">
                <a href="?remove_doctor=<?= h($d['doctor_id']) ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Remove this doctor? This cannot be undone.')">
                  Remove
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Nurses -->
      <div class="staff-category">
        <h2>💉 Nurses (<?= count($nurses) ?>)</h2>
        <a href="/vitalcare/admin/add-nurse.php" class="btn btn-sm" style="margin-bottom:14px;">+ Add Nurse</a>
        <?php if (empty($nurses)): ?>
          <p style="color:var(--secondary);">No nurses registered yet.</p>
        <?php else: ?>
          <div class="staff-list">
            <?php foreach ($nurses as $n): ?>
            <div class="staff-member">
              <div>
                <strong><?= h($n['full_name']) ?></strong><br>
                <small><?= h($n['speciality'] ?: 'General Care') ?> &bull; <?= h($n['email']) ?></small><br>
                <small><?= $n['experience'] ? $n['experience'] . ' yrs exp' : '' ?></small>
              </div>
              <div class="staff-actions">
                <a href="?remove_nurse=<?= h($n['nurse_id']) ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Remove this nurse?')">
                  Remove
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
