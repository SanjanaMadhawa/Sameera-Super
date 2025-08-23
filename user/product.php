<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sameera_super");
if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}

// Category selection
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

// Fetch products with optional category filter
$sql = "SELECT * FROM inventory";
if ($selectedCategory) {
    $sql .= " WHERE category = ?";
}
$sql .= " ORDER BY FIELD(category,'Fruits','Vegetables','Grocery','Dry Foods','Meat'), name";

$stmt = $conn->prepare($sql);
if ($selectedCategory) {
    $stmt->bind_param("s", $selectedCategory);
}
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

// Load cart count if logged in
if (isset($_SESSION['userEmail'])) {
    $stmt = $conn->prepare("
      SELECT c.cart_id 
      FROM carts c 
      WHERE c.user_id = (SELECT user_id FROM users WHERE email = ?) 
        AND c.status = 'open' 
      ORDER BY c.cart_id DESC LIMIT 1
    ");
    $stmt->bind_param("s", $_SESSION['userEmail']);
    $stmt->execute();
    $stmt->bind_result($cid);
    if ($stmt->fetch()) {
        $cartId = $cid;
        $stmt->close();

        $countStmt = $conn->prepare("SELECT COUNT(*) as total_items FROM cart_items WHERE cart_id = ?");
        $countStmt->bind_param("i", $cartId);
        $countStmt->execute();
        $countRes = $countStmt->get_result();
        $countRow = $countRes->fetch_assoc();
        $_SESSION['cart_count'] = (int)$countRow['total_items'];
        $countStmt->close();
    } else {
        $_SESSION['cart_count'] = 0;
    }
}

// Helper function for image
function find_image_web_path_user($filename) {
    if (empty($filename)) return null;
    $candidates = [
        ['disk' => __DIR__ . '/../img/upload/', 'web' => '../img/upload/']
    ];
    foreach ($candidates as $p) {
        if (file_exists($p['disk'] . $filename)) {
            return $p['web'] . rawurlencode($filename);
        }
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products | Sameera Super</title>
  <link rel="stylesheet" href="style_u.css">
</head>
<body>

<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg"> Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="home_u.php">Home</a></li>
    <li><a href="product.php" class="active">Products</a></li>
    <li><a href="cart.php">Cart <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count']>0): ?><span class="cart-count"><?= $_SESSION['cart_count'] ?></span><?php endif;?></a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="container">
  <h2>Product List</h2>
  <input type="text" id="search" placeholder="Search by name..." onkeyup="filterProducts()">
  <div id="no-results">No products match your search.</div>

  <section class="products" id="products">
    <?php
      if (!empty($items)) {
        $currentCategory = null;
        $openedBlock = false;
        foreach ($items as $p) {
          if ($currentCategory !== $p['category']) {
            if ($openedBlock) { echo "</div></div>"; }
            $currentCategory = $p['category'];
            $openedBlock = true;
            echo "<div class='category-block'>";
            echo "<h3 class='category-title'>" . htmlspecialchars($currentCategory) . "</h3>";
            echo "<div class='pr_contain'>";
          }
          $imgWeb = find_image_web_path_user($p['image']);
          ?>
          <div class="box">
            <?php if ($imgWeb): ?>
              <img src="<?= $imgWeb ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
              <img src="img/default.png" alt="No image">
            <?php endif; ?>

            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <div class="content">
              <div class="info">
                <?php if ((int)$p['stock'] > 0): ?>
                  <div class="stock">Stock: <?= (int)$p['stock'] ?></div>
                <?php else: ?>
                  <div class="out-of-stock">Out of stock</div>
                <?php endif; ?>
                <span>Rs.<?= number_format((float)$p['price'], 2) ?></span>
              </div>

              <?php if ((int)$p['stock'] > 0): ?>
              <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= (int)$p['inventory_id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit">Add to Cart</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
          <?php
        }
        if ($openedBlock) { echo "</div></div>"; }
      } else {
        echo "<p>No products available.</p>";
      }
    ?>
  </section>
</div>

<script>
  function filterProducts() {
    var q = document.getElementById("search").value.trim().toLowerCase();
    var blocks = document.querySelectorAll(".category-block");
    var noResults = document.getElementById("no-results");
    var anyVisible = false;

    blocks.forEach(function(block) {
      var boxes = block.querySelectorAll(".box");
      var matchesInBlock = 0;
      boxes.forEach(function(box) {
        var h3 = box.querySelector("h3");
        var name = h3 ? h3.textContent.toLowerCase() : "";
        var match = name.indexOf(q) !== -1;
        box.style.display = match ? "" : "none";
        if (match) matchesInBlock++;
      });
      block.style.display = matchesInBlock > 0 ? "" : "none";
      if (matchesInBlock > 0) anyVisible = true;
    });

    noResults.style.display = anyVisible ? "none" : "block";
  }

  document.addEventListener("DOMContentLoaded", filterProducts);
</script>
</body>
</html>
