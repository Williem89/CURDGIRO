<?php
include 'koneksi.php';

if (isset($_POST['id_entitas'])) {
    $id_entitas = $_POST['id_entitas'];
    $result = $conn->query("
        SELECT DISTINCT namabank FROM (
            SELECT d.namabank FROM data_giro d 
            INNER JOIN list_entitas e ON d.id_entitas = e.id_entitas 
            WHERE e.id_entitas = '$id_entitas'
            UNION
            SELECT c.namabank FROM data_cek c 
            INNER JOIN list_entitas e ON c.id_entitas = e.id_entitas 
            WHERE e.id_entitas = '$id_entitas'
        ) AS combined_namabank
    ");

    echo '<option value="">Select Nama Bank</option>';
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['namabank']}'>{$row['namabank']}</option>";
    }
}
?>
