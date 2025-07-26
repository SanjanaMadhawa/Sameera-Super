<?php
// suppliers.php

$conn = new mysqli("localhost", "root", "", "sameera_super");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ADD Supplier
if (isset($_POST['add_supplier'])) {
  $name = $_POST['name'];
  $contact = $_POST['contact'];
  $address = $_POST['address'];
  $products = $_POST['products'];
  $stmt = $conn->prepare("INSERT INTO suppliers (name, contact, address, products) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $contact, $address, $products);
  $stmt->execute();
  header("Location: suppliers.php");
  exit;
}

// DELETE Supplier
if (isset($_GET['delete_supplier'])) {
  $id = intval($_GET['delete_supplier']);
  $conn->query("DELETE FROM suppliers WHERE supplier_id = $id");
  header("Location: suppliers.php");
  exit;
}

// UPDATE Supplier
if (isset($_POST['update_supplier'])) {
  $id = $_POST['supplier_id'];
  $name = $_POST['name'];
  $contact = $_POST['contact'];
  $address = $_POST['address'];
  $products = $_POST['products'];
  $stmt = $conn->prepare("UPDATE suppliers SET name=?, contact=?, address=?, products=? WHERE supplier_id=?");
  $stmt->bind_param("ssssi", $name, $contact, $address, $products, $id);
  $stmt->execute();
  header("Location: suppliers.php");
  exit;
}

// Fetch data
$suppliers = $conn->query("SELECT * FROM suppliers");
$editing = false;
$editData = null;

// If editing
if (isset($_GET['edit_supplier'])) {
  $edit_id = intval($_GET['edit_supplier']);
  $result = $conn->query("SELECT * FROM suppliers WHERE supplier_id = $edit_id");
  if ($result->num_rows > 0) {
    $editData = $result->fetch_assoc();
    $editing = true;
  }
}
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
<body>

<div class="container" >


<h2>Supplier Management</h2>

<!-- Add / Edit Form -->
<form class="form" method="POST">
  <input type="hidden" name="supplier_id" value="<?= $editing ? $editData['supplier_id'] : '' ?>">
  <input type="text" name="name" placeholder="Supplier Name" required value="<?= $editing ? $editData['name'] : '' ?>">
  <input type="text" name="contact" placeholder="Contact Number" required value="<?= $editing ? $editData['contact'] : '' ?>">
  <input type="text" name="address" placeholder="Address" required value="<?= $editing ? $editData['address'] : '' ?>">
  <input type="text" name="products" placeholder="Products Supplied" required value="<?= $editing ? $editData['products'] : '' ?>">
  <button type="submit" name="<?= $editing ? 'update_supplier' : 'add_supplier' ?>" class="action-btn">
    <?= $editing ? 'Update Supplier' : 'Add Supplier' ?>
  </button>
</form>

<!-- Supplier Table -->
<table class="inventory-table" >
  <thead>
    <tr>
      <th>ID</th><th>Name</th><th>Contact</th><th>Address</th><th>Products</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $suppliers->fetch_assoc()): ?>
    <tr>
      <td><?= $row['supplier_id'] ?></td>
      <td><?= $row['name'] ?></td>
      <td><?= $row['contact'] ?></td>
      <td><?= $row['address'] ?></td>
      <td><?= $row['products'] ?></td>
      <td>
        <a href="?edit_supplier=<?= $row['supplier_id'] ?>"><button class="action-btn">Edit</button></a>
        <a href="?delete_supplier=<?= $row['supplier_id'] ?>" onclick="return confirm('Are you sure?')"><button class="action-btn delete-btn">Delete</button></a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</div>

</body>
</html>
