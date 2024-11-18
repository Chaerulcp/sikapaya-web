<?php
$dsn = 'mysql:host=localhost;dbname=u609729740_sika';
$username = 'u609729740_sika';
$password = 'L2$gbJYv;Cp?';

try {
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
?>
