<?php
// Database connection
include 'koneksi.php';

// Start session
session_start();
$createdBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';

// Fetch cek numbers and customer data from the database
$cek_data = [];
$fetch_stmt = $conn->prepare("SELECT nocek, namabank, ac_number FROM data_cek WHERE statuscek = 'Unused'");
$fetch_stmt->execute();
$fetch_stmt->bind_result($cek_number, $namabank, $ac_number);

while ($fetch_stmt->fetch()) {
    $cek_data[$cek_number] = ['namabank' => $namabank, 'ac_number' => $ac_number];
}
$fetch_stmt->close();

// Check if form is submitted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_cek_number = filter_input(INPUT_POST, 'cek_number', FILTER_SANITIZE_STRING);
    $tanggal_cek = filter_input(INPUT_POST, 'tanggal_cek', FILTER_SANITIZE_STRING);
    $tanggal_jatuh_tempo = filter_input(INPUT_POST, 'tanggal_jatuh_tempo', FILTER_SANITIZE_STRING);
    $nominal = filter_input(INPUT_POST, 'nominal', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $Keterangan = filter_input(INPUT_POST, 'Keterangan', FILTER_SANITIZE_STRING);
    $nama_penerima = filter_input(INPUT_POST, 'nama_penerima', FILTER_SANITIZE_STRING);
    $ac_penerima = filter_input(INPUT_POST, 'ac_penerima', FILTER_SANITIZE_STRING);
    $bank_penerima = filter_input(INPUT_POST, 'bank_cust', FILTER_SANITIZE_STRING);
    $PVRNo = filter_input(INPUT_POST, 'PVRNo', FILTER_SANITIZE_STRING);

    if (empty($selected_cek_number) || empty($tanggal_cek) || empty($tanggal_jatuh_tempo) || 
        empty($nominal) || empty($Keterangan) || empty($nama_penerima)) {
        $message = 'Error: All fields are required.';
    } elseif ($nominal <= 0) {
        $message = 'Error: Nominal must be greater than zero.';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO detail_cek (nocek, tanggal_cek, tanggal_jatuh_tempo, nominal, 
                nama_penerima, ac_penerima, bank_penerima, Keterangan, PVRNo, Statcek, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }

            $statcek = 'Issued';  // Set Statcek to 'Issued'
            $stmt->bind_param("sssssssssss", 
                $selected_cek_number, 
                $tanggal_cek, 
                $tanggal_jatuh_tempo, 
                $nominal, 
                $nama_penerima, 
                $ac_penerima, 
                $bank_penerima, 
                $Keterangan,
                $PVRNo, 
                $statcek, 
                $createdBy
            );

            if (!$stmt->execute()) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }

            // Update status of the selected cek number to 'Used'
            $update_stmt = $conn->prepare("UPDATE data_cek SET statuscek = 'Used' WHERE nocek = ?");
            $update_stmt->bind_param("s", $selected_cek_number);
            $update_stmt->execute();

            $conn->commit();
            $message = 'New record created successfully and status updated to Used.';
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            $message = 'An error occurred. Please try again.';
        } finally {
            if (isset($stmt)) $stmt->close();
            if (isset($update_stmt)) $update_stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issued Cek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #343a40;
        }
        form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 500px;
            margin: auto;
        }
        input[type="text"], input[type="date"], input[type="number"], select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        input[type="submit"], .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
            width: calc(100% - 20px);
        }
        input[type="submit"]:hover, .back-button:hover {
            background-color: #0056b3;
        }
        .back-button {
            background-color: #6c757d;
            margin-top: 20px;
        }
        .message {
            text-align: center;
            margin: 10px 0;
            color: red;
        }
        .success-message {
            color: green;
        }
    </style>
    <script>
        function updateBankAndAccount() {
            const cekSelect = document.getElementById('cek_number');
            const selectedcek = cekSelect.value;
            const bankInput = document.getElementById('namabank');
            const acNumberInput = document.getElementById('ac_number');

            if (selectedcek) {
                const data = <?php echo json_encode($cek_data); ?>;
                bankInput.value = data[selectedcek].namabank;
                acNumberInput.value = data[selectedcek].ac_number;
            } else {
                bankInput.value = '';
                acNumberInput.value = '';
            }
        }

        function setDefaultDueDate() {
            const tanggalcek = document.getElementById('tanggal_cek');
            const tanggalJatuhTempo = document.getElementById('tanggal_jatuh_tempo');
            tanggalJatuhTempo.value = tanggalcek.value;
        }

        function searchcek() {
            const input = document.getElementById('search_cek_no').value.toLowerCase();
            const select = document.getElementById('cek_number');
            const options = select.getElementsByTagName('option');

            let hasOptions = false;

            for (let i = 0; i < options.length; i++) {
                const optionText = options[i].textContent.toLowerCase();
                const isVisible = optionText.includes(input);
                options[i].style.display = isVisible ? 'block' : 'none';
                if (isVisible) hasOptions = true;
            }

            select.style.display = input ? (hasOptions ? 'block' : 'none') : 'none';
        }
    </script>
</head>
<body>
    <h1>Issued Cek</h1>
    <?php if ($message): ?>
        <div class="<?php echo strpos($message, 'Error') === false ? 'success-message' : 'message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="cek_number">No Cek:</label>
        <input type="text" id="search_cek_no" oninput="searchcek()" required>
        <select id="cek_number" name="cek_number" size="5" onchange="updateBankAndAccount()" style="display:none;">
            <?php foreach (array_keys($cek_data) as $cek): ?>
                <option value="<?php echo htmlspecialchars($cek); ?>"><?php echo htmlspecialchars($cek); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank" readonly required><br><br>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" readonly required><br><br>

        <label for="tanggal_cek">Tanggal Cek:</label>
        <input type="date" id="tanggal_cek" name="tanggal_cek" required onchange="setDefaultDueDate()"><br><br>

        <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo:</label>
        <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required><br><br>

        <label for="nominal">Nominal:</label>
        <input type="number" id="nominal" name="nominal" step="0.01" required><br><br>

        <label for="nama_penerima">Nama Penerima:</label>
        <input type="text" id="nama_penerima" name="nama_penerima" required><br><br>

        <label for="ac_penerima">A/C Penerima:</label>
        <input type="text" id="ac_penerima" name="ac_penerima" required><br><br>

        <label for="bank_cust">Bank Penerima:</label>
        <input type="text" id="bank_cust" name="bank_cust" required><br><br>

        <label for="PVRNo">PVR No:</label>
        <input type="text" id="PVRNo" name="PVRNo"><br><br>

        <input type="submit" value="Submit">
        <a href="your-back-url-here" class="back-button">Back</a>
    </form>
</body>
</html>
