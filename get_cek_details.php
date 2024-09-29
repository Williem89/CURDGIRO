<?php
include 'koneksi.php'; // Koneksi ke database

if (isset($_GET['noCek'])) {
    $noCek = $_GET['noCek'];

    // Query untuk mengambil data Cek berdasarkan No Cek
    $sql = "SELECT ac_number, ac_name, Nama_bank FROM data_Cek WHERE noCek = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $noCek);
    $stmt->execute();
    $result = $stmt->get_result();

    // Mengambil data jika ada
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data); // Mengirim data sebagai JSON
    } else {
        echo json_encode(null); // Jika tidak ada data
    }

    $stmt->close();
}

$conn->close(); // Menutup koneksi
?>
