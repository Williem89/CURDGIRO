<?php
include 'koneksi.php';

if (isset($_GET['nogiro'])) {
    $selected_nogiro = $_GET['nogiro'];

    // Ambil detail dari tabel data_giro
    $detailSql = "SELECT ac_number, ac_name, namabank FROM data_giro WHERE nogiro = ?";
    $stmt = $conn->prepare($detailSql);
    $stmt->bind_param("s", $selected_nogiro);
    $stmt->execute();
    $giroData = $stmt->get_result()->fetch_assoc();

    // Ambil detail penerima dari tabel detail_giro
    $detailPenerimaSql = "SELECT nama_penerima, ac_penerima, bank_penerima, nominal, PVRNO, keterangan FROM detail_giro WHERE nogiro = ?";
    $stmtPenerima = $conn->prepare($detailPenerimaSql);
    $stmtPenerima->bind_param("s", $selected_nogiro);
    $stmtPenerima->execute();
    $detailPenerima = $stmtPenerima->get_result()->fetch_assoc();

    // Gabungkan data
    $response = array_merge($giroData, $detailPenerima);
    echo json_encode($response);
}

$conn->close();
?>
