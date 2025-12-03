<?php
include __DIR__ . '/../includes/db.php';
$type = $_GET['type'] ?? 'human';
$id = $_GET['appointment_id'] ?? null;
if (!$id) { die("Missing appointment_id"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $speciality = $_POST['speciality'] ?: null;
    $date = $_POST['appointment_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $notes = $_POST['notes'] ?: null;
    $status = $_POST['status'] ?: 'pending';
    if ($type === 'human') {
        $stmt = $conn->prepare("UPDATE human_appointments SET speciality=?, appointment_date=?, start_time=?, end_time=?, notes=?, status=? WHERE appointment_id=?");
    } else {
        $stmt = $conn->prepare("UPDATE pet_appointments SET speciality=?, appointment_date=?, start_time=?, end_time=?, notes=?, status=? WHERE appointment_id=?");
    }
    $stmt->bind_param('sssssss', $speciality, $date, $start, $end, $notes, $status, $id);
    if ($stmt->execute()) { echo "Updated."; }
    else { echo $stmt->error; }
}

// load record
if ($type === 'human') {
    $res = $conn->query("SELECT * FROM human_appointments WHERE appointment_id='".$conn->real_escape_string($id)."'");
} else {
    $res = $conn->query("SELECT * FROM pet_appointments WHERE appointment_id='".$conn->real_escape_string($id)."'");
}
$appt = $res->fetch_assoc();
if (!$appt) die("Appointment not found.");
?>
<form method="POST">
  Speciality: <input name="speciality" value="<?= htmlspecialchars($appt['speciality']) ?>"><br>
  Date: <input type="date" name="appointment_date" value="<?= htmlspecialchars($appt['appointment_date']) ?>"><br>
  Start: <input name="start_time" value="<?= htmlspecialchars($appt['start_time']) ?>"><br>
  End: <input name="end_time" value="<?= htmlspecialchars($appt['end_time']) ?>"><br>
  Notes: <textarea name="notes"><?= htmlspecialchars($appt['notes']) ?></textarea><br>
  Status:
  <select name="status">
    <?php foreach(['pending','approved','done','cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= ($appt['status']===$s) ? 'selected' : '' ?>><?= $s ?></option>
    <?php endforeach; ?>
  </select><br>
  <button type="submit">Save</button>
</form>
