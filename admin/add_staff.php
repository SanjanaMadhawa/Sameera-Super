<?php
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = isset($_POST['staffName']) ? $_POST['staffName'] : '';
    $email = isset($_POST['staffEmail']) ? $_POST['staffEmail'] : '';
    $phone = isset($_POST['staffPhone']) ? $_POST['staffPhone'] : '';
    $role  = isset($_POST['staffRole']) ? $_POST['staffRole'] : '';
    $dept  = isset($_POST['staffDepartment']) ? $_POST['staffDepartment'] : '';

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($role) && !empty($dept)) {
        $sql = "INSERT INTO staff (name, email, phone, role, department) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $phone, $role, $dept);

        if ($stmt->execute()) {
            header("Location: staff.php?success=1");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "All fields are required.";
    }
}
?>
