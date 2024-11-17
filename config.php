<?php
$host = 'sikapayya.com';
$dbname = 'u609729740_sikapaiyya';
$username = 'u609729740_root';
$password = ':bG7w*n$o1Dw';

// Buat koneksi ke database
$conn = mysqli_connect($host, $username, $password, $database);

// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Jika koneksi berhasil
echo "Koneksi ke database berhasil";
?>