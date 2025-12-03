<?php
include __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = make_id();
    $patient_id = $_POST['patient_id'] ?: null;
    $doctor_id  = $_POST['doctor_id'] ?: null;
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'] ?: null;
    $stmt = $conn->prepare("INSERT INTO human_reviews (review_id, patient_id, doctor_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssis', $id, $patient_id, $doctor_id, $rating, $comment);
    if ($stmt->execute()) echo "Review saved.";
    else echo $stmt->error;
}
$patients = $conn->query("SELECT patient_id, first_name, last_name FROM human_patients");
$doctors = $conn->query("SELECT doctor_id, full_name FROM human_doctors");
?>
<form method="POST">
  Patient:
  <select name="patient_id">
    <?php while($r=$patients->fetch_assoc()): ?>
      <option value="<?= $r['patient_id'] ?>"><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></option>
    <?php endwhile; ?>
  </select><br>
  Doctor:
  <select name="doctor_id">
    <?php while($r=$doctors->fetch_assoc()): ?>
      <option value="<?= $r['doctor_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
    <?php endwhile; ?>
  </select><br>
  Rating:
  <select name="rating">
    <?php for($i=1;$i<=5;$i++): ?>
      <option value="<?= $i ?>"><?= $i ?></option>
    <?php endfor; ?>
  </select><br>
  Comment:<br><textarea name="comment"></textarea><br>
  <button type="submit">Save Review</button>
</form>
