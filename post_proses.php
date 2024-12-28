<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Amankan parameter id (konversi ke integer)
    
    // Periksa apakah ID valid di database
    $check_sql = "SELECT * FROM post WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Proses SQL untuk memperbarui data
    $update_stmt = $conn->prepare("
            UPDATE pre 
            SET os = COALESCE(os, 0) + ? 
            WHERE jenis_prepost = ? AND tahapan = ?
        ");

    if ($result->num_rows > 0) {
        // Lakukan pembaruan jika ID valid
        $update_sql = "UPDATE post SET post = '1' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $id);

        $tutupsql = "SELECT * FROM tutup_pre WHERE jenis_post = ? AND tahapan_post = ?";
        $tutupstmt = $conn->prepare($tutupsql);
        $tutupstmt->bind_param("ss", $result['jenis_post'], $result['tahapan_post']);
        $tutupstmt->execute();
        $tutupresult = $tutupstmt->get_result();

        foreach ($tutupresult as $row) {
            $update_stmt->bind_param("iss", $row['nominal'], $row['jenis_pre'], $row['tahapan_pre']);
            $update_stmt->execute();
        }

        if ($stmt->execute()) {
            echo "<script>alert('Data updated successfully'); window.location.href='prepost.php';</script>";
        } else {
            echo "<script>alert('Failed to update data'); window.location.href='prepost.php';</script>";
        }
    } else {
        // ID tidak valid
        echo "<script>alert('Invalid record ID'); window.location.href='prepost.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('No ID specified'); window.location.href='prepost.php';</script>";
}

$conn->close();
?>
