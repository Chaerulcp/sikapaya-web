<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Inisialisasi pesan error
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['error']); // Clear the error message after displaying

// Fungsi untuk mendapatkan nama admin
function getAdminName($db, $admin_id) {
    $stmt = $db->prepare("SELECT nama FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$admin_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['nama'] : 'Unknown Admin';
}

// Ambil semua sesi chat aktif
$stmt = $db->prepare("SELECT cs.id as session_id, 
                            u.nama as user_name, 
                            cs.created_at, 
                            cs.admin_id,
                            (SELECT COUNT(*) FROM chats WHERE session_id = cs.id) as message_count
                     FROM chat_sessions cs 
                     JOIN users u ON cs.user_id = u.id 
                     WHERE cs.is_active = TRUE 
                     AND (cs.admin_id IS NULL OR cs.admin_id = ?) 
                     ORDER BY cs.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$activeSessions = $stmt->fetchAll();

// Proses untuk join chat
if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];

    try {
        // Cek apakah sesi chat masih aktif dan belum ada admin yang menangani
        $stmt = $db->prepare("SELECT * FROM chat_sessions WHERE id = ? AND is_active = TRUE");
        $stmt->execute([$session_id]);
        $chatSession = $stmt->fetch();

        if ($chatSession) {
            // Update admin_id jika belum ada admin yang menangani
            if (!$chatSession['admin_id']) {
                $stmt = $db->prepare("UPDATE chat_sessions SET admin_id = ? WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], $session_id]);

                // Tambahkan pesan sistem bahwa admin telah bergabung
                $admin_name = getAdminName($db, $_SESSION['user_id']);
                $joinMessage = "Admin {$admin_name} telah bergabung dalam chat";
                $stmt = $db->prepare("INSERT INTO chats (session_id, sender_id, message, is_system_message) 
                                    VALUES (?, ?, ?, TRUE)");
                $stmt->execute([$session_id, $_SESSION['user_id'], $joinMessage]);

                // Eksekusi chat_bot.php
                $_SESSION['current_chat_session'] = $session_id; // Set session untuk chat_bot.php
                include 'chat_bot.php';
            }

            // Ambil semua pesan dalam sesi ini
            $stmt = $db->prepare("SELECT c.*, u.nama, u.role, c.is_system_message
                                FROM chats c 
                                JOIN users u ON c.sender_id = u.id 
                                WHERE c.session_id = ? 
                                ORDER BY c.created_at ASC");
            $stmt->execute([$session_id]);
            $messages = $stmt->fetchAll();
        } else {
            $_SESSION['error'] = "Sesi chat tidak ditemukan atau sudah tidak aktif.";
            header("Location: admin_chat.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: admin_chat.php");
        exit();
    }
}

// Proses pengiriman pesan
if (isset($_POST['send_message']) && !empty($_POST['message'])) {
    $messageText = $_POST['message'];
    $sender_id = $_SESSION['user_id'];
    $session_id = $_POST['session_id'];

    try {
        $stmt = $db->prepare("INSERT INTO chats (sender_id, message, session_id) VALUES (?, ?, ?)");
        $stmt->execute([$sender_id, $messageText, $session_id]);

        header("Location: admin_chat.php?session_id={$session_id}");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan saat mengirim pesan: " . $e->getMessage();
    }
}

// Proses pengakhiran chat
if (isset($_POST['end_chat'])) {
    $session_id = $_POST['session_id'];

    try {
        // Dapatkan nama admin
        $admin_name = getAdminName($db, $_SESSION['user_id']);

        // Mulai transaksi
        $db->beginTransaction();

        // Update status chat menjadi tidak aktif
        $stmt = $db->prepare("UPDATE chat_sessions SET is_active = FALSE WHERE id = ?");
        $stmt->execute([$session_id]);

        // Dapatkan informasi user
        $stmt = $db->prepare("SELECT user_id FROM chat_sessions WHERE id = ?");
        $stmt->execute([$session_id]);
        $chat_user = $stmt->fetch();

        if ($chat_user) {
            // Tambahkan pesan sistem tentang pengakhiran chat
            $endMessage = "Chat telah diakhiri oleh admin {$admin_name}";
            $stmt = $db->prepare("INSERT INTO chats (session_id, sender_id, message, is_system_message) 
                                VALUES (?, ?, ?, TRUE)");
            $stmt->execute([$session_id, $_SESSION['user_id'], $endMessage]);

            // Tambahkan notifikasi untuk user
            $stmt = $db->prepare("INSERT INTO notifications (user_id, message, type) 
                                VALUES (?, ?, 'chat_ended')");
            $stmt->execute([$chat_user['user_id'], $endMessage]);
        }

        $db->commit();

        header("Location: admin_chat.php");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>Admin Chat</title>
    <style>
        .chat-container {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
        }
        .message.user {
            background-color: #007bff;
            color: white;
            margin-left: 20%;
        }
        .message.admin {
            background-color: #e9ecef;
            margin-right: 20%;
        }
        .message-info {
            font-size: 0.8em;
            margin-bottom: 5px;
        }
        .message-time {
            font-size: 0.7em;
            color: #666;
        }
        .active-chats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .chat-session {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0,  0, 0.1);
        }
        .chat-session:hover {
            background: #f0f0f0;
        }
        .system-message {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">Sikapaiyya</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="service.php">Service List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white position-relative" href="admin_chat.php">
                            Chat
                            <?php 
                            // Hitung jumlah chat aktif yang belum ditangani admin lain
                            $stmt = $db->prepare("SELECT COUNT(*) FROM chat_sessions 
                                               WHERE is_active = TRUE 
                                               AND (admin_id IS NULL OR admin_id = ?)");
                            $stmt->execute([$_SESSION['user_id']]);
                            $activeChats = $stmt->fetchColumn();
                            if($activeChats > 0): 
                            ?>
                                <span class="badge bg-danger"><?= $activeChats ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown">
                            Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item text-primary" href="profil_admin.php">Profile</a></li>
                            <li><a class="dropdown-item text-primary" href="logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Active Chats -->
    <div class="container" style="margin-top: 100px;">
        <h2 style="margin-bottom: 20px;">Active Chats</h2>
        <div class="active-chats">
            <?php if (empty($activeSessions)): ?>
                <div class="alert alert-info">Tidak ada chat aktif saat ini.</div>
            <?php else: ?>
                <?php foreach ($activeSessions as $session): ?>
                    <div class="chat-session">
                        <h5><?= htmlspecialchars($session['user_name']); ?></h5>
                        <p><?= date('d/m/Y H:i', strtotime($session['created_at'])); ?></p>
                        <p><?= $session['message_count']; ?> pesan</p>
                        <?php if (!$session['admin_id']): ?>
                            <a href="admin_chat.php?session_id=<?= $session['session_id']; ?>" 
                               class="btn btn-primary btn-sm">Join Chat</a>
                        <?php elseif ($session['admin_id'] == $_SESSION['user_id']): ?>
                            <a href="admin_chat.php?session_id=<?= $session['session_id']; ?>" 
                               class="btn btn-success btn-sm">Lanjutkan Chat</a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>Ditangani Admin Lain</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Container -->
        <?php if (isset($session_id)): ?>
            <div class="chat-container">
                <?php foreach ($messages as $message): ?>
                    <?php if (isset($message['is_system_message']) && $message['is_system_message']): ?>
                        <div class="system-message">
                            <?= htmlspecialchars($message['message']); ?>
                        </div>
                    <?php else: ?>
                        <div class="message <?= $message['role'] == 'admin' ? 'admin' : 'user'; ?>">
                            <p class="message-info"><?= htmlspecialchars($message['nama']); ?> (<?= htmlspecialchars($message['role']); ?>)</p>
                            <p class="message-time"><?= htmlspecialchars($message['created_at']); ?></p>
                            <p><?= htmlspecialchars($message['message']); ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Send Message Form -->
            <form action="admin_chat.php" method="post" id="messageForm">
                <input type="hidden" name="session_id" value="<?= $session_id; ?>">
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea class="form-control" id="message" name="message" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-primary">Send</button>
            </form>

            <!-- End Chat Form - Terpis ah dari form pesan -->
            <form action="admin_chat.php" method="post" id="endChatForm" style="margin-top: 10px;">
                <input type="hidden" name="session_id" value="<?= $session_id; ?>">
                <button type="submit" name="end_chat" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to end this chat session?')">
                    End Chat
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll ke bagian bawah chat
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Auto refresh setiap 10 detik jika tidak ada chat yang aktif
            <?php if (!isset($session_id)): ?>
            setInterval(function() {
                if (!document.hidden) {
                    location.reload();
                }
            }, 10000);
            <?php endif; ?>

            // Auto refresh untuk chat yang sedang aktif
            <?php if (isset($session_id)): ?>
            setInterval(function() {
                if (!document.hidden) {
                    fetch('get_messages.php?session_id=<?= $session_id ?>')
                        .then(response => response.json())
                        .then(data => {
                            if (data.messages) {
                                updateChatContainer(data.messages);
                            }
                        });
                }
            }, 5000);
            <?php endif; ?>
        });

        function updateChatContainer(messages) {
            const chatContainer = document.querySelector('.chat-container');
            if (!chatContainer) return;

            let html = '';
            messages.forEach(message => {
                if (message.is_system_message) {
                    html += `<div class="system-message">${escapeHtml(message.message)}</div>`;
                } else {
                    html += `
                        <div class="message ${message.role === 'admin' ? 'admin' : 'user'}">
                            <p class="message-info">${escapeHtml(message.nama)} (${escapeHtml(message.role)})</p>
                            <p class="message-time">${escapeHtml(message.created_at)}</p>
                            <p>${escapeHtml(message.message)}</p>
                        </div>
                    `;
                }
            });
            chatContainer.innerHTML = html;
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>