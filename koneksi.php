<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rental_pickup";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Memulai session secara global jika belum aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>