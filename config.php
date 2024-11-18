<?php
$dsn = 'mysql:host=localhost;dbname=u609729740_sikapaiyya';
$username = 'u609729740_root';
$password = ':bG7w*n$o1Dw';

try {
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
?>
