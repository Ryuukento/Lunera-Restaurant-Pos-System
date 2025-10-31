<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(['status'=>'error','message'=>'Unauthorized']);
  exit;
}

$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($name === '') {
  echo json_encode(['status'=>'error','message'=>'Name required']); exit;
}

$stmt = $conn->prepare("INSERT INTO menu (name, description, price, image, status, category) VALUES (?, ?, ?, 'noimage.jpg', 'Available', ?)");
$stmt->bind_param('sdss', $name, $description, $price, $category);
if ($stmt->execute()) echo json_encode(['status'=>'success']);
else echo json_encode(['status'=>'error','message'=>'DB error']);
