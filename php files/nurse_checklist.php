<?php
include __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = make_id();
    $nurse_id = $_POST['nurse_id'];
    $role = $_POST['role'];
    $patient_id = $_POST['patient_id'] ?: null;
    $pet_id = $_POST['pet_id'] ?: null;
    $task = $_POST['task_description'] ?: null;
    $stmt = $conn->prepare("INSERT INTO nurse_checklist (checklist_id, nurse_id, role, patient_id, pet_id, task_description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $id, $nurse_id, $role, $patient_id, $pet_id, $task);
    if ($stmt->execute()) echo "Task saved.<br>";
    else echo $stmt->error;
}

$nurses = $conn->query("SELECT nurse_id, full_name FROM human_nurses");
$patients = $conn->query("SELECT patient_id, first_name, last_name FROM human_patients");
$pets = $conn->query("SELECT pet_id, pet_name FROM pets");

// List current tasks
$tasks = $conn->query("SELECT * FROM nurse_checklist ORDER BY created_at DESC");
?>
<form method="POST">
  Nurse:
  <select name="nurse_id">
    <?php while($r=$nurses->fetch_assoc()): ?>
      <option value="<?= $r['nurse_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
    <?php endwhile; ?>
  </select><br>
  Role:
  <select name="role">
    <option value="human_patient">human_patient</option>
    <option value="pet_owner">pet_owner</option>
    <option value="human_doctor">human_doctor</option>
    <option value="pet_doctor">pet_doctor</option>
    <option value="human_nurse">human_nurse</option>
    <option value="pet_nurse">pet_nurse</option>
    <option value="human_admin">human_admin</option>
    <option value="pet_admin">pet_admin</option>
    <option value="receptionist">receptionist</option>
  </select><br>
  Patient (optional):
  <select name="patient_id">
    <option value="">--none--</option>
    <?php while($r=$patients->fetch_assoc()): ?>
      <option value="<?= $r['patient_id'] ?>"><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></option>
    <?php endwhile; ?>
  </select><br>
  Pet (optional):
  <select name="pet_id">
    <option value="">--none--</option>
    <?php while($r=$pets->fetch_assoc()): ?>
      <option value="<?= $r['pet_id'] ?>"><?= htmlspecialchars($r['pet_name']) ?></option>
    <?php endwhile; ?>
  </select><br>
  Task:<br><textarea name="task_description"></textarea><br>
  <button type="submit">Add Task</button>
</form>

<h3>Existing tasks</h3>
<?php while($t = $tasks->fetch_assoc()): ?>
  <div style="border:1px solid #ccc;padding:6px;margin:6px;">
    <?= htmlspecialchars($t['task_description']) ?> — role: <?= htmlspecialchars($t['role']) ?> — completed: <?= $t['completed'] ? 'yes' : 'no' ?>
  </div>
<?php endwhile; ?>
