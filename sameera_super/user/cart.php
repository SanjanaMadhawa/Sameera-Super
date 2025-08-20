<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user_id
$userEmail = $_SESSION['userEmail'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$userId = (int)$user['user_id'];
$stmt->close();

/*Ensure there is a single OPEN cart for this user and keep its ID in session.*/
function getOrCreateOpenCartId(mysqli $conn, int $userId): int {
    // 1) Try to reuse open cart
    $stmt = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ? AND status = 'open' ORDER BY cart_id DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($existingCartId);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$existingCartId;
    }
    $stmt->close();

    // 2) Create a new open cart
    $stmt = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    return (int)$newId;
}

// Keep active cart id in session (optional but convenient)
if (!isset($_SESSION['active_cart_id'])) {
    $_SESSION['active_cart_id'] = getOrCreateOpenCartId($conn, $userId);
}
$cartId = (int)$_SESSION['active_cart_id'];

/*HANDLERS*/

// Add to cart (from product.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && !isset($_POST['update_item_id']) && !isset($_POST['remove_item_id'])) {
    $pid = max(1, (int)$_POST['product_id']);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    // Upsert: if the product already exists in this cart, increase quantity
    $stmt = $conn->prepare("
        INSERT INTO cart_items (cart_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ");
    $stmt->bind_param("iii", $cartId, $pid, $qty);
    $stmt->execute();
    $stmt->close();

    header("Location: cart.php");
    exit();
}

// Update quantity
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

// Remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item_id'])) {
    $itemId = (int)$_POST['remove_item_id'];
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE item_id = ? AND cart_id = ?");
    $stmt->bind_param("ii", $itemId, $cartId);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

/*FETCH CART ITEMS*/
$stmt = $conn->prepare("
  SELECT ci.item_id, i.inventory_id, i.name, i.price, ci.quantity
  FROM cart_items ci
  JOIN inventory i ON ci.product_id = i.inventory_id
  WHERE ci.cart_id = ?
");
$stmt->bind_param("i", $cartId);
$stmt->execute();
$items = $stmt->get_result();

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart | Sameera Super</title>
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
    <li><a href="cart.php" class="active">Cart</a></li>
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
        <th>Subtotal</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($c = $items->fetch_assoc()): 
        $sub = $c['price'] * $c['quantity'];
        $total += $sub;
    ?>
      <tr>
        <td><h3><?= htmlspecialchars($c['name']) ?></h3></td>
        <td>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="update_item_id" value="<?= (int)$c['item_id'] ?>">
            <input type="number" name="new_quantity" value="<?= (int)$c['quantity'] ?>" min="1">
            <button class="cart-button update" type="submit">Update</button>
          </form>
        </td>
        <td>Rs. <?= number_format($c['price'], 2) ?></td>
        <td>Rs. <?= number_format($sub, 2) ?></td>
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
  <?php if ($total > 0): ?>
    <form method="POST" action="checkout.php">
        <button type="submit" class="cart-button">Proceed to Checkout</button>
    </form>
<?php endif; ?>

</div>
</body>
</html>

