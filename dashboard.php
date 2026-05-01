<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// --- Fetch Metrics ---
$today_date = date('Y-m-d');

// 1. Total Appointments (Overall)
$total_appts = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];

// 2. Total Staff Count
$staff_count = $conn->query("SELECT COUNT(*) as c FROM staff")->fetch_assoc()['c'];

// 3. Appointments Today
$today_appts_total = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = '$today_date'")->fetch_assoc()['c'];

// 4. Pending Appointments Today
$today_pending = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = '$today_date' AND status = 'Pending'")->fetch_assoc()['c'];

// 5. Completed Appointments Today
$today_completed = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = '$today_date' AND status = 'Completed'")->fetch_assoc()['c'];

// 6. Doctor and Nurse Count
$doctor_count = $conn->query("SELECT COUNT(*) as c FROM staff WHERE role = 'Doctor'")->fetch_assoc()['c'];
$nurse_count = $conn->query("SELECT COUNT(*) as c FROM staff WHERE role = 'Nurse'")->fetch_assoc()['c'];

// 7. Completion Rate (Calculation)
$completion_rate = ($today_appts_total > 0) ? round(($today_completed / $today_appts_total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Dashboard-specific CSS for the cards */
        .cards-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px;
        }
        .card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
            text-align: center; 
        }
        .card h3 { 
            color: #666; 
            margin-top: 0; 
            font-size: 14px;
            text-transform: uppercase;
        }
        .card p { 
            font-size: 32px; 
            font-weight: bold; 
            margin: 10px 0 0; 
            color: #333; 
        }
        .card.rate p { color: #28a745; }
        .staff-counts {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
            font-size: 14px;
        }
        .staff-counts span {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, <strong><?php echo $_SESSION['username']; ?></strong>. Today is <?php echo date("l, F j, Y"); ?>.</p>

        <div class="cards-grid">
            <div class="card rate">
                <h3>Completion Rate Today</h3>
                <p><?php echo $completion_rate; ?>%</p>
                <small>Completed: <?php echo $today_completed; ?> / Total: <?php echo $today_appts_total; ?></small>
            </div>

            <div class="card">
                <h3>Appointments Today</h3>
                <p><?php echo $today_appts_total; ?></p>
                <small style="color: green;"> Completed: <?php echo $today_completed; ?></small> | 
                <small style="color: orange;"> Pending: <?php echo $today_pending; ?></small>
            </div>
            
            <div class="card">
                <h3>Total Staff</h3>
                <p><?php echo $staff_count; ?></p>
                <div class="staff-counts">
                    <span>Doctors: <?php echo $doctor_count; ?></span>
                    <span>Nurses: <?php echo $nurse_count; ?></span>
                </div>
            </div>

            <div class="card">
                <h3>Total Appointments (All Time)</h3>
                <p><?php echo $total_appts; ?></p>
            </div>
        </div>
        
        </div>

</body>
</html>