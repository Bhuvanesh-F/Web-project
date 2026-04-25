<?php
// patient/medical-records.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requirePatient();

$pid = $_SESSION['patient_id'];

$records = [];
$stmt = $conn->prepare(
    'SELECT r.record_id, r.record_date, r.diagnosis, r.treatment,
            r.prescription, r.notes, d.full_name AS doctor_name
     FROM   human_medical_records r
     LEFT JOIN human_doctors d ON r.doctor_id = d.doctor_id
     WHERE  r.patient_id = ?
     ORDER  BY r.record_date DESC'
);
$stmt->bind_param('s', $pid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $records[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – Medical Records</title>
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
        <li><a href="/vitalcare/patient/medical-records.php" class="active">Medical Records</a></li>
        <li><a href="/vitalcare/patient/profile.php">Profile</a></li>
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
  <h2>My Medical Records</h2>

  <?php if (empty($records)): ?>
    <div class="card" style="text-align:center;padding:40px;">
      <p>No medical records found.</p>
    </div>
  <?php else: ?>
    <?php foreach ($records as $r): ?>
    <div class="card" style="margin-bottom:20px;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
        <div>
          <h3 style="color:var(--primary);"><?= h($r['diagnosis'] ?: 'Consultation') ?></h3>
          <p style="color:var(--secondary);font-size:.9rem;">
            <?= $r['doctor_name'] ? 'Dr. ' . h($r['doctor_name']) : 'Clinic' ?> &bull;
            <?= date('d M Y', strtotime($r['record_date'])) ?>
          </p>
        </div>
      </div>
      <hr style="margin:14px 0;border-color:#e9ecef;">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
        <?php if ($r['diagnosis']): ?>
        <div>
          <strong style="font-size:.85rem;text-transform:uppercase;color:var(--secondary);">Diagnosis</strong>
          <p><?= h($r['diagnosis']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($r['treatment']): ?>
        <div>
          <strong style="font-size:.85rem;text-transform:uppercase;color:var(--secondary);">Treatment</strong>
          <p><?= h($r['treatment']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($r['prescription']): ?>
        <div>
          <strong style="font-size:.85rem;text-transform:uppercase;color:var(--secondary);">Prescription</strong>
          <p><?= h($r['prescription']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($r['notes']): ?>
        <div>
          <strong style="font-size:.85rem;text-transform:uppercase;color:var(--secondary);">Notes</strong>
          <p><?= h($r['notes']) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
