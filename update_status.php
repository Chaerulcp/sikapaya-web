<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Cek apakah ada ID service yang dikirim
if(!isset($_GET['id'])) {
    header("Location: service.php");
    exit();
}

$service_id = $_GET['id'];

// Ambil data service yang akan diupdate
$stmt = $db->prepare("SELECT * FROM service WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

// Jika service tidak ditemukan, redirect ke halaman service
if(!$service) {
    header("Location: service.php");
    exit();
}

// Proses form update status
if(isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];
    
    // Update status service
    $stmt = $db->prepare("UPDATE service SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $service_id]);
    
    header("Location: service.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Update Status Service</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Update Status Service</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Detail Service</h5>
                <p><strong>Nama:</strong> <?= htmlspecialchars($service['nama']) ?></p>
                <p><strong>Jenis Alat:</strong> <?= htmlspecialchars($service['jenis_alat']) ?></p>
                <p><strong>Kerusakan:</strong> <?= htmlspecialchars($service['kerusakan']) ?></p>
                <p><strong>Status Saat Ini:</strong > <?= htmlspecialchars($service['status']) ?></p>
                <form method="POST" action="update_status.php?id=<?= $service_id ?>">
                    <label for="new_status">Pilih Status Baru:</label>
                    <select name="new_status" class="form-select form-select-sm">
                        <option value="Menunggu Konfirmasi" <?= $service['status'] == 'Menunggu Konfirmasi' ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                        <option value="Diterima" <?= $service['status'] == 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                        <option value="Dalam Proses" <?= $service['status'] == 'Dalam Proses' ? 'selected' : ''; ?>>Dalam Proses</option>
                        <option value="Selesai" <?= $service['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?= $service['status'] == 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update Status</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>