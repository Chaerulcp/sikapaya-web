<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Landing Page</title>
</head>

<body style="height: 2000px;">

    <!--Navbar-->
    <nav class="navbar navbar-expand-lg navbar-light bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">Sikapaiya</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="riwayat.php">Riwayat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="form.php">Form Service</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="chat.php">Chat</a>
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

    <!-- Brand Cards -->
    <div class="container" style="margin-top: 100px;">
        <h2 style="margin-bottom: 20px;" id="brand">Brand</h2>
        <div class="row">
            <!-- Brand cards from original design -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/Logo_LG.jpg" class="card-img-top" alt="..." style="height: 150px;">
                    <div class="card-body">
                        <h5 class="card-title">LG</h5>
                        <p class="card-text">LG, singkatan dari Lucky-Goldstar, adalah sebuah konglomerat multinasional yang telah menjadi nama besar dunia elektronik.</p>
                        <a href="#" class="btn btn-primary">Selengkapnya</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/Logo_DP.jpeg" class="card-img-top" alt="..." style="height: 150px;">
                    <div class="card-body">
                        <h5 class="card-title">Denpoo</h5>
                        <p class="card-text">Denpoo, ditulis sebagai DENPOO, adalah sebuah perusahaan alat elektronik konsumen asal Indonesia yang telah berhasil menembus pasar dalam negeri dan punya nama besar bahkan mancanegara. Didirikan pada tahun 1990.</p>
                        <a href="#" class="btn btn-primary">Selengkapnya</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/Logo_PAN.jpg" class="card-img-top" alt="..." style="height: 150px;">
                    <div class="card-body">
                        <h5 class="card-title">Panasonic</h5>
                        <p class="card-text">Panasonic, yang dulunya dikenal sebagai Matsushita Electric, adalah sebuah perusahaan multinasional asal Jepang yang telah menjadi salah satu pemimpin dalam industri elektronik dunia. Didirikan pada tahun 1918.</p>
                        <a href="form.php?jenis=Panasonic" class="btn btn-primary">Selengkapnya</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/Logo_SHA.jpg" class="card-img-top" alt="..." style="height: 150px;">
                    <div class="card-body">
                        <h5 class="card-title">SHARP</h5>
                        <p class="card-text">Sharp Corporation adalah sebuah perusahaan multinasional asal Jepang yang telah lama dikenal sebagai salah satu pemimpin dalam industri elektronik dan salah satu yang terbaik di Jepang. Didirikan pada tahun 1912.</p>
                        <a href="form.php?jenis=SHARP" class="btn btn-primary">Selengkapnya</a>
                    </div>
                </div>
            </div>
            <!-- Add other brand cards here -->
        </div>
    </div>

    <!-- Electronic Device Cards -->
    <div class="container" style="margin-top: 100px;">
        <h2 style="margin-bottom: 20px;">Alat Elektronik</h2>
        <div class="row">
            <!-- Device cards from original design -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/Kulkas.jpg" class="card-img-top" alt="..." style="height: 250px;">
                    <div class="card-body">
                        <h5 class="card-title">Kulkas</h5>
                        <p class="card-text">Klik tombol dibawah untuk membuat kartu service.</p>
                        <a href="form.php" class="btn btn-primary">Service</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/MECU.jpg" class="card-img-top" alt="..." style="height: 250px;">
                    <div class="card-body">
                        <h5 class="card-title">Masin Cuci</h5>
                        <p class="card-text">Klik tombol dibawah untuk membuat kartu service.</p>
                        <a href="form.php" class="btn btn-primary">Service</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/TV.jpg" class="card-img-top" alt="..." style="height: 250px;">
                    <div class="card-body">
                        <h5 class="card-title">TV</h5>
                        <p class="card-text">Klik tombol dibawah untuk membuat kartu service.</p>
                        <a href="form.php" class="btn btn-primary">Service</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <img src="Assets/KOM.png" class="card-img-top" alt="..." style="height: 250px;">
                    <div class="card-body">
                        <h5 class="card-title">Komputer</h5>
                        <p class="card-text">Klik tombol dibawah untuk membuat kartu service.</p>
                        <a href="form.php" class="btn btn-primary">Service</a>
                    </div>
                </div>
            </div>
            <!-- Add other device cards here -->
        </div>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>

</html>