<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user_id
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['userEmail']);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$userId = (int)$user['user_id'];
$stmt->close();

/* Ensure open cart id */
if (!isset($_SESSION['active_cart_id'])) {
    $stmt = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ? AND status = 'open' ORDER BY cart_id DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($existingCartId);
    if ($stmt->fetch()) {
        $_SESSION['active_cart_id'] = (int)$existingCartId;
    }
    $stmt->close();

    if (!isset($_SESSION['active_cart_id'])) {
        $stmt2 = $conn->prepare("INSERT INTO carts (user_id, status) VALUES (?, 'open')");
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();
        $_SESSION['active_cart_id'] = $stmt2->insert_id;
        $stmt2->close();
    }
}
$cartId = (int)$_SESSION['active_cart_id'];

/* HANDLE add to cart */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $productId = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['quantity']);

    // check if already in cart
    $stmt = $conn->prepare("SELECT item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cartId, $productId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // update existing item
        $newQty = $row['quantity'] + $qty;
        $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE item_id = ?");
        $updateStmt->bind_param("ii", $newQty, $row['item_id']);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // insert new item
        $insertStmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?,?,?)");
        $insertStmt->bind_param("iii", $cartId, $productId, $qty);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $stmt->close();

    header("Location: cart.php");
    exit();
}

/* HANDLE cart update/remove */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item_id'])) {
    $itemId = (int)$_POST['update_item_id'];
    $newQty = max(1, (int)$_POST['new_quantity']);
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE item_id = ? AND cart_id = ?");
    $stmt->bind_param("iii", $newQty, $itemId, $cartId);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item_id'])) {
    $itemId = (int)$_POST['remove_item_id'];
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE item_id = ? AND cart_id = ?");
    $stmt->bind_param("ii", $itemId, $cartId);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

/* FETCH CART ITEMS */
$stmt = $conn->prepare("
  SELECT ci.item_id, i.inventory_id, i.name, i.price, ci.quantity
  FROM cart_items ci
  JOIN inventory i ON ci.product_id = i.inventory_id
  WHERE ci.cart_id = ?
");
$stmt->bind_param("i", $cartId);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

$total = 0;

/* Count items */
$countStmt = $conn->prepare("SELECT COUNT(*) as total_items FROM cart_items WHERE cart_id = ?");
$countStmt->bind_param("i", $cartId);
$countStmt->execute();
$countRes = $countStmt->get_result();
$countRow = $countRes->fetch_assoc();
$_SESSION['cart_count'] = (int)$countRow['total_items'];
$countStmt->close();

/* FETCH CART DETAILS (for this user) */
$stmtCD = $conn->prepare("
    SELECT id, cart_id, order_id, product_id, product_name, quantity, price, status, created_at
    FROM cart_details
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmtCD->bind_param("i", $userId);
$stmtCD->execute();
$cartDetails = $stmtCD->get_result();
$stmtCD->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart | Sameera Super</title>
  <link rel="stylesheet" href="style_u.css">
</head>
<body>
<nav class="navbar">
  <div class="logo">
    <img src="img.png" alt="Sameera Super Logo" class="logoimg"> Sameera Super
  </div>
  <ul class="nav-links">
    <li><a href="home_u.php">Home</a></li>
    <li><a href="product.php">Products</a></li>
    <li><a href="cart.php">Cart <?php if (!empty($_SESSION['cart_count'])) echo "<span class='cart-count'>".$_SESSION['cart_count']."</span>"; ?></a></li>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<div class="container">
<div class="section-box">
  <h2>Your Shopping Cart</h2>
  <?php if ($items->num_rows === 0): ?>
      <h3>Your cart is empty, please visit the <a href="product.php">product page</a></h3>
  <?php else: ?>
    <table class="inventory-table">
      <thead>
        <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php 
        $total = 0;
        while ($c = $items->fetch_assoc()):
            $sub = $c['price'] * $c['quantity'];
            $total += $sub;
        ?>
        <tr>
          <td><?=htmlspecialchars($c['name'])?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="update_item_id" value="<?= (int)$c['item_id'] ?>">
              <input type="number" name="new_quantity" value="<?= (int)$c['quantity'] ?>" min="1">
              <button class="cart-button update" type="submit">Update</button>
            </form>
          </td>
          <td>Rs. <?= number_format($c['price'],2) ?></td>
          <td>Rs. <?= number_format($sub,2) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="remove_item_id" value="<?= (int)$c['item_id'] ?>">
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
    <form method="POST" action="checkout.php">
      <button type="submit" class="cart-button">Proceed to Checkout</button>
    </form>
  <?php endif; ?>
</div>


  <div class="section-box">
    <h2>Cart Details (Order history & statuses)</h2>
    <?php if ($cartDetails->num_rows === 0): ?>
      <p>No order / cart details yet.</p>
    <?php else: ?>
      <table class="inventory-table">
        <thead>
          <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Status</th><th>Order ID</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php while ($r = $cartDetails->fetch_assoc()): ?>
            <tr>
              <td><?=htmlspecialchars($r['product_name'])?></td>
              <td><?= (int)$r['quantity'] ?></td>
              <td>Rs. <?= number_format($r['price'],2) ?></td>
              <td>Rs. <?= number_format($r['price'] * $r['quantity'],2) ?></td>
              <td><?=htmlspecialchars($r['status'])?></td>
              <td><?= $r['order_id'] ? (int)$r['order_id'] : '-' ?></td>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
