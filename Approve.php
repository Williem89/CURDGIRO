<?php
// Include the database connection
include 'koneksi.php';

// Start the session
session_start();

// Set the timezone to GMT +7
date_default_timezone_set('Asia/Bangkok');

// Assuming user info is stored in session
if (!isset($_SESSION['username'])) {
    die("Unauthorized access. Please log in.");
}
$approveBy = $_SESSION['username']; // Get the logged-in user

// Fetch the generated giro/cek entries grouped by BatchId
$entries = [];
$result = $conn->query("SELECT * FROM data_giro WHERE statusgiro = 'Belum Aktif' ORDER BY BatchId, nogiro");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entries[$row['BatchId']][] = $row; // Group by BatchId
    }
}

// Initialize a message variable
$message = "";

// Process approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['batch'] as $batchId => $status) {
        if ($status === 'Approved') {
            $newStatus = 'Unused';
            $approveAt = date('Y-m-d H:i:s'); // Get current date and time
            $stmt = $conn->prepare("UPDATE data_giro SET statusgiro = ?, ApproveBy = ?, ApproveAt = ? WHERE BatchId = ?");
            $stmt->bind_param("ssss", $newStatus, $approveBy, $approveAt, $batchId);
        } else {
            // Handle rejection
            $newStatus = 'Rejected';
            $stmt = $conn->prepare("UPDATE data_giro SET statusgiro = ? WHERE BatchId = ?");
            $stmt->bind_param("ss", $newStatus, $batchId);
            $message .= "Batch ID: " . htmlspecialchars($batchId) . " - Silahkan Hubungi administrator untuk merevisi data anda.<br>";
        }

        if (!$stmt->execute()) {
            echo "<script>alert('Error updating approval status: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
    echo "<script>alert('Approval status updated successfully.');</script>";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Giro/Cek</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Approval Giro/Cek</h1>
    <form method="POST" action="">
        <?php 
        $batchCounter = 1; // Initialize batch counter
        foreach ($entries as $batchId => $batchEntries): ?>
            <h2><?php echo $batchCounter . ". Batch ID: " . htmlspecialchars($batchId); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Giro</th>
                        <th>Nama Bank</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $giroCounter = 1; // Initialize giro counter for each batch
                    foreach ($batchEntries as $entry): ?>
                        <tr>
                            <td><?php echo $giroCounter++; ?></td>
                            <td><?php echo htmlspecialchars($entry['nogiro']); ?></td>
                            <td><?php echo htmlspecialchars($entry['namabank']); ?></td>
                            <td><?php echo htmlspecialchars($entry['ac_number']); ?></td>
                            <td><?php echo htmlspecialchars($entry['ac_name']); ?></td>
                            <td><?php echo htmlspecialchars($entry['statusgiro']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <label>
                <input type="radio" name="batch[<?php echo $batchId; ?>]" value="Approved"> Approve
            </label>
            <label>
                <input type="radio" name="batch[<?php echo $batchId; ?>]" value="Rejected"> Reject
            </label>
            <br><br>
            <?php $batchCounter++; ?>
        <?php endforeach; ?>
        <input type="submit" value="Update Approval Status">
    </form>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
</body>
</html>
