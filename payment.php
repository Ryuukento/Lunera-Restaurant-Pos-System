<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? 1;

// Hanapin yung pending order
$order = $conn->query("SELECT id FROM orders WHERE user_id=$user_id AND status='pending' LIMIT 1");

if ($order->num_rows > 0) {
    $orderRow = $order->fetch_assoc();
    $order_id = $orderRow['id'];

    // Kunin lahat ng items
    $items = $conn->query("
        SELECT m.name, m.price, oi.quantity, m.image 
        FROM order_items oi 
        JOIN menu m ON oi.menu_id = m.id 
        WHERE oi.order_id = $order_id
    ");

    $total = 0;
} else {
    $order_id = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - Lunera</title>
    <link rel="stylesheet" href="assets/css/payment.css">
</head>
<body>
    <div class="container">
        <h2>Order Summary</h2>
        <?php if ($order_id && $items->num_rows > 0): ?>
            <?php while ($row = $items->fetch_assoc()): 
                $subtotal = $row['price'] * $row['quantity'];
                $total += $subtotal;
            ?>
                <div class="order-item">
                    <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                    <div>
                        <b><?= $row['name'] ?></b><br>
                        ₱<?= number_format($row['price'], 2) ?> x <?= $row['quantity'] ?> = ₱<?= number_format($subtotal, 2) ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="total">Total: ₱<?= number_format($total, 2) ?></div>

            <div class="payment-options">
                <h3>Choose Payment Method:</h3>
                <form method="post" action="process_payment.php">
                    <label>
                        <input type="radio" name="payment_method" value="Cash" required> Cash
                    </label><br>
                    <label>
                        <input type="radio" name="payment_method" value="Credit Card" required> Credit Card
                    </label><br>
                    <label>
                        <input type="radio" name="payment_method" value="Mobile Payment" required> Mobile Payment
                    </label><br>
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <button type="submit" class="btn">Confirm Payment</button>
                </form>
            </div>
        <?php else: ?>
            <p>No active order found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
