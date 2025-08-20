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
  <link rel="stylesheet" href="style2.css">

</head>
<body>

<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg">
    Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="product.php">Products</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<!--Product Section--> 
<div class="container">
  <h2>Product List</h2>
  <input type="text" id="search" placeholder="Search..." onkeyup="filterProducts()">

<section class="products" id="products">
  <div class="heading">
    
  </div>

  <div class="pr_contain">
    <?php while($p = $res->fetch_assoc()): ?>
      <div class="box">
        <?php if (!empty($p['image']) && file_exists("uploads/" . $p['image'])): ?>
          <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
        <?php else: ?>
          <img src="Img/default.png" alt="No image">
        <?php endif; ?>

        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <div class="content">
          <span>Rs.<?= number_format($p['price'], 2) ?></span>
          <?php if ($p['stock'] > 0): ?>
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id" value="<?= $p['inventory_id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit">Add to Cart</button>
            </form>
          <?php else: ?>
            <div class="out-of-stock">Out of stock</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>
</div>

 <!-- Active navbar link -->
<script>
  const currentPage = window.location.pathname.split("/").pop();
  const navLinks = document.querySelectorAll(".nav-links a");

  navLinks.forEach(link => {
    const linkPage = link.getAttribute("href");
    if (linkPage === currentPage) {
      link.classList.add("active");
    }
  });
</script>
</body>
</html>
