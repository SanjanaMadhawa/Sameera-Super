<?php
require_once 'connection.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM staff WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: staff.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>
