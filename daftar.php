<?php
session_start();
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

ob_start(); // Start output buffering

if(isset($_POST['daftar'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $nohp = $_POST['nohp'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'user' atau 'admin'

    // Cek jika role adalah admin dan email tidak menggunakan domain @sikapaiya.com
    if ($role == 'admin' && !preg_match('/@sikapayya\.com$/', $email)) {
        $error = "Untuk mendaftar sebagai admin, Anda harus melamar pekerjaan terlebih dahulu di toko sikapayya untuk mendapatkan email khusus. Silakan kunjungi toko kami untuk informasi lebih lanjut.";
    } else {
        // Buat token konfirmasi
        $token = bin2hex(random_bytes(50));

        try {
            $stmt = $db->prepare("INSERT INTO users (nama, alamat, no_hp, email, password, role, is_active, confirmation_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $alamat, $nohp, $email, $password, $role, 0, $token]);

            // Kirim email konfirmasi
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.googlemail.com'; // Ganti dengan host SMTP Anda
            $mail->SMTPAuth = true;
            $mail->Username = 'no.reply.sikapayya@gmail.com'; // Ganti dengan username SMTP Anda
            $mail->Password = 'ypzhzsfzerjfndmd'; // Ganti dengan password SMTP Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Port untuk TLS

            $mail->setFrom('admin@sikapaiyya.com', 'Sikapaiyya');
            $mail->addAddress($email);
            $mail->Subject = 'Konfirmasi Akun';
            $mail->isHTML(true);
            $mail->Body = "
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
                    color: #fff;
                    padding: 10px;
                    text-align: center;
                }
                .email-body {
                    padding: 20px;
                }
                .email-footer {
                    background-color: #f8f9fa;
                    padding: 10px;
                    text-align: center;
                    font-size: 12px;
                    color: #6c757d;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #fff;
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
                    <p>Halo $nama,</p>
                    <p>Terima kasih telah mendaftar di Sikapaiyya. Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
                    <p><a href='https://sikapayya.com/confirm.php?token=$token' class='btn'>Aktifkan Akun</a></p>
                    <p>Jika Anda tidak mendaftar akun ini, abaikan email ini.</p>
                </div>
                <div class='email-footer'>
                    <p>&copy; 2024 Sikapaiyya. All rights reserved.</p>
                </div>
                </div>
            </body>
            </html>
            ";
            $mail->send();

            $_SESSION['message'] = "Akun Anda berhasil dibuat. Silakan periksa email Anda untuk mengaktifkan akun.";
            header("Location: index.php");
            exit();
        } catch(PDOException $e) {
            $error = "Pendaftaran gagal: " . $e->getMessage();
        } catch (Exception $e) {
            $error = "Pesan tidak dapat dikirim. Kesalahan Mailer: {$mail->ErrorInfo}";
        }
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'activated') {
    $_SESSION['message'] = "Akun Anda telah diaktifkan. Silakan login.";
}

ob_end_flush(); // End output buffering and flush output
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <title>Daftar</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .form-control {
            border-radius: 5px;
        }
        .form-check-label {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container" style="padding-top: 50px;">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h2>Daftarkan Akun Anda</h2>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" name="nama" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat</label>
                                <input type="text" class="form-control" name="alamat" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor HP</label>
                                <input type="text" class="form-control" name="nohp" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" value="user" checked>
                                <label class="form-check-label">User</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" value="admin">
                                <label class="form-check-label">Admin</label>
                            </div>
                            <button type="submit" name="daftar" class="btn btn-primary btn-block mt-4">Daftar</button>
                            <a href="index.php" class="btn btn-danger btn-block mt-2">Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>