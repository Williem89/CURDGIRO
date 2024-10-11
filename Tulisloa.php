<?php
// Database connection
include 'koneksi.php';

// Start session
session_start();

// Assuming the username is stored in session after user login
$createdBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';

// Initialize arrays to hold loa numbers and customer data
$loa_data = [];

// Fetch all loa numbers along with their bank and account numbers from the database
$fetch_stmt = $conn->prepare("SELECT noloa, namabank, ac_number FROM data_loa WHERE statusloa = 'Unused'");
$fetch_stmt->execute();
$fetch_stmt->bind_result($loa_number, $namabank, $ac_number);

while ($fetch_stmt->fetch()) {
    $loa_data[$loa_number] = ['namabank' => $namabank, 'ac_number' => $ac_number];
}
$fetch_stmt->close();

// Check if form is submitted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values and sanitize them
    $selected_loa_number = filter_input(INPUT_POST, 'loa_number', FILTER_SANITIZE_STRING);
    //$no_cust = filter_input(INPUT_POST, 'no_cust', FILTER_SANITIZE_STRING);
    $nama_penerima = filter_input(INPUT_POST, 'nama_penerima', FILTER_SANITIZE_STRING);
    $tanggal_loa = filter_input(INPUT_POST, 'tanggal_loa', FILTER_SANITIZE_STRING);
    $tanggal_jatuh_tempo = filter_input(INPUT_POST, 'tanggal_jatuh_tempo', FILTER_SANITIZE_STRING);
    $nominal = filter_input(INPUT_POST, 'nominal', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $Keterangan = filter_input(INPUT_POST, 'Keterangan', FILTER_SANITIZE_STRING);
    $PVRNo = filter_input(INPUT_POST, 'PVRNo', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($selected_loa_number) || empty($tanggal_loa) || empty($tanggal_jatuh_tempo) || 
        empty($nominal)) {
        $message = 'Error: All fields are required.';
    } else if ($nominal <= 0) {
        $message = 'Error: Nominal must be greater than zero.';
    //} else if (!is_uploaded_file($_FILES['foto_loa']['tmp_name'])) {
        //$message = 'Error: No file input';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        // Define the target directory and file name
        $targetDir = "imggiro/";
        $fileName = basename($_FILES["foto_giro"]["name"]);
        
        // Generate a random string to append to the filename
        $randomString = bin2hex(random_bytes(8));
        $fileName = $randomString . "_" . $fileName;

        $targetFilePath = $targetDir . $fileName;
        try {
            // Prepare statement to insert into the detail_loa table
            $stmt = $conn->prepare("INSERT INTO detail_loa (noloa, tanggal_loa, tanggal_jatuh_tempo, nominal, 
                nama_penerima, ac_penerima, bank_penerima, Keterangan, PVRNo, Statloa, image_giro, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            // Check if statement preparation was successful
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }

            // Bind parameters
            $statloa = 'Pending';  // Set Statloa to 'Issued'

            $stmt->bind_param("ssssssssssss", 
                $selected_loa_number, 
                $tanggal_loa, 
                $tanggal_jatuh_tempo, 
                $nominal, 
                $nama_penerima, 
                $ac_penerima, 
                $bank_penerima, 
                $Keterangan,
                $PVRNo, 
                $statloa,
                $fileName,
                $createdBy
            );

            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }

            // Update status of the selected loa number to 'Used'
            $update_stmt = $conn->prepare("UPDATE data_loa SET statusloa = 'Used' WHERE noloa = ?");
            $update_stmt->bind_param("s", $selected_loa_number);
            $update_stmt->execute();

            // Commit transaction
            $conn->commit();
            $message = 'New record created successfully and status updated to Used.';
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            $message = 'An error occurred. Please try again.';
        } finally {
            // Close the statements
            if (isset($stmt)) $stmt->close();
            if (isset($update_stmt)) $update_stmt->close();
        }
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issued LOA</title>
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
        label {
            display: block;
            margin: 10px 0 5px;
            color: #495057;
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
            text-align: center;
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
            color: red; /* Error message color */
        }
        .success-message {
            color: green; /* Success message color */
        }
    </style>
    <script>
        function updateBankAndAccount() {
            const loaSelect = document.getElementById('loa_number');
            const selectedloa = loaSelect.value;
            const bankInput = document.getElementById('namabank');
            const acNumberInput = document.getElementById('ac_number');

            if (selectedloa) {
                const data = <?php echo json_encode($loa_data); ?>;
                bankInput.value = data[selectedloa].namabank;
                acNumberInput.value = data[selectedloa].ac_number;
                document.getElementById('search_loa_no').value = selectedloa;
                document.getElementById('loa_number').style.display = "none"
            } else {
                bankInput.value = '';
                acNumberInput.value = '';
            }
        }


        function setDefaultDueDate() {
            const tanggalloa = document.getElementById('tanggal_loa');
            const tanggalJatuhTempo = document.getElementById('tanggal_jatuh_tempo');
            tanggalJatuhTempo.value = tanggalloa.value;
        }

        function searchloa() {
            const input = document.getElementById('search_loa_no').value.toLowerCase();
            const select = document.getElementById('loa_number');
            const options = select.getElementsByTagName('option');

            let hasOptions = false;

            // Loop through options and filter based on input
            for (let i = 0; i < options.length; i++) {
                const optionText = options[i].textContent.toLowerCase();
                const isVisible = optionText.includes(input);
                options[i].style.display = isVisible ? 'block' : 'none';
                
                if (isVisible) {
                    hasOptions = true;
                }
            }

            // Hide or show the select element based on input
            select.style.display = input ? (hasOptions ? 'block' : 'none') : 'none';
        }

        function clearInput() {
            const input = document.getElementById('search_loa_no');
            input.value = '';
        }
    </script>
</head>
<body>
    <h1>Issued LOA</h1>
    <?php if ($message): ?>
        <div class="<?php echo strpos($message, 'Error') === false ? 'success-message' : 'message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="loa_number">No loa:</label>
        <input type="text" id="search_loa_no" oninput="searchloa()" onfocusout="clearInput()" required>
        <select id="loa_number" name="loa_number" size="5" required onchange="updateBankAndAccount()" style="display:none;">
            <?php foreach (array_keys($loa_data) as $loa): ?>
                <option value="<?php echo htmlspecialchars($loa); ?>"><?php echo htmlspecialchars($loa); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank" readonly required><br><br>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" readonly required><br><br>

        <label for="tanggal_loa">Tanggal loa:</label>
        <input type="date" id="tanggal_loa" name="tanggal_loa" required onchange="setDefaultDueDate()"><br><br>

        <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo:</label>
        <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required><br><br>

        <label for="nominal">Nominal:</label>
        <input type="number" id="nominal" name="nominal" required><br><br>

        <label for="nama_penerima">Nama Penerima:</label>
        <input type="text" id="nama_penerima" name="nama_penerima" required><br><br>
     
        <label for="ac_penerima">Account Number Penerima:</label>
        <input type="text" id="ac_penerima" name="ac_penerima"><br><br>
       
        <label for="bank_cust">Bank Penerima:</label>
        <input type="text" id="bank_cust" name="bank_cust"><br><br>

        <label for="PVRNo">PVR No:</label>
        <input type="text" id="PVRNo" name="PVRNo"><br><br>

        <label for="Keterangan">Keterangan:</label>
        <input type="text" id="Keterangan" name="Keterangan"><br><br>

        <label for="Keterangan">Foto Giro:</label>
        <input type="file" id="Keterangan" name="foto_giro"><br><br>

        <input type="submit" value="Submit">
        <a href="dashboard.php" class="back-button">Kembali</a>
    </form>
</body>
</html>