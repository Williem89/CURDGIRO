<?php
include 'koneksi.php';

if (isset($_GET['acnumber']) && isset($_GET['type'])) {
    $acnumber = $_GET['acnumber'];
    $type = $_GET['type'];
    
    // Prepare the SQL statement based on payment type
    if ($type === 'Giro') {
        $stmt = $conn->prepare("SELECT nogiro AS number FROM data_giro WHERE ac_number = ? AND statusgiro = 'Unused'");
    } else if ($type === 'Cek') {
        $stmt = $conn->prepare("SELECT nocek AS number FROM data_cek WHERE ac_number = ? AND statuscek = 'Unused'");
    }
    
    $stmt->bind_param("s", $acnumber);
    $stmt->execute();
    $result = $stmt->get_result();

    $numbers = [];
    while ($row = $result->fetch_assoc()) {
        $numbers[] = $row;
    }

    echo json_encode($numbers);
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Required parameters are missing'
    ]);
}

$conn->close();
?>