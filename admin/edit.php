<?php
require_once 'connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staffId']) && !empty($_POST['staffId'])) {
    $id = intval($_POST['staffId']);
    $name = $conn->real_escape_string($_POST['staffName']);
    $email = $conn->real_escape_string($_POST['staffEmail']);
    $phone = $conn->real_escape_string($_POST['staffPhone']);
    $role = $conn->real_escape_string($_POST['staffRole']);
    $department = $conn->real_escape_string($_POST['staffDepartment']);
    $sql = "UPDATE staff SET name='$name', email='$email', phone='$phone', role='$role', department='$department' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        // Optionally, redirect back to staff.php or reload
        header('Location: staff.php');
        exit();
    } else {
        echo "<script>alert('Error updating staff: {$conn->error}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff Management | Sameera Super</title>
  <script src="staff.js"></script>
  
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f7f9;
      margin: 0;
      padding: 0;
    }

    .navbar {
      background: #2b6777;
      color: white;
      padding: 15px 10%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      font-size: 26px;
      font-weight: bold;
      color: white;
    }

    .logoimg {
      height: 40px;
      vertical-align: middle;
      margin-right: 10px;
      border-radius: 10px;
    }

    .nav-links {
      list-style: none;
      display: flex;
      gap: 20px;
    }

    .nav-links li a {
      color: white;
      text-decoration: none;
    }

    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    h2 {
      text-align: center;
      color: #2b6777;
    }

    .form {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .form input, .form select {
      flex: 1 1 200px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .form button {
      background-color: #2b6777;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: center;
    }

    th {
      background-color: #e1ecf4;
    }

    .edit-btn, .delete-btn {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 6px 12px;
      margin: 2px;
      border-radius: 4px;
      cursor: pointer;
    }

    .delete-btn {
      background-color: #dc3545;
    }

    .report-btn {
      margin-top: 10px;
      background: #6c63ff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="inventory.html">Inventory</a></li>
    <li><a href="suppliers.html">Suppliers</a></li>
    <li><a href="orders.html">Orders</a></li>
    <li><a href="customers.html">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.html">Login</a></li>
  </ul>
</nav>

<!-- Content -->
<div class="container">
  <h2>Staff Management</h2>

  <!-- Staff Form -->
<form id="staffForm" action="add_staff.php" method="POST" class="form">
    <input type="name" name="staffName" id="staffName" placeholder="Full Name" required />
    <input type="email" name="staffEmail" id="staffEmail" placeholder="Email" required />
    <input type="tel" name="staffPhone" id="staffPhone" placeholder="Phone Number" pattern="[0-9]{10}" required />
    <select name="staffRole" id="staffRole" required>
      <option value="">Select Role</option>
      <option>Cashier</option>
      <option>Inventory Manager</option>
      <option>HR</option>
      <option>Supervisor</option>
    </select>
    <input type="text" name="staffDepartment" id="staffDepartment" placeholder="Department" required />
    <button type="submit">Add Staff</button>
</form>

  <!-- Staff Table -->
  <table id="staffTable">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Department</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
<?php
$result = $conn->query("SELECT * FROM staff");
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['email']}</td>
            <td>{$row['phone']}</td>
            <td>{$row['role']}</td>
            <td>{$row['department']}</td>
            <td>
              <button class='edit-btn' onclick='editStaff(this)'>Edit</button>
              <button class='delete-btn' onclick='deleteStaff({$row['id']}, this)'>Delete</button>
            </td>
          </tr>";
}