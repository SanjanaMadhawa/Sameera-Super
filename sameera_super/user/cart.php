<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sameera_super");
$user_id = 1;

// Add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $pid = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    $conn->query("INSERT INTO cart(user_id, product_id, quantity) VALUES($user_id, $pid, $qty)");
}

// Remove from cart
if (isset($_POST['remove_cart_id'])) {
    $remove_id = intval($_POST['remove_cart_id']);
    $conn->query("DELETE FROM cart WHERE cart_id = $remove_id");
}

// Update quantity
if (isset($_POST['update_cart_id']) && isset($_POST['new_quantity'])) {
    $update_id = intval($_POST['update_cart_id']);
    $new_qty = intval($_POST['new_quantity']);
    $conn->query("UPDATE cart SET quantity = $new_qty WHERE cart_id = $update_id");
}

// Fetch cart data
$res = $conn->query("
  SELECT c.cart_id, i.name, i.price, c.quantity
  FROM cart c
  JOIN inventory i ON c.product_id = i.inventory_id
  WHERE c.user_id = $user_id
");

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart | Sameera Super</title>
  <link rel="stylesheet" href="style.css">
  <style>
    
  </style>
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

<div class="container">
  <h2>Your Shopping Cart</h2>
  <table class="inventory-table">
    <thead>
      <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Total</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($c = $res->fetch_assoc()):
        $sub = $c['price'] * $c['quantity'];
        $total += $sub;
      ?>
        <tr>
          <td><h3><?= htmlspecialchars($c['name']) ?></h3></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="update_cart_id" value="<?= $c['cart_id'] ?>">
              <input type="number" name="new_quantity" value="<?= $c['quantity'] ?>" min="1">
              <button class="cart-button update" type="submit">Update</button>
            </form>
          </td>
          <td>Rs. <?= number_format($c['price'], 2) ?></td>
          <td>Rs. <?= number_format($sub, 2) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="remove_cart_id" value="<?= $c['cart_id'] ?>">
              <button class="cart-button remove" type="submit">Remove</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <br>
  <h3>Total: Rs. <?= number_format($total, 2) ?></h3>
  <br>
  <?php if ($total > 0): ?>
    <form method="POST" action="checkout.php">
      <button class="cart-button">Proceed to Checkout</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
