<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Pastikan ID aman dengan konversi ke integer

    $stmt = $conn->prepare("SELECT * FROM post WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tutupost = $conn->prepare("SELECT * FROM tutup_post WHERE jenis_post = ? AND tahapan_post = ?");
        $tutupost->bind_param("ss", $row['jenis_post'], $row['tahapan_post']);

        $tutuppre = $conn->prepare("SELECT * FROM tutup_pre WHERE jenis_post = ? AND tahapan_post = ?");
        $tutuppre->bind_param("ss", $row['jenis_post'], $row['tahapan_post']);

        $tutupost->execute();
        $resulttutupost = $tutupost->get_result();
        $rowtutupost = [];
        while ($row2 = $resulttutupost->fetch_assoc()) {
            $rowtutupost[] = $row2;
        }

        $tutuppre->execute();
        $resulttutuppre = $tutuppre->get_result();
        $rowtutupre = [];
        while ($row3 = $resulttutuppre->fetch_assoc()) {
            $rowtutupre[] = $row3;
        }

        echo json_encode(['success' => true, 'row' => $row, 'rowtutupost' => $rowtutupost, 'rowtutupre' => $rowtutupre]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
}

$conn->close();
?>
