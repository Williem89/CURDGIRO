<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $nominal = floatval($_POST['nominal']);
    $penutupan = $_POST['penutupan'];
    $tahapan_post = $_POST['tahapan_post'];

    // Update the post table
    $stmt = $conn->prepare("UPDATE post SET released = ?, post = 1 WHERE id = ?");
    $stmt->bind_param("di", $nominal, $id);
    $stmt->execute();
    $stmt->close();

    $updatestmt = $conn->prepare("UPDATE tutup_pre SET nominal = ? WHERE tahapan_post = ? AND tahapan_pre = ?");
    foreach ($penutupan['nominal'] as $index => $nominal) {
        $updatestmt->bind_param("dss", $nominal, $tahapan_post, $penutupan['tahapan_pre'][$index]);
        $updatestmt->execute();
    }
    $updatestmt->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
