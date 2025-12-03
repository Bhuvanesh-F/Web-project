<?php
include __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type']; // human or pet
    $id = make_id();
    $doctor_id = $_POST['doctor_id'] ?: null;
    $patient_or_pet = $_POST['subject_id'];
    $diagnosis = $_POST['diagnosis'] ?: null;
    $treatment = $_POST['treatment'] ?: null;
    $prescription = $_POST['prescription'] ?: null;
    $notes = $_POST['notes'] ?: null;

    if ($type === 'human') {
        $stmt = $conn->prepare("INSERT INTO human_medical_records (record_id, doctor_id, patient_id, diagnosis, treatment, prescription, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $id, $doctor_id, $patient_or_pet, $diagnosis, $treatment, $prescription, $notes);
    } else {
        $stmt = $conn->prepare("INSERT INTO pet_medical_records (record_id, doctor_id, pet_id, diagnosis, treatment, prescription, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $id, $doctor_id, $patient_or_pet, $diagnosis, $treatment, $prescription, $notes);
    }
    if ($stmt->execute()) echo "Record saved.";
    else echo $stmt->error;
}

// simple dropdowns
$humanPatients = $conn->query("SELECT patient_id, first_name, last_name FROM human_patients");
$humanDoctors = $conn->query("SELECT doctor_id, full_name FROM human_doctors");
$pets = $conn->query("SELECT pet_id, pet_name FROM pets");
$petDoctors = $conn->query("SELECT doctor_id, full_name FROM pet_doctors");
?>
<form method="POST">
  Type:
  <select name="type" onchange="toggle()">
    <option value="human">Human</option>
    <option value="pet">Pet</option>
  </select><br>

  <div id="human">
    Patient:
    <select name="subject_id">
      <?php while($r=$humanPatients->fetch_assoc()): ?>
        <option value="<?= $r['patient_id'] ?>"><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></option>
      <?php endwhile; ?>
    </select><br>
    Doctor:
    <select name="doctor_id">
      <option value="">--none--</option>
      <?php while($r=$humanDoctors->fetch_assoc()): ?>
        <option value="<?= $r['doctor_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
      <?php endwhile; ?>
    </select><br>
  </div>

  <div id="pet" style="display:none">
    Pet:
    <select name="subject_id">
      <?php while($r=$pets->fetch_assoc()): ?>
        <option value="<?= $r['pet_id'] ?>"><?= htmlspecialchars($r['pet_name']) ?></option>
      <?php endwhile; ?>
    </select><br>
    Doctor:
    <select name="doctor_id">
      <option value="">--none--</option>
      <?php while($r=$petDoctors->fetch_assoc()): ?>
        <option value="<?= $r['doctor_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
      <?php endwhile; ?>
    </select><br>
  </div>

  Diagnosis:<br><textarea name="diagnosis"></textarea><br>
  Treatment:<br><textarea name="treatment"></textarea><br>
  Prescription:<br><textarea name="prescription"></textarea><br>
  Notes:<br><textarea name="notes"></textarea><br>
  <button type="submit">Save Record</button>
</form>

<script>
function toggle(){
  var t = document.querySelector('select[name="type"]').value;
  document.getElementById('human').style.display = (t==='human') ? 'block' : 'none';
  document.getElementById('pet').style.display = (t==='pet') ? 'block' : 'none';
}
</script>
