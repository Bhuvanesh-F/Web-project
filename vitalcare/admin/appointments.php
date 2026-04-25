<?php
// admin/appointments.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Status update
if (isset($_GET['update'], $_GET['status'])) {
    $apptId    = trim($_GET['update']);
    $newStatus = trim($_GET['status']);
    $allowed   = ['pending','approved','done','cancelled'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $conn->prepare('UPDATE human_appointments SET status=? WHERE appointment_id=?');
        $stmt->bind_param('ss', $newStatus, $apptId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: /vitalcare/admin/appointments.php?msg=updated');
    exit;
}

// Fetch all
$appts = [];
$res = $conn->query(
    'SELECT a.appointment_id, a.appointment_date, a.preferred_time, a.speciality, a.notes, a.status, a.created_at,
            CONCAT(p.first_name," ",p.last_name) AS patient_name, p.email AS patient_email,
            d.full_name AS doctor_name
     FROM   human_appointments a
     JOIN   human_patients p ON a.patient_id = p.patient_id
     LEFT JOIN human_doctors d ON a.doctor_id = d.doctor_id
     ORDER  BY a.appointment_date DESC'
);
while ($row = $res->fetch_assoc()) $appts[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – Manage Appointments</title>
  <link rel="stylesheet" href="/vitalcare/css/style.css">
</head>
<body>
<header>
  <div class="container">
    <nav class="navbar admin-nav">
      <a href="/vitalcare/index.php" class="logo">VitalCare</a>
      <ul class="nav-links">
        <li><a href="/vitalcare/admin/dashboard.php">Dashboard</a></li>
        <li><a href="/vitalcare/admin/appointments.php" class="active">Appointments</a></li>
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
  <h2>All Appointments</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Appointment status updated.</div>
  <?php endif; ?>

  <div class="card table-wrap">
    <table>
      <thead>
        <tr>
          <th>Patient</th><th>Email</th><th>Date</th><th>Time</th>
          <th>Speciality</th><th>Doctor</th><th>Reason</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($appts)): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--secondary);">No appointments yet.</td></tr>
        <?php else: ?>
          <?php foreach ($appts as $a): ?>
          <tr>
            <td><?= h($a['patient_name']) ?></td>
            <td><?= h($a['patient_email']) ?></td>
            <td><?= date('d M Y', strtotime($a['appointment_date'])) ?></td>
            <td><?= h($a['preferred_time'] ?? '—') ?></td>
            <td><?= h($a['speciality']) ?></td>
            <td><?= $a['doctor_name'] ? h($a['doctor_name']) : '—' ?></td>
            <td><?= h(mb_strimwidth($a['notes'] ?? '', 0, 30, '…')) ?></td>
            <td><span class="badge badge-<?= h($a['status']) ?>"><?= h($a['status']) ?></span></td>
            <td style="white-space:nowrap;">
              <?php if ($a['status'] === 'pending'): ?>
                <a href="?update=<?= h($a['appointment_id']) ?>&status=approved"
                   class="btn btn-sm btn-success"
                   onclick="return confirm('Approve?')">✓</a>
                <a href="?update=<?= h($a['appointment_id']) ?>&status=cancelled"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Cancel?')">✗</a>
              <?php elseif ($a['status'] === 'approved'): ?>
                <a href="?update=<?= h($a['appointment_id']) ?>&status=done"
                   class="btn btn-sm"
                   onclick="return confirm('Mark as done?')">✔ Done</a>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
