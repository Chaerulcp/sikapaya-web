<?php

use PHPMailer\PHPMailer\PHPMailer;

session_start();
require_once 'config.php';

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ambil data service
$stmt = $db->prepare("SELECT * FROM service");
$stmt->execute();
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Service List</title>
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
                            if ($activeChats > 0):
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

    <!-- Service List -->
    <div class="container" style="margin-top: 100px;">
        <h2 style="margin-bottom: 20px;">Service List</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">No. HP</th>
                        <th scope="col">Merk</th>
                        <th scope="col">Jenis Alat</th>
                        <th scope="col">Kerusakan</th>
                        <th scope="col">Metode Pembayaran</th>
                        <th scope="col">Catatan</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <th scope="row"><?= $service['id']; ?></th>
                            <td><?= htmlspecialchars($service['nama']); ?></td>
                            <td><?= htmlspecialchars($service['alamat']); ?></td>
                            <td><?= htmlspecialchars($service['tanggal']); ?></td>
                            <td><?= htmlspecialchars($service['no_hp']); ?></td>
                            <td><?= htmlspecialchars($service['merk']); ?></td>
                            <td><?= htmlspecialchars($service['jenis_alat']); ?></td>
                            <td><?= htmlspecialchars($service['kerusakan']); ?></td>
                            <td><?= htmlspecialchars($service['metode_pembayaran']); ?></td>
                            <td><?= htmlspecialchars($service['catatan']); ?></td>
                            <td><?= htmlspecialchars($service['status']); ?></td>
                            <td>
                                <a href="update_status.php?id=<?= $service['id']; ?>" class="btn btn-primary btn-sm">Update Status</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status']) && isset($_POST['id'])) {
    $status = $_POST['status'];
    $id = $_POST['id'];

    // Update status in the database
    $stmt = $db->prepare("UPDATE service SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // Get user email
    $stmt = $db->prepare("SELECT email FROM users WHERE id = (SELECT user_id FROM service WHERE id = ?)");
    $stmt->execute([$id]);
    $userEmail = $stmt->fetchColumn();

    // Send email notification
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Ganti dengan host SMTP Anda
        $mail->SMTPAuth = true;
        $mail->Username = 'no.reply.sikapayya@gmail.com'; // Ganti dengan username SMTP Anda
        $mail->Password = 'ypzhzsfzerjfndmd'; // Ganti dengan password SMTP Anda
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Port untuk TLS

        $mail->setFrom('admin@sikapaiyya.com', 'Sikapaiyya');
        $mail->addAddress($userEmail);
        $mail->Subject = 'Service Status Update';
        $mail->isHTML(true);
        $mail->Body = "
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                }
                .container {
                    width: 80%;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 10px;
                    background-color: #f9f9f9;
                }
                .header {
                    text-align: center;
                    padding-bottom: 20px;
                }
                .header h1 {
                    margin: 0;
                    color: #333;
                }
                .content {
                    margin-top: 20px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Sikapaiyya</h1>
                </div>
                <div class='content'>
                    <p>Yth. Pelanggan,</p>
                    <p>Kami ingin memberitahukan bahwa status permintaan layanan Anda (ID: {$id}) telah diperbarui menjadi: <strong>{$status}</strong>.</p>
                    <p>Terima kasih telah menggunakan layanan kami.</p>
                </div>
                <div class='footer'>
                    <p>Salam hormat,<br>Tim Sikapaiyya</p>
                </div>
            </div>
        </body>
        </html>
        ";
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Redirect back to the service list
    header("Location: service.php");
    exit();
}
?>
