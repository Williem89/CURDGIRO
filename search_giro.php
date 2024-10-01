<?php
include 'koneksi.php';

// Get the search query from the request
$search = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
$giro_data = [];

if ($search) {
    // Prepare the SQL statement to search for No Giro
    $stmt = $conn->prepare("SELECT nogiro, namabank, ac_number FROM data_giro WHERE nogiro LIKE CONCAT('%', ?, '%') AND statusgiro = 'Unused'");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the results into an array
    while ($row = $result->fetch_assoc()) {
        $giro_data[] = $row;
    }

    $stmt->close();
}

// Set the response header to JSON
header('Content-Type: application/json');
// Return the JSON encoded data
echo json_encode($giro_data);
$conn->close();
?>
