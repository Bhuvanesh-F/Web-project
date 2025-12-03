<?php
include __DIR__ . '/../includes/db.php';
$type = $_GET['type'] ?? 'human';
$id = $_GET['appointment_id'] ?? null;
if (!$id) die("Missing id");

if ($type === 'human') {
    $stmt = $conn->prepare("DELETE FROM human_appointments WHERE appointment_id=?");
} else {
    $stmt = $conn->prepare("DELETE FROM pet_appointments WHERE appointment_id=?");
}
$stmt->bind_param('s', $id);
if ($stmt->execute()) {
    echo "Deleted. <a href='appointment_add.php'>Back</a>";
} else {
    echo $stmt->error;
}
