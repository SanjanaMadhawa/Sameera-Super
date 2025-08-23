<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

// Get user_id from session email
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['userEmail']);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$userId = (int)$user['user_id'];
$stmt->close();

// Ensure active cart exists
if (empty($_SESSION['active_cart_id'])) {
    header("Location: cart.php");
    exit();
}
$cartId = (int)$_SESSION['active_cart_id'];

// Ensure cart has items
$check = $conn->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
$check->bind_param("i", $cartId);
$check->execute();
$check->bind_result($cnt);
$check->fetch();
$check->close();

if ($cnt == 0) {
    header("Location: cart.php");
    exit();
}

$conn->begin_transaction();

try {
    // Calculate cart total
    $stmt = $conn->prepare("
        SELECT SUM(i.price * ci.quantity) AS total
        FROM cart_items ci
        JOIN inventory i ON ci.product_id = i.inventory_id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $totalRes = $stmt->get_result();
    $total = (float)($totalRes->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    if ($total <= 0) {
        throw new Exception("Cart total invalid.");
    }

    // Create order
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, order_date, total_amount) 
        VALUES (?, NOW(), ?)
    ");
    $stmt->bind_param("id", $userId, $total);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert order items from cart
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        SELECT ?, ci.product_id, ci.quantity, i.price
        FROM cart_items ci
        JOIN inventory i ON ci.product_id = i.inventory_id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $cartId);
    $stmt->execute();
    $stmt->close();

    // Reduce inventory stock safely
    $stmt = $conn->prepare("
        UPDATE inventory i
        JOIN cart_items ci ON ci.product_id = i.inventory_id
        SET i.stock = i.stock - ci.quantity
        WHERE ci.cart_id = ? AND i.stock >= ci.quantity
    ");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $stmt->close();

    // Mark cart as checked out
    $stmt = $conn->prepare("UPDATE carts SET status = 'checked_out' WHERE cart_id = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $stmt->close();

    // Clear cart items
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $stmt->close();

    // Reset session cart
    unset($_SESSION['active_cart_id']);

    $conn->commit();

    // Redirect with success
    header("Location: cart.php?success=order_placed");
    exit();

} catch (Throwable $e) {
    $conn->rollback();
    header("Location: cart.php?error=checkout_failed");
    exit();
}
?>



