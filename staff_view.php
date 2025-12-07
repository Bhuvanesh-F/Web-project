<?php
session_start();
if (!isset($_SESSION['admin_id'])) header("Location: login.php");
include 'db_connect.php';

# Initialize search variable
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Staff</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Specific styles for the search bar */
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            max-width: 600px;
        }
        .search-container input {
            flex: 1; /* Takes up available space */
            padding: 10px;
            margin: 0; /* Override global margin */
        }
        .search-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .reset-btn {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            line-height: 1.5; /* Align text vertically */
        }
        .action-links a { text-decoration: none; margin: 0 5px; font-weight: bold; }
        .edit-btn { color: #ffc107; }
        .delete-btn { color: #dc3545; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Staff Directory</h1>
        
        <div class="search-container">
            <form method="GET" action="" style="display: flex; gap: 10px; width: 100%;">
                <input type="text" name="search" placeholder="Search by Name or Role..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>
            <?php if ($search != ""): ?>
                <a href="staff_view.php" class="reset-btn">Reset</a>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Specialty</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
               
                if ($search != "") {
                    # Using prepared statements for security
                    $sql = "SELECT * FROM staff WHERE name LIKE ? OR role LIKE ? ORDER BY role, name";
                    $stmt = $conn->prepare($sql);
                    $likeSearch = "%" . $search . "%"; # partial matching
                    $stmt->bind_param("ss", $likeSearch, $likeSearch);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $sql = "SELECT * FROM staff ORDER BY role, name";
                    $result = $conn->query($sql);
                }

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['id'] . "</td>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>" . htmlspecialchars($row['role']) . "</td>
                                <td>" . htmlspecialchars($row['specialty']) . "</td>
                                <td>" . htmlspecialchars($row['phone']) . "</td>
                                <td class='action-links'>
                                    <a href='staff_edit.php?id=" . $row['id'] . "' class='edit-btn'>Edit</a>
                                    <a href='staff_delete.php?id=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this staff member?\")'>Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No staff members found matching your search.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>