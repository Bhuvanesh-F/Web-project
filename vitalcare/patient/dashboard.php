<?php
// patient/dashboard.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requirePatient();

$pid  = $_SESSION['patient_id'];
$name = h($_SESSION['patient_name'] ?? 'Patient');

// ── Fetch upcoming appointments ───────────────────────────────
$appts = [];
$stmt  = $conn->prepare(
    'SELECT a.appointment_id, a.speciality, a.appointment_date, a.preferred_time,
            a.status, d.full_name AS doctor_name
     FROM   human_appointments a
     LEFT JOIN human_doctors d ON a.doctor_id = d.doctor_id
     WHERE  a.patient_id = ?
     ORDER  BY a.appointment_date DESC
     LIMIT  5'
);
$stmt->bind_param('s', $pid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $appts[] = $row;
$stmt->close();

// ── Recent medical records ────────────────────────────────────
$records = [];
$stmt = $conn->prepare(
    'SELECT r.record_date, r.diagnosis, d.full_name AS doctor_name
     FROM   human_medical_records r
     LEFT JOIN human_doctors d ON r.doctor_id = d.doctor_id
     WHERE  r.patient_id = ?
     ORDER  BY r.record_date DESC
     LIMIT  3'
);
$stmt->bind_param('s', $pid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $records[] = $row;
$stmt->close();

$pageTitle = 'Dashboard';
// Custom header for patient nav
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – Patient Dashboard</title>
  <link rel="stylesheet" href="/vitalcare/css/style.css">
</head>
<body>
<header>
  <div class="container">
    <nav class="navbar">
      <a href="/vitalcare/index.php" class="logo">VitalCare</a>
      <ul class="nav-links">
        <li><a href="/vitalcare/patient/dashboard.php"    class="active">Dashboard</a></li>
        <li><a href="/vitalcare/patient/appointments.php">Appointments</a></li>
        <li><a href="/vitalcare/patient/medical-records.php">Medical Records</a></li>
        <li><a href="/vitalcare/patient/profile.php">Profile</a></li>
      </ul>
      <div class="user-info">
        <span>Welcome, <?= $name ?></span>
        <a href="/vitalcare/book-appointment.php" class="btn btn-sm">+ Book</a>
        <a href="/vitalcare/logout.php" class="btn btn-sm btn-danger">Log Out</a>
      </div>
    </nav>
  </div>
</header>

<main>
<div class="container page-content">
  <h1>VitalCare</h1>
  <h2>Patient Dashboard</h2>

  <div class="dashboard-grid">

    <!-- Upcoming Appointments -->
    <div class="dashboard-section">
      <h3>📅 Upcoming Appointments</h3>
      <?php if (empty($appts)): ?>
        <p style="color:var(--secondary);font-size:.9rem;">No appointments yet.</p>
        <a href="/vitalcare/book-appointment.php" class="btn btn-sm" style="margin-top:10px;">Book Now</a>
      <?php else: ?>
        <?php foreach ($appts as $a): ?>
          <div class="appt-card">
            <h4><?= h($a['speciality']) ?></h4>
            <p>
              <?= $a['doctor_name'] ? 'Dr. ' . h($a['doctor_name']) : 'Doctor TBC' ?> –
              <?= date('d M Y', strtotime($a['appointment_date'])) ?>
              <?= $a['preferred_time'] ? ' at ' . h($a['preferred_time']) : '' ?>
            </p>
            <span class="badge badge-<?= h($a['status']) ?>"><?= h($a['status']) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Recent Medical Records -->
    <div class="dashboard-section">
      <h3>🗂 Recent Medical Records</h3>
      <?php if (empty($records)): ?>
        <p style="color:var(--secondary);font-size:.9rem;">No records found.</p>
      <?php else: ?>
        <?php foreach ($records as $r): ?>
          <div class="appt-card">
            <h4><?= h($r['diagnosis'] ?: 'Consultation') ?></h4>
            <p>
              <?= $r['doctor_name'] ? 'Dr. ' . h($r['doctor_name']) : 'Clinic' ?> –
              <?= date('d M Y', strtotime($r['record_date'])) ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section">
      <h3>⚡ Quick Actions</h3>
      <div class="quick-actions">
        <div class="action-buttons">
          <a href="/vitalcare/book-appointment.php"       class="btn">📅 Book an Appointment</a>
          <a href="/vitalcare/patient/appointments.php"   class="btn btn-secondary">📋 View All Appointments</a>
          <a href="/vitalcare/patient/medical-records.php"class="btn btn-secondary">🗂 Medical Records</a>
          <a href="/vitalcare/patient/profile.php"        class="btn btn-secondary">👤 My Profile</a>
        </div>
      </div>
    </div>

    <!-- Account Summary -->
    <div class="dashboard-section">
      <h3>👤 Account Summary</h3>
      <p><strong>Name:</strong> <?= $name ?></p>
      <p><strong>Email:</strong> <?= h($_SESSION['patient_email'] ?? '') ?></p>
      <p><strong>Total Appointments:</strong> <?= count($appts) ?></p>
      <p style="margin-top:14px;">
        <a href="/vitalcare/patient/profile.php" class="btn btn-sm btn-secondary">Edit Profile</a>
      </p>
    </div>

  </div>
</div>
</main>

<script src="/vitalcare/js/main.js"></script>
</body>
</html>
