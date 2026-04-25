<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Stats
$stats = [];
$stats['patients']     = $conn->query('SELECT COUNT(*) c FROM human_patients')->fetch_assoc()['c'];
$stats['doctors']      = $conn->query('SELECT COUNT(*) c FROM human_doctors')->fetch_assoc()['c'];
$stats['nurses']       = $conn->query('SELECT COUNT(*) c FROM human_nurses')->fetch_assoc()['c'];
$stats['appointments'] = $conn->query('SELECT COUNT(*) c FROM human_appointments WHERE status="pending"')->fetch_assoc()['c'];

// Recent appointments
$recent = [];
$res = $conn->query(
    'SELECT a.appointment_id, a.appointment_date, a.preferred_time, a.speciality, a.status,
            CONCAT(p.first_name," ",p.last_name) AS patient_name,
            d.full_name AS doctor_name
     FROM   human_appointments a
     JOIN   human_patients p ON a.patient_id = p.patient_id
     LEFT JOIN human_doctors d ON a.doctor_id = d.doctor_id
     ORDER  BY a.created_at DESC LIMIT 8'
);
while ($row = $res->fetch_assoc()) $recent[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – Admin Dashboard</title>
  <link rel="stylesheet" href="/vitalcare/css/style.css">
</head>
<body>
<header>
  <div class="container">
    <nav class="navbar admin-nav">
      <a href="/vitalcare/index.php" class="logo">VitalCare</a>
      <ul class="nav-links">
        <li><a href="/vitalcare/admin/dashboard.php"    class="active">Dashboard</a></li>
        <li><a href="/vitalcare/admin/appointments.php">Appointments</a></li>
        <li><a href="/vitalcare/admin/staff.php">Manage Staff</a></li>
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
  <h2>Admin Dashboard</h2>

  <!-- Stat Cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:32px;">
    <?php
    $statCards = [
        ['icon'=>'👥', 'label'=>'Patients',      'val'=>$stats['patients']],
        ['icon'=>'🩺', 'label'=>'Doctors',       'val'=>$stats['doctors']],
        ['icon'=>'💉', 'label'=>'Nurses',        'val'=>$stats['nurses']],
        ['icon'=>'📅', 'label'=>'Pending Appts', 'val'=>$stats['appointments']],
    ];
    foreach ($statCards as $sc):
    ?>
    <div class="card" style="text-align:center;padding:24px;">
      <div style="font-size:2rem;"><?= $sc['icon'] ?></div>
      <div style="font-size:2rem;font-weight:800;color:var(--primary);margin:8px 0;"><?= $sc['val'] ?></div>
      <div style="color:var(--secondary);font-size:.9rem;"><?= $sc['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick links -->
  <div class="card" style="margin-bottom:28px;">
    <h3 style="margin-bottom:14px;">⚡ Quick Actions</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="/vitalcare/admin/add-doctor.php" class="btn">+ Add Doctor</a>
      <a href="/vitalcare/admin/add-nurse.php"  class="btn btn-secondary">+ Add Nurse</a>
      <a href="/vitalcare/admin/appointments.php" class="btn btn-secondary">View All Appointments</a>
      <a href="/vitalcare/admin/staff.php"      class="btn btn-secondary">Manage Staff</a>
    </div>
  </div>

  <!-- Recent Appointments -->
  <div class="card">
    <h3 style="margin-bottom:16px;">📋 Recent Appointments</h3>
    <?php if (empty($recent)): ?>
      <p style="color:var(--secondary);">No appointments recorded yet.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Patient</th><th>Date</th><th>Time</th><th>Speciality</th><th>Doctor</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $r): ?>
          <tr>
            <td><?= h($r['patient_name']) ?></td>
            <td><?= date('d M Y', strtotime($r['appointment_date'])) ?></td>
            <td><?= h($r['preferred_time'] ?? '—') ?></td>
            <td><?= h($r['speciality']) ?></td>
            <td><?= $r['doctor_name'] ? h($r['doctor_name']) : '—' ?></td>
            <td><span class="badge badge-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
            <td>
              <a href="/vitalcare/admin/appointments.php?update=<?= h($r['appointment_id']) ?>&status=approved"
                 class="btn btn-sm btn-success"
                 onclick="return confirm('Approve this appointment?')">✓ Approve</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
