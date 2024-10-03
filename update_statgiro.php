<?php
// Include the database connection
include 'koneksi.php';
session_start(); // Start the session to access user information

// Assuming the user's information is stored in session
$user_logged_in = $_SESSION['username']; // Adjust this based on your session variable

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Log incoming data for debugging
file_put_contents('debug_log.txt', print_r($data, true), FILE_APPEND);

// Check if required parameters are set
if (isset($data['nogiro'], $data['tanggal'], $data['statgiro'], $data["action"])) {

    $nogiro = $data['nogiro'];
    $tanggal = $data['tanggal'];
    $statgiro = $data['statgiro'];
    $action = $data['action'];

    if ($action == "cairgiro") {
        $sql = "UPDATE detail_giro SET StatGiro = ?, tanggal_Cair_giro = ?, SeatleBy = '$user_logged_in' WHERE nogiro = ?";
        $stmt = $conn->prepare($sql);

        // Check if preparation was successful
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Preparation failed: ' . $conn->error]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param("sss", $statgiro, $tanggal, $nogiro);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
        }

        // Close the statement and connection
        $stmt->close();
    } else if ($action == "returngiro") {
        $sql = "UPDATE detail_giro SET StatGiro = ?, tglkembalikebank = ?, dikembalikanoleh = '$user_logged_in' WHERE nogiro = ?";
        $stmt = $conn->prepare($sql);

        // Check if preparation was successful
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Preparation failed: ' . $conn->error]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param("sss", $statgiro, $tanggal, $nogiro);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
        }

        // Close the statement and connection
        $stmt->close();
    } else if ($action == "voidgiro") {
        $sql = "UPDATE detail_giro SET StatGiro = ?, TglVoid = ?, VoidBy = '$user_logged_in' WHERE nogiro = ?";
        $stmt = $conn->prepare($sql);

        // Check if preparation was successful
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Preparation failed: ' . $conn->error]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param("sss", $statgiro, $tanggal, $nogiro);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
        }

        // Close the statement and connection
        $stmt->close();
    }
    //$seatleBy = $data['SeatleBy'];

    // Prepare the SQL statement    



} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

// Close the database connection
$conn->close();
