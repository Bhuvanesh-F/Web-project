<?php
// patient/appointments.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requirePatient();

$pid = $_SESSION['patient_id'];

// Cancel appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    verifyCsrf();
    $cid  = trim($_POST['cancel_id']);
    $stmt = $conn->prepare(
        'UPDATE human_appointments SET status="cancelled"
         WHERE appointment_id=? AND patient_id=? AND status="pending"'
    );
    $stmt->bind_param('ss', $cid, $pid);
    $stmt->execute();
    $stmt->close();
    header('Location: /vitalcare/patient/appointments.php?msg=cancelled');
    exit;
}

// Fetch all appointments
$appts = [];
$stmt  = $conn->prepare(
    'SELECT a.appointment_id, a.speciality, a.appointment_date, a.preferred_time,
            a.notes, a.status, a.created_at,
            d.full_name AS doctor_name
     FROM   human_appointments a
     LEFT JOIN human_doctors d ON a.doctor_id = d.doctor_id
     WHERE  a.patient_id = ?
     ORDER  BY a.appointment_date DESC'
);
$stmt->bind_param('s', $pid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $appts[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VitalCare – My Appointments</title>
  <link rel="stylesheet" href="/vitalcare/css/style.css">
</head>
<body>
<header>
  <div class="container">
    <nav class="navbar">
      <a href="/vitalcare/index.php" class="logo">VitalCare</a>
      <ul class="nav-links">
        <li><a href="/vitalcare/patient/dashboard.php">Dashboard</a></li>
        <li><a href="/vitalcare/patient/appointments.php" class="active">Appointments</a></li>
        <li><a href="/vitalcare/patient/medical-records.php">Medical Records</a></li>
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
  <h2>My Appointments</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
    <div class="alert alert-info">Appointment cancelled successfully.</div>
  <?php endif; ?>

  <div style="margin-bottom:20px;">
    <a href="/vitalcare/book-appointment.php" class="btn">+ New Appointment</a>
  </div>

  <?php if (empty($appts)): ?>
    <div class="card" style="text-align:center;padding:40px;">
      <p>You have no appointments yet.</p>
      <a href="/vitalcare/book-appointment.php" class="btn" style="margin-top:14px;">Book Your First Appointment</a>
    </div>
  <?php else: ?>
    <div class="card table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th><th>Time</th><th>Speciality</th>
            <th>Doctor</th><th>Reason</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appts as $a): ?>
          <tr>
            <td><?= date('d M Y', strtotime($a['appointment_date'])) ?></td>
            <td><?= h($a['preferred_time'] ?? 'TBC') ?></td>
            <td><?= h($a['speciality']) ?></td>
            <td><?= $a['doctor_name'] ? h($a['doctor_name']) : '—' ?></td>
            <td><?= h(mb_strimwidth($a['notes'] ?? '', 0, 40, '…')) ?></td>
            <td><span class="badge badge-<?= h($a['status']) ?>"><?= h($a['status']) ?></span></td>
            <td>
              <?php if ($a['status'] === 'pending'): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="cancel_id"  value="<?= h($a['appointment_id']) ?>">
                  <button type="submit" class="btn btn-sm btn-danger"
                          data-confirm="Cancel this appointment?"
                          onclick="return confirm('Cancel this appointment?')">
                    Cancel
                  </button>
                </form>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</main>
<script src="/vitalcare/js/main.js"></script>
</body></html>
