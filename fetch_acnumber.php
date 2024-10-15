<?php
include 'koneksi.php';

if (isset($_POST['namabank']) && isset($_POST['id_entitas'])) {
    $namabank = $_POST['namabank'];
    $id_entitas = $_POST['id_entitas'];

    $stmt = $conn->prepare("SELECT DISTINCT ac_number FROM data_giro WHERE namabank = ? AND id_entitas = ?");
    $stmt->bind_param("si", $namabank, $id_entitas); // "si" untuk string dan integer
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">Select No. Rekening</option>';
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['ac_number']}'>{$row['ac_number']}</option>";
    }

    $stmt->close();
}
?>
