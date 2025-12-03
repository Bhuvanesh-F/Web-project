<?php
// clinic/appointments/appointment_add.php
include __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type']; // 'human' or 'pet'
    $id = make_id();
    $speciality = $_POST['speciality'] ?: null;
    $date = $_POST['appointment_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $notes = $_POST['notes'] ?: null;
    $status = 'pending';

    if ($type === 'human') {
        $patient_id = $_POST['human_patient_id'];
        $doctor_id  = $_POST['human_doctor_id'] ?: null;
        $stmt = $conn->prepare("INSERT INTO human_appointments (appointment_id, patient_id, doctor_id, speciality, appointment_date, start_time, end_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssss', $id, $patient_id, $doctor_id, $speciality, $date, $start, $end, $notes, $status);
    } else {
        $pet_id = $_POST['pet_id'];
        $doctor_id  = $_POST['pet_doctor_id'] ?: null;
        $stmt = $conn->prepare("INSERT INTO pet_appointments (appointment_id, pet_id, doctor_id, speciality, appointment_date, start_time, end_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssss', $id, $pet_id, $doctor_id, $speciality, $date, $start, $end, $notes, $status);
    }

    if ($stmt->execute()) {
        echo "Appointment added successfully. <a href='appointment_add.php'>Add another</a>";
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch lists for dropdowns
$humanPatients = $conn->query("SELECT patient_id, first_name, last_name FROM human_patients");
$humanDoctors  = $conn->query("SELECT doctor_id, full_name FROM human_doctors");
$pets          = $conn->query("SELECT pet_id, pet_name FROM pets");
$petDoctors    = $conn->query("SELECT doctor_id, full_name FROM pet_doctors");
?>

<form method="POST">
  <label>Type:
    <select name="type" id="type" onchange="toggleType()">
      <option value="human">Human</option>
      <option value="pet">Pet</option>
    </select>
  </label><br>

  <div id="human-block">
    <label>Human patient:
      <select name="human_patient_id">
        <?php while($r = $humanPatients->fetch_assoc()): ?>
          <option value="<?= $r['patient_id'] ?>"><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </label><br>
    <label>Doctor (human):
      <select name="human_doctor_id">
        <option value="">--none--</option>
        <?php while($r = $humanDoctors->fetch_assoc()): ?>
          <option value="<?= $r['doctor_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </label><br>
  </div>

  <div id="pet-block" style="display:none">
    <label>Pet:
      <select name="pet_id">
        <?php while($r = $pets->fetch_assoc()): ?>
          <option value="<?= $r['pet_id'] ?>"><?= htmlspecialchars($r['pet_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </label><br>
    <label>Doctor (pet):
      <select name="pet_doctor_id">
        <option value="">--none--</option>
        <?php while($r = $petDoctors->fetch_assoc()): ?>
          <option value="<?= $r['doctor_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </label><br>
  </div>

  <label>Speciality: <input name="speciality"></label><br>
  <label>Date: <input type="date" name="appointment_date" required></label><br>
  <label>Start (YYYY-MM-DD HH:MM:SS): <input name="start_time" required value="<?= date('Y-m-d H:i:s') ?>"></label><br>
  <label>End (YYYY-MM-DD HH:MM:SS): <input name="end_time" required value="<?= date('Y-m-d H:i:s', time()+3600) ?>"></label><br>
  <label>Notes: <textarea name="notes"></textarea></label><br>
  <button type="submit">Save Appointment</button>
</form>

<script>
function toggleType(){
  var t = document.getElementById('type').value;
  document.getElementById('human-block').style.display = (t==='human') ? 'block' : 'none';
  document.getElementById('pet-block').style.display = (t==='pet') ? 'block' : 'none';
}
</script>

