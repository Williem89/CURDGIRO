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

// Fetch Giro and Cek entries grouped by BatchId
$entries = [];
$queryGiro = "SELECT * FROM data_giro WHERE statusgiro = 'Belum Aktif' ORDER BY BatchId, nogiro";
$queryCek = "SELECT * FROM data_cek WHERE statuscek = 'Belum Aktif' ORDER BY BatchId, nocek";
$queryloa = "SELECT * FROM data_loa WHERE statusloa = 'Belum Aktif' ORDER BY BatchId, noloa";

if ($resultGiro = $conn->query($queryGiro)) {
    while ($row = $resultGiro->fetch_assoc()) {
        $entries[$row['BatchId']]['giro'][] = $row; // Group by BatchId for Giro
    }
} else {
    die("Database query failed: " . $conn->error);
}

if ($resultCek = $conn->query($queryCek)) {
    while ($row = $resultCek->fetch_assoc()) {
        $entries[$row['BatchId']]['cek'][] = $row; // Group by BatchId for Cek
    }
} else {
    die("Database query failed: " . $conn->error);
}

if ($resultloa = $conn->query($queryloa)) {
    while ($row = $resultloa->fetch_assoc()) {
        $entries[$row['BatchId']]['loa'][] = $row; // Group by BatchId for loa
    }
} else {
    die("Database query failed: " . $conn->error);
}

// Initialize a message variable
$message = "";

// Process approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['batch'] as $batchId => $status) {
        if (!in_array($status, ['Approved', 'Rejected'])) {
            continue; // Skip invalid statuses
        }

        $newStatus = $status === 'Approved' ? 'Unused' : 'Rejected';
        $approveAt = date('d-m-y H:i:s'); // Get current date and time

        // Update Giro status
        $stmt = $conn->prepare("UPDATE data_giro SET statusgiro = ?, ApproveBy = ?, ApproveAt = ? WHERE BatchId = ?");
        if ($stmt) {
            $stmt->bind_param("ssss", $newStatus, $approveBy, $approveAt, $batchId);
            if (!$stmt->execute()) {
                die("Giro update failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            die("Prepare failed: " . $conn->error);
        }

        // Update Cek status
        $stmt = $conn->prepare("UPDATE data_cek SET statuscek = ?, ApproveBy = ?, ApproveAt = ? WHERE BatchId = ?");
        if ($stmt) {
            $stmt->bind_param("ssss", $newStatus, $approveBy, $approveAt, $batchId);
            if (!$stmt->execute()) {
                die("Cek update failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            die("Prepare failed: " . $conn->error);
        }

         // Update loa status
         $stmt = $conn->prepare("UPDATE data_loa SET statusloa = ?, ApproveBy = ?, ApproveAt = ? WHERE BatchId = ?");
         if ($stmt) {
             $stmt->bind_param("ssss", $newStatus, $approveBy, $approveAt, $batchId);
             if (!$stmt->execute()) {
                 die("loa update failed: " . $stmt->error);
             }
             $stmt->close();
         } else {
             die("Prepare failed: " . $conn->error);
         }

        // Handle rejection message
        if ($status === 'Rejected') {
            $message .= "Batch ID: " . htmlspecialchars($batchId) . " - Silahkan Hubungi administrator untuk merevisi data anda.<br>";
        }
    }
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
            font-family: 'Arial', sans-serif;
            background-color: #e9ecef; /* Light gray background */
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #343a40; /* Dark gray */
            margin-bottom: 20px;
        }

        section {
            background: #ffffff; /* White background for sections */
            border: 1px solid #ced4da; /* Light gray border */
            border-radius: 10px; /* Rounded corners */
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
        }

        h2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #007bff; /* Blue border */
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #007bff; /* Blue text */
        }

        .button-container {
            display: flex;
            justify-content: flex-start; /* Aligns items to the left */
            margin-bottom: 20px; /* Adds space below the button */
        }

        button.back {
            background-color: #28a745; /* Green for Back button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.back:hover {
            background-color: #218838; /* Darker green on hover */
        }

        button.approve {
            background-color: #007bff; /* Blue for Approve button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.approve:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        button.reject {
            background-color: #dc3545; /* Red for Reject button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.reject:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6; /* Light gray border */
        }

        th {
            background-color: #007bff; /* Blue header */
            color: white;
        }

        tr:hover {
            background-color: #f8f9fa; /* Light gray on hover */
        }

        .message {
            color: #dc3545; /* Red for error/success messages */
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
function confirmApproval(batchId) {
    Swal.fire({
        title: "Are you sure?",
        text: "Your data is correct, right?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, approve it!"
    }).then((result) => {
        if (result.isConfirmed) {
            // Update status
            document.getElementById('status-' + batchId).value = 'Approved';
            // Submit the form
            document.forms[0].submit(); 

            // Optionally show success message immediately
            Swal.fire({
                title: "Approved!",
                text: "The batch has been approved.",
                icon: "success",
                timer: 1500, // Optional: auto close after 1.5 seconds
                showConfirmButton: false
            });

            // Reload the page after form submission
            setTimeout(() => {
                location.reload(); // Reload the page
            }, 1600); // Wait slightly longer than the timer duration
        }
    });
}


function confirmReject(batchId) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545", // Red for reject
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, reject it!"
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('status-' + batchId).value = 'Rejected'; // Set the rejection status
            document.forms[0].submit(); // Submit the form after rejection

            // Show success message
            Swal.fire({
                title: "Rejected!",
                text: "The batch has been rejected.",
                icon: "success"
            }).then(() => {
                location.reload(); // Reload the page after the message is confirmed
            });
        }
    });
}

        </script>

</head>
<body>
    <h1>Approval Giro/Cek</h1>
    <div class="button-container">
            <button type="button" class="back" onclick="window.location.href='dashboard.php';">Back</button>
        </div>
        <form method="POST" action="">
    <?php 
    $batchCounter = 1; // Initialize batch counter
    foreach ($entries as $batchId => $batchEntries): ?>
        <section>
            <h2>
                <span><?php echo $batchCounter . ". Batch ID: " . htmlspecialchars($batchId); ?></span>
                <div>
                    <button type="button" class="approve" onclick="confirmApproval('<?php echo $batchId; ?>')">Approve</button>
                    <button type="button" class="reject" onclick="confirmReject('<?php echo $batchId; ?>')">Reject</button>
                    <input type="hidden" id="status-<?php echo $batchId; ?>" name="batch[<?php echo $batchId; ?>]" value="">
                </div>
            </h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis</th>
                        <th>No Giro</th>
                        <th>Nama Bank</th>
                        <th>Account Number</th>
                        <th>Account Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Initialize giro counter for each batch
                    if (isset($batchEntries['giro'])): 
                        $giroCounter = 1; 
                        foreach ($batchEntries['giro'] as $entry): ?>
                            <tr>
                                <td><?php echo $giroCounter++; ?></td>
                                <td><?php echo htmlspecialchars($entry['jenis_giro']); ?></td>
                                <td><?php echo htmlspecialchars($entry['nogiro']); ?></td>
                                <td><?php echo htmlspecialchars($entry['namabank']); ?></td>
                                <td><?php echo htmlspecialchars($entry['ac_number']); ?></td>
                                <td><?php echo htmlspecialchars($entry['ac_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['statusgiro']); ?></td>
                            </tr>
                        <?php endforeach; 
                    endif; ?>
                    
                    <?php 
                    // Reset counter for cek
                    if (isset($batchEntries['cek'])): 
                        $cekCounter = 1; 
                        foreach ($batchEntries['cek'] as $entry): ?>
                            <tr>
                                <td><?php echo $cekCounter++; ?></td>
                                <td><?php echo htmlspecialchars($entry['jenis_cek']); ?></td>
                                <td><?php echo htmlspecialchars($entry['nocek']); ?></td>
                                <td><?php echo htmlspecialchars($entry['namabank']); ?></td>
                                <td><?php echo htmlspecialchars($entry['ac_number']); ?></td>
                                <td><?php echo htmlspecialchars($entry['ac_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['statuscek']); ?></td>
                            </tr>
                        <?php endforeach; 
                    endif; ?>

                    <?php 
                    // Reset counter for loa
                    if (isset($batchEntries['loa'])): 
                        $loaCounter = 1; 
                        foreach ($batchEntries['loa'] as $entry): ?>
                            <tr>
                                <td><?php echo $loaCounter++; ?></td>
                                <td><?php echo htmlspecialchars($entry['jenis_loa']); ?></td>
                                <td><?php echo htmlspecialchars($entry['noloa']); ?></td>
                                <td><?php echo htmlspecialchars($entry['namabank']); ?></td>
                                <td><?php echo htmlspecialchars($entry['ac_number']); ?></td>
                                <td><?php echo htmlspecialchars($entry['ac_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['statusloa']); ?></td>
                            </tr>
                        <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
        </section>    

        <?php $batchCounter++; ?>
    <?php endforeach; ?>
</form>


    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
</body>
</html>
