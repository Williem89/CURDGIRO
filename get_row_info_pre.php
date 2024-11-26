<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Pastikan ID aman dengan konversi ke integer

    $stmt = $conn->prepare("SELECT * FROM pre WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'row' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
}

$conn->close();
?>
