<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sameera_super");
$user_id = 1; // Simulated logged-in user

// Fetch all products with stock > 0
$res = $conn->query("SELECT * FROM inventory WHERE stock > 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products | Sameera Super</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body>
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="product.php" class="active">Products</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="container">
  <h2>Product List</h2>
  <input type="text" id="search" placeholder="Search..." onkeyup="filterProducts()">

  <table class="inventory-table" id="productTable">
    <thead>
      <tr>
        <th>Product Name</th>
        <th>Price (LKR)</th>
        <th>Expiry Date</th>
        <th>Quantity</th>
        <th colspan="2">Actions</th>
      </tr>
    </thead>
    <tbody id="list">
      <?php while($p = $res->fetch_assoc()): ?>
        <tr class="product-card">
          <td><h3><?= htmlspecialchars($p['name']) ?></h3></td>
          <td>Rs.<?= htmlspecialchars($p['price']) ?></td>
          <td><?= htmlspecialchars($p['expiry_date']) ?></td>
          <td>
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id" value="<?= $p['inventory_id'] ?>">
              <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock'] ?>">
          </td>
          <td>
              <button type="submit" class="action-btn">Add to Cart</button>
            </form>
            <a href="product.php?delete_product=<?= $p['inventory_id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')">
            </a>
          </td>
         
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
function filterProducts(){
  const q = document.getElementById('search').value.toLowerCase();
  document.querySelectorAll('#productTable tbody .product-card').forEach(row => {
    const name = row.querySelector('h3').innerText.toLowerCase();
    row.style.display = name.includes(q) ? '' : 'none';
  });
}
</script>
</body>
</html>
