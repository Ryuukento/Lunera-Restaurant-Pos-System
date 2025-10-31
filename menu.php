<?php
include("connection.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // kunin current status
    $result = $conn->query("SELECT status FROM menu WHERE id=$id");
    $row = $result->fetch_assoc();
    $newStatus = ($row['status'] === 'available') ? 'unavailable' : 'available';

    // update status
    $conn->query("UPDATE menu SET status='$newStatus' WHERE id=$id");
}

header("Location: index.php");
exit;
?>
