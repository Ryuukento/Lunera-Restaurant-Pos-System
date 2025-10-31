<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$status = ($_POST['status'] ?? '') === '1' ? 'Available' : 'Unavailable';

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE menu SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error','message'=>'DB error']);
    exit;
}
echo json_encode(['status'=>'error','message'=>'Invalid data']);
