<?php
// api/get-doctors.php – returns JSON list of doctors for a speciality
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$speciality = trim($_GET['speciality'] ?? '');

if ($speciality === '') {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare(
    'SELECT doctor_id, full_name, speciality
     FROM human_doctors
     WHERE speciality = ?
     ORDER BY full_name'
);
$stmt->bind_param('s', $speciality);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        'doctor_id'  => $row['doctor_id'],
        'full_name'  => $row['full_name'],
        'speciality' => $row['speciality'],
    ];
}
$stmt->close();

echo json_encode($doctors);
