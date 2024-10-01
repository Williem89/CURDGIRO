<?php
include 'koneksi.php'; // Koneksi ke database

if (isset($_GET['nogiro'])) {
    $nogiro = $_GET['nogiro'];

    // Query untuk mengambil data giro berdasarkan No Giro
    $sql = "SELECT ac_number, ac_name, Nama_bank FROM data_giro WHERE nogiro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nogiro);
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
