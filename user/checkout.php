<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userEmail'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['userEmail'];

// Fetch user ID & name
$stmt = $conn->prepare("SELECT user_id, name FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$userId = $user['user_id'];
$userName = $user['name'];
$stmt->close();

// Fetch cart items
$cart = $conn->query("
  SELECT c.product_id, i.name AS product_name, i.price, c.quantity
  FROM cart c
  JOIN inventory i ON c.product_id = i.inventory_id
  WHERE c.user_id = $userId
");

if ($cart->num_rows == 0) {
    echo "Cart is empty!";
    exit();
}

// Insert into orders table
$orderDate = date("Y-m-d");
$conn->query("INSERT INTO orders (user_id, order_date, total_amount) VALUES ($userId, '$orderDate', 0)");
$orderId = $conn->insert_id;

$totalAmount = 0;
$items = [];

while ($item = $cart->fetch_assoc()) {
    $pid = $item['product_id'];
    $pname = $item['product_name'];
    $price = $item['price'];
    $qty = $item['quantity'];
    $subtotal = $price * $qty;

    $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price)
                  VALUES ($orderId, $pid, $qty, $price)");

    $items[] = [
        'product_id' => $pid,
        'product_name' => $pname,
        'quantity' => $qty,
        'subtotal' => $subtotal
    ];

    $totalAmount += $subtotal;
}

// Update total in orders table
$conn->query("UPDATE orders SET total_amount = $totalAmount WHERE order_id = $orderId");

// Clear the cart
$conn->query("DELETE FROM cart WHERE user_id = $userId");

// Pass order details via session
$_SESSION['orderDetails'] = [
    'order_id' => $orderId,
    'user_name' => $userName,
    'items' => $items,
    'total_amount' => $totalAmount
];

// Redirect to orders page
header("Location: cart.php");
exit();
?>

