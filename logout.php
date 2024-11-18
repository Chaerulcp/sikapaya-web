<?php
session_start(); // Mulai sesi
// Hancurkan semua data sesi
$_SESSION = []; // Kosongkan semua variabel sesi
session_destroy(); // Hancurkan sesi

// Redirect ke halaman login atau landing page
header("Location: index.php"); // Ganti dengan halaman login Anda
exit();
?>