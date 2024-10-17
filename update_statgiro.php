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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES["file"])) {

    $target_dir = "imggiro/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $filename = basename($_FILES["file"]["name"]);

    // Check if file is a valid type (e.g., only allow certain file types)
    $allowedTypes = ['jpg', 'png', 'pdf'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        $uploadOk = 0;
    }

    // Check file size (e.g., limit to 5MB)
    if ($_FILES["file"]["size"] > 5000000) {
        echo json_encode(['success' => false, 'message' => 'File is too large']);
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo json_encode(['success' => false, 'message' => 'File was not uploaded']);
    } else {
        // Try to upload file
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading file']);
        }
    }
}

// Check if required parameters are set
if (isset($_POST['nogiro'], $_POST["action"], $_POST["jenis"])) {
    $data = $_POST;
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
                    $sql = "UPDATE detail_giro SET StatGiro = 'Pending Post', tanggal_Cair_giro = ?, SeatleBy = ? WHERE nogiro = ?";
                    break;
                case "return":
                    $sql = "UPDATE detail_giro SET StatGiro = 'Pending Return', tglkembalikebank = ?, dikembalikanoleh = ?, lampiran = ? WHERE nogiro = ?";
                    break;
                case "void":
                    $sql = "UPDATE detail_giro SET StatGiro = 'Pending Void', TglVoid = ?, VoidBy = ?, a_void = ? WHERE nogiro = ?";
                    break;
                case "acc":
                    $sql = "UPDATE detail_giro SET StatGiro = 'Issued', ApproveBy = ?, ApproveAt = ? WHERE nogiro = ?";
                    break;
                case "apv":
                    $sql = "UPDATE detail_giro SET StatGiro = 'Void', ApproveBy = ?, ApproveAt = ? WHERE nogiro = ?";
                    break;
                case "apr":
                    $sql = "UPDATE detail_giro SET StatGiro = 'Return', ApproveBy = ?, ApproveAt = ? WHERE nogiro = ?";
                    break;
                case "add":
                    $sql = "UPDATE detail_giro SET image_giro = ? WHERE nogiro = ?";
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    exit;
            }
            break;
        case "Cek":
            switch ($action) {
                case "cair":
                    $sql = "UPDATE detail_cek SET StatCek = 'Pending Post', tanggal_Cair_cek = ?, SeatleBy = ? WHERE nocek = ?";
                    break;
                case "return":
                    $sql = "UPDATE detail_cek SET StatCek = 'Pending Return', tglkembalikebank = ?, dikembalikanoleh = ?, lampiran = ? WHERE nocek = ?";
                    break;
                case "void":
                    $sql = "UPDATE detail_cek SET StatCek = 'Pending Void', TglVoid = ?, VoidBy = ?, a_void = ? WHERE nocek = ?";
                    break;
                case "acc":
                    $sql = "UPDATE detail_cek SET StatCek = 'Issued', ApproveBy = ?, ApproveAt = ? WHERE nocek = ?";
                    break;
                case "apv":
                    $sql = "UPDATE detail_cek SET StatCek = 'Void', ApproveBy = ?, ApproveAt = ? WHERE nocek = ?";
                    break;
                case "apr":
                    $sql = "UPDATE detail_cek SET StatCek = 'Return', ApproveBy = ?, ApproveAt = ? WHERE nocek = ?";
                    break;
                case "add":
                    $sql = "UPDATE detail_cek SET image_giro = ? WHERE nocek = ?";
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    exit;
            }
            break;
        case 'loa':
            switch ($action) {
                case "cair":
                    $sql = "UPDATE detail_loa SET StatLoa = 'Pending Post', tanggal_Cair_loa = ?, SeatleBy = ? WHERE noloa = ?";
                    break;
                case "return":
                    $sql = "UPDATE detail_loa SET StatLoa = 'Pending Return', tglkembalikebank = ?, dikembalikanoleh = ?, lampiran = ? WHERE noloa = ?";
                    break;
                case "void":
                    $sql = "UPDATE detail_loa SET StatLoa = 'Pending Void', TglVoid = ?, VoidBy = ?, a_void = ? WHERE noloa = ?";
                    break;
                case "acc":
                    $sql = "UPDATE detail_loa SET StatGiro = 'Issued', ApproveBy = ?, ApproveAt = ? WHERE noloa = ?";
                    break;
                case "apv":
                    $sql = "UPDATE detail_loa SET StatGiro = 'Void', ApproveBy = ?, ApproveAt = ? WHERE noloa = ?";
                    break;
                case "apr":
                    $sql = "UPDATE detail_loa SET StatGiro = 'Return', ApproveBy = ?, ApproveAt = ? WHERE noloa = ?";
                    break;
                case "add":
                    $sql = "UPDATE detail_loa SET image_giro = ? WHERE noloa = ?";
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
    if ($action === "cair") {
        $stmt->bind_param("sss", $tanggal, $user_logged_in, $nogiro);
    } else if ($action === "return") {
        $stmt->bind_param("ssss", $tanggal, $user_logged_in, $filename, $nogiro);
    } else if ($action === "void") {
        $stmt->bind_param("ssss", $tanggal, $user_logged_in, $alasan, $nogiro);
    } else if ($action === "acc") {
        $stmt->bind_param("sss", $user_logged_in, $tanggal, $nogiro);
    } else if ($action === "apv") {
        $stmt->bind_param("sss", $user_logged_in, $tanggal, $nogiro);
    } else if ($action === "apr") {
        $stmt->bind_param("sss", $user_logged_in, $tanggal, $nogiro);
    } else if ($action === "add") {
        $stmt->bind_param("ss", $filename, $nogiro);
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
