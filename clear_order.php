<?php
session_start();
include("config.php");

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id) {
    // Kunin pending order at burahin
    $order = $conn->query("SELECT id FROM orders WHERE user_id=$user_id AND status='pending' LIMIT 1");
    if ($order->num_rows > 0) {
        $order_id = $order->fetch_assoc()['id'];
        $conn->query("DELETE FROM order_items WHERE order_id=$order_id");
        $conn->query("UPDATE orders SET status='completed' WHERE id=$order_id");
    }
}
?>
