<?php
if (!isset($_GET['order_id'])) {
    die("Invalid Request");
}

$orderId = intval($_GET['order_id']);

// Generate PDF or display receipt
echo "<h2>Invoice for Order ID: $orderId</h2>";
echo "<p>(This should generate/download a PDF invoice. You can use a PDF library like FPDF or DOMPDF.)</p>";
?>
