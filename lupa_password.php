<?php
session_start();
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

if (isset($_POST['reset_password'])) {
    $email = $_POST['email'];

    // Cek apakah email terdaftar
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Buat token untuk reset password
        $token = bin2hex(random_bytes(50));
        $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $stmt->execute([$token, $email]);

        // Kirim email menggunakan PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Konfigurasi server
            $mail->isSMTP();
            $mail->Host = 'smtp.googlemail.com'; // Ganti dengan host SMTP Anda
            $mail->SMTPAuth = true;
            $mail->Username = 'no.reply.sikapayya@gmail.com'; // Ganti dengan email Anda
            $mail->Password = 'ypzhzsfzerjfndmd'; // Ganti dengan password email Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Port untuk TLS

            // Penerima
            $mail->setFrom('admin@sikapaiya.com', 'Sikapaiyya');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $resetLink = "http://sikapayya.com/reset_password.php?token=" . $token; // Ganti dengan domain Anda
            $mail->Subject = 'Reset Password';
            $mail->Body    = "
                <html>
                <head>
                    <style>
                        .email-container {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            color: #333;
                        }
                        .email-header {
                            background-color: #007bff;
                            color: white;
                            padding: 10px;
                            text-align: center;
                        }
                        .email-body {
                            padding: 20px;
                        }
                        .email-footer {
                            background-color: #f1f1f1;
                            padding: 10px;
                            text-align: center;
                            font-size: 12px;
                            color: #666;
                        }
                        .btn {
                            display: inline-block;
                            padding: 10px 20px;
                            margin: 10px 0;
                            font-size: 16px;
                            color: white;
                            background-color: #007bff;
                            text-decoration: none;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <h1>Sikapaiyya</h1>
                        </div>
                        <div class='email-body'>
                            <p>Halo,</p>
                            <p>Kami menerima permintaan untuk mereset password Anda. Klik tombol di bawah ini untuk mereset password Anda:</p>
                            <p><a href='$resetLink' class='btn'>Reset Password</a></p>
                            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                        </div>
                        <div class='email-footer'>
                            <p>&copy; 2024 Sikapaiyya. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $mail->send();
            $success = "Email reset password telah dikirim!";
        } catch (Exception $e) {
            $error = "Pesan tidak dapat dikirim. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Lupa Password</title>
</head>

<body>
    <div class="container mt-5">
        <h2>Lupa Password</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-primary">Kirim Link Reset Password</button>
            <button type="button" class="btn btn-secondary" onclick="location.href='index.php'">Kembali</button>

        </form>
    </div>
    <script src="js/bootstrap.js"></script>
</body>

</html>