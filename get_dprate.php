<?php
include 'koneksi.php';

if (isset($_GET['jeniski'])) {
    $jeniski = $_GET['jeniski'];

    // Query to fetch dprate based on jeniski
    $query = "SELECT dprate FROM bnl WHERE id = '$jeniski'";
    $result = mysqli_query($conn, $query);

    // Check if a result is returned
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['dprate' => $row['dprate']]);
    } else {
        echo json_encode(['dprate' => 0]); // Default value if not found
    }
}
?>
