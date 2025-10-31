<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $payment_method = $_POST['payment_method'];

    // Step 1: Update order status to "paid"
    $stmt = $conn->prepare("UPDATE orders SET status='paid', payment_method=? WHERE id=?");
    $stmt->bind_param("si", $payment_method, $order_id);
    $stmt->execute();

    // Step 2: Clear order_items for this order (optional kung gusto mong itago history)
    $conn->query("DELETE FROM order_items WHERE order_id=$order_id");

    echo "<script>
        alert('Payment successful via $payment_method! Thank you.');
        window.location.href='index.php';
    </script>";
}
?>
