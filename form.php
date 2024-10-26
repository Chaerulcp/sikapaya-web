<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if(isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $tanggal = $_POST['tanggal'];
    $nohp = $_POST['nohp'];
    $merk = $_POST['merk'];
    $jenis_alat = $_POST['jenis_alat'];
    $kerusakan = $_POST['kerusakan'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $catatan = $_POST ['catatan'];

    $stmt = $db->prepare("INSERT INTO service (user_id, nama, alamat, tanggal, no_hp, merk, jenis_alat, kerusakan, metode_pembayaran, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $nama, $alamat, $tanggal, $nohp, $merk, $jenis_alat, $kerusakan, $metode_pembayaran, $catatan]);

    header("Location: riwayat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Form Service</title>
</head>
<body>

    <!--Navbar-->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">Sikapaiyya</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="landing.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="riwayat.php">Riwayat</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown">
                            Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item text-primary" href="#">Profile</a></li>
                            <li><a class="dropdown-item text-primary" href="logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Form -->
    <div class="container" style="margin-top: 100px;">
        <h2 style="margin-bottom: 20px;">Form Service</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" required>
            </div>
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
            </div>
            <div class="mb-3">
                <label for="nohp" class="form-label">No. HP</label>
                <input type="tel" class="form-control" id="nohp" name="nohp" required>
            </div>
            <div class="mb-3">
                <label for="merk" class="form-label">Merk</label>
                <input type="text" class="form-control" id="merk" name="merk" required>
            </div>
            <div class="mb-3">
                <label for="jenis_alat" class="form-label">Jenis Alat</label>
                <input type="text" class="form-control" id="jenis_alat" name="jenis_alat" required>
            </div>
            <div class="mb-3">
                <label for="kerusakan" class="form-label">Kerusakan</label>
                <textarea class="form-control" id="kerusakan" name="kerusakan" required></textarea>
            </div>
            <div class="mb-3">
                <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                    <option value="Cash">Cash</option>
                    <option value="Kartu Kredit">Kartu Kredit</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
 </div>
            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan</label>
                <textarea class="form-control" id="catatan" name="catatan"></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>