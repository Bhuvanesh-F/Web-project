<?php
session_start();
if (!isset($_SESSION['admin_id'])) header("Location: login.php");
include 'db_connect.php';

$id = $_GET['id']; # Get ID from URL
$message = "";

 # Handle Form Submission 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $specialty = $_POST['specialty'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE staff SET name=?, role=?, specialty=?, phone=?, email=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $role, $specialty, $phone, $email, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Staff updated successfully!'); window.location='staff_view.php';</script>";
    } else {
        $message = "<p style='color:red;'>Error updating record: " . $conn->error . "</p>";
    }
}

# Fetch Existing Data
$stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    die("Staff member not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Staff</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Edit Staff Member</h1>
        
        <div class="form-container">
            <?php echo $message; ?>
            <form method="POST" action="">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>

                <label>Role</label>
                <select name="role">
                    <option value="Doctor" <?php if($staff['role'] == 'Doctor') echo 'selected'; ?>>Doctor</option>
                    <option value="Nurse" <?php if($staff['role'] == 'Nurse') echo 'selected'; ?>>Nurse</option>
                </select>

                <label>Specialty / Department</label>
                <input type="text" name="specialty" value="<?php echo htmlspecialchars($staff['specialty']); ?>" required>

                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($staff['phone']); ?>" required>

                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>

                <button type="submit" class="btn-submit">Update Staff</button>
            </form>
            <br>
            <a href="staff_view.php">Cancel</a>
        </div>
    </div>

</body>
</html>