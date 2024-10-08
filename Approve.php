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

// Function to fetch entries
function fetchEntries($conn) {
    $entries = []; // Initialize as an array
    
    // Fetch Giro entries
    $resultGiro = $conn->query("SELECT * FROM data_giro WHERE statusgiro = 'Belum Aktif' ORDER BY BatchId, nogiro");
    if ($resultGiro && $resultGiro->num_rows > 0) {
        while ($row = $resultGiro->fetch_assoc()) {
            $entries[$row['BatchId']]['giro'][] = $row; // Group by BatchId for Giro
        }
    }

    // Fetch Cek entries
    $resultCek = $conn->query("SELECT * FROM data_cek WHERE statuscek = 'Belum Aktif' ORDER BY BatchId, nocek");
    if ($resultCek && $resultCek->num_rows > 0) {
        while ($row = $resultCek->fetch_assoc()) {
            $entries[$row['BatchId']]['cek'][] = $row; // Group by BatchId for Cek
        }
    }

    return $entries; // Always return an array
}

// Initialize a message variable
$message = "";

/// Process approval
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging output
    var_dump($_POST); 
    
    if (isset($_POST['batch'])) {
        $batch = $_POST['batch'];
        foreach ($batch as $id => $status) {
            echo "Batch ID: " . htmlspecialchars($id) . " - Status: " . htmlspecialchars($status) . "<br>";
        }
    } else {
        echo "No batch data received.";
    }
}
// Fetch entries
$entries = fetchEntries($conn);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Giro/Cek</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        input[type="submit"], button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s;
            margin: 0 5px;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .message {
            color: #dc3545;
            margin-top: 20px;
            font-weight: bold;
        }
        .form-container {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <h1>Approval Giro/Cek</h1>
    <button type="button" onclick="window.location.href='dashboard.php';">Back</button>

    <?php 
    // Display messages if available
    if ($message) {
        echo "<div class='message'>$message</div>";
    }

    // Check if $entries is not empty before rendering
    if (!empty($entries)) {
        $batchCounter = 1; // Initialize batch counter
        foreach ($entries as $batchId => $batchEntries): ?>
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-header">
                        <h2><?php echo $batchCounter . ". Batch ID: " . htmlspecialchars($batchId); ?></h2>
                        <div>
                            <button type="submit" name="batch[<?php echo $batchId; ?>]" value="Approved">Approve</button>
                            <button type="submit" name="batch[<?php echo $batchId; ?>]" value="Rejected" style="background-color: #dc3545;">Reject</button>
                        </div>
                    </div>
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
                </form>
            </div>
            <?php $batchCounter++; ?>
        <?php endforeach; 
    } else {
        echo "<p>No entries available for approval.</p>";
    }
    ?>
    
    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault(); // Prevent form submission
                
                // Determine the button value
                const buttonValue = e.submitter.value; // Get the value of the button that was clicked

                // Set up SweetAlert2 options based on the button value
                let title, text;
                if (buttonValue === 'Approved') {
                    title = "Are you sure you want to approve?";
                    text = "This action cannot be undone.";
                } else {
                    title = "Are you sure you want to reject?";
                    text = "Please ensure that you contact the administrator for revisions.";
                }

                // Show the confirmation dialog
                Swal.fire({
                    title: title,
                    text: text,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: buttonValue === 'Approved' ? "Yes, approve it!" : "Yes, reject it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    </script>
</body>
</html>
