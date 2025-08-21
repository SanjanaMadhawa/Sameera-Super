<?php
require_once 'connection.php';
require_once '../vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    die("Order ID is required.");
}

$orderId = intval($_GET['order_id']);

// Fetch order details
$stmtOrder = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, u.name AS customer_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$stmtOrder->bind_param("i", $orderId);
$stmtOrder->execute();
$order = $stmtOrder->get_result()->fetch_assoc();
$stmtOrder->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$stmtItems = $conn->prepare("
    SELECT oi.product_id, i.name AS product_name, oi.quantity, oi.price
    FROM order_items oi
    JOIN inventory i ON oi.product_id = i.inventory_id
    WHERE oi.order_id = ?
");
$stmtItems->bind_param("i", $orderId);
$stmtItems->execute();
$resItems = $stmtItems->get_result();

$itemsHtml = "";
while ($item = $resItems->fetch_assoc()) {
    $subtotal = $item['quantity'] * $item['price'];
    $itemsHtml .= "
        <tr>
            <td>{$item['product_id']}</td>
            <td>{$item['product_name']}</td>
            <td>{$item['quantity']}</td>
            <td>Rs. " . number_format($item['price'], 2) . "</td>
            <td>Rs. " . number_format($subtotal, 2) . "</td>
        </tr>
    ";
}
$stmtItems->close();

// Build simple HTML invoice
$html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        h2 { text-align: center; color: #2b6777; margin-bottom: 20px; }
        .customer-info { margin: 10px 0 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        tfoot td { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h2>Sameera Super - Invoice</h2>
    
    <div class='customer-info'>
        <p><strong>Invoice Date:</strong> " . date("Y-m-d") . "</p>
        <p><strong>Order ID:</strong> {$order['order_id']}</p>
        <p><strong>Customer:</strong> {$order['customer_name']} ({$order['email']})</p>
        <p><strong>Order Date:</strong> {$order['order_date']}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            $itemsHtml
        </tbody>
        <tfoot>
            <tr>
                <td colspan='4' style='text-align:right'>Total</td>
                <td>Rs. " . number_format($order['total_amount'], 2) . "</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
";

// Initialize Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output as download
$dompdf->stream("invoice_order_{$orderId}.pdf", ["Attachment" => true]);
?>
