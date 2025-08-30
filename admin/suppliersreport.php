<?php
require_once 'connection.php'; 

if (isset($_POST['report_csv']) && isset($_POST['report_type'])) {
    $reportType = $_POST['report_type'];

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $reportType . '_suppliers_report.csv');

    $output = fopen("php://output", "w");

    // Write column headers
    fputcsv($output, ["ID", "Name", "Contact", "Address", "Products", "Rating"]);

    // Select query based on report type
    if ($reportType === "high_rating") {
        $query = "SELECT s.supplier_id, s.name, s.contact, s.address, s.products, pf.rating 
                  FROM suppliers s 
                  LEFT JOIN supplier_performance pf ON s.supplier_id = pf.supplier_id 
                  WHERE pf.rating='Excellent'";
    } elseif ($reportType === "low_rating") {
        $query = "SELECT s.supplier_id, s.name, s.contact, s.address, s.products, pf.rating 
                  FROM suppliers s 
                  LEFT JOIN supplier_performance pf ON s.supplier_id = pf.supplier_id 
                  WHERE pf.rating='Bad'";
    } else { 
        $query = "SELECT s.supplier_id, s.name, s.contact, s.address, s.products, 
                  COALESCE(pf.rating, 'N/A') AS rating 
                  FROM suppliers s 
                  LEFT JOIN supplier_performance pf ON s.supplier_id = pf.supplier_id";
    }

    $result = $conn->query($query);

    // Write rows into CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
?>
