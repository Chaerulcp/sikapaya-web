<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM service WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$services = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Riwayat Service</title>
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
                        <a class="nav-link text-white" href="form.php">Form Service</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown">
                            Pengaturan
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item text-primary" href="profil.php">Profile</a></li>
                            <li><a class="dropdown-item text-primary" href="logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Riwayat Service -->
    <div class="container" style="margin-top: 100px;">
        <h2 style="margin-bottom: 20px;">Riwayat Service</h2>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach($services as $service): ?>
                <tr>
                    <th scope="row"><?= $service['id']; ?></th>
                    <td><?= $service['nama']; ?></td>
                    <td><?= $service['alamat']; ?></td>
                    <td><?= $service['tanggal']; ?></td>
                    <td><?= $service['no_hp']; ?></td>
                    <td><?= $service['merk']; ?></td>
                    <td><?= $service['jenis_alat']; ?></td>
                    <td><?= $service['kerusakan']; ?></td>
                    <td><?= $service['metode_pembayaran']; ?></td>
                    <td><?= $service['catatan']; ?></td>
                    <td><?= $service['status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>