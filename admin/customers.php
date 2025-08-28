<?php
session_start();
require_once 'connection.php'; // Database connection file

if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Customer
if (isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = password_hash("123456", PASSWORD_DEFAULT); // default password

    $stmt = $conn->prepare("INSERT INTO users (name,email,phone,address,password) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss",$name,$email,$phone,$address,$password);
    $stmt->execute();
    $stmt->close();
    header("Location: customers.php");
    exit();
}

// Handle Edit Customer
if (isset($_POST['edit_customer'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=? WHERE user_id=?");
    $stmt->bind_param("ssssi",$name,$email,$phone,$address,$id);
    $stmt->execute();
    $stmt->close();
    header("Location: customers.php");
    exit();
}

// Handle Delete Customer
if (isset($_POST['delete_customer'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("UPDATE users SET is_active=0 WHERE user_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
    header("Location: customers.php");
    exit();
}


// Fetch all users
$result = $conn->query("SELECT * FROM users WHERE is_active=1 ORDER BY user_id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Management | Sameera Super</title>
<link rel="stylesheet" href="style1.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg"> Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="inventory.php">Inventory</a></li>
    <li><a href="suppliers.php">Suppliers</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="customers.php" class="active">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="container">
  <div class="section-box">
  <h2>Customer Management</h2>
<div class="section-box">
  <!-- Add Customer Form -->
  <form class="form" method="POST" id="customerForm">
    <input type="text" name="name" placeholder="Full Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="tel" name="phone" placeholder="Phone (e.g., 0771234567)" pattern="[0-9]{10}" required />
    <input type="text" name="address" placeholder="Address" required />
    <button type="submit" name="add_customer">Add Customer</button>
  </form>

  <!-- Customer Table -->
  <table class="inventory-table">
    <thead>
      <tr>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <form method="POST">
          <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required></td>
          <td><input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required></td>
          <td><input type="tel" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" pattern="[0-9]{10}" required></td>
          <td><input type="text" name="address" value="<?= htmlspecialchars($row['address']) ?>" required></td>
          <td>
            <input type="hidden" name="id" value="<?= $row['user_id'] ?>">
            <button type="submit" name="edit_customer">Edit</button>
            <button type="submit" name="delete_customer" onclick="return confirm('Delete this customer?')">Delete</button>
          </td>
        </form>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
 </div>

  <!-- Report Section -->
  <div class="section-box">
    <h3>Generate Customer Report</h3>
    <form method="POST" action="customerreport.php">
      <label for="report_type">Choose report type:</label>
      <select name="report_type" id="report_type" required>
        <option value="all">All Active Customers</option>
        <option value="recent">Recently Added (last 30 days)</option>
      </select>
      <button type="submit" name="report_csv">Download CSV Report</button>
    </form>
  </div>

</div>

</body>
</html>
