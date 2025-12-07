<?php
session_start();
include 'db_connect.php';

# Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

#Check if ID is set in URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    # Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        # Redirect back to view staff page
        header("Location: staff_view.php");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    $stmt->close();
} else {
    header("Location: staff_view.php");
}
?>