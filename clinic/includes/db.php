<?php
// clinic/includes/db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "clinic";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// helper: simple 36-hex id
function make_id() {
    // returns 36 hex chars (18 bytes * 2 = 36)
    return bin2hex(random_bytes(18));
}

