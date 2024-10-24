<?php
require_once 'db_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $serviceData = [
        'name' => $_POST['nama'],
        'address' => $_POST['alamat'],
        'date' => $_POST['tanggal'],
        'phone_number' => $_POST['nohp'],
        'brand' => $_POST['merk'],
        'device_type' => $_POST['jenisalat'],
        'issue' => $_POST['kerusakan'],
        'payment_method' => $_POST['bayar'],
        'notes' => $_POST['catat'],
        'status' => 'Menunggu Konfirmasi'
    ];
    
    $serviceId = insertRecord('services', $serviceData);
    if ($serviceId) {
        echo "<script>
                document.querySelector('.alert').style.display = 'block';
                setTimeout(function() {
                    window.location.href = 'landing.html';
                }, 3000);
              </script>";
    } else {
        echo "<script>alert('Pengajuan service gagal. Silakan coba lagi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Form</title>
</head>
<body style="height: 1200px;">
    <div class="alert alert-success" role="alert" style="display: none;">
        <h4 class="alert-heading" id="myAlert">Selamat!</h4>
        <p>Kamu sudah sukses membuat laporan</p>
        <hr>
        <p class="mb-0">Silahkan kembali dan menunggu laporan direspon</p>
    </div>

    <div class="container" style="padding-top: 50px;">
        <h2 style="margin-bottom: 20px;">Form Pengisian Aduan</h2>
        <br>
        <form method="POST" action="form.php">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" aria-describedby="alamatHelp" required>
                <div id="alamatHelp" class="form-text">Pastikan alamat anda diisi dengan benar!</div>
            </div>
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
            </div>
            <div class="mb-3">
                <label for="nohp" class="form-label">Nomor HP</label>
                <input type="text" class="form-control" id="nohp" name="nohp" required>
            </div>
            <div class="mb-3">
                <label for="merk" class="form-label">Merek</label>
                <input type="text" class="form-control" id="merk" name="merk" required>
            </div>
            <div class="mb-3">
                <label for="jenisalat" class="form-label">Jenis Alat</label>
                <input type="text" class="form-control" id="jenisalat" name="jenisalat" required>
            </div>
            <div class="mb-3">
                <label for="kerusakan" class="form-label">Kerusakan</label>
                <input type="text" class="form-control" id="kerusakan" name="kerusakan" required>
            </div>
            <div class="mb-3">
                <label for="catat" class="form-label">Catatan</label>
                <textarea class="form-control" id="catat" name="catat" rows="3"></textarea>
            </div>
            <div>
                <label for="bayar" class="form-label">Metode Pembayaran</label>
                <select class="form-select" aria-label="Default select example" id="bayar" name="bayar">
                    <option selected>Pilih Metode</option>
                    <option value="Bayar Ditempat">Bayar Ditempat</option>
                    <option value="Kartu Kredit">Kartu Kredit</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
            </div>
            <br>
            <button type="submit" class="btn btn-primary" id="myButton">Kirim</button>
            <a href="landing.html" class="btn btn-outline-danger">Kembali</a>
        </form>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>