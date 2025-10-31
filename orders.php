<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
</head>
<body>

<h2>📋 Current Orders</h2>
<table>
    <tr>
        <th>Order ID</th>
        <th>Menu Item</th>
        <th>Quantity</th>
        <th>Date</th>
        <th>Status</th>
    </tr>
    <?php
    $orders = $conn->query("
        SELECT o.id as order_id, m.name, oi.quantity, o.order_date, o.status
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN menu m ON oi.menu_id = m.id
        ORDER BY o.order_date DESC
    ");
    while ($row = $orders->fetch_assoc()) {
        echo "<tr>
            <td>".$row['order_id']."</td>
            <td>".$row['name']."</td>
            <td>".$row['quantity']."</td>
            <td>".$row['order_date']."</td>
            <td>".$row['status']."</td>
        </tr>";
    }
    ?>
</table>

<br>
<a href="index.php">⬅ Back to Dashboard</a>

</body>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding:20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: maroon; color: white; }
    </style>
</html>
