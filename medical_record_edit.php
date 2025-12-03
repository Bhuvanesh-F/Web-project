<?php
include '../includes/db.php';

// ---------------------------------------
// 1. GET RECORD BY ID (FROM URL)
// ---------------------------------------
if (!isset($_GET['id'])) {
    die("ERROR: Medical Record ID not provided.");
}

$record_id = $_GET['id'];

$sql = "SELECT * FROM medical_records WHERE record_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $record_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("ERROR: Medical record not found.");
}

$record = $result->fetch_assoc();

// ---------------------------------------
// 2. PROCESS UPDATE IF FORM SUBMITTED
// ---------------------------------------
if (isset($_POST['update'])) {

    $doctor_id      = $_POST['doctor_id'];
    $patient_id     = $_POST['patient_id'];
    $pet_id         = $_POST['pet_id'];
    $record_type    = $_POST['record_type'];
    $record_details = $_POST['record_details'];
    $record_date    = $_POST['record_date'];

    $update_sql = "UPDATE medical_records SET 
        doctor_id = ?, 
        patient_id = ?, 
        pet_id = ?, 
        record_type = ?, 
        record_details = ?, 
        record_date = ?
        WHERE record_id = ?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param(
        "sssssss",
        $doctor_id,
        $patient_id,
        $pet_id,
        $record_type,
        $record_details,
        $record_date,
        $record_id
    );

    if ($stmt->execute()) {
        echo "Medical record updated successfully!";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Medical Record</title>
</head>
<body>

<h2>Edit Medical Record</h2>

<form method="POST">

    <label>Doctor ID:</label><br>
    <input type="text" name="doctor_id" value="<?= $record['doctor_id'] ?>" required><br><br>

    <label>Patient (Human) ID:</label><br>
    <input type="text" name="patient_id" value="<?= $record['patient_id'] ?>"><br><br>

    <label>Pet ID:</label><br>
    <input type="text" name="pet_id" value="<?= $record['pet_id'] ?>"><br><br>

    <label>Record Type:</label><br>
    <input type="text" name="record_type" value="<?= $record['record_type'] ?>" required><br><br>

    <label>Record Details:</label><br>
    <textarea name="record_details" required><?= $record['record_details'] ?></textarea><br><br>

    <label>Date:</label><br>
    <input type="date" name="record_date" value="<?= $record['record_date'] ?>" required><br><br>

    <button type="submit" name="update">Update Record</button>

</form>

</body>
</html>
