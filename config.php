<?php
$host = 'sikapayya.com';
$dbname = 'sikapaiyya';
$username = 'root';
$password = ':bG7w*n$o1Dw';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
