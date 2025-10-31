<?php
session_start();
include("config.php");

header('Content-Type: application/json');

// Check kung naka-login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$menu_id = $_POST['menu_id'] ?? 0;
$sugar_level = $_POST['sugar_level'] ?? '100%'; // Default kung walang input

if (!$menu_id) {
    echo json_encode(["status" => "error", "message" => "No menu_id provided"]);
    exit;
}

// Hanapin kung may pending order
$order = $conn->query("SELECT id FROM orders WHERE user_id=$user_id AND status='pending' LIMIT 1");
if ($order->num_rows > 0) {
    $order_id = $order->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO orders (user_id, status) VALUES ($user_id, 'pending')");
    $order_id = $conn->insert_id;
}

// Kunin category ng menu item para malaman kung drink o hindi
$menu_info = $conn->query("SELECT category FROM menu WHERE id=$menu_id LIMIT 1");
$menu_category = ($menu_info->num_rows > 0) ? strtolower($menu_info->fetch_assoc()['category']) : '';

// Kung beverages, i-save ang sugar level; kung hindi, default sa 100%
if ($menu_category !== 'beverages') {
    $sugar_level = '100%';
}

// I-check kung existing na yung item na may parehong sugar level (para ma-grupo nang tama)
$check = $conn->query("
    SELECT id, quantity FROM order_items 
    WHERE order_id=$order_id AND menu_id=$menu_id AND sugar_level='$sugar_level'
");

if ($check->num_rows > 0) {
    // Kung meron na, dagdagan lang quantity
    $row = $check->fetch_assoc();
    $new_qty = $row['quantity'] + 1;
    $conn->query("UPDATE order_items SET quantity=$new_qty WHERE id=" . $row['id']);
} else {
    // Kung wala pa, insert bagong row na may sugar level
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, sugar_level) VALUES (?, ?, 1, ?)");
    $stmt->bind_param("iis", $order_id, $menu_id, $sugar_level);
    $stmt->execute();
}

echo json_encode(["status" => "success", "message" => "Item added successfully"]);
?>
