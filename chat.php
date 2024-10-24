<?php
require_once 'db_functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $chatData = [
            'user_id' => $user_id,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        insertRecord('chat_messages', $chatData);
    }
}

$messages = getRecords('chat_messages', [], 'timestamp ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Chat</title>
    <style>
        .chat-container {
            height: 400px;
            overflow-y: scroll;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .message {
            margin-bottom: 10px;
            padding: 5px;
            border-radius: 5px;
        }
        .user-message {
            background-color: #e6f2ff;
            text-align: right;
        }
        .tech-message {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Chat</h2>
        <div class="chat-container mb-3">
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['user_id'] == $user_id ? 'user-message' : 'tech-message'; ?>">
                    <strong><?php echo $message['user_id'] == $user_id ? 'Anda' : 'Teknisi'; ?>:</strong>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <small class="text-muted"><?php echo $message['timestamp']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="POST">
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="message" placeholder="Ketik pesan Anda...">
                <button class="btn btn-primary" type="submit">Kirim</button>
            </div>
        </form>
        <a href="<?php echo $user_role == 'user' ? 'landing.php' : 'service.php'; ?>" class="btn btn-secondary">Kembali</a>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>