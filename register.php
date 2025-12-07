<?php
session_start(); 
include 'db_connect.php';
require_once 'validate_user.php';

// Initialize variables
$errors = [];
$success = false;

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Enhanced Server-side validation
        if (!validateEmail($email)) $errors[] = 'Invalid email format';
        if (!validateUsername($username)) $errors[] = 'Username must be 3-20 alphanumeric chars';
        if (!validatePassword($password)) $errors[] = 'Password must be 8+ chars with 1 uppercase, 1 lowercase, 1 number';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
        
        // Check duplicates only if validation passed
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT admin_id FROM human_admins WHERE email = ? OR username = ?");
            $stmt->bind_param("ss", $email, $username);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $errors[] = 'Email or username already exists';
                }
            } else {
                $errors[] = 'Database error during duplicate check';
            }
            $stmt->close();
        }

        // Insert new admin if no errors
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO human_admins (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = true;
                echo "<script>alert('Registration Successful! Please Login.'); window.location='login.php';</script>";
            } else {
                $errors[] = "Registration failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Register</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { display: block; width: 100%; margin: 10px 0; padding: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Register Admin</h2>
        <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"><br>
        <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"><br>
        <input type="password" name="password" placeholder="Password (8+ chars, 1 upper, 1 lower, 1 number)" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit">Register</button>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</body>

</html>
