<?php
session_start();
require_once "connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['loginEmail']);
    $password = $_POST['loginPassword'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_email'] = $admin['email'];

        $_SESSION['alert'] = "Login successful!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['alert'] = "Invalid email or password!";
        header("Location: login.php");
        exit();
    }
}
?>
