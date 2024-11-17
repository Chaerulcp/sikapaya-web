<?php
$host = 'https://sikapayya.com/';
$dbname = 'u609729740_sikapaiyya';
$username = 'u609729740_root';
$password = '5Ix>2=El';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
