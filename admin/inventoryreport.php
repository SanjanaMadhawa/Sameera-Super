<?php
require_once 'connection.php'; // use your DB connection

if (isset($_POST['report_csv']) && isset($_POST['report_type'])) {
    $reportType = $_POST['report_type'];

    // Tell the browser this is a CSV file to download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $reportType . '_report.csv');

    // Open a write stream to output
    $output = fopen("php://output", "w");

    // Write column headers
    fputcsv($output, ["ID", "Name", "Price", "Expiry Date", "Stock"]);

    // Pick query based on selected report type
    if ($reportType === "low_stock") {
        $query = "SELECT inventory_id, name, price, expiry_date, stock 
                  FROM inventory WHERE stock < 10";
    } elseif ($reportType === "expired") {
        $query = "SELECT inventory_id, name, price, expiry_date, stock 
                  FROM inventory WHERE expiry_date < CURDATE()";
    } else { // full inventory
        $query = "SELECT inventory_id, name, price, expiry_date, stock FROM inventory";
    }

    // Fetch data
    $result = $conn->query($query);

    // Write rows into CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
?>
