<?php
// Include the database connection
include 'koneksi.php';

// Start the session
session_start();

// Check if user is logged in with sufficient privileges
if (!isset($_SESSION['username']) || !isset($_SESSION['UsrLevel']) || $_SESSION['UsrLevel'] != '2') {
    header('Location: backoff.html');
    exit();
}

$approveBy = $_SESSION['username']; // Get the logged-in user

// Fetch the generated giro entries grouped by BatchId
$entries = [];
$resultGiro = $conn->query("SELECT * FROM data_giro WHERE statusgiro = 'Belum Aktif' ORDER BY BatchId, nogiro");

if ($resultGiro) {
    while ($row = $resultGiro->fetch_assoc()) {
        $entries[$row['BatchId']]['giro'][] = $row; // Group by BatchId for Giro
    }
}

// Fetch the generated cek entries grouped by BatchId
$resultCek = $conn->query("SELECT * FROM data_cek WHERE statuscek = 'Belum Aktif' ORDER BY BatchId, nocek");

if ($resultCek) {
    while ($row = $resultCek->fetch_assoc()) {
        $entries[$row['BatchId']]['cek'][] = $row; // Group by BatchId for Cek
    }
}

// Initialize a message variable
$message = "";

// Process approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['batch'] as $batchId => $status) {
        $newStatus = $status === 'Approved' ? 'Unused' : 'Rejected';
        $approveAt = date('d-m-y H:i:s'); // Get current date and time
        
        // Update Giro status
        $stmt = $conn->prepare("UPDATE data_giro SET statusgiro = ?, ApproveBy = ?, ApproveAt = ? WHERE BatchId = ?");
        $stmt->bind_param("ssss", $newStatus, $approveBy, $approveAt, $batchId);
        $stmt->execute();
        $stmt->close();

        // Update Cek status
        $stmt = $conn->prepare("UPDATE data_cek SET statuscek = ?, ApproveBy = ?, ApproveAt = ? WHERE BatchId = ?");
        $stmt->bind_param("ssss", $newStatus, $approveBy, $approveAt, $batchId);
        $stmt->execute();
        $stmt->close();

        // Handle rejection message
        if ($status === 'Rejected') {
            $message .= "Batch ID: " . htmlspecialchars($batchId) . " - Silahkan Hubungi administrator untuk merevisi data anda.<br>";
        }
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        h2 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        label {
            margin-right: 20px;
        }
        .message {
            color: #dc3545;
            margin-top: 20px;
            font-weight: bold;
        }
        .button-container {
            display: flex;
            justify-content: center; /* Center the button */
            margin-top: 20px; /* Space above the button */
        }
    </style>
</head>
<body>
    <h1>Approval Giro/Cek</h1>
    <form method="POST" action="">
        <?php 
        $batchCounter = 1; // Initialize batch counter
        foreach ($entries as $batchId => $batchEntries): ?>
            <section>
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
                        if (isset($batchEntries['giro'])) {
                            foreach ($batchEntries['giro'] as $entry): ?>
                                <tr>
                                    <td><?php echo $giroCounter++; ?></td>
                                    <td><?php echo htmlspecialchars($entry['nogiro']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['namabank']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['ac_number']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['ac_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['statusgiro']); ?></td>
                                </tr>
                            <?php endforeach; 
                        }
                        
                        // Reset counter for cek
                        $cekCounter = 1;
                        if (isset($batchEntries['cek'])): ?>
                            <tr>
                                <th colspan="6" style="background-color: #007bff; color: white;">Data Cek</th>
                            </tr>
                            <tr>
                                <th>No</th>
                                <th>No Cek</th>
                                <th>Nama Bank</th>
                                <th>Account Number</th>
                                <th>Account Name</th>
                                <th>Status</th>
                            </tr>
                            <?php foreach ($batchEntries['cek'] as $entry): ?>
                                <tr>
                                    <td><?php echo $cekCounter++; ?></td>
                                    <td><?php echo htmlspecialchars($entry['nocek']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['namabank']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['ac_number']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['ac_name']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['statuscek']); ?></td>
                                </tr>
                            <?php endforeach; 
                        endif; ?>
                    </tbody>
                </table>
                <div>
                    <label>
                        <input type="radio" name="batch[<?php echo $batchId; ?>]" value="Approved"> Approve
                    </label>
                    <label>
                        <input type="radio" name="batch[<?php echo $batchId; ?>]" value="Rejected"> Reject
                    </label>
                </div>
                <br>
            </section>
            <?php $batchCounter++; ?>
        <?php endforeach; ?>
        <div class="button-container">
            <input type="submit" value="Submit" onclick="setTimeout(() => { location.reload(); }, 1000);">
            <button type="button" onclick="window.location.href='dashboard.php';">Back</button>
            </div>
    </form>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
</body>
</html>
