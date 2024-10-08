<?php
// Include the database connection
include 'koneksi.php';

// Start the session
session_start();

// Fetch entities from list_entitas
$entities = [];
$result = $conn->query("SELECT id_entitas, nama_entitas FROM list_entitas");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entities[] = $row;
    }
}

// Initialize Batch ID
$batchId = '';

// Fetch the last Batch ID from the database
$sql = "SELECT BatchId FROM data_giro ORDER BY BatchId DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $lastBatchId = $row['BatchId'];
    $lastNumber = intval(substr($lastBatchId, 0, 4));
    $newBatchNumber = $lastNumber + 1;
} else {
    $newBatchNumber = 1;
}

// Generate a new Batch ID with random number and current date
$randomNumber = rand(1000, 9999);
$todayDate = date('Ymd');
$batchId = str_pad($newBatchNumber, 4, '0', STR_PAD_LEFT) . $randomNumber . $todayDate;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $prefix = filter_input(INPUT_POST, 'prefix', FILTER_SANITIZE_STRING);
    $start_number = filter_input(INPUT_POST, 'Start_number', FILTER_SANITIZE_STRING);
    $jumlah_giro = filter_input(INPUT_POST, 'Jumlah_giro', FILTER_SANITIZE_NUMBER_INT);
    $namabank = filter_input(INPUT_POST, 'namabank', FILTER_SANITIZE_STRING);
    $ac_number = filter_input(INPUT_POST, 'ac_number', FILTER_SANITIZE_STRING);
    $ac_name = filter_input(INPUT_POST, 'ac_name', FILTER_SANITIZE_STRING);
    $jenis_giro = filter_input(INPUT_POST, 'jenis_giro', FILTER_SANITIZE_STRING);
    $id_entitas = filter_input(INPUT_POST, 'nama_entitas', FILTER_SANITIZE_NUMBER_INT);

    // Get the logged-in user's username
    $created_by = $_SESSION['username'] ?? 'system';

    // Check for empty fields
    if (empty($prefix) || empty($start_number) || empty($jumlah_giro) || empty($namabank) || empty($ac_number) || empty($ac_name) || empty($jenis_giro) || empty($id_entitas)) {
        echo "<script>alert('Error: All fields are required.');</script>";
    } else {
        // Ensure start_number is always 6 characters long
        $start_number = str_pad($start_number, 6, '0', STR_PAD_LEFT);
        $end_number = (int)$start_number + (int)$jumlah_giro - 1;
        $giro_numbers = [];

        for ($i = (int)$start_number; $i <= $end_number; $i++) {
            // Include prefix in the giro number
            $giro_number = $prefix . "-" . str_pad($i, 6, '0', STR_PAD_LEFT);
            $giro_numbers[] = $giro_number;

            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM data_giro WHERE nogiro = ?");
            $check_stmt->bind_param("s", $giro_number);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                echo "<script>alert('Nomor giro $giro_number sudah ada, tidak akan dimasukkan.');</script>";
                continue;
            }

            // Choose the correct table and prepare the insert statement
            if ($jenis_giro === 'Giro') {
                $stmt = $conn->prepare("INSERT INTO data_giro (BatchId, nogiro, namabank, ac_number, ac_name, statusgiro, created_by, created_at, jenis_giro, id_entitas) 
                VALUES (?, ?, ?, ?, ?, 'Belum Aktif', ?, NOW(), ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("sssssssi", $batchId, $giro_number, $namabank, $ac_number, $ac_name, $created_by, $jenis_giro, $id_entitas);
                } else {
                    echo "<script>alert('Error preparing statement for data_giro: " . $conn->error . "');</script>";
                }
            } else if ($jenis_giro === 'Cek') {
                $stmt = $conn->prepare("INSERT INTO data_cek (BatchId, NOCEK, namabank, ac_number, ac_name, statuscek, created_by, created_at, jenis_cek, id_entitas) 
                VALUES (?, ?, ?, ?, ?, 'Belum Aktif', ?, NOW(), ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("sssssssi", $batchId, $giro_number, $namabank, $ac_number, $ac_name, $created_by, $jenis_giro, $id_entitas);
                } else {
                    echo "<script>alert('Error preparing statement for data_cek: " . $conn->error . "');</script>";
                }
            } else if ($jenis_giro === 'loa') {
                $stmt = $conn->prepare("INSERT INTO data_loa (BatchId, noloa, namabank, ac_number, ac_name, statusloa, created_by, created_at, jenis_loa, id_entitas) 
                VALUES (?, ?, ?, ?, ?, 'Belum Aktif', ?, NOW(), ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("sssssssi", $batchId, $giro_number, $namabank, $ac_number, $ac_name, $created_by, $jenis_giro, $id_entitas);
                } else {
                    echo "<script>alert('Error preparing statement for data_cek: " . $conn->error . "');</script>";
                }
            }

            // Execute the statement
            if ($stmt) {
                if (!$stmt->execute()) {
                    echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
                }
                $stmt->close();
            }
        }

        if (!empty($giro_numbers)) {
            echo "<script>alert('New records created successfully for giro numbers: " . implode(', ', $giro_numbers) . "');</script>";
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
    <title>Insert Data Giro</title>
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
        form {
            max-width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="number"],
        input[type="text"],
        select {
            width: 100%;
            padding: 5px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"],
        a {
            display: inline-block;
            width: 25%;
            padding: 8px 5px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            margin: 6px 0;
            border: none;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        a {
            color: white;
            background-color: #007bff;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function fetchAccountNumbers() {
            const entitySelect = document.getElementById('nama_entitas');
            const entityId = entitySelect.value;

            const acNumberSelect = document.getElementById('ac_number');
            acNumberSelect.innerHTML = '<option value="">-- Pilih Account Number --</option>';

            if (entityId) {
                fetch(`fetch_accounts.php?id_entitas=${entityId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.no_akun;
                            option.textContent = account.no_akun;
                            acNumberSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching account numbers:', error);
                    });
            }
        }

        function validateForm() {
            const prefix = document.getElementById('prefix').value;
            const startNumber = document.getElementById('Start_number').value;

            // Check if prefix length is greater than 3
            if (prefix.length > 3) {
                alert('Error: Prefix must be a maximum of 3 characters.');
                return false;
            }

            // Check if start number is exactly 6 characters
            if (startNumber.length !== 6) {
                alert('Error: Start number must be exactly 6 characters.');
                return false;
            }

            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('nama_entitas').addEventListener('change', fetchAccountNumbers);
            document.getElementById('ac_number').addEventListener('change', function() {
                const selectedAccount = this.value;
                if (selectedAccount) {
                    fetch(`fetch_account_details.php?ac_number=${selectedAccount}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('bank_name_display').textContent = data.bank_name;
                            document.getElementById('namabank').value = data.bank_name; // Set the input value
                            document.getElementById('ac_name').value = data.account_name; // Set the input value
                            document.getElementById('account_name_display').textContent = data.account_name;
                        })
                        .catch(error => {
                            console.error('Error fetching account details:', error);
                        });
                }
            });
        });
    </script>
</head>
<body>
    <h1>Generate Giro/Cek</h1>
    <form method="POST" action="" onsubmit="return validateForm();">
        <label for="jenis_giro">Jenis:</label>
            <select id="jenis_giro" name="jenis_giro" required>
                <option value="Giro" selected>Giro</option>
                <option value="Cek">Cek</option>
                <option value="loa">LOA</option>
            </select>

        <br>
        <label for="batchId">Batch ID:</label>
        <input type="text" id="batchId" name="batchId" value="<?php echo htmlspecialchars($batchId); ?>" readonly disabled>
        
        <label for="nama_entitas">Entitas:</label>
        <select id="nama_entitas" name="nama_entitas" required>
            <option value="">-- Pilih Entitas --</option>
            <?php foreach ($entities as $entity): ?>
                <option value="<?php echo $entity['id_entitas']; ?>">
                    <?php echo htmlspecialchars($entity['nama_entitas']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="ac_number">Account Number:</label>
        <select id="ac_number" name="ac_number" required>
            <option value="">Select Account Number</option>
        </select>

        <div>
            <strong>Bank Name:</strong> <span id="bank_name_display" hidden></span>
            <input type="text" id="namabank" name="namabank" value="" required readonly>
        </div>
        <br>
        <div>
            <strong>Account Name:</strong> <span id="account_name_display" hidden></span>
            <input type="text" id="ac_name" name="ac_name" value="" required readonly>
        </div>

        <br>

        <label for="Start_number">Mulai dari no. :</label>
        <table>
            <tr>
                <td><input type="text" id="prefix" name="prefix" required maxlength="3" style="width:70px;"></td>
                <td>-</td> 
                <td><input type="text" id="Start_number" name="Start_number" required maxlength="6" style="width:220px;"></td>
            </tr>
        </table>

        <label for="Jumlah_giro">Jumlah Giro:</label>
        <input type="number" id="Jumlah_giro" name="Jumlah_giro" required>

        <input type="submit" value="Submit">
        <a href="dashboard.php">Kembali</a>
    </form>
</body>
</html>
