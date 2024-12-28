<?php
include 'koneksi.php';

if (isset($_POST['entitas'])) {
    $entitas = $_POST['entitas'];
    $query = "SELECT norek FROM list_rekeningdhe WHERE kode_entitas = '$entitas'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='" . $row['norek'] . "'>" . $row['norek'] . "</option>";
        }
    } else {
        echo "<option value=''>No data available</option>";
    }
}
?>
