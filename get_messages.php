<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

if (!isset($_GET['session_id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Session ID required']));
}

try {
    $stmt = $db->prepare("SELECT c.*, u.nama, u.role, c.is_system_message
                         FROM chats c 
                         JOIN users u ON c.sender_id = u.id 
                         WHERE c.session_id = ? 
                         ORDER BY c.created_at ASC");
    $stmt->execute([$_GET['session_id']]);
    $messages = $stmt->fetchAll();

    exit(json_encode(['messages' => $messages]));
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]));
}