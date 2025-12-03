<?php
include __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = make_id();
    $owner_id = $_POST['owner_id'];
    $pet_name = $_POST['pet_name'];
    $species = $_POST['species'] ?: null;
    $breed = $_POST['breed'] ?: null;
    $age = (int)($_POST['age'] ?: 0);
    $gender = $_POST['gender'] ?: 'unknown';
    $vax = $_POST['vaccination_status'] ?: 'unknown';

    $stmt = $conn->prepare("INSERT INTO pets (pet_id, owner_id, pet_name, species, breed, age, gender, vaccination_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssiss', $id, $owner_id, $pet_name, $species, $breed, $age, $gender, $vax);
    if ($stmt->execute()) echo "Pet added.";
    else echo $stmt->error;
}

$owners = $conn->query("SELECT owner_id, full_name FROM pet_owners");
?>
<form method="POST">
  Owner:
  <select name="owner_id">
    <?php while($r=$owners->fetch_assoc()): ?>
      <option value="<?= $r['owner_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></option>
    <?php endwhile; ?>
  </select><br>
  Name: <input name="pet_name" required><br>
  Species: <input name="species"><br>
  Breed: <input name="breed"><br>
  Age: <input name="age" type="number" min="0"><br>
  Gender:
  <select name="gender">
    <option value="unknown">unknown</option>
    <option value="male">male</option>
    <option value="female">female</option>
    <option value="other">other</option>
  </select><br>
  Vaccination:
  <select name="vaccination_status">
    <option value="unknown">unknown</option>
    <option value="not_vaccinated">not_vaccinated</option>
    <option value="partially_vaccinated">partially_vaccinated</option>
    <option value="fully_vaccinated">fully_vaccinated</option>
  </select><br>
  <button type="submit">Add Pet</button>
</form>
