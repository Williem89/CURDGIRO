<?php
// File: koneksi.php

// Konfigurasi database
$host = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password (usually empty for XAMPP)
$database = "curdgiro"; // Database name

// Membuat koneksi
$connection = mysqli_connect($host, $username, $password, $database);

// Memeriksa koneksi
if (!$connection) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
