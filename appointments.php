<?php
session_start();
if (!isset($_SESSION['admin_id'])) header("Location: login.php");
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>All Appointments</h1>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM appointments ORDER BY appointment_date DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Color code status
                        $statusColor = ($row['status'] == 'Pending') ? 'orange' : (($row['status'] == 'Completed') ? 'green' : 'red');
                        
                        echo "<tr>
                                <td>" . $row['id'] . "</td>
                                <td>" . htmlspecialchars($row['patient_name']) . "</td>
                                <td>" . htmlspecialchars($row['appointment_date']) . "</td>
                                <td style='color:$statusColor; font-weight:bold;'>" . htmlspecialchars($row['status']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No appointments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>