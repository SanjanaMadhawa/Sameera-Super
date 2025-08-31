<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// ---- Deliver Order ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver_order_id'])) {
    $deliverOrderId = intval($_POST['deliver_order_id']);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE order_items SET status='Ready' WHERE order_id = ?");
        $stmt->bind_param("i", $deliverOrderId); 
        $stmt->execute(); 
        $stmt->close();

        $stmt = $conn->prepare("UPDATE orders SET status='Ready' WHERE order_id = ?");
        $stmt->bind_param("i", $deliverOrderId); 
        $stmt->execute(); 
        $stmt->close();

        $stmt = $conn->prepare("UPDATE cart_details SET status='Ready' WHERE order_id = ?");
        $stmt->bind_param("i", $deliverOrderId); 
        $stmt->execute(); 
        $stmt->close();

        // create invoice if missing
        $checkInvoice = $conn->prepare("SELECT invoice_id FROM invoices WHERE order_id = ?");
        $checkInvoice->bind_param("i", $deliverOrderId);
        $checkInvoice->execute();
        $resInv = $checkInvoice->get_result();
        if ($resInv->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO invoices (order_id, user_id, invoice_date) VALUES (?, (SELECT user_id FROM orders WHERE order_id = ?), CURDATE())");
            $ins->bind_param("ii", $deliverOrderId, $deliverOrderId);
            $ins->execute();
            $ins->close();
        }
        $checkInvoice->close();
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
    }
    header("Location: orders.php");
    exit();
}

// ---- Cancel Order ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancelOrderId = intval($_POST['cancel_order_id']);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE order_items SET status='Cancelled' WHERE order_id = ?");
        $stmt->bind_param("i", $cancelOrderId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE orders SET status='Cancelled' WHERE order_id = ?");
        $stmt->bind_param("i", $cancelOrderId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE cart_details SET status='Cancelled' WHERE order_id = ?");
        $stmt->bind_param("i", $cancelOrderId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
    }
    header("Location: orders.php");
    exit();
}

// ---- Update Quantities ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $updOrderId = intval($_POST['update_order_id']);
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        $conn->begin_transaction();
        try {
            foreach ($_POST['quantities'] as $productId => $quantity) {
                $quantity = intval($quantity);
                if ($quantity > 0) {
                    $stmt = $conn->prepare("UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $quantity, $updOrderId, $productId);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE cart_details SET quantity = ? WHERE order_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $quantity, $updOrderId, $productId);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $stmtTotal = $conn->prepare("SELECT SUM(price * quantity) AS new_total FROM order_items WHERE order_id = ?");
            $stmtTotal->bind_param("i", $updOrderId);
            $stmtTotal->execute();
            $resTotal = $stmtTotal->get_result();
            $newTotal = $resTotal->fetch_assoc()['new_total'] ?? 0;
            $stmtTotal->close();

            $stmtUpdTotal = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
            $stmtUpdTotal->bind_param("di", $newTotal, $updOrderId);
            $stmtUpdTotal->execute();
            $stmtUpdTotal->close();

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
        }
    }
    header("Location: orders.php");
    exit();
}

// ---- Delete single item ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $delOrderId = intval($_POST['delete_order_id']);
    $delProductId = intval($_POST['delete_product_id']);

    $conn->begin_transaction();
    try {
        $stmtDel = $conn->prepare("DELETE FROM order_items WHERE order_id = ? AND product_id = ?");
        $stmtDel->bind_param("ii", $delOrderId, $delProductId);
        $stmtDel->execute();
        $stmtDel->close();

        $stmt = $conn->prepare("UPDATE cart_details SET status='Deleted' WHERE order_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $delOrderId, $delProductId);
        $stmt->execute();
        $stmt->close();

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

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
    }
    header("Location: orders.php");
    exit();
}

// === Fetch Orders ===
$orders = [];
$resOrders = $conn->query("
  SELECT o.*, u.name AS customer_name 
  FROM orders o 
  JOIN users u ON o.user_id = u.user_id
  ORDER BY o.order_id DESC
");
while ($order = $resOrders->fetch_assoc()) {
    $items = [];
    $orderId = $order['order_id'];
    $resItems = $conn->query("
        SELECT oi.product_id, oi.quantity, oi.price, oi.status, i.name AS product_name
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
        'items' => $items,
        'status' => $order['status'] ?? 'Pending'
    ];
}

// === Fetch Invoices ===
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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders | Sameera Super</title>
<link rel="stylesheet" href="style1.css">
<style>
/* keep your styles as-is (copied from your original) */
body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; margin: 0; padding: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccccccff; padding: 10px; text-align: center; }
th { background-color: #73a8dcff; }
td > form { display: flex; justify-content: center; align-items: center; height: 100%; margin: 0; padding: 0; }
.action-btn { background-color: #ff9900; color: white; padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; margin: 2px; min-width: 80px; max-width: 100px; }
.action-buttons { display: flex; gap: 10px; margin-top: 10px; }
.cancel-btn { background-color: #d9534f; }
.cancel-btn:hover { background-color: #b11a15ff; }
.update-btn { background-color: #055f65; }
.update-btn:hover { background-color: #ee9b00; }
.section-title { color: #2b6777; font-size: 20px; margin: 30px 0 10px; }
.order-block { margin-bottom: 40px; }
td > input[type="number"] { width: 60px; padding: 5px; border-radius: 4px; border: 1px solid #ccc; text-align: center; }
.status-cancelled { color: red; font-weight: bold; }
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
    <li><a href="customers.php">Customers</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="login.php">Login</a></li>
  </ul>
</nav>

<main class="container">
  <div class="section-box">
<h2>Order Management - Sameera Super</h2>

<div class="section-box">
<h3 class="section-title">📦 Track Orders</h3>
<?php if (empty($orders)): ?>
<p>No orders found.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php if($order['status'] !== 'Cancelled' && $order['status'] !== 'Ready'): ?>
      <div class="order-block">
        <h4>Order ID: <?= $order['order_id'] ?> | Customer: <?= htmlspecialchars($order['customer_name']) ?> | Date: <?= $order['order_date'] ?> | Total: Rs. <?= number_format($order['total_amount'], 2) ?> | Status: <?= $order['status'] ?></h4>
        
        <!-- Update form starts -->
        <form method="POST">
          <input type="hidden" name="update_order_id" value="<?= $order['order_id'] ?>">

          <table>
            <thead>
              <tr>
                <th>Customer</th><th>Product Id</th><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= $item['product_id'] ?></td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td>
                  <input type="number" name="quantities[<?= $item['product_id'] ?>]" value="<?= $item['quantity'] ?>" min="1">
                </td>
                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                <td>
                  <!-- Delete button has its own form -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_order_id" value="<?= $order['order_id'] ?>">
                    <input type="hidden" name="delete_product_id" value="<?= $item['product_id'] ?>">
                    <button class="action-btn" type="submit" name="delete_item">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- Update + Cancel + Delivered buttons inside the same form -->
          <div class="action-buttons">
            <button class="action-btn update-btn" type="submit" name="update_order">Update</button>

            <button class="action-btn cancel-btn" type="submit" name="cancel_order_id" value="<?= $order['order_id'] ?>">Cancel</button>

            <button class="action-btn" type="submit" name="deliver_order_id" value="<?= $order['order_id'] ?>">Delivered</button>
          </div>
        </form>
        <!-- Update form ends -->

      </div>
      <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</div>


<!-- Invoice Summary -->
<div class="section-box">

<?php if ($invoiceResult->num_rows > 0): ?>
<h3>📄 Invoice Summary</h3>
<table>
  <thead><tr><th>Invoice ID</th><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Download</th></tr></thead>
  <tbody>
    <?php while ($invoice = $invoiceResult->fetch_assoc()): ?>
      <tr>
        <td><?= $invoice['invoice_id'] ?></td>
        <td><?= $invoice['order_id'] ?></td>
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