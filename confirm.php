<?php
require_once 'config.php';
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE confirmation_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // Aktifkan akun
            $stmt = $db->prepare("UPDATE users SET is_active = 1, confirmation_token = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Set notifikasi sukses
            $_SESSION['message'] = "Akun Anda telah berhasil diaktifkan.";

            // Redirect ke halaman login dengan notifikasi
            header("Location: index.php?status=activated");
            exit();
        } else {
            // Redirect ke halaman login dengan notifikasi token tidak valid
            header("Location: index.php?status=invalid_token");
            exit();
        }
    } catch (PDOException $e) {
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
} else {
    // Redirect ke halaman login jika token tidak ditemukan
    header("Location: index.php?status=no_token");
    exit();
}
?>
