<?php
session_start();
require_once 'connection.php';

// Deliver Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver_order_id'])) {
    $deliverOrderId = intval($_POST['deliver_order_id']);
    $checkInvoice = $conn->query("SELECT * FROM invoices WHERE order_id = $deliverOrderId");
    if ($checkInvoice->num_rows === 0) {
        $conn->query("INSERT INTO invoices (order_id, user_id, invoice_date) VALUES ($deliverOrderId, (
            SELECT user_id FROM orders WHERE order_id = $deliverOrderId
        ), CURDATE())");
        $conn->query("UPDATE orders SET status = 'Delivered' WHERE order_id = $deliverOrderId");
    }
}

// Delete order item if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $delOrderId = intval($_POST['delete_order_id']);
    $delProductId = intval($_POST['delete_product_id']);

    $stmtDel = $conn->prepare("DELETE FROM order_items WHERE order_id = ? AND product_id = ?");
    $stmtDel->bind_param("ii", $delOrderId, $delProductId);
    $stmtDel->execute();
    $stmtDel->close();

    $stmtTotal = $conn->prepare("SELECT SUM(price * quantity) AS new_total FROM order_items WHERE order_id = ?");
    $stmtTotal->bind_param("i", $delOrderId);
    $stmtTotal->execute();
    $resTotal = $stmtTotal->get_result();
    $newTotal = $resTotal->fetch_assoc()['new_total'] ?? 0;
    $stmtTotal->close();

    $stmtUpd = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
    $stmtUpd->bind_param("di", $newTotal, $delOrderId);
    $stmtUpd->execute();
    $stmtUpd->close();

    header("Location: orders.php");
    exit();
}

// Cancel Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancelOrderId = intval($_POST['cancel_order_id']);
    $conn->query("DELETE FROM order_items WHERE order_id = $cancelOrderId");
    $conn->query("DELETE FROM orders WHERE order_id = $cancelOrderId");
    header("Location: orders.php");
    exit();
}

// Fetch All Orders with Customer Names
$orders = [];
$resOrders = $conn->query("
  SELECT o.*, u.name AS customer_name 
  FROM orders o 
  JOIN users u ON o.user_id = u.user_id 
  WHERE o.status IS NULL OR o.status != 'Delivered' 
  ORDER BY o.order_id DESC
");
while ($order = $resOrders->fetch_assoc()) {
    $items = [];
    $orderId = $order['order_id'];
    $resItems = $conn->query("
        SELECT oi.product_id, oi.quantity, oi.price, i.name AS product_name
        FROM order_items oi
        JOIN inventory i ON oi.product_id = i.inventory_id
        WHERE oi.order_id = $orderId
    ");
    while ($item = $resItems->fetch_assoc()) {
        $items[] = $item;
    }

    $orders[] = [
        'order_id' => $orderId,
        'order_date' => $order['order_date'],
        'total_amount' => $order['total_amount'],
        'customer_name' => $order['customer_name'],
        'items' => $items
    ];
}

// Fetch All Invoices with Customer Names
$invoiceResult = $conn->query("
  SELECT i.invoice_id, i.order_id, i.invoice_date, o.total_amount, u.name AS customer_name
  FROM invoices i 
  JOIN orders o ON i.order_id = o.order_id 
  JOIN users u ON o.user_id = u.user_id 
  ORDER BY i.invoice_id DESC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Orders | Sameera Super</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f4f8;
      margin: 0;
      padding: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: center;
    }

    th {
      background-color: #e1ecf4;
    }

    .status-select {
      padding: 6px;
      border-radius: 5px;
    }

    .action-btn {
      background-color: #ff9900;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin: 2px;
      min-width: 80px;
      max-width: 100px;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .cancel-btn {
      background-color: #d9534f;
    }

    .cancel-btn:hover {
      background-color: #c9302c;
    }

    .action-btn:hover {
      background-color: #055f65;
    }

    .section-title {
      color: #2b6777;
      font-size: 20px;
      margin: 30px 0 10px;
    }

    .order-block {
      margin-bottom: 40px;
    }

    td > form {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100%;
      margin: 0;
      padding: 0;
    }

    td > form > button.action-btn {
      width: 100px;
      height: 35px;
    }
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
    <li><a href="inventory.php">Inventory</a></li>
    <li><a href="suppliers.php">Suppliers</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="customers.html">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<main class="container">
  <h2>Order Management - Sameera Super</h2>

  <h3 class="section-title">📦 Track Orders</h3>

  <?php if (empty($orders)): ?>
    <p>No orders found.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <div class="order-block">
        <h4>Order ID: <?= $order['order_id'] ?> | Customer: <?= htmlspecialchars($order['customer_name']) ?> | Date: <?= $order['order_date'] ?> | Total: Rs. <?= number_format($order['total_amount'], 2) ?></h4>
        <table>
          <thead>
            <tr>
              <th>Customer</th>
              <th>Product Id</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Subtotal</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= $item['product_id'] ?></td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                <td><select class="status-select"><option selected>Pending</option><option>Delivered</option></select></td>
                <td>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                    <input type="hidden" name="delete_order_id" value="<?= $order['order_id'] ?>">
                    <input type="hidden" name="delete_product_id" value="<?= $item['product_id'] ?>">
                    <button class="action-btn" type="submit" name="delete_item">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="action-buttons">
          <form method="POST">
            <input type="hidden" name="deliver_order_id" value="<?= $order['order_id'] ?>">
            <button class="action-btn" type="submit">Delivered</button>
          </form>
          <form method="POST" onsubmit="return confirm('Are you sure to cancel this order?')">
            <input type="hidden" name="cancel_order_id" value="<?= $order['order_id'] ?>">
            <button class="action-btn cancel-btn" type="submit">Cancel</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($invoiceResult->num_rows > 0): ?>
    <h3 class="section-title">📄 Invoice Summary</h3>
    <table>
      <thead>
        <tr>
          <th>Invoice ID</th>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Total (LKR)</th>
          <th>Download</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($invoice = $invoiceResult->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($invoice['invoice_id']) ?></td>
            <td><?= htmlspecialchars($invoice['order_id']) ?></td>
            <td><?= htmlspecialchars($invoice['customer_name']) ?></td>
            <td><?= $invoice['invoice_date'] ?></td>
            <td><?= number_format($invoice['total_amount'], 2) ?></td>
            <td>
              <form action="generate_invoice.php" method="GET">
                <input type="hidden" name="order_id" value="<?= $invoice['order_id'] ?>">
                <button class="action-btn" type="submit">Download</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>

</body>
</html>
