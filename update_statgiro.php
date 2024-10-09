<?php
// Include the database connection
include 'koneksi.php';
session_start(); // Start the session to access user information

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_logged_in = $_SESSION['username']; // Get the logged-in user

// Enable error//// reporting for debugging (consider removing in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Check if required parameters are set
if (isset($data['nogiro'], $data['tanggal'], $data['statgiro'], $data["action"], $data["alasan"], $data["jenis"])) {
    $nogiro = $data['nogiro'];
    $tanggal = $data['tanggal'];
    $statgiro = $data['statgiro'];
    $action = $data['action'];
    $alasan = $data['alasan'];
    $jenis = $data['jenis'];

    // // Log the 'jenis' data
    // error_log("Jenis: " . htmlspecialchars($jenis, ENT_QUOTES, 'UTF-8'));

    // Prepare the SQL statement based on action
    switch ($jenis) {
        case "Giro":
            switch ($action) {
                case "cair":
                    $sql = "UPDATE detail_giro SET StatGiro = ?, tanggal_Cair_giro = ?, SeatleBy = ? WHERE nogiro = ?";
                    break;
                case "return":
                    $sql = "UPDATE detail_giro SET StatGiro = ?, tglkembalikebank = ?, dikembalikanoleh = ? WHERE nogiro = ?";
                    break;
                case "void":
                    $sql = "UPDATE detail_giro SET StatGiro = ?, TglVoid = ?, VoidBy = ?, a_void = ? WHERE nogiro = ?";
                    break;
                case "acc":
                    $sql = "UPDATE detail_giro SET StatGiro = 'Issued', ApproveBy = ?, ApproveAt = ? WHERE nogiro = ?";
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    exit;
            }
            break;
        case "Cek":
            switch ($action) {
                case "cair":
                    $sql = "UPDATE detail_cek SET StatCek = ?, tanggal_Cair_cek = ?, SeatleBy = ? WHERE nocek = ?";
                    break;
                case "return":
                    $sql = "UPDATE detail_cek SET StatCek = ?, tglkembalikebank = ?, dikembalikanoleh = ? WHERE nocek = ?";
                    break;
                case "void":
                    $sql = "UPDATE detail_cek SET StatCek = ?, TglVoid = ?, VoidBy = ?, a_void = ? WHERE nocek = ?";
                    break;
                case "acc":
                    $sql = "UPDATE detail_cek SET StatGiro = 'Issued', ApproveBy = ?, ApproveAt = ? WHERE nocek = ?";
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    exit;
            }
            break;
        case 'loa':
            switch ($action) {
                case "cair":
                    $sql = "UPDATE detail_loa SET StatLoa = ?, tanggal_Cair_loa = ?, SeatleBy = ? WHERE noloa = ?";
                    break;
                case "return":
                    $sql = "UPDATE detail_loa SET StatLoa = ?, tglkembalikebank = ?, dikembalikanoleh = ? WHERE noloa = ?";
                    break;
                case "void":
                    $sql = "UPDATE detail_loa SET StatLoa = ?, TglVoid = ?, VoidBy = ?, a_void = ? WHERE noloa = ?";
                    break;
                case "acc":
                    $sql = "UPDATE detail_loa SET StatGiro = 'Issued', ApproveBy = ?, ApproveAt = ? WHERE noloa = ?";
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    exit;
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid jenis']);
            exit;
    }

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Preparation failed: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
        echo json_encode(['success' => false, 'message' => 'Preparation failed']);
        exit;
    }

    // Bind parameters
    if ($action === "cair" || $action === "return") {
        $stmt->bind_param("ssss", $statgiro, $tanggal, $user_logged_in, $nogiro);
    } else if ($action === "void") {
        $stmt->bind_param("sssss", $statgiro, $tanggal, $user_logged_in, $alasan, $nogiro);
    } else if ($action === "acc") {
        $stmt->bind_param("sss", $user_logged_in, $tanggal, $nogiro);
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        error_log("Execution failed: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8'));
        echo json_encode(['success' => false, 'message' => 'Execution failed']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

// Close the database connection
$conn->close();
