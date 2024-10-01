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
    $detailPenerimaSql = "SELECT nama_penerima, ac_penerima, StatGiro, bank_penerima, nominal, PVRNO, keterangan, tanggal_giro, tanggal_jatuh_tempo, tanggal_cair_giro, TglVoid, tglkembalikebank FROM detail_giro WHERE nogiro = ?";
    $stmtPenerima = $conn->prepare($detailPenerimaSql);
    $stmtPenerima->bind_param("s", $selected_nogiro);
    $stmtPenerima->execute();
    $detailPenerima = $stmtPenerima->get_result()->fetch_assoc();

    // Gabungkan data
    $response = array_merge($giroData, $detailPenerima);
    echo json_encode($response);
}

if (isset($_GET['nocek'])) {
    $selected_nocek = $_GET['nocek'];

    // Ambil detail dari tabel data_cek
    $detailSql = "SELECT ac_number, ac_name, namabank FROM data_cek WHERE nocek = ?";
    $stmt = $conn->prepare($detailSql);
    $stmt->bind_param("s", $selected_nocek);
    $stmt->execute();
    $cekData = $stmt->get_result()->fetch_assoc();

    // Ambil detail penerima dari tabel detail_cek
    $detailPenerimaSql = "SELECT nama_penerima, ac_penerima, Statcek, bank_penerima, nominal, PVRNO, keterangan, tanggal_cek, tanggal_jatuh_tempo, tanggal_cair_cek, TglVoid, tglkembalikebank FROM detail_cek WHERE nocek = ?";
    $stmtPenerima = $conn->prepare($detailPenerimaSql);
    $stmtPenerima->bind_param("s", $selected_nocek);
    $stmtPenerima->execute();
    $detailPenerima = $stmtPenerima->get_result()->fetch_assoc();

    // Gabungkan data
    $response = array_merge($cekData, $detailPenerima);
    echo json_encode($response);
}

$conn->close();
?>
