<?php
include __DIR__ . '/../includes/db.php';
$id = $_GET['pet_id'] ?? null;
if (!$id) die("Missing pet_id");
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pet_name = $_POST['pet_name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $vax = $_POST['vaccination_status'];
    $stmt = $conn->prepare("UPDATE pets SET pet_name=?, species=?, breed=?, age=?, gender=?, vaccination_status=? WHERE pet_id=?");
    $stmt->bind_param('sssiiss', $pet_name, $species, $breed, $age, $gender, $vax, $id);
    if ($stmt->execute()) echo "Updated.";
    else echo $stmt->error;
}
$res = $conn->query("SELECT * FROM pets WHERE pet_id='".$conn->real_escape_string($id)."'");
$p = $res->fetch_assoc();
if (!$p) die("Not found.");
?>
<form method="POST">
  Name: <input name="pet_name" value="<?= htmlspecialchars($p['pet_name']) ?>"><br>
  Species: <input name="species" value="<?= htmlspecialchars($p['species']) ?>"><br>
  Breed: <input name="breed" value="<?= htmlspecialchars($p['breed']) ?>"><br>
  Age: <input name="age" type="number" value="<?= htmlspecialchars($p['age']) ?>"><br>
  Gender:
  <select name="gender">
    <?php foreach(['unknown','male','female','other'] as $g): ?>
      <option value="<?= $g ?>" <?= ($p['gender']===$g) ? 'selected' : '' ?>><?= $g ?></option>
    <?php endforeach; ?>
  </select><br>
  Vaccination:
  <select name="vaccination_status">
    <?php foreach(['unknown','not_vaccinated','partially_vaccinated','fully_vaccinated'] as $v): ?>
      <option value="<?= $v ?>" <?= ($p['vaccination_status']===$v) ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select><br>
  <button type="submit">Save</button>
</form>
