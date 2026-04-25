<?php
// api/book-appointment.php – AJAX endpoint
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// ── Collect & validate ────────────────────────────────────────
$full_name   = trim($_POST['full_name']        ?? '');
$contact     = trim($_POST['contact_number']   ?? '');
$speciality  = trim($_POST['doctor_speciality']?? '');
$doctor_id   = trim($_POST['doctor_id']        ?? '') ?: null;
$appt_date   = trim($_POST['preferred_date']   ?? '');
$appt_time   = trim($_POST['preferred_time']   ?? '');
$symptoms    = trim($_POST['symptoms']         ?? '');
$appt_type   = trim($_POST['appointment_type'] ?? 'human');

$errors = [];

if (strlen($full_name) < 2) $errors[] = 'Full name is required.';
if (!preg_match('/^(\+230\s?)?\d{3}\s?\d{4}$/', $contact)) $errors[] = 'Invalid phone number.';
if ($speciality === '') $errors[] = 'Please select a speciality.';
if (!$appt_date || strtotime($appt_date) < strtotime('today')) $errors[] = 'Please choose a future date.';
if ($appt_time === '') $errors[] = 'Please choose a time slot.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── If patient is logged in, link the appointment ─────────────
$patient_id = $_SESSION['patient_id'] ?? null;

// If not logged in we still allow booking (guest); but we need a patient record.
// For simplicity, require login for saving to DB; otherwise prompt.
if (!$patient_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Please <a href="/vitalcare/login.php">log in</a> to save your appointment.'
    ]);
    exit;
}

// ── Insert into DB ────────────────────────────────────────────
$appt_id   = uuid4();
$start_dt  = $appt_date . ' ' . $appt_time . ':00';
$end_dt    = date('Y-m-d H:i:s', strtotime($start_dt) + 3600); // 1-hour slot

$stmt = $conn->prepare(
    'INSERT INTO human_appointments
     (appointment_id, patient_id, doctor_id, speciality, appointment_date, preferred_time, notes, status)
     VALUES (?,?,?,?,?,?,?,\'pending\')'
);
$stmt->bind_param('sssssss',
    $appt_id, $patient_id, $doctor_id, $speciality, $appt_date, $appt_time, $symptoms
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'appointment_id' => $appt_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
$stmt->close();
