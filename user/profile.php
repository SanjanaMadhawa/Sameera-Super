<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['userEmail'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['userEmail'];

// Fetch user details from DB
$sql = "SELECT name, email, phone, address FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Database error.");
}

$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // User not found - maybe session invalid, log out
    header("Location: logout.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile | Sameera Super</title>
  <link rel="stylesheet" href="style1.css">
  <style>
    .profile-container {
  max-width: 600px;
  background: white;
  margin: 40px auto;
  padding: 20px 30px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.profile-container h2 {
  text-align: center;
  margin-bottom: 20px;
}

.profile-table {
  width: 100%;
  border-collapse: collapse;
}

.profile-table th,
.profile-table td {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}

.profile-table th {
  background-color: #f2f2f2;
  width: 35%;
  font-weight: 600;
}

.profile-actions {
  margin-top: 20px;
  text-align: center;
}

.profile-actions a {
  text-decoration: none;
  color: white;
  background-color: #dc3545;
  padding: 10px 25px;
  border-radius: 5px;
  font-weight: bold;
}

.profile-actions a:hover {
  background-color: #c82333;
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
    <li><a href="product.php">Products</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="profile.php" class="active">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="profile-container">
  <h2>Your Profile</h2>
  <table class="profile-table">
    <tr>
      <th>Full Name</th>
      <td><?= htmlspecialchars($user['name']); ?></td>
    </tr>
    <tr>
      <th>Email</th>
      <td><?= htmlspecialchars($user['email']); ?></td>
    </tr>
    <tr>
      <th>Phone Number</th>
      <td><?= htmlspecialchars($user['phone']); ?></td>
    </tr>
    <tr>
      <th>Address</th>
      <td><?= nl2br(htmlspecialchars($user['address'])); ?></td>
    </tr>
  </table>

  <div class="profile-actions">
    <a href="logout.php">Logout</a>
  </div>
</div>

</body>
</html>
