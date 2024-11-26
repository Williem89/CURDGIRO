<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Amankan parameter id (konversi ke integer)
    
    // Periksa apakah ID valid di database
    $check_sql = "SELECT * FROM pre WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Lakukan pembaruan jika ID valid
        $update_sql = "UPDATE pre SET post = '1' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $id);

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
