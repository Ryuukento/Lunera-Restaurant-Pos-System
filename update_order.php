<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_item_id = intval($_POST['order_item_id']);
    $action = $_POST['action'] ?? '';

    if ($order_item_id > 0 && in_array($action, ['add', 'minus', 'delete'])) {
        // Kunin ang kasalukuyang item
        $item = $conn->query("SELECT quantity FROM order_items WHERE id=$order_item_id LIMIT 1");
        if ($item && $item->num_rows > 0) {
            $row = $item->fetch_assoc();
            $quantity = (int)$row['quantity'];

            if ($action === 'add') {
                $quantity++;
                $conn->query("UPDATE order_items SET quantity=$quantity WHERE id=$order_item_id");
            } elseif ($action === 'minus') {
                if ($quantity > 1) {
                    $quantity--;
                    $conn->query("UPDATE order_items SET quantity=$quantity WHERE id=$order_item_id");
                } else {
                    // kung 0 na, tanggalin item
                    $conn->query("DELETE FROM order_items WHERE id=$order_item_id");
                }
            } elseif ($action === 'delete') {
                // Diretso delete buong item
                $conn->query("DELETE FROM order_items WHERE id=$order_item_id");
            }

            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}

// fallback error
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
