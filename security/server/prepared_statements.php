<?php
// prepared_statements.php - mysqli and PDO examples

// mysqli prepared SELECT (example)
function getUserByEmail(mysqli $db, $email) {
$sql = "SELECT id, email, password_hash, role FROM users WHERE email = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
return $res->fetch_assoc();
}

// PDO prepared INSERT (example)
function createUserPDO(PDO $pdo, $email, $passwordHash) {
$sql = "INSERT INTO users (email, password_hash) VALUES (:email, :hash)";
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email, ':hash' => $passwordHash]);
return $pdo->lastInsertId();
}

?>