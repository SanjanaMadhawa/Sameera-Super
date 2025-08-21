<?php
require_once 'connection.php';

// Redirect if form not submitted properly
if (!isset($_POST["signUpSubmit"])) {
    header("Location: login.php");
    exit();
}

// Collect form data and sanitize (basic)
$txtName = trim($_POST["name"]);
$txtEmail = trim($_POST["email"]);
$txtPassword = $_POST["password"];
$txtConfirmPassword = $_POST["confirmPassword"];
$txtContactNo = trim($_POST["phone"]);
$txtAddress = trim($_POST["address"]);

// Check if password and confirm password match
if ($txtPassword !== $txtConfirmPassword) {
    header("Location: login.php?msg=pw_mismatch");
    exit();
}


// Prepare SQL statement with placeholders
$sql = "INSERT INTO users (name, email, phone, address, password) VALUES ('$txtName','$txtEmail','$txtContactNo','$txtAddress','$txtPassword')";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Debugging: You can log $conn->error for more details
    header("Location: login.php?msg=stmt_error");
    exit();
}

// Execute the statement
if ($stmt->execute()) {
    header("Location: login.php?msg=1"); // success message
} else {
    header("Location: login.php?msg=0"); // failure message
}

$stmt->close();
$conn->close();
?>
