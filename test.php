<?php
include 'koneksi.php'; // Pastikan file koneksi database ada

// Ambil nominal pinjaman baru dari form
$nominal_pencairan = (float)$_POST['nominal'];
$tanggal_pencairan = date('Y-m-d');

// Insert ke tabel pencairan
$sql_insert_pencairan = "INSERT INTO pencairan (tanggal_pencairan, nominal_pencairan) VALUES ('$tanggal_pencairan', $nominal_pencairan)";
if (!$conn->query($sql_insert_pencairan)) {
    die("Error insert pencairan: " . $conn->error);
}
$id_pencairan = $conn->insert_id;

// Ambil daftar hutang berdasarkan tanggal jatuh tempo ASC


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_hutang = $row['id_hutang'];
        $sisa_os = (float)$row['sisa_os'];

        if ($nominal_pencairan > 0) {
            $nominal_dilunasi = min($nominal_pencairan, $sisa_os);
            $sisa_setelah_pelunasan = $sisa_os - $nominal_dilunasi;

            // Update hutang
            $sql_update_hutang = "UPDATE hutang SET sisa_os = $sisa_setelah_pelunasan WHERE id_hutang = $id_hutang";
            if (!$conn->query($sql_update_hutang)) {
                die("Error update hutang: " . $conn->error);
            }

            // Insert log pelunasan
            $sql_insert_pelunasan = "INSERT INTO pelunasan (id_hutang, id_pencairan, nominal_dilunasi, sisa_setelah_pelunasan)
                                     VALUES ($id_hutang, $id_pencairan, $nominal_dilunasi, $sisa_setelah_pelunasan)";
            if (!$conn->query($sql_insert_pelunasan)) {
                die("Error insert pelunasan: " . $conn->error);
            }

            // Kurangi nominal pencairan
            $nominal_pencairan -= $nominal_dilunasi;
        } else {
            break; // Pinjaman habis, keluar dari loop
        }
    }
}

// Redirect ke halaman laporan atau tampilkan pesan sukses
header('Location: laporan_pelunasan.php');
exit;
?>
