<?php
include 'koneksi.php';

$search = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
$banks = [];

if ($search) {
    $stmt = $conn->prepare("SELECT id, nama_bank FROM list_bank WHERE nama_bank LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $banks[] = $row;
    }

    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($banks);
$conn->close();
?>
