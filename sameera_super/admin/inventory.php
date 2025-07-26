<?php
require_once 'connection.php';

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    if (!empty($_POST['name']) && !empty($_POST['price']) && !empty($_POST['expiry']) && !empty($_POST['stock'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $expiry_date = $_POST['expiry'];
        $stock = $_POST['stock'];

        $stmt = $conn->prepare("INSERT INTO inventory (name, price, expiry_date, stock) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $name, $price, $expiry_date, $stock);

        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('All fields are required!');</script>";
    }
}

// Handle Delete Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE inventory_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Product deleted!');</script>";
}

// Fetch all products
$res = $conn->query("SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sameera Super</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
      <li><a href="index.html">Home</a></li>
      <li><a href="inventory.php">Inventory</a></li>
      <li><a href="suppliers.php">Suppliers</a></li>
      <li><a href="orders.html">Orders</a></li>
      <li><a href="customers.html">Customers</a></li>
      <li><a href="staff.html">Staff</a></li>
      <li><a href="login.html">Login</a></li>
  </ul>
</nav>

<div class="container">
  <h2>Inventory Management</h2>
  <form method="POST" class="form">
    <input type="text" name="name" placeholder="Product Name" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="date" name="expiry" required>
    <input type="number" name="stock" placeholder="Stock" required>
    <button name="add">Add Product</button>
  </form>

  <table class="inventory-table">
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Price</th><th>Expiry</th><th>Stock</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php while($p = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $p['inventory_id'] ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= $p['price'] ?></td>
        <td><?= $p['expiry_date'] ?></td>
        <td><?= $p['stock'] ?></td>
        <td>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="id" value="<?= $p['inventory_id'] ?>">
            <button name="delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
