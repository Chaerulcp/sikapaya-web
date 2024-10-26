<?php
require_once 'config.php';

$receiverId = $_GET['receiver_id'];
$message = $_GET['message'];

$stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $receiverId, $message]);

echo json_encode(['success' => true]);