<?php
session_start();
require_once 'connection.php';

if (!isset($_POST["loginSubmit"])) {
    header("Location: login.php");
    exit();
}

$txtEmail = trim($_POST["loginEmail"]);
$txtPassword = $_POST["loginPassword"];

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: login.php?msg=stmt_error");
    exit();
}

$stmt->bind_param("s", $txtEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if ($txtPassword === $user['password']) {  // Plain text comparison
        $_SESSION["userEmail"] = $user['email'];
        $_SESSION["userName"] = $user['name'];
        header("Location: profile.php");
        exit();
    } else {
        header("Location: login.php?msg=invalid_password");
        exit();
    }
} else {
    header("Location: login.php?msg=user_not_found");
    exit();
}

$stmt->close();
$conn->close();
?>
