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
        $tutuppre = $conn->prepare("SELECT * FROM tutup_pre WHERE jenis_pre = ? AND tahapan_pre = ?");
        $tutuppre->bind_param("ss", $row['jenis_prepost'], $row['tahapan']);

        $tutuppre->execute();
        $resulttutuppre = $tutuppre->get_result();
        $rowtutuppre = [];
        while ($row2 = $resulttutuppre->fetch_assoc()) {
            $rowtutuppre[] = $row2;
        }

        echo json_encode(['success' => true, 'row' => $row, 'rowtutuppre' => $rowtutuppre]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
}

$conn->close();
?>
