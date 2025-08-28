<?php
session_start();
require_once "connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // ❌ storing plain text (insecure)

    // check if email exists
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['alert'] = "Email already registered!";
        header("Location: login.php");
        exit();
    }

    // insert new admin with plain password
    $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['alert'] = "Registration successful. Please login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['alert'] = "Registration failed.";
        header("Location: login.php");
        exit();
    }
}
?>
