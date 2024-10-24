<?php
require_once 'db_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userData = [
        'name' => $_POST['nama'],
        'address' => $_POST['alamat'],
        'phone_number' => $_POST['nohp'],
        'email' => $_POST['email'],
        'role' => $_POST['flexRadioDefault'] == 'flexRadioDefault1' ? 'user' : 'admin'
    ];
    
    $userId = insertRecord('users', $userData);
    if ($userId) {
        header("Location: landing.html");
        exit();
    } else {
        echo "Pendaftaran gagal. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">
    <title>Daftar</title>
</head>
<body style="height: 1200px;">
    <div class="container" style="padding-top: 50px;">
        <h2 style="margin-bottom: 20px;">Daftarkan Akun Anda</h2>
        <br>
        <form method="POST" action="daftar.php">
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
                <label for="nohp" class="form-label">Nomor HP</label>
                <input type="text" class="form-control" id="nohp" name="nohp" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="flexRadioDefault1" checked>
                <label class="form-check-label" for="flexRadioDefault1">
                    User
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="flexRadioDefault2">
                <label class="form-check-label" for="flexRadioDefault2">
                    Admin
                </label>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Daftar</button>
            <a href="index.php" class="btn btn-outline-danger">Kembali</a>
        </form>
    </div>

    <script src="js/bootstrap.js"></script>
    <script src="js/popper.min.js"></script>
</body>
</html>