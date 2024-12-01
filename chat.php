<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fungsi untuk memeriksa status chat
function isChatActive($chatSession)
{
    return isset($chatSession['is_active']) && $chatSession['is_active'];
}

// Fungsi untuk memeriksa apakah chat sudah berakhir
function isChatEnded($chatSession)
{
    return $chatSession && isset($chatSession['is_active']) && !$chatSession['is_active'];
}

// Fungsi untuk memeriksa pesan bot
function isBotMessage($message) {
    return isset($message['is_bot_message']) && $message['is_bot_message'];
}

// Inisialisasi variabel
$systemMessage = '';
$chatSessionId = null;
$isChatEnded = false;
$messages = [];
$chatSession = false;

// Cek apakah ada chat session untuk user ini
try {
    $stmt = $db->prepare("SELECT * FROM chat_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $chatSession = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chatSession) {
        $chatSessionId = $chatSession['id'];
        $isChatEnded = !isChatActive($chatSession);

        // Ambil pesan-pesan chat termasuk pesan bot
        if ($chatSessionId) {
            $stmt = $db->prepare("SELECT c.*, u.nama, u.role, c.is_bot_message, c.is_system_message 
                               FROM chats c 
                               LEFT JOIN users u ON c.sender_id = u.id 
                               WHERE c.session_id = ? 
                               ORDER BY c.created_at ASC");
            $stmt->execute([$chatSessionId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    $systemMessage = "Terjadi kesalahan: " . $e->getMessage();
}

// Proses pengiriman pesan
if (isset($_POST['send_message']) && !empty($_POST['message'])) {
    $messageText = $_POST['message'];
    $sender_id = $_SESSION['user_id'];

    try {
        // Jika tidak ada session atau session sebelumnya sudah tidak aktif
        if (!$chatSession || !isset($chatSession['is_active']) || !$chatSession['is_active']) {
            // Buat session baru
            $stmt = $db->prepare("INSERT INTO chat_sessions (user_id, is_active) VALUES (?, TRUE)");
            $stmt->execute([$_SESSION['user_id']]);
            $chatSessionId = $db->lastInsertId();

            // Masukkan pesan pertama
            $stmt = $db->prepare("INSERT INTO chats (sender_id, message, session_id) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $messageText, $chatSessionId]);

            // Panggil chat bot untuk respons awal
            $_SESSION['current_chat_session'] = $chatSessionId;
            include 'chat_bot.php';

            // Cari admin yang tersedia
            $stmt = $db->query("SELECT id FROM users WHERE role = 'admin' AND id NOT IN (SELECT admin_id FROM chat_sessions WHERE is_active = TRUE AND admin_id IS NOT NULL) LIMIT 1");
            $availableAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($availableAdmin) {
                $stmt = $db->prepare("UPDATE chat_sessions SET admin_id = ? WHERE id = ?");
                $stmt->execute([$availableAdmin['id'], $chatSessionId]);
                $systemMessage = "Admin telah terhubung dan akan segera merespon.";
            } else {
                $systemMessage = "Semua admin sedang sibuk. Mohon tunggu sebentar.";
            }
        } else {
            // Jika session masih aktif, tambahkan pesan baru
            $stmt = $db->prepare("INSERT INTO chats (sender_id, message, session_id) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $messageText, $chatSession['id']]);

            // Proses respons bot jika diperlukan
            $_SESSION['current_chat_session'] = $chatSession['id'];
            include 'chat_bot.php';
        }

        header("Location: chat.php");
        exit();
    } catch (PDOException $e) {
        $systemMessage = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Proses pengakhiran chat
if (isset($_POST['end_chat']) && $chatSession && isset($chatSession['id'])) {
    try {
        $stmt = $db->prepare("UPDATE chat_sessions SET is_active = FALSE WHERE id = ?");
        $stmt->execute([$chatSession['id']]);

        // Tambahkan pesan sistem bahwa chat telah berakhir
        $stmt = $db->prepare("INSERT INTO chats (session_id, sender_id, message, is_system_message) 
                            VALUES (?, ?, ?, TRUE)");
        $stmt->execute([$chatSession['id'], $_SESSION['user_id'], "Chat telah diakhiri oleh pengguna"]);

        header("Location: chat.php");
        exit();
    } catch (PDOException $e) {
        $systemMessage = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Proses memulai chat baru
if (isset($_POST['start_new_chat'])) {
    try {
        // Buat session chat baru
        $stmt = $db->prepare("INSERT INTO chat_sessions (user_id, is_active) VALUES (?, TRUE)");
        $stmt->execute([$_SESSION['user_id']]);
        $newChatSessionId = $db->lastInsertId();

        // Reset variabel
        $isChatEnded = false;
        $chatSession = [
            'id' => $newChatSessionId,
            'is_active' => true,
            'user_id' => $_SESSION['user_id']
        ];
        $messages = [];

        // Tambahkan pesan sistem
        $stmt = $db->prepare("INSERT INTO chats (session_id, sender_id, message, is_system_message) 
                            VALUES (?, ?, ?, TRUE)");
        $stmt->execute([$newChatSessionId, $_SESSION['user_id'], "Chat baru dimulai"]);


        header("Location: chat.php");
        exit();
    } catch (PDOException $e) {
        $systemMessage = "Terjadi kesalahan saat memulai chat baru: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Chat Privat</title>
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
            margin-left:  20%;
        }

        .message.admin {
            background-color: #e9ecef;
            margin-right: 20%;
        }

        .message.bot {
            background-color: #e3f2fd;
            margin-right: 20%;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .message.bot .message-info {
            color: #1976d2;
            font-weight: bold;
        }

        .message-info {
            font-size: 0.8em;
            margin-bottom: 5px;
        }

        .message-time {
            font-size: 0.7em;
            color: #aaa;
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

        .welcome-message {
            text-align: center ;
            padding: 10px;
            margin: 10px 0;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            color: #856404;
        }

        .end-chat-form {
            margin-left: 10px;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .input-group {
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Navbar-->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="landing.php">Sikapaiya</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="landing.php">Home</a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
    <!-- End Nav -->

    <div class="container" style="margin-top: 100px;">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <?php if (!empty($systemMessage)): ?>
                    <div class="system-message">
                        <?= $systemMessage ?>
                    </div>
                <?php endif; ?>

                <div class="chat-container">
                    <?php if (!$chatSession): ?>
                        <!-- Tampilan awal untuk user baru -->
                        <div class="welcome-message">
                            <h4>Selamat datang di Layanan Chat Sikapaiyya</h4>
                            <p class="mb-4">Silakan ikuti petunjuk berikut sebelum memulai chat:</p>
                            <div class="text-start mb-4" style="max-width: 500px; margin: 0 auto;">
                                <ol>
                                    <li>Pastikan Anda sudah membaca syarat dan ketentuan layanan.</li>
                                    <li>Jelaskan masalah Anda dengan detail dan jelas.</li>
                                    <li>Tunggu respon dari admin kami.</li>
                                    <li>Chat akan berakhir jika Anda menekan tombol "Akhiri Chat".</li>
                                </ol>
                            </div>
                            <p class="mb-4">Jika Anda siap untuk memulai, klik tombol di bawah ini:</p>
                            <form action="" method="post">
                                <button type="submit" name="start_new_chat" class="btn btn-primary btn-lg">
                                    Mulai Chat
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Tampilan chat yang sudah ada -->
                        <div class="mb-3">
                            <h5>Chat Anda:</h5>
                        </div>
                        <?php if (isset($messages) && count($messages) > 0): ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php if (isBotMessage($msg)): ?>
                                    <div class="message bot">
                                        <div class="message-info">Bot Sikapaiyya</div>
                                        <div class="message-time"><?= date('d/m /Y H:i', strtotime($msg['created_at'])) ?></div>
                                        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="message <?= $msg['role'] === 'user' ? 'user' : 'admin' ?>">
                                        <div class="message-info"><?= htmlspecialchars($msg['nama']) ?></div>
                                        <div class="message-time"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></div>
                                        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if ($isChatEnded): ?>
                                <div class="system-message">
                                    Chat telah berakhir
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="system-message">
                                Mulai chat dengan mengirim pesan
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (!$isChatEnded): ?>
                    <div class="mb-3" id="message-controls" style="<?= !$chatSession ? 'display: none;' : '' ?>">
                        <form action="" method="post" class="message-form" style="display: inline-block; width: 85%;">
                            <div class="input-group">
                                <textarea class="form-control" id="message" name="message" placeholder="Tulis pesan anda..."></textarea>
                                <button type="submit" name="send_message" class="btn btn-primary">Kirim</button>
                            </div>
                        </form>

                        <?php if (isset($chatSession) && $chatSession && isset($chatSession['is_active']) && $chatSession['is_active']): ?>
                            <form action="" method="post" class="end-chat-form" style="display: inline-block;">
                                <button type="button" onclick="confirmEndChat()" class="btn btn-danger">Akhiri Chat</button>
                                <input type="hidden" name="end_chat" value="1">
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <form action="chat.php" method="post" class="mb-3">
                        <button type="submit" name="start_new_chat" class="btn btn-primary btn-block w-100">Mulai Chat Baru</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan kontrol pesan
        function showMessageControls() {
            const messageControls = document.getElementById('message-controls');
            if (messageControls) {
                messageControls.style.display = 'block';
            }
        }

        // Fungsi untuk konfirmasi mengakhiri chat
        function confirmEndChat() {
            if (confirm('Apakah Anda yakin ingin mengakhiri chat ini?')) {
                document.querySelector('.end-chat-form').submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tangani submit form start new chat
            const startChatForm = document.querySelector('form[name="start_new_chat"]');
            if (startChatForm) {
                startChatForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    // Kirim form
                    this.submit();
                    // Tampilkan kontrol pesan
                    showMessageControls();
                });
            }

            // Auto-scroll ke bagian bawah chat
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Ambil referensi form dan input message
            const messageForm = document.querySelector('.message-form');
            const messageInput = document.querySelector('#message');
            let isTyping = false;

            // Deteksi ketika user mulai mengetik
            if (messageInput) {
                messageInput.addEventListener('focus', function() {
                    isTyping = true;
                });

                messageInput.addEventListener('blur', function() {
                    isTyping = false;
                });
            }

            // Validasi form untuk pengiriman pesan
            if (messageForm) {
                messageForm.addEventListener(' submit', function(e) {
                    const messageInput = this.querySelector('#message');
                    if (!messageInput.value.trim()) {
                        e.preventDefault();
                        alert('Silakan tulis pesan terlebih dahulu');
                    }
                });
            }

            // Function untuk memperbarui chat
            function updateChat() {
                if (!document.hidden && !isTyping) {
                    fetch('get_messages.php?session_id=<?php echo $chatSessionId; ?>')
                        .then(response => response.json())
                        .then(data => {
                            if (data.messages) {
                                updateChatContainer(data.messages);
                            }
                        })
                        .catch(error => console.log('Error:', error));
                }
            }

            // Fungsi untuk memperbarui container chat
            function updateChatContainer(messages) {
                if (!chatContainer) return;

                let html = '';
                messages.forEach(message => {
                    const messageClass = message.role === 'user' ? 'user' : message.role === 'admin' ? 'admin' : 'bot';
                    const time = new Date(message.created_at).toLocaleString();

                    html += `
                    <div class="message ${messageClass}">
                        <div class="message-info">${escapeHtml(message.nama)}</div>
                        <div class="message-time">${time}</div>
                        <p>${escapeHtml(message.message)}</p>
                    </div>
                `;
                });

                chatContainer.innerHTML = html;
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Function untuk escape HTML
            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Set interval untuk update chat
            setInterval(updateChat, 5000); // Update setiap 5 detik
        });
    </script>
</body>

</html>