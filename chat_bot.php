<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Tidak perlu mendeklarasikan ulang fungsi getAdminName() karena sudah ada di admin_chat.php

// Inisialisasi variabel
$systemMessage = '';
$chatSessionId = isset($_SESSION['current_chat_session']) ? $_SESSION['current_chat_session'] : null;

try {
    // Jika ada chat session
    if ($chatSessionId) {
        $stmt = $db->prepare("SELECT * FROM chat_sessions WHERE id = ?");
        $stmt->execute([$chatSessionId]);
        $chatSession = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chatSession) {
            $sender_id = $_SESSION['user_id'];
            
            // Kirim pesan selamat datang
            $welcomeMessage = "Selamat datang di Layanan Chat Sikapaiyya! 👋\n\nSaya Bot Sikapaiyya, siap membantu Anda.";
            $stmt = $db->prepare("INSERT INTO chats (sender_id, message, session_id, is_bot_message) VALUES (?, ?, ?, TRUE)");
            $stmt->execute([$sender_id, $welcomeMessage, $chatSessionId]);

            // Kirim menu layanan
            $serviceMessage = "Silakan pilih layanan yang Anda butuhkan dengan mengetik nomornya:

1️⃣ Konsultasi Service Elektronik
2️⃣ Cek Status Service
3️⃣ Form Service Baru
4️⃣ Riwayat Service
5️⃣ Berbicara dengan Admin

Atau ketik 'MENU' untuk melihat daftar layanan lengkap.";
            $stmt->execute([$sender_id, $serviceMessage, $chatSessionId]);

            // Kirim jam operasional
            $operationalMessage = "⏰ Jam Operasional Service:
Senin - Jumat: 08.00 - 17.00 WITA
Sabtu: 08.00 - 15.00 WITA
Minggu & Hari Libur: Tutup

Response time: 5-10 menit pada jam operasional";
            $stmt->execute([$sender_id, $operationalMessage, $chatSessionId]);

            // Handler untuk pesan user
            if (isset($_POST['message'])) {
                $messageText = trim($_POST['message']);
                $command = strtolower($messageText);

                // Auto-response berdasarkan command
                switch ($command) {
                    case '1':
                        $autoResponse = "Untuk konsultasi service elektronik, silakan jelaskan:

1. Jenis elektronik yang bermasalah
2. Kerusakan yang dialami
3. Sudah berapa lama mengalami kerusakan

Teknisi kami akan segera menganalisa masalah Anda.

Atau Anda bisa langsung mengisi form service di: form.php";
                        break;

                    case '2':
                        $autoResponse = "Untuk mengecek status service, silakan berikan nomor service Anda.
Atau Anda bisa mengecek langsung di menu Riwayat: riwayat.php

Format: STATUS [nomor]
Contoh: STATUS SRV001";
                        break;

                    case '3':
                        $autoResponse = "Untuk membuat service baru, silakan:

1. Kunjungi halaman Form Service: form.php
2. Isi data lengkap termasuk:
   - Nama lengkap
   - Alamat
   - Nomor HP
   - Jenis elektronik
   - Kerusakan
   - Metode pembayaran

Atau Anda bisa menjelaskan kebutuhan service di sini, admin kami akan membantu prosesnya.";
                        break;

                    case '4':
                        $autoResponse = "Untuk melihat riwayat service Anda:

1. Kunjungi menu Riwayat: riwayat.php
2. Di sana Anda dapat melihat:
   - Status service
   - Tanggal service
   - Detail service
   - Metode pembayaran

Atau sebutkan nomor service untuk pengecekan cepat.";
                        break;

                    case '5':
                        $autoResponse = "Anda akan terhubung dengan admin kami dalam beberapa saat.
Mohon tunggu sebentar...

Waktu estimasi: 5-10 menit pada jam operasional.";
                        
                        // Update session untuk menandai perlu admin
                        $stmt = $db->prepare("UPDATE chat_sessions SET needs_admin = TRUE WHERE id = ?");
                        $stmt->execute([$chatSessionId]);
                        break;

                    case 'menu':
                        $autoResponse = "MENU LAYANAN SIKAPAIYYA:
-----------------------------
1. Konsultasi Service
2. Cek Status Service
3. Form Service Baru
4. Riwayat Service
5. Chat dengan Admin
-----------------------------
Ketik nomor untuk memilih layanan";
                        break;

                    case 'bantuan':
                        $autoResponse = "Panduan Penggunaan Layanan Sikapaiyya:

1. Form Service: form.php
2. Cek Riwayat: riwayat.php
3. Status Service: Ketik 'STATUS'
4. Konsultasi: Pilih menu 1
5. Admin: Pilih menu 5

Butuh bantuan lebih lanjut?
Hubungi kami di:
📞 Telepon: [Nomor Telepon]
📧 Email: [Email]";
                        break;

                    default:
                        // Cek jika format STATUS
                        if (strpos(strtoupper($messageText), 'STATUS') === 0) {
                            $autoResponse = "Sedang mengecek status service...
Mohon tunggu sebentar.";
                        }
                        break;
                }

                // Kirim auto-response jika ada
                if (isset($autoResponse)) {
                    $stmt = $db->prepare("INSERT INTO chats (sender_id, message, session_id, is_bot_message) VALUES (?, ?, ?, TRUE)");
                    $stmt->execute([$sender_id, $autoResponse, $chatSessionId]);
                }
            }
        }
    }
} catch (PDOException $e) {
    $systemMessage = "Terjadi kesalahan: " . $e->getMessage();
}

// Jika dipanggil melalui AJAX, return response dalam format JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $systemMessage
    ]);
    exit();
}
