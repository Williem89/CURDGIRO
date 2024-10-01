<?php
// Include the database connection
include 'koneksi.php';

// Get the account number from the request
$ac_number = filter_input(INPUT_GET, 'ac_number', FILTER_SANITIZE_STRING);

$response = [];

// Check if the account number is provided
if ($ac_number) {
    // Prepare and execute the query to fetch account details
    $stmt = $conn->prepare("SELECT nama_bank, nama_akun FROM list_rekening WHERE no_akun = ?");
    $stmt->bind_param("s", $ac_number);
    
    if ($stmt->execute()) {
        $stmt->bind_result($bank_name, $account_name);
        if ($stmt->fetch()) {
            $response['bank_name'] = $bank_name;
            $response['account_name'] = $account_name;
        } else {
            $response['error'] = 'No account found.';
        }
    } else {
        $response['error'] = 'Database query failed.';
    }
    $stmt->close();
} else {
    $response['error'] = 'Account number is required.';
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$conn->close();
?>
