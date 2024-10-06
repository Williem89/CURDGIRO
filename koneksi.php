<?php
// koneksi.php
$servername = "localhost"; // Ganti jika perlu
$username = "root"; // Ganti dengan username database Anda
$password = "it@123"; // Ganti dengan password database Anda
$dbname = "curdgiro"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set the timezone to GMT +7
date_default_timezone_set('Asia/Bangkok');

?>
