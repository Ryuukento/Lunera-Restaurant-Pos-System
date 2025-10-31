<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? 1; // fallback kung walang session

// Hanapin yung pending order ng user
$order = $conn->query("SELECT id FROM orders WHERE user_id=$user_id AND status='pending' LIMIT 1");

if ($order->num_rows > 0) {
    $orderRow = $order->fetch_assoc();
    $order_id = $orderRow['id'];

    // Kunin lahat ng items sa order na yun
    $items = $conn->query("
        SELECT oi.id as order_item_id, m.name, m.price, m.category, oi.quantity, oi.sugar_level, m.image 
        FROM order_items oi 
        JOIN menu m ON oi.menu_id = m.id 
        WHERE oi.order_id = $order_id
    ");

    if ($items->num_rows > 0) {
        echo "<div style='max-height:400px; overflow-y:auto;'>";

        $grandTotal = 0;

        while ($row = $items->fetch_assoc()) {
            $imageFile = !empty($row['image']) ? 'images/' . htmlspecialchars($row['image']) : 'images/placeholder.png';
            $subtotal = $row['price'] * $row['quantity'];
            $grandTotal += $subtotal;

            // 🧃 Ipakita lang ang sugar level kung Beverages
            $sugarInfo = '';
            if ($row['category'] === 'Beverages') {
                $sugarInfo = "<div class='sugar-info' style='color:#a33; font-size:13px; margin-top:3px;'>Sugar Level: " . htmlspecialchars($row['sugar_level']) . "</div>";
            }

            echo "
            <div class='order-item' style='display:flex; align-items:center; margin-bottom:15px; border-bottom:1px solid #ddd; padding-bottom:10px;'>
                <img src='$imageFile' alt='" . htmlspecialchars($row['name']) . "' 
                     style='width:60px; height:60px; object-fit:cover; border-radius:8px; margin-right:10px;' 
                     onerror=\"this.src='images/placeholder.png'\">
                <div style='flex:1;'>
                    <b class='item-name'>" . htmlspecialchars($row['name']) . "</b><br>
                    <span class='item-price'>₱" . number_format($row['price'], 2) . "</span>
                    $sugarInfo
                </div>
                <div style='display:flex; align-items:center; gap:5px;'>
                    <button onclick=\"updateOrder({$row['order_item_id']}, 'add')\" 
                        style='background:#800000; color:#fff; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-weight:bold;'>+</button>
                   <span class='qty-display' style='min-width:25px; text-align:center; font-weight:bold; color:#333;'>{$row['quantity']}</span>
                    <button onclick=\"updateOrder({$row['order_item_id']}, 'minus')\" 
                        style='background:#555; color:#fff; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-weight:bold;'>-</button>
                    <button class='delete-btn' onclick=\"updateOrder({$row['order_item_id']}, 'delete')\" 
                        style='background:none; border:none; cursor:pointer; font-size:16px;'>🗑️</button>
                </div>
            </div>";
        }

        echo "</div>";

        // Sticky footer for total + proceed
        echo "
        <div style='position:sticky; bottom:0; background:#fff; border-top:2px solid #ddd; padding:15px; text-align:center;'>
            <div style='text-align:right; font-weight:bold; font-size:16px; margin-bottom:10px;'>
                Total: ₱" . number_format($grandTotal, 2) . "
            </div>
            <button type='button' onclick=\"openPaymentPopup($grandTotal)\" 
                style='display:inline-block; background:#800000; color:#fff; padding:10px 20px; border-radius:5px; border:none; cursor:pointer; font-size:16px;'>
                Proceed to Payment
            </button>
        </div>";
    } else {
        echo "<p>No items yet.</p>";
    }
} else {
    echo "<p>No active order.</p>";
}
?>
