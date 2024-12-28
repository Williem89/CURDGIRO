<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $transaksi = $_POST['transaksi'];
    $tgl_uk = $_POST['tgl_uk'];
    $tgl_jt = $_POST['tgl_jt'];
    $nominal_uk = $_POST['nominal_uk'];
    $tgl_pindahdana30 = $_POST['tgl_pindahdana30'];
    $tgl_pembayaran_uk = $_POST['tgl_pembayaran_uk'];
    $margin_deposit = $_POST['marginDeposit'];
    $project = $_POST['project'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];

    if (isset($project) && isset($keterangan)) {
        $query = "UPDATE dhe_transactions SET project = ?, keterangan = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $project, $keterangan, $id);
    } else if ($status == 0) {
        $status = 1;
        $query = "UPDATE dhe_transactions SET transaksi = ?, tgl_uk = ?, jatuh_tempo_uk = ?, nominal_uk = ?, margin_deposit = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssddii", $transaksi, $tgl_uk, $tgl_jt, $nominal_uk, $margin_deposit, $status, $id);
    } else if ($status == 1) {
        $status = 2;
        $query = "UPDATE dhe_transactions SET tgl_pembayaran_uk = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sii", $tgl_pembayaran_uk, $status, $id);
    } else if ($status == 2) {
        $status = 3;
        $query = "UPDATE dhe_transactions SET tgl_pindahdana30 = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sii", $tgl_pindahdana30, $status, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
