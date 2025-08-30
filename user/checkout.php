<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

// get user id
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['userEmail']);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$userId = (int)$user['user_id'];
$stmt->close();

// ensure cart id exists in session
if (empty($_SESSION['active_cart_id'])) {
    header("Location: cart.php");
    exit();
}
$cartId = (int)$_SESSION['active_cart_id'];

// ensure cart has items
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
        INSERT INTO orders (user_id, order_date, total_amount, status) 
        VALUES (?, NOW(), ?, 'Pending')
    ");
    $stmt->bind_param("id", $userId, $total);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert order items from cart (and set order_items.status='Pending')
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price, status)
        SELECT ?, ci.product_id, ci.quantity, i.price, 'Pending'
        FROM cart_items ci
        JOIN inventory i ON ci.product_id = i.inventory_id
        WHERE ci.cart_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $cartId);
    $stmt->execute();
    $stmt->close();

    // ALSO insert into cart_details so user sees statuses later
    $stmt = $conn->prepare("
        INSERT INTO cart_details (user_id, cart_id, order_id, product_id, product_name, quantity, price, status)
        SELECT ?, ?, ?, oi.product_id, i.name, oi.quantity, oi.price, 'Pending'
        FROM order_items oi
        JOIN inventory i ON oi.product_id = i.inventory_id
        WHERE oi.order_id = ?
    ");
    // bind: userId, cartId, orderId, orderId (last for WHERE)
    $stmt->bind_param("iiii", $userId, $cartId, $orderId, $orderId);
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

    header("Location: cart.php?success=order_placed");
    exit();
} catch (Throwable $e) {
    $conn->rollback();
    // optionally log $e->getMessage()
    header("Location: cart.php?error=checkout_failed");
    exit();
}
