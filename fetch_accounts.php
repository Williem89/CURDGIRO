<?php
include 'koneksi.php';

$id_entitas = intval($_GET['id_entitas']);
$account_numbers = [];

if ($id_entitas) {
    $stmt = $conn->prepare("SELECT no_akun FROM list_rekening WHERE id_entitas = ?");
    $stmt->bind_param("i", $id_entitas);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $account_numbers[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($account_numbers);
?>
