<?php
require_once 'connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_csv'], $_POST['report_type'])) {
    $reportType = $_POST['report_type'];

    // Make sure no previous output corrupts CSV
    if (ob_get_level()) { ob_end_clean(); }
    mysqli_set_charset($conn, 'utf8mb4');

    // Base query: active customers
    $sql = "SELECT user_id, name, email, phone, address FROM users WHERE is_active = 1";

    // Only if you have a date column, enable this:
    if ($reportType === "recent") {
        // ⚠️ Change 'created_at' to your actual column name (e.g., registered_date)
        $dateColumn = "created_at"; // <-- replace with your actual column if exists
        $check = $conn->query("SHOW COLUMNS FROM users LIKE '$dateColumn'");
        if ($check && $check->num_rows > 0) {
            $sql .= " AND $dateColumn >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        } else {
            // If no date column exists, fallback to all customers
            $sql = "SELECT user_id, name, email, phone, address FROM users WHERE is_active = 1";
        }
    }

    $result = $conn->query($sql);
    if (!$result) {
        http_response_code(500);
        echo "Query failed: " . $conn->error;
        exit;
    }

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $reportType . '_customers.csv');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    $out = fopen('php://output', 'w');

    // Column headers
    fputcsv($out, ["ID", "Full Name", "Email", "Phone", "Address"]);

    // Write rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row['user_id'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['address']
        ]);
    }

    fclose($out);
    exit;
}

http_response_code(400);
echo "Invalid request";
