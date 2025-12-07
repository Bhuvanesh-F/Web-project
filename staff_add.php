<?php
session_start();
if (!isset($_SESSION['admin_id'])) header("Location: login.php");
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $role = $_POST['role']; # Doctor or Nurse
    $specialty = $_POST['specialty'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO staff (name, role, specialty, phone, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $role, $specialty, $phone, $email);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>New $role added successfully!</p>";
    } else {
        $message = "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Staff</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Add New Staff Member</h1>
        
        <div class="form-container">
            <?php echo $message; ?>
            <form method="POST" action="">
                <label>Full Name</label>
                <input type="text" name="name" required>

                <label>Role</label>
                <select name="role">
                    <option value="Doctor">Doctor</option>
                    <option value="Nurse">Nurse</option>
                </select>

                <label>Specialty / Department</label>
                <input type="text" name="specialty" placeholder="e.g. Cardiology or Pediatrics" required>

                <label>Phone Number</label>
                <input type="text" name="phone" required>

                <label>Email Address</label>
                <input type="email" name="email" required>

                <button type="submit" class="btn-submit">Add Staff</button>
            </form>
        </div>
    </div>

</body>
</html>