<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['userEmail'];

// Fetch user details
$sql = "SELECT user_id, name, email, phone, address FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $userId = (int)$user['user_id'];
} else {
    header("Location: logout.php");
    exit();
}
$stmt->close();

// Fetch purchase history
$historySql = "
    SELECT o.order_id, o.order_date, i.name AS product_name, oi.quantity, oi.price
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN inventory i ON oi.product_id = i.inventory_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC, o.order_id DESC
";
$historyStmt = $conn->prepare($historySql);
$historyStmt->bind_param("i", $userId);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();
$historyStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Profile | Sameera Super</title>
<link rel="stylesheet" href="style_u.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="home_u.php">Home</a></li>
    <li><a href="product.php">Products</a></li>
    <li>
      <a href="cart.php"> Cart
        <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
        <span class="cart-count"><?= $_SESSION['cart_count'] ?></span>
        <?php endif; ?>
      </a>
    </li>
    <li><a href="profile.php" class="active">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="container">
  <h2>Your Profile</h2>
  <table class="inventory-table">
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
    <br>

  <h2>Purchase History</h2>
  <?php if ($historyResult && $historyResult->num_rows > 0): ?>
    <table class="inventory-table">
      <thead>
        <tr class="tr_table">
          <th>Order ID</th>
          <th>Order Date</th>
          <th>Product Name</th>
          <th>Quantity</th>
          <th>Price (Rs.)</th>
          <th>Subtotal (Rs.)</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $historyResult->fetch_assoc()): 
            $subtotal = $row['quantity'] * $row['price'];
        ?>
        <tr>
          <td><?= (int)$row['order_id'] ?></td>
          <td><?= htmlspecialchars($row['order_date']) ?></td>
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td><?= (int)$row['quantity'] ?></td>
          <td><?= number_format($row['price'], 2) ?></td>
          <td><?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="text-align:center;">You have not purchased any items yet.</p>
  <?php endif; ?>

  <div class="profile-actions">
    <a href="logout.php">Logout</a>
  </div>
</div>

</body>
</html>
